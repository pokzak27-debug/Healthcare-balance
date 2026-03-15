<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'sexe',
        'email',
        'phone',
        'phone_digits',
        'address',
        'notes',
        'is_active',
        'cabinet_id',
        'custom_data',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'phone' => 'encrypted',
            'custom_data' => 'array',
        ];
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function cabinet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalEntries(): HasMany
    {
        return $this->hasMany(MedicalEntry::class)->orderByDesc('entry_date')->orderByDesc('created_at');
    }

    public function diagnosticFishes(): HasMany
    {
        return $this->hasMany(DiagnosticFish::class)->orderByDesc('date_examen')->orderByDesc('created_at');
    }

    protected static function booted(): void
    {
        static::saving(function (Patient $patient) {
            if ($patient->isDirty('phone') && $patient->phone) {
                $patient->phone_digits = preg_replace('/\D/', '', $patient->phone);
            }
        });
    }
}
