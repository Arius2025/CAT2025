<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progres_ujians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_peserta');
            $table->enum('paket_ujian', ['TWK', 'TIU', 'TKP', 'LENGKAP']);
            $table->json('jawaban_peserta_json')->nullable();
            $table->json('status_soal_json')->nullable();
            $table->integer('waktu_sisa'); 
            $table->enum('status_ujian', ['BERJALAN', 'SELESAI'])->default('BERJALAN');
            $table->json('skor_akhir_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_ujians');
    }
};