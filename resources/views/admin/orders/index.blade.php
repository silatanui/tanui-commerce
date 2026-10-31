@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<div class="page-head">
    <h1>Demonstration orders</h1>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Sample total</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>{{ $order->customer_details['full_name'] ?? 'Guest' }}</td>
                    <td><span class="status-pill is-{{ $order->status }}">{{ $order->status }}</span></td>
                    <td>{{ money($order->total, $order->currency) }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a class="btn-table btn-table-primary" href="{{ route('admin.orders.show', $order) }}">View</a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
