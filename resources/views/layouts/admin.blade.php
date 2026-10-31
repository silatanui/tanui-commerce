<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') · Tanui Commerce</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body class="admin">
    @php
        $pendingReviews = $pendingReviews ?? 0;
        $mainLinks = [
            'admin.dashboard' => ['Dashboard', 'layout-dashboard'],
            'admin.products.index' => ['Product', 'package'],
            'admin.orders.index' => ['Order', 'receipt'],
            'admin.customers.index' => ['Customer', 'users'],
        ];
        $otherLinks = [
            'admin.categories.index' => ['Categories', 'folders'],
            'admin.brands.index' => ['Brands', 'badge'],
            'admin.inventory.index' => ['Inventory', 'warehouse'],
            'admin.coupons.index' => ['Coupons', 'ticket'],
            'admin.reviews.index' => ['Reviews', 'star', $pendingReviews],
            'admin.analytics' => ['Analytics', 'chart-column'],
            'admin.settings' => ['Integration', 'puzzle'],
        ];
        $isActive = function (string $route): bool {
            $pattern = str_replace('.index', '.*', $route);

            return request()->routeIs($pattern) || request()->routeIs($route);
        };
    @endphp
    <div class="admin-shell">
        <aside class="sidebar">
            <a class="brand-lockup" href="{{ route('admin.dashboard') }}">
                <span class="brand-mark">TC</span>
                <span>
                    <strong>Tanui Admin</strong>
                    <span>Commerce demonstration</span>
                </span>
            </a>

            <div class="nav-group">
                <span class="nav-label">Main</span>
                @foreach ($mainLinks as $route => [$label, $icon])
                    <a class="nav-link {{ $isActive($route) ? 'active' : '' }}" href="{{ route($route) }}">
                        <i data-lucide="{{ $icon }}"></i> {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="nav-group">
                <span class="nav-label">Other</span>
                @foreach ($otherLinks as $route => $item)
                    @php [$label, $icon, $badge] = array_pad($item, 3, null); @endphp
                    <a class="nav-link {{ $isActive($route) ? 'active' : '' }}" href="{{ route($route) }}">
                        <i data-lucide="{{ $icon }}"></i> {{ $label }}
                        @if ($badge)
                            <span class="nav-badge">{{ $badge }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="nav-group">
                <span class="nav-label">Account</span>
                <a class="nav-link {{ $isActive('admin.stores') ? 'active' : '' }}" href="{{ route('admin.stores') }}">
                    <i data-lucide="store"></i> Stores
                </a>
                <a class="nav-link" href="{{ route('home') }}">
                    <i data-lucide="arrow-up-right"></i> Platform
                </a>
                <a class="nav-link {{ $isActive('admin.demo.reset') ? 'active' : '' }}" href="{{ route('admin.demo.reset') }}">
                    <i data-lucide="rotate-ccw"></i> Reset demo
                </a>
                <a class="nav-link {{ $isActive('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                    <i data-lucide="settings"></i> Setting
                </a>
            </div>

            <div class="sidebar-user">
                <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                    <span>{{ auth()->user()->email ?? 'admin@tanuicommerce.test' }}</span>
                </div>
            </div>
        </aside>

        <div class="admin-main">
            <div class="topbar">
                <form class="topbar-search" action="{{ route('admin.products.index') }}" method="get">
                    <i data-lucide="search"></i>
                    <input type="search" name="q" placeholder="Search products" value="{{ request('q') }}" aria-label="Search products">
                </form>
                <div class="topbar-actions">
                    <span class="sim-label">Simulated data</span>
                    <form action="{{ route('admin.stores.switch') }}" method="post" class="store-switch">
                        @csrf
                        <select name="store_id" aria-label="Active store" onchange="this.form.submit()">
                            @foreach ($adminStores ?? [] as $option)
                                <option value="{{ $option->id }}" @selected(($adminStore->id ?? null) === $option->id)>{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    <span class="chip"><i data-lucide="calendar"></i> Last 14 days</span>
                    <a class="btn-admin" href="{{ route('admin.analytics') }}"><i data-lucide="sliders-horizontal"></i> Filter by</a>
                    <a class="btn-admin-dark" href="{{ route('admin.analytics') }}"><i data-lucide="download"></i> Export</a>
                    <form action="{{ route('admin.logout') }}" method="post">
                        @csrf
                        <button class="icon-btn" type="submit" title="Sign out" aria-label="Sign out"><i data-lucide="log-out"></i></button>
                    </form>
                </div>
            </div>
            <x-alerts />
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html>
