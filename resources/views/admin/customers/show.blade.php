@extends('layouts.admin')
@section('content')
<h1 class="h3">{{ $customer->name }}</h1>
<p>{{ $customer->email }} · {{ $customer->phone }}</p>
<form method="post" action="{{ route('admin.customers.update', $customer) }}" class="d-flex gap-2 mb-4">
    @csrf
    @method('PATCH')
    <select class="form-select" style="max-width:12rem" name="status"><option value="active" @selected($customer->status === 'active')>Active</option><option value="suspended" @selected($customer->status === 'suspended')>Suspended</option></select>
    <button class="btn btn-dark">Update status</button>
</form>
<h2 class="h5">Orders in {{ $adminStore->name }}</h2>
@forelse ($orders as $order)
<p><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a> · {{ money($order->total, $order->currency) }}</p>
@empty
<p>No orders in the active store.</p>
@endforelse
@endsection
