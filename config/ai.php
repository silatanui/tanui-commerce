<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI / RAG (thesis-aligned)
    |--------------------------------------------------------------------------
    |
    | Grounded storefront assistant: sanitize → embed → vector search → LLM.
    | Chat model defaults to gpt-4.1-mini (replacement for deprecated gpt-4o-mini).
    | Put secrets only in .env — never in code or the browser.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', true),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4.1-mini'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'embedding_dimensions' => (int) env('OPENAI_EMBEDDING_DIMENSIONS', 1536),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.4),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 500),
        'timeout' => (int) env('OPENAI_TIMEOUT', 45),
    ],

    'vector' => [
        // qdrant = thesis stack; database = local MySQL cosine fallback
        'driver' => env('AI_VECTOR_DRIVER', 'database'),
        'collection' => env('AI_QDRANT_COLLECTION', 'tanui_commerce_knowledge'),
        'top_k' => (int) env('AI_TOP_K', 5),
        'min_score' => (float) env('AI_MIN_SCORE', 0.35),
    ],

    'qdrant' => [
        'url' => rtrim(env('QDRANT_URL', 'http://127.0.0.1:6333'), '/'),
        'api_key' => env('QDRANT_API_KEY'),
        'timeout' => (int) env('QDRANT_TIMEOUT', 20),
    ],

    'chat' => [
        'max_history' => (int) env('AI_CHAT_MAX_HISTORY', 8),
        'system_prompt' => <<<'PROMPT'
You are the grounded shopping assistant for {store_name}, a demonstration storefront inside Tanui Commerce.
Answer only using the CONTEXT snippets below. If the context is insufficient, say you do not have that information in this store's catalogue.
Do not invent prices, stock, warranties, or product specs. Prefer concise, helpful answers.
Mention that prices are sample demonstration prices when discussing cost.
Never claim a real payment was taken.
PROMPT,
    ],

];
