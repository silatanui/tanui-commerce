@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Products</h1><a class="btn btn-dark" href="{{ route('admin.products.create') }}">New product</a></div>
<form class="mb-3"><input class="form-control" name="q" value="{{ $search }}" placeholder="Search name or SKU"></form>
<table class="table"><thead><tr><th>Product</th><th>SKU</th><th>Sample price</th><th>Status</th><th></th></tr></thead><tbody>
@foreach ($products as $product)
<tr>
    <td>{{ $product->name }}<div class="small text-muted">{{ $product->category?->name }}</div></td>
    <td>{{ $product->sku }}</td>
    <td>{{ money($product->price, $adminStore->currency) }}</td>
    <td>{{ $product->status }}</td>
    <td><a href="{{ route('admin.products.edit', $product) }}">Edit</a></td>
</tr>
@endforeach
</tbody></table>
{{ $products->links() }}
@endsection
