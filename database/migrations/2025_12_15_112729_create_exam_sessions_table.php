<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('participant_name');
            $table->integer('score_twk')->default(0);
            $table->integer('score_tiu')->default(0);
            $table->integer('score_tkp')->default(0);
            $table->boolean('is_finished')->default(false);
            $table->text('answers_data')->nullable(); // Untuk menyimpan JSON jawaban
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};