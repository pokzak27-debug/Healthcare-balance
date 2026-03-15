<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffActivity extends Model
{
    protected $fillable = ['user_id', 'activity', 'type', 'read_by_doctor'];

    public const TYPES = ['info' => 'Information', 'alert' => 'Alerte', 'report' => 'Compte rendu'];

    protected function casts(): array
    {
        return ['read_by_doctor' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
