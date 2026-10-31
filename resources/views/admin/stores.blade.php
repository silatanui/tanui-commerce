@extends('layouts.admin')
@section('content')
<h1 class="h3">Stores</h1>
<table class="table"><thead><tr><th>Store</th><th>Slug</th><th>Products</th><th>Orders</th><th></th></tr></thead><tbody>
@foreach ($stores as $store)
<tr>
    <td>{{ $store->name }}</td>
    <td>{{ $store->slug }}</td>
    <td>{{ $store->products_count }}</td>
    <td>{{ $store->orders_count }}</td>
    <td><a href="{{ route('store.home', $store->slug) }}">View</a></td>
</tr>
@endforeach
</tbody></table>
@endsection
