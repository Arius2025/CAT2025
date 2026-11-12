<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\ExamMode;
use App\Models\SubCategory;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_modes') || !Schema::hasTable('sub_categories')) {
            return;
        }

        foreach (['skb', 'pppk'] as $modeCode) {
            $mode = ExamMode::query()->where('code', $modeCode)->first();
            if (!$mode) {
                continue;
            }

            foreach (['S1 Keperawatan', 'D4 Anastesi'] as $name) {
                SubCategory::query()->firstOrCreate(
                    ['exam_mode_id' => $mode->id, 'name' => $name],
                    ['kind' => 'formasi']
                );
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sub_categories') || !Schema::hasTable('exam_modes')) {
            return;
        }

        foreach (['skb', 'pppk'] as $modeCode) {
            $mode = ExamMode::query()->where('code', $modeCode)->first();
            if (!$mode) {
                continue;
            }

            SubCategory::query()
                ->where('exam_mode_id', $mode->id)
                ->whereIn('name', ['S1 Keperawatan', 'D4 Anastesi'])
                ->delete();
        }
    }
};
