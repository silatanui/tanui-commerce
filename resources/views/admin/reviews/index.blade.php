@extends('layouts.admin')
@section('content')
<h1 class="h3">Reviews</h1>
<form class="mb-3"><select class="form-select" name="status" onchange="this.form.submit()"><option value="">All</option>@foreach (['pending','approved','rejected'] as $option)<option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>@endforeach</select></form>
@foreach ($reviews as $review)
<article class="card card-body mb-3">
    <strong>{{ $review->product->name }}</strong> · {{ $review->user->name }} · {{ $review->rating }}/5
    <p>{{ $review->review }}</p>
    <form method="post" action="{{ route('admin.reviews.update', $review) }}" class="d-flex gap-2">
        @csrf
        @method('PATCH')
        <select class="form-select" name="status"><option value="pending" @selected($review->status === 'pending')>Pending</option><option value="approved" @selected($review->status === 'approved')>Approved</option><option value="rejected" @selected($review->status === 'rejected')>Rejected</option></select>
        <button class="btn btn-dark">Save</button>
    </form>
</article>
@endforeach
{{ $reviews->links() }}
@endsection
