<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuestionAudit;
use App\Models\SoalAudit;
use Illuminate\Support\Facades\Schema;

class Soal extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori', 
        'standar_tahun',
        'sub_topik',
        'exam_mode_id',
        'sub_category_id',
        'difficulty',
        'created_by',
        'teks_soal', 
        'opsi_a', 
        'opsi_b', 
        'opsi_c', 
        'opsi_d', 
        'opsi_e', 
        'jawaban_benar', 
        'bobot_tkp', 
        'pembahasan',
        'hash_soal',
        'qc_status',
        'qc_checked_at',
        'qc_confidence',
        'audit_status',
        'audited_at',
        'audited_by',
        'last_audit_source',
        'last_audit_score',
        'gambar_path', 'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d', 'gambar_e',
        'prompt_gambar_soal', 'prompt_gambar_a', 'prompt_gambar_b', 'prompt_gambar_c', 'prompt_gambar_d', 'prompt_gambar_e'
    ];

    /**
     * Casting kolom bobot_tkp agar otomatis menjadi array.
     */
    protected $casts = [
        'bobot_tkp' => 'array',
        'qc_checked_at' => 'datetime',
        'qc_confidence' => 'decimal:2',
        'audited_at' => 'datetime',
        'last_audit_score' => 'decimal:2',
    ];

    public function audits()
    {
        return $this->hasMany(SoalAudit::class);
    }

    public function questionAudits()
    {
        return $this->hasMany(QuestionAudit::class, 'question_id');
    }

    public function examMode()
    {
        return $this->belongsTo(ExamMode::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'question_id');
    }

    public function scopeForExamMode($query, ?int $examModeId = null, ?int $defaultSkdId = null)
    {
        if (!$examModeId || !Schema::hasColumn('soals', 'exam_mode_id')) {
            return $query;
        }

        return $query->where(function ($q) use ($examModeId, $defaultSkdId) {
            $q->where('exam_mode_id', $examModeId);

            if ($defaultSkdId && $examModeId === $defaultSkdId) {
                $q->orWhereNull('exam_mode_id');
            }
        });
    }

    public function scopeForSubCategory($query, ?int $subCategoryId = null)
    {
        if (!$subCategoryId || !Schema::hasColumn('soals', 'sub_category_id')) {
            return $query;
        }

        return $query->where(function ($q) use ($subCategoryId) {
            $q->where('sub_category_id', $subCategoryId)
              ->orWhereNull('sub_category_id');
        });
    }

    public function scopeStandar2027($query)
    {
        if (!Schema::hasColumn('soals', 'standar_tahun')) {
            return $query;
        }

        return $query->where('standar_tahun', '2027');
    }

    public function scopePra2027($query)
    {
        if (!Schema::hasColumn('soals', 'standar_tahun')) {
            return $query->whereNull('id');
        }

        return $query->where(function ($q) {
            $q->whereNull('standar_tahun')
              ->orWhere('standar_tahun', '!=', '2027');
        });
    }

    /**
     * Scope untuk memastikan soal figural yang belum lengkap gambarnya tidak muncul di ujian,
     * serta mendukung opsi membatasi hanya soal berstandar 2027 atau seluruh soal siap uji.
     */
    public function scopeSiapUjian($query, bool $onlyStandar2027 = false)
    {
        if ($onlyStandar2027 && Schema::hasColumn('soals', 'standar_tahun')) {
            $query->where('standar_tahun', '2027');
        }

        if (Schema::hasColumn('soals', 'qc_status')) {
            $query->where(function ($qc) {
                $qc->whereNull('qc_status')
                    ->orWhereIn('qc_status', ['approved', 'revised', 'valid', 'reviewed']);
            });
        }

        return $query->where(function ($q) {
            // Non-figural questions: allow NULL sub_topik OR any sub_topik that doesn't mention figural keywords.
            $q->where(function ($subQ) {
                $subQ->whereNull('sub_topik')
                    ->orWhere(function ($s) {
                        $s->where('sub_topik', 'NOT LIKE', '%Figural%')
                          ->where('sub_topik', 'NOT LIKE', '%Spasial%')
                          ->where('sub_topik', 'NOT LIKE', '%Gambar%')
                          ->where('sub_topik', 'NOT LIKE', '%Visual%')
                          ->where('sub_topik', 'NOT LIKE', '%Pola%');
                    });
            })->orWhere(function ($subQ) {
                $subQ->whereNotNull('gambar_path')
                     ->whereNotNull('gambar_a')
                     ->whereNotNull('gambar_b')
                     ->whereNotNull('gambar_c')
                     ->whereNotNull('gambar_d')
                     ->whereNotNull('gambar_e');
            });
        });
    }
}
