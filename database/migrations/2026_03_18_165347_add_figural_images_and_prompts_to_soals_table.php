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
            $table->text('prompt_gambar_soal')->nullable()->after('kategori');
            $table->text('prompt_gambar_a')->nullable()->after('prompt_gambar_soal');
            $table->text('prompt_gambar_b')->nullable()->after('prompt_gambar_a');
            $table->text('prompt_gambar_c')->nullable()->after('prompt_gambar_b');
            $table->text('prompt_gambar_d')->nullable()->after('prompt_gambar_c');
            $table->text('prompt_gambar_e')->nullable()->after('prompt_gambar_d');

            $table->string('gambar_a')->nullable()->after('opsi_a');
            $table->string('gambar_b')->nullable()->after('opsi_b');
            $table->string('gambar_c')->nullable()->after('opsi_c');
            $table->string('gambar_d')->nullable()->after('opsi_d');
            $table->string('gambar_e')->nullable()->after('opsi_e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->dropColumn([
                'prompt_gambar_soal', 'prompt_gambar_a', 'prompt_gambar_b', 'prompt_gambar_c', 'prompt_gambar_d', 'prompt_gambar_e',
                'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d', 'gambar_e'
            ]);
        });
    }
};
