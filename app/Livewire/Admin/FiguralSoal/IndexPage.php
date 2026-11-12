<?php

namespace App\Livewire\Admin\FiguralSoal;

use App\Models\ExamMode;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class IndexPage extends Component
{
    public function render()
    {
        $request = request();
        $baseQuery = Soal::query()->where('kategori', 'TIU');

        if (Schema::hasColumn('soals', 'exam_mode_id') && Schema::hasTable('exam_modes')) {
            $baseQuery->where(function ($q) {
                $q->whereNull('exam_mode_id')
                    ->orWhereHas('examMode', function ($sub) {
                        $sub->where('has_figural', true);
                    });
            });
        }

        $baseQuery->where(function ($q) {
            $q->where('sub_topik', 'LIKE', '%Figural%')
                ->orWhere('sub_topik', 'LIKE', '%Spasial%')
                ->orWhere('sub_topik', 'LIKE', '%Gambar%')
                ->orWhere('sub_topik', 'LIKE', '%Visual%')
                ->orWhere('sub_topik', 'LIKE', '%Pola%')
                ->orWhereNotNull('gambar_path');
        });

        $totalTiu = (clone $baseQuery)->count();
        $totalGambar = (clone $baseQuery)->whereNotNull('gambar_path')->count();
        $totalTanpaGambar = max(0, $totalTiu - $totalGambar);

        $query = (clone $baseQuery)->orderBy('created_at', 'desc');

        if ($request->has('filter')) {
            if ($request->filter === 'ada_gambar') {
                $query->whereNotNull('gambar_path');
            } elseif ($request->filter === 'tanpa_gambar') {
                $query->whereNull('gambar_path');
            }
        }

        $soals = $query->paginate(20);
        $figuralModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->where('has_figural', true)->orderBy('name')->get(['id', 'name', 'code'])
            : collect();

        return view('livewire.admin.figural-soal.index-page', [
            'soals' => $soals,
            'totalTiu' => $totalTiu,
            'totalGambar' => $totalGambar,
            'totalTanpaGambar' => $totalTanpaGambar,
            'figuralModes' => $figuralModes,
        ]);
    }
}