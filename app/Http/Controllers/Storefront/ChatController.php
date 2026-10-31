<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Ai\RagChatbotService;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function store(Request $request, string $store, RagChatbotService $chatbot): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'session_token' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $chatbot->ask(
            store: current_store(),
            rawQuestion: $data['message'],
            sessionToken: $data['session_token'] ?? null,
            userId: $request->user()?->id,
            ip: $request->ip(),
        );

        return response()->json([
            'ok' => true,
            'reply' => $result['answer'],
            'sources' => $result['sources'],
            'products' => $result['products'] ?? [],
            'latency_ms' => $result['latency_ms'],
            'session_token' => $result['session_token'],
            'model' => $result['model'] ?? null,
            'driver' => $result['driver'] ?? null,
            'demo_note' => 'Answers are grounded in this store’s demonstration catalogue only.',
        ]);
    }

    public function conversations(Request $request, string $store, RagChatbotService $chatbot): JsonResponse
    {
        $data = $request->validate([
            'tokens' => ['nullable', 'array', 'max:30'],
            'tokens.*' => ['string', 'max:64'],
        ]);

        return response()->json([
            'ok' => true,
            'conversations' => $chatbot->sessionSummaries(current_store(), $data['tokens'] ?? []),
        ]);
    }

    public function messages(Request $request, string $store, string $token, RagChatbotService $chatbot): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'messages' => $chatbot->sessionMessages(current_store(), $token),
        ]);
    }

    public function addToCart(Request $request, string $store, CartService $carts): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'variant_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $storeModel = current_store();
        $product = Product::query()->forStore($storeModel)->published()->with('variants')->findOrFail($data['product_id']);
        $variant = $product->variants()->where('status', 'active')->findOrFail($data['variant_id']);

        $carts->add($storeModel, $product, $variant, (int) ($data['quantity'] ?? 1));

        return response()->json([
            'ok' => true,
            'message' => $product->name.' added to the demonstration cart.',
            'cart_count' => $carts->count($storeModel),
        ]);
    }
}
