@extends('layouts.platform')
@section('title', 'Tanui Commerce project')
@section('content')
    <section class="section">
        <div class="container">
            <p class="eyebrow">Portfolio project</p>
            <h2>Tanui Commerce</h2>
            <p class="col-lg-8">An interactive multi-store ecommerce demonstration for fashion, electronics, furniture and grocery. The live stores and the administration dashboard are part of this application.</p>
            <div class="row g-4 mt-2">
                @foreach ($stores as $store)
                    <div class="col-md-6">
                        <article class="panel">
                            <p class="eyebrow">{{ $store->slug }}</p>
                            <h3>{{ $store->name }}</h3>
                            <p class="muted">{{ $store->description }}</p>
                            <a class="btn btn-dark" href="{{ route('store.home', $store->slug) }}">Open storefront</a>
                        </article>
                    </div>
                @endforeach
            </div>
            <div class="mt-5">
                <h3>Technology</h3>
                <div class="stack-grid">
                    @foreach (['Laravel', 'PHP', 'MySQL', 'Blade', 'Bootstrap 5', 'Chart.js'] as $item)
                        <div class="panel text-center">{{ $item }}</div>
                    @endforeach
                </div>
            </div>
            <div class="mt-5">
                <h3>Architecture</h3>
                <p>Store resolution middleware loads the active shop. Eloquent relationships keep products, carts and orders attached to that store. Checkout recalculates prices on the server and refuses card data. Policies keep administration inside the selected store.</p>
            </div>
        </div>
    </section>
@endsection
