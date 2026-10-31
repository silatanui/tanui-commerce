@extends('layouts.admin')
@section('content')
<h1 class="h3">Customers</h1>
<form class="mb-3"><input class="form-control" name="q" value="{{ $search }}" placeholder="Search customers"></form>
<table class="table"><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Orders in this store</th></tr></thead><tbody>
@foreach ($customers as $customer)
<tr><td><a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a></td><td>{{ $customer->email }}</td><td>{{ $customer->status }}</td><td>{{ $customer->orders_count }}</td></tr>
@endforeach
</tbody></table>
{{ $customers->links() }}
@endsection
