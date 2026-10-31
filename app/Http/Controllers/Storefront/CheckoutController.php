<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\CustomerAddress;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\StoreConfigurationService;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $carts,
        private CheckoutService $checkout,
        private StoreConfigurationService $stores,
    ) {}

    public function show()
    {
        $store = current_store();
        $quote = $this->carts->quote($store);

        if ($quote['cart']->items->isEmpty()) {
            return redirect()->route('store.cart', $store->slug)->with('status', 'Add a product before checkout.');
        }

        $address = auth()->user()
            ? CustomerAddress::query()->where('user_id', auth()->id())->where('is_default', true)->first()
                ?? CustomerAddress::query()->where('user_id', auth()->id())->latest()->first()
            : null;

        return view('storefront.checkout', [
            'quote' => $quote,
            'shippingMethods' => $this->stores->shippingMethods($store),
            'address' => $address,
        ]);
    }

    public function place(CheckoutRequest $request)
    {
        $store = current_store();
        $order = $this->checkout->place($store, $request->validated(), $request->user());

        return redirect()
            ->route('store.order', [$store->slug, $order->order_number])
            ->with('status', demo_confirmation());
    }
}
