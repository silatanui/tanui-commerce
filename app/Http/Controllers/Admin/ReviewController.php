<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $store = admin_store();
        $status = $request->string('status')->toString();

        $reviews = ProductReview::query()
            ->whereHas('product', fn ($query) => $query->where('store_id', $store->id))
            ->with(['product', 'user'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('admin.reviews.index', compact('reviews', 'status'));
    }

    public function update(Request $request, ProductReview $review)
    {
        abort_unless((int) $review->product->store_id === (int) session('admin_store_id'), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $review->update(['status' => $data['status']]);

        return back()->with('status', 'Review status updated.');
    }
}
