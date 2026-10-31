<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->subDays(29)->startOfDay();
        $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay();

        return view('admin.analytics', [
            'report' => $analytics->dashboard(admin_store(), $from, $to),
        ]);
    }
}
