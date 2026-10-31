<?php

return [
    'low_stock_threshold' => 5,
    'currencies' => [
        'KES' => 'KSh',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
    ],
    'shipping_methods' => [
        'standard' => [
            'label' => 'Standard delivery',
            'cost' => 350,
            'detail' => '3–5 demonstration days',
        ],
        'express' => [
            'label' => 'Express delivery',
            'cost' => 750,
            'detail' => '1–2 demonstration days',
        ],
        'pickup' => [
            'label' => 'Collection point',
            'cost' => 0,
            'detail' => 'Ready for collection in the demonstration',
        ],
    ],
    'order_statuses' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ],
];
