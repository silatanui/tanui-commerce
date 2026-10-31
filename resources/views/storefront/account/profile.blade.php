@extends('layouts.storefront')
@section('title', 'Profile')
@section('content')
<section class="section pt-4"><div class="container col-lg-6">
    <h1>Profile</h1>
    <form class="panel p-4" method="post" action="{{ route('store.account.profile.update', $store->slug) }}">
        @csrf
        @method('PUT')
        <label class="form-label" for="name">Name</label>
        <input class="form-control mb-3" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
        <label class="form-label" for="email">Email</label>
        <input class="form-control mb-3" id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
        <label class="form-label" for="phone">Phone</label>
        <input class="form-control mb-3" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
        <label class="form-label" for="password">New password</label>
        <input class="form-control mb-3" id="password" type="password" name="password">
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input class="form-control mb-3" id="password_confirmation" type="password" name="password_confirmation">
        <button class="btn btn-accent">Save</button>
    </form>
</div></section>
@endsection
