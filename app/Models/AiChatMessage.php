<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = [
        'ai_chat_session_id',
        'role',
        'content',
        'raw_content',
        'sources',
        'latency_ms',
    ];

    protected function casts(): array
    {
        return [
            'sources' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'ai_chat_session_id');
    }
}
