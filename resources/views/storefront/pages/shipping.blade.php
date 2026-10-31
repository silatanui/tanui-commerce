@extends('layouts.storefront')
@section('title', 'Shipping · '.$store->name)
@section('content')
<section class="content-page">
    <div class="container">
        <p class="eyebrow">Delivery</p>
        <h1>Shipping information</h1>
        <p class="lede col-lg-8">Checkout already offers sample shipping methods for {{ $store->name }}. Timelines and costs below are for demonstration only — nothing is dispatched.</p>

        <div class="content-cards mt-4">
            @foreach ($methods as $key => $method)
                <article class="content-card">
                    <p class="eyebrow mb-1">{{ strtoupper($key) }}</p>
                    <h2 class="h5">{{ $method['label'] }}</h2>
                    <p class="mb-2">{{ $method['detail'] }}</p>
                    <strong>{{ money($method['cost'], $store->currency) }}</strong>
                </article>
            @endforeach
        </div>

        <div class="content-note mt-4">
            <h2 class="h5">What to expect in a live build</h2>
            <p class="mb-0">A production shop would connect carrier rates, collection points and tracking notifications. Here those steps stay fictional so you can review the flow without real logistics.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a class="btn btn-accent" href="{{ route('store.checkout', $store->slug) }}">Go to checkout</a>
            <a class="btn btn-outline-dark" href="{{ route('store.returns', $store->slug) }}">Returns policy</a>
        </div>
    </div>
</section>
@endsection
