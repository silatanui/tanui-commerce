<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function index()
    {
        $items = Wishlist::query()
            ->where('user_id', auth()->id())
            ->whereHas('product', fn ($query) => $query->forStore(current_store()))
            ->with(['product.images', 'product.brand', 'product.variants'])
            ->latest()
            ->get();

        return view('storefront.wishlist', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);
        $product = Product::query()->forStore(current_store())->published()->findOrFail($data['product_id']);

        Wishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return back()->with('status', 'Saved to your demonstration wishlist.');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);

        Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->whereHas('product', fn ($query) => $query->forStore(current_store()))
            ->delete();

        return back()->with('status', 'Removed from the wishlist.');
    }

    public function move(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);
        $store = current_store();
        $product = Product::query()->forStore($store)->published()->with('variants')->findOrFail($data['product_id']);
        $variant = $product->variants->first(fn ($item) => $item->status === 'active' && $item->stock_quantity > 0)
            ?? $product->variants->firstWhere('status', 'active');

        abort_unless($variant, 422, 'This product has no available option.');

        $this->carts->add($store, $product, $variant, 1);

        Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return redirect()->route('store.cart', $store->slug)->with('status', 'Moved to the cart.');
    }
}
