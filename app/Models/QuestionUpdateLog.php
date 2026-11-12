<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionUpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'source',
        'old_answer',
        'new_answer',
        'old_explanation',
        'new_explanation',
        'old_question',
        'new_question',
        'imported_by',
    ];

    public function question()
    {
        return $this->belongsTo(Soal::class, 'question_id');
    }

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
