<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            if (!Schema::hasColumn('ujians', 'exam_mode_id')) {
                $table->foreignId('exam_mode_id')->nullable()->after('paket')->constrained('exam_modes')->nullOnDelete();
            }
            if (!Schema::hasColumn('ujians', 'sub_category_id')) {
                $table->foreignId('sub_category_id')->nullable()->after('exam_mode_id')->constrained('sub_categories')->nullOnDelete();
            }
        });

        Schema::table('soals', function (Blueprint $table) {
            if (!Schema::hasColumn('soals', 'difficulty')) {
                $table->string('difficulty', 20)->nullable()->after('sub_category_id');
            }
            if (!Schema::hasColumn('soals', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('difficulty')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            if (Schema::hasColumn('ujians', 'sub_category_id')) {
                $table->dropConstrainedForeignId('sub_category_id');
            }
            if (Schema::hasColumn('ujians', 'exam_mode_id')) {
                $table->dropConstrainedForeignId('exam_mode_id');
            }
        });

        Schema::table('soals', function (Blueprint $table) {
            if (Schema::hasColumn('soals', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('soals', 'difficulty')) {
                $table->dropColumn('difficulty');
            }
        });
    }
};
