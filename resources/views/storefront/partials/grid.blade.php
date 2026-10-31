<div class="row g-4">
    @forelse ($items as $product)
        <div class="col-6 col-lg-3">
            <x-product-card :product="$product" />
        </div>
    @empty
        <div class="col-12"><p class="muted">No demonstration products in this section yet.</p></div>
    @endforelse
</div>
