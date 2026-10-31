<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'status',
        'sort_order',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function coverProduct(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Product::class)->ofMany(
            ['id' => 'max'],
            fn ($query) => $query->where('status', 'published')
        );
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    /**
     * Prefer a published product photo from this category or its children.
     * Category SVG placeholders are skipped so tiles do not show duplicate labels.
     */
    public function coverImageUrl(): ?string
    {
        $product = $this->resolveCoverProduct();
        $url = $product?->primaryImageUrl();

        if ($url) {
            return $url;
        }

        if ($this->image && ! str_ends_with(strtolower($this->image), '.svg')) {
            return asset($this->image);
        }

        return null;
    }

    public function resolveCoverProduct(): ?Product
    {
        if ($this->relationLoaded('coverProduct') && $this->coverProduct) {
            return $this->coverProduct;
        }

        if ($this->relationLoaded('children')) {
            foreach ($this->children as $child) {
                if ($child->relationLoaded('coverProduct') && $child->coverProduct) {
                    return $child->coverProduct;
                }
            }
        }

        $ids = collect([$this->id]);

        if ($this->relationLoaded('children')) {
            $ids = $ids->merge($this->children->pluck('id'));
        } else {
            $ids = $ids->merge($this->children()->pluck('id'));
        }

        return Product::query()
            ->whereIn('category_id', $ids->unique()->filter()->all())
            ->where('status', 'published')
            ->with('images')
            ->latest('id')
            ->first();
    }
}
