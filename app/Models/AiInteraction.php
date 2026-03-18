<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInteraction extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'source',
        'context_type',
        'related_id',
        'prompt',
        'response',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(90));
    }
}

