<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'user_id',
        'question_id',
        'exam_mode_id',
        'sub_category_id',
        'kategori',
        'sub_topik',
        'selected_answer',
        'correct_answer',
        'is_answered',
        'is_correct',
        'score',
        'answered_at',
    ];

    protected $casts = [
        'is_answered' => 'boolean',
        'is_correct' => 'boolean',
        'score' => 'integer',
        'answered_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Ujian::class, 'exam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Soal::class, 'question_id');
    }

    public function examMode()
    {
        return $this->belongsTo(ExamMode::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
