<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'store_id',
        'code',
        'discount_type',
        'discount_value',
        'minimum_amount',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isUsable(float $subtotal): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $subtotal >= (float) $this->minimum_amount;
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->isUsable($subtotal)) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return round($subtotal * ((float) $this->discount_value / 100), 2);
        }

        return round(min($subtotal, (float) $this->discount_value), 2);
    }

    public function label(): string
    {
        if ($this->discount_type === 'percentage') {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'% off';
        }

        return money($this->discount_value, $this->store?->currency).' off';
    }
}
