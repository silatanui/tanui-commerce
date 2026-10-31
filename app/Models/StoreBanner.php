<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreBanner extends Model
{
    protected $fillable = [
        'store_id',
        'title',
        'subtitle',
        'image',
        'link',
        'position',
        'sort_order',
        'status',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }
}
