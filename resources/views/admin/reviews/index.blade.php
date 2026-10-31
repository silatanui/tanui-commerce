@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')
<div class="page-head">
    <h1>Reviews</h1>
    <form>
        <select class="form-select form-select-sm" name="status" onchange="this.form.submit()" aria-label="Filter by status" style="min-width:9rem">
            <option value="">All statuses</option>
            @foreach (['pending','approved','rejected'] as $option)
                <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
            @endforeach
        </select>
    </form>
</div>
<div class="admin-table-wrap">
    <table class="table table-hover admin-datatable table-sm align-middle">
        <thead>
            <tr>
                <th>Product</th>
                <th>Customer</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reviews as $review)
                <tr>
                    <td><strong>{{ $review->product->name }}</strong></td>
                    <td>{{ $review->user->name }}</td>
                    <td>{{ $review->rating }}/5</td>
                    <td>{{ \Illuminate\Support\Str::limit($review->review, 72) }}</td>
                    <td><span class="status-pill is-{{ $review->status }}">{{ $review->status }}</span></td>
                    <td class="text-end">
                        <form method="post" action="{{ route('admin.reviews.update', $review) }}" class="table-actions">
                            @csrf
                            @method('PATCH')
                            <select class="form-select" name="status">
                                <option value="pending" @selected($review->status === 'pending')>Pending</option>
                                <option value="approved" @selected($review->status === 'approved')>Approved</option>
                                <option value="rejected" @selected($review->status === 'rejected')>Rejected</option>
                            </select>
                            <button class="btn-table btn-table-primary" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
