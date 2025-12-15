<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = [
        'participant_name', 'score_twk', 'score_tiu', 
        'score_tkp', 'is_finished', 'answers_data'
    ];
}