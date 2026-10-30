<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model 
{
     protected $fillable = ['course_id', 'statut', 'change_at']; 
     protected $casts = ['change_at' => 'datetime']; 
     public function course() 
     { 
        return $this->belongsTo(Course::class); 
     } 
}
