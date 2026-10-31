@extends('layouts.storefront')
@section('title', 'Cart · '.$store->name)
@section('content')
<section class="section pt-4">
    <div class="container">
        <h1>Cart</h1>
        <p class="sample-note">Totals are calculated on the server from catalogue prices.</p>
        <div class="row g-4">
            <div class="col-lg-8">
                @forelse ($quote['cart']->items as $item)
                    <article class="panel p-3 mb-3 d-flex gap-3">
                        <img src="{{ $item->product->primaryImageUrl() }}" alt="" width="96" height="120" style="object-fit:cover;border-radius:.6rem">
                        <div class="flex-grow-1">
                            <h2 class="h5 mb-1"><a href="{{ route('store.product', [$store->slug, $item->product->slug]) }}">{{ $item->product->name }}</a></h2>
                            <p class="mb-1">{{ $item->variant?->label() }}</p>
                            <p class="mb-2">{{ money($item->unit_price, $store->currency) }} <span class="sample-note">Sample price</span></p>
                            <form class="d-flex gap-2" method="post" action="{{ route('store.cart.update', $store->slug) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <input class="form-control" style="max-width:5rem" type="number" name="quantity" min="1" max="20" value="{{ $item->quantity }}">
                                <button class="btn btn-outline-dark">Update</button>
                            </form>
                        </div>
                        <div>
                            <strong>{{ money($item->lineTotal(), $store->currency) }}</strong>
                            <form method="post" action="{{ route('store.cart.remove', $store->slug) }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <button class="btn btn-link px-0">Remove</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p>Your cart is empty. <a href="{{ route('store.shop', $store->slug) }}">Continue shopping</a>.</p>
                @endforelse
            </div>
            <aside class="col-lg-4">
                <div class="summary p-3">
                    <h2 class="h5">Order summary</h2>
                    <p class="d-flex justify-content-between"><span>Subtotal</span><span>{{ money($quote['subtotal'], $store->currency) }}</span></p>
                    <p class="d-flex justify-content-between"><span>Discount</span><span>{{ money($quote['discount'], $store->currency) }}</span></p>
                    <form method="post" action="{{ route('store.cart.coupon', $store->slug) }}" class="mb-3">
                        @csrf
                        <label class="form-label" for="code">Demonstration code</label>
                        <div class="d-flex gap-2">
                            <input class="form-control" id="code" name="code" placeholder="{{ $coupons->first()->code ?? 'CODE' }}">
                            <button class="btn btn-outline-dark">Apply</button>
                        </div>
                    </form>
                    @if ($coupons->isNotEmpty())
                        <p class="small">Try @foreach ($coupons as $coupon)<span class="credential">{{ $coupon->code }}</span> @endforeach</p>
                    @endif
                    <a class="btn btn-outline-dark w-100 mb-2" href="{{ route('store.shop', $store->slug) }}">Continue shopping</a>
                    <a class="btn btn-accent w-100" href="{{ route('store.checkout', $store->slug) }}">Checkout</a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
