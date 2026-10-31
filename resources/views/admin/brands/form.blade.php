@extends('layouts.admin')
@section('title', $brand->exists ? 'Edit brand' : 'New brand')
@section('content')
@include('admin.partials.form-head', [
    'title' => $brand->exists ? 'Edit brand' : 'New brand',
    'subtitle' => 'House-brand storytelling for the catalogue. Let AI draft a polished brand blurb.',
    'backRoute' => route('admin.brands.index'),
    'backLabel' => 'Brands',
    'aiResource' => 'brand',
])

<div class="af-shell">
    <form class="af-form" method="post" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($brand->exists) @method('PUT') @endif

        <section class="af-section">
            <h2>Brand profile</h2>
            <p>Name and editorial description shown on product pages.</p>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="name">Name</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="brand">AI suggest</button>
                </div>
                <input class="form-control" id="name" name="name" value="{{ old('name', $brand->name) }}" required>
            </div>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="description">Description</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="brand">AI suggest</button>
                </div>
                <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $brand->description) }}</textarea>
            </div>
            <div class="af-field">
                <label class="form-label" for="logo">Logo</label>
                <input class="form-control" id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif">
            </div>
        </section>

        <div class="af-actions">
            <button class="btn-admin-dark" type="submit">Save brand</button>
            <a class="btn-admin" href="{{ route('admin.brands.index') }}">Cancel</a>
        </div>
    </form>

    <aside class="af-side">
        <h2>Tips</h2>
        <ul>
            <li>Keep brand voice editorial and concise.</li>
            <li>Logo works best as a square PNG or SVG mark.</li>
            <li>AI fill is a draft, not a final legal statement.</li>
        </ul>
    </aside>
</div>

@if ($brand->exists)
    <form class="mt-3" method="post" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Delete this brand?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">Delete brand</button>
    </form>
@endif
@endsection
