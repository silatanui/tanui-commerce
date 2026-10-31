<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, string $store, string $product)
    {
        $record = Product::query()->forStore(current_store())->published()->where('slug', $product)->firstOrFail();

        ProductReview::query()->create([
            'product_id' => $record->id,
            'user_id' => $request->user()->id,
            'rating' => $request->integer('rating'),
            'review' => $request->string('review')->toString(),
            'status' => 'pending',
        ]);

        return back()->with('status', 'Review submitted for demonstration moderation.');
    }
}
