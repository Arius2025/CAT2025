<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'condition_json',
        'result_text',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'condition_json' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
}
