@extends('layouts.storefront')
@section('title', ($category->name ?? ($searching ? 'Search' : 'Shop')).' · '.$store->name)
@section('content')
<section class="section pt-4">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-3">
                <form class="filters panel p-3" method="get" action="{{ $category ? route('store.category', [$store->slug, $category->slug]) : ($searching ? route('store.search', $store->slug) : route('store.shop', $store->slug)) }}">
                    <h2 class="h5">Filter</h2>
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <label class="form-label" for="sort">Sort</label>
                    <select class="form-select mb-3" id="sort" name="sort">
                        @foreach (['latest' => 'Newest', 'price_asc' => 'Price, low to high', 'price_desc' => 'Price, high to low', 'name' => 'Name'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mb-2">Brand</p>
                    @foreach ($brands as $brand)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="brand[]" value="{{ $brand->id }}" id="brand-{{ $brand->id }}" @checked(in_array($brand->id, $filters['brand'] ?? []))>
                            <label class="form-check-label" for="brand-{{ $brand->id }}">{{ $brand->name }}</label>
                        </div>
                    @endforeach
                    <div class="row g-2 mt-3">
                        <div class="col"><label class="form-label" for="min_price">Min</label><input class="form-control" id="min_price" name="min_price" value="{{ $filters['min_price'] ?? '' }}"></div>
                        <div class="col"><label class="form-label" for="max_price">Max</label><input class="form-control" id="max_price" name="max_price" value="{{ $filters['max_price'] ?? '' }}"></div>
                    </div>
                    @if ($colors->isNotEmpty())
                        <label class="form-label mt-3" for="color">Colour</label>
                        <select class="form-select" id="color" name="color">
                            <option value="">Any</option>
                            @foreach ($colors as $color)
                                <option value="{{ $color }}" @selected(($filters['color'] ?? '') === $color)>{{ $color }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if ($sizes->isNotEmpty())
                        <label class="form-label mt-3" for="size">Size</label>
                        <select class="form-select" id="size" name="size">
                            <option value="">Any</option>
                            @foreach ($sizes as $size)
                                <option value="{{ $size }}" @selected(($filters['size'] ?? '') === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    @endif
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="availability" value="in_stock" id="availability" @checked(($filters['availability'] ?? '') === 'in_stock')>
                        <label class="form-check-label" for="availability">In demonstration stock</label>
                    </div>
                    <button class="btn btn-accent w-100 mt-3">Apply</button>
                </form>
            </aside>
            <div class="col-lg-9">
                <p class="eyebrow">{{ $store->name }}</p>
                <h1 class="mb-4">{{ $category->name ?? ($searching ? 'Search'.(request('q') ? ': '.request('q') : '') : 'Shop') }}</h1>
                @include('storefront.partials.grid', ['items' => $products])
                <div class="mt-4">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</section>
@endsection
