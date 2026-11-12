<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'soal_id',
        'provider',
        'model',
        'status',
        'db_key',
        'ai_key',
        'pembahasan_ok',
        'needs_revision',
        'revision_reason',
        'ai_pembahasan',
        'notes',
        'audited_at',
        'proposed_teks_soal',
        'proposed_opsi_a',
        'proposed_opsi_b',
        'proposed_opsi_c',
        'proposed_opsi_d',
        'proposed_opsi_e',
        'proposed_jawaban_benar',
        'proposed_pembahasan',
    ];

    protected $casts = [
        'pembahasan_ok' => 'boolean',
        'needs_revision' => 'boolean',
        'audited_at' => 'datetime',
    ];

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }
}
