@extends('layouts.admin')
@section('title', 'AI Assistant')
@section('content')
<div class="ai-page" id="admin-assistant"
     data-endpoint="{{ route('admin.assistant.chat') }}"
     data-configured="{{ $configured ? '1' : '0' }}">
    <header class="ai-hero">
        <div>
            <p class="ai-eyebrow">Decision support</p>
            <h1>AI Assistant</h1>
            <p class="ai-lead">Ask about {{ $storeName }} performance. The assistant reads live demonstration analytics and can return decisions, tables, and charts.</p>
        </div>
        <div class="ai-period">
            <div>
                <label for="ai-from">From</label>
                <input id="ai-from" type="date" value="{{ $defaultFrom }}">
            </div>
            <div>
                <label for="ai-to">To</label>
                <input id="ai-to" type="date" value="{{ $defaultTo }}">
            </div>
            <span class="ai-status" id="ai-status">{{ $configured ? 'Model ready' : 'Local mode' }}</span>
        </div>
    </header>

    <div class="ai-shell">
        <section class="ai-chat-card">
            <div class="ai-thread" id="ai-thread" aria-live="polite"></div>

            <div class="ai-suggestions" id="ai-suggestions">
                <button type="button" data-prompt="Summarise store performance and give three decisions I should take this week.">Weekly decisions</button>
                <button type="button" data-prompt="Show a revenue and orders trend chart and explain what changed.">Trend chart</button>
                <button type="button" data-prompt="Rank top products in a table and recommend what to restock or promote.">Product ranking</button>
                <button type="button" data-prompt="Where are online clients coming from in Kenya and what should we focus on by county?">Kenya counties</button>
                <button type="button" data-prompt="Compare device split and order status mix with charts, then suggest conversion improvements.">Conversion levers</button>
            </div>

            <form class="ai-composer" id="ai-form">
                @csrf
                <textarea id="ai-input" rows="2" maxlength="2000" placeholder="Ask about sales, conversion, inventory risk, Kenya origins, or what to do next…" required></textarea>
                <button type="submit" id="ai-send" class="btn-admin-dark">Ask</button>
            </form>
        </section>

        <aside class="ai-side">
            <article class="ai-side-card">
                <h2>What it can do</h2>
                <ul>
                    <li>Explain KPI movement vs the prior period</li>
                    <li>Generate ranking tables for products and counties</li>
                    <li>Render Chart.js visuals from analytics data</li>
                    <li>Recommend operational next steps</li>
                </ul>
            </article>
            <article class="ai-side-card">
                <h2>Grounding</h2>
                <p>Answers are built from the same AnalyticsService report used by Dashboard and Analytics, for the date range above.</p>
            </article>
            <article class="ai-side-card" id="ai-latest-actions" hidden>
                <h2>Latest actions</h2>
                <ol id="ai-actions-list"></ol>
            </article>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<style>
.ai-page{display:grid;gap:1rem}
.ai-hero{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:end;padding-bottom:.75rem;border-bottom:1px solid var(--admin-line)}
.ai-eyebrow{margin:0 0 .35rem;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--admin-muted);line-height:1em}
.ai-hero h1{margin:0;font-size:2rem;font-weight:700;letter-spacing:-.03em;line-height:1em}
.ai-lead{margin:.55rem 0 0;max-width:40rem;color:var(--admin-muted);font-size:.92rem;line-height:1.35}
.ai-period{display:flex;gap:.55rem;flex-wrap:wrap;align-items:end}
.ai-period label{display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-muted);margin-bottom:.28rem;line-height:1em}
.ai-period input{border:1px solid var(--admin-line);background:var(--admin-surface);color:var(--admin-ink);padding:.55rem .7rem;font:inherit}
.ai-status{align-self:center;border:1px solid var(--admin-line);padding:.45rem .7rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-muted);line-height:1em}

.ai-shell{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(240px,.7fr);gap:1rem;align-items:start}
.ai-chat-card,.ai-side-card{background:var(--admin-surface);border:1px solid var(--admin-line)}
.ai-chat-card{display:grid;grid-template-rows:minmax(360px,1fr) auto auto;min-height:640px}
.ai-thread{padding:1rem;overflow:auto;display:flex;flex-direction:column;gap:.85rem;background:linear-gradient(180deg,var(--admin-gray-6),var(--admin-surface) 28%)}
.ai-msg{max-width:92%;padding:.85rem .95rem;border:1px solid var(--admin-line);background:var(--admin-surface);display:grid;gap:.75rem}
.ai-msg--user{align-self:end;background:var(--admin-ink);color:#fff;border-color:var(--admin-ink)}
html.admin-theme-dark .ai-msg--user{background:#f2f2f2;color:#121212;border-color:#f2f2f2}
.ai-msg--assistant{align-self:start}
.ai-msg__meta{font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;opacity:.7;line-height:1em}
.ai-msg__text{font-size:.92rem;line-height:1.45;white-space:pre-wrap}
.ai-msg--user .ai-msg__text{line-height:1.35}
.ai-actions{margin:0;padding-left:1.1rem;display:grid;gap:.35rem;font-size:.86rem;line-height:1.3}
.ai-table-wrap{overflow:auto;border:1px solid var(--admin-line)}
.ai-table{width:100%;border-collapse:collapse;font-size:.82rem}
.ai-table th,.ai-table td{padding:.55rem .65rem;border-bottom:1px solid var(--admin-line);text-align:left;line-height:1.2}
.ai-table th{background:var(--admin-gray-6);font-weight:650;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--admin-muted)}
.ai-chart-block{border:1px solid var(--admin-line);padding:.75rem}
.ai-chart-block h3,.ai-table-wrap h3{margin:0 0 .55rem;font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-muted);line-height:1em}
.ai-chart-frame{position:relative;height:220px}

.ai-suggestions{display:flex;flex-wrap:wrap;gap:.45rem;padding:.75rem 1rem;border-top:1px solid var(--admin-line)}
.ai-suggestions button{border:1px solid var(--admin-line);background:var(--admin-gray-6);color:var(--admin-ink);padding:.45rem .7rem;font:inherit;font-size:.78rem;line-height:1em;cursor:pointer}
.ai-suggestions button:hover{background:var(--admin-surface)}

.ai-composer{display:grid;grid-template-columns:1fr auto;gap:.65rem;padding:1rem;border-top:1px solid var(--admin-line);align-items:end}
.ai-composer textarea{width:100%;border:1px solid var(--admin-line);background:var(--admin-surface);color:var(--admin-ink);padding:.75rem .85rem;font:inherit;resize:vertical;min-height:72px}
.ai-composer button{min-width:96px;justify-content:center}

.ai-side{display:grid;gap:1rem}
.ai-side-card{padding:1rem}
.ai-side-card h2{margin:0 0 .55rem;font-size:.78rem;text-transform:uppercase;letter-spacing:.07em;color:var(--admin-muted);line-height:1em}
.ai-side-card p,.ai-side-card li{color:var(--admin-muted);font-size:.86rem;line-height:1.35}
.ai-side-card ul,.ai-side-card ol{margin:0;padding-left:1.1rem;display:grid;gap:.35rem}
.ai-side-card ol{color:var(--admin-ink)}
.ai-empty{margin:auto;text-align:center;color:var(--admin-muted);max-width:22rem;padding:2rem 1rem}
.ai-empty strong{display:block;color:var(--admin-ink);margin-bottom:.45rem;font-size:1rem}
.ai-typing{opacity:.7;font-size:.84rem}

@media (max-width:1100px){
  .ai-shell{grid-template-columns:1fr}
  .ai-chat-card{min-height:560px}
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/admin-assistant.js') }}?v={{ @filemtime(public_path('js/admin-assistant.js')) ?: time() }}"></script>
@endpush
