<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_mode_packages')) {
            Schema::create('exam_mode_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_mode_id')->constrained('exam_modes')->cascadeOnDelete();
                $table->string('code', 30);
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedInteger('duration_minutes')->nullable();
                $table->unsignedInteger('total_questions')->nullable();
                $table->json('sections')->nullable();
                $table->json('passing_rules')->nullable();
                $table->json('score_rules')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['exam_mode_id', 'code']);
            });
        }

        Schema::table('ujians', function (Blueprint $table) {
            if (!Schema::hasColumn('ujians', 'exam_mode_package_id')) {
                $table->unsignedBigInteger('exam_mode_package_id')->nullable()->after('sub_category_id');
            }
            if (!Schema::hasColumn('ujians', 'duration_minutes')) {
                $table->unsignedInteger('duration_minutes')->nullable()->after('sisa_waktu');
            }
            if (!Schema::hasColumn('ujians', 'total_score')) {
                $table->unsignedInteger('total_score')->nullable()->after('skor_tkp');
            }
            if (!Schema::hasColumn('ujians', 'score_breakdown')) {
                $table->json('score_breakdown')->nullable()->after('analitik_topik');
            }
            if (!Schema::hasColumn('ujians', 'result_summary')) {
                $table->json('result_summary')->nullable()->after('score_breakdown');
            }
            if (!Schema::hasColumn('ujians', 'package_snapshot')) {
                $table->json('package_snapshot')->nullable()->after('result_summary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            foreach (['exam_mode_package_id', 'duration_minutes', 'total_score', 'score_breakdown', 'result_summary', 'package_snapshot'] as $col) {
                if (Schema::hasColumn('ujians', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('exam_mode_packages');
    }
};
