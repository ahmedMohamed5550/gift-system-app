<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function getProfitAttribute(): float
    {
        return (float) round((($this->unit_price - $this->unit_cost) * $this->quantity), 2);
    }

    public function getReturnedQuantityAttribute(): float
    {
        return (float) ($this->returnItems()
            ->whereHas('saleReturn', fn($q) => $q->where('status', 'approved'))
            ->sum('quantity') ?? 0);
    }

    public function getReturnableQuantityAttribute(): float
    {
        return (float) max(0, round((float) $this->quantity - $this->returned_quantity, 3));
    }
}
