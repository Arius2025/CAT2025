<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ujians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_peserta');
            $table->string('paket');
            $table->json('jawaban_user')->nullable(); // Simpan ID soal dan pilihan
            $table->integer('skor_twk')->default(0);
            $table->integer('skor_tiu')->default(0);
            $table->integer('skor_tkp')->default(0);
            $table->boolean('is_selesai')->default(false);
            $table->integer('sisa_waktu')->default(6000); // dalam detik
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujians');
    }
};
