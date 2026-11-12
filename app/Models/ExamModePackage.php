<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamModePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_mode_id',
        'code',
        'name',
        'description',
        'duration_minutes',
        'total_questions',
        'sections',
        'passing_rules',
        'score_rules',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'sections' => 'array',
        'passing_rules' => 'array',
        'score_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function examMode()
    {
        return $this->belongsTo(ExamMode::class);
    }
}
