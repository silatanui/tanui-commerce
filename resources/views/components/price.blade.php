@props(['amount', 'compare' => null, 'currency' => null])
<p class="price">
    <span data-live-price>{{ money($amount, $currency ?? ($store->currency ?? 'KES')) }}</span>
    @if ($compare && (float) $compare > (float) $amount)
        <s>{{ money($compare, $currency ?? ($store->currency ?? 'KES')) }}</s>
    @endif
    <small>Sample price</small>
</p>
