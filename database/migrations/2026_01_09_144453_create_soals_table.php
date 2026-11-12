<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soals', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['TWK', 'TIU', 'TKP']);
            $table->text('teks_soal');
            $table->text('opsi_a');
            $table->text('opsi_b');
            $table->text('opsi_c');
            $table->text('opsi_d');
            $table->text('opsi_e');
            $table->char('jawaban_benar', 1)->nullable(); // A, B, C, D, atau E
            
            // Kolom ini akan menyimpan array skor untuk TKP (misal: {"A":5, "B":4, ...})
            $table->json('bobot_tkp')->nullable(); 
            
            $table->text('pembahasan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soals');
    }
};