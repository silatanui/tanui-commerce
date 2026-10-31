@extends('layouts.admin')
@section('title', 'Store settings')
@section('content')
@include('admin.partials.form-head', [
    'title' => 'Store configuration',
    'subtitle' => 'Brand, contact, shipping and merchandising assets for '.$store->name.'. AI can draft tagline, description and footer copy.',
    'aiResource' => 'settings',
])

<div class="af-shell">
    <form class="af-form" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <section class="af-section">
            <h2>Brand</h2>
            <p>Public identity for the storefront.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $store->name) }}" required>
                </div>
                <div class="col-md-6">
                    <div class="af-field-label">
                        <label for="tagline">Tagline</label>
                        <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="settings">AI suggest</button>
                    </div>
                    <input class="form-control" id="tagline" name="tagline" value="{{ old('tagline', $store->setting('tagline')) }}">
                </div>
                <div class="col-12">
                    <div class="af-field-label">
                        <label for="description">Description</label>
                        <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="settings">AI suggest</button>
                    </div>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $store->description) }}</textarea>
                </div>
                <div class="col-md-3"><label class="form-label" for="primary_color">Primary colour</label><input class="form-control" id="primary_color" name="primary_color" value="{{ old('primary_color', $store->primary_color) }}" required></div>
                <div class="col-md-3"><label class="form-label" for="secondary_color">Secondary colour</label><input class="form-control" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $store->secondary_color) }}" required></div>
                <div class="col-md-3"><label class="form-label" for="currency">Currency</label><select class="form-select" id="currency" name="currency">@foreach (config('commerce.currencies') as $code => $symbol)<option value="{{ $code }}" @selected(old('currency', $store->currency) === $code)>{{ $code }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label" for="low_stock_threshold">Low stock</label><input class="form-control" id="low_stock_threshold" type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $store->setting('low_stock_threshold', 5)) }}" required></div>
            </div>
        </section>

        <section class="af-section">
            <h2>Contact & footer</h2>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" value="{{ old('email', $store->setting('email')) }}"></div>
                <div class="col-md-4"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $store->setting('phone')) }}"></div>
                <div class="col-md-4"><label class="form-label" for="hours">Hours</label><input class="form-control" id="hours" name="hours" value="{{ old('hours', $store->setting('hours')) }}"></div>
                <div class="col-12"><label class="form-label" for="address">Address</label><input class="form-control" id="address" name="address" value="{{ old('address', $store->setting('address')) }}"></div>
                <div class="col-12">
                    <div class="af-field-label">
                        <label for="footer_text">Footer</label>
                        <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="settings">AI suggest</button>
                    </div>
                    <textarea class="form-control" id="footer_text" name="footer_text" rows="3">{{ old('footer_text', $store->setting('footer_text')) }}</textarea>
                </div>
            </div>
        </section>

        <section class="af-section">
            <h2>Shipping samples</h2>
            @php $shipping = $store->setting('shipping_costs', []); @endphp
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="shipping_standard">Standard</label><input class="form-control" id="shipping_standard" name="shipping_standard" type="number" step="0.01" value="{{ old('shipping_standard', $shipping['standard'] ?? 350) }}" required></div>
                <div class="col-md-4"><label class="form-label" for="shipping_express">Express</label><input class="form-control" id="shipping_express" name="shipping_express" type="number" step="0.01" value="{{ old('shipping_express', $shipping['express'] ?? 750) }}" required></div>
                <div class="col-md-4"><label class="form-label" for="shipping_pickup">Collection</label><input class="form-control" id="shipping_pickup" name="shipping_pickup" type="number" step="0.01" value="{{ old('shipping_pickup', $shipping['pickup'] ?? 0) }}" required></div>
            </div>
        </section>

        <section class="af-section">
            <h2>Assets</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="logo">Logo</label>
                    <input class="form-control" id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif">
                    <div class="settings-asset-preview">
                        <img src="{{ $store->logoUrl() }}" alt="Current logo">
                        <span class="small text-muted">Current storefront logo</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="favicon">Favicon</label>
                    <input class="form-control" id="favicon" type="file" name="favicon" accept="image/png,image/jpeg,image/webp,image/gif,image/x-icon">
                    <div class="settings-asset-preview">
                        <img src="{{ $store->favicon ? asset($store->favicon) : asset('demo-assets/stores/'.$store->slug.'-mark.svg') }}" alt="Current favicon">
                        <span class="small text-muted">Current browser icon</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="af-actions">
            <button class="btn-admin-dark" type="submit">Save configuration</button>
        </div>
    </form>

    <aside class="af-side">
        <h2>Tips</h2>
        <ul>
            <li>Tagline should fit one line in the header.</li>
            <li>Colours drive accents across the storefront theme.</li>
            <li>Use AI fill when you need fresh demo copy fast.</li>
        </ul>
    </aside>
</div>

<div class="af-form" style="margin-top:1rem">
    <section class="af-section" style="border-bottom:0;margin:0;padding:0">
        <h2>Banners</h2>
        <p>Hero and promo strips for the storefront.</p>
        <div class="af-banner-list">
            @forelse ($banners as $banner)
                <div class="af-banner-item">
                    <div>
                        <strong>{{ $banner->title }}</strong>
                        <div class="af-hint">{{ $banner->position }} · {{ $banner->subtitle }}</div>
                    </div>
                    <form method="post" action="{{ route('admin.banners.destroy', $banner) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Remove</button></form>
                </div>
            @empty
                <p class="text-muted mb-0">No banners yet.</p>
            @endforelse
        </div>

        <form method="post" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="af-field-label">
                        <label class="form-label" for="banner_title">Title</label>
                        <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="banner">AI suggest</button>
                    </div>
                    <input class="form-control" id="banner_title" name="title" placeholder="Title" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="banner_subtitle">Subtitle</label><input class="form-control" id="banner_subtitle" name="subtitle" placeholder="Subtitle"></div>
                <div class="col-md-2"><label class="form-label" for="banner_position">Position</label><select class="form-select" id="banner_position" name="position"><option value="hero">Hero</option><option value="promo">Promo</option></select></div>
                <div class="col-md-2"><label class="form-label" for="banner_image">Image</label><input class="form-control" id="banner_image" type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif"></div>
            </div>
            <div class="af-actions">
                <button class="btn-admin" type="submit">Add banner</button>
            </div>
        </form>
    </section>
</div>
@endsection
