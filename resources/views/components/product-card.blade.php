@props(['product'])
@php
    $rating = $product->averageRating();
    $reviewsCount = $product->approvedReviewsCount();
@endphp
<article class="pcard">
    <a class="pcard__media" href="{{ route('store.product', [$store->slug, $product->slug]) }}">
        <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}">
        @if ($badge = $product->badge())
            <span class="pcard__badge">{{ $badge }}</span>
        @endif
    </a>
    <div class="pcard__body">
        <p class="eyebrow mb-1">{{ $product->brand?->name }}</p>
        <h3><a href="{{ route('store.product', [$store->slug, $product->slug]) }}">{{ $product->name }}</a></h3>
        @if ($rating)
            <p class="pcard__rating" aria-label="Rated {{ $rating }} out of 5 from {{ $reviewsCount }} {{ \Illuminate\Support\Str::plural('review', $reviewsCount) }}">
                <span class="stars" aria-hidden="true">{{ str_repeat('★', (int) round($rating)) }}{{ str_repeat('☆', max(0, 5 - (int) round($rating))) }}</span>
                <span>{{ $rating }}</span>
                <span class="pcard__rating-count">({{ $reviewsCount }})</span>
            </p>
        @else
            <p class="pcard__rating pcard__rating--empty">No ratings yet</p>
        @endif
        <x-price :amount="$product->price" :compare="$product->compare_at_price" :currency="$store->currency" />
    </div>
</article>
