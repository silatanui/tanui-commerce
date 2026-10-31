@extends('layouts.platform')
@section('title', 'Contact · Tanui Commerce')
@section('content')
    <section class="section">
        <div class="container col-lg-7">
            <p class="eyebrow">Project enquiry</p>
            <h2>Get in touch to discuss your project.</h2>
            <p>Need an ecommerce website for your business? This form is a demonstration. It does not store the message and it does not send email.</p>
            <x-alerts />
            <form method="post" action="{{ route('contact.send') }}" class="panel">
                @csrf
                <div class="mb-3"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" required></div>
                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required></div>
                <div class="mb-3"><label class="form-label" for="message">Project notes</label><textarea class="form-control" id="message" name="message" rows="5" required>{{ old('message') }}</textarea></div>
                <button class="btn btn-dark">Submit demonstration enquiry</button>
            </form>
            <p class="mt-4 muted">Portfolio reference: tanuisila.dev/projects/tanui-commerce</p>
        </div>
    </section>
@endsection
