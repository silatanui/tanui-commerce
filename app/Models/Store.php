<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'currency',
        'status',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(StoreSetting::class);
    }

    public function banners(): HasMany
    {
        return $this->hasMany(StoreBanner::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        $settings = $this->relationLoaded('settings') ? $this->settings : $this->settings()->get();
        $row = $settings->firstWhere('setting_key', $key);

        if (! $row) {
            return $default;
        }

        $value = $row->setting_value;

        if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }

    public function theme(): string
    {
        return $this->slug;
    }

    public function logoUrl(): string
    {
        return $this->logo ? asset($this->logo) : asset('demo-assets/stores/'.$this->slug.'.svg');
    }
}
