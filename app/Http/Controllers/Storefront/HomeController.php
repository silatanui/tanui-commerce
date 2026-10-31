<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreBanner;
use App\Support\StoreLookbook;
use App\Support\StorePromos;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $store = current_store();

        $products = Product::query()
            ->forStore($store)
            ->published()
            ->withRatings()
            ->with(['brand', 'images', 'category', 'variants']);

        $bestsellerIds = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as units'))
            ->whereNotNull('product_id')
            ->whereHas('order', fn ($query) => $query->where('store_id', $store->id)->where('status', '!=', 'cancelled'))
            ->groupBy('product_id')
            ->orderByDesc('units')
            ->limit(8)
            ->pluck('product_id');

        $bestsellers = $bestsellerIds->isEmpty()
            ? (clone $products)->where('is_featured', true)->limit(8)->get()
            : Product::query()->forStore($store)->published()->withRatings()->with(['brand', 'images', 'category', 'variants'])->whereIn('id', $bestsellerIds)->get()
                ->sortBy(fn ($product) => $bestsellerIds->search($product->id))
                ->values();

        $promos = StorePromos::forStore($store);
        $lookbook = StoreLookbook::forStore($store);
        $lookSpotlight = collect($lookbook['looks'] ?? [])->first(
            fn (array $look) => ($look['layout'] ?? '') === 'split' && count($look['products'] ?? []) >= 2
        ) ?? ($lookbook['looks'][1] ?? null);

        return view('storefront.home', [
            'banners' => StoreBanner::query()
                ->where('store_id', $store->id)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get(),
            'featured' => (clone $products)->where('is_featured', true)->limit(8)->get(),
            'arrivals' => (clone $products)->latest()->limit(8)->get(),
            'bestsellers' => $bestsellers,
            'offers' => (clone $products)->whereNotNull('compare_at_price')->limit(4)->get(),
            'widePromos' => $promos['wide'],
            'stackPromos' => $promos['stack'],
            'lookSpotlight' => $lookSpotlight,
        ]);
    }
}
