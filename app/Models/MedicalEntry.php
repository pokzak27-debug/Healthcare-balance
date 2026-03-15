<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalEntry extends Model
{
    protected $fillable = ['patient_id', 'appointment_id', 'user_id', 'type', 'content', 'entry_date'];

    public const TYPES = [
        'diagnostic' => 'Diagnostic',
        'maladie' => 'Maladie',
        'traitement' => 'Traitement',
        'note_clinique' => 'Note clinique',
    ];

    protected function casts(): array
    {
        return ['entry_date' => 'date'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
