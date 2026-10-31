<?php

namespace App\Services;

use App\Models\DemoActivityLog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoResetService
{
    public function reset(): void
    {
        $tables = [
            'order_items',
            'shipments',
            'orders',
            'cart_items',
            'carts',
            'wishlists',
            'inventory_movements',
            'product_reviews',
            'product_images',
            'product_variants',
            'products',
            'categories',
            'brands',
            'coupons',
            'store_banners',
            'store_settings',
            'customer_addresses',
            'demo_activity_logs',
            'demo_sessions',
            'stores',
            'sessions',
            'password_reset_tokens',
            'users',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);

        DemoActivityLog::query()->create([
            'session_id' => session()->getId(),
            'action' => 'demo_reset',
            'description' => 'An administrator restored the original demonstration data.',
        ]);
    }
}
