<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('soals', function (Blueprint $table) {
            // Menyimpan bobot nilai TKP dalam format JSON: {"A":5,"B":4,"C":3,"D":2,"E":1}
            $table->json('bobot_tkp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            //
        });
    }
};
