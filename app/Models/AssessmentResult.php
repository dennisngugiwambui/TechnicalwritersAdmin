<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentResult extends Model
{
    protected $fillable = [
        'user_id',
        'assessment_id',
        'assessment_type',
        'score',
        'max_score',
        'percentage',
        'passed',
        'time_taken',
        'completed_at'
    ];
    
    protected $casts = [
        'percentage' => 'float',
        'passed' => 'boolean',
        'completed_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
