@props(['product'])
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
        <x-price :amount="$product->price" :compare="$product->compare_at_price" :currency="$store->currency" />
    </div>
</article>
