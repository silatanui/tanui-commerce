@extends('layouts.admin')
@section('title', 'Coupons')
@section('content')
<div class="page-head">
    <h1>Coupons</h1>
    <a class="btn-page" href="{{ route('admin.coupons.create') }}">New coupon</a>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th>Code</th>
                <th>Type</th>
                <th>Value</th>
                <th>Minimum</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($coupons as $coupon)
                <tr>
                    <td><strong>{{ $coupon->code }}</strong></td>
                    <td>{{ $coupon->discount_type }}</td>
                    <td>{{ $coupon->discount_value }}</td>
                    <td>{{ $coupon->minimum_amount }}</td>
                    <td><span class="status-pill is-{{ $coupon->status }}">{{ $coupon->status }}</span></td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a class="btn-table btn-table-primary" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
                            <form method="post" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete this coupon?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn-table btn-table-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
