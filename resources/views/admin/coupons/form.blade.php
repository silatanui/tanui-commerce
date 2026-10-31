@extends('layouts.admin')
@section('title', $coupon->exists ? 'Edit coupon' : 'New coupon')
@section('content')
@include('admin.partials.form-head', [
    'title' => $coupon->exists ? 'Edit coupon' : 'New coupon',
    'subtitle' => 'Create a demonstration discount with clear code, value and schedule. Use AI fill for a ready-to-edit draft.',
    'backRoute' => route('admin.coupons.index'),
    'backLabel' => 'Coupons',
    'aiResource' => 'coupon',
])

<div class="af-shell">
    <form class="af-form" method="post" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
        @csrf
        @if ($coupon->exists) @method('PUT') @endif

        <section class="af-section">
            <h2>Offer basics</h2>
            <p>Code and discount mechanics shoppers will see at checkout.</p>
            <div class="af-field">
                <div class="af-field-label">
                    <label for="code">Code</label>
                    <button type="button" class="af-ai-btn" data-ai-field-btn data-ai-resource="coupon">AI suggest</button>
                </div>
                <input class="form-control" id="code" name="code" value="{{ old('code', $coupon->code) }}" required>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="discount_type">Type</label>
                    <select class="form-select" id="discount_type" name="discount_type">
                        <option value="percentage" @selected(old('discount_type', $coupon->discount_type) === 'percentage')>Percentage</option>
                        <option value="fixed" @selected(old('discount_type', $coupon->discount_type) === 'fixed')>Fixed amount</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="discount_value">Value</label>
                    <input class="form-control" id="discount_value" name="discount_value" type="number" step="0.01" value="{{ old('discount_value', $coupon->discount_value) }}" required>
                </div>
            </div>
        </section>

        <section class="af-section">
            <h2>Rules & schedule</h2>
            <p>Optional minimum spend and active window.</p>
            <div class="af-field">
                <label class="form-label" for="minimum_amount">Minimum order</label>
                <input class="form-control" id="minimum_amount" name="minimum_amount" type="number" step="0.01" value="{{ old('minimum_amount', $coupon->minimum_amount) }}">
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="starts_at">Starts</label>
                    <input class="form-control" id="starts_at" type="date" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="expires_at">Ends</label>
                    <input class="form-control" id="expires_at" type="date" name="expires_at" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="af-field" style="margin-top:.85rem">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" @selected(old('status', $coupon->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $coupon->status) === 'inactive')>Inactive</option>
                </select>
            </div>
        </section>

        <div class="af-actions">
            <button class="btn-admin-dark" type="submit">Save coupon</button>
            <a class="btn-admin" href="{{ route('admin.coupons.index') }}">Cancel</a>
        </div>
    </form>

    <aside class="af-side">
        <h2>Tips</h2>
        <ul>
            <li>Use memorable codes like WELCOME10.</li>
            <li>Percentage offers need a sensible minimum order.</li>
            <li>AI fill drafts the whole offer; edit before saving.</li>
        </ul>
    </aside>
</div>

@if ($coupon->exists)
    <form class="mt-3" method="post" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete this coupon?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">Delete coupon</button>
    </form>
@endif
@endsection
