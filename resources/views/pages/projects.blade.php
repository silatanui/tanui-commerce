@extends('layouts.platform')
@section('title', 'Stores · Tanui Commerce')
@push('styles')
<style>
/* Critical projects layout */
.proj-hero{position:relative;min-height:92vh;max-height:820px;display:flex;align-items:flex-end;color:#f6f3ee;overflow:hidden;background:#0b1f3a}
.proj-hero>.container{position:relative;z-index:2;width:100%}
.proj-hero__media,.proj-hero__veil{position:absolute;inset:0}
.proj-hero__media{z-index:0}
.proj-hero__media img{width:100%;height:100%;object-fit:cover;object-position:center 35%}
.proj-hero__veil{z-index:1;pointer-events:none;background:linear-gradient(90deg,rgba(11,31,58,.88) 0%,rgba(11,31,58,.55) 48%,rgba(11,31,58,.28) 100%),linear-gradient(0deg,rgba(11,31,58,.72) 0%,transparent 48%)}
.proj-hero__content{position:relative;padding:7rem 0 4.5rem;max-width:44rem}
.proj-brand{display:flex;align-items:center;gap:.75rem;font-family:var(--display);font-size:clamp(1.7rem,3.2vw,2.35rem);font-weight:700;letter-spacing:-.04em;margin:0 0 1rem;line-height:1}
.proj-brand img{width:42px;height:42px;flex:0 0 auto}
.proj-brand__text span{color:var(--gold)}
.proj-hero h1{font-family:var(--display);font-size:clamp(2.6rem,6vw,4.8rem);letter-spacing:-.05em;line-height:.98;margin:0 0 1rem;max-width:11ch}
.proj-hero__lede{font-size:1.08rem;max-width:36rem;color:rgba(246,243,238,.86);margin:0;line-height:1em}
.proj-hero__actions{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.6rem}
.proj-intro p,.proj-store__copy p,.proj-admin__band p,.proj-tech-copy,.platform p{line-height:1em}
.proj-store{padding:2.5rem 0 3.5rem}
.proj-store__grid{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.2fr);gap:2.5rem 3rem;align-items:center}
.proj-store--flip .proj-store__copy{order:2}
.proj-store--flip .proj-store__visual{order:1}
.proj-store__actions{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.4rem}
.proj-store__visual{display:grid;gap:.75rem}
.proj-store__hero-shot{display:block;overflow:hidden;aspect-ratio:16/10;background:#1a2230;border-radius:0}
.proj-store__hero-shot img{width:100%;height:100%;object-fit:cover}
.proj-store__thumbs{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
.proj-store__thumbs figure{margin:0;overflow:hidden;aspect-ratio:4/5;background:#ddd6cb;border-radius:0}
.proj-store__thumbs img{width:100%;height:100%;object-fit:cover}
.proj-store__points{margin:1rem 0 0;padding:0;list-style:none;display:grid;gap:.55rem}
.proj-store__points li{position:relative;padding-left:1rem;color:var(--muted);font-size:.95rem;line-height:1em}
.proj-store__points li::before{content:"";position:absolute;left:0;top:.35em;width:.35rem;height:.35rem;background:var(--gold)}
.proj-admin__band{background:radial-gradient(circle at 85% 20%,rgba(196,163,90,.22),transparent 34%),linear-gradient(145deg,#0b1f3a 0%,#16324f 70%,#1d140c 100%);color:#f6f3ee;padding:3rem;display:flex;justify-content:space-between;gap:2rem;align-items:end;flex-wrap:wrap;border-radius:0}
.proj-tech{list-style:none;margin:0;padding:0;display:flex;flex-wrap:wrap;gap:.65rem 1.35rem;color:var(--muted)}
.proj-tech-copy{max-width:42rem;color:var(--muted);margin:0 0 1.15rem}
.proj-intro__lede{max-width:44rem;color:var(--muted);margin:1rem 0 0;font-size:1.02rem;line-height:1em}
@media (max-width:991.98px){.proj-store__grid,.proj-store--flip .proj-store__grid{grid-template-columns:1fr}.proj-store--flip .proj-store__copy,.proj-store--flip .proj-store__visual{order:initial}.proj-hero{min-height:78vh;max-height:none}}
</style>
@endpush
@section('content')
@php
    $storeMedia = [
        'fashion' => [
            'hero' => 'demo-assets/banners/fashion-0.png',
            'products' => [
                'demo-assets/products/fashion-ivory-column-dress.png',
                'demo-assets/products/fashion-wool-overcoat.png',
                'demo-assets/products/fashion-leather-tote.png',
            ],
            'detail' => 'An editorial fashion house built to show seasonal merchandising, size and colour variations, lookbook storytelling, and a polished browse-to-bag path. Open the shop to walk through filters, product pages, wishlist, and a simulated checkout with sample prices.',
            'points' => [
                'Clothing, footwear, and accessories with real variation options',
                'Lookbook, deals, and new arrivals pages for merchandising demos',
                'AI assistant grounded in the fashion catalogue',
            ],
        ],
        'electronics' => [
            'hero' => 'demo-assets/banners/electronics-0.png',
            'products' => [
                'demo-assets/products/electronics-nimbus-phone-x.png',
                'demo-assets/products/electronics-fieldnote-laptop-14.png',
                'demo-assets/products/electronics-studio-headphones.png',
            ],
            'detail' => 'A specifications-first tech catalogue for phones, computers, and audio. Use it to demonstrate comparison-friendly product detail, stock states, search suggestions, and order flows that feel closer to a modern electronics retailer.',
            'points' => [
                'Spec-heavy product pages with sample inventory levels',
                'Live search and cart drawer for a faster browse experience',
                'Category navigation across phones, computers, and audio',
            ],
        ],
        'home' => [
            'hero' => 'demo-assets/banners/home-0.png',
            'products' => [
                'demo-assets/products/home-three-seat-linen-sofa.png',
                'demo-assets/products/home-oak-dining-table.png',
                'demo-assets/products/home-ceramic-table-lamp.png',
            ],
            'detail' => 'A furniture and home studio focused on rooms, materials, and larger product imagery. It is useful for showing how higher-ticket items, dimensions, and material notes can sit inside the same multi-store commerce engine.',
            'points' => [
                'Living, dining, bedroom, and office catalogue groups',
                'Material and dimension notes on key furniture pieces',
                'Large lifestyle photography for room-led browsing',
            ],
        ],
        'market' => [
            'hero' => 'demo-assets/banners/market-0.png',
            'products' => [
                'demo-assets/products/market-olive-oil.png',
                'demo-assets/products/market-single-origin-coffee.png',
                'demo-assets/products/market-sourdough-loaf.png',
            ],
            'detail' => 'A grocery and household shop for pantry goods, drinks, and everyday essentials. It shows how unit-based products, repeat purchase patterns, and simpler product cards can still share the same cart, coupons, and admin tooling.',
            'points' => [
                'Pantry, beverages, household, and personal care samples',
                'Unit pricing and coupon demos for everyday baskets',
                'Fast catalogue browsing suited to frequent restocking',
            ],
        ],
    ];
@endphp

<section class="proj-hero">
    <div class="proj-hero__media" aria-hidden="true">
        <img src="{{ asset('demo-assets/banners/fashion-0.png') }}" alt="" width="1600" height="900" fetchpriority="high">
    </div>
    <div class="proj-hero__veil" aria-hidden="true"></div>
    <div class="container">
        <div class="proj-hero__content">
            <p class="proj-brand">
                <img src="{{ asset('demo-assets/brand/tanui-mark.svg') }}" alt="" width="42" height="42">
                <span class="proj-brand__text">Tanui <span>Commerce</span></span>
            </p>
            <h1>Four stores. One platform.</h1>
            <p class="proj-hero__lede">Tanui Commerce is a live multi-store Laravel demonstration. Each shop has its own catalogue, branding, and checkout path, while one admin panel keeps products, orders, and settings under control. Explore fashion, electronics, home, and grocery without any real payments.</p>
            <div class="proj-hero__actions">
                <a class="btn btn-gold btn-lg" href="#stores">Browse the stores</a>
                <a class="btn btn-ghost btn-lg" href="{{ route('admin.dashboard') }}">Open admin</a>
            </div>
        </div>
    </div>
</section>

<section class="section proj-intro" id="stores">
    <div class="container">
        <p class="eyebrow">Live demos</p>
        <h2>Each shop has its own catalogue, look, and checkout flow.</h2>
        <p class="proj-intro__lede">The sections below open into fully working storefronts. You can browse products, open the bag drawer, try sample coupons, ask the catalogue-grounded assistant for help, and move through a simulated checkout. Everything is demonstration data, so card details are rejected and nothing is charged.</p>
    </div>
</section>

@foreach ($stores as $index => $store)
    @php
        $media = $storeMedia[$store->slug] ?? null;
        $flip = $index % 2 === 1;
        $detail = $media['detail'] ?? str_replace(['—', '–'], [',', ','], $store->description);
        $points = $media['points'] ?? [];
    @endphp
    <section class="proj-store {{ $flip ? 'proj-store--flip' : '' }}">
        <div class="container">
            <div class="proj-store__grid">
                <div class="proj-store__copy">
                    <p class="eyebrow">{{ $store->slug }}</p>
                    <h2>{{ $store->name }}</h2>
                    <p class="muted">{{ $detail }}</p>
                    @if ($points)
                        <ul class="proj-store__points">
                            @foreach ($points as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="proj-store__actions">
                        <a class="btn btn-dark" href="{{ route('store.home', $store->slug) }}">Open storefront</a>
                        <a class="btn btn-outline-dark" href="{{ route('store.shop', $store->slug) }}">Browse shop</a>
                    </div>
                </div>
                <div class="proj-store__visual">
                    @if ($media)
                        <a class="proj-store__hero-shot" href="{{ route('store.home', $store->slug) }}">
                            <img src="{{ asset($media['hero']) }}" alt="{{ $store->name }} lifestyle scene" loading="lazy" width="1600" height="900">
                        </a>
                        <div class="proj-store__thumbs">
                            @foreach ($media['products'] as $productImg)
                                <figure>
                                    <img src="{{ asset($productImg) }}" alt="" loading="lazy" width="900" height="1120">
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endforeach

<section class="section proj-admin">
    <div class="container">
        <div class="proj-admin__band">
            <div>
                <p class="eyebrow">Operations</p>
                <h2>Admin dashboard</h2>
                <p class="lede">Switch between the four demo shops from one control panel. Manage products, categories, brands, inventory movements, coupons, reviews, and orders, then inspect simulated analytics for the active store. The interface is intentionally sharp and monochrome so the data stays easy to scan.</p>
            </div>
            <a class="btn btn-gold btn-lg" href="{{ route('admin.dashboard') }}">Open admin</a>
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <h3 class="projects-tech-title">Technology</h3>
        <p class="proj-tech-copy">Built as a practical Laravel portfolio piece: store resolution middleware, Blade storefronts, Bootstrap layouts, MySQL catalogues, and Chart.js dashboards. The same services power every shop while theme, catalogue, and settings stay isolated per store.</p>
        <ul class="proj-tech">
            @foreach (['Laravel', 'PHP', 'MySQL', 'Blade', 'Bootstrap 5', 'Chart.js'] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </div>
</section>
@endsection
