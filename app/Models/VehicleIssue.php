<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleIssue extends Model
{
    public const TYPES = [
        'panne' => 'Panne moteur',
        'pneus' => 'Pneus / crevaison',
        'freins' => 'Freins',
        'carrosserie' => 'Carrosserie / accident',
        'electrique' => 'Électricité / éclairage',
        'autre' => 'Autre',
    ];

    public const GRAVITES = [
        'faible' => 'Faible',
        'moyenne' => 'Moyenne',
        'urgente' => 'Urgente',
    ];

    protected $fillable = [
        'company_id', 'vehicle_id', 'user_id',
        'type', 'gravite', 'description', 'statut', 'resolu_at',
    ];

    protected function casts(): array
    {
        return ['resolu_at' => 'datetime'];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getGraviteLibelleAttribute(): string
    {
        return self::GRAVITES[$this->gravite] ?? ucfirst($this->gravite);
    }
}
