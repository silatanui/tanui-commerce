@if (! empty($stackPromos))
<div class="promo-stack">
    @foreach ($stackPromos as $promo)
        <a class="promo-stack__card" href="{{ $promo['href'] }}">
            <div class="promo-stack__media">
                <img src="{{ $promo['image'] }}" alt="{{ $promo['title'] }}" loading="lazy">
            </div>
            <div class="promo-stack__copy">
                <strong>{{ $promo['title'] }}</strong>
                <span>{{ $promo['text'] }}</span>
                <em>{{ $promo['cta'] }} →</em>
            </div>
        </a>
    @endforeach
</div>
@endif
