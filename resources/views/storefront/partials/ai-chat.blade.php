@if (config('ai.enabled'))
@php
    $shopUrl = route('store.shop', $store->slug);
    $cartUrl = route('store.cart', $store->slug);
@endphp
<div
    class="tc"
    id="ai-chat"
    hidden
    data-endpoint="{{ route('store.ai.chat', $store->slug) }}"
    data-conversations="{{ route('store.ai.conversations', $store->slug) }}"
    data-messages-base="{{ url('store/'.$store->slug.'/ai/conversations') }}"
    data-cart-endpoint="{{ route('store.ai.cart', $store->slug) }}"
    data-commerce-endpoint="{{ route('store.ai.commerce', $store->slug) }}"
    data-store="{{ $store->slug }}"
    data-store-name="{{ $store->name }}"
    data-shop-url="{{ $shopUrl }}"
    data-cart-url="{{ $cartUrl }}"
>
    <button type="button" class="tc__launcher" id="ai-chat-toggle" aria-expanded="false" aria-controls="tc-shell">
        <i data-lucide="messages-square"></i>
        <span>Store assistant</span>
    </button>

    <div class="tc__backdrop" id="tc-backdrop" hidden></div>

    <section class="tc__shell" id="tc-shell" role="dialog" aria-modal="true" aria-label="Store assistant" hidden>
        <header class="tc__top">
            <div class="tc__brand">
                <span class="tc__mark" aria-hidden="true"><i data-lucide="messages-square"></i></span>
                <div>
                    <p class="tc__eyebrow">Shopping assistant</p>
                    <strong>Store assistant</strong>
                    <em>{{ $store->name }}</em>
                </div>
            </div>
            <div class="tc__top-actions">
                <button type="button" class="tc__icon-btn" id="tc-history-toggle" aria-label="Toggle conversations">
                    <i data-lucide="panel-left"></i>
                </button>
                <button type="button" class="tc__icon-btn" id="ai-chat-close" aria-label="Close store assistant">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </header>

        <div class="tc__body">
            <aside class="tc__sidebar" id="tc-sidebar">
                <div class="tc__sidebar-head">
                    <span>Conversations</span>
                    <button type="button" class="tc__new" id="tc-new">New</button>
                </div>
                <div class="tc__conv-list" id="tc-conv-list"></div>
            </aside>

            <div class="tc__main">
                <div class="tc__messages" id="ai-chat-messages" aria-live="polite">
                    <div class="tc__intro">
                        <p class="tc__eyebrow">{{ $store->name }}</p>
                        <strong>Shop from chat — product to delivery</strong>
                        <p>Find items, add to cart, check out, confirm the demo payment, then track delivery. Answers stay grounded in this demonstration catalogue. No real payments.</p>
                        <div class="tc__chips">
                            <button type="button" class="tc__chip" data-prompt="Find a product for me">Find a product</button>
                            <button type="button" class="tc__chip" data-action="cart">View cart</button>
                            <button type="button" class="tc__chip" data-action="checkout">Checkout</button>
                            <button type="button" class="tc__chip" data-prompt="Track my order">Track an order</button>
                        </div>
                    </div>
                </div>

                <div class="tc__dock">
                    <div class="tc__quick" id="tc-quick">
                        <button type="button" data-action="cart">View cart</button>
                        <button type="button" data-action="checkout">Checkout</button>
                        <a href="{{ $shopUrl }}">Browse shop</a>
                        <button type="button" data-action="track">Track order</button>
                    </div>

                    <form class="tc__composer" id="ai-chat-form">
                        @csrf
                        <label class="visually-hidden" for="ai-chat-input">Your message</label>
                        <textarea id="ai-chat-input" name="message" rows="1" maxlength="1000" placeholder="Ask about products, or say “checkout” / “track order”…" required></textarea>
                        <button type="submit" class="tc__send" id="ai-chat-send" aria-label="Send">
                            <i data-lucide="arrow-up"></i>
                        </button>
                    </form>
                    <p class="tc__footnote">Catalogue-grounded · sample prices · no real payments</p>
                </div>
            </div>

            <aside class="tc__details" id="tc-details" hidden>
                <div class="tc__details-head">
                    <span>Product details</span>
                    <button type="button" class="tc__icon-btn tc__icon-btn--ink" id="tc-details-close" aria-label="Close details">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="tc__details-body" id="tc-details-body"></div>
            </aside>
        </div>
    </section>
</div>
@endif
