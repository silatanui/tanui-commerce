@extends('layouts.storefront')
@section('title', 'Account · '.$store->name)
@section('content')
<section class="section pt-4"><div class="container">
    <h1>Hello, {{ auth()->user()->name }}</h1>
    <div class="row g-4">
        <aside class="col-md-3 account-nav">
            <a class="active" href="{{ route('store.account', $store->slug) }}">Overview</a>
            <a href="{{ route('store.account.profile', $store->slug) }}">Profile</a>
            <a href="{{ route('store.account.orders', $store->slug) }}">Orders</a>
            <a href="{{ route('store.account.addresses', $store->slug) }}">Addresses</a>
            <a href="{{ route('store.wishlist', $store->slug) }}">Wishlist</a>
            <form method="post" action="{{ route('store.logout', $store->slug) }}">@csrf<button class="btn btn-link px-0">Sign out</button></form>
        </aside>
        <div class="col-md-9">
            <h2 class="h4">Recent demonstration orders</h2>
            @forelse ($orders as $order)
                <p><a href="{{ route('store.order', [$store->slug, $order->order_number]) }}">{{ $order->order_number }}</a> · {{ ucfirst($order->status) }} · {{ money($order->total, $order->currency) }}</p>
            @empty
                <p>No orders in this store yet.</p>
            @endforelse
        </div>
    </div>
</div></section>
@endsection
