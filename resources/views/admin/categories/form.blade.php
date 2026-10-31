@extends('layouts.admin')
@section('content')
<h1 class="h3">{{ $category->exists ? 'Edit category' : 'New category' }}</h1>
<form class="card card-body" method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($category->exists) @method('PUT') @endif
    <label class="form-label">Name</label>
    <input class="form-control mb-3" name="name" value="{{ old('name', $category->name) }}" required>
    <label class="form-label">Parent</label>
    <select class="form-select mb-3" name="parent_id"><option value="">None</option>@foreach ($parents as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>@endforeach</select>
    <label class="form-label">Description</label>
    <textarea class="form-control mb-3" name="description">{{ old('description', $category->description) }}</textarea>
    <label class="form-label">Sort order</label>
    <input class="form-control mb-3" name="sort_order" type="number" value="{{ old('sort_order', $category->sort_order) }}">
    <label class="form-label">Status</label>
    <select class="form-select mb-3" name="status"><option value="active" @selected(old('status', $category->status) === 'active')>Active</option><option value="hidden" @selected(old('status', $category->status) === 'hidden')>Hidden</option></select>
    <label class="form-label">Image</label>
    <input class="form-control mb-3" type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif">
    <button class="btn btn-dark">Save</button>
</form>
@if ($category->exists)
<form class="mt-3" method="post" action="{{ route('admin.categories.destroy', $category) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">Delete</button></form>
@endif
@endsection
