<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected ShiftService $shiftService
    ) {}

    /**
     * Generate a unique return number.
     * Format: RET-YYYYMMDD-XXXX
     */
    public function generateUniqueReturnNumber(): string
    {
        $prefix = 'RET-' . now()->format('Ymd') . '-';

        do {
            $random = strtoupper(Str::random(4));
            $returnNumber = $prefix . $random;
        } while (SaleReturn::where('return_number', $returnNumber)->exists());

        return $returnNumber;
    }

    /**
     * Search and retrieve an original completed sale with calculated returnable quantities.
     *
     * @param string $search Invoice number or ID
     * @return Sale
     * @throws ValidationException
     */
    public function getInvoiceForReturn(string $search): Sale
    {
        $sale = Sale::with(['items.product', 'user', 'shift', 'returns.items'])
            ->where('invoice_number', trim($search))
            ->orWhere('id', is_numeric($search) ? (int)$search : 0)
            ->first();

        if (!$sale) {
            throw ValidationException::withMessages([
                'search' => ['لم يتم العثور على أي فاتورة مبيعات مطابقة لرقم البحث المدخل.'],
            ]);
        }

        if ($sale->status === 'cancelled') {
            throw ValidationException::withMessages([
                'search' => ['لا يمكن عمل مرتجع لفاتورة ملغاة بالفعل.'],
            ]);
        }

        // Attach dynamic returnable quantities for each sale item
        foreach ($sale->items as $item) {
            $alreadyReturned = (float) ReturnItem::where('sale_item_id', $item->id)
                ->whereHas('saleReturn', fn($q) => $q->where('status', 'approved'))
                ->sum('quantity');

            $returnableQty = max(0, round((float) $item->quantity - $alreadyReturned, 3));

            $item->returned_quantity_calculated = $alreadyReturned;
            $item->returnable_quantity_calculated = $returnableQty;
        }

        $sale->total_returnable_items = $sale->items->sum('returnable_quantity_calculated');

        return $sale;
    }

    /**
     * Create a full or partial return inside an atomic database transaction.
     *
     * 1. Validate active shift for cashier.
     * 2. Lock original sale and products in ascending ID order to prevent deadlocks.
     * 3. Validate returnable quantities: return_qty <= sold_qty - already_returned_qty.
     * 4. Calculate refund totals strictly using original sale item unit prices.
     * 5. Create return and return item records.
     * 6. Increase inventory using InventoryService and record immutable movement history.
     * 7. Record activity audit log.
     *
     * @param array $data
     * @param User $user
     * @param Shift|null $shift
     * @return SaleReturn
     * @throws ValidationException
     */
    public function createReturn(array $data, User $user, ?Shift $shift = null): SaleReturn
    {
        // 1. Shift validation
        if (!$shift) {
            $shift = $this->shiftService->getActiveShift($user);
        }

        if (!$shift || $shift->status !== 'open') {
            throw ValidationException::withMessages([
                'shift' => ['لا يمكن إتمام المرتجع دون وجود وردية عمل مفتوحة للكاشير. يرجى فتح وردية أولاً.'],
            ]);
        }

        // 2. Validate sale existence
        $saleId = $data['sale_id'] ?? null;
        if (!$saleId) {
            throw ValidationException::withMessages([
                'sale_id' => ['معرف الفاتورة الأصلية مطلوب.'],
            ]);
        }

        // 3. Validate items input
        $itemsData = $data['items'] ?? [];
        if (empty($itemsData) || !is_array($itemsData)) {
            throw ValidationException::withMessages([
                'items' => ['يجب تحديد منتج واحد على الأقل مع كمية الإرجاع.'],
            ]);
        }

        // Filter and aggregate quantities
        $sanitizedItems = [];
        foreach ($itemsData as $index => $item) {
            if (!isset($item['sale_item_id'])) {
                continue;
            }

            $saleItemId = (int) $item['sale_item_id'];
            $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0.0;

            if ($qty < 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => ['كمية المرتجع لا يمكن أن تكون سالبة.'],
                ]);
            }

            if ($qty > 0) {
                $sanitizedItems[$saleItemId] = ($sanitizedItems[$saleItemId] ?? 0) + $qty;
            }
        }

        if (empty($sanitizedItems)) {
            throw ValidationException::withMessages([
                'items' => ['يجب تحديد كمية صالحة أكبر من الصفر لمنتج واحد على الأقل لإتمام المرتجع.'],
            ]);
        }

        // 4. Validate refund method
        $refundMethod = $data['refund_method'] ?? 'cash';
        if (!in_array($refundMethod, ['cash', 'vodafone_cash', 'instapay'], true)) {
            throw ValidationException::withMessages([
                'refund_method' => ['طريقة الاسترداد المحددة غير صالحة. الطرق المتاحة: نقداً (كاش)، فودافون كاش، انستا باي.'],
            ]);
        }

        $reason = trim($data['reason'] ?? 'مرتجع مبيعات');
        if (empty($reason)) {
            $reason = 'مرتجع مبيعات';
        }

        // Execute atomic database transaction
        return DB::transaction(function () use ($saleId, $sanitizedItems, $refundMethod, $reason, $user, $shift) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::where('id', $saleId)->lockForUpdate()->first();

            if (!$lockedSale) {
                throw ValidationException::withMessages([
                    'sale_id' => ['الفاتورة الأصلية غير موجودة بالنظام.'],
                ]);
            }

            if ($lockedSale->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'sale_id' => ['لا يمكن إنشاء مرتجع لفاتورة مبيعات ملغاة.'],
                ]);
            }

            // Lock SaleItem records
            $saleItemIds = array_keys($sanitizedItems);
            $lockedSaleItems = SaleItem::whereIn('id', $saleItemIds)
                ->where('sale_id', $lockedSale->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($lockedSaleItems->count() !== count($saleItemIds)) {
                throw ValidationException::withMessages([
                    'items' => ['بعض بنود الفاتورة المحددة غير صحيحة أو لا تنتمي لهذه الفاتورة.'],
                ]);
            }

            // Lock Product records in ascending ID order to prevent deadlocks across concurrent transactions
            $productIds = $lockedSaleItems->pluck('product_id')->unique()->sort()->values()->all();
            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $preparedItems = [];
            $totalRefundAmount = 0.0;

            foreach ($sanitizedItems as $saleItemId => $returnQty) {
                /** @var SaleItem $saleItem */
                $saleItem = $lockedSaleItems->get($saleItemId);
                /** @var Product $product */
                $product = $lockedProducts->get($saleItem->product_id);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ["المنتج المرتبط بالبند رقم #{$saleItemId} غير موجود."],
                    ]);
                }

                // Calculate cumulative already returned quantity under exclusive lock
                $alreadyReturned = (float) ReturnItem::where('sale_item_id', $saleItem->id)
                    ->whereHas('saleReturn', fn($q) => $q->where('status', 'approved'))
                    ->sum('quantity');

                $returnableQty = max(0, round((float) $saleItem->quantity - $alreadyReturned, 3));

                if ($returnQty > $returnableQty) {
                    throw ValidationException::withMessages([
                        'items' => [
                            sprintf(
                                'الكمية المراد إرجاعها من المنتج "%s" (%.3f) تتجاوز الكمية المتبقية القابلة للإرجاع (%.3f).',
                                $product->name,
                                $returnQty,
                                $returnableQty
                            ),
                        ],
                    ]);
                }

                // Calculate line total strictly using original sale item price
                $unitPrice = round((float) $saleItem->unit_price, 2);
                $lineTotal = round($unitPrice * $returnQty, 2);
                $totalRefundAmount += $lineTotal;

                $preparedItems[] = [
                    'sale_item' => $saleItem,
                    'product' => $product,
                    'quantity' => $returnQty,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            }

            $totalRefundAmount = round($totalRefundAmount, 2);

            // Generate unique return number
            $returnNumber = $this->generateUniqueReturnNumber();

            // 1. Create SaleReturn record
            $saleReturn = SaleReturn::create([
                'return_number' => $returnNumber,
                'sale_id' => $lockedSale->id,
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'reason' => $reason,
                'total_amount' => $totalRefundAmount,
                'refund_method' => $refundMethod,
                'status' => 'approved',
            ]);

            // 2. Create Return Items and increase stock
            foreach ($preparedItems as $itemData) {
                /** @var SaleItem $saleItem */
                $saleItem = $itemData['sale_item'];
                /** @var Product $product */
                $product = $itemData['product'];
                $returnQty = $itemData['quantity'];

                ReturnItem::create([
                    'return_id' => $saleReturn->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $product->id,
                    'quantity' => $returnQty,
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['total'],
                ]);

                // Increase inventory stock using InventoryService
                $this->inventoryService->recordMovement(
                    product: $product,
                    quantity: $returnQty, // Positive value increases stock (type: 'in')
                    referenceType: 'return',
                    userId: $user->id,
                    notes: "مرتجع مبيعات إشعار #{$saleReturn->return_number} (فاتورة #{$lockedSale->invoice_number})",
                    referenceId: $saleReturn->id
                );
            }

            // 3. Record Activity Log
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'return_created',
                'subject_type' => SaleReturn::class,
                'subject_id' => $saleReturn->id,
                'new_values' => [
                    'return_number' => $saleReturn->return_number,
                    'sale_id' => $lockedSale->id,
                    'invoice_number' => $lockedSale->invoice_number,
                    'shift_id' => $saleReturn->shift_id,
                    'total_amount' => $saleReturn->total_amount,
                    'refund_method' => $saleReturn->refund_method,
                    'status' => $saleReturn->status,
                    'items_count' => count($preparedItems),
                ],
                'description' => "تم إنشاء إشعار مرتجع مبيعات رقم #{$saleReturn->return_number} بقيمة {$saleReturn->total_amount} جنيه مصري لفاتورة #{$lockedSale->invoice_number}.",
            ]);

            return $saleReturn->load(['items.product', 'sale', 'shift', 'user']);
        });
    }

    /**
     * Safely cancel an approved return with inventory reversal exactly once.
     *
     * @param SaleReturn|int $return
     * @param User $user
     * @param string|null $reason
     * @return SaleReturn
     * @throws ValidationException
     */
    public function cancelReturn(SaleReturn|int $return, User $user, ?string $reason = null): SaleReturn
    {
        $returnId = $return instanceof SaleReturn ? $return->id : $return;

        return DB::transaction(function () use ($returnId, $user, $reason) {
            /** @var SaleReturn $lockedReturn */
            $lockedReturn = SaleReturn::where('id', $returnId)
                ->with(['items.product', 'sale'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReturn->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => ['إشعار المرتجع ملغى بالفعل، ولا يمكن تكرار إلغائه أو تكرار خصم المخزون.'],
                ]);
            }

            if ($lockedReturn->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => ['لا يمكن إلغاء إشعار مرتجع غير معتمد.'],
                ]);
            }

            // Lock products in ascending ID order to prevent deadlocks
            $productIds = $lockedReturn->items->pluck('product_id')->unique()->sort()->values()->all();
            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Reverse stock for each returned item (decrease stock)
            foreach ($lockedReturn->items as $item) {
                $product = $lockedProducts->get($item->product_id);
                if ($product) {
                    $this->inventoryService->recordMovement(
                        product: $product,
                        quantity: -(float) $item->quantity, // Negative decreases stock (reverses return)
                        referenceType: 'return',
                        userId: $user->id,
                        notes: "إلغاء إشعار مرتجع #{$lockedReturn->return_number}" . ($reason ? " (السبب: {$reason})" : ''),
                        referenceId: $lockedReturn->id
                    );
                }
            }

            // Update status and audit fields
            $lockedReturn->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
            ]);

            // Audit log
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'return_cancelled',
                'subject_type' => SaleReturn::class,
                'subject_id' => $lockedReturn->id,
                'old_values' => ['status' => 'approved'],
                'new_values' => [
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_by' => $user->id,
                ],
                'description' => "تم إلغاء إشعار المرتجع رقم #{$lockedReturn->return_number} وإعادة خصم الأصناف من المخزون." . ($reason ? " السبب: {$reason}" : ''),
            ]);

            return $lockedReturn->fresh(['items.product', 'sale', 'shift', 'user', 'cancelledByUser']);
        });
    }

    /**
     * Get summary metrics for returns dashboard/index.
     */
    public function getReturnMetrics(): array
    {
        return [
            'total_count' => SaleReturn::count(),
            'approved_count' => SaleReturn::where('status', 'approved')->count(),
            'cancelled_count' => SaleReturn::where('status', 'cancelled')->count(),
            'total_refunded' => (float) (SaleReturn::where('status', 'approved')->sum('total_amount') ?? 0),
            'cash_refunded' => (float) (SaleReturn::where('status', 'approved')->where('refund_method', 'cash')->sum('total_amount') ?? 0),
            'electronic_refunded' => (float) (SaleReturn::where('status', 'approved')->whereIn('refund_method', ['vodafone_cash', 'instapay'])->sum('total_amount') ?? 0),
        ];
    }
}

