<?php

use App\Models\Store;

function current_store(): ?Store
{
    if (! app()->bound('currentStore')) {
        return null;
    }

    $store = app('currentStore');

    return $store instanceof Store ? $store : null;
}

function admin_store(): ?Store
{
    $id = session('admin_store_id');
    $store = $id ? Store::query()->find($id) : null;

    if (! $store) {
        $store = Store::query()->orderBy('name')->first();

        if ($store) {
            session(['admin_store_id' => $store->id]);
        }
    }

    return $store;
}

function money(mixed $amount, ?string $currency = null): string
{
    $currency ??= current_store()?->currency ?? 'KES';
    $symbols = config('commerce.currencies', []);
    $symbol = $symbols[$currency] ?? $currency;

    return $symbol.' '.number_format((float) $amount, 2);
}

function money_amount(mixed $amount): string
{
    return number_format(round((float) $amount, 2), 2, '.', '');
}

function safe_hex(?string $color, string $fallback): string
{
    return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : $fallback;
}

function demo_notice(): string
{
    return (string) config('demo.notice');
}

function demo_confirmation(): string
{
    return (string) config('demo.confirmation');
}
