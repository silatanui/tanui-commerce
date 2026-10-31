<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tanui Commerce')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700|syne:600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/platform.css') }}" rel="stylesheet">
</head>
<body class="platform">
    <header class="platform-nav">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="brand-mark fs-3" href="{{ route('home') }}">Tanui <span>Commerce</span></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#platformNav" aria-label="Open menu"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="platformNav">
                    <ul class="navbar-nav ms-auto gap-lg-2">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Platform</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('projects') }}">Stores</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.login') }}">Admin</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    @yield('content')
    <footer class="platform-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-3">
            <div>
                <strong>Tanui Commerce</strong>
                <p class="mb-0">Ecommerce solutions for modern businesses.</p>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('projects') }}">Projects</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
