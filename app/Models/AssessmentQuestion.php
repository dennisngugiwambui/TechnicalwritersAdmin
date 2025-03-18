<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AssessmentQuestion extends Model
{
    protected $fillable = [
        'type',
        'question',
        'correct_answer',
        'options',
        'difficulty',
        'explanation'
    ];
    
    protected $casts = [
        'options' => 'array'
    ];
}
