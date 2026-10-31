<?php

namespace App\Services;

use App\Models\DemoActivityLog;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private CartService $carts,
        private InventoryService $inventory,
        private StoreConfigurationService $stores,
    ) {}

    public function place(Store $store, array $data, ?User $user = null): Order
    {
        if (! config('demo.allow_checkout')) {
            abort(403, 'Demonstration checkout is disabled.');
        }

        $blocked = ['card_number', 'card', 'cvv', 'cvc', 'pan', 'payment_token', 'payment_method'];

        foreach ($blocked as $field) {
            if (request()->filled($field)) {
                throw ValidationException::withMessages([
                    'payment' => 'Payment cards are not accepted on this demonstration.',
                ]);
            }
        }

        return DB::transaction(function () use ($store, $data, $user) {
            $cart = $this->carts->refreshPrices($this->carts->current($store, $user));

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $subtotal = 0.0;
            $lines = [];

            foreach ($cart->items as $item) {
                $product = $item->product;
                $variant = $item->variant;

                if (! $product || ! $variant || $product->store_id !== $store->id || $variant->product_id !== $product->id) {
                    throw ValidationException::withMessages([
                        'cart' => 'A cart item does not belong to this store.',
                    ]);
                }

                if ($variant->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => $product->name.' does not have enough demonstration stock.',
                    ]);
                }

                $unit = (float) $product->unitPrice($variant);
                $lineTotal = round($unit * $item->quantity, 2);
                $subtotal += $lineTotal;
                $lines[] = [$item, $product, $variant, $unit, $lineTotal];
            }

            $subtotal = round($subtotal, 2);
            $coupon = $this->carts->couponFromSession($store, $subtotal);
            $discount = $coupon ? $coupon->discountFor($subtotal) : 0.0;
            $methods = $this->stores->shippingMethods($store);
            $methodKey = $data['shipping_method'] ?? '';

            if (! isset($methods[$methodKey])) {
                throw ValidationException::withMessages([
                    'shipping_method' => 'Choose a delivery option.',
                ]);
            }

            $shipping = round((float) $methods[$methodKey]['cost'], 2);
            $total = round(max(0, $subtotal - $discount + $shipping), 2);

            $order = Order::query()->create([
                'store_id' => $store->id,
                'user_id' => $user?->id,
                'order_number' => $this->orderNumber($store),
                'subtotal' => money_amount($subtotal),
                'discount' => money_amount($discount),
                'shipping_cost' => money_amount($shipping),
                'total' => money_amount($total),
                'currency' => $store->currency,
                'status' => 'confirmed',
                'payment_status' => 'not_processed',
                'is_demo' => true,
                'coupon_code' => $coupon?->code,
                'customer_details' => [
                    'full_name' => $data['full_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ],
                'shipping_details' => [
                    'address_line' => $data['address_line'],
                    'city' => $data['city'],
                    'postal_code' => $data['postal_code'] ?? '',
                    'country' => $data['country'],
                    'method' => $methodKey,
                    'method_label' => $methods[$methodKey]['label'],
                ],
            ]);

            foreach ($lines as [$item, $product, $variant, $unit, $lineTotal]) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'sku' => $variant->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => money_amount($unit),
                    'subtotal' => money_amount($lineTotal),
                    'attributes' => $variant->getAttribute('attributes') ?? [],
                ]);

                $this->inventory->change(
                    $store,
                    $variant,
                    -1 * (int) $item->quantity,
                    'sale',
                    $order->order_number,
                    'Demonstration order'
                );
            }

            $order->shipment()->create([
                'shipping_method' => $methods[$methodKey]['label'],
                'shipping_cost' => money_amount($shipping),
                'status' => 'pending',
                'tracking_reference' => 'DEMO-'.$order->order_number,
            ]);

            $cart->update(['status' => 'converted']);
            session()->forget('coupon.'.$store->id);

            $known = session('demo_order_numbers', []);
            $known[] = $order->order_number;
            session(['demo_order_numbers' => $known]);

            DemoActivityLog::query()->create([
                'session_id' => session()->getId(),
                'store_id' => $store->id,
                'action' => 'demo_checkout',
                'description' => 'Demonstration order '.$order->order_number.' created. No payment was processed.',
            ]);

            return $order->load(['items', 'shipment', 'store']);
        });
    }

    private function orderNumber(Store $store): string
    {
        do {
            $number = 'TC-'.strtoupper(substr($store->slug, 0, 3)).'-'.now()->format('y').'-'.random_int(10000, 99999);
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
