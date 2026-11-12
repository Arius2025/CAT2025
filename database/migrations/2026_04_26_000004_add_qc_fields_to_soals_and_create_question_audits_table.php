<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            if (!Schema::hasColumn('soals', 'qc_status')) {
                $table->string('qc_status', 24)->nullable()->after('hash_soal');
                $table->timestamp('qc_checked_at')->nullable()->after('qc_status');
                $table->decimal('qc_confidence', 5, 2)->nullable()->after('qc_checked_at');
                $table->index('qc_status', 'soals_qc_status_index');
            }
        });

        if (!Schema::hasTable('question_audits')) {
            Schema::create('question_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->nullable()->constrained('soals')->nullOnDelete();
                $table->string('source', 32)->default('existing_tiu');
                $table->string('audit_type', 32)->default('qc');
                $table->string('status', 24)->default('suspicious');
                $table->decimal('confidence', 5, 2)->nullable();
                $table->string('issue_type', 64)->nullable();
                $table->char('old_answer', 1)->nullable();
                $table->char('suggested_answer', 1)->nullable();
                $table->longText('old_explanation')->nullable();
                $table->longText('suggested_explanation')->nullable();
                $table->text('notes')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();

                $table->index(['source', 'status'], 'question_audits_source_status_index');
                $table->index(['question_id', 'created_at'], 'question_audits_question_created_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('question_audits');

        Schema::table('soals', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('soals', 'qc_status')) {
                $table->dropIndex('soals_qc_status_index');
                $drops[] = 'qc_status';
            }
            if (Schema::hasColumn('soals', 'qc_checked_at')) {
                $drops[] = 'qc_checked_at';
            }
            if (Schema::hasColumn('soals', 'qc_confidence')) {
                $drops[] = 'qc_confidence';
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
