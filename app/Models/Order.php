<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'order_number',
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'currency',
        'status',
        'payment_status',
        'is_demo',
        'customer_details',
        'shipping_details',
        'coupon_code',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'is_demo' => 'boolean',
            'customer_details' => 'array',
            'shipping_details' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            $order->is_demo = true;
            $order->payment_status = 'not_processed';
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
