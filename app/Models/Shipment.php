<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'shipping_method',
        'shipping_cost',
        'status',
        'tracking_reference',
    ];

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
