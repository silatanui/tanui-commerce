@extends('layouts.admin')
@section('content')
<h1 class="h3">{{ $coupon->exists ? 'Edit coupon' : 'New coupon' }}</h1>
<form class="card card-body" method="post" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
    @csrf
    @if ($coupon->exists) @method('PUT') @endif
    <label class="form-label">Code</label>
    <input class="form-control mb-3" name="code" value="{{ old('code', $coupon->code) }}" required>
    <label class="form-label">Type</label>
    <select class="form-select mb-3" name="discount_type"><option value="percentage" @selected(old('discount_type', $coupon->discount_type) === 'percentage')>Percentage</option><option value="fixed" @selected(old('discount_type', $coupon->discount_type) === 'fixed')>Fixed amount</option></select>
    <label class="form-label">Value</label>
    <input class="form-control mb-3" name="discount_value" type="number" step="0.01" value="{{ old('discount_value', $coupon->discount_value) }}" required>
    <label class="form-label">Minimum order</label>
    <input class="form-control mb-3" name="minimum_amount" type="number" step="0.01" value="{{ old('minimum_amount', $coupon->minimum_amount) }}">
    <label class="form-label">Starts</label>
    <input class="form-control mb-3" type="date" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d')) }}">
    <label class="form-label">Ends</label>
    <input class="form-control mb-3" type="date" name="expires_at" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d')) }}">
    <label class="form-label">Status</label>
    <select class="form-select mb-3" name="status"><option value="active">Active</option><option value="inactive" @selected(old('status', $coupon->status) === 'inactive')>Inactive</option></select>
    <button class="btn btn-dark">Save</button>
</form>
@if ($coupon->exists)
<form class="mt-3" method="post" action="{{ route('admin.coupons.destroy', $coupon) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">Delete</button></form>
@endif
@endsection
