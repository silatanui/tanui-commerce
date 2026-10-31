@extends('layouts.storefront')
@section('title', $product->name.' · '.$store->name)
@section('content')
@php
    $payload = $product->variants->map(function ($variant) use ($product, $store) {
        return [
            'id' => $variant->id,
            'attributes' => $variant->getAttribute('attributes') ?? [],
            'stock' => $variant->stock_quantity,
            'formatted_price' => money($variant->price ?? $product->price, $store->currency),
        ];
    })->values();
    $attributeNames = $product->variants->flatMap(fn ($variant) => array_keys($variant->getAttribute('attributes') ?? []))->unique()->values();
@endphp
<section class="section pt-4">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6 gallery">
                <img id="primary-image" src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}">
                <div class="d-flex gap-2 mt-3">
                    @foreach ($product->images as $image)
                        <img class="thumb" src="{{ $image->url() }}" alt="{{ $image->alt_text }}" onclick="document.getElementById('primary-image').src=this.src">
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6" data-variants='@json($payload)'>
                <p class="eyebrow">{{ $product->brand?->name }} · {{ $product->category?->name }}</p>
                <h1>{{ $product->name }}</h1>
                <p>{{ $product->short_description }}</p>
                <x-price :amount="$product->price" :compare="$product->compare_at_price" :currency="$store->currency" />
                <p class="sample-note mt-2">Demonstration product. This is not for sale.</p>
                @if ($product->averageRating())
                    <p class="stars mt-3" aria-label="Rating {{ $product->averageRating() }}">{{ str_repeat('★', (int) round($product->averageRating())) }} <span class="text-body">{{ $product->averageRating() }}</span></p>
                @endif
                <form action="{{ route('store.cart.add', $store->slug) }}" method="post" class="mt-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" value="{{ optional($product->variants->first())->id }}">
                    @foreach ($attributeNames as $name)
                        <div class="variant-group mb-3" data-attribute="{{ $name }}">
                            <div class="mb-2">{{ $name }}</div>
                            @foreach ($product->variants->map(fn ($variant) => $variant->getAttribute('attributes')[$name] ?? null)->filter()->unique() as $value)
                                <button class="variant-option" type="button" data-attribute="{{ $name }}" data-value="{{ $value }}">{{ $value }}</button>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="d-flex gap-2 align-items-center" data-qty>
                        <button class="qty-btn" type="button" data-step="-1" aria-label="Decrease quantity">−</button>
                        <input class="form-control" style="max-width:5rem" type="number" name="quantity" value="1" min="1" max="20">
                        <button class="qty-btn" type="button" data-step="1" aria-label="Increase quantity">+</button>
                    </div>
                    <p class="mt-3" data-live-stock>{{ $product->availableStock() }} in demonstration stock</p>
                    <button class="btn btn-accent" data-add type="submit">Add to cart</button>
                </form>
                <div class="d-flex gap-3 mt-3">
                    @auth
                        <form method="post" action="{{ route('store.wishlist.add', $store->slug) }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><button class="btn btn-link px-0">Save to wishlist</button></form>
                    @else
                        <a href="{{ route('store.login', $store->slug) }}">Sign in to save</a>
                    @endauth
                    <form method="post" action="{{ route('store.compare.add', $store->slug) }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><button class="btn btn-link px-0">Compare</button></form>
                </div>
                <h2 class="h5 mt-4">Specifications</h2>
                <table class="spec-table w-100">
                    @foreach ($product->specifications ?? [] as $name => $value)
                        <tr><td class="text-muted">{{ $name }}</td><td>{{ $value }}</td></tr>
                    @endforeach
                    <tr><td class="text-muted">SKU</td><td>{{ $product->sku }}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-lg-8">
                <h2>Description</h2>
                <p>{{ $product->description }}</p>
                <h2 class="h4 mt-4">Reviews</h2>
                @forelse ($product->reviews as $review)
                    <article class="panel p-3 mb-3">
                        <strong>{{ $review->user->name }}</strong>
                        <span class="stars">{{ str_repeat('★', $review->rating) }}</span>
                        <p class="mb-0">{{ $review->review }}</p>
                    </article>
                @empty
                    <p class="muted">No approved reviews yet.</p>
                @endforelse
                @auth
                    <form class="panel p-3" method="post" action="{{ route('store.reviews.store', [$store->slug, $product->slug]) }}">
                        @csrf
                        <label class="form-label" for="rating">Rating</label>
                        <select class="form-select mb-3" id="rating" name="rating">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        <label class="form-label" for="review">Review</label>
                        <textarea class="form-control mb-3" id="review" name="review" rows="3" required></textarea>
                        <button class="btn btn-accent">Submit for moderation</button>
                    </form>
                @endauth
            </div>
        </div>
        <h2 class="mt-5 mb-4">Related products</h2>
        @include('storefront.partials.grid', ['items' => $related])
    </div>
</section>
@endsection
