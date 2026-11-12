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
        Schema::table('soals', function (Blueprint $table) {
            // Mengubah kolom yang sudah ada menjadi tipe JSON
            // Perlu install: composer require doctrine/dbal
            $table->json('bobot_tkp')->nullable()->change();
            
            // Menambahkan kolom jika belum ada
            if (!Schema::hasColumn('soals', 'pembahasan')) {
                $table->text('pembahasan')->nullable();
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
