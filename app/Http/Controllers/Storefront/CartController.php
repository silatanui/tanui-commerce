<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CartService;
use App\Services\StoreConfigurationService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $carts,
        private StoreConfigurationService $stores,
    ) {}

    public function index()
    {
        $store = current_store();
        $quote = $this->carts->quote($store);

        return view('storefront.cart', [
            'quote' => $quote,
            'shippingMethods' => $this->stores->shippingMethods($store),
            'coupons' => Coupon::query()->where('store_id', $store->id)->where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'variant_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $store = current_store();
        $product = Product::query()->forStore($store)->published()->findOrFail($data['product_id']);
        $variant = $product->variants()->where('status', 'active')->findOrFail($data['variant_id']);

        $this->carts->add($store, $product, $variant, (int) $data['quantity']);

        return back()->with('status', $product->name.' was added to the demonstration cart.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $this->carts->updateQuantity(current_store(), (int) $data['item_id'], (int) $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['item_id' => ['required', 'integer']]);
        $this->carts->remove(current_store(), (int) $data['item_id']);

        return back()->with('status', 'Item removed from the cart.');
    }

    public function coupon(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);
        $coupon = $this->carts->applyCoupon(current_store(), $data['code']);

        return back()->with('status', 'Demonstration code '.$coupon->code.' applied.');
    }
}
