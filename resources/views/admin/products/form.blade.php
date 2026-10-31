@extends('layouts.admin')
@section('content')
<h1 class="h3">{{ $product->exists ? 'Edit product' : 'New product' }}</h1>
<form method="post" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="card card-body">
    @csrf
    @if ($product->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $product->name) }}" required></div>
        <div class="col-md-4"><label class="form-label">SKU</label><input class="form-control" name="sku" value="{{ old('sku', $product->sku) }}"></div>
        <div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category_id" required>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Brand</label><select class="form-select" name="brand_id"><option value="">None</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Sample price</label><input class="form-control" name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" required></div>
        <div class="col-md-3"><label class="form-label">Compare-at price</label><input class="form-control" name="compare_at_price" type="number" step="0.01" value="{{ old('compare_at_price', $product->compare_at_price) }}"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="published" @selected(old('status', $product->status) === 'published')>Published</option><option value="draft" @selected(old('status', $product->status) === 'draft')>Draft</option></select></div>
        <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured" @checked(old('is_featured', $product->is_featured))><label class="form-check-label" for="featured">Featured</label></div></div>
        <div class="col-12"><label class="form-label">Short description</label><input class="form-control" name="short_description" value="{{ old('short_description', $product->short_description) }}"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4">{{ old('description', $product->description) }}</textarea></div>
        <div class="col-12"><label class="form-label">Images</label><input class="form-control" type="file" name="images[]" accept="image/png,image/jpeg,image/webp,image/gif" multiple></div>
    </div>
    <h2 class="h5 mt-4">Specifications</h2>
    @php $specs = old('spec_names') ? collect(old('spec_names'))->mapWithKeys(fn ($name, $i) => [$name => old('spec_values.'.$i)]) : collect($product->specifications ?? []); @endphp
    @foreach ($specs as $name => $value)
        <div class="row g-2 mb-2"><div class="col"><input class="form-control" name="spec_names[]" value="{{ $name }}"></div><div class="col"><input class="form-control" name="spec_values[]" value="{{ $value }}"></div></div>
    @endforeach
    <div class="row g-2 mb-2"><div class="col"><input class="form-control" name="spec_names[]" placeholder="Name"></div><div class="col"><input class="form-control" name="spec_values[]" placeholder="Value"></div></div>
    <h2 class="h5 mt-4">Variations</h2>
    <div id="variant-rows">
        @php $rows = $product->exists ? $product->variants : collect([null]); @endphp
        @foreach ($rows as $index => $variant)
            @php $attributes = $variant ? ($variant->getAttribute('attributes') ?? []) : []; @endphp
            <div class="border rounded p-3 mb-2">
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
    <button class="btn btn-outline-dark" type="button" data-add-row="#variant-rows" data-template="#variant-template">Add variation</button>
    <div class="mt-4 d-flex gap-2"><button class="btn btn-dark">Save product</button></div>
</form>
@if ($product->exists)
<form class="mt-3" method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Archive this product?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Archive</button></form>
@endif
<template id="variant-template">
    <div class="border rounded p-3 mb-2"><div class="row g-2">
        <div class="col-md-3"><input class="form-control" name="variants[__INDEX__][sku]" placeholder="SKU"></div>
        <div class="col-md-2"><input class="form-control" name="variants[__INDEX__][price]" placeholder="Price override"></div>
        <div class="col-md-2"><input class="form-control" name="variants[__INDEX__][stock]" placeholder="Stock" value="0"></div>
        <div class="col-md-2"><input class="form-control" name="variants[__INDEX__][attributes][0][name]" placeholder="Option"></div>
        <div class="col"><input class="form-control" name="variants[__INDEX__][attributes][0][value]" placeholder="Value"></div>
    </div></div>
</template>
@endsection
