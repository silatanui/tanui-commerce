<?php

return [
    'mode' => (bool) env('DEMO_MODE', true),
    'allow_checkout' => (bool) env('DEMO_ALLOW_CHECKOUT', true),
    'allow_payments' => false,
    'allow_emails' => (bool) env('DEMO_ALLOW_EMAILS', false),
    'notice' => 'DEMONSTRATION STORE: This website is an interactive portfolio project. Products, prices, inventory and transactions are fictional. No real purchases or payments are accepted.',
    'confirmation' => 'Demo order completed successfully. No payment was processed, and no products will be delivered.',
    'reset_phrase' => 'RESET DEMONSTRATION',
];
