@php
    $comingSoon = fn (string $feature) => route('store.coming-soon', [$store->slug, $feature]);
@endphp
<header class="store-nav">
    <div class="store-nav__top">
        <div class="container store-nav__top-inner">
            <a class="brand-link" href="{{ route('store.home', $store->slug) }}">
                @if ($store->logo)
                    <img class="brand-logo" src="{{ $store->logoUrl() }}" alt="" width="36" height="36">
                @endif
                <span class="wordmark">{{ $store->name }}</span>
            </a>

            <form class="search-form" action="{{ route('store.search', $store->slug) }}" method="get" role="search" data-live-search>
                <label class="visually-hidden" for="store-search">Search</label>
                <span class="search-form__icon" aria-hidden="true"><i data-lucide="search"></i></span>
                <input
                    id="store-search"
                    class="form-control"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search for products, brands and more..."
                    autocomplete="off"
                    aria-autocomplete="list"
                    aria-controls="search-suggest"
                    aria-expanded="false"
                >
                <div class="search-suggest" id="search-suggest" hidden role="listbox" aria-label="Search suggestions"></div>
            </form>

            <div class="store-nav__actions">
                <a class="action-link" href="{{ auth()->check() ? route('store.wishlist', $store->slug) : route('store.login', $store->slug) }}">
                    <i data-lucide="heart"></i>
                    <span>Wishlist</span>
                </a>
                <button type="button" class="action-link action-link--btn" id="cart-drawer-toggle" aria-expanded="false" aria-controls="cart-drawer">
                    <i data-lucide="shopping-bag"></i>
                    <span>Cart</span>
                    <em class="cart-count" id="cart-count">{{ $cartCount }}</em>
                </button>
                <div class="dropdown">
                    <button class="action-link action-link--btn" type="button" id="accountMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-lucide="user"></i>
                        <span>{{ auth()->check() ? 'Account' : 'Sign In' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end store-dropdown" aria-labelledby="accountMenu">
                        @auth
                            <li class="store-dropdown__label">{{ auth()->user()->name }}</li>
                            <li><a class="dropdown-item" href="{{ route('store.account', $store->slug) }}"><strong>Overview</strong></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.account.orders', $store->slug) }}"><strong>Orders</strong></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.account.profile', $store->slug) }}"><strong>Profile</strong></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.account.addresses', $store->slug) }}"><strong>Addresses</strong></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.wishlist', $store->slug) }}"><strong>Wishlist</strong></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('store.logout', $store->slug) }}" method="post">
                                    @csrf
                                    <button class="dropdown-item" type="submit"><strong>Sign out</strong></button>
                                </form>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('store.login', $store->slug) }}"><strong>Sign in</strong><span>Customer demo account</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.register', $store->slug) }}"><strong>Create account</strong><span>No real email is sent</span></a></li>
                        @endauth
                    </ul>
                </div>

                <button class="navbar-toggler store-nav__toggler" type="button" data-bs-toggle="collapse" data-bs-target="#storeMenu" aria-controls="storeMenu" aria-expanded="false" aria-label="Open menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="store-nav__bottom">
        <div class="container">
            <div class="collapse store-nav__menu" id="storeMenu">
                <div class="dropdown">
                    <button class="categories-btn dropdown-toggle" type="button" id="allCategoriesMenu" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i data-lucide="menu"></i>
                        <span>All Categories</span>
                    </button>
                    <div class="dropdown-menu store-dropdown store-dropdown--wide" aria-labelledby="allCategoriesMenu">
                        <div class="store-dropdown__grid">
                            @foreach ($navCategories as $category)
                                <div class="store-dropdown__col">
                                    <a class="store-dropdown__heading" href="{{ route('store.category', [$store->slug, $category->slug]) }}">{{ $category->name }}</a>
                                    @forelse ($category->children as $child)
                                        <a href="{{ route('store.category', [$store->slug, $child->slug]) }}">{{ $child->name }}</a>
                                    @empty
                                        <a href="{{ route('store.category', [$store->slug, $category->slug]) }}">Shop {{ $category->name }}</a>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <ul class="store-nav__links">
                    <li><a href="{{ route('store.shop', $store->slug) }}">Shop</a></li>
                    <li><a href="{{ route('store.blog', $store->slug) }}">News</a></li>
                    <li class="dropdown">
                        <button class="nav-plain dropdown-toggle" type="button" id="categoriesMenu" data-bs-toggle="dropdown" aria-expanded="false">Categories</button>
                        <ul class="dropdown-menu store-dropdown" aria-labelledby="categoriesMenu">
                            @foreach ($navCategories as $category)
                                <li><a class="dropdown-item" href="{{ route('store.category', [$store->slug, $category->slug]) }}"><strong>{{ $category->name }}</strong></a></li>
                            @endforeach
                        </ul>
                    </li>
                    <li><a href="{{ route('store.deals', $store->slug) }}">Deals</a></li>
                    <li><a href="{{ route('store.arrivals', $store->slug) }}">New Arrivals</a></li>
                    <li><a href="{{ route('store.bestsellers', $store->slug) }}">Best Sellers</a></li>
                    <li><a href="{{ $comingSoon('brands') }}">Brands</a></li>
                    <li class="dropdown">
                        <button class="nav-plain dropdown-toggle" type="button" id="pagesMenu" data-bs-toggle="dropdown" aria-expanded="false">Pages</button>
                        <ul class="dropdown-menu store-dropdown" aria-labelledby="pagesMenu">
                            <li><a class="dropdown-item" href="{{ route('store.compare', $store->slug) }}"><strong>Compare</strong><span>Up to 3 products</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.lookbook', $store->slug) }}"><strong>Lookbook</strong><span>Editorial samples</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.blog', $store->slug) }}"><strong>News</strong><span>Store journal</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.gift-cards', $store->slug) }}"><strong>Gift cards</strong><span>Demo preview</span></a></li>
                            <li><a class="dropdown-item" href="{{ $comingSoon('loyalty') }}"><strong>Loyalty</strong><span>Coming soon</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.shipping', $store->slug) }}"><strong>Shipping</strong><span>Demo delivery options</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.returns', $store->slug) }}"><strong>Returns</strong><span>Policy walkthrough</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('store.faq', $store->slug) }}"><strong>FAQ</strong><span>Help centre</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('contact') }}"><strong>Contact</strong><span>Portfolio form</span></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
