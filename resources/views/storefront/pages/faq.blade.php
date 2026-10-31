@extends('layouts.storefront')
@section('title', 'Help & FAQ · '.$store->name)
@section('content')
<section class="content-page">
    <div class="container">
        <p class="eyebrow">Help</p>
        <h1>FAQ</h1>
        <p class="lede col-lg-8">Straight answers about this demonstration storefront — accounts, carts, the Store assistant and checkout.</p>

        <div class="faq-list mt-4">
            @foreach ($items as $item)
                <details class="faq-item">
                    <summary>{{ $item['q'] }}</summary>
                    <p>{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Continue shopping</a>
            <a class="btn btn-outline-dark" href="{{ route('store.shipping', $store->slug) }}">Shipping info</a>
            <a class="btn btn-outline-dark" href="{{ route('contact') }}">Contact Tanui Commerce</a>
        </div>
    </div>
</section>
@endsection
