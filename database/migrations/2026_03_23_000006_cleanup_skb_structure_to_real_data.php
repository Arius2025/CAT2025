<?php

use App\Models\ExamMode;
use App\Models\SubCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_modes') || !Schema::hasTable('sub_categories')) {
            return;
        }

        $skbMode = ExamMode::query()->where('code', 'skb')->first();
        $pppkMode = ExamMode::query()->where('code', 'pppk')->first();

        if ($skbMode) {
            foreach (['D3 Farmasi', 'D3 Manajemen Informatika', 'S1 Teknik Informatika'] as $name) {
                SubCategory::query()->firstOrCreate([
                    'exam_mode_id' => $skbMode->id,
                    'name' => $name,
                ]);
            }
        }

        if ($pppkMode) {
            foreach (['D3 Farmasi', 'D3 Manajemen Informatika', 'S1 Teknik Informatika'] as $name) {
                SubCategory::query()->firstOrCreate([
                    'exam_mode_id' => $pppkMode->id,
                    'name' => $name,
                ]);
            }
        }
    }

    public function down(): void
    {
        // noop - additive only.
    }
};
