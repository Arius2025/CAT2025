<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_master_admin')) {
                $table->boolean('is_master_admin')->default(false)->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'preferred_exam_mode_id')) {
                $table->unsignedBigInteger('preferred_exam_mode_id')->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('users', 'preferred_sub_category_id')) {
                $table->unsignedBigInteger('preferred_sub_category_id')->nullable()->after('preferred_exam_mode_id');
            }
            if (!Schema::hasColumn('users', 'visible_menu_keys')) {
                $table->json('visible_menu_keys')->nullable()->after('preferred_sub_category_id');
            }
        });

        try {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', 1)
                ->update(['is_master_admin' => true]);
        } catch (\Throwable $e) {
            // ignore on first setup mismatch
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['visible_menu_keys', 'preferred_sub_category_id', 'preferred_exam_mode_id', 'is_master_admin'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
