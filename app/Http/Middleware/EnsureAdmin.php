<?php

namespace App\Http\Middleware;

use App\Models\ProductReview;
use App\Models\Store;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Portfolio demo: guests enter the admin area without a login form.
        if (! $user) {
            $admin = User::query()
                ->where('role', 'admin')
                ->where('status', 'active')
                ->orderBy('id')
                ->first();

            if (! $admin) {
                abort(503, 'No demonstration administrator is available. Seed the database first.');
            }

            Auth::login($admin);
            $user = $admin;
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
