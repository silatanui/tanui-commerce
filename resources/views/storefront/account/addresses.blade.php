@extends('layouts.storefront')
@section('title', 'Addresses')
@section('content')
<section class="section pt-4"><div class="container">
    <h1>Addresses</h1>
    <div class="row g-4">
        <div class="col-lg-5">
            <form class="panel p-3" method="post" action="{{ route('store.account.addresses.store', $store->slug) }}">
                @csrf
                <label class="form-label" for="full_name">Full name</label>
                <input class="form-control mb-2" id="full_name" name="full_name" required>
                <label class="form-label" for="phone">Phone</label>
                <input class="form-control mb-2" id="phone" name="phone">
                <label class="form-label" for="address_line">Address</label>
                <input class="form-control mb-2" id="address_line" name="address_line" required>
                <label class="form-label" for="city">City</label>
                <input class="form-control mb-2" id="city" name="city" required>
                <label class="form-label" for="postal_code">Postal code</label>
                <input class="form-control mb-2" id="postal_code" name="postal_code">
                <label class="form-label" for="country">Country</label>
                <input class="form-control mb-2" id="country" name="country" value="Kenya" required>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default"><label class="form-check-label" for="is_default">Default</label></div>
                <button class="btn btn-accent">Save address</button>
            </form>
        </div>
        <div class="col-lg-7">
            @foreach ($addresses as $address)
                <article class="panel p-3 mb-3">
                    <strong>{{ $address->full_name }}</strong>
                    @if ($address->is_default)<span class="sample-note">Default</span>@endif
                    <p class="mb-2">{{ $address->address_line }}, {{ $address->city }} {{ $address->postal_code }}, {{ $address->country }}</p>
                    <form method="post" action="{{ route('store.account.addresses.destroy', [$store->slug, $address]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-dark">Remove</button></form>
                </article>
            @endforeach
        </div>
    </div>
</div></section>
@endsection
