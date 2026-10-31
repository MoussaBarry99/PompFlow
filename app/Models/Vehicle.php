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
}