<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') · Tanui Commerce</title>
    @php
        $adminStore = $adminStore ?? null;
        $adminFavicon = $adminStore?->favicon
            ? asset($adminStore->favicon)
            : asset('demo-assets/stores/'.($adminStore->slug ?? 'fashion').'-mark.svg');
    @endphp
    <link rel="icon" href="{{ $adminFavicon }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: time() }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('tanui-admin-theme');
                if (theme !== 'dark' && theme !== 'light') {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.classList.toggle('admin-theme-dark', theme === 'dark');
            } catch (e) {}
        })();
    </script>
</head>
<body class="admin" data-ai-suggest-url="{{ route('admin.ai.suggest') }}">
    <script>
        (function () {
            var theme = document.documentElement.getAttribute('data-theme') || 'light';
            document.body.setAttribute('data-theme', theme);
            document.body.classList.toggle('admin-theme-dark', theme === 'dark');
        })();
    </script>
    @php
        $pendingReviews = $pendingReviews ?? 0;
        $mainLinks = [
            'admin.dashboard' => ['Dashboard', 'layout-dashboard'],
            'admin.products.index' => ['Products', 'package'],
            'admin.orders.index' => ['Orders', 'receipt'],
            'admin.customers.index' => ['Customers', 'users'],
        ];
        $otherLinks = [
            'admin.categories.index' => ['Categories', 'folder-tree'],
            'admin.brands.index' => ['Brands', 'award'],
            'admin.inventory.index' => ['Inventory', 'boxes'],
            'admin.coupons.index' => ['Coupons', 'ticket'],
            'admin.posts.index' => ['News', 'newspaper'],
            'admin.reviews.index' => ['Reviews', 'star', $pendingReviews],
            'admin.analytics' => ['Analytics', 'chart-column'],
            'admin.reports' => ['Reports', 'file-text'],
            'admin.assistant' => ['AI Assistant', 'bot'],
            'admin.settings' => ['Integration', 'puzzle'],
        ];
        $isActive = function (string $route): bool {
            $pattern = str_replace('.index', '.*', $route);

            return request()->routeIs($pattern) || request()->routeIs($route);
        };
        $brandLogo = $adminStore?->logo
            ? asset($adminStore->logo)
            : asset('demo-assets/stores/'.($adminStore->slug ?? 'fashion').'-mark.svg');
    @endphp
    <div class="admin-shell">
        <aside class="sidebar">
            <a class="brand-lockup" href="{{ route('admin.dashboard') }}">
                <span class="brand-mark">
                    <img src="{{ $brandLogo }}" alt="" width="28" height="28">
                </span>
                <span class="brand-text">
                    <strong>Tanui Admin</strong>
                    <span>Commerce demonstration</span>
                </span>
            </a>

            <div class="nav-group">
                <span class="nav-label">Main</span>
                @foreach ($mainLinks as $route => [$label, $icon])
                    <a class="nav-link {{ $isActive($route) ? 'active' : '' }}" href="{{ route($route) }}">
                        <i data-lucide="{{ $icon }}" aria-hidden="true"></i>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>

            <div class="nav-group">
                <span class="nav-label">Other</span>
                @foreach ($otherLinks as $route => $item)
                    @php [$label, $icon, $badge] = array_pad($item, 3, null); @endphp
                    <a class="nav-link {{ $isActive($route) ? 'active' : '' }}" href="{{ route($route) }}">
                        <i data-lucide="{{ $icon }}" aria-hidden="true"></i>
                        <span>{{ $label }}</span>
                        @if ($badge)
                            <span class="nav-badge">{{ $badge }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="nav-group">
                <span class="nav-label">Account</span>
                <a class="nav-link {{ $isActive('admin.stores') ? 'active' : '' }}" href="{{ route('admin.stores') }}">
                    <i data-lucide="store" aria-hidden="true"></i>
                    <span>Stores</span>
                </a>
                <a class="nav-link" href="{{ route('home') }}">
                    <i data-lucide="arrow-up-right" aria-hidden="true"></i>
                    <span>Platform</span>
                </a>
                <a class="nav-link {{ $isActive('admin.demo.reset') ? 'active' : '' }}" href="{{ route('admin.demo.reset') }}">
                    <i data-lucide="rotate-ccw" aria-hidden="true"></i>
                    <span>Reset demo</span>
                </a>
                <a class="nav-link {{ $isActive('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                    <i data-lucide="settings" aria-hidden="true"></i>
                    <span>Settings</span>
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
                    <i data-lucide="search" aria-hidden="true"></i>
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
                    <span class="chip"><i data-lucide="calendar" aria-hidden="true"></i> Last 14 days</span>
                    <button type="button" class="theme-toggle" id="admin-theme-toggle" title="Toggle light and dark theme" aria-label="Toggle light and dark theme">
                        <i data-lucide="moon" class="icon-moon" aria-hidden="true"></i>
                        <i data-lucide="sun" class="icon-sun" aria-hidden="true"></i>
                    </button>
                    <a class="btn-admin" href="{{ route('admin.assistant') }}"><i data-lucide="bot" aria-hidden="true"></i> Ask AI</a>
                    <a class="btn-admin-dark" href="{{ route('admin.reports') }}"><i data-lucide="download" aria-hidden="true"></i> Export</a>
                    <a class="icon-btn" href="{{ route('home') }}" title="Back to platform" aria-label="Back to platform"><i data-lucide="arrow-up-right" aria-hidden="true"></i></a>
                </div>
            </div>
            <x-alerts />
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    <script src="{{ asset('js/admin.js') }}?v={{ @filemtime(public_path('js/admin.js')) ?: time() }}"></script>
    <script src="{{ asset('js/admin-form-ai.js') }}?v={{ @filemtime(public_path('js/admin-form-ai.js')) ?: time() }}"></script>
    <script>
        (function () {
            function applyAdminTheme(theme) {
                var next = theme === 'dark' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', next);
                document.documentElement.classList.toggle('admin-theme-dark', next === 'dark');
                if (document.body) {
                    document.body.setAttribute('data-theme', next);
                    document.body.classList.toggle('admin-theme-dark', next === 'dark');
                    document.body.classList.toggle('admin', true);
                }
                try { localStorage.setItem('tanui-admin-theme', next); } catch (e) {}
                var toggle = document.getElementById('admin-theme-toggle');
                if (toggle) {
                    toggle.setAttribute('aria-pressed', next === 'dark' ? 'true' : 'false');
                }
            }
            document.addEventListener('click', function (event) {
                var toggle = event.target.closest('#admin-theme-toggle');
                if (!toggle) return;
                event.preventDefault();
                var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                applyAdminTheme(current === 'dark' ? 'light' : 'dark');
                if (window.lucide) window.lucide.createIcons();
            });
            window.__tanuiApplyAdminTheme = applyAdminTheme;
        })();
    </script>
    @stack('scripts')
</body>
</html>
