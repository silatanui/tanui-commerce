<?php

namespace App\Http\Middleware;

use App\Models\DemoSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = current_store();

        if ($store && config('demo.mode')) {
            DemoSession::query()->firstOrCreate(
                [
                    'session_id' => $request->session()->getId(),
                    'store_id' => $store->id,
                ],
                ['expires_at' => now()->addDays(7)]
            );
        }

        return $next($request);
    }
}
