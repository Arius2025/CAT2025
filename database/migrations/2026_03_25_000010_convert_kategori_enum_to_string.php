<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historically `kategori` was an ENUM(TWK, TIU, TKP).
        // The app now supports more categories (PPPK/SKB) so we must remove ENUM constraints.
        $driver = DB::getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            // Non-MySQL drivers are not expected in prod; skip safely.
            return;
        }

        if (Schema::hasTable('soals') && Schema::hasColumn('soals', 'kategori')) {
            // Keep NOT NULL to match old schema; existing rows already have values.
            DB::statement("ALTER TABLE `soals` MODIFY `kategori` VARCHAR(50) NOT NULL");
        }

        if (Schema::hasTable('user_soal_histories') && Schema::hasColumn('user_soal_histories', 'kategori')) {
            DB::statement("ALTER TABLE `user_soal_histories` MODIFY `kategori` VARCHAR(50) NOT NULL");
        }
    }

    public function down(): void
    {
        // Intentionally no-op. Reverting to ENUM would be destructive once new categories exist.
    }
};

