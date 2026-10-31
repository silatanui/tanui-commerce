@if (config('ai.enabled'))
@php
    $shopUrl = route('store.shop', $store->slug);
    $cartUrl = route('store.cart', $store->slug);
    $dealsUrl = route('store.coming-soon', [$store->slug, 'deals']);
    $ordersUrl = route('store.coming-soon', [$store->slug, 'orders']);
@endphp
<div
    class="tc"
    id="ai-chat"
    hidden
    data-endpoint="{{ route('store.ai.chat', $store->slug) }}"
    data-conversations="{{ route('store.ai.conversations', $store->slug) }}"
    data-messages-base="{{ url('store/'.$store->slug.'/ai/conversations') }}"
    data-cart-endpoint="{{ route('store.ai.cart', $store->slug) }}"
    data-store="{{ $store->slug }}"
    data-store-name="{{ $store->name }}"
    data-shop-url="{{ $shopUrl }}"
    data-cart-url="{{ $cartUrl }}"
>
    <button type="button" class="tc__launcher" id="ai-chat-toggle" aria-expanded="false" aria-controls="tc-shell">
        <i data-lucide="sparkles"></i>
        <span>Tanui Concierge</span>
    </button>

    <div class="tc__backdrop" id="tc-backdrop" hidden></div>

    <section class="tc__shell" id="tc-shell" role="dialog" aria-modal="true" aria-label="Tanui Concierge" hidden>
        <header class="tc__top">
            <div class="tc__brand">
                <span class="tc__mark" aria-hidden="true"><i data-lucide="watch"></i></span>
                <div>
                    <strong>Tanui Concierge</strong>
                    <em>Experience luxury · {{ $store->name }}</em>
                </div>
            </div>
            <div class="tc__top-actions">
                <button type="button" class="tc__icon-btn" id="tc-history-toggle" aria-label="Toggle conversations">
                    <i data-lucide="clock-3"></i>
                </button>
                <button type="button" class="tc__icon-btn" id="ai-chat-close" aria-label="Close concierge">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </header>

        <div class="tc__body">
            <aside class="tc__sidebar" id="tc-sidebar">
                <div class="tc__sidebar-head">
                    <span>Conversations</span>
                    <button type="button" class="tc__new" id="tc-new">+ New</button>
                </div>
                <div class="tc__conv-list" id="tc-conv-list"></div>
            </aside>

            <div class="tc__main">
                <div class="tc__messages" id="ai-chat-messages" aria-live="polite">
                    <div class="tc__intro">
                        <strong>Welcome to {{ $store->name }}</strong>
                        <p>Ask about products, specs, or recommendations. Answers are grounded in this demonstration catalogue.</p>
                    </div>
                </div>

                <div class="tc__quick" id="tc-quick">
                    <a href="{{ $ordersUrl }}">Track order</a>
                    <a href="{{ $shopUrl }}">Browse shop</a>
                    <a href="{{ $cartUrl }}">View cart</a>
                    <a href="{{ $dealsUrl }}">Special offers</a>
                </div>

                <form class="tc__composer" id="ai-chat-form">
                    @csrf
                    <label class="visually-hidden" for="ai-chat-input">Your question</label>
                    <textarea id="ai-chat-input" name="message" rows="1" maxlength="1000" placeholder="Ask about products, orders, recommendations…" required></textarea>
                    <button type="submit" class="tc__send" id="ai-chat-send" aria-label="Send">
                        <i data-lucide="send"></i>
                    </button>
                </form>
                <p class="tc__footnote">RAG grounded · {{ config('ai.openai.chat_model') }} · demo prices · no real payments</p>
            </div>

            <aside class="tc__details" id="tc-details" hidden>
                <div class="tc__details-head">
                    <span>Product details</span>
                    <button type="button" class="tc__icon-btn tc__icon-btn--dark" id="tc-details-close" aria-label="Close details">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="tc__details-body" id="tc-details-body"></div>
            </aside>
        </div>
    </section>
</div>
@endif
