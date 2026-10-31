<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing($request, null);
    }

    public function category(Request $request, string $store, string $category): View
    {
        $record = Category::query()
            ->where('store_id', current_store()->id)
            ->where('slug', $category)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->listing($request, $record);
    }

    public function search(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:120']]);

        return $this->listing($request, null, true);
    }

    private function listing(Request $request, ?Category $category, bool $search = false): View
    {
        $store = current_store();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'array'],
            'brand.*' => ['integer'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:40'],
            'size' => ['nullable', 'string', 'max:40'],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc,name'],
            'availability' => ['nullable', 'in:in_stock'],
        ]);

        $query = Product::query()->forStore($store)->published()->with(['brand', 'images', 'category', 'variants']);

        if ($category) {
            $ids = $category->children()->pluck('id')->push($category->id);
            $query->whereIn('category_id', $ids);
        }

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$term}%"));
            });
        }

        if (! empty($filters['brand'])) {
            $query->whereIn('brand_id', $filters['brand']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['color'])) {
            $color = $filters['color'];
            $query->whereHas('variants', fn ($variant) => $variant->where('attributes->Color', $color));
        }

        if (! empty($filters['size'])) {
            $size = $filters['size'];
            $query->whereHas('variants', fn ($variant) => $variant->where('attributes->Size', $size));
        }

        if (($filters['availability'] ?? null) === 'in_stock') {
            $query->whereHas('variants', fn ($variant) => $variant->where('stock_quantity', '>', 0));
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $facetVariants = ProductVariant::query()
            ->whereHas('product', fn ($product) => $product->forStore($store)->published())
            ->get(['attributes']);

        $colors = $facetVariants->map(fn ($variant) => $variant->getAttribute('attributes')['Color'] ?? null)->filter()->unique()->sort()->values();
        $sizes = $facetVariants->map(fn ($variant) => $variant->getAttribute('attributes')['Size'] ?? null)->filter()->unique()->sort()->values();

        return view('storefront.catalog', [
            'products' => $query->paginate(12)->withQueryString(),
            'category' => $category,
            'searching' => $search || ! empty($filters['q']),
            'brands' => Brand::query()->where('store_id', $store->id)->orderBy('name')->get(),
            'colors' => $colors,
            'sizes' => $sizes,
            'filters' => $filters,
        ]);
    }
}
