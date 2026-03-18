<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'number', 'patient_id', 'appointment_id', 'amount', 'status',
        'quote_date', 'valid_until', 'description', 'notes', 'invoice_id', 'custom_data',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'sent' => 'Envoyé',
        'accepted' => 'Accepté',
        'rejected' => 'Refusé',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'quote_date' => 'date',
            'valid_until' => 'date',
            'custom_data' => 'array',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $last = static::whereYear('created_at', $year)->orderByDesc('id')->first();
        $seq = $last ? ((int) substr($last->number, -4)) + 1 : 1;

        return 'DEV-' . $year . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function canBeAccepted(): bool
    {
        return in_array($this->status, ['draft', 'sent'], true);
    }

    public function canBeConvertedToInvoice(): bool
    {
        return $this->status === 'accepted' && ! $this->invoice_id;
    }
}
