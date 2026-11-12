<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_soal_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('soal_id')->nullable();
            $table->enum('kategori', ['TWK', 'TIU', 'TKP']);
            // Stable signature to avoid repeats even when DB has duplicate IDs with same content.
            $table->char('signature', 40);
            $table->unsignedBigInteger('ujian_id')->nullable();
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'signature'], 'user_sig_unique');
            $table->index(['user_id', 'kategori', 'used_at'], 'user_kat_used_idx');
            $table->index(['user_id', 'soal_id'], 'user_soal_idx');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('soal_id')->references('id')->on('soals')->onDelete('set null');
            $table->foreign('ujian_id')->references('id')->on('ujians')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_soal_histories');
    }
};

