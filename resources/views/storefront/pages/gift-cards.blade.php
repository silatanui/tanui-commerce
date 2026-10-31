@extends('layouts.storefront')
@section('title', 'Gift cards · '.$store->name)
@section('content')
<section class="gift-hero">
    <div class="container">
        <div class="gift-hero__grid">
            <div class="gift-hero__copy">
                <p class="eyebrow">Demonstration only</p>
                <h1>Gift cards</h1>
                <p class="lede">Preview how a branded digital gift card would look for {{ $store->name }}. Amounts are samples — no balance is created and no email is sent.</p>
                <ul class="gift-hero__points">
                    <li>Choose a sample amount</li>
                    <li>Add a recipient and short note</li>
                    <li>See a confirmation preview — nothing is charged</li>
                </ul>
            </div>
            <div class="gift-card-preview" aria-hidden="true">
                <p class="gift-card-preview__brand">{{ $store->name }}</p>
                <strong>Digital gift card</strong>
                <em>Sample balance</em>
                <span>{{ money($denominations[1] ?? $denominations[0], $store->currency) }}</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 g-xl-5">
            <div class="col-lg-7">
                <div class="section-head mb-3">
                    <div>
                        <h2>Sample amounts</h2>
                        <p>Pick a denomination to start the preview form.</p>
                    </div>
                </div>

                <div class="gift-amounts" role="list">
                    @foreach ($denominations as $amount)
                        <button
                            type="button"
                            class="gift-amount {{ $loop->index === 1 ? 'is-active' : '' }}"
                            data-gift-amount="{{ $amount }}"
                            role="listitem"
                        >
                            <span>Gift card</span>
                            <strong>{{ money($amount, $store->currency) }}</strong>
                        </button>
                    @endforeach
                </div>

                <form class="gift-form panel p-4 mt-4" method="post" action="{{ route('store.gift-cards.preview', $store->slug) }}" id="gift-card-form">
                    @csrf
                    <input type="hidden" name="amount" id="gift-amount" value="{{ old('amount', $denominations[1] ?? $denominations[0]) }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="occasion">Occasion</label>
                            <select class="form-select" id="occasion" name="occasion" required>
                                @foreach ($occasions as $occasion)
                                    <option value="{{ $occasion }}" @selected(old('occasion') === $occasion)>{{ $occasion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="recipient_name">Recipient name</label>
                            <input class="form-control" id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" maxlength="80" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="recipient_email">Recipient email</label>
                            <input class="form-control" id="recipient_email" name="recipient_email" type="email" value="{{ old('recipient_email') }}" maxlength="120" required>
                            <p class="sample-note mt-1 mb-0">No message is delivered in this demonstration.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="message">Note <span class="text-muted">(optional)</span></label>
                            <textarea class="form-control" id="message" name="message" rows="3" maxlength="280">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-accent" type="submit">Preview gift card</button>
                        <a class="btn btn-outline-dark" href="{{ route('store.shop', $store->slug) }}">Continue shopping</a>
                    </div>
                </form>
            </div>

            <div class="col-lg-5">
                <aside class="gift-aside">
                    <h2 class="h5">How it would work live</h2>
                    <ol class="content-steps mt-3">
                        <li>
                            <strong>Purchase</strong>
                            <span>Customer pays through a real gateway; the card is issued with a unique code.</span>
                        </li>
                        <li>
                            <strong>Deliver</strong>
                            <span>Email or SMS to the recipient with branding from {{ $store->name }}.</span>
                        </li>
                        <li>
                            <strong>Redeem</strong>
                            <span>Code applies at checkout against catalogue totals and inventory rules.</span>
                        </li>
                    </ol>
                    <p class="gift-aside__note mb-0">This page stops at the preview step so clients can see the UX without storing balances or taking payments.</p>
                </aside>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('gift-amount');
  const preview = document.querySelector('.gift-card-preview span');
  document.querySelectorAll('[data-gift-amount]').forEach((button) => {
    button.addEventListener('click', () => {
      document.querySelectorAll('[data-gift-amount]').forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
      if (input) input.value = button.dataset.giftAmount || '';
      if (preview) preview.textContent = button.querySelector('strong')?.textContent || preview.textContent;
    });
  });
});
</script>
@endpush
