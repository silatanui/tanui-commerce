<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDocument extends Model
{
    protected $fillable = [
        'store_id',
        'source_type',
        'source_id',
        'external_id',
        'title',
        'content',
        'content_hash',
        'embedding',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
