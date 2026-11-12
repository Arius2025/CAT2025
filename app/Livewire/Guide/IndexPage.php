<?php

namespace App\Livewire\Guide;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class IndexPage extends Component
{
    public function render()
    {
        $schemaReady = Schema::hasTable('exam_modes') && Schema::hasTable('sub_categories');
        $modeStats = [
            'total' => Soal::count(),
            'skd' => Soal::query()->when(Schema::hasColumn('soals', 'exam_mode_id'), fn ($q) => $q->whereNull('exam_mode_id'))->count(),
            'skb' => 0,
            'pppk' => 0,
            'farmasi' => 0,
            'figural' => Soal::whereNotNull('prompt_gambar_soal')->count(),
            'unlabeled' => Soal::whereNull('sub_topik')->orWhere('sub_topik', '')->count(),
        ];

        if (Schema::hasTable('exam_modes') && Schema::hasColumn('soals', 'exam_mode_id')) {
            $skbMode = ExamMode::query()->where('code', 'skb')->first();
            $pppkMode = ExamMode::query()->where('code', 'pppk')->first();
            $modeStats['skb'] = $skbMode ? Soal::query()->where('exam_mode_id', $skbMode->id)->count() : 0;
            $modeStats['pppk'] = $pppkMode ? Soal::query()->where('exam_mode_id', $pppkMode->id)->count() : 0;
        }

        if (Schema::hasColumn('soals', 'sub_category_id') && Schema::hasTable('sub_categories')) {
            $farmasiSubIds = SubCategory::query()
                ->where('name', 'like', '%farmasi%')
                ->orWhere('name', 'like', '%apoteker%')
                ->pluck('id')
                ->all();
            if (!empty($farmasiSubIds)) {
                $modeStats['farmasi'] = Soal::query()->whereIn('sub_category_id', $farmasiSubIds)->count();
            }
        } else {
            $modeStats['farmasi'] = Soal::query()
                ->where('sub_topik', 'like', '%farmasi%')
                ->orWhere('teks_soal', 'like', '%farmasi%')
                ->count();
        }

        return view('livewire.guide.index-page', [
            'schemaReady' => $schemaReady,
            'appUrl' => url('/'),
            'apiUrl' => url('/api'),
            'modeStats' => $modeStats,
        ]);
    }
}
