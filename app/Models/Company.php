<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function clients()
        {
            return $this->hasMany(Client::class);
        }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
    public function course()
    {
        return $this->hasMany(Course::class);
    }
    protected $fillable = [
        'nom',
        'contact',
        'adresse',
    ];
}
