<?php

namespace App\Models;

use App\Models\Machine;
use App\Models\Patient;
use App\Models\Prestation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'cabinet_id',
        'name',
        'slug',
        'is_active',
        'sort_order',
        'status',
        'current_appointment_id',
        'current_patient_id',
        'current_prestation_id',
        'current_machine_id',
        'occupied_started_at',
    ];

    public const STATUSES = [
        'free' => 'Libre',
        'occupied' => 'Occupée',
        'cleaning' => 'Nettoyage',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'occupied_started_at' => 'datetime',
        ];
    }

    public function currentPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'current_patient_id');
    }

    public function currentPrestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class, 'current_prestation_id');
    }

    public function currentMachine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'current_machine_id');
    }

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class)->orderBy('sort_order')->orderBy('name');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function currentAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'current_appointment_id');
    }

    public function prestations(): HasMany
    {
        return $this->hasMany(Prestation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
