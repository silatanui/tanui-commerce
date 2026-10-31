@extends('layouts.admin')
@section('title', 'Inventory')
@section('content')
<div class="page-head">
    <h1>Inventory</h1>
    <span class="sim-label">Low-stock threshold {{ $threshold }}</span>
</div>
<form class="row g-2 mb-3" method="post" action="{{ route('admin.inventory.adjust') }}">
    @csrf
    <div class="col-md-4">
        <select class="form-select form-select-sm" name="variant_id" required>
            @foreach ($variants as $variant)
                <option value="{{ $variant->id }}">{{ $variant->product->name }} · {{ $variant->label() }} ({{ $variant->stock_quantity }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><input class="form-control form-control-sm" name="quantity" type="number" placeholder="Quantity" required></div>
    <div class="col-md-2">
        <select class="form-select form-select-sm" name="movement_type">
            <option value="restock">Restock</option>
            <option value="adjustment">Adjustment</option>
            <option value="damage">Damage</option>
        </select>
    </div>
    <div class="col-md-3"><input class="form-control form-control-sm" name="notes" placeholder="Notes"></div>
    <div class="col-md-1"><button class="btn-page w-100" style="justify-content:center">Save</button></div>
</form>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th style="width:3.25rem"></th>
                <th>Product</th>
                <th>Option</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($variants as $variant)
                <tr class="{{ $variant->stock_quantity <= $threshold ? 'table-warning' : '' }}">
                    <td>
                        <img class="table-thumb" src="{{ $variant->product->primaryImageUrl() }}" alt="" width="44" height="44" loading="lazy">
                    </td>
                    <td><strong>{{ $variant->product->name }}</strong></td>
                    <td>{{ $variant->label() }}</td>
                    <td>{{ $variant->stock_quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="page-head mt-3">
    <h1>Recent movements</h1>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th>When</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Product</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('d M H:i') }}</td>
                    <td>{{ $movement->movement_type }}</td>
                    <td>{{ $movement->quantity }}</td>
                    <td>{{ $movement->variant?->product?->name }}</td>
                    <td>{{ $movement->reference }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
