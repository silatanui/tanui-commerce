@extends('layouts.storefront')
@section('title', 'Deals · '.$store->name)
@section('content')
<section class="deals-hero">
    <div class="container">
        <div class="deals-hero__grid">
            <div class="deals-hero__copy">
                <p class="eyebrow">Sale samples</p>
                <h1>Deals</h1>
                <p class="lede">Demonstration markdowns with a compare-at price — sample savings only, no real checkout charges.</p>
                <div class="deals-hero__actions">
                    <a class="btn btn-light" href="{{ route('store.shop', $store->slug) }}">Browse full shop</a>
                    <a class="btn btn-outline-light" href="{{ route('store.arrivals', $store->slug) }}">See new arrivals</a>
                </div>
            </div>
            @if ($highlight)
                <a class="deals-hero__feature" href="{{ route('store.product', [$store->slug, $highlight->slug]) }}">
                    <img src="{{ $highlight->primaryImageUrl() }}" alt="{{ $highlight->name }}" loading="eager">
                    <span class="deals-hero__feature-copy">
                        <span class="eyebrow">Highlighted offer</span>
                        <strong>{{ $highlight->name }}</strong>
                        <em>
                            {{ money($highlight->price, $store->currency) }}
                            <s>{{ money($highlight->compare_at_price, $store->currency) }}</s>
                        </em>
                        @if ($savingsLabel)
                            <span class="deals-hero__save">Save {{ $savingsLabel }}</span>
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
                <h2>Marked-down samples</h2>
                <p>
                    @if ($products->total() > 0)
                        {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} with sample sale pricing
                    @else
                        No sale samples in this store yet
                    @endif
                </p>
            </div>
            <a href="{{ route('store.shop', $store->slug) }}">Shop all →</a>
        </div>

        <div class="row g-4">
            @forelse ($products as $product)
                <div class="col-6 col-lg-3">
                    <x-product-card :product="$product" />
                </div>
            @empty
                <div class="col-12">
                    <div class="deals-empty">
                        <p class="mb-2">No products currently carry a compare-at markdown in this demonstration catalogue.</p>
                        <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Continue shopping</a>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($products->hasPages())
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
