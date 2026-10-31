@extends('layouts.storefront')
@section('title', 'Returns · '.$store->name)
@section('content')
<section class="content-page">
    <div class="container">
        <p class="eyebrow">Aftercare</p>
        <h1>Returns &amp; exchanges</h1>
        <p class="lede col-lg-8">{{ $store->name }} does not process real returns. The policy below shows how a polished customer journey would read in a live storefront.</p>

        <ol class="content-steps mt-4">
            <li>
                <strong>Start from your order</strong>
                <span>Customers would open the order confirmation or account history and choose the item to return.</span>
            </li>
            <li>
                <strong>Choose a reason</strong>
                <span>Size, colour, damaged in transit or changed mind — captured for operations analytics.</span>
            </li>
            <li>
                <strong>Print a label / book collection</strong>
                <span>Carrier integrations would issue a prepaid label or schedule a pickup window.</span>
            </li>
            <li>
                <strong>Refund or exchange</strong>
                <span>Once the warehouse receives the item, the payment provider issues a refund or stock is reserved for an exchange.</span>
            </li>
        </ol>

        <div class="content-note mt-4">
            <p class="mb-0">In this portfolio build, orders remain sample records. Use the Store assistant or admin dashboard to inspect order status without moving physical goods.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a class="btn btn-accent" href="{{ route('store.faq', $store->slug) }}">Read the FAQ</a>
            <a class="btn btn-outline-dark" href="{{ route('store.account.orders', $store->slug) }}">View demo orders</a>
        </div>
    </div>
</section>
@endsection
