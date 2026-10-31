<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $store, string $product): View
    {
        $storeModel = current_store();

        $record = Product::query()
            ->forStore($storeModel)
            ->published()
            ->where('slug', $product)
            ->with([
                'brand',
                'category.parent',
                'images',
                'variants',
                'reviews' => fn ($query) => $query->where('status', 'approved')->latest()->with('user'),
            ])
            ->firstOrFail();

        $related = Product::query()
            ->forStore($storeModel)
            ->published()
            ->where('category_id', $record->category_id)
            ->whereKeyNot($record->id)
            ->with(['brand', 'images', 'variants'])
            ->limit(4)
            ->get();

        return view('storefront.product', [
            'product' => $record,
            'related' => $related,
        ]);
    }
}
