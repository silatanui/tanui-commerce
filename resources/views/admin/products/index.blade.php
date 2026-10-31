@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="page-head">
    <h1>Products</h1>
    <a class="btn-page" href="{{ route('admin.products.create') }}">New product</a>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th style="width:3.25rem"></th>
                <th>Product</th>
                <th>SKU</th>
                <th>Sample price</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>
                        <img class="table-thumb" src="{{ $product->primaryImageUrl() }}" alt="" width="44" height="44" loading="lazy">
                    </td>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        <div class="small text-muted">{{ $product->category?->name }}</div>
                    </td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ money($product->price, $adminStore->currency) }}</td>
                    <td><span class="status-pill is-{{ $product->status }}">{{ $product->status }}</span></td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a class="btn-table btn-table-primary" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                            <form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn-table btn-table-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
