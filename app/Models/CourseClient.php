<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseClient extends Model
{
    protected $fillable = [
        'course_id',
        'client_id',
        'montant_facture',
        'type_marchandise',
        'poids_kg',
        'description_marchandise',
    ];

    protected $casts = ['montant_facture' => 'decimal:2'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}