<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        // Enforce audit-trail immutability: prevent deletion of stock movements
        static::deleting(function ($movement) {
            throw new RuntimeException('لا يمكن حذف سجلات حركات المخزون حفاظاً على دقة السجل التاريخي والمحاسبي.');
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes ---

    public function scopeIn(Builder $query): Builder
    {
        return $query->where('type', 'in');
    }

    public function scopeOut(Builder $query): Builder
    {
        return $query->where('type', 'out');
    }

    public function scopeReferenceType(Builder $query, string $refType): Builder
    {
        return $query->where('reference_type', $refType);
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    // --- Accessors & Helpers ---

    public function getIsIncreaseAttribute(): bool
    {
        return $this->type === 'in';
    }

    public function getIsDecreaseAttribute(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Consistent quantity convention: positive values increase stock, negative values decrease stock.
     */
    public function getSignedQuantityAttribute(): float
    {
        $qty = (float) $this->quantity;
        if ($this->type === 'out' && $qty > 0) {
            return -$qty;
        }

        return $qty;
    }

    public function getFormattedQuantityAttribute(): string
    {
        $signed = $this->signed_quantity;
        $sign = $signed > 0 ? '+' : '';

        return $sign . number_format($signed, 3);
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === 'in' ? 'badge-success' : 'badge-danger';
    }

    public function getTypeDirectionArabicAttribute(): string
    {
        return $this->type === 'in' ? 'وارد (+)' : 'صادر (-)';
    }

    public function getReferenceTypeEnumAttribute(): ?StockMovementType
    {
        return StockMovementType::fromReference($this->reference_type);
    }

    public function getReferenceTypeLabelAttribute(): string
    {
        if ($enum = $this->reference_type_enum) {
            return $enum->label();
        }

        return match ($this->reference_type) {
            'adjustment' => 'تسوية جردية',
            'purchase' => 'شراء / توريد',
            'sale' => 'فاتورة بيع',
            'return' => 'مرتجع مبيعات',
            default => $this->reference_type ?: 'حركة مخزنية',
        };
    }

    public function getReferenceTypeBadgeClassAttribute(): string
    {
        if ($enum = $this->reference_type_enum) {
            return $enum->badgeClass();
        }

        return match ($this->reference_type) {
            'adjustment' => 'badge-primary',
            'purchase' => 'badge-success',
            'sale' => 'badge-info',
            'return' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    public function getFullDescriptionArabicAttribute(): string
    {
        $typeStr = $this->reference_type_label;
        $dirStr = $this->type === 'in' ? 'إضافة' : 'خصم';

        if ($this->reference_type === 'adjustment') {
            return "تسوية جردية ({$dirStr})";
        }

        return $typeStr;
    }
}
