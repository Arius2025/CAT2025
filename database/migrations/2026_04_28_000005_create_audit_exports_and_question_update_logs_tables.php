<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_exports')) {
            Schema::create('audit_exports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('total_questions')->default(0);
                $table->string('source_scope', 64)->default('manual');
                $table->string('format', 24)->default('json');
                $table->json('filter_used')->nullable();
                $table->json('question_ids')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at'], 'audit_exports_user_created_index');
            });
        }

        if (!Schema::hasTable('question_update_logs')) {
            Schema::create('question_update_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('soals')->cascadeOnDelete();
                $table->string('source', 32)->default('gemini_import');
                $table->char('old_answer', 1)->nullable();
                $table->char('new_answer', 1)->nullable();
                $table->longText('old_explanation')->nullable();
                $table->longText('new_explanation')->nullable();
                $table->longText('old_question')->nullable();
                $table->longText('new_question')->nullable();
                $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['question_id', 'created_at'], 'question_update_logs_question_created_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('question_update_logs');
        Schema::dropIfExists('audit_exports');
    }
};
