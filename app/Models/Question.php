<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {
    protected $fillable = [
        'category', 'question_text', 'option_a', 'option_b', 
        'option_c', 'option_d', 'option_e', 'correct_answer', 'tkp_points'
    ];

    protected $casts = [
        'tkp_points' => 'array',
    ];
}