@extends('layouts.storefront')
@section('title', 'Wishlist · '.$store->name)
@section('content')
<section class="section pt-4"><div class="container">
    <h1>Wishlist</h1>
    @forelse ($items as $item)
        <article class="panel p-3 mb-3 d-flex justify-content-between gap-3">
            <div>
                <h2 class="h5">{{ $item->product->name }}</h2>
                <x-price :amount="$item->product->price" :currency="$store->currency" />
            </div>
            <div class="d-flex gap-2">
                <form method="post" action="{{ route('store.wishlist.move', $store->slug) }}">@csrf<input type="hidden" name="product_id" value="{{ $item->product_id }}"><button class="btn btn-accent">Move to cart</button></form>
                <form method="post" action="{{ route('store.wishlist.remove', $store->slug) }}">@csrf @method('DELETE')<input type="hidden" name="product_id" value="{{ $item->product_id }}"><button class="btn btn-outline-dark">Remove</button></form>
            </div>
        </article>
    @empty
        <p>No saved products in this store.</p>
    @endforelse
</div></section>
@endsection
