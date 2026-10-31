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
                        <p class="eyebrow">Tech edit</p>
                        <h1>{{ $heroBanner?->title ?? $store->name }}</h1>
                        <p>{{ $heroBanner?->subtitle }}</p>
                        <div class="hero-store__actions mt-3">
                            <a class="btn btn-light" href="{{ route('store.shop', $store->slug) }}">Browse devices</a>
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
                <h2>Shop by category</h2>
                <p>Browse the electronics demonstration catalogue.</p>
            </div>
            <a href="{{ route('store.shop', $store->slug) }}">View all →</a>
        </div>
        <div class="row g-3">
            @foreach ($navCategories as $category)
                <div class="col-6 col-md-4 col-lg">
                    @include('storefront.partials.category-tile', ['category' => $category, 'label' => 'Category'])
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Featured devices</h2>
                <p>Highlighted samples from the tech catalogue.</p>
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
            <h2>{{ $promoBanner?->title ?? 'Compare before you add' }}</h2>
            <p class="mb-0">{{ $promoBanner?->subtitle ?? 'Open a product and add it to the comparison tray. Up to three items from this store.' }}</p>
        </div>
        <div class="section-head mt-5">
            <div>
                <h2>New in the catalogue</h2>
                <p>Recent demonstration devices.</p>
            </div>
            <a href="{{ route('store.arrivals', $store->slug) }}">See arrivals →</a>
        </div>
        @include('storefront.partials.grid', ['items' => $arrivals])
    </div>
</section>
