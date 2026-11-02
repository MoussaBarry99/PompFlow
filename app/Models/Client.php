<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    //
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function courses()
    {
            return $this->belongsToMany(Course::class, 'course_clients');
    }
    protected $fillable = [
        'nom',
        'prenom',
        'adresse',
        'contact',
        'company_id',
    ];

    
}
