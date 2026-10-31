@extends('layouts.storefront')
@section('title', 'New arrivals · '.$store->name)
@section('content')
<section class="arrivals-hero">
    <div class="container">
        <div class="arrivals-hero__grid">
            <div class="arrivals-hero__copy">
                <p class="eyebrow">Just in</p>
                <h1>New arrivals</h1>
                <p class="lede">The newest pieces in the {{ $store->name }} demonstration catalogue — sorted by when they were added.</p>
                <div class="arrivals-hero__actions">
                    <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Browse full shop</a>
                    @if (config('ai.enabled'))
                        <button type="button" class="btn btn-outline-dark" data-open-assistant>Ask what’s new</button>
                    @endif
                </div>
            </div>
            @if ($highlight)
                <a class="arrivals-hero__feature" href="{{ route('store.product', [$store->slug, $highlight->slug]) }}">
                    <img src="{{ $highlight->primaryImageUrl() }}" alt="{{ $highlight->name }}" loading="eager">
                    <span class="arrivals-hero__feature-copy">
                        <span class="eyebrow">Latest drop</span>
                        <strong>{{ $highlight->name }}</strong>
                        <em>{{ money($highlight->price, $store->currency) }}</em>
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
                <h2>Fresh to the floor</h2>
                <p>{{ $products->total() }} demonstration {{ \Illuminate\Support\Str::plural('product', $products->total()) }} · newest first</p>
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
                    <p class="muted mb-0">No new demonstration products yet. Browse the full shop instead.</p>
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
