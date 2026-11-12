<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_modes') || !Schema::hasTable('sub_categories')) {
            return;
        }

        $now = now();
        $modes = [
            ['name' => 'SKD', 'code' => 'skd', 'color' => '#0ea5e9', 'has_figural' => true],
            ['name' => 'SKB', 'code' => 'skb', 'color' => '#22c55e', 'has_figural' => false],
            ['name' => 'PPPK', 'code' => 'pppk', 'color' => '#f59e0b', 'has_figural' => false],
        ];

        foreach ($modes as $mode) {
            $exists = DB::table('exam_modes')->where('code', $mode['code'])->first();
            if ($exists) {
                DB::table('exam_modes')
                    ->where('id', $exists->id)
                    ->update([
                        'name' => $mode['name'],
                        'color' => $mode['color'],
                        'has_figural' => $mode['has_figural'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('exam_modes')->insert([
                    'name' => $mode['name'],
                    'code' => $mode['code'],
                    'color' => $mode['color'],
                    'has_figural' => $mode['has_figural'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $modeIds = DB::table('exam_modes')->pluck('id', 'code');
        $subCategories = [
            'skd' => ['TWK', 'TIU', 'TKP'],
            'skb' => ['D3 Farmasi', 'SKB Farmasi', 'Profesi Apoteker', 'D3 Manajemen Informatika', 'S1 Teknik Informatika'],
            'pppk' => ['PPPK Apoteker/Farmasi', 'Farmakologi', 'Farmakoterapi', 'Farmasi Klinis', 'Pelayanan Kefarmasian', 'Resep dan Dosis', 'Regulasi Obat', 'Monitoring Efek Samping Obat', 'PPPK Tenaga Kesehatan', 'PPPK Guru', 'PPPK Teknis', 'PPPK Umum'],
        ];

        foreach ($subCategories as $code => $rows) {
            $examModeId = $modeIds[$code] ?? null;
            if (!$examModeId) {
                continue;
            }

            foreach ($rows as $name) {
                $exists = DB::table('sub_categories')
                    ->where('exam_mode_id', $examModeId)
                    ->where('name', $name)
                    ->exists();

                if (!$exists) {
                    DB::table('sub_categories')->insert([
                        'name' => $name,
                        'exam_mode_id' => $examModeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Keep reference data intact on rollback for safety.
    }
};
