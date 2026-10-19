<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Record a stock movement with pessimistic row locking and transactional atomicity.
     *
     * @param int|Product $product
     * @param float $quantity Positive values increase stock; negative values decrease stock.
     * @param string $referenceType Originating operation type: 'adjustment', 'purchase', 'sale', 'return'
     * @param int $userId Responsible user ID
     * @param string|null $notes Movement reason / details
     * @param int|null $referenceId Originating record ID (e.g. sale_id, return_id)
     * @return StockMovement
     * @throws ValidationException
     */
    public function recordMovement(
        int|Product $product,
        float $quantity,
        string $referenceType,
        int $userId,
        ?string $notes = null,
        ?int $referenceId = null
    ): StockMovement {
        if ($quantity == 0) {
            throw ValidationException::withMessages([
                'quantity' => ['كمية حركة المخزون لا يمكن أن تكون صفراً.'],
            ]);
        }

        return DB::transaction(function () use ($product, $quantity, $referenceType, $userId, $notes, $referenceId) {
            $productId = $product instanceof Product ? $product->id : $product;

            // Acquire exclusive row lock on the product to prevent race conditions & concurrency issues
            /** @var Product $lockedProduct */
            $lockedProduct = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $currentStock = (float) $lockedProduct->stock_quantity;
            $newStock = $currentStock + $quantity;

            // Validate against negative inventory balance
            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        sprintf(
                            'الكمية المراد خصمها (%.3f) تتجاوز الرصيد المتوفر حالياً بالمخزون (%.3f) للمنتج "%s".',
                            abs($quantity),
                            $currentStock,
                            $lockedProduct->name
                        ),
                    ],
                ]);
            }

            // Update the locked product's stock quantity
            $lockedProduct->update([
                'stock_quantity' => $newStock,
            ]);

            // Consistent quantity convention:
            // Positive values increase stock (type: in)
            // Negative values decrease stock (type: out)
            $type = $quantity > 0 ? 'in' : 'out';
            $signedQuantity = $quantity > 0 ? abs($quantity) : -abs($quantity);

            // Record immutable movement
            return StockMovement::create([
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $signedQuantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Perform a manual stock adjustment for a product.
     * Supports relative adjustments (add / subtract) and absolute counts (physical inventory count).
     *
     * @param int|Product $product
     * @param string $adjustmentMode 'relative' (add/subtract) or 'absolute' (physical count)
     * @param float $value Quantity to adjust by or the actual counted stock
     * @param string $direction 'add' or 'subtract' (used in relative mode)
     * @param string $reason Explanation / audit reason
     * @param int $userId Responsible user ID
     * @return StockMovement
     * @throws ValidationException
     */
    public function adjustStock(
        int|Product $product,
        string $adjustmentMode,
        float $value,
        string $direction,
        string $reason,
        int $userId
    ): StockMovement {
        $productId = $product instanceof Product ? $product->id : $product;

        return DB::transaction(function () use ($productId, $adjustmentMode, $value, $direction, $reason, $userId) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
            $currentStock = (float) $lockedProduct->stock_quantity;

            if ($adjustmentMode === 'absolute') {
                if ($value < 0) {
                    throw ValidationException::withMessages([
                        'counted_quantity' => ['الرصيد الفعلي للمنتج لا يمكن أن يكون سالباً.'],
                    ]);
                }

                $delta = $value - $currentStock;
                if ($delta == 0) {
                    throw ValidationException::withMessages([
                        'counted_quantity' => ['الرصيد المدخل مطابق تماماً للرصيد المسجل بالنظام، لا توجد تسوية مطلوبة.'],
                    ]);
                }

                $quantityChange = $delta;
            } else {
                if ($value <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => ['يجب أن تكون كمية التسوية أكبر من الصفر.'],
                    ]);
                }

                $quantityChange = ($direction === 'subtract') ? -$value : $value;
            }

            $movement = $this->recordMovement(
                product: $lockedProduct,
                quantity: $quantityChange,
                referenceType: 'adjustment',
                userId: $userId,
                notes: $reason
            );

            $newStock = (float) $lockedProduct->fresh()->stock_quantity;

            \App\Services\ActivityLogService::log(
                action: 'stock_adjusted',
                subject: $lockedProduct,
                description: "تسوية مخزنية يدوية للمنتج \"{$lockedProduct->name}\": تعديل الرصيد من {$currentStock} إلى {$newStock} (الفرق: {$quantityChange}، السبب: {$reason}).",
                oldValues: [
                    'stock_quantity' => $currentStock,
                ],
                newValues: [
                    'stock_quantity' => $newStock,
                    'quantity_change' => (float) $quantityChange,
                    'adjustment_mode' => $adjustmentMode,
                    'movement_id' => $movement->id,
                    'reason' => $reason,
                ],
                userId: $userId
            );

            return $movement;
        });
    }

    /**
     * Get summary inventory analytics and KPIs.
     */
    public function getInventoryMetrics(): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_count' => Product::whereColumn('stock_quantity', '<=', 'minimum_stock')
                                       ->where('stock_quantity', '>', 0)
                                       ->count(),
            'out_of_stock_count' => Product::where('stock_quantity', '<=', 0)->count(),
            'total_stock_value' => (float) (Product::selectRaw('SUM(stock_quantity * purchase_price) as total')->value('total') ?? 0),
            'total_selling_value' => (float) (Product::selectRaw('SUM(stock_quantity * selling_price) as total')->value('total') ?? 0),
        ];
    }
}

