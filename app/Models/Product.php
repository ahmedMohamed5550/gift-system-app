<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subcategory_id',
        'name',
        'description',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'minimum_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function getProfitAttribute(): float
    {
        return (float) ($this->selling_price - $this->purchase_price);
    }

    public function getProfitMarginPercentageAttribute(): float
    {
        if ((float) $this->purchase_price > 0) {
            return round((( (float)$this->selling_price - (float)$this->purchase_price ) / (float)$this->purchase_price) * 100, 1);
        }

        return 0.0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return (float) $this->stock_quantity <= (float) $this->minimum_stock;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return (float) $this->stock_quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        }

        if ($this->is_low_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusBadgeAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'badge-danger',
            'low_stock' => 'badge-warning',
            default => 'badge-success',
        };
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'نفذ من المخزون',
            'low_stock' => 'مخزون منخفض',
            default => 'متوفر',
        };
    }

    public function getTotalStockValueAttribute(): float
    {
        return (float) ($this->stock_quantity * $this->purchase_price);
    }
}
