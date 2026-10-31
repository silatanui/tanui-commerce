<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAssistantService;
use App\Services\Ai\OpenAiClient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function index(OpenAiClient $openai)
    {
        $store = admin_store();
        abort_unless($store, 404, 'Seed the demonstration stores before opening the assistant.');

        return view('admin.assistant', [
            'configured' => (bool) config('ai.enabled') && $openai->configured(),
            'storeName' => $store->name,
            'defaultFrom' => now()->subDays(29)->toDateString(),
            'defaultTo' => now()->toDateString(),
        ]);
    }

    public function chat(Request $request, AdminAssistantService $assistant)
    {
        $store = admin_store();
        abort_unless($store, 404);

        $data = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:2000'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'history' => ['nullable', 'array', 'max:16'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->subDays(29)->startOfDay();
        $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $result = $assistant->ask(
            $store,
            $data['message'],
            $data['history'] ?? [],
            $from,
            $to,
        );

        return response()->json([
            'ok' => true,
            'reply' => $result,
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ]);
    }
}
