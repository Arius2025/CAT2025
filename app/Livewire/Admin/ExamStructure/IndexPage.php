<?php

namespace App\Livewire\Admin\ExamStructure;

use App\Models\ExamMode;
use App\Models\SubCategory;
use App\Support\ExamStructureSupport;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class IndexPage extends Component
{
    public function render()
    {
        if (ExamStructureSupport::schemaReady()) {
            $this->syncDefaultStructures();
        }

        $schemaReady = ExamStructureSupport::schemaReady();
        $migrationCommand = 'php artisan migrate';
        $selectedModeId = request()->filled('exam_mode_id') ? (int) request()->query('exam_mode_id') : 0;
        $selectedKind = trim((string) request()->query('kind', 'formasi'));
        $search = trim((string) request()->query('q', ''));
        $examModes = $schemaReady
            ? ExamMode::query()->withCount('subCategories')->orderBy('name')->get()
            : ExamStructureSupport::modes()->map(function ($mode) {
                $mode->sub_categories_count = ExamStructureSupport::subCategories(null, $mode->code, null)->count();
                return $mode;
            });
        $subCategories = $schemaReady
            ? SubCategory::query()
                ->with('examMode')
                ->when($selectedModeId > 0, fn ($q) => $q->where('exam_mode_id', $selectedModeId))
                ->when($selectedKind !== '' && Schema::hasColumn('sub_categories', 'kind'), fn ($q) => $q->where('kind', $selectedKind))
                ->when($search !== '', fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
                ->orderBy('name')
                ->get()
            : ExamStructureSupport::modes()->flatMap(function ($mode) {
                return ExamStructureSupport::subCategories(null, $mode->code, null)->map(function ($sub) use ($mode) {
                    $sub->examMode = $mode;
                    return $sub;
                });
            })->values();
        $packages = $schemaReady
            ? $examModes->mapWithKeys(function ($mode) {
                return [$mode->id => ExamStructureSupport::packages($mode->id, $mode->code)];
            })
            : ExamStructureSupport::modes()->mapWithKeys(function ($mode) {
                return [$mode->id => ExamStructureSupport::packages($mode->id, $mode->code)];
            });
        $subCategoryStats = $schemaReady
            ? [
                'all' => SubCategory::query()->count(),
                'formasi' => Schema::hasColumn('sub_categories', 'kind') ? SubCategory::query()->where('kind', 'formasi')->count() : 0,
                'topik' => Schema::hasColumn('sub_categories', 'kind') ? SubCategory::query()->where('kind', 'topik')->count() : 0,
            ]
            : ['all' => $subCategories->count(), 'formasi' => 0, 'topik' => 0];

        return view('livewire.admin.exam-structure.index-page', compact(
            'examModes',
            'subCategories',
            'packages',
            'schemaReady',
            'migrationCommand',
            'selectedModeId',
            'selectedKind',
            'search',
            'subCategoryStats'
        ));
    }

    private function syncDefaultStructures(): void
    {
        if (!Schema::hasTable('exam_modes') || !Schema::hasTable('sub_categories')) {
            return;
        }

        $modes = collect([
            ['name' => 'SKD', 'code' => 'skd', 'color' => '#0ea5e9', 'has_figural' => true],
            ['name' => 'SKB', 'code' => 'skb', 'color' => '#22c55e', 'has_figural' => false],
            ['name' => 'PPPK', 'code' => 'pppk', 'color' => '#f59e0b', 'has_figural' => false],
        ])->map(function ($row) {
            return ExamMode::query()->firstOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'color' => $row['color'],
                    'has_figural' => $row['has_figural'],
                    'is_active' => true,
                ]
            );
        })->keyBy('code');

        $defaults = [
            'skd' => ['TWK', 'TIU', 'TKP'],
            'skb' => ['D3 Farmasi', 'D3 Manajemen Informatika', 'S1 Teknik Informatika', 'S1 Keperawatan', 'D4 Anastesi'],
            'pppk' => ['D3 Farmasi', 'D3 Manajemen Informatika', 'S1 Teknik Informatika', 'S1 Keperawatan', 'D4 Anastesi'],
        ];

        foreach ($defaults as $code => $names) {
            $mode = $modes->get($code);
            if (!$mode) {
                continue;
            }

            foreach ($names as $name) {
                SubCategory::query()->firstOrCreate([
                    'exam_mode_id' => $mode->id,
                    'name' => $name,
                ], [
                    'kind' => 'formasi',
                ]);
            }
        }
    }
}