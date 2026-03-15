<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'patient_id', 'appointment_id', 'amount', 'status',
        'invoice_date', 'due_date', 'description', 'notes', 'custom_data', 'confirmed_at',
    ];

    public const STATUSES = ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'cancelled' => 'Annulée'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'invoice_date' => 'date',
            'due_date' => 'date',
            'custom_data' => 'array',
            'confirmed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function quote(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $last = static::whereYear('created_at', $year)->orderByDesc('id')->first();
        $seq = $last ? ((int) substr($last->number, -4)) + 1 : 1;

        return 'FAC-' . $year . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
