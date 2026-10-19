<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected ShiftService $shiftService
    ) {}

    /**
     * Generate a unique invoice number.
     */
    public function generateUniqueInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $last = Sale::whereDate('created_at', $today)
                ->where('invoice_number', 'like', 'INV-GF-%')
                ->lockForUpdate()
                ->get(['invoice_number'])
                ->map(fn ($sale) => (int) str_replace('INV-GF-', '', $sale->invoice_number))
                ->max();

            return 'INV-GF-' . (($last ?? 0) + 1);
        });
    }

    /**
     * Complete a sale inside an atomic database transaction.
     *
     * 1. Validate request, active shift, and product availability.
     * 2. Lock relevant stock records (ordered by ID to prevent deadlocks).
     * 3. Recalculate totals on the server (never trust client prices).
     * 4. Create the sale.
     * 5. Create sale items with snapshot unit price and unit cost.
     * 6. Decrease stock and record immutable stock movements.
     * 7. Record activity audit log.
     *
     * @throws ValidationException
     */
    public function createSale(array $data, User $user, ?Shift $shift = null): Sale
    {
        // 1. Shift validation
        if (!$shift) {
            $shift = $this->shiftService->getActiveShift($user);
        }

        if (!$shift || $shift->status !== 'open') {
            throw ValidationException::withMessages([
                'shift' => ['لا يمكن إتمام عملية البيع دون وجود وردية عمل مفتوحة للكاشير. يرجى فتح وردية أولاً.'],
            ]);
        }

        // 2. Validate items structure
        $rawItems = $data['items'] ?? [];
        if (empty($rawItems) || !is_array($rawItems)) {
            throw ValidationException::withMessages([
                'items' => ['يجب إضافة منتج واحد على الأقل لإتمام الفاتورة.'],
            ]);
        }

        // Aggregate quantities per product_id in case the same product is repeated
        $aggregatedItems = [];
        foreach ($rawItems as $index => $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                throw ValidationException::withMessages([
                    'items' => ["بيانات البند رقم " . ($index + 1) . " غير مكتملة."],
                ]);
            }

            $pid = (int) $item['product_id'];
            $qty = (float) $item['quantity'];

            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    'items' => ['يجب أن تكون كمية كل منتج أكبر من الصفر.'],
                ]);
            }

            $aggregatedItems[$pid] = ($aggregatedItems[$pid] ?? 0) + $qty;
        }

        // 3. Payment method validation
        $paymentMethod = $data['payment_method'] ?? 'cash';
        if (!in_array($paymentMethod, ['cash', 'vodafone_cash', 'instapay'], true)) {
            throw ValidationException::withMessages([
                'payment_method' => ['طريقة الدفع المحددة غير صالحة. الطرق المتاحة: نقداً (كاش)، فودافون كاش، انستا باي.'],
            ]);
        }

        // Execute sale inside atomic database transaction with pessimistic locking
        return DB::transaction(function () use ($aggregatedItems, $data, $user, $shift, $paymentMethod) {
            // Sort product IDs in ascending order to prevent deadlocks across concurrent requests
            $productIds = array_keys($aggregatedItems);
            sort($productIds);

            // Fetch products with exclusive row lock
            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Verify existence, active status, and stock availability under the lock
            foreach ($aggregatedItems as $productId => $quantity) {
                $product = $lockedProducts->get($productId);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ["المنتج رقم #{$productId} غير موجود بالنظام."],
                    ]);
                }

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ["المنتج '{$product->name}' غير متاح للبيع حالياً (معطل)."],
                    ]);
                }

                $availableStock = (float) $product->stock_quantity;
                if ($availableStock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => [
                            sprintf(
                                'الكمية المطلوبة من المنتج "%s" (%.3f) تتجاوز الرصيد المتوفر بالمخزون حالياً (%.3f).',
                                $product->name,
                                $quantity,
                                $availableStock
                            ),
                        ],
                    ]);
                }
            }

            // Recalculate line totals and subtotal on server - strictly ignoring browser prices
            $subtotal = 0.0;
            $preparedItems = [];

            foreach ($aggregatedItems as $productId => $quantity) {
                /** @var Product $product */
                $product = $lockedProducts->get($productId);

                $unitPrice = round((float) $product->selling_price, 2);
                $unitCost = round((float) $product->purchase_price, 2);
                $lineTotal = round($unitPrice * $quantity, 2);

                $subtotal += $lineTotal;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);

            // Discount validation
            $rawDiscount = isset($data['discount']) ? (float) $data['discount'] : 0.0;
            if ($rawDiscount < 0) {
                throw ValidationException::withMessages([
                    'discount' => ['قيمة الخصم لا يمكن أن تكون سالبة.'],
                ]);
            }

            if ($rawDiscount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => ['قيمة الخصم لا يمكن أن تتجاوز المجموع الفرعي للفاتورة (' . number_format($subtotal, 2) . ' جنيه مصري).'],
                ]);
            }

            $discount = round($rawDiscount, 2);
            $total = round($subtotal - $discount, 2);

            // Generate unique invoice number
            $invoiceNumber = $this->generateUniqueInvoiceNumber();

            // 1. Create Sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
            ]);

            // 2. Create Sale Items & Decrease Stock
            foreach ($preparedItems as $itemData) {
                /** @var Product $product */
                $product = $itemData['product'];
                $quantity = $itemData['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $itemData['unit_price'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total' => $itemData['total'],
                ]);

                // Record inventory movement (decrease stock)
                $this->inventoryService->recordMovement(
                    product: $product,
                    quantity: -$quantity,
                    referenceType: 'sale',
                    userId: $user->id,
                    notes: "فاتورة مبيعات رقم #{$sale->invoice_number}",
                    referenceId: $sale->id
                );
            }

            // 3. Audit Activity Log
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'sale_created',
                'subject_type' => Sale::class,
                'subject_id' => $sale->id,
                'new_values' => [
                    'invoice_number' => $sale->invoice_number,
                    'shift_id' => $sale->shift_id,
                    'subtotal' => $sale->subtotal,
                    'discount' => $sale->discount,
                    'total' => $sale->total,
                    'payment_method' => $sale->payment_method,
                    'items_count' => count($preparedItems),
                ],
                'description' => "تم إنشاء فاتورة مبيعات جديدة رقم #{$sale->invoice_number} بقيمة {$sale->total} جنيه مصري.",
            ]);

            return $sale->load(['items.product', 'shift', 'user']);
        });
    }

    /**
     * Safely cancel a completed sale with stock reversal exactly once.
     *
     * @throws ValidationException
     */
    public function cancelSale(Sale|int $sale, User $user, ?string $reason = null): Sale
    {
        $saleId = $sale instanceof Sale ? $sale->id : $sale;

        return DB::transaction(function () use ($saleId, $user, $reason) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::where('id', $saleId)
                ->with(['items.product'])
                ->lockForUpdate()
                ->firstOrFail();

            // Prevent duplicate cancellation / double stock reversal
            if ($lockedSale->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => ['هذه الفاتورة ملغاة بالفعل، ولا يمكن تكرار إلغائها أو تكرار استرجاع المخزون.'],
                ]);
            }

            // Order product IDs ascending to prevent deadlocks
            $productIds = $lockedSale->items->pluck('product_id')->unique()->sort()->values()->all();
            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Reverse stock for each sold item
            foreach ($lockedSale->items as $item) {
                $product = $lockedProducts->get($item->product_id);
                if ($product) {
                    $this->inventoryService->recordMovement(
                        product: $product,
                        quantity: (float) $item->quantity, // Positive increases stock back
                        referenceType: 'sale',
                        userId: $user->id,
                        notes: "إلغاء فاتورة مبيعات #{$lockedSale->invoice_number}" . ($reason ? " (السبب: {$reason})" : ''),
                        referenceId: $lockedSale->id
                    );
                }
            }

            // Update sale status
            $lockedSale->update([
                'status' => 'cancelled',
            ]);

            // Audit log
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'sale_cancelled',
                'subject_type' => Sale::class,
                'subject_id' => $lockedSale->id,
                'old_values' => ['status' => 'completed'],
                'new_values' => [
                    'status' => 'cancelled',
                    'reason' => $reason,
                    'cancelled_by' => $user->id,
                ],
                'description' => "تم إلغاء فاتورة المبيعات رقم #{$lockedSale->invoice_number} وإعادة كافة الأصناف للمخزون." . ($reason ? " السبب: {$reason}" : ''),
            ]);

            return $lockedSale->fresh(['items.product', 'user', 'shift']);
        });
    }
}

