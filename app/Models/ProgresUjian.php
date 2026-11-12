<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgresUjian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_peserta',
        'paket_ujian',
        'jawaban_peserta_json',
        'status_soal_json',
        'waktu_sisa',
        'status_ujian',
        'skor_akhir_json',
    ];

    protected $casts = [
        'jawaban_peserta_json' => 'array',
        'status_soal_json' => 'array',
        'skor_akhir_json' => 'array',
    ];
}