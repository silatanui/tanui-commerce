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
                        <p class="eyebrow">Fresh picks</p>
                        <h1>{{ $heroBanner?->title ?? $store->name }}</h1>
                        <p>{{ $heroBanner?->subtitle }}</p>
                        <a class="btn btn-light mt-3" href="{{ route('store.shop', $store->slug) }}">Shop the aisle</a>
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
                <h2>Promotional samples</h2>
                <p>{{ $promoBanner?->subtitle ?? 'Sale badges use a compare-at sample price.' }}</p>
            </div>
            <a href="{{ route('store.shop', $store->slug) }}">Browse offers →</a>
        </div>
        <div class="promo-band mb-4 {{ $promoBanner?->imageUrl() ? 'has-image' : '' }}" @if ($promoBanner?->imageUrl()) style="background-image:url('{{ $promoBanner?->imageUrl() }}')" @endif>
            <h2>{{ $promoBanner?->title ?? 'Promotional samples' }}</h2>
            <p class="mb-0">{{ $promoBanner?->subtitle ?? 'Sale badges use a compare-at sample price. Discount codes are validated on the server.' }}</p>
        </div>
        @include('storefront.partials.grid', ['items' => $offers->isNotEmpty() ? $offers : $featured])
        <div class="section-head mt-5">
            <div>
                <h2>Shop the aisle</h2>
                <p>Everyday demonstration groceries and household items.</p>
            </div>
            <a href="{{ route('store.shop', $store->slug) }}">View aisle →</a>
        </div>
        @include('storefront.partials.grid', ['items' => $arrivals])
    </div>
</section>
