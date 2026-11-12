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
        Schema::table('ujians', function (Blueprint $table) {
            $table->json('analitik_topik')->nullable()->after('is_selesai');
            $table->integer('waktu_pengerjaan')->nullable()->after('analitik_topik')->comment('Total durasi ujian dalam detik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn(['analitik_topik', 'waktu_pengerjaan']);
        });
    }
};
