@extends('layouts.platform')
@section('title', 'Tanui Commerce')
@push('styles')
<style>
.home-hero{position:relative;min-height:92vh;max-height:820px;display:flex;align-items:flex-end;color:#f6f3ee;overflow:hidden;background:#0b1f3a}
.home-hero>.container{position:relative;z-index:2;width:100%}
.home-hero__media,.home-hero__veil{position:absolute;inset:0}
.home-hero__media{z-index:0}
.home-hero__media img{width:100%;height:100%;object-fit:cover;object-position:center 40%}
.home-hero__veil{z-index:1;pointer-events:none;background:linear-gradient(90deg,rgba(11,31,58,.9) 0%,rgba(11,31,58,.55) 50%,rgba(11,31,58,.25) 100%),linear-gradient(0deg,rgba(11,31,58,.7) 0%,transparent 45%)}
.home-hero__content{padding:7rem 0 4.5rem;max-width:42rem}
.home-hero h1{font-family:var(--display);font-size:clamp(2.6rem,6vw,4.8rem);letter-spacing:-.05em;line-height:.98;margin:0 0 1rem;max-width:14ch}
.home-hero__lede{font-size:1.12rem;max-width:32rem;color:rgba(246,243,238,.86);margin:0}
.home-hero__actions{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.6rem}
.home-stores{padding-top:4rem}
.home-store-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1.5rem}
.home-store{text-decoration:none;display:block;color:inherit}
.home-store__media{aspect-ratio:16/10;overflow:hidden;background:#1a2230}
.home-store__media img{width:100%;height:100%;object-fit:cover;transition:transform .55s ease}
.home-store:hover .home-store__media img{transform:scale(1.035)}
.home-store__meta{padding:1rem 0 .25rem}
.home-store__meta h3{font-family:var(--display);font-size:1.35rem;margin:0 0 .35rem;letter-spacing:-.03em}
.home-store__meta p{margin:0;color:var(--muted);font-size:.95rem;max-width:36ch}
.home-features{padding-top:1rem}
@media (max-width:767.98px){.home-store-grid{grid-template-columns:1fr}.home-hero{min-height:78vh;max-height:none}.home-hero__content{padding:6rem 0 3rem}}
</style>
@endpush
@section('content')
@php
    $storeCovers = [
        'fashion' => 'demo-assets/banners/fashion-0.png',
        'electronics' => 'demo-assets/banners/electronics-0.png',
        'home' => 'demo-assets/banners/home-0.png',
        'market' => 'demo-assets/banners/market-0.png',
    ];
@endphp

<section class="home-hero">
    <div class="home-hero__media" aria-hidden="true">
        <img src="{{ asset('demo-assets/banners/fashion-0.png') }}" alt="" width="1600" height="900" fetchpriority="high">
    </div>
    <div class="home-hero__veil" aria-hidden="true"></div>
    <div class="container">
        <div class="home-hero__content">
            <p class="brand-signal">Tanui <span>Commerce</span></p>
            <h1>Building better online shopping experiences.</h1>
            <p class="home-hero__lede">A multi-store Laravel demonstration. Explore four independently branded shops, then inspect the administration that runs them.</p>
            <div class="home-hero__actions">
                <a class="btn btn-gold btn-lg" href="{{ route('projects') }}">Explore the stores</a>
                <a class="btn btn-ghost btn-lg" href="{{ route('store.home', 'fashion') }}?open_assistant=1">Try the AI assistant</a>
                <a class="btn btn-ghost btn-lg" href="{{ route('admin.dashboard') }}">Open the dashboard</a>
            </div>
        </div>
    </div>
</section>

<section class="section home-stores">
    <div class="container">
        <div class="mb-4">
            <p class="eyebrow">Four storefronts</p>
            <h2>One commerce engine.</h2>
        </div>
        <div class="home-store-grid">
            @forelse ($stores as $store)
                @php $cover = $storeCovers[$store->slug] ?? null; @endphp
                <a class="home-store" href="{{ route('store.home', $store->slug) }}">
                    <div class="home-store__media">
                        @if ($cover)
                            <img src="{{ asset($cover) }}" alt="{{ $store->name }}" loading="lazy" width="1600" height="900">
                        @endif
                    </div>
                    <div class="home-store__meta">
                        <h3>{{ $store->name }}</h3>
                        <p>{{ $store->setting('tagline') ?: \Illuminate\Support\Str::limit(str_replace(['—', '–'], [',', ','], $store->description), 110) }}</p>
                    </div>
                </a>
            @empty
                <p>Seed the database to load the four demonstration stores.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="section home-features pt-0">
    <div class="container">
        <h2 class="mb-4">What the demonstration covers</h2>
        <div class="feature-grid">
            @foreach ([
                'Catalogues and variations' => 'Shared Laravel services, isolated by store, with no payment gateway and no real fulfilment.',
                'Live search and cart drawer' => 'Typeahead product suggestions and an instant bag drawer keep the browse-buy loop feeling modern.',
                'Catalogue-grounded AI' => 'A store assistant answers from each shop catalogue, can add to cart, and walk through demo checkout in chat.',
                'Simulated checkout' => 'Addresses, shipping methods and sample coupons. Card details are rejected so nothing is charged.',
                'Inventory and orders' => 'Admin tools mirror a real back office: stock, customers, coupons and order status.',
                'Analytics' => 'Per-store dashboards summarise demonstration revenue, bestsellers and inventory movement.',
            ] as $feature => $copy)
                <article class="panel"><h3 class="h5">{{ $feature }}</h3><p class="muted mb-0">{{ $copy }}</p></article>
            @endforeach
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="cta-band">
            <h2>Need an ecommerce website for your business?</h2>
            <p class="lede">Custom ecommerce built around your products, customers, and operations.</p>
            <a class="btn btn-gold" href="{{ route('contact') }}">Get in touch</a>
        </div>
    </div>
</section>
@endsection
