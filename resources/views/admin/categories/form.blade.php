@extends('layouts.admin')
@section('title', $category->exists ? 'Edit category' : 'New category')
@section('content')
@include('admin.partials.form-head', [
    'title' => $category->exists ? 'Edit category' : 'New category',
    'subtitle' => 'Organise the catalogue aisle. AI can draft the name and shopper-facing description.',
    'backRoute' => route('admin.categories.index'),
    'backLabel' => 'Categories',
    'aiResource' => 'category',
])

<div class="af-shell">
    <form class="af-form" method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <section class="af-section">
            <h2>Category identity</h2>
            <p>What shoppers see in navigation and filters.</p>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="name">Name</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="category">AI suggest</button>
                </div>
                <input class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="af-field">
                <label class="form-label" for="parent_id">Parent</label>
                <select class="form-select" id="parent_id" name="parent_id">
                    <option value="">None</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="description">Description</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="category">AI suggest</button>
                </div>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
            </div>
        </section>

        <section class="af-section">
            <h2>Publishing</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="sort_order">Sort order</label>
                    <input class="form-control" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $category->sort_order) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" @selected(old('status', $category->status) === 'active')>Active</option>
                        <option value="hidden" @selected(old('status', $category->status) === 'hidden')>Hidden</option>
                    </select>
                </div>
            </div>
            <div class="af-field" style="margin-top:.85rem">
                <label class="form-label" for="image">Image</label>
                <input class="form-control" id="image" type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif">
            </div>
        </section>

        <div class="af-actions">
            <button class="btn-admin-dark" type="submit">Save category</button>
            <a class="btn-admin" href="{{ route('admin.categories.index') }}">Cancel</a>
        </div>
    </form>

    <aside class="af-side">
        <h2>Tips</h2>
        <ul>
            <li>Prefer short names that match how shoppers browse.</li>
            <li>Parent categories create nested navigation.</li>
            <li>AI fill drafts copy from the store brand context.</li>
        </ul>
    </aside>
</div>

@if ($category->exists)
    <form class="mt-3" method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">Delete category</button>
    </form>
@endif
@endsection
