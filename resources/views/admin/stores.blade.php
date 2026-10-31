@extends('layouts.admin')
@section('title', 'Stores')
@section('content')
<div class="page-head">
    <h1>Stores</h1>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th>Store</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Orders</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stores as $store)
                <tr>
                    <td><strong>{{ $store->name }}</strong></td>
                    <td>{{ $store->slug }}</td>
                    <td>{{ $store->products_count }}</td>
                    <td>{{ $store->orders_count }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a class="btn-table btn-table-primary" href="{{ route('store.home', $store->slug) }}">View</a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
