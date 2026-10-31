@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Brands</h1><a class="btn btn-dark" href="{{ route('admin.brands.create') }}">New brand</a></div>
<form class="mb-3"><input class="form-control" name="q" value="{{ $search }}" placeholder="Search brands"></form>
<table class="table"><thead><tr><th>Name</th><th>Products</th><th></th></tr></thead><tbody>
@foreach ($brands as $brand)
<tr><td>{{ $brand->name }}</td><td>{{ $brand->products_count }}</td><td><a href="{{ route('admin.brands.edit', $brand) }}">Edit</a></td></tr>
@endforeach
</tbody></table>
{{ $brands->links() }}
@endsection
