@php
    /** @var array $meta */
    $store = $meta['store'];
    $accent = $meta['accent'];
    $ink = $meta['ink'];
    $currency = $meta['currency'];
    $kpis = [
        'Sales' => money($analytics['sales'] ?? 0, $currency),
        'Orders' => number_format((int) ($analytics['orders'] ?? 0)),
        'AOV' => money($analytics['average'] ?? 0, $currency),
        'Customers' => number_format((int) ($analytics['customers'] ?? 0)),
        'Conversion' => ($analytics['conversion'] ?? 0).'%',
        'Sessions' => number_format((int) ($analytics['interactions'] ?? 0)),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] }} · {{ $store->name }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: {{ $ink }};
            font-size: 11px;
            line-height: 1.35;
            background: #fff;
        }
        .page {
            page-break-after: always;
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }
        .page:last-child { page-break-after: auto; }
        .pad { padding: 22mm 18mm 20mm; }
        .eyebrow {
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #777;
            margin: 0 0 8px;
        }
        h1 { margin: 0; font-size: 28px; letter-spacing: -0.5px; line-height: 1.05; }
        h2 { margin: 0 0 8px; font-size: 16px; letter-spacing: -0.2px; }
        h3 { margin: 0 0 6px; font-size: 12px; letter-spacing: 0.4px; text-transform: uppercase; color: #666; }
        p { margin: 0 0 8px; color: #444; }
        .muted { color: #777; }
        .accent { color: {{ $accent }}; }
        .rule { height: 2px; background: {{ $accent }}; width: 54px; margin: 12px 0 16px; }
        .rule-wide { height: 1px; background: #e5e5e5; margin: 14px 0; }

        /* Cover */
        .cover-bg {
            position: absolute; inset: 0;
            background: linear-gradient(145deg, #111 0%, #2a211e 48%, {{ $accent }} 160%);
        }
        .cover-panel {
            position: absolute; left: 0; top: 0; bottom: 0; width: 58%;
            background: rgba(255,255,255,0.97);
            padding: 28mm 16mm 20mm 18mm;
        }
        .cover-aside {
            position: absolute; right: 0; top: 0; bottom: 0; width: 42%;
            overflow: hidden;
        }
        .cover-aside img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .cover-aside .fallback {
            width: 100%; height: 100%;
            background: {{ $accent }};
        }
        .cover-meta { position: absolute; left: 18mm; bottom: 18mm; width: 48%; }
        .cover-badge {
            display: inline-block;
            border: 1px solid {{ $ink }};
            padding: 5px 9px;
            font-size: 9px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }
        .cover-title { font-size: 34px; line-height: 1.02; margin: 0 0 12px; }
        .cover-sub { font-size: 12px; color: #555; max-width: 90%; }

        /* TOC */
        .toc-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .toc-row td {
            padding: 9px 0;
            border-bottom: 1px solid #ececec;
            vertical-align: bottom;
        }
        .toc-row .num {
            width: 28px;
            color: {{ $accent }};
            font-weight: bold;
        }
        .toc-row .dots {
            border-bottom: 1px dotted #bbb;
            height: 12px;
        }
        .toc-row .pg {
            width: 28px;
            text-align: right;
            font-weight: bold;
        }

        /* KPI cards */
        .kpi-table { width: 100%; border-collapse: collapse; margin: 10px 0 16px; }
        .kpi-table td {
            width: 33.33%;
            background: #f7f4f2;
            border: 3px solid #fff;
            padding: 10px 12px;
            vertical-align: top;
        }
        .kpi-table .label {
            font-size: 8px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #777;
            margin-bottom: 4px;
        }
        .kpi-table .value {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: -0.3px;
        }

        .panel {
            border: 1px solid #e8e2de;
            padding: 12px;
            margin-bottom: 12px;
            background: #fff;
        }
        .panel-accent {
            border-left: 4px solid {{ $accent }};
            background: #fbf7f5;
            padding: 12px 14px;
            margin-bottom: 12px;
        }
        .two-col { width: 100%; border-collapse: collapse; }
        .two-col > tbody > tr > td { vertical-align: top; }
        .two-col .left { width: 58%; padding-right: 10px; }
        .two-col .right { width: 42%; padding-left: 6px; }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data th {
            text-align: left;
            font-size: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #777;
            border-bottom: 2px solid {{ $ink }};
            padding: 6px 5px;
        }
        table.data td {
            padding: 7px 5px;
            border-bottom: 1px solid #ececec;
            vertical-align: middle;
        }
        table.data tr:nth-child(even) td { background: #faf8f7; }

        .thumb {
            width: 34px;
            height: 34px;
            object-fit: cover;
            background: #eee;
        }
        .pill {
            display: inline-block;
            padding: 2px 7px;
            background: {{ $accent }};
            color: #fff;
            font-size: 8px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .pill-dark { background: {{ $ink }}; }
        .pill-soft { background: #ece7e4; color: {{ $ink }}; }

        .footer-bar {
            position: absolute;
            left: 18mm;
            right: 18mm;
            bottom: 12mm;
            border-top: 1px solid #e5e5e5;
            padding-top: 6px;
            font-size: 8px;
            color: #888;
        }
        .footer-bar .right { float: right; }
        .chart-box { text-align: center; margin: 6px 0 4px; }
        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: {{ $accent }};
            margin-right: 4px;
        }
        .note-list { margin: 0; padding-left: 16px; }
        .note-list li { margin-bottom: 6px; color: #333; }
        .gallery { width: 100%; border-collapse: collapse; }
        .gallery td {
            width: 25%;
            padding: 6px;
            vertical-align: top;
        }
        .gallery .frame {
            border: 1px solid #e8e2de;
            padding: 6px;
            background: #faf8f7;
            text-align: center;
        }
        .gallery img {
            width: 100%;
            height: 78px;
            object-fit: cover;
            background: #ddd;
        }
        .gallery .cap {
            margin-top: 5px;
            font-size: 9px;
            font-weight: bold;
        }
        .gallery .sub {
            font-size: 8px;
            color: #777;
        }
        .stat-inline { font-size: 18px; font-weight: bold; color: {{ $accent }}; }
    </style>
</head>
<body>

{{-- COVER --}}
<section class="page">
    <div class="cover-bg"></div>
    <div class="cover-panel">
        @if ($meta['logo_path'])
            <img src="{{ $meta['logo_path'] }}" alt="" style="height:34px;margin-bottom:22px;">
        @endif
        <div class="cover-badge">Confidential · Demonstration</div>
        <h1 class="cover-title">{{ $meta['title'] }}</h1>
        <div class="rule"></div>
        <p class="cover-sub">{{ $meta['subtitle'] }} for <strong>{{ $store->name }}</strong>.</p>
        <p class="cover-sub" style="margin-top:18px;">
            Reporting period<br>
            <strong style="font-size:14px;color:{{ $ink }}">{{ $meta['from']->format('d M Y') }} — {{ $meta['to']->format('d M Y') }}</strong>
        </p>
        <div class="cover-meta">
            <p class="eyebrow">Prepared by Tanui Admin</p>
            <p style="margin:0;color:#555;">Generated {{ $meta['generated_at']->format('d M Y · H:i') }}</p>
            <p style="margin:4px 0 0;color:#555;">Currency {{ $currency }} · Simulated commerce dataset</p>
        </div>
    </div>
    <div class="cover-aside">
        @if ($meta['cover_image'])
            <img src="{{ $meta['cover_image'] }}" alt="">
        @else
            <div class="fallback"></div>
        @endif
    </div>
</section>

{{-- TOC --}}
<section class="page">
    <div class="pad">
        <p class="eyebrow">Preliminary</p>
        <h1>Contents</h1>
        <div class="rule"></div>
        <p>This dossier is structured for decision makers. Charts include a 7-day forward projection (dashed) derived from recent trend momentum.</p>
        <table class="toc-row">
            @foreach ($toc as $i => $item)
                <tr>
                    <td class="num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="pg">{{ $item['page'] }}</td>
                </tr>
            @endforeach
        </table>

        <div style="margin-top:28px;" class="panel-accent">
            <h3>How to read this report</h3>
            <p style="margin:0;">Solid chart lines show actual period performance. Dashed lines estimate the next seven days. Geography is simulated Kenya county demand for demonstration planning.</p>
        </div>
    </div>
    <div class="footer-bar">
        <span>{{ $store->name }} · {{ $meta['title'] }}</span>
        <span class="right">Preliminary</span>
    </div>
</section>

{{-- EXECUTIVE --}}
<section class="page">
    <div class="pad">
        <p class="eyebrow">Section 01</p>
        <h1>Executive brief</h1>
        <div class="rule"></div>

        <table class="kpi-table">
            <tr>
                @foreach (array_slice($kpis, 0, 3, true) as $label => $value)
                    <td>
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $value }}</div>
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach (array_slice($kpis, 3, 3, true) as $label => $value)
                    <td>
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $value }}</div>
                    </td>
                @endforeach
            </tr>
        </table>

        <table class="two-col">
            <tr>
                <td class="left">
                    <div class="panel">
                        <h3>Period movement</h3>
                        <table class="data">
                            <thead><tr><th>Metric</th><th>Change vs prior</th></tr></thead>
                            <tbody>
                                @foreach (($analytics['changes'] ?? []) as $key => $change)
                                    <tr>
                                        <td>{{ ucfirst($key) }}</td>
                                        <td><strong class="{{ $change >= 0 ? 'accent' : '' }}">{{ ($change >= 0 ? '+' : '').$change }}%</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
                <td class="right">
                    <div class="panel">
                        <h3>Projection pulse</h3>
                        <p>Next 7 days estimated sales</p>
                        <div class="stat-inline">{{ money($projections['sales_next'] ?? 0, $currency) }}</div>
                        <p class="muted" style="margin-top:8px;">Orders ≈ {{ number_format((int) ($projections['orders_next'] ?? 0)) }} · Growth signal {{ ($projections['growth_pct'] ?? 0) }}% · Confidence {{ $projections['confidence'] ?? 'n/a' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <div class="panel-accent">
            <h3>Decision notes</h3>
            <ol class="note-list">
                @foreach ($decision_notes as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ol>
        </div>
    </div>
    <div class="footer-bar">
        <span>{{ $store->name }} · Executive</span>
        <span class="right">03</span>
    </div>
</section>

{{-- SALES / TREND --}}
@if (in_array($meta['type'], ['company', 'products', 'customers'], true))
<section class="page">
    <div class="pad">
        <p class="eyebrow">Section 02</p>
        <h1>Performance &amp; projections</h1>
        <div class="rule"></div>
        <div class="panel">
            <h3>Revenue trajectory <span class="muted">(actual + 7-day projection)</span></h3>
            <div class="chart-box">{!! $charts['trend'] !!}</div>
            <p><span class="legend-dot" style="background:{{ $ink }}"></span> Actual sales &nbsp;&nbsp; <span class="legend-dot"></span> Projected sales</p>
        </div>
        <div class="panel">
            <h3>Order volume trajectory</h3>
            <div class="chart-box">{!! $charts['orders_trend'] !!}</div>
        </div>
        <table class="two-col">
            <tr>
                <td class="left">
                    <div class="panel">
                        <h3>Category contribution</h3>
                        <div class="chart-box">{!! $charts['categories'] !!}</div>
                    </div>
                </td>
                <td class="right">
                    <div class="panel">
                        <h3>Order status mix</h3>
                        <div class="chart-box">{!! $charts['statuses'] !!}</div>
                        <table class="data">
                            <tbody>
                                @foreach (($analytics['order_statuses'] ?? []) as $row)
                                    <tr>
                                        <td>{{ $row['status'] }}</td>
                                        <td><strong>{{ $row['total'] }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="footer-bar">
        <span>{{ $store->name }} · Performance</span>
        <span class="right">04</span>
    </div>
</section>
@endif

{{-- PRODUCTS --}}
@if (in_array($meta['type'], ['company', 'products'], true))
<section class="page">
    <div class="pad">
        <p class="eyebrow">Section 03</p>
        <h1>{{ $meta['type'] === 'products' ? 'Catalogue gallery' : 'Product highlights' }}</h1>
        <div class="rule"></div>

        @if (!empty($catalogue))
            <table class="gallery">
                @foreach (collect($catalogue)->take(8)->chunk(4) as $row)
                    <tr>
                        @foreach ($row as $item)
                            <td>
                                <div class="frame">
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="">
                                    @else
                                        <div style="height:78px;background:#ddd;"></div>
                                    @endif
                                    <div class="cap">{{ \Illuminate\Support\Str::limit($item['name'], 28) }}</div>
                                    <div class="sub">{{ money($item['price'], $currency) }} · stock {{ $item['stock'] }}</div>
                                </div>
                            </td>
                        @endforeach
                        @for ($i = count($row); $i < 4; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
            <div class="rule-wide"></div>
        @endif

        <h3>Bestsellers in period</h3>
        <table class="data">
            <thead>
                <tr>
                    <th></th>
                    <th>Product</th>
                    <th>Units</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products_block as $item)
                    <tr>
                        <td style="width:40px;">
                            @if ($item['image'])
                                <img class="thumb" src="{{ $item['image'] }}" alt="">
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item['name'] }}</strong>
                            @if (!empty($item['category']))
                                <div class="muted">{{ $item['category'] }}</div>
                            @endif
                        </td>
                        <td>{{ number_format($item['units']) }}</td>
                        <td><strong>{{ money($item['revenue'], $currency) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="4">No product sales in this period.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if (!empty($analytics['low_stock']))
            <div style="margin-top:14px;" class="panel-accent">
                <h3>Stock risk register</h3>
                <table class="data">
                    <thead><tr><th>Product</th><th>Variant</th><th>Qty</th></tr></thead>
                    <tbody>
                        @foreach ($analytics['low_stock'] as $variant)
                            <tr>
                                <td>{{ $variant->product->name ?? 'Product' }}</td>
                                <td>{{ method_exists($variant, 'label') ? $variant->label() : '—' }}</td>
                                <td><span class="pill">{{ $variant->stock_quantity }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="footer-bar">
        <span>{{ $store->name }} · Products</span>
        <span class="right">05</span>
    </div>
</section>
@endif

{{-- CUSTOMERS --}}
@if (in_array($meta['type'], ['company', 'customers'], true))
<section class="page">
    <div class="pad">
        <p class="eyebrow">Section 04</p>
        <h1>{{ $meta['type'] === 'customers' ? 'Customer leaderboard' : 'Customer intelligence' }}</h1>
        <div class="rule"></div>

        <table class="two-col">
            <tr>
                <td class="left">
                    <div class="panel">
                        <h3>Audience devices</h3>
                        <div class="chart-box">{!! $charts['devices'] !!}</div>
                        <table class="data">
                            <tbody>
                                @foreach (($analytics['devices'] ?? []) as $device)
                                    <tr>
                                        <td>{{ $device['label'] }}</td>
                                        <td><strong>{{ $device['share'] }}%</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
                <td class="right">
                    <div class="panel-accent">
                        <h3>Loyalty proxy</h3>
                        <p>Returning visitor share</p>
                        <div class="stat-inline">{{ $analytics['returning_share'] ?? 0 }}%</div>
                        <p class="muted" style="margin-top:8px;">Use this as a rehearsal signal for retention programmes and post-purchase messaging.</p>
                    </div>
                    <div class="panel">
                        <h3>Open work</h3>
                        <p>Pending fulfilment: <strong>{{ number_format((int) ($analytics['pending'] ?? 0)) }}</strong></p>
                        <p>Pending reviews: <strong>{{ number_format((int) ($analytics['pending_reviews'] ?? 0)) }}</strong></p>
                    </div>
                </td>
            </tr>
        </table>

        <h3>Top customers by spend</h3>
        <table class="data">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Tier</th>
                    <th>Orders</th>
                    <th>Spend</th>
                    <th>Last order</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers_block as $customer)
                    <tr>
                        <td>
                            <strong>{{ $customer['name'] }}</strong>
                            <div class="muted">{{ $customer['email'] }}</div>
                        </td>
                        <td><span class="pill {{ $customer['tier'] === 'Loyal' ? '' : ($customer['tier'] === 'Returning' ? 'pill-dark' : 'pill-soft') }}">{{ $customer['tier'] }}</span></td>
                        <td>{{ $customer['orders'] }}</td>
                        <td><strong>{{ money($customer['spend'], $currency) }}</strong></td>
                        <td>{{ $customer['last_order'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No customer orders in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="footer-bar">
        <span>{{ $store->name }} · Customers</span>
        <span class="right">06</span>
    </div>
</section>
@endif

{{-- GEO + OUTLOOK --}}
<section class="page">
    <div class="pad">
        <p class="eyebrow">Section 05</p>
        <h1>Geography &amp; outlook</h1>
        <div class="rule"></div>

        <div class="panel">
            <h3>Simulated Kenya client origins</h3>
            <p class="muted">Demonstration geography for online session concentration by county.</p>
            <table class="data">
                <thead>
                    <tr><th>County</th><th>Sessions</th><th>Orders</th><th>Share</th></tr>
                </thead>
                <tbody>
                    @foreach (array_slice($analytics['geo']['top_counties'] ?? [], 0, 10) as $county)
                        <tr>
                            <td><strong>{{ $county['name'] }}</strong></td>
                            <td>{{ number_format($county['sessions']) }}</td>
                            <td>{{ number_format($county['orders']) }}</td>
                            <td>{{ $county['share'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if (!empty($orders_block))
            <h3>Recent order strip</h3>
            <table class="data">
                <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach ($orders_block as $order)
                        <tr>
                            <td>{{ $order['number'] }}</td>
                            <td>{{ $order['customer'] }}</td>
                            <td>{{ $order['status'] }}</td>
                            <td>{{ money($order['total'], $currency) }}</td>
                            <td>{{ $order['date'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="panel-accent" style="margin-top:14px;">
            <h3>Forward recommendations</h3>
            <ol class="note-list">
                <li>Protect the top revenue SKUs and counties while the projected growth signal remains {{ ($projections['growth_pct'] ?? 0) >= 0 ? 'non-negative' : 'soft' }}.</li>
                <li>Convert pending fulfilment and reviews before launching net-new merchandising experiments.</li>
                <li>Rehearse a weekly operating rhythm: sales pulse → stock risk → customer tiers → county focus.</li>
            </ol>
        </div>
    </div>
    <div class="footer-bar">
        <span>{{ $store->name }} · Outlook · End of report</span>
        <span class="right">Final</span>
    </div>
</section>

</body>
</html>
