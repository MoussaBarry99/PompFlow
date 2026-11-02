<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'company_id',
        'driver_id',
        'immatriculation',
        'type',
        'capacite',
        'statut_entretien',
        'prochain_entretien',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'vehicle_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    protected function casts(): array
    {
        return ['prochain_entretien' => 'date'];
    }
    /** non_planifie | en_retard | bientot (≤ 14 jours) | ok */
public function getEntretienStatutAttribute(): string
    {
        $date = $this->prochain_entretien;

        return match (true) {
            $date === null => 'non_planifie',
            $date->lt(today()) => 'en_retard',
            $date->lte(today()->addDays(14)) => 'bientot',
            default => 'ok',
        };
    }
}