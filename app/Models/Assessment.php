<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'token',
        'started_at',
        'expires_at',
        'completed_at',
        'question_count'
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function result()
    {
        return $this->hasOne(AssessmentResult::class);
    }
}

