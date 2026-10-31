<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Store;
use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStore
{
    public function __construct(private CartService $carts) {}

    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('store');

        $store = Store::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $store->load('settings');
        app()->instance('currentStore', $store);

        view()->share('store', $store);
        view()->share('cartCount', $this->carts->count($store));
        view()->share('navCategories', Category::query()
            ->where('store_id', $store->id)
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->with(['children' => fn ($query) => $query->where('status', 'active')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get());

        return $next($request);
    }
}
