@extends('layouts.storefront')
@section('title', $title.' · '.$store->name)
@section('content')
<section class="coming-soon">
    <div class="container">
        <p class="eyebrow">Coming soon</p>
        <h1>{{ $title }}</h1>
        <p class="lede col-lg-7">{{ $summary }}</p>
        <p class="col-lg-7">{{ $note }}</p>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a class="btn btn-accent" href="{{ route('store.shop', $store->slug) }}">Continue shopping</a>
            <a class="btn btn-outline-dark" href="{{ route('store.home', $store->slug) }}">Back to store home</a>
        </div>
        <p class="sample-note mt-4 mb-0">Demonstration feature placeholder · no real service is available here.</p>
    </div>
</section>
@endsection
