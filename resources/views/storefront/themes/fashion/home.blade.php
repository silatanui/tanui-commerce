@php
    $heroBanner = $banners->firstWhere('position', 'hero');
    $promoBanner = $banners->firstWhere('position', 'promo');
@endphp
<section class="home-split section pb-0">
    <div class="container">
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-8">
                <div class="hero-store hero-store--panel {{ $heroBanner?->imageUrl() ? 'has-image' : '' }}" @if ($heroBanner?->imageUrl()) style="background-image:url('{{ $heroBanner?->imageUrl() }}')" @endif>
                    <div class="hero-store__inner">
                        <p class="eyebrow">New season</p>
                        <h1>{{ $heroBanner?->title ?? 'The new season, edited' }}</h1>
                        <p>{{ $heroBanner?->subtitle ?? $store->setting('tagline') }}</p>
                        <div class="hero-store__actions mt-3">
                            <a class="btn btn-light" href="{{ route('store.shop', $store->slug) }}">Shop the edit</a>
                            @if (config('ai.enabled'))
                                <button type="button" class="btn btn-outline-light" data-open-assistant>Ask the store</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @include('storefront.partials.promo-stack')
            </div>
        </div>
    </div>
</section>

@include('storefront.partials.trust-strip')

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Shop by category</h2>
                <p>Browse the fashion demonstration collections.</p>
            </div>
            <a href="{{ route('store.shop', $store->slug) }}">View all →</a>
        </div>
        <div class="row g-3">
            @foreach ($navCategories as $category)
                <div class="col-6 col-lg-3">
                    @include('storefront.partials.category-tile', ['category' => $category, 'label' => 'Collection'])
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Featured</h2>
                <p>Edited picks from the sample catalogue.</p>
            </div>
            <a href="{{ route('store.shop', $store->slug) }}">Shop featured →</a>
        </div>
        @include('storefront.partials.grid', ['items' => $featured])
    </div>
</section>

@include('storefront.partials.lookbook-spotlight', ['look' => $lookSpotlight ?? null])

<section class="section pt-0">
    <div class="container">
        <div class="promo-band {{ $promoBanner?->imageUrl() ? 'has-image' : '' }}" @if ($promoBanner?->imageUrl()) style="background-image:url('{{ $promoBanner?->imageUrl() }}')" @endif>
            <h2>{{ $promoBanner?->title ?? 'New arrivals' }}</h2>
            <p>{{ $promoBanner?->subtitle }}</p>
            <a class="btn btn-light" href="{{ route('store.arrivals', $store->slug) }}">Explore arrivals</a>
        </div>
        <div class="section-head mt-5 mb-0">
            <div>
                <h2>Just in</h2>
                <p>Recent demonstration products.</p>
            </div>
        </div>
        <div class="mt-3">
            @include('storefront.partials.grid', ['items' => $arrivals])
        </div>
    </div>
</section>

@include('storefront.partials.promo-wide')

<section class="section pt-0">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Best-selling samples</h2>
                <p>Based on simulated order history.</p>
            </div>
            <a href="{{ route('store.bestsellers', $store->slug) }}">View best sellers →</a>
        </div>
        @include('storefront.partials.grid', ['items' => $bestsellers])
    </div>
</section>
