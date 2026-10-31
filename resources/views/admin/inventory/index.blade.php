@extends('layouts.admin')
@section('content')
<h1 class="h3">Inventory</h1>
<p class="sim-label">Low-stock threshold {{ $threshold }}</p>
<form class="row g-2 my-3" method="post" action="{{ route('admin.inventory.adjust') }}">
    @csrf
    <div class="col-md-4"><select class="form-select" name="variant_id" required>@foreach ($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->product->name }} · {{ $variant->label() }} ({{ $variant->stock_quantity }})</option>@endforeach</select></div>
    <div class="col-md-2"><input class="form-control" name="quantity" type="number" placeholder="Quantity" required></div>
    <div class="col-md-2"><select class="form-select" name="movement_type"><option value="restock">Restock</option><option value="adjustment">Adjustment</option><option value="damage">Damage</option></select></div>
    <div class="col-md-3"><input class="form-control" name="notes" placeholder="Notes"></div>
    <div class="col-md-1"><button class="btn btn-dark">Save</button></div>
</form>
<table class="table"><thead><tr><th>Product</th><th>Option</th><th>Stock</th></tr></thead><tbody>
@foreach ($variants as $variant)
<tr class="{{ $variant->stock_quantity <= $threshold ? 'table-warning' : '' }}"><td>{{ $variant->product->name }}</td><td>{{ $variant->label() }}</td><td>{{ $variant->stock_quantity }}</td></tr>
@endforeach
</tbody></table>
{{ $variants->links() }}
<h2 class="h5 mt-4">Recent movements</h2>
@foreach ($movements as $movement)
<p>{{ $movement->created_at->format('d M H:i') }} · {{ $movement->movement_type }} · {{ $movement->quantity }} · {{ $movement->variant?->product?->name }} · {{ $movement->reference }}</p>
@endforeach
@endsection
