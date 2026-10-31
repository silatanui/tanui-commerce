<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminContentAssistService;
use Illuminate\Http\Request;

class FormAssistController extends Controller
{
    public function suggest(Request $request, AdminContentAssistService $assist)
    {
        $store = admin_store();
        abort_unless($store, 404);

        $data = $request->validate([
            'resource' => ['required', 'in:product,category,brand,coupon,settings,blog,banner'],
            'context' => ['nullable', 'array'],
        ]);

        $result = $assist->suggest($store, $data['resource'], $data['context'] ?? []);

        return response()->json([
            'ok' => true,
            'suggestion' => $result,
        ]);
    }
}
