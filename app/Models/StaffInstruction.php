<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StaffInstruction extends Model
{
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'instruction',
        'is_done',
        'done_at',
        'patient_id',
        'appointment_id',
        'room_id',
        'prestation_id',
        'prestation_ids',
        'duration_minutes',
        'voir_patient',
        'is_read',
        'read_at',
        'read_by',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'done_at' => 'datetime',
            'voir_patient' => 'boolean',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'duration_minutes' => 'integer',
            'prestation_ids' => 'array',
        ];
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    /** Prestations multiples (sélection). */
    public function getPrestationsListAttribute(): \Illuminate\Support\Collection
    {
        $ids = $this->prestation_ids;
        if (is_array($ids) && count($ids) > 0) {
            return \App\Models\Prestation::whereIn('id', $ids)->ordered()->get();
        }
        if ($this->prestation_id && $this->relationLoaded('prestation') && $this->prestation) {
            return collect([$this->prestation]);
        }
        if ($this->prestation_id) {
            $p = \App\Models\Prestation::find($this->prestation_id);
            return $p ? collect([$p]) : collect();
        }
        return collect();
    }

    public function getPrestationNamesAttribute(): string
    {
        return $this->prestations_list->pluck('name')->implode(', ') ?: '—';
    }

    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function scopePendingForStaff(Builder $query, ?int $userId = null): Builder
    {
        return $query->where('is_done', false)
            ->where(function (Builder $q) use ($userId) {
                $q->whereNull('to_user_id')->orWhere('to_user_id', $userId);
            })
            ->orderByDesc('created_at');
    }
}
