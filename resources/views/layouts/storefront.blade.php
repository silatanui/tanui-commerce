@php
    $accent = safe_hex($store->primary_color, '#16324F');
    $secondary = safe_hex($store->secondary_color, '#111111');
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $store->slug }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $store->name)</title>
    <link rel="icon" href="{{ $store->favicon ? asset($store->favicon) : asset('demo-assets/stores/'.$store->slug.'-mark.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|outfit:500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/storefront.css') }}?v=20261031y" rel="stylesheet">
    <link href="{{ asset('css/themes/'.$store->slug.'.css') }}?v=20261031y" rel="stylesheet">
    <style>
        [data-theme="{{ $store->slug }}"] { --accent: {{ $accent }}; --ink-strong: {{ $secondary }}; }
    </style>
</head>
<body class="storefront">
    <x-demo-notice />
    @include('storefront.partials.nav')
    <main class="store-main">
        @if (session('success') || session('status') || session('error') || $errors->any())
            <div class="container pt-3">
                <x-alerts />
            </div>
        @endif
        @yield('content')
    </main>
    @include('storefront.partials.footer')
    @include('storefront.partials.cart-drawer')
    @include('storefront.partials.ai-chat')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    <script src="{{ asset('js/storefront.js') }}?v=20261031p"></script>
    <script src="{{ asset('js/ai-chat.js') }}?v=20261031p"></script>
    @stack('scripts')
</body>
</html>
