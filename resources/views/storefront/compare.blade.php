@extends('layouts.storefront')
@section('title', 'Compare · '.$store->name)
@section('content')
<section class="section pt-4"><div class="container">
    <h1>Compare</h1>
    <div class="table-responsive">
        <table class="table bg-white">
            <thead><tr><th></th>@foreach ($products as $product)<th>{{ $product->name }}</th>@endforeach</tr></thead>
            <tbody>
                <tr><th>Sample price</th>@foreach ($products as $product)<td>{{ money($product->price, $store->currency) }}</td>@endforeach</tr>
                <tr><th>Brand</th>@foreach ($products as $product)<td>{{ $product->brand?->name }}</td>@endforeach</tr>
                <tr><th>Stock</th>@foreach ($products as $product)<td>{{ $product->availableStock() }}</td>@endforeach</tr>
                @php $specNames = $products->flatMap(fn ($product) => array_keys($product->specifications ?? []))->unique(); @endphp
                @foreach ($specNames as $name)
                    <tr><th>{{ $name }}</th>@foreach ($products as $product)<td>{{ $product->specifications[$name] ?? '—' }}</td>@endforeach</tr>
                @endforeach
                <tr><th></th>@foreach ($products as $product)<td><form method="post" action="{{ route('store.compare.remove', $store->slug) }}">@csrf @method('DELETE')<input type="hidden" name="product_id" value="{{ $product->id }}"><button class="btn btn-sm btn-outline-dark">Remove</button></form></td>@endforeach</tr>
            </tbody>
        </table>
    </div>
</div></section>
@endsection
