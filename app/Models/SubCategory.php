<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kind',
        'exam_mode_id',
    ];

    public function scopeByKind($query, ?string $kind = null)
    {
        $kind = trim((string) ($kind ?? ''));
        if ($kind === '' || !\Illuminate\Support\Facades\Schema::hasColumn('sub_categories', 'kind')) {
            return $query;
        }

        return $query->where('kind', $kind);
    }

    public function examMode()
    {
        return $this->belongsTo(ExamMode::class);
    }

    public function soals()
    {
        return $this->hasMany(Soal::class);
    }
}
