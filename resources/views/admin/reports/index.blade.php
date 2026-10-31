@extends('layouts.admin')
@section('title', 'Reports')
@section('content')
<div class="rp-page">
    <header class="rp-hero">
        <div>
            <p class="rp-eyebrow">Executive downloads</p>
            <h1>PDF Reports</h1>
            <p class="rp-lead">Generate designed dossiers for {{ $storeName }} with cover pages, contents, charts, projections, product imagery and decision notes. Choose a period, then download.</p>
        </div>
    </header>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form class="rp-form" method="post" action="{{ route('admin.reports.download') }}" target="_blank">
        @csrf
        <section class="rp-card">
            <h2>Reporting period</h2>
            <p>All figures, charts and projections are calculated inside this window.</p>
            <div class="rp-period">
                <div>
                    <label for="from">From</label>
                    <input id="from" type="date" name="from" value="{{ old('from', $defaultFrom) }}" required>
                </div>
                <div>
                    <label for="to">To</label>
                    <input id="to" type="date" name="to" value="{{ old('to', $defaultTo) }}" required>
                </div>
                <div class="rp-presets" role="group" aria-label="Quick ranges">
                    <button type="button" data-days="7">7 days</button>
                    <button type="button" data-days="14">14 days</button>
                    <button type="button" data-days="30">30 days</button>
                    <button type="button" data-days="90">90 days</button>
                </div>
            </div>
        </section>

        <section class="rp-grid">
            <article class="rp-option">
                <input type="radio" name="type" id="type-company" value="company" checked>
                <label for="type-company">
                    <span class="rp-option__tag">Combined</span>
                    <strong>Company performance</strong>
                    <span>Full executive report: sales, products, customers, operations, Kenya origins, outlook.</span>
                </label>
            </article>
            <article class="rp-option">
                <input type="radio" name="type" id="type-products" value="products">
                <label for="type-products">
                    <span class="rp-option__tag">Catalogue</span>
                    <strong>Products report</strong>
                    <span>Gallery, bestsellers, category mix, stock risk and assortment projections.</span>
                </label>
            </article>
            <article class="rp-option">
                <input type="radio" name="type" id="type-customers" value="customers">
                <label for="type-customers">
                    <span class="rp-option__tag">Audience</span>
                    <strong>Customers report</strong>
                    <span>Leaderboard, loyalty tiers, device mix, geographic focus and retention notes.</span>
                </label>
            </article>
        </section>

        <div class="rp-actions">
            <button type="submit" class="btn-admin-dark"><i data-lucide="download" aria-hidden="true"></i> Download PDF</button>
            <p>Designed multi-page A4 dossier · charts include a 7-day projection ribbon</p>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.rp-page{display:grid;gap:1rem;max-width:980px}
.rp-hero{padding-bottom:.75rem;border-bottom:1px solid var(--admin-line)}
.rp-eyebrow{margin:0 0 .35rem;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--admin-muted);line-height:1em}
.rp-hero h1{margin:0;font-size:2rem;font-weight:700;letter-spacing:-.03em;line-height:1em}
.rp-lead{margin:.55rem 0 0;color:var(--admin-muted);font-size:.92rem;line-height:1.35;max-width:42rem}
.rp-form{display:grid;gap:1rem}
.rp-card{background:var(--admin-surface);border:1px solid var(--admin-line);padding:1rem}
.rp-card h2{margin:0 0 .35rem;font-size:1.05rem;letter-spacing:-.02em;line-height:1em}
.rp-card > p{margin:0 0 .85rem;color:var(--admin-muted);font-size:.86rem;line-height:1.3}
.rp-period{display:flex;flex-wrap:wrap;gap:.7rem;align-items:end}
.rp-period label{display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--admin-muted);margin-bottom:.28rem;line-height:1em}
.rp-period input{border:1px solid var(--admin-line);background:var(--admin-surface);color:var(--admin-ink);padding:.55rem .7rem;font:inherit}
.rp-presets{display:flex;flex-wrap:wrap;gap:.4rem}
.rp-presets button{border:1px solid var(--admin-line);background:var(--admin-gray-6);color:var(--admin-ink);padding:.5rem .7rem;font:inherit;font-size:.78rem;line-height:1em;cursor:pointer}
.rp-presets button:hover{background:var(--admin-surface)}
.rp-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
.rp-option{position:relative}
.rp-option input{position:absolute;opacity:0;pointer-events:none}
.rp-option label{display:grid;gap:.45rem;min-height:100%;background:var(--admin-surface);border:1px solid var(--admin-line);padding:1rem;cursor:pointer}
.rp-option input:checked + label{border-color:var(--admin-ink);box-shadow:inset 3px 0 0 var(--admin-ink)}
.rp-option__tag{font-size:.68rem;letter-spacing:.07em;text-transform:uppercase;color:var(--admin-muted);line-height:1em}
.rp-option strong{font-size:1rem;letter-spacing:-.02em;line-height:1.1}
.rp-option span:last-child{color:var(--admin-muted);font-size:.84rem;line-height:1.35}
.rp-actions{display:flex;flex-wrap:wrap;gap:.85rem;align-items:center}
.rp-actions p{margin:0;color:var(--admin-muted);font-size:.8rem;line-height:1.3}
@media (max-width:900px){.rp-grid{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
(() => {
  const from = document.getElementById('from');
  const to = document.getElementById('to');
  document.querySelectorAll('.rp-presets [data-days]').forEach((button) => {
    button.addEventListener('click', () => {
      const days = Number(button.dataset.days || 30);
      const end = new Date();
      const start = new Date();
      start.setDate(end.getDate() - (days - 1));
      const fmt = (d) => d.toISOString().slice(0, 10);
      from.value = fmt(start);
      to.value = fmt(end);
    });
  });
})();
</script>
@endpush
