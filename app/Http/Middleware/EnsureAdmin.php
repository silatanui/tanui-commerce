<?php

namespace App\Http\Middleware;

use App\Models\ProductReview;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('admin.login'));
        }

        if (! $user->isAdmin() || ! $user->isActive()) {
            abort(403, 'Administrator access is required.');
        }

        if (! $request->session()->has('admin_store_id')) {
            $storeId = Store::query()->orderBy('name')->value('id');

            if ($storeId) {
                $request->session()->put('admin_store_id', $storeId);
            }
        }

        $adminStore = Store::query()->find($request->session()->get('admin_store_id'));

        if ($adminStore) {
            $adminStore->load('settings');
            view()->share('adminStore', $adminStore);
            view()->share('adminStores', Store::query()->orderBy('name')->get());
            view()->share(
                'pendingReviews',
                ProductReview::query()
                    ->where('status', 'pending')
                    ->whereHas('product', fn ($query) => $query->where('store_id', $adminStore->id))
                    ->count()
            );
        }

        return $next($request);
    }
}
