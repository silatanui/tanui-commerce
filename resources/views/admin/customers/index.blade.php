@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
<div class="page-head">
    <h1>Customers</h1>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Orders in this store</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->email }}</td>
                    <td><span class="status-pill is-{{ $customer->status }}">{{ $customer->status }}</span></td>
                    <td>{{ $customer->orders_count }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a class="btn-table btn-table-primary" href="{{ route('admin.customers.show', $customer) }}">View</a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
