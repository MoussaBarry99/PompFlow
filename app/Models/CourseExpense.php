<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseExpense extends Model
{
    protected $fillable = [
        'company_id',
        'course_id',
        'user_id',
        'type',
        'description',
        'montant',
    ];

    protected $casts = ['montant' => 'decimal:2'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}