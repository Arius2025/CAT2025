<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_questions',
        'source_scope',
        'format',
        'filter_used',
        'question_ids',
    ];

    protected $casts = [
        'filter_used' => 'array',
        'question_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
