@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Coupons</h1><a class="btn btn-dark" href="{{ route('admin.coupons.create') }}">New coupon</a></div>
<table class="table"><thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Minimum</th><th>Status</th></tr></thead><tbody>
@foreach ($coupons as $coupon)
<tr><td><a href="{{ route('admin.coupons.edit', $coupon) }}">{{ $coupon->code }}</a></td><td>{{ $coupon->discount_type }}</td><td>{{ $coupon->discount_value }}</td><td>{{ $coupon->minimum_amount }}</td><td>{{ $coupon->status }}</td></tr>
@endforeach
</tbody></table>
{{ $coupons->links() }}
@endsection
