@extends('layouts.admin')
@section('content')
<h1 class="h3">Analytics</h1>
<p class="sim-label">All figures are simulated</p>
<form class="row g-2 my-3">
    <div class="col-md-3"><input class="form-control" type="date" name="from" value="{{ $report['from']->format('Y-m-d') }}"></div>
    <div class="col-md-3"><input class="form-control" type="date" name="to" value="{{ $report['to']->format('Y-m-d') }}"></div>
    <div class="col-md-2"><button class="btn btn-dark">Apply</button></div>
</form>
<div class="row g-3">
    <div class="col-md-3"><article class="stat"><span>Simulated sales</span><strong>{{ money($report['sales'], $adminStore->currency) }}</strong></article></div>
    <div class="col-md-3"><article class="stat"><span>Orders</span><strong>{{ $report['orders'] }}</strong></article></div>
    <div class="col-md-3"><article class="stat"><span>Average order</span><strong>{{ money($report['average'], $adminStore->currency) }}</strong></article></div>
    <div class="col-md-3"><article class="stat"><span>Products</span><strong>{{ $report['products'] }}</strong></article></div>
</div>
<div class="card card-body my-4"><canvas id="salesChart" height="110"></canvas></div>
<div class="row g-4">
    <div class="col-md-6"><div class="card card-body"><h2 class="h5">Category performance</h2>@foreach ($report['categories'] as $row)<p class="d-flex justify-content-between"><span>{{ $row->category_name }}</span><span>{{ money($row->revenue, $adminStore->currency) }}</span></p>@endforeach</div></div>
    <div class="col-md-6"><div class="card card-body"><h2 class="h5">Low stock</h2>@forelse ($report['low_stock'] as $variant)<p>{{ $variant->product->name }} · {{ $variant->stock_quantity }}</p>@empty<p>No low-stock variants.</p>@endforelse</div></div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
const trend = @json($report['trend']);
new Chart(document.getElementById('salesChart'), { type: 'bar', data: { labels: trend.map(row => row.label), datasets: [{ label: 'Orders', data: trend.map(row => row.orders), backgroundColor: '#087ea4' }] } });
</script>
@endpush
