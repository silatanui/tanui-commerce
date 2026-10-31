@extends('layouts.platform')
@section('title', 'Tanui Commerce')
@section('content')
    <section class="hero">
        <div class="container">
            <p class="eyebrow">Ecommerce solutions for modern businesses</p>
            <h1>Building better online shopping experiences.</h1>
            <p class="lede mt-4">Tanui Commerce is a multi-store Laravel demonstration. Explore four independently branded shops, then inspect the administration that runs them. Products, prices and orders are fictional.</p>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a class="btn btn-gold btn-lg" href="{{ route('projects') }}">Explore the stores</a>
                <a class="btn btn-ghost btn-lg" href="{{ route('admin.login') }}">Open the dashboard</a>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <p class="eyebrow">Four storefronts</p>
                    <h2>One commerce engine.</h2>
                </div>
            </div>
            <div class="row g-4">
                @forelse ($stores as $store)
                    <div class="col-md-6 col-xl-3">
                        <a class="store-card" href="{{ route('store.home', $store->slug) }}">
                            <div class="store-card__swatch" style="background: {{ safe_hex($store->primary_color, '#16324F') }}">
                                <strong>{{ $store->setting('tagline') }}</strong>
                            </div>
                            <div class="p-4">
                                <h3>{{ $store->name }}</h3>
                                <p class="muted mb-0">{{ $store->description }}</p>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12"><p>Seed the database to load the four demonstration stores.</p></div>
                @endforelse
            </div>
        </div>
    </section>
    <section class="section pt-0">
        <div class="container">
            <h2 class="mb-4">What the demonstration covers</h2>
            <div class="feature-grid">
                @foreach (['Catalogues and variations', 'Search and filtering', 'Cart and wishlist', 'Simulated checkout', 'Inventory and orders', 'Analytics'] as $feature)
                    <article class="panel"><h3 class="h5">{{ $feature }}</h3><p class="muted mb-0">Shared Laravel services, isolated by store, with no payment gateway and no real fulfilment.</p></article>
                @endforeach
            </div>
        </div>
    </section>
    <section class="section pt-0">
        <div class="container">
            <div class="cta-band">
                <h2>Need an ecommerce website for your business?</h2>
                <p class="lede">I develop customised ecommerce solutions designed around your products, customers and business operations.</p>
                <p>Whether you operate a fashion business, electronics shop, furniture store or grocery business, let's build an online shopping experience that works for your business.</p>
                <a class="btn btn-gold" href="{{ route('contact') }}">Get in touch to discuss your project.</a>
            </div>
        </div>
    </section>
@endsection
