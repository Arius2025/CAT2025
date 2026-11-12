<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_master_admin')) {
                $table->boolean('is_master_admin')->default(false)->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'preferred_exam_mode_id')) {
                $table->unsignedBigInteger('preferred_exam_mode_id')->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('users', 'preferred_exam_mode_ids')) {
                $table->json('preferred_exam_mode_ids')->nullable()->after('preferred_exam_mode_id');
            }
            if (!Schema::hasColumn('users', 'preferred_sub_category_id')) {
                $table->unsignedBigInteger('preferred_sub_category_id')->nullable()->after('preferred_exam_mode_ids');
            }
            if (!Schema::hasColumn('users', 'preferred_formasi_names')) {
                $table->json('preferred_formasi_names')->nullable()->after('preferred_sub_category_id');
            }
            if (!Schema::hasColumn('users', 'visible_menu_keys')) {
                $table->json('visible_menu_keys')->nullable()->after('preferred_formasi_names');
            }
        });
    }

    public function down(): void
    {
        // additive safety migration; no destructive rollback
    }
};
