<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'price',
        'compare_at_price',
        'product_type',
        'specifications',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'specifications' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeWithRatings(Builder $query): Builder
    {
        return $query
            ->withAvg(['reviews as approved_rating_avg' => fn ($builder) => $builder->where('status', 'approved')], 'rating')
            ->withCount(['reviews as approved_reviews_count' => fn ($builder) => $builder->where('status', 'approved')]);
    }

    public function scopeForStore(Builder $query, Store|int $store): Builder
    {
        $id = $store instanceof Store ? $store->id : $store;

        return $query->where('store_id', $id);
    }

    public function primaryImageUrl(): string
    {
        $image = $this->relationLoaded('images')
            ? ($this->images->firstWhere('is_primary', true) ?? $this->images->first())
            : ($this->images()->where('is_primary', true)->first() ?? $this->images()->first());

        return $image ? $image->url() : asset('demo-assets/placeholder.svg');
    }

    public function badge(): ?string
    {
        if ($this->compare_at_price && (float) $this->compare_at_price > (float) $this->price) {
            return 'Sale';
        }

        if ($this->is_featured) {
            return 'Featured';
        }

        if ($this->created_at && $this->created_at->gt(now()->subDays(21))) {
            return 'New';
        }

        return null;
    }

    public function unitPrice(?ProductVariant $variant = null): string
    {
        if ($variant && $variant->price !== null) {
            return money_amount($variant->price);
        }

        return money_amount($this->price);
    }

    public function availableStock(): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->where('status', 'active')->sum('stock_quantity');
        }

        return (int) $this->variants()->where('status', 'active')->sum('stock_quantity');
    }

    public function averageRating(): ?float
    {
        if (array_key_exists('approved_rating_avg', $this->attributes) && $this->approved_rating_avg !== null) {
            return round((float) $this->approved_rating_avg, 1);
        }

        $ratings = $this->relationLoaded('reviews')
            ? $this->reviews->where('status', 'approved')->pluck('rating')
            : $this->reviews()->where('status', 'approved')->pluck('rating');

        if ($ratings->isEmpty()) {
            return null;
        }

        return round($ratings->avg(), 1);
    }

    public function approvedReviewsCount(): int
    {
        if (array_key_exists('approved_reviews_count', $this->attributes)) {
            return (int) $this->approved_reviews_count;
        }

        if ($this->relationLoaded('reviews')) {
            return $this->reviews->where('status', 'approved')->count();
        }

        return (int) $this->reviews()->where('status', 'approved')->count();
    }
}
