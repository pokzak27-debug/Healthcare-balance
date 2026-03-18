<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CabinetExpense extends Model
{
    protected $fillable = [
        'type', 'category', 'description', 'amount', 'expense_date', 'due_date', 'paid_at', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public const TYPE_SUPPLY = 'supply';
    public const TYPE_CHARGE = 'charge';

    public const SUPPLY_CATEGORIES = [
        'huiles' => 'Huiles / crèmes massage',
        'bandages' => 'Bandages / strapping',
        'compresses' => 'Compresses',
        'coton' => 'Coton / ouate',
        'desinfectant' => 'Désinfectant',
        'gants' => 'Gants',
        'gel_ultrasound' => 'Gel ultrasound',
        'papier_table' => 'Papier de table / housses',
        'electrodes' => 'Électrodes',
        'autres' => 'Autres fournitures',
    ];

    public const CHARGE_CATEGORIES = [
        'loyer' => 'Loyer',
        'electricite' => 'Électricité',
        'eau' => 'Eau',
        'telephone' => 'Téléphone / Internet',
        'assurances' => 'Assurances',
        'entretien' => 'Entretien / ménage',
        'autres' => 'Autres charges',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->type === self::TYPE_SUPPLY
            ? (self::SUPPLY_CATEGORIES[$this->category] ?? $this->category)
            : (self::CHARGE_CATEGORIES[$this->category] ?? $this->category);
    }
}
