<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $store = admin_store();
        abort_unless($store, 404, 'Seed the demonstration stores before opening reports.');

        return view('admin.reports.index', [
            'storeName' => $store->name,
            'defaultFrom' => now()->subDays(29)->toDateString(),
            'defaultTo' => now()->toDateString(),
        ]);
    }

    public function download(Request $request, ReportPdfService $reports)
    {
        $store = admin_store();
        abort_unless($store, 404);

        $data = $request->validate([
            'type' => ['required', 'in:company,products,customers'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        // Guard extremely long ranges for PDF generation time.
        if ($from->diffInDays($to) > 120) {
            return back()->withErrors(['to' => 'Choose a period of 120 days or fewer for PDF reports.']);
        }

        return $reports->download($store, $data['type'], $from, $to);
    }
}
