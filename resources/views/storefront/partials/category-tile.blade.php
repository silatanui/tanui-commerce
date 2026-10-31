@php
    /** @var \App\Models\Category $category */
    $cover = $category->coverImageUrl();
@endphp
<a class="category-tile {{ $cover ? 'category-tile--media' : '' }}" href="{{ route('store.category', [$store->slug, $category->slug]) }}">
    @if ($cover)
        <span class="category-tile__media" aria-hidden="true">
            <img src="{{ $cover }}" alt="" loading="lazy">
        </span>
    @endif
    <span class="category-tile__copy">
        <span class="eyebrow">{{ $label ?? 'Collection' }}</span>
        <strong>{{ $category->name }}</strong>
        <span class="category-tile__cta">Shop →</span>
    </span>
</a>
