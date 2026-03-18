<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssistantLog extends Model
{
    use Prunable;
    protected $table = 'ai_assistant_logs';

    protected $fillable = [
        'user_id',
        'patient_id',
        'context_type',
        'prompt_key',
        'request_payload',
        'response_text',
        'response_json',
        'model',
        'tokens_used',
        'ip',
        'user_agent',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_json' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(90));
    }
}
