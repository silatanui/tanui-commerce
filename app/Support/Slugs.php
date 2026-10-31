<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Slugs
{
    public static function unique(string $class, string $name, int $storeId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while (self::exists($class, $storeId, 'slug', $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public static function sku(string $class, string $prefix, int $storeId, ?int $ignoreId = null): string
    {
        $base = strtoupper(Str::slug($prefix, '')) ?: 'SKU';
        $sku = $base.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        while (self::exists($class, $storeId, 'sku', $sku, $ignoreId)) {
            $sku = $base.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        return $sku;
    }

    private static function exists(string $class, int $storeId, string $column, string $value, ?int $ignoreId): bool
    {
        /** @var Model $class */
        $query = $class::query()->where('store_id', $storeId)->where($column, $value);

        if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            $query->withTrashed();
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
