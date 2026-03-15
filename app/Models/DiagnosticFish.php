<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosticFish extends Model
{
    protected $table = 'diagnostic_fishes';

    protected $fillable = [
        'patient_id',
        'user_id',
        'examen_clinique',
        'examen_subjectif',
        'examen_objectif',
        'examen_dynamique',
        'examen_statique',
        'diagnostic',
        'protocole',
        'date_examen',
    ];

    protected function casts(): array
    {
        return ['date_examen' => 'date'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
