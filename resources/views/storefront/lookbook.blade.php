@extends('layouts.storefront')
@section('title', $title.' · '.$store->name)
@section('content')
<section class="lookbook-hero">
    <div class="container">
        <p class="eyebrow">Editorial sample</p>
        <h1>{{ $title }}</h1>
        <p class="lede col-lg-8">{{ $lede }}</p>
    </div>
</section>

@forelse ($looks as $index => $look)
    <section class="lookbook-look lookbook-look--{{ $look['layout'] }} {{ $index % 2 === 1 ? 'lookbook-look--alt' : '' }}">
        <div class="container">
            <div class="lookbook-look__head">
                <div>
                    <p class="eyebrow">Look {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
                    <h2>{{ $look['title'] }}</h2>
                    <p>{{ $look['caption'] }}</p>
                </div>
            </div>

            <div class="lookbook-mosaic lookbook-mosaic--{{ $look['layout'] }}">
                @foreach ($look['products'] as $product)
                    <a class="lookbook-tile" href="{{ route('store.product', [$store->slug, $product->slug]) }}">
                        <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" loading="lazy">
                        <span class="lookbook-tile__meta">
                            <strong>{{ $product->name }}</strong>
                            <em>{{ money($product->price, $store->currency) }}</em>
                            @if ($rating = $product->averageRating())
                                <span class="pcard__rating" aria-label="Rated {{ $rating }} out of 5">
                                    <span class="stars" aria-hidden="true">{{ str_repeat('★', (int) round($rating)) }}{{ str_repeat('☆', 5 - (int) round($rating)) }}</span>
                                    <span>{{ $rating }}</span>
                                </span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@empty
    <section class="section">
        <div class="container">
            <p class="muted">No lookbook products are available for this store yet.</p>
            <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Browse shop</a>
        </div>
    </section>
@endforelse

<section class="section pt-0">
    <div class="container">
        <div class="lookbook-note">
            <p class="mb-0">This lookbook reuses catalogue product images for a demonstration editorial layout. Swap in campaign photography anytime — send lifestyle shots if you want these boards upgraded.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Shop the catalogue</a>
            <a class="btn btn-outline-dark" href="{{ route('store.arrivals', $store->slug) }}">New arrivals</a>
        </div>
    </div>
</section>
@endsection
