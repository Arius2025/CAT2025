<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'source',
        'audit_type',
        'status',
        'confidence',
        'issue_type',
        'old_answer',
        'suggested_answer',
        'old_explanation',
        'suggested_explanation',
        'notes',
        'payload',
        'reviewed_at',
        'applied_at',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'payload' => 'array',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(Soal::class, 'question_id');
    }
}
