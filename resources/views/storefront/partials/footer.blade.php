<footer class="store-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <h2 class="h4 mb-3">{{ $store->name }}</h2>
                <p class="mb-3" style="opacity:.8">{{ $store->setting('tagline') }}</p>
                <p class="mb-0" style="opacity:.72">{{ $store->setting('address') }}<br>{{ $store->setting('phone') }}<br>{{ $store->setting('email') }}</p>
            </div>
            <div class="col-6 col-lg-2">
                <div class="footer-label">Categories</div>
                @foreach ($navCategories as $category)
                    <a class="d-block py-1" href="{{ route('store.category', [$store->slug, $category->slug]) }}">{{ $category->name }}</a>
                @endforeach
            </div>
            <div class="col-6 col-lg-2">
                <div class="footer-label">Explore</div>
                <a class="d-block py-1" href="{{ route('store.shop', $store->slug) }}">Shop</a>
                <a class="d-block py-1" href="{{ route('store.compare', $store->slug) }}">Compare</a>
                <a class="d-block py-1" href="{{ route('store.coming-soon', [$store->slug, 'faq']) }}">Help</a>
                <a class="d-block py-1" href="{{ route('contact') }}">Contact</a>
            </div>
            <div class="col-lg-4">
                <div class="footer-label">Newsletter demonstration</div>
                <p style="opacity:.8">The form does not store addresses or create a subscription.</p>
                <form action="{{ route('store.newsletter', $store->slug) }}" method="post" class="d-flex gap-2">
                    @csrf
                    <label class="visually-hidden" for="newsletter-email">Email</label>
                    <input id="newsletter-email" class="form-control" type="email" name="email" placeholder="name@example.test" required>
                    <button class="btn btn-accent">Preview</button>
                </form>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-between gap-2 mt-5 pt-3" style="border-top:1px solid rgba(255,255,255,.12); opacity:.7">
            <p class="mb-0 small">{{ $store->setting('footer_text') }}</p>
            <p class="mb-0 small">Part of <a href="{{ route('home') }}">Tanui Commerce</a></p>
        </div>
    </div>
</footer>
