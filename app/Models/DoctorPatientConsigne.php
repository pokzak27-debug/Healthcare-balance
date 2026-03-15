<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPatientConsigne extends Model
{
    protected $table = 'doctor_patient_consignes';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'room_id',
        'prestation_id',
        'duration_minutes',
        'doctor_sees_patient',
        'from_user_id',
        'status',
        'acknowledged_at',
        'acknowledged_by',
        'completed_at',
        'completed_by',
        'notes',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'acknowledged' => 'Lu',
        'completed' => 'Terminé',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'doctor_sees_patient' => 'boolean',
            'acknowledged_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
