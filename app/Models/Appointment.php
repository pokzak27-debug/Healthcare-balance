<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'pole_id',
        'room_id',
        'prestation_id',
        'user_id',
        'appointment_date',
        'appointment_time',
        'duration_minutes',
        'status',
        'reason',
        'notes',
        'custom_data',
        'arrived_at',
    ];

    public const DURATIONS = [15, 30, 45, 60, 90, 120];

    public function endTime(): string
    {
        $start = \Carbon\Carbon::parse($this->appointment_time);
        return $start->addMinutes($this->duration_minutes ?? 30)->format('H:i');
    }

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'duration_minutes' => 'integer',
            'custom_data' => 'array',
            'arrived_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function pole(): BelongsTo
    {
        return $this->belongsTo(Pole::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
