<div
    id="store-ux"
    hidden
    data-cart-url="{{ route('store.cart', $store->slug) }}"
    data-suggest-url="{{ route('store.search.suggest', $store->slug) }}"
    data-csrf="{{ csrf_token() }}"
></div>

<div class="cart-drawer__backdrop" id="cart-drawer-backdrop" hidden></div>
<aside class="cart-drawer" id="cart-drawer" aria-hidden="true" aria-labelledby="cart-drawer-title">
    <header class="cart-drawer__head">
        <div>
            <p class="eyebrow mb-0">Demonstration cart</p>
            <h2 class="h5 mb-0" id="cart-drawer-title">Your bag</h2>
        </div>
        <button type="button" class="cart-drawer__close" id="cart-drawer-close" aria-label="Close cart">
            <i data-lucide="x"></i>
        </button>
    </header>
    <div class="cart-drawer__body" id="cart-drawer-body">
        <p class="cart-drawer__empty">Your demonstration cart is empty.</p>
    </div>
    <footer class="cart-drawer__foot" id="cart-drawer-foot" hidden>
        <div class="cart-drawer__totals">
            <span>Subtotal</span>
            <strong id="cart-drawer-subtotal">—</strong>
        </div>
        <p class="cart-drawer__note">Sample prices · no real payment</p>
        <div class="cart-drawer__actions">
            <a class="btn btn-outline-dark" href="{{ route('store.cart', $store->slug) }}">View cart</a>
            <a class="btn btn-accent" href="{{ route('store.checkout', $store->slug) }}">Checkout</a>
        </div>
    </footer>
</aside>

<div class="ux-toast" id="ux-toast" hidden role="status" aria-live="polite"></div>
