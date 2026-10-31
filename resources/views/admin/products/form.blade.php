@extends('layouts.admin')
@section('title', $product->exists ? 'Edit product' : 'New product')
@section('content')
@include('admin.partials.form-head', [
    'title' => $product->exists ? 'Edit product' : 'New product',
    'subtitle' => 'Build a catalogue item with pricing, copy and variants. Use AI fill to draft names and descriptions.',
    'backRoute' => route('admin.products.index'),
    'backLabel' => 'Products',
    'aiResource' => 'product',
])

<div class="af-shell">
    <form method="post" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="af-form">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <section class="af-section">
            <h2>Basics</h2>
            <p>Identity, taxonomy and sample pricing.</p>
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="af-field-label">
                        <label for="name">Name</label>
                        <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="product">AI suggest</button>
                    </div>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="sku">SKU</label>
                    <input class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="category_id">Category</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="brand_id">Brand</label>
                    <select class="form-select" id="brand_id" name="brand_id">
                        <option value="">None</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="price">Sample price</label>
                    <input class="form-control" id="price" name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="compare_at_price">Compare-at</label>
                    <input class="form-control" id="compare_at_price" name="compare_at_price" type="number" step="0.01" value="{{ old('compare_at_price', $product->compare_at_price) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="published" @selected(old('status', $product->status) === 'published')>Published</option>
                        <option value="draft" @selected(old('status', $product->status) === 'draft')>Draft</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured" @checked(old('is_featured', $product->is_featured))>
                        <label class="form-check-label" for="featured">Featured</label>
                    </div>
                </div>
            </div>
        </section>

        <section class="af-section">
            <h2>Copy</h2>
            <p>Short line for cards, longer story for the product page.</p>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="short_description">Short description</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="product">AI suggest</button>
                </div>
                <input class="form-control" id="short_description" name="short_description" value="{{ old('short_description', $product->short_description) }}">
            </div>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="description">Description</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="product">AI suggest</button>
                </div>
                <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="af-field">
                <label class="form-label" for="images">Images</label>
                <input class="form-control" id="images" type="file" name="images[]" accept="image/png,image/jpeg,image/webp,image/gif" multiple>
            </div>
        </section>

        <section class="af-section">
            <h2>Specifications</h2>
            <p>Optional key/value details.</p>
            @php $specs = old('spec_names') ? collect(old('spec_names'))->mapWithKeys(fn ($name, $i) => [$name => old('spec_values.'.$i)]) : collect($product->specifications ?? []); @endphp
            @foreach ($specs as $name => $value)
                <div class="row g-2 mb-2">
                    <div class="col"><input class="form-control" name="spec_names[]" value="{{ $name }}"></div>
                    <div class="col"><input class="form-control" name="spec_values[]" value="{{ $value }}"></div>
                </div>
            @endforeach
            <div class="row g-2 mb-2">
                <div class="col"><input class="form-control" name="spec_names[]" placeholder="Name"></div>
                <div class="col"><input class="form-control" name="spec_values[]" placeholder="Value"></div>
            </div>
        </section>

        <section class="af-section">
            <h2>Variations</h2>
            <p>Optional SKUs, stock and option pairs.</p>
            <div id="variant-rows">
                @php $rows = $product->exists ? $product->variants : collect([null]); @endphp
                @foreach ($rows as $index => $variant)
                    @php $attributes = $variant ? ($variant->getAttribute('attributes') ?? []) : []; @endphp
                    <div class="border p-3 mb-2">
                        @if ($variant)<input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">@endif
                        <div class="row g-2">
                            <div class="col-md-3"><input class="form-control" name="variants[{{ $index }}][sku]" placeholder="SKU" value="{{ $variant?->sku }}"></div>
                            <div class="col-md-2"><input class="form-control" name="variants[{{ $index }}][price]" placeholder="Price override" value="{{ $variant?->price }}"></div>
                            <div class="col-md-2"><input class="form-control" name="variants[{{ $index }}][stock]" placeholder="Stock" value="{{ $variant?->stock_quantity ?? 0 }}"></div>
                            @foreach ([0, 1] as $slot)
                                @php $attrName = array_keys($attributes)[$slot] ?? ''; $attrValue = array_values($attributes)[$slot] ?? ''; @endphp
                                <div class="col-md-2"><input class="form-control" name="variants[{{ $index }}][attributes][{{ $slot }}][name]" placeholder="Option" value="{{ $attrName }}"></div>
                                <div class="col"><input class="form-control" name="variants[{{ $index }}][attributes][{{ $slot }}][value]" placeholder="Value" value="{{ $attrValue }}"></div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="btn-admin" type="button" data-add-row="#variant-rows" data-template="#variant-template">Add variation</button>
        </section>

        <div class="af-actions">
            <button class="btn-admin-dark" type="submit">Save product</button>
            <a class="btn-admin" href="{{ route('admin.products.index') }}">Cancel</a>
        </div>
    </form>

    <aside class="af-side">
        <h2>Tips</h2>
        <ul>
            <li>Start with a name, then AI fill the rest.</li>
            <li>Short description appears on cards and search.</li>
            <li>Variants are optional for simple SKUs.</li>
        </ul>
    </aside>
</div>

@if ($product->exists)
    <form class="mt-3" method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Archive this product?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">Archive</button>
    </form>
@endif
<template id="variant-template">
    <div class="border p-3 mb-2"><div class="row g-2">
        <div class="col-md-3"><input class="form-control" name="variants[__INDEX__][sku]" placeholder="SKU"></div>
        <div class="col-md-2"><input class="form-control" name="variants[__INDEX__][price]" placeholder="Price override"></div>
        <div class="col-md-2"><input class="form-control" name="variants[__INDEX__][stock]" placeholder="Stock" value="0"></div>
        <div class="col-md-2"><input class="form-control" name="variants[__INDEX__][attributes][0][name]" placeholder="Option"></div>
        <div class="col"><input class="form-control" name="variants[__INDEX__][attributes][0][value]" placeholder="Value"></div>
    </div></div>
</template>
@endsection
