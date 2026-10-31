@extends('layouts.admin')
@section('content')
<h1 class="h3">Store configuration</h1>
<form class="card card-body" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $store->name) }}" required></div>
        <div class="col-md-6"><label class="form-label">Tagline</label><input class="form-control" name="tagline" value="{{ old('tagline', $store->setting('tagline')) }}"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $store->description) }}</textarea></div>
        <div class="col-md-3"><label class="form-label">Primary colour</label><input class="form-control" name="primary_color" value="{{ old('primary_color', $store->primary_color) }}" required></div>
        <div class="col-md-3"><label class="form-label">Secondary colour</label><input class="form-control" name="secondary_color" value="{{ old('secondary_color', $store->secondary_color) }}" required></div>
        <div class="col-md-3"><label class="form-label">Currency</label><select class="form-select" name="currency">@foreach (config('commerce.currencies') as $code => $symbol)<option value="{{ $code }}" @selected(old('currency', $store->currency) === $code)>{{ $code }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Low stock</label><input class="form-control" type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $store->setting('low_stock_threshold', 5)) }}" required></div>
        <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="{{ old('email', $store->setting('email')) }}"></div>
        <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $store->setting('phone')) }}"></div>
        <div class="col-md-4"><label class="form-label">Hours</label><input class="form-control" name="hours" value="{{ old('hours', $store->setting('hours')) }}"></div>
        <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address', $store->setting('address')) }}"></div>
        <div class="col-12"><label class="form-label">Footer</label><textarea class="form-control" name="footer_text">{{ old('footer_text', $store->setting('footer_text')) }}</textarea></div>
        @php $shipping = $store->setting('shipping_costs', []); @endphp
        <div class="col-md-4"><label class="form-label">Standard delivery</label><input class="form-control" name="shipping_standard" type="number" step="0.01" value="{{ old('shipping_standard', $shipping['standard'] ?? 350) }}" required></div>
        <div class="col-md-4"><label class="form-label">Express delivery</label><input class="form-control" name="shipping_express" type="number" step="0.01" value="{{ old('shipping_express', $shipping['express'] ?? 750) }}" required></div>
        <div class="col-md-4"><label class="form-label">Collection</label><input class="form-control" name="shipping_pickup" type="number" step="0.01" value="{{ old('shipping_pickup', $shipping['pickup'] ?? 0) }}" required></div>
        <div class="col-md-6"><label class="form-label">Logo</label><input class="form-control" type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif"></div>
        <div class="col-md-6"><label class="form-label">Favicon</label><input class="form-control" type="file" name="favicon" accept="image/png,image/jpeg,image/webp,image/gif,image/x-icon"></div>
    </div>
    <button class="btn btn-dark mt-3">Save configuration</button>
</form>
<h2 class="h5 mt-4">Banners</h2>
@foreach ($banners as $banner)
    <div class="d-flex justify-content-between border rounded p-2 mb-2"><span>{{ $banner->title }} · {{ $banner->position }}</span><form method="post" action="{{ route('admin.banners.destroy', $banner) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form></div>
@endforeach
<form class="card card-body" method="post" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-2">
        <div class="col-md-4"><input class="form-control" name="title" placeholder="Title" required></div>
        <div class="col-md-4"><input class="form-control" name="subtitle" placeholder="Subtitle"></div>
        <div class="col-md-2"><select class="form-select" name="position"><option value="hero">Hero</option><option value="promo">Promo</option></select></div>
        <div class="col-md-2"><input class="form-control" type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif"></div>
    </div>
    <button class="btn btn-outline-dark mt-2">Add banner</button>
</form>
@endsection
