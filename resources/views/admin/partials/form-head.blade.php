@props([
    'title',
    'subtitle' => null,
    'backRoute' => null,
    'backLabel' => 'Back',
    'aiResource' => null,
])
<div class="af-head">
    <div>
        @if ($backRoute)
            <a class="af-back" href="{{ $backRoute }}">← {{ $backLabel }}</a>
        @endif
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    <div class="af-head__actions">
        @if ($aiResource)
            <button type="button" class="btn-admin af-ai-all" data-ai-fill="{{ $aiResource }}">
                <i data-lucide="sparkles" aria-hidden="true"></i> AI fill
            </button>
        @endif
        {{ $actions ?? '' }}
    </div>
</div>
