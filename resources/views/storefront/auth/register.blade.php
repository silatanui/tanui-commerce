@extends('layouts.storefront')
@section('title', 'Register · '.$store->name)
@section('content')
<section class="section"><div class="container col-lg-5">
    <h1>Create a demonstration account</h1>
    <p>Use fictional details. This does not open a real customer record outside the demo database.</p>
    <form class="panel p-4" method="post" action="{{ route('store.register.store', $store->slug) }}">
        @csrf
        <label class="form-label" for="name">Name</label>
        <input class="form-control mb-3" id="name" name="name" value="{{ old('name') }}" required>
        <label class="form-label" for="email">Email</label>
        <input class="form-control mb-3" id="email" type="email" name="email" value="{{ old('email') }}" required>
        <label class="form-label" for="phone">Phone</label>
        <input class="form-control mb-3" id="phone" name="phone" value="{{ old('phone') }}">
        <label class="form-label" for="password">Password</label>
        <input class="form-control mb-3" id="password" type="password" name="password" required>
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input class="form-control mb-3" id="password_confirmation" type="password" name="password_confirmation" required>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="accept_demo" value="1" id="accept_demo" required><label class="form-check-label" for="accept_demo">I understand this account is for the demonstration only.</label></div>
        <button class="btn btn-accent">Create account</button>
    </form>
</div></section>
@endsection
