@extends('layouts.admin')
@section('content')
<h1 class="h3">{{ $brand->exists ? 'Edit brand' : 'New brand' }}</h1>
<form class="card card-body" method="post" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($brand->exists) @method('PUT') @endif
    <label class="form-label">Name</label>
    <input class="form-control mb-3" name="name" value="{{ old('name', $brand->name) }}" required>
    <label class="form-label">Description</label>
    <textarea class="form-control mb-3" name="description">{{ old('description', $brand->description) }}</textarea>
    <label class="form-label">Logo</label>
    <input class="form-control mb-3" type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif">
    <button class="btn btn-dark">Save</button>
</form>
@if ($brand->exists)
<form class="mt-3" method="post" action="{{ route('admin.brands.destroy', $brand) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">Delete</button></form>
@endif
@endsection
