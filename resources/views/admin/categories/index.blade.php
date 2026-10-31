@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Categories</h1><a class="btn btn-dark" href="{{ route('admin.categories.create') }}">New category</a></div>
<form class="mb-3"><input class="form-control" name="q" value="{{ $search }}" placeholder="Search categories"></form>
<table class="table"><thead><tr><th>Name</th><th>Parent</th><th>Products</th><th></th></tr></thead><tbody>
@foreach ($categories as $category)
<tr><td>{{ $category->name }}</td><td>{{ $category->parent?->name }}</td><td>{{ $category->products_count }}</td><td><a href="{{ route('admin.categories.edit', $category) }}">Edit</a></td></tr>
@endforeach
</tbody></table>
{{ $categories->links() }}
@endsection
