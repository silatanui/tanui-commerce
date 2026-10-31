<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'attributes',
        'price',
        'stock_quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function label(): string
    {
        $attributes = $this->getAttribute('attributes');

        if (! is_array($attributes) || $attributes === []) {
            return 'Standard';
        }

        return collect($attributes)->map(fn ($value, $name) => $name.': '.$value)->implode(' · ');
    }

    public function sellingPrice(): string
    {
        return money_amount($this->price ?? $this->product?->price ?? 0);
    }
}
