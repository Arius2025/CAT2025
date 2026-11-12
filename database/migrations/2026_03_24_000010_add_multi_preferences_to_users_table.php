<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'preferred_exam_mode_ids')) {
                $table->json('preferred_exam_mode_ids')->nullable()->after('preferred_exam_mode_id');
            }
            if (!Schema::hasColumn('users', 'preferred_formasi_names')) {
                $table->json('preferred_formasi_names')->nullable()->after('preferred_sub_category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['preferred_formasi_names', 'preferred_exam_mode_ids'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
