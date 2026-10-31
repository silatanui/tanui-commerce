@if (! empty($widePromos))
<section class="section promo-wide-section pt-0">
    <div class="container">
        <div class="promo-wide-grid">
            @foreach ($widePromos as $promo)
                <article class="promo-wide tone-{{ $promo['tone'] ?? 'sand' }}">
                    <div class="promo-wide__copy">
                        <p class="promo-wide__eyebrow">{{ $promo['eyebrow'] }}</p>
                        <h2>{{ $promo['title'] }}</h2>
                        @if (! empty($promo['text']))
                            <p>{{ $promo['text'] }}</p>
                        @endif
                        <a href="{{ $promo['href'] }}">{{ $promo['cta'] }} <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="promo-wide__media">
                        <img src="{{ $promo['image'] }}" alt="{{ $promo['title'] }}" loading="lazy">
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
