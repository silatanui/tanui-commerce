<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tanui Commerce')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/platform.css') }}?v={{ @filemtime(public_path('css/platform.css')) ?: time() }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="platform">
    <header class="platform-nav">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="brand-lockup" href="{{ route('home') }}" aria-label="Tanui Commerce home">
                    <img class="brand-lockup__mark" src="{{ asset('demo-assets/brand/tanui-mark.svg') }}" alt="" width="36" height="36">
                    <span class="brand-lockup__word">Tanui <span>Commerce</span></span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#platformNav" aria-label="Open menu"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="platformNav">
                    <ul class="navbar-nav ms-auto gap-lg-2">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') || request()->routeIs('projects') ? 'active' : '' }}" href="{{ route('home') }}">Stores</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    @yield('content')
    <footer class="platform-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-3 align-items-center">
            <div class="footer-brand">
                <img src="{{ asset('demo-assets/brand/tanui-mark.svg') }}" alt="" width="28" height="28">
                <div>
                    <strong>Tanui Commerce</strong>
                    <p class="mb-0">Ecommerce solutions for modern businesses.</p>
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('home') }}">Stores</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
