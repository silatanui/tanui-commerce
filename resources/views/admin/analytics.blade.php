@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')
@php
    $currency = $adminStore->currency;
    $geo = $report['geo'];
    $topCounties = $geo['top_counties'] ?? [];
@endphp

<div class="ax-page">
    <header class="ax-hero">
        <div class="ax-hero__copy">
            <p class="ax-eyebrow">Store intelligence · Kenya</p>
            <h1>Analytics</h1>
            <p class="ax-lead">Demonstration performance for {{ $adminStore->name }}. Visitor geography is simulated across Kenya’s 47 counties.</p>
        </div>
        <form class="ax-range" method="get">
            <div>
                <label for="from">From</label>
                <input id="from" type="date" name="from" value="{{ $report['from']->format('Y-m-d') }}">
            </div>
            <div>
                <label for="to">To</label>
                <input id="to" type="date" name="to" value="{{ $report['to']->format('Y-m-d') }}">
            </div>
            <button type="submit">Apply</button>
            <span class="ax-sim">Simulated</span>
        </form>
    </header>

    <section class="ax-kpis" aria-label="Key metrics">
        <article>
            <span>Sales</span>
            <strong>{{ money($report['sales'], $currency) }}</strong>
            <em class="{{ $report['changes']['sales'] >= 0 ? 'up' : 'down' }}">{{ ($report['changes']['sales'] >= 0 ? '+' : '').$report['changes']['sales'] }}%</em>
        </article>
        <article>
            <span>Orders</span>
            <strong>{{ number_format($report['orders']) }}</strong>
            <em class="{{ $report['changes']['orders'] >= 0 ? 'up' : 'down' }}">{{ ($report['changes']['orders'] >= 0 ? '+' : '').$report['changes']['orders'] }}%</em>
        </article>
        <article>
            <span>Avg order</span>
            <strong>{{ money($report['average'], $currency) }}</strong>
            <em>{{ number_format($report['orders']) }} checkouts</em>
        </article>
        <article>
            <span>Customers</span>
            <strong>{{ number_format($report['customers']) }}</strong>
            <em class="{{ $report['changes']['customers'] >= 0 ? 'up' : 'down' }}">{{ ($report['changes']['customers'] >= 0 ? '+' : '').$report['changes']['customers'] }}%</em>
        </article>
        <article>
            <span>Sessions</span>
            <strong>{{ number_format($report['interactions']) }}</strong>
            <em class="{{ $report['changes']['interactions'] >= 0 ? 'up' : 'down' }}">{{ ($report['changes']['interactions'] >= 0 ? '+' : '').$report['changes']['interactions'] }}%</em>
        </article>
        <article>
            <span>Conversion</span>
            <strong>{{ $report['conversion'] }}%</strong>
            <em>{{ $report['returning_share'] }}% returning</em>
        </article>
    </section>

    <section class="ax-map-card">
        <div class="ax-map-card__head">
            <div>
                <h2>Client origins by county</h2>
                <p>Choropleth of simulated online sessions across Kenya. Darker counties show higher traffic.</p>
            </div>
            <div class="ax-map-meta">
                <span>{{ number_format($geo['total_sessions']) }} sessions</span>
                <span>47 counties</span>
            </div>
        </div>
        <div class="ax-map-shell">
            <div class="ax-map-stage">
                <div id="kenyaMap" class="ax-map" role="img" aria-label="Kenya county choropleth of simulated client origins"></div>
                <div class="ax-legend" aria-hidden="true">
                    <span>Low</span>
                    <div class="ax-legend__ramp"></div>
                    <span>High</span>
                </div>
            </div>
            <aside class="ax-rank">
                <h3>Top counties</h3>
                <ol>
                    @foreach ($topCounties as $i => $county)
                        <li>
                            <span class="ax-rank__n">{{ $i + 1 }}</span>
                            <div>
                                <strong>{{ $county['name'] }}</strong>
                                <small>{{ number_format($county['sessions']) }} sessions · {{ $county['share'] }}%</small>
                            </div>
                            <b>{{ number_format($county['orders']) }}</b>
                        </li>
                    @endforeach
                </ol>
                <p class="ax-rank__note">Order counts are demonstration allocations from the selected range.</p>
            </aside>
        </div>
    </section>

    <section class="ax-grid">
        <article class="ax-panel ax-panel--wide">
            <header>
                <div>
                    <h2>Revenue and orders</h2>
                    <p>Daily demonstration totals against target</p>
                </div>
            </header>
            <div class="ax-chart ax-chart--tall">
                <canvas id="analyticsTrendChart"></canvas>
            </div>
        </article>

        <article class="ax-panel">
            <header>
                <div>
                    <h2>Order status</h2>
                    <p>Pipeline mix</p>
                </div>
            </header>
            <div class="ax-chart">
                <canvas id="statusChart"></canvas>
            </div>
        </article>

        <article class="ax-panel">
            <header>
                <div>
                    <h2>Devices</h2>
                    <p>Session share by class</p>
                </div>
            </header>
            <div class="ax-chart">
                <canvas id="deviceChart"></canvas>
            </div>
            <ul class="ax-mini-list">
                @foreach ($report['devices'] as $device)
                    <li><span>{{ $device['label'] }}</span><strong>{{ $device['share'] }}%</strong></li>
                @endforeach
            </ul>
        </article>

        <article class="ax-panel">
            <header>
                <div>
                    <h2>Categories</h2>
                    <p>Revenue contribution</p>
                </div>
            </header>
            <div class="ax-chart">
                <canvas id="categoryChart"></canvas>
            </div>
            <ul class="ax-mini-list">
                @forelse ($report['categories'] as $row)
                    <li><span>{{ $row->category_name }}</span><strong>{{ money($row->revenue, $currency) }}</strong></li>
                @empty
                    <li><span>No category sales</span><strong>—</strong></li>
                @endforelse
            </ul>
        </article>

        <article class="ax-panel">
            <header>
                <div>
                    <h2>Bestsellers</h2>
                    <p>Units in range</p>
                </div>
            </header>
            <ul class="ax-mini-list ax-mini-list--stack">
                @forelse ($report['popular'] as $row)
                    <li>
                        <div>
                            <strong>{{ $row->product_name }}</strong>
                            <small>{{ number_format($row->units) }} units</small>
                        </div>
                        <b>{{ money($row->revenue, $currency) }}</b>
                    </li>
                @empty
                    <li><span>No product sales yet</span></li>
                @endforelse
            </ul>
        </article>

        <article class="ax-panel">
            <header>
                <div>
                    <h2>Operations</h2>
                    <p>Watchlist for this store</p>
                </div>
            </header>
            <ul class="ax-mini-list">
                <li><span>Catalogue</span><strong>{{ number_format($report['products']) }}</strong></li>
                <li><span>Open fulfilment</span><strong>{{ number_format($report['pending']) }}</strong></li>
                <li><span>Pending reviews</span><strong>{{ number_format($report['pending_reviews']) }}</strong></li>
                <li><span>Returning visitors</span><strong>{{ $report['returning_share'] }}%</strong></li>
            </ul>
            <h3 class="ax-subhead">Low stock</h3>
            <ul class="ax-mini-list ax-mini-list--stack">
                @forelse ($report['low_stock'] as $variant)
                    <li>
                        <div>
                            <strong>{{ $variant->product->name }}</strong>
                            <small>{{ $variant->label() }}</small>
                        </div>
                        <b>{{ $variant->stock_quantity }}</b>
                    </li>
                @empty
                    <li><span>No low-stock variants</span></li>
                @endforelse
            </ul>
        </article>
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
.ax-page{display:grid;gap:1.15rem}
.ax-hero{display:flex;justify-content:space-between;gap:1.25rem;align-items:end;flex-wrap:wrap;padding:.15rem 0 .35rem;border-bottom:1px solid var(--admin-line)}
.ax-eyebrow{margin:0 0 .4rem;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--admin-muted);line-height:1em}
.ax-hero h1{margin:0;font-size:2rem;font-weight:700;letter-spacing:-.03em;line-height:1em}
.ax-lead{margin:.55rem 0 0;max-width:42rem;color:var(--admin-muted);font-size:.92rem;line-height:1.35}
.ax-range{display:flex;flex-wrap:wrap;gap:.55rem;align-items:end}
.ax-range label{display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-muted);margin-bottom:.28rem;line-height:1em}
.ax-range input{border:1px solid var(--admin-line);background:var(--admin-surface);color:var(--admin-ink);padding:.55rem .7rem;font:inherit;min-width:148px}
.ax-range button{border:1px solid #1a1a1a;background:#1a1a1a;color:#fff;padding:.58rem 1rem;font:inherit;font-weight:600}
html.admin-theme-dark .ax-range button{background:#f2f2f2;border-color:#f2f2f2;color:#121212}
.ax-sim{align-self:center;border:1px solid var(--admin-line);padding:.45rem .7rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-muted);line-height:1em}

.ax-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.7rem}
.ax-kpis article{background:var(--admin-surface);border:1px solid var(--admin-line);padding:.9rem .95rem;min-height:104px;display:grid;align-content:space-between;gap:.45rem}
.ax-kpis span{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--admin-muted);line-height:1em}
.ax-kpis strong{font-size:1.35rem;font-weight:700;letter-spacing:-.02em;line-height:1em}
.ax-kpis em{font-style:normal;font-size:.78rem;color:var(--admin-muted);line-height:1em}
.ax-kpis em.up{color:var(--admin-ink)}
.ax-kpis em.down{color:var(--admin-muted)}

.ax-map-card,.ax-panel{background:var(--admin-surface);border:1px solid var(--admin-line);padding:1rem}
.ax-map-card__head,.ax-panel > header{display:flex;justify-content:space-between;gap:1rem;align-items:start;margin-bottom:.85rem}
.ax-map-card h2,.ax-panel h2{margin:0;font-size:1.05rem;font-weight:650;letter-spacing:-.02em;line-height:1em}
.ax-map-card p,.ax-panel header p{margin:.4rem 0 0;color:var(--admin-muted);font-size:.84rem;line-height:1.25;max-width:40rem}
.ax-map-meta{display:flex;gap:.45rem;flex-wrap:wrap}
.ax-map-meta span{border:1px solid var(--admin-line);padding:.4rem .65rem;font-size:.72rem;line-height:1em;color:var(--admin-muted);text-transform:uppercase;letter-spacing:.05em}
.ax-map-shell{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(260px,.75fr);gap:1rem;align-items:stretch}
.ax-map-stage{position:relative;height:520px;min-height:520px;background:#f3efec;border:1px solid var(--admin-line);overflow:hidden}
html.admin-theme-dark .ax-map-stage{background:#141211}
.ax-map{position:absolute;inset:0;z-index:1;width:100%;height:100%;min-height:520px}
.ax-legend{position:absolute;left:12px;bottom:12px;z-index:500;display:flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.94);border:1px solid #ddd;padding:.4rem .55rem;font-size:.7rem;color:#555;line-height:1em}
html.admin-theme-dark .ax-legend{background:rgba(20,20,20,.94);border-color:#3a3a3a;color:#c8c8c8}
.ax-legend__ramp{width:108px;height:10px;background:linear-gradient(90deg,#f7ebe8 0%,#f0c4b8 18%,#e8927a 38%,#d45a3a 58%,#a83d28 78%,#5c2418 100%)}
html.admin-theme-dark .ax-legend__ramp{background:linear-gradient(90deg,#2a1c1a 0%,#4a2a24 18%,#7a3d30 38%,#b04a32 58%,#d97855 78%,#f0b49a 100%)}

.ax-rank{border:1px solid var(--admin-line);padding:.85rem .9rem;display:flex;flex-direction:column;min-height:520px}
.ax-rank h3{margin:0 0 .75rem;font-size:.78rem;text-transform:uppercase;letter-spacing:.07em;color:var(--admin-muted);line-height:1em}
.ax-rank ol{list-style:none;margin:0;padding:0;display:grid;gap:.15rem;flex:1}
.ax-rank li{display:grid;grid-template-columns:28px 1fr auto;gap:.65rem;align-items:center;padding:.55rem 0;border-bottom:1px solid var(--admin-line)}
.ax-rank li:last-child{border-bottom:0}
.ax-rank__n{width:28px;height:28px;display:grid;place-items:center;background:var(--admin-gray-6);color:var(--admin-ink);font-size:.75rem;font-weight:650}
.ax-rank strong{display:block;font-size:.9rem;line-height:1em}
.ax-rank small{display:block;margin-top:.28rem;color:var(--admin-muted);font-size:.72rem;line-height:1em}
.ax-rank b{font-size:.86rem;font-weight:650}
.ax-rank__note{margin:.75rem 0 0;color:var(--admin-muted);font-size:.72rem;line-height:1.3}

.ax-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
.ax-panel--wide{grid-column:1 / -1}
.ax-chart{position:relative;min-height:220px}
.ax-chart--tall{min-height:280px}
.ax-mini-list{list-style:none;margin:.75rem 0 0;padding:0;display:grid;gap:.35rem}
.ax-mini-list li{display:flex;justify-content:space-between;gap:1rem;align-items:start;padding:.45rem 0;border-bottom:1px solid var(--admin-line);font-size:.86rem;line-height:1em}
.ax-mini-list li:last-child{border-bottom:0}
.ax-mini-list strong,.ax-mini-list b{font-weight:650}
.ax-mini-list small{display:block;margin-top:.28rem;color:var(--admin-muted);font-size:.72rem;line-height:1em}
.ax-mini-list--stack li{align-items:center}
.ax-subhead{margin:1rem 0 .4rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--admin-muted);line-height:1em}

.ax-hotspot{background:transparent;border:0}
.ax-hotspot__dot{display:block;width:12px;height:12px;border-radius:50% !important;background:#e4572e;box-shadow:0 0 0 0 rgba(228,87,46,.55);animation:axPulse 1.8s ease-out infinite}
@keyframes axPulse{0%{box-shadow:0 0 0 0 rgba(228,87,46,.55)}70%{box-shadow:0 0 0 14px rgba(228,87,46,0)}100%{box-shadow:0 0 0 0 rgba(228,87,46,0)}}

.leaflet-container{background:transparent;font-family:var(--admin-sans)}
.leaflet-control-zoom{
  border:1px solid var(--admin-line) !important;
  border-radius:0 !important;
  box-shadow:none !important;
  margin:12px !important;
  overflow:hidden;
}
.leaflet-control-zoom a{
  width:34px !important;
  height:34px !important;
  line-height:34px !important;
  border-radius:0 !important;
  border-bottom:1px solid var(--admin-line) !important;
  background:var(--admin-surface) !important;
  color:var(--admin-ink) !important;
  font:600 18px/34px var(--admin-sans) !important;
  text-indent:0 !important;
}
.leaflet-control-zoom a:last-child{border-bottom:0 !important}
.leaflet-control-zoom a:hover{
  background:var(--admin-gray-6) !important;
  color:var(--admin-ink) !important;
}
.leaflet-control-zoom a.leaflet-disabled{
  opacity:.35 !important;
  cursor:default !important;
  background:var(--admin-surface) !important;
  color:var(--admin-muted) !important;
}
html.admin-theme-dark .leaflet-control-zoom{
  border-color:#3a3a3a !important;
}
html.admin-theme-dark .leaflet-control-zoom a{
  background:#1c1c1c !important;
  color:#f2f2f2 !important;
  border-bottom-color:#3a3a3a !important;
}
html.admin-theme-dark .leaflet-control-zoom a:hover{
  background:#2a2a2a !important;
  color:#ffffff !important;
}
.leaflet-popup-content-wrapper,
.leaflet-popup-tip{
  border-radius:0 !important;
  background:var(--admin-surface);
  color:var(--admin-ink);
  box-shadow:var(--admin-shadow);
}
html.admin-theme-dark .leaflet-popup-content-wrapper,
html.admin-theme-dark .leaflet-popup-tip{
  background:#1c1c1c;
  color:#f2f2f2;
}
.ax-popup strong{display:block;margin-bottom:.25rem}
.ax-popup span{display:block;color:#666;font-size:.82rem;line-height:1.35}
html.admin-theme-dark .ax-popup span{color:#aaa}

@media (max-width:1200px){
  .ax-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}
  .ax-map-shell{grid-template-columns:1fr}
  .ax-rank{min-height:auto}
  .ax-map-stage,.ax-map{height:440px;min-height:440px}
}
@media (max-width:760px){
  .ax-kpis,.ax-grid{grid-template-columns:1fr}
  .ax-panel--wide{grid-column:auto}
  .ax-hero h1{font-size:1.65rem}
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    const isDark = () => document.documentElement.classList.contains('admin-theme-dark')
        || document.documentElement.getAttribute('data-theme') === 'dark';

    const chartTheme = () => {
        if (isDark()) {
            return {
                muted: '#b8b8b8',
                grid: '#3a3a3a',
                line: '#ffffff',
                lineSoft: '#c8c8c8',
                fill: 'rgba(255,255,255,0.14)',
                bars: ['#ffffff', '#d0d0d0', '#a8a8a8', '#808080', '#585858'],
            };
        }
        return {
            muted: '#555555',
            grid: '#e0e0e0',
            line: '#111111',
            lineSoft: '#4a4a4a',
            fill: 'rgba(17,17,17,0.08)',
            bars: ['#111111', '#3a3a3a', '#666666', '#909090', '#b8b8b8'],
        };
    };

    const trend = @json($report['trend']);
    const statuses = @json($report['order_statuses']);
    const devices = @json($report['devices']);
    const categories = @json($report['categories']);
    const counties = @json($geo['counties'] ?? []);
    const hotspots = @json($geo['hotspots'] ?? []);
    const maxSessions = {{ (int) ($geo['max_sessions'] ?? 1) }};
    const geojsonUrl = @json($geo['geojson_url'] ?? asset('geo/kenya-counties.geojson'));
    const theme = chartTheme();

    new Chart(document.getElementById('analyticsTrendChart'), {
        type: 'line',
        data: {
            labels: trend.map((row) => row.label),
            datasets: [
                {
                    label: 'Revenue',
                    data: trend.map((row) => row.sales),
                    borderColor: theme.line,
                    backgroundColor: theme.fill,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    borderWidth: 2.5,
                    yAxisID: 'y',
                },
                {
                    label: 'Orders',
                    data: trend.map((row) => row.orders),
                    borderColor: theme.lineSoft,
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.3,
                    pointRadius: 2,
                    borderWidth: 2,
                    borderDash: [5, 4],
                    yAxisID: 'y1',
                },
                {
                    label: 'Target',
                    data: trend.map((row) => row.target),
                    borderColor: theme.muted,
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.25,
                    pointRadius: 0,
                    borderWidth: 1.5,
                    borderDash: [2, 4],
                    yAxisID: 'y',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    labels: { color: theme.muted, boxWidth: 12, font: { family: 'Outfit' } },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: theme.muted, maxTicksLimit: 10 } },
                y: {
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: theme.grid },
                    ticks: { color: theme.muted },
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: { color: theme.muted, precision: 0 },
                },
            },
        },
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statuses.map((row) => row.status),
            datasets: [{
                data: statuses.map((row) => row.total),
                backgroundColor: statuses.map((_, i) => theme.bars[i % theme.bars.length]),
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: theme.muted, boxWidth: 10, font: { family: 'Outfit' } },
                },
            },
            cutout: '64%',
        },
    });

    new Chart(document.getElementById('deviceChart'), {
        type: 'bar',
        data: {
            labels: devices.map((row) => row.label),
            datasets: [{
                data: devices.map((row) => row.share),
                backgroundColor: devices.map((_, i) => theme.bars[i % theme.bars.length]),
                borderRadius: 0,
                barThickness: 26,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: theme.grid },
                    ticks: { color: theme.muted, callback: (value) => value + '%' },
                },
                y: { grid: { display: false }, ticks: { color: theme.muted } },
            },
        },
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categories.map((row) => row.category_name),
            datasets: [{
                data: categories.map((row) => Number(row.revenue)),
                backgroundColor: categories.map((_, i) => theme.bars[i % theme.bars.length]),
                borderRadius: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: theme.muted } },
                y: { beginAtZero: true, grid: { color: theme.grid }, ticks: { color: theme.muted } },
            },
        },
    });

    const sessionsByCounty = Object.fromEntries(counties.map((row) => [row.name, row]));
    const heatLight = ['#f7ebe8', '#f0c4b8', '#e8927a', '#d45a3a', '#a83d28', '#5c2418'];
    const heatDark = ['#2a1c1a', '#4a2a24', '#7a3d30', '#b04a32', '#d97855', '#f0b49a'];

    function heatPalette() {
        return isDark() ? heatDark : heatLight;
    }

    function heatColor(sessions) {
        const heat = heatPalette();
        const t = Math.max(0, Math.min(1, sessions / Math.max(1, maxSessions)));
        const idx = Math.min(heat.length - 1, Math.floor(t * (heat.length - 1) + 0.0001));
        return heat[idx];
    }

    function countyStyle(feature) {
        const name = feature.properties && feature.properties.name;
        const row = sessionsByCounty[name];
        const sessions = row ? row.sessions : 0;
        return {
            fillColor: heatColor(sessions),
            weight: 1,
            opacity: 1,
            color: isDark() ? '#0e0e0e' : '#ffffff',
            fillOpacity: isDark() ? 0.96 : 0.92,
        };
    }

    const mapEl = document.getElementById('kenyaMap');
    if (typeof L === 'undefined') {
        if (mapEl) {
            mapEl.innerHTML = '<div style="padding:1.5rem;color:#777;font-size:.9rem">Map library failed to load. Refresh the page.</div>';
        }
        return;
    }

    const map = L.map(mapEl, {
        scrollWheelZoom: false,
        zoomControl: true,
        attributionControl: false,
        minZoom: 5,
        maxZoom: 9,
    }).setView([0.25, 37.8], 6);

    let countiesLayer = null;

    fetch(geojsonUrl, { credentials: 'same-origin' })
        .then((response) => {
            if (!response.ok) {
                throw new Error('GeoJSON HTTP ' + response.status);
            }
            return response.json();
        })
        .then((geojson) => {
            if (!geojson || !Array.isArray(geojson.features) || !geojson.features.length) {
                throw new Error('Empty GeoJSON');
            }

            countiesLayer = L.geoJSON(geojson, {
                style: countyStyle,
                onEachFeature(feature, featureLayer) {
                    const name = feature.properties && feature.properties.name;
                    const row = sessionsByCounty[name] || { sessions: 0, orders: 0, share: 0 };
                    featureLayer.bindPopup(
                        `<div class="ax-popup"><strong>${name || 'County'}</strong><span>${row.sessions} sessions<br>${row.orders} orders<br>${row.share}% of traffic</span></div>`
                    );
                    featureLayer.on({
                        mouseover(e) {
                            e.target.setStyle({
                                weight: 2,
                                color: isDark() ? '#f2f2f2' : '#1a1a1a',
                                fillOpacity: 1,
                            });
                            e.target.bringToFront();
                        },
                        mouseout(e) {
                            countiesLayer.resetStyle(e.target);
                        },
                    });
                },
            }).addTo(map);

            map.fitBounds(countiesLayer.getBounds(), { padding: [18, 18], maxZoom: 7 });

            hotspots.forEach((spot) => {
                const icon = L.divIcon({
                    className: 'ax-hotspot',
                    html: '<span class="ax-hotspot__dot"></span>',
                    iconSize: [12, 12],
                    iconAnchor: [6, 6],
                });
                L.marker([spot.lat, spot.lng], { icon, zIndexOffset: 400 })
                    .addTo(map)
                    .bindPopup(
                        `<div class="ax-popup"><strong>${spot.name}</strong><span>${spot.county} county<br>${spot.sessions} sessions<br>${spot.orders} orders</span></div>`
                    );
            });

            requestAnimationFrame(() => map.invalidateSize());
            setTimeout(() => map.invalidateSize(), 200);
        })
        .catch((err) => {
            console.error('Kenya map failed', err);
            mapEl.innerHTML =
                '<div style="padding:1.5rem;color:#777;font-size:.9rem">County map could not load. Check /geo/kenya-counties.geojson.</div>';
        });

    // Keep choropleth in sync when the admin theme toggles.
    const themeRoot = document.documentElement;
    const themeObserver = new MutationObserver(() => {
        if (countiesLayer) {
            countiesLayer.setStyle(countyStyle);
        }
        map.invalidateSize();
    });
    themeObserver.observe(themeRoot, { attributes: true, attributeFilter: ['class', 'data-theme'] });
})();
</script>
@endpush
