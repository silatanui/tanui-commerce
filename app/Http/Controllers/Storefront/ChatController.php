<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Ai\ChatCommerceService;
use App\Services\Ai\RagChatbotService;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function store(Request $request, string $store, RagChatbotService $chatbot, ChatCommerceService $commerce): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'session_token' => ['nullable', 'string', 'max:64'],
        ]);

        $storeModel = current_store();
        $session = $commerce->resolveSession(
            $storeModel,
            $data['session_token'] ?? null,
            $request->user()?->id,
            $request->ip(),
        );

        $intent = $chatbot->detectCommerceIntent($data['message']);

        if ($intent !== null) {
            return $this->handleIntent($intent, $storeModel, $session, $request, $commerce, $data['message']);
        }

        $result = $chatbot->ask(
            store: $storeModel,
            rawQuestion: $data['message'],
            sessionToken: $session->session_token,
            userId: $request->user()?->id,
            ip: $request->ip(),
        );

        return response()->json([
            'ok' => true,
            'reply' => $result['answer'],
            'sources' => $result['sources'],
            'products' => $result['products'] ?? [],
            'ui' => $result['ui'] ?? ['step' => 'browse', 'products' => $result['products'] ?? []],
            'latency_ms' => $result['latency_ms'],
            'session_token' => $result['session_token'] ?? $session->session_token,
            'model' => $result['model'] ?? null,
            'driver' => $result['driver'] ?? null,
            'demo_note' => 'Answers are grounded in this store’s demonstration catalogue only.',
        ]);
    }

    public function commerce(Request $request, string $store, ChatCommerceService $commerce): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'max:40'],
            'session_token' => ['nullable', 'string', 'max:64'],
            'product_id' => ['nullable', 'integer'],
            'variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:0', 'max:20'],
            'item_id' => ['nullable', 'integer'],
            'order_number' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'draft' => ['nullable', 'array'],
        ]);

        $storeModel = current_store();
        $session = $commerce->resolveSession(
            $storeModel,
            $data['session_token'] ?? null,
            $request->user()?->id,
            $request->ip(),
        );

        try {
            $result = match ($data['action']) {
                'cart.summary' => $commerce->cartSummary($storeModel),
                'cart.update' => $commerce->cartUpdate(
                    $storeModel,
                    (int) $data['item_id'],
                    (int) ($data['quantity'] ?? 1),
                ),
                'cart.remove' => $commerce->cartRemove($storeModel, (int) $data['item_id']),
                'buy_now' => $commerce->buyNow(
                    $storeModel,
                    (int) $data['product_id'],
                    (int) $data['variant_id'],
                    (int) ($data['quantity'] ?? 1),
                ),
                'checkout.start' => $commerce->checkoutStart($storeModel),
                'checkout.save' => $commerce->checkoutSave($storeModel, $session, $data['draft'] ?? []),
                'checkout.confirm' => $commerce->checkoutConfirm($storeModel, $session, $request->user()),
                'order.track' => $commerce->orderTrack(
                    $storeModel,
                    $data['order_number'] ?? null,
                    $data['email'] ?? null,
                    $session,
                ),
                default => throw ValidationException::withMessages(['action' => 'Unknown commerce action.']),
            };
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Request failed.',
                'errors' => $e->errors(),
                'session_token' => $session->session_token,
            ], 422);
        }

        $commerce->recordTurn(
            $session,
            $commerce->actionLabel($data['action'], $data),
            $result['reply'] ?? null,
            $result['ui'] ?? null,
        );

        return response()->json([
            'ok' => true,
            'reply' => $result['reply'] ?? null,
            'type' => $result['type'] ?? null,
            'products' => $result['products'] ?? [],
            'cart' => $result['cart'] ?? null,
            'order' => $result['order'] ?? null,
            'ui' => $result['ui'] ?? null,
            'session_token' => $result['session_token'] ?? $session->session_token,
            'demo_note' => 'Demo checkout only — no payment was collected.',
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
        $storeModel = current_store();

        return response()->json([
            'ok' => true,
            'session_token' => $token,
            'messages' => $chatbot->sessionMessages($storeModel, $token),
            'context' => $chatbot->sessionContext($storeModel, $token),
            'ui' => $chatbot->sessionContext($storeModel, $token)['last_ui'] ?? null,
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

    private function handleIntent(
        string $intent,
        $storeModel,
        $session,
        Request $request,
        ChatCommerceService $commerce,
        string $message,
    ): JsonResponse {
        try {
            $result = match ($intent) {
                'cart' => $commerce->cartSummary($storeModel),
                'checkout' => $commerce->checkoutStart($storeModel),
                'track' => $this->trackFromMessage($commerce, $storeModel, $session, $message),
                default => $commerce->cartSummary($storeModel),
            };
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Request failed.',
                'errors' => $e->errors(),
                'session_token' => $session->session_token,
            ], 422);
        }

        $commerce->recordTurn(
            $session,
            $message,
            $result['reply'] ?? null,
            $result['ui'] ?? null,
        );

        return response()->json([
            'ok' => true,
            'reply' => $result['reply'] ?? null,
            'type' => $result['type'] ?? null,
            'products' => [],
            'cart' => $result['cart'] ?? null,
            'order' => $result['order'] ?? null,
            'ui' => $result['ui'] ?? null,
            'sources' => [],
            'latency_ms' => 0,
            'session_token' => $session->session_token,
            'demo_note' => 'Demo commerce steps — no real payments.',
        ]);
    }

    private function trackFromMessage(ChatCommerceService $commerce, $storeModel, $session, string $message): array
    {
        $orderNumber = null;
        if (preg_match('/\b(TC-[A-Z0-9]+-\d{2}-\d+)\b/i', $message, $m)) {
            $orderNumber = strtoupper($m[1]);
        }

        $email = null;
        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $message, $em)) {
            $email = $em[0];
        }

        return $commerce->orderTrack($storeModel, $orderNumber, $email, $session);
    }
}
