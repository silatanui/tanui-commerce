@php
    /** @var array{title?: string, caption?: string, layout?: string, products?: list<\App\Models\Product>}|null $look */
    $look ??= null;
    $products = collect($look['products'] ?? [])->filter()->values();
@endphp
@if ($look && $products->isNotEmpty())
<section class="section lookbook-spotlight">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow mb-1">From the lookbook</p>
                <h2>{{ $look['title'] }}</h2>
                <p>{{ $look['caption'] }}</p>
            </div>
            <a href="{{ route('store.lookbook', $store->slug) }}">Open lookbook →</a>
        </div>

        <div class="lookbook-mosaic lookbook-mosaic--{{ $look['layout'] ?? 'split' }}">
            @foreach ($products as $product)
                <a class="lookbook-tile" href="{{ route('store.product', [$store->slug, $product->slug]) }}">
                    <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" loading="lazy">
                    <span class="lookbook-tile__meta">
                        <strong>{{ $product->name }}</strong>
                        <em>{{ money($product->price, $store->currency) }}</em>
                        @if ($rating = $product->averageRating())
                            <span class="pcard__rating" aria-label="Rated {{ $rating }} out of 5">
                                <span class="stars" aria-hidden="true">{{ str_repeat('★', (int) round($rating)) }}{{ str_repeat('☆', max(0, 5 - (int) round($rating))) }}</span>
                                <span>{{ $rating }}</span>
                            </span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
