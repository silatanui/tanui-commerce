@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php
    $currency = $adminStore->currency;
    $changeClass = fn (float $value) => $value >= 0 ? 'delta-up' : 'delta-down';
    $changeText = fn (float $value, string $suffix) => ($value >= 0 ? '+' : '').$value.'% '.$suffix;
    $channelColors = ['#1a1a1a', '#5a5a5a', '#9a9a9a', '#c8c8c8']; // JS overrides for dark theme
@endphp

<div class="dash-grid">
    <div class="kpi-row">
        <article class="dash-card kpi-card">
            <div class="kpi-label">Total Sales</div>
            <p class="kpi-value">{{ money($report['sales'], $currency) }}</p>
            <div class="kpi-meta {{ $changeClass($report['changes']['sales']) }}">{{ $changeText($report['changes']['sales'], 'vs prior period') }}</div>
            <a class="kpi-link" href="{{ route('admin.analytics') }}" aria-label="Open analytics"><i data-lucide="arrow-up-right"></i></a>
        </article>
        <article class="dash-card kpi-card">
            <div class="kpi-label">Total Order</div>
            <p class="kpi-value">{{ number_format($report['orders']) }}</p>
            <div class="kpi-meta {{ $changeClass($report['changes']['orders']) }}">{{ $changeText($report['changes']['orders'], 'demo orders') }}</div>
            <a class="kpi-link" href="{{ route('admin.orders.index') }}" aria-label="Open orders"><i data-lucide="arrow-up-right"></i></a>
        </article>
        <article class="dash-card kpi-card">
            <div class="kpi-label">Total Customer</div>
            <p class="kpi-value">{{ number_format($report['customers']) }}</p>
            <div class="kpi-meta {{ $changeClass($report['changes']['customers']) }}">{{ $changeText($report['changes']['customers'], 'new customers') }}</div>
            <a class="kpi-link" href="{{ route('admin.customers.index') }}" aria-label="Open customers"><i data-lucide="arrow-up-right"></i></a>
        </article>
        <article class="dash-card kpi-card">
            <div class="kpi-label">Interaction</div>
            <p class="kpi-value">{{ number_format($report['interactions']) }}</p>
            <div class="kpi-meta {{ $changeClass($report['changes']['interactions']) }}">{{ $changeText($report['changes']['interactions'], 'demo sessions') }}</div>
            <a class="kpi-link" href="{{ route('admin.analytics') }}" aria-label="Open analytics"><i data-lucide="arrow-up-right"></i></a>
        </article>
    </div>

    <div class="mid-row">
        <section class="dash-card">
            <div class="card-head">
                <div>
                    <h2>Category performance</h2>
                    <p>Simulated sales by catalogue group</p>
                </div>
                <a class="chip" href="{{ route('admin.analytics') }}">Details</a>
            </div>
            <div class="channel-panel">
                <div class="gauge-wrap">
                    <canvas id="channelChart" width="220" height="160"></canvas>
                    <div class="gauge-center">
                        <strong>{{ number_format($report['channel_units']) }}</strong>
                        <span>Product sales</span>
                    </div>
                </div>
                <div class="channel-list">
                    @forelse ($report['channels'] as $index => $channel)
                        <div class="channel-row">
                            <span class="channel-dot" style="background: {{ $channelColors[$index] ?? '#94a3b8' }}"></span>
                            <div>
                                <strong>{{ $channel['name'] }}</strong>
                                <small>{{ number_format($channel['units']) }} units · {{ $channel['share'] }}%</small>
                            </div>
                            <div class="channel-value">
                                {{ $channel['formatted_revenue'] }}
                                <span>sample revenue</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No category sales in this period.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="dash-card">
            <div class="card-head">
                <div>
                    <h2>Average Sales</h2>
                    <p>Revenue against a simulated daily target</p>
                </div>
                <div class="chart-filters">
                    <select disabled aria-label="Product filter"><option>All Product</option></select>
                    <select disabled aria-label="Category filter"><option>All Categories</option></select>
                    <select disabled aria-label="Year filter"><option>{{ now()->year }}</option></select>
                </div>
            </div>
            <canvas id="salesChart" height="120"></canvas>
        </section>
    </div>

    <div class="bottom-row">
        <section class="dash-card">
            <div class="card-head">
                <div>
                    <h2>Recent Activity</h2>
                    <p>Incoming and outgoing demonstration stock</p>
                </div>
                <a class="chip" href="{{ route('admin.inventory.index') }}">Inventory</a>
            </div>
            <div class="activity-list">
                @forelse ($report['movements'] as $movement)
                    <div class="activity-item">
                        <div class="activity-icon {{ $movement['outgoing'] ? 'out' : 'in' }}">
                            <i data-lucide="{{ $movement['outgoing'] ? 'arrow-up-right' : 'arrow-down-left' }}"></i>
                        </div>
                        <div>
                            <strong>{{ $movement['title'] }}</strong>
                            <small>{{ $movement['subtitle'] }} · {{ $movement['when'] }}</small>
                        </div>
                        <div class="activity-amt">
                            {{ $movement['outgoing'] ? '-' : '+' }}{{ $movement['quantity'] }}
                            <span>{{ $movement['amount'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No inventory movements yet.</p>
                @endforelse
            </div>
            <div class="mt-3 pt-3 border-top">
                <div class="card-head mb-2">
                    <div>
                        <h2 class="h6 mb-0">Recent orders</h2>
                    </div>
                    <a class="chip" href="{{ route('admin.orders.index') }}">View all</a>
                </div>
                @foreach ($report['recent']->take(4) as $order)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a>
                            <div class="small text-muted">{{ ucfirst($order->status) }}</div>
                        </div>
                        <strong>{{ money($order->total, $order->currency) }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dash-card">
            <div class="card-head">
                <div>
                    <h2>Top 3 Product</h2>
                    <p>Units sold in the selected range</p>
                </div>
            </div>
            <canvas id="topProductsChart" height="180"></canvas>
            <div class="mt-3">
                @foreach ($report['top_products'] as $row)
                    <div class="d-flex justify-content-between py-1">
                        <span>{{ $row->product_name }}</span>
                        <strong>{{ $row->units }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dash-card">
            <div class="card-head">
                <div>
                    <h2>Store activity</h2>
                    <p>Demo sessions and order timing</p>
                </div>
            </div>
            <div class="visitor-stat">
                <div>
                    <div class="kpi-label">Total sessions</div>
                    <strong>{{ number_format($report['interactions']) }}</strong>
                </div>
                <div class="{{ $changeClass($report['changes']['interactions']) }}">
                    {{ $changeText($report['changes']['interactions'], 'vs prior') }}
                </div>
            </div>
            <div class="visitor-bars">
                @php
                    $channelMax = max(1, collect($report['channels'])->max('share') ?: 1);
                @endphp
                @foreach ($report['channels'] as $index => $channel)
                    <div class="visitor-bar-row">
                        <span>{{ $channel['name'] }}</span>
                        <div class="visitor-track"><div class="visitor-fill" style="width: {{ min(100, ($channel['share'] / $channelMax) * 100) }}%; background: {{ $channelColors[$index] ?? '#5a5a5a' }}"></div></div>
                        <strong>{{ $channel['share'] }}%</strong>
                    </div>
                @endforeach
            </div>
            <div class="heatmap" aria-label="Order activity heatmap">
                <span></span>
                @foreach ($report['heatmap']['days'] as $day)
                    <span class="hd">{{ $day }}</span>
                @endforeach
                @foreach ($report['heatmap']['hours'] as $hour)
                    <span class="hh">{{ $hour }}</span>
                    @foreach ($report['heatmap']['days'] as $day)
                        @php
                            $value = $report['heatmap']['cells'][$day][$hour] ?? 0;
                            $intensity = $value / max(1, $report['heatmap']['max']);
                            $alpha = 0.1 + ($intensity * 0.82);
                        @endphp
                        <span class="heat-cell" style="background: rgba(26, 26, 26, {{ $alpha }})" title="{{ $day }} {{ $hour }}: {{ $value }}"></span>
                    @endforeach
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
const isDark = document.documentElement.classList.contains('admin-theme-dark')
    || document.documentElement.getAttribute('data-theme') === 'dark';
const palette = isDark
    ? { ink: '#f2f2f2', soft: '#9a9a9a', fill: 'rgba(242,242,242,0.12)', grid: '#333', bars: ['#f2f2f2', '#c8c8c8', '#9a9a9a'], channels: ['#f2f2f2', '#b0b0b0', '#6b6b6b'] }
    : { ink: '#1a1a1a', soft: '#6b6b6b', fill: 'rgba(26,26,26,0.1)', grid: '#e6e6e6', bars: ['#1a1a1a', '#5a5a5a', '#9a9a9a'], channels: ['#1a1a1a', '#5a5a5a', '#9a9a9a'] };

const trend = @json($report['trend']);
const channels = @json($report['channels']);
const topProducts = @json($report['top_products']);

new Chart(document.getElementById('channelChart'), {
    type: 'doughnut',
    data: {
        labels: channels.map((row) => row.name),
        datasets: [{
            data: channels.map((row) => row.units || row.share),
            backgroundColor: palette.channels,
            borderWidth: 0,
            circumference: 180,
            rotation: 270,
            cutout: '78%'
        }]
    },
    options: {
        plugins: { legend: { display: false }, tooltip: { enabled: true } },
        maintainAspectRatio: false
    }
});

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: trend.map((row) => row.label),
        datasets: [
            {
                label: 'Revenue',
                data: trend.map((row) => row.sales),
                borderColor: palette.ink,
                backgroundColor: palette.fill,
                fill: true,
                tension: 0.35,
                pointRadius: 2,
                borderWidth: 2.5
            },
            {
                label: 'Target',
                data: trend.map((row) => row.target),
                borderColor: palette.soft,
                borderDash: [6, 6],
                fill: false,
                tension: 0.35,
                pointRadius: 0,
                borderWidth: 2
            }
        ]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: palette.soft, maxTicksLimit: 8 } },
            y: { grid: { color: palette.grid }, ticks: { color: palette.soft }, beginAtZero: true }
        }
    }
});

new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: topProducts.map((row) => row.product_name),
        datasets: [{
            data: topProducts.map((row) => Number(row.units)),
            backgroundColor: palette.bars,
            borderRadius: 0,
            barThickness: 28
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: palette.grid }, ticks: { precision: 0, color: palette.soft } },
            y: { grid: { display: false }, ticks: { color: palette.soft } }
        }
    }
});
</script>
@endpush

