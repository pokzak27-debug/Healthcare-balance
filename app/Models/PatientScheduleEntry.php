<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientScheduleEntry extends Model
{
    protected $fillable = [
        'patient_id',
        'date',
        'time',
        'pole_label',
        'source_appointment_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function sourceAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'source_appointment_id');
    }
}

