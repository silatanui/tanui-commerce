@extends('layouts.storefront')
@section('title', 'Checkout · '.$store->name)
@section('content')
<section class="section pt-4">
    <div class="container col-lg-8">
        <h1>Checkout</h1>
        <x-demo-notice />
        <p class="mt-3">Enter fictional customer details. Card numbers are not accepted and will be rejected.</p>
        <form method="post" action="{{ route('store.checkout.place', $store->slug) }}" class="panel p-4">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="full_name">Full name</label><input class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $address->full_name ?? auth()->user()->name ?? '') }}" required></div>
                <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required></div>
                <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $address->phone ?? auth()->user()->phone ?? '') }}" required></div>
                <div class="col-12"><label class="form-label" for="address_line">Address</label><input class="form-control" id="address_line" name="address_line" value="{{ old('address_line', $address->address_line ?? '') }}" required></div>
                <div class="col-md-4"><label class="form-label" for="city">City</label><input class="form-control" id="city" name="city" value="{{ old('city', $address->city ?? 'Nairobi') }}" required></div>
                <div class="col-md-4"><label class="form-label" for="postal_code">Postal code</label><input class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}"></div>
                <div class="col-md-4"><label class="form-label" for="country">Country</label><input class="form-control" id="country" name="country" value="{{ old('country', $address->country ?? 'Kenya') }}" required></div>
            </div>
            <fieldset class="mt-4">
                <legend class="h5">Delivery option</legend>
                @foreach ($shippingMethods as $key => $method)
                    <label class="d-flex justify-content-between border rounded p-3 mb-2">
                        <span><input type="radio" name="shipping_method" value="{{ $key }}" @checked(old('shipping_method', 'standard') === $key) required> {{ $method['label'] }}<br><small>{{ $method['detail'] }}</small></span>
                        <strong>{{ money($method['cost'], $store->currency) }}</strong>
                    </label>
                @endforeach
            </fieldset>
            <div class="summary p-3 mt-3">
                <p class="d-flex justify-content-between"><span>Subtotal</span><span>{{ money($quote['subtotal'], $store->currency) }}</span></p>
                <p class="d-flex justify-content-between"><span>Discount</span><span>{{ money($quote['discount'], $store->currency) }}</span></p>
                <p class="mb-0 small">Shipping is added from the option you select. The server ignores any price sent by the browser.</p>
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="accept_demo" value="1" id="accept_demo" required>
                <label class="form-check-label" for="accept_demo">I understand this is a demonstration order. No payment will be taken and nothing will be delivered.</label>
            </div>
            <button class="btn btn-accent mt-3">Confirm demonstration order</button>
        </form>
    </div>
</section>
@endsection
