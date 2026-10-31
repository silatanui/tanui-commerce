@extends('layouts.storefront')
@section('title', $order->order_number)
@section('content')
<section class="section pt-4">
    <div class="container col-lg-8">
        <p class="eyebrow">Demonstration order</p>
        <h1>{{ $order->order_number }}</h1>
        <div class="alert alert-success">{{ $demoConfirmation }}</div>
        <p>Status: {{ ucfirst($order->status) }}. Payment status: not processed.</p>
        <div class="panel p-3">
            @foreach ($order->items as $item)
                <p class="d-flex justify-content-between"><span>{{ $item->product_name }} × {{ $item->quantity }}<br><small>{{ $item->sku }}</small></span><span>{{ money($item->subtotal, $order->currency) }}</span></p>
            @endforeach
            <hr>
            <p class="d-flex justify-content-between"><span>Subtotal</span><span>{{ money($order->subtotal, $order->currency) }}</span></p>
            <p class="d-flex justify-content-between"><span>Discount</span><span>{{ money($order->discount, $order->currency) }}</span></p>
            <p class="d-flex justify-content-between"><span>Delivery</span><span>{{ money($order->shipping_cost, $order->currency) }}</span></p>
            <p class="d-flex justify-content-between"><strong>Total sample</strong><strong>{{ money($order->total, $order->currency) }}</strong></p>
        </div>
        <p class="mt-3">{{ $order->shipping_details['method_label'] ?? 'Delivery' }} · {{ $order->shipping_details['city'] ?? '' }}. Tracking reference {{ $order->shipment->tracking_reference ?? 'not assigned' }} is fictional.</p>
    </div>
</section>
@endsection
