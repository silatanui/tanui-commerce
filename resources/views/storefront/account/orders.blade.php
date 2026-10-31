@extends('layouts.storefront')
@section('title', 'Orders')
@section('content')
<section class="section pt-4"><div class="container">
    <h1>Order history</h1>
    <p class="sample-note">These records are demonstration orders.</p>
    @foreach ($orders as $order)
        <article class="panel p-3 mb-3">
            <a href="{{ route('store.order', [$store->slug, $order->order_number]) }}">{{ $order->order_number }}</a>
            <span>{{ ucfirst($order->status) }}</span>
            <strong class="float-end">{{ money($order->total, $order->currency) }}</strong>
        </article>
    @endforeach
    {{ $orders->links() }}
</div></section>
@endsection
