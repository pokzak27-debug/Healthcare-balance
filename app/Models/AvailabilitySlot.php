<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilitySlot extends Model
{
    protected $fillable = [
        'user_id',
        'pole_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_minutes',
        'is_active',
    ];

    public const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    public const SLOT_MINUTES = [15, 30, 45, 60];

    protected function casts(): array
    {
        return [
            'slot_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pole(): BelongsTo
    {
        return $this->belongsTo(Pole::class);
    }

    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '';
    }
}
