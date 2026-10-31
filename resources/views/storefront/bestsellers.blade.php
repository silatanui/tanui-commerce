@extends('layouts.storefront')
@section('title', 'Best sellers · '.$store->name)
@section('content')
<section class="bestsellers-hero">
    <div class="container">
        <div class="bestsellers-hero__grid">
            <div class="bestsellers-hero__copy">
                <p class="eyebrow">Customer favourites</p>
                <h1>Best sellers</h1>
                <p class="lede">
                    @if ($source === 'orders')
                        Ranked from simulated order history in {{ $store->name }} — units sold in the demonstration only.
                    @else
                        No order history yet for this store, so featured catalogue picks are shown instead.
                    @endif
                </p>
                <div class="bestsellers-hero__actions">
                    <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Browse full shop</a>
                    <a class="btn btn-outline-dark" href="{{ route('store.deals', $store->slug) }}">View deals</a>
                </div>
            </div>
            @if ($highlight)
                <a class="bestsellers-hero__feature" href="{{ route('store.product', [$store->slug, $highlight->slug]) }}">
                    <img src="{{ $highlight->primaryImageUrl() }}" alt="{{ $highlight->name }}" loading="eager">
                    <span class="bestsellers-hero__feature-copy">
                        <span class="eyebrow">{{ $source === 'orders' ? '#1 sample' : 'Featured pick' }}</span>
                        <strong>{{ $highlight->name }}</strong>
                        <em>{{ money($highlight->price, $store->currency) }}</em>
                        @if ($source === 'orders' && isset($unitsById[$highlight->id]))
                            <span class="bestsellers-hero__units">{{ (int) $unitsById[$highlight->id] }} demo units sold</span>
                        @endif
                    </span>
                </a>
            @endif
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Top of the chart</h2>
                <p>{{ $products->count() }} {{ \Illuminate\Support\Str::plural('product', $products->count()) }} · {{ $source === 'orders' ? 'by simulated sales' : 'featured stand-ins' }}</p>
            </div>
            <a href="{{ route('store.arrivals', $store->slug) }}">New arrivals →</a>
        </div>

        <div class="row g-4">
            @forelse ($products as $index => $product)
                <div class="col-6 col-lg-3">
                    <div class="bestsellers-card">
                        <span class="bestsellers-card__rank">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <x-product-card :product="$product" />
                        @if ($source === 'orders' && isset($unitsById[$product->id]))
                            <p class="bestsellers-card__meta">{{ (int) $unitsById[$product->id] }} demo units</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="muted mb-0">No bestseller data yet. Browse the full shop instead.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
