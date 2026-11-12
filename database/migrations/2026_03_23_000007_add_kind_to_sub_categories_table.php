<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sub_categories')) {
            return;
        }

        Schema::table('sub_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_categories', 'kind')) {
                $table->string('kind', 30)->nullable()->after('name');
            }
        });

        $rows = DB::table('sub_categories')->select('id', 'name')->get();
        foreach ($rows as $row) {
            $name = strtolower(trim((string) $row->name));
            $kind = match (true) {
                in_array($name, ['twk', 'tiu', 'tkp']) => 'formasi',
                str_contains($name, 'd3 ') => 'formasi',
                str_contains($name, 's1 ') => 'formasi',
                default => 'topik',
            };

            DB::table('sub_categories')
                ->where('id', $row->id)
                ->update(['kind' => $kind]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sub_categories')) {
            return;
        }

        Schema::table('sub_categories', function (Blueprint $table) {
            if (Schema::hasColumn('sub_categories', 'kind')) {
                $table->dropColumn('kind');
            }
        });
    }
};
