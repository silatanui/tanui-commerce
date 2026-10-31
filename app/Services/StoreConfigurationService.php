<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreSetting;

class StoreConfigurationService
{
    public function set(Store $store, string $key, mixed $value): void
    {
        StoreSetting::query()->updateOrCreate(
            ['store_id' => $store->id, 'setting_key' => $key],
            ['setting_value' => is_array($value) ? json_encode($value) : (string) $value]
        );
    }

    public function shippingMethods(Store $store): array
    {
        $methods = config('commerce.shipping_methods', []);
        $override = $store->setting('shipping_costs', []);

        if (! is_array($override)) {
            return $methods;
        }

        foreach ($override as $key => $cost) {
            if (isset($methods[$key]) && is_numeric($cost)) {
                $methods[$key]['cost'] = (float) $cost;
            }
        }

        return $methods;
    }
}
