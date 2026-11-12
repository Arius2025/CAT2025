<?php

namespace App\Livewire\Ujian;

use App\Models\Ujian;
use App\Support\ExamRankingSupport;
use App\Support\ExamStructureSupport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class LeaderboardPanel extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'paket', except: 'D')]
    public string $paket = 'D';

    #[Url(as: 'exam_mode_id', except: '')]
    public string $examModeId = '';

    #[Url(as: 'sub_category_id', except: '')]
    public string $subCategoryId = '';

    public function mount(): void
    {
        $this->paket = $this->normalizePaket($this->paket);

        if (!$this->examModes->contains(fn ($mode) => (string) $mode->id === $this->examModeId)) {
            $this->examModeId = '';
        }

        if (!$this->subCategories->contains(fn ($subCategory) => (string) $subCategory->id === $this->subCategoryId)) {
            $this->subCategoryId = '';
        }
    }

    public function updatedExamModeId(): void
    {
        if (!$this->subCategories->contains(fn ($subCategory) => (string) $subCategory->id === $this->subCategoryId)) {
            $this->subCategoryId = '';
        }
    }

    public function updatedPaket(string $value): void
    {
        $this->paket = $this->normalizePaket($value);
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'examModeId', 'subCategoryId');
        $this->paket = 'D';
    }

    #[Computed]
    public function examModes(): Collection
    {
        return ExamStructureSupport::modes()->values();
    }

    #[Computed]
    public function selectedMode(): ?object
    {
        if ($this->examModeId === '') {
            return null;
        }

        return $this->examModes->firstWhere('id', (int) $this->examModeId);
    }

    #[Computed]
    public function subCategories(): Collection
    {
        return ExamStructureSupport::subCategories(
            $this->examModeId !== '' ? (int) $this->examModeId : null,
            $this->selectedMode?->code ?? null,
            'formasi'
        )->values();
    }

    #[Computed]
    public function rankings(): Collection
    {
        return $this->baseQuery()
            ->where('paket', $this->paket)
            ->take(50)
            ->get();
    }

    #[Computed]
    public function modeStats(): Collection
    {
        $rows = $this->rankings;
        $defaultMode = ExamStructureSupport::defaultMode();

        return $this->examModes
            ->map(function ($mode) use ($rows, $defaultMode) {
                $count = $rows->filter(function ($row) use ($mode, $defaultMode) {
                    if (Schema::hasColumn('ujians', 'exam_mode_id') && !is_null($row->exam_mode_id)) {
                        return (int) $row->exam_mode_id === (int) $mode->id;
                    }

                    return (string) ($mode->code ?? '') === (string) ($defaultMode->code ?? 'skd');
                })->count();

                return (object) [
                    'id' => $mode->id,
                    'name' => $mode->name,
                    'code' => $mode->code,
                    'color' => $mode->color ?? null,
                    'count' => $count,
                ];
            })
            ->filter(fn ($item) => $item->count > 0)
            ->values();
    }

    public function scoreMeta(object $row): array
    {
        return ExamRankingSupport::buildScoreMeta($row);
    }

    public function displayMeta(object $row): array
    {
        $modeName = ExamRankingSupport::modeName($row);
        $subName = ExamRankingSupport::subCategoryName($row);

        return [
            'mode_name' => $modeName,
            'sub_name' => $subName,
            'is_farmasi' => ExamRankingSupport::isFarmasiContext($modeName, $subName),
        ];
    }

    public function render()
    {
        return view('livewire.ujian.leaderboard-panel');
    }

    private function baseQuery(): Builder
    {
        $search = trim($this->search);

        return Ujian::query()
            ->when(
                Schema::hasColumn('ujians', 'exam_mode_id') || Schema::hasColumn('ujians', 'sub_category_id'),
                fn (Builder $query) => $query->with(['examMode', 'subCategory'])
            )
            ->where('is_selesai', true)
            ->when(
                $this->examModeId !== '' && Schema::hasColumn('ujians', 'exam_mode_id'),
                fn (Builder $query) => $query->where('exam_mode_id', (int) $this->examModeId)
            )
            ->when(
                $this->subCategoryId !== '' && Schema::hasColumn('ujians', 'sub_category_id'),
                fn (Builder $query) => $query->where('sub_category_id', (int) $this->subCategoryId)
            )
            ->when($search !== '', fn (Builder $query) => $query->where('nama_peserta', 'like', '%' . $search . '%'))
            ->selectRaw('*, ' . ExamRankingSupport::totalScoreSelectExpression())
            ->orderByDesc('total_skor')
            ->orderByDesc('skor_tkp')
            ->orderByDesc('skor_tiu');
    }

    private function normalizePaket(string $value): string
    {
        $value = strtoupper(trim($value));

        return in_array($value, ['A', 'B', 'C', 'D'], true) ? $value : 'D';
    }
}

