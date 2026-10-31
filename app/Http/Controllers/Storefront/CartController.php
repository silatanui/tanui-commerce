<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CartService;
use App\Services\StoreConfigurationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $carts,
        private StoreConfigurationService $stores,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $store = current_store();

        if ($request->expectsJson() || $request->boolean('drawer')) {
            return response()->json($this->carts->summaryPayload($store));
        }

        $quote = $this->carts->quote($store);

        return view('storefront.cart', [
            'quote' => $quote,
            'shippingMethods' => $this->stores->shippingMethods($store),
            'coupons' => Coupon::query()->where('store_id', $store->id)->where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $product->name.' added to the demonstration cart.',
                ...$this->carts->summaryPayload($store),
            ]);
        }

        return back()->with('status', $product->name.' was added to the demonstration cart.');
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $this->carts->updateQuantity(current_store(), (int) $data['item_id'], (int) $data['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Cart updated.',
                ...$this->carts->summaryPayload(current_store()),
            ]);
        }

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['item_id' => ['required', 'integer']]);
        $this->carts->remove(current_store(), (int) $data['item_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Item removed from the cart.',
                ...$this->carts->summaryPayload(current_store()),
            ]);
        }

        return back()->with('status', 'Item removed from the cart.');
    }

    public function coupon(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);
        $coupon = $this->carts->applyCoupon(current_store(), $data['code']);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Demonstration code '.$coupon->code.' applied.',
                ...$this->carts->summaryPayload(current_store()),
            ]);
        }

        return back()->with('status', 'Coupon code '.$coupon->code.' applied.');
    }
}
