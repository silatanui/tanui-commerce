@extends('layouts.admin')
@section('content')
<h1 class="h3">Reset demonstration data</h1>
<p>This restores products, categories, brands, inventory, customers, orders and store settings to the original seeded state. It signs you out. Visitors cannot run this action.</p>
<form class="card card-body" method="post" action="{{ route('admin.demo.reset.store') }}">
    @csrf
    <label class="form-label" for="confirmation">Type {{ config('demo.reset_phrase') }} to confirm</label>
    <input class="form-control mb-3" id="confirmation" name="confirmation" required>
    <button class="btn btn-danger">Restore original data</button>
</form>
@endsection
