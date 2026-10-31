@extends('layouts.storefront')
@section('title', 'Sign in · '.$store->name)
@section('content')
<section class="section"><div class="container col-lg-5">
    <h1>Customer sign in</h1>
    <p>Use the demonstration customer <span class="credential">amina@example.test</span> / <span class="credential">password</span>. Administrators sign in from the dashboard.</p>
    <form class="panel p-4" method="post" action="{{ route('store.login.attempt', $store->slug) }}">
        @csrf
        <label class="form-label" for="email">Email</label>
        <input class="form-control mb-3" id="email" type="email" name="email" value="{{ old('email') }}" required>
        <label class="form-label" for="password">Password</label>
        <input class="form-control mb-3" id="password" type="password" name="password" required>
        <button class="btn btn-accent">Sign in</button>
        <a class="ms-3" href="{{ route('store.register', $store->slug) }}">Create a demonstration account</a>
    </form>
</div></section>
@endsection
