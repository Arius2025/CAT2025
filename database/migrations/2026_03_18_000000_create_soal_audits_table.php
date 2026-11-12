<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')->constrained('soals')->cascadeOnDelete();

            $table->string('provider', 32)->default('ollama_qwen');
            $table->string('model', 64)->nullable();

            $table->string('status', 16)->default('unknown'); // match|mismatch|invalid|error|unknown
            $table->char('db_key', 1)->nullable();
            $table->char('ai_key', 1)->nullable();
            $table->boolean('pembahasan_ok')->default(false);
            $table->text('ai_pembahasan')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('audited_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->unique(['soal_id', 'provider', 'model'], 'soal_audits_unique_soal_provider_model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_audits');
    }
};

