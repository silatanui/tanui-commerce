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
                        <p class="eyebrow">Living spaces</p>
                        <h1>{{ $heroBanner?->title ?? 'Rooms, composed' }}</h1>
                        <p>{{ $heroBanner?->subtitle ?? $store->setting('tagline') }}</p>
                        <div class="hero-store__actions mt-3">
                            <a class="btn btn-light" href="{{ route('store.shop', $store->slug) }}">View the collection</a>
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

@include('storefront.partials.promo-wide')

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Materials, dimensions, colour</h2>
                <p>Each piece lists the details a furniture buyer expects, using fictional measurements.</p>
            </div>
            <a href="{{ route('store.shop', $store->slug) }}">Shop rooms →</a>
        </div>
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                @if ($featured->first())
                    <x-product-card :product="$featured->first()" />
                @endif
            </div>
            <div class="col-lg-5">
                @foreach ($navCategories as $category)
                    <a class="d-block py-2 border-bottom text-decoration-none" href="{{ route('store.category', [$store->slug, $category->slug]) }}">
                        <strong>{{ $category->name }}</strong>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="section-head mt-5">
            <div>
                <h2>Also in the rooms</h2>
                <p>More pieces from the home demonstration set.</p>
            </div>
        </div>
        @include('storefront.partials.grid', ['items' => $featured->skip(1)])
    </div>
</section>

@include('storefront.partials.lookbook-spotlight', ['look' => $lookSpotlight ?? null])

<section class="section pt-0">
    <div class="container">
        <div class="promo-band {{ $promoBanner?->imageUrl() ? 'has-image' : '' }}" @if ($promoBanner?->imageUrl()) style="background-image:url('{{ $promoBanner?->imageUrl() }}')" @endif>
            <h2>{{ $promoBanner?->title ?? 'Delivery information' }}</h2>
            <p class="mb-0">{{ $promoBanner?->subtitle }}</p>
        </div>
    </div>
</section>
