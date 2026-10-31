@extends('layouts.admin')
@section('content')
<h1 class="h3">Demonstration orders</h1>
<form class="row g-2 mb-3">
    <div class="col-md-6"><input class="form-control" name="q" value="{{ $search }}" placeholder="Order number or customer"></div>
    <div class="col-md-4"><select class="form-select" name="status"><option value="">All statuses</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-dark">Filter</button></div>
</form>
<table class="table"><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Sample total</th></tr></thead><tbody>
@foreach ($orders as $order)
<tr>
    <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
    <td>{{ $order->customer_details['full_name'] ?? 'Guest' }}</td>
    <td>{{ ucfirst($order->status) }}</td>
    <td>{{ money($order->total, $order->currency) }}</td>
</tr>
@endforeach
</tbody></table>
{{ $orders->links() }}
@endsection
