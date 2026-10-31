@extends('layouts.admin')
@section('content')
<p class="sim-label">Demonstration record · no payment processed</p>
<h1 class="h3">{{ $order->order_number }}</h1>
<form class="d-flex gap-2 mb-4" method="post" action="{{ route('admin.orders.update', $order) }}">
    @csrf
    @method('PATCH')
    <select class="form-select" style="max-width:16rem" name="status">@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>@endforeach</select>
    <button class="btn btn-dark">Update status</button>
</form>
<div class="row g-4">
    <div class="col-lg-7"><div class="card card-body">
        @foreach ($order->items as $item)
            <p class="d-flex justify-content-between"><span>{{ $item->product_name }} × {{ $item->quantity }}<br><small>{{ $item->sku }}</small></span><span>{{ money($item->subtotal, $order->currency) }}</span></p>
        @endforeach
        <hr>
        <p class="d-flex justify-content-between"><span>Total</span><strong>{{ money($order->total, $order->currency) }}</strong></p>
    </div></div>
    <div class="col-lg-5"><div class="card card-body">
        <h2 class="h5">Customer</h2>
        <p>{{ $order->customer_details['full_name'] ?? '' }}<br>{{ $order->customer_details['email'] ?? '' }}<br>{{ $order->customer_details['phone'] ?? '' }}</p>
        <h2 class="h5">Delivery</h2>
        <p>{{ $order->shipping_details['method_label'] ?? '' }}<br>{{ $order->shipping_details['address_line'] ?? '' }}<br>{{ $order->shipping_details['city'] ?? '' }} {{ $order->shipping_details['country'] ?? '' }}</p>
        <p>Tracking {{ $order->shipment->tracking_reference ?? 'n/a' }} · {{ $order->shipment->status ?? 'pending' }}</p>
    </div></div>
</div>
@endsection
