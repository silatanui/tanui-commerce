<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function find(Store $store, ?User $user = null): ?Cart
    {
        $user ??= Auth::user();

        $query = Cart::query()
            ->where('store_id', $store->id)
            ->where('status', 'open');

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('session_id', session()->getId())->whereNull('user_id');
        }

        return $query->latest('id')->first();
    }

    public function current(Store $store, ?User $user = null): Cart
    {
        $user ??= Auth::user();
        $cart = $this->find($store, $user);

        if (! $cart) {
            $cart = Cart::query()->create([
                'store_id' => $store->id,
                'user_id' => $user?->id,
                'session_id' => session()->getId(),
                'status' => 'open',
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $cart->load(['items.product.images', 'items.product.brand', 'items.variant']);
    }

    public function count(Store $store): int
    {
        $cart = $this->find($store);

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    public function add(Store $store, Product $product, ProductVariant $variant, int $quantity): Cart
    {
        $this->guardLine($store, $product, $variant, $quantity);
        $cart = $this->current($store);
        $existing = $cart->items()->where('variant_id', $variant->id)->first();
        $newQuantity = ($existing->quantity ?? 0) + $quantity;
        $this->guardStock($variant, $newQuantity);

        $payload = [
            'quantity' => $newQuantity,
            'unit_price' => $product->unitPrice($variant),
        ];

        if ($existing) {
            $existing->update($payload);
        } else {
            $cart->items()->create($payload + [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'quantity' => $quantity,
            ]);
        }

        return $this->current($store);
    }

    public function updateQuantity(Store $store, int $itemId, int $quantity): void
    {
        $cart = $this->current($store);
        $item = $cart->items()->whereKey($itemId)->firstOrFail();
        $variant = $item->variant()->firstOrFail();
        $product = $item->product()->firstOrFail();
        $this->guardLine($store, $product, $variant, $quantity);
        $this->guardStock($variant, $quantity);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $product->unitPrice($variant),
        ]);
    }

    public function remove(Store $store, int $itemId): void
    {
        $this->find($store)?->items()->whereKey($itemId)->delete();
    }

    public function refreshPrices(Cart $cart): Cart
    {
        $cart->loadMissing(['items.product', 'items.variant']);

        foreach ($cart->items as $item) {
            if (! $item->product || ! $item->variant || $item->product->store_id !== $cart->store_id) {
                $item->delete();

                continue;
            }

            $item->update([
                'unit_price' => $item->product->unitPrice($item->variant),
            ]);
        }

        return $cart->fresh(['items.product.images', 'items.product.brand', 'items.variant']) ?? $cart;
    }

    public function quote(Store $store, ?Cart $cart = null): array
    {
        $cart ??= $this->current($store);
        $cart = $this->refreshPrices($cart);
        $subtotal = round($cart->items->sum(fn ($item) => (float) $item->unit_price * $item->quantity), 2);
        $coupon = $this->couponFromSession($store, $subtotal);
        $discount = $coupon ? $coupon->discountFor($subtotal) : 0.0;

        return [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon' => $discount > 0 ? $coupon : ($coupon && $coupon->isUsable($subtotal) ? $coupon : null),
        ];
    }

    /**
     * JSON payload for the storefront cart drawer / AJAX responses.
     *
     * @return array<string, mixed>
     */
    public function summaryPayload(Store $store): array
    {
        $quote = $this->quote($store);
        $cart = $quote['cart'];

        $lines = $cart->items->map(function ($item) use ($store) {
            $product = $item->product;

            return [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'name' => $product?->name ?? 'Item',
                'url' => $product ? route('store.product', [$store->slug, $product->slug]) : null,
                'quantity' => (int) $item->quantity,
                'unit_label' => money($item->unit_price, $store->currency),
                'line_label' => money((float) $item->unit_price * $item->quantity, $store->currency),
                'image' => $product?->primaryImageUrl(),
                'max' => min(20, max(1, (int) ($item->variant?->stock_quantity ?? 20))),
            ];
        })->values()->all();

        return [
            'ok' => true,
            'count' => (int) $cart->items->sum('quantity'),
            'subtotal_label' => money($quote['subtotal'], $store->currency),
            'discount' => $quote['discount'],
            'discount_label' => money($quote['discount'], $store->currency),
            'coupon' => $quote['coupon']?->code,
            'lines' => $lines,
            'cart_url' => route('store.cart', $store->slug),
            'checkout_url' => route('store.checkout', $store->slug),
        ];
    }

    public function couponFromSession(Store $store, float $subtotal): ?Coupon
    {
        $code = session('coupon.'.$store->id);

        if (! is_string($code) || $code === '') {
            return null;
        }

        $coupon = Coupon::query()
            ->where('store_id', $store->id)
            ->whereRaw('upper(code) = ?', [strtoupper($code)])
            ->first();

        if (! $coupon || ! $coupon->isUsable($subtotal)) {
            session()->forget('coupon.'.$store->id);

            return null;
        }

        return $coupon;
    }

    public function applyCoupon(Store $store, string $code): Coupon
    {
        $quote = $this->quote($store);
        $coupon = Coupon::query()
            ->where('store_id', $store->id)
            ->whereRaw('upper(code) = ?', [strtoupper(trim($code))])
            ->first();

        if (! $coupon || ! $coupon->isUsable($quote['subtotal'])) {
            throw ValidationException::withMessages([
                'code' => 'That demonstration code cannot be applied to this cart.',
            ]);
        }

        session(['coupon.'.$store->id => $coupon->code]);

        return $coupon;
    }

    public function merge(Store $store, User $user): void
    {
        $sessionCart = Cart::query()
            ->where('store_id', $store->id)
            ->where('status', 'open')
            ->where('session_id', session()->getId())
            ->whereNull('user_id')
            ->latest('id')
            ->first();

        if (! $sessionCart) {
            return;
        }

        $userCart = Cart::query()
            ->where('store_id', $store->id)
            ->where('status', 'open')
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($userCart && $userCart->id !== $sessionCart->id) {
            foreach ($sessionCart->items as $item) {
                $existing = $userCart->items()->where('variant_id', $item->variant_id)->first();

                if ($existing) {
                    $existing->update(['quantity' => $existing->quantity + $item->quantity]);
                    $item->delete();
                } else {
                    $item->update(['cart_id' => $userCart->id]);
                }
            }

            $sessionCart->update(['status' => 'merged']);

            return;
        }

        $sessionCart->update(['user_id' => $user->id]);
    }

    private function guardLine(Store $store, Product $product, ProductVariant $variant, int $quantity): void
    {
        if ($product->store_id !== $store->id || $product->status !== 'published') {
            throw ValidationException::withMessages([
                'product' => 'This product is not available in the active store.',
            ]);
        }

        if ($variant->product_id !== $product->id || $variant->status !== 'active') {
            throw ValidationException::withMessages([
                'variant' => 'Choose an available option.',
            ]);
        }

        if ($quantity < 1 || $quantity > 20) {
            throw ValidationException::withMessages([
                'quantity' => 'Choose a quantity between 1 and 20.',
            ]);
        }
    }

    private function guardStock(ProductVariant $variant, int $quantity): void
    {
        if ($quantity > $variant->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Only '.$variant->stock_quantity.' units are in demonstration stock.',
            ]);
        }
    }
}
