@extends('layouts.platform')
@section('title', 'About · Tanui Commerce')
@section('content')
    <section class="section">
        <div class="container col-lg-8">
            <p class="eyebrow">About the project</p>
            <h2>A portfolio platform for complete ecommerce work.</h2>
            <p class="mt-3">Tanui Commerce shows how one Laravel application can present four different businesses without copying the codebase four times. Storefront themes change the presentation. The catalogue, cart, checkout, inventory and administration stay shared.</p>
            <p>Every price is a sample. Checkout creates a demonstration order number and states that no payment was processed. Administrator registration is not public.</p>
            <p class="mb-0">The architecture is meant to be adapted later for a real client project: same modules, different branding, and a genuine payment provider only when a business actually needs one.</p>
        </div>
    </section>
@endsection
