<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('soals', 'standar_tahun')) {
            Schema::table('soals', function (Blueprint $table) {
                $table->string('standar_tahun', 20)->default('pra-2027')->after('kategori')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('soals', 'standar_tahun')) {
            Schema::table('soals', function (Blueprint $table) {
                $table->dropColumn('standar_tahun');
            });
        }
    }
};
