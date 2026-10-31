@extends('layouts.storefront')
@section('title', 'Wishlist · '.$store->name)
@section('content')
<section class="wishlist-hero">
    <div class="container">
        <div class="wishlist-hero__inner">
            <div>
                <p class="eyebrow">Saved for later</p>
                <h1>Wishlist</h1>
                <p class="lede">
                    @if ($items->isNotEmpty())
                        {{ $items->count() }} {{ \Illuminate\Support\Str::plural('saved product', $items->count()) }} in the {{ $store->name }} demonstration.
                    @else
                        Keep pieces here while you browse the catalogue — demonstration saves only.
                    @endif
                </p>
            </div>
            <div class="wishlist-hero__actions">
                <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Continue shopping</a>
                <a class="btn btn-outline-dark" href="{{ route('store.account', $store->slug) }}">Account</a>
            </div>
        </div>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        @if ($items->isNotEmpty())
            <div class="wishlist-grid">
                @foreach ($items as $item)
                    @php
                        $product = $item->product;
                        $rating = $product?->averageRating();
                        $reviewsCount = $product?->approvedReviewsCount() ?? 0;
                        $inStock = $product && $product->availableStock() > 0;
                    @endphp
                    @continue(! $product)

                    <article class="wishlist-card">
                        <a class="wishlist-card__media" href="{{ route('store.product', [$store->slug, $product->slug]) }}">
                            <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" loading="lazy">
                            @if ($badge = $product->badge())
                                <span class="wishlist-card__badge">{{ $badge }}</span>
                            @endif
                        </a>

                        <div class="wishlist-card__body">
                            <p class="eyebrow mb-1">{{ $product->brand?->name }}</p>
                            <h2>
                                <a href="{{ route('store.product', [$store->slug, $product->slug]) }}">{{ $product->name }}</a>
                            </h2>

                            @if ($rating)
                                <p class="pcard__rating" aria-label="Rated {{ $rating }} out of 5">
                                    <span class="stars" aria-hidden="true">{{ str_repeat('★', (int) round($rating)) }}{{ str_repeat('☆', max(0, 5 - (int) round($rating))) }}</span>
                                    <span>{{ $rating }}</span>
                                    <span class="pcard__rating-count">({{ $reviewsCount }})</span>
                                </p>
                            @endif

                            <div class="wishlist-card__price">
                                <x-price :amount="$product->price" :compare="$product->compare_at_price" :currency="$store->currency" />
                                <span class="wishlist-card__stock {{ $inStock ? 'is-in' : 'is-out' }}">
                                    {{ $inStock ? 'In demo stock' : 'Out of demo stock' }}
                                </span>
                            </div>

                            <div class="wishlist-card__actions">
                                <form method="post" action="{{ route('store.wishlist.move', $store->slug) }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button class="btn btn-accent" type="submit" @disabled(! $inStock)>
                                        {{ $inStock ? 'Move to cart' : 'Unavailable' }}
                                    </button>
                                </form>
                                <a class="btn btn-outline-dark" href="{{ route('store.product', [$store->slug, $product->slug]) }}">View</a>
                                <form method="post" action="{{ route('store.wishlist.remove', $store->slug) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button class="wishlist-card__remove" type="submit" aria-label="Remove {{ $product->name }} from wishlist">Remove</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="wishlist-empty">
                <p class="eyebrow mb-2">Nothing saved yet</p>
                <h2>Your wishlist is empty</h2>
                <p>Open a product and choose <strong>Save to wishlist</strong> to collect demonstration favourites here.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Browse shop</a>
                    <a class="btn btn-outline-dark" href="{{ route('store.arrivals', $store->slug) }}">New arrivals</a>
                    <a class="btn btn-outline-dark" href="{{ route('store.lookbook', $store->slug) }}">Lookbook</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
