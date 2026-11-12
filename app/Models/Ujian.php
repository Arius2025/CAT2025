<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujians';

    protected $fillable = [
        'user_id',
        'nama_peserta',
        'paket',
        'exam_mode_id',
        'sub_category_id',
        'exam_mode_package_id',
        'sub_topik_filter',
        'jawaban_user',
        'skor_twk',
        'skor_tiu',
        'skor_tkp',
        'total_score',
        'is_selesai',
        'sisa_waktu',
        'duration_minutes',
        'analitik_topik',
        'score_breakdown',
        'result_summary',
        'package_snapshot',
        'waktu_pengerjaan'
    ];

    protected $casts = [
        'jawaban_user' => 'array',
        'is_selesai' => 'boolean',
        'skor_twk' => 'integer',
        'skor_tiu' => 'integer',
        'skor_tkp' => 'integer',
        'total_score' => 'integer',
        'analitik_topik' => 'array',
        'score_breakdown' => 'array',
        'result_summary' => 'array',
        'package_snapshot' => 'array',
    ];

    public function getTotalSkorAttribute()
    {
        return !is_null($this->total_score) ? (int) $this->total_score : ($this->skor_twk + $this->skor_tiu + $this->skor_tkp);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function examMode()
    {
        return $this->belongsTo(ExamMode::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function packageConfig()
    {
        return $this->belongsTo(ExamModePackage::class, 'exam_mode_package_id');
    }
}
