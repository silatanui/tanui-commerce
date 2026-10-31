<?php

namespace App\Services\Ai;

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\StoreConfigurationService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatCommerceService
{
    public function __construct(
        private CartService $carts,
        private CheckoutService $checkout,
        private StoreConfigurationService $stores,
    ) {}

    public function resolveSession(Store $store, ?string $sessionToken, ?int $userId, ?string $ip): AiChatSession
    {
        if ($sessionToken) {
            $existing = AiChatSession::query()
                ->where('store_id', $store->id)
                ->where('session_token', $sessionToken)
                ->first();

            if ($existing) {
                $existing->touch();

                return $existing;
            }
        }

        return AiChatSession::query()->create([
            'store_id' => $store->id,
            'session_token' => Str::random(48),
            'user_id' => $userId,
            'ip_address' => $ip,
            'context' => [],
        ]);
    }

    /**
     * Persist chat turns so history survives closing the panel or navigating the storefront.
     *
     * @param  array<string,mixed>|null  $ui
     */
    public function recordTurn(
        AiChatSession $session,
        ?string $userMessage,
        ?string $assistantReply,
        ?array $ui = null,
    ): void {
        if ($userMessage !== null && trim($userMessage) !== '') {
            AiChatMessage::query()->create([
                'ai_chat_session_id' => $session->id,
                'role' => 'user',
                'content' => Str::limit(trim($userMessage), 1000, ''),
            ]);
        }

        if ($assistantReply !== null && trim($assistantReply) !== '') {
            AiChatMessage::query()->create([
                'ai_chat_session_id' => $session->id,
                'role' => 'assistant',
                'content' => Str::limit(trim($assistantReply), 4000, ''),
                'sources' => $ui ? ['ui' => $ui] : null,
            ]);
        }

        if ($ui !== null) {
            $context = $session->context ?? [];
            $context['last_ui'] = $ui;
            if (isset($ui['step'])) {
                $context['step'] = $ui['step'];
            }
            $session->update(['context' => $context]);
        } else {
            $session->touch();
        }
    }

    /**
     * User-facing label for a commerce action (for history).
     */
    public function actionLabel(string $action, array $data = []): ?string
    {
        return match ($action) {
            'cart.summary' => 'View cart',
            'cart.update' => 'Update cart quantity',
            'cart.remove' => 'Remove cart item',
            'buy_now' => 'Buy now',
            'checkout.start' => 'Checkout',
            'checkout.save' => 'Save checkout details',
            'checkout.confirm' => 'Place demo order',
            'order.track' => ! empty($data['order_number'])
                ? 'Track order '.$data['order_number']
                : 'Track my order',
            default => null,
        };
    }

    /**
     * @return array{type:string,reply:string,cart:array,ui:array}
     */
    public function cartSummary(Store $store): array
    {
        $payload = $this->formatCart($store);

        return [
            'type' => 'cart',
            'reply' => $payload['count'] === 0
                ? 'Your demonstration cart is empty. Ask me to find a product, then add it from the cards.'
                : 'Here is your demonstration cart. Adjust quantities or continue to checkout.',
            'cart' => $payload,
            'ui' => ['step' => 'cart', 'cart' => $payload],
        ];
    }

    /**
     * @return array{type:string,reply:string,cart:array,ui:array}
     */
    public function cartUpdate(Store $store, int $itemId, int $quantity): array
    {
        if ($quantity < 1) {
            $this->carts->remove($store, $itemId);
        } else {
            $this->carts->updateQuantity($store, $itemId, $quantity);
        }

        $payload = $this->formatCart($store);

        return [
            'type' => 'cart',
            'reply' => 'Cart updated.',
            'cart' => $payload,
            'ui' => ['step' => 'cart', 'cart' => $payload],
        ];
    }

    /**
     * @return array{type:string,reply:string,cart:array,ui:array}
     */
    public function cartRemove(Store $store, int $itemId): array
    {
        $this->carts->remove($store, $itemId);
        $payload = $this->formatCart($store);

        return [
            'type' => 'cart',
            'reply' => 'Item removed from the demonstration cart.',
            'cart' => $payload,
            'ui' => ['step' => 'cart', 'cart' => $payload],
        ];
    }

    /**
     * @return array{type:string,reply:string,cart:array,products:list,ui:array}
     */
    public function buyNow(Store $store, int $productId, int $variantId, int $quantity = 1): array
    {
        $product = Product::query()->forStore($store)->published()->with('variants')->findOrFail($productId);
        $variant = $product->variants()->where('status', 'active')->findOrFail($variantId);
        $this->carts->add($store, $product, $variant, max(1, min(20, $quantity)));

        return $this->checkoutStart($store, 'Added '.$product->name.' — continue with checkout details.');
    }

    /**
     * @return array{type:string,reply:string,cart:array,ui:array}
     */
    public function checkoutStart(Store $store, ?string $reply = null): array
    {
        $cart = $this->formatCart($store);

        if ($cart['count'] === 0) {
            return [
                'type' => 'cart',
                'reply' => 'Add a product before checkout.',
                'cart' => $cart,
                'ui' => ['step' => 'cart', 'cart' => $cart],
            ];
        }

        $methods = $this->shippingMethodsPayload($store);

        return [
            'type' => 'checkout',
            'reply' => $reply ?? 'Enter delivery details for this demonstration order. No card is required.',
            'cart' => $cart,
            'ui' => [
                'step' => 'checkout',
                'cart' => $cart,
                'shipping_methods' => $methods,
                'draft' => [],
            ],
        ];
    }

    /**
     * @param  array<string,mixed>  $draft
     * @return array{type:string,reply:string,cart:array,ui:array,session_token:string}
     */
    public function checkoutSave(Store $store, AiChatSession $session, array $draft): array
    {
        $cart = $this->formatCart($store);

        if ($cart['count'] === 0) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $validated = $this->validateDraft($draft);
        $context = $session->context ?? [];
        $context['checkout_draft'] = $validated;
        $context['step'] = 'payment';
        $session->update(['context' => $context]);

        return [
            'type' => 'payment',
            'reply' => 'Details saved. Confirm the demonstration payment — no card number is collected.',
            'cart' => $cart,
            'session_token' => $session->session_token,
            'ui' => [
                'step' => 'payment',
                'cart' => $cart,
                'draft' => $validated,
                'shipping_methods' => $this->shippingMethodsPayload($store),
            ],
        ];
    }

    /**
     * @return array{type:string,reply:string,order:array,ui:array,session_token:string}
     */
    public function checkoutConfirm(Store $store, AiChatSession $session, ?\App\Models\User $user = null): array
    {
        $draft = $session->context['checkout_draft'] ?? null;

        if (! is_array($draft)) {
            throw ValidationException::withMessages([
                'checkout' => 'Complete delivery details before confirming payment.',
            ]);
        }

        $validated = $this->validateDraft($draft);
        $validated['accept_demo'] = true;

        $order = $this->checkout->place($store, $validated, $user);
        $order->loadMissing('store');
        $orderPayload = $this->formatOrder($order);

        $context = $session->context ?? [];
        $context['last_order_number'] = $order->order_number;
        $context['step'] = 'order';
        $context['checkout_draft'] = null;
        $session->update(['context' => $context]);

        return [
            'type' => 'order',
            'reply' => 'Demonstration order '.$order->order_number.' placed. No payment was processed. You can track delivery below.',
            'order' => $orderPayload,
            'session_token' => $session->session_token,
            'ui' => [
                'step' => 'order',
                'order' => $orderPayload,
            ],
        ];
    }

    /**
     * @return array{type:string,reply:string,order:?array,ui:array}
     */
    public function orderTrack(Store $store, ?string $orderNumber = null, ?string $email = null, ?AiChatSession $session = null): array
    {
        $orderNumber = $orderNumber ?: ($session?->context['last_order_number'] ?? null);

        if (! $orderNumber) {
            return [
                'type' => 'track_prompt',
                'reply' => 'Share your demonstration order number (and email if needed) to track delivery.',
                'order' => null,
                'ui' => ['step' => 'track_prompt'],
            ];
        }

        $order = Order::query()
            ->where('store_id', $store->id)
            ->where('order_number', $orderNumber)
            ->with(['items', 'shipment'])
            ->first();

        if (! $order) {
            throw ValidationException::withMessages(['order' => 'That order number was not found in this store.']);
        }

        $owns = auth()->check() && (int) $order->user_id === (int) auth()->id();
        $known = in_array($order->order_number, session('demo_order_numbers', []), true);
        $emailMatch = $email && strcasecmp((string) ($order->customer_details['email'] ?? ''), $email) === 0;
        $sessionMatch = $session && ($session->context['last_order_number'] ?? null) === $order->order_number;

        if (! ($owns || $known || $emailMatch || $sessionMatch || auth()->user()?->isAdmin())) {
            throw ValidationException::withMessages(['order' => 'Confirm the email used at checkout to view this order.']);
        }

        $payload = $this->formatOrder($order);

        return [
            'type' => 'delivery',
            'reply' => 'Delivery status for '.$order->order_number.'.',
            'order' => $payload,
            'ui' => [
                'step' => 'delivery',
                'order' => $payload,
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function formatCart(Store $store): array
    {
        $quote = $this->carts->quote($store);
        $cart = $quote['cart'];
        $methods = $this->stores->shippingMethods($store);
        $defaultShipping = (float) ($methods['standard']['cost'] ?? 0);
        $totalEstimate = round(max(0, $quote['subtotal'] - $quote['discount'] + $defaultShipping), 2);

        $lines = $cart->items->map(function ($item) use ($store) {
            $product = $item->product;

            return [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'name' => $product?->name ?? 'Item',
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'unit_label' => money($item->unit_price, $store->currency),
                'line_total' => round((float) $item->unit_price * $item->quantity, 2),
                'line_label' => money((float) $item->unit_price * $item->quantity, $store->currency),
                'image' => $product?->primaryImageUrl(),
                'max' => min(20, (int) ($item->variant?->stock_quantity ?? 20)),
            ];
        })->values()->all();

        return [
            'count' => (int) $cart->items->sum('quantity'),
            'currency' => $store->currency,
            'subtotal' => $quote['subtotal'],
            'subtotal_label' => money($quote['subtotal'], $store->currency),
            'discount' => $quote['discount'],
            'discount_label' => money($quote['discount'], $store->currency),
            'coupon' => $quote['coupon']?->code,
            'shipping_hint' => money($defaultShipping, $store->currency),
            'total_estimate' => $totalEstimate,
            'total_estimate_label' => money($totalEstimate, $store->currency),
            'lines' => $lines,
        ];
    }

    /**
     * @return list<array{key:string,label:string,cost:float,cost_label:string,detail:string}>
     */
    public function shippingMethodsPayload(Store $store): array
    {
        $methods = $this->stores->shippingMethods($store);

        return collect($methods)->map(fn ($method, $key) => [
            'key' => (string) $key,
            'label' => $method['label'],
            'cost' => (float) $method['cost'],
            'cost_label' => money($method['cost'], $store->currency),
            'detail' => $method['detail'] ?? '',
        ])->values()->all();
    }

    /**
     * @param  array<string,mixed>  $draft
     * @return array<string,mixed>
     */
    private function validateDraft(array $draft): array
    {
        $methods = array_keys(config('commerce.shipping_methods', []));

        $validator = validator($draft, [
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'address_line' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:80'],
            'shipping_method' => ['required', 'in:'.implode(',', $methods)],
            'card_number' => ['prohibited'],
            'card' => ['prohibited'],
            'cvv' => ['prohibited'],
            'cvc' => ['prohibited'],
            'pan' => ['prohibited'],
            'payment_token' => ['prohibited'],
            'payment_method' => ['prohibited'],
        ], [
            'card_number.prohibited' => 'Payment cards are not accepted.',
            'cvv.prohibited' => 'Payment cards are not accepted.',
        ]);

        return $validator->validate();
    }

    /**
     * @return array<string,mixed>
     */
    private function formatOrder(Order $order): array
    {
        $statuses = config('commerce.order_statuses', []);
        $pipeline = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $current = $order->status;
        $currentIndex = array_search($current, $pipeline, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $timeline = collect($pipeline)->map(function (string $key, int $i) use ($statuses, $currentIndex, $current) {
            return [
                'key' => $key,
                'label' => $statuses[$key] ?? ucfirst($key),
                'state' => $current === 'cancelled'
                    ? 'idle'
                    : ($i < $currentIndex ? 'done' : ($i === $currentIndex ? 'current' : 'upcoming')),
            ];
        })->values()->all();

        if ($current === 'cancelled') {
            $timeline[] = [
                'key' => 'cancelled',
                'label' => $statuses['cancelled'] ?? 'Cancelled',
                'state' => 'current',
            ];
        }

        return [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $statuses[$order->status] ?? $order->status,
            'payment_status' => $order->payment_status,
            'is_demo' => (bool) $order->is_demo,
            'subtotal_label' => money($order->subtotal, $order->currency),
            'shipping_label' => money($order->shipping_cost, $order->currency),
            'discount_label' => money($order->discount, $order->currency),
            'total_label' => money($order->total, $order->currency),
            'currency' => $order->currency,
            'customer' => $order->customer_details ?? [],
            'shipping' => $order->shipping_details ?? [],
            'shipment' => [
                'status' => $order->shipment?->status,
                'method' => $order->shipment?->shipping_method,
                'tracking_reference' => $order->shipment?->tracking_reference,
                'eta_note' => $order->shipping_details['method_label'] ?? ($order->shipment?->shipping_method),
            ],
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'line_label' => money($item->subtotal, $order->currency),
            ])->values()->all(),
            'timeline' => $timeline,
            'url' => route('store.order', [
                $order->store?->slug ?: current_store()->slug,
                $order->order_number,
            ]),
        ];
    }
}
