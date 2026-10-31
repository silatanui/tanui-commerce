<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analytics)
    {
        $store = admin_store();
        abort_unless($store, 404, 'Seed the demonstration stores before opening the dashboard.');

        return view('admin.dashboard', [
            'report' => $analytics->dashboard($store, now()->subDays(13)->startOfDay(), Carbon::now()->endOfDay()),
        ]);
    }
}
