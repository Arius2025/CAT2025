<?php

namespace App\Livewire\Admin\AuditSoal;

use App\Models\ExamMode;
use App\Models\SoalAudit;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use WithPagination;

    #[Url(as: 'kategori', except: '')]
    public string $kategori = '';

    #[Url(as: 'exam_mode_id', except: '')]
    public string $examModeId = '';

    #[Url(as: 'sub_category_id', except: '')]
    public string $subCategoryId = '';

    #[Url(as: 'difficulty', except: '')]
    public string $difficulty = '';

    public function updatedKategori(): void
    {
        $this->resetPage();
    }

    public function updatedExamModeId(): void
    {
        if (!$this->subCategories()->contains(fn ($sub) => (string) $sub->id === $this->subCategoryId)) {
            $this->subCategoryId = '';
        }

        $this->resetPage();
    }

    public function updatedSubCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedDifficulty(): void
    {
        $this->resetPage();
    }

    public function selectKategori(string $kategori = ''): void
    {
        $this->kategori = strtoupper(trim($kategori));
    }

    public function applyQuickMode(string $modeCode): void
    {
        $mode = $this->examModes()->firstWhere('code', $modeCode);
        $this->examModeId = $mode ? (string) $mode->id : '';
        $this->subCategoryId = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->kategori = '';
        $this->examModeId = '';
        $this->subCategoryId = '';
        $this->difficulty = '';
        $this->resetPage();
    }

    public function render()
    {
        $kategori = strtoupper(trim($this->kategori));
        $examModeId = (int) $this->examModeId;
        $subCategoryId = (int) $this->subCategoryId;
        $difficulty = trim($this->difficulty);

        $query = SoalAudit::with('soal')
            ->where(function ($q) {
                $q->where('status', 'mismatch')
                    ->orWhere('pembahasan_ok', false);
            });

        if ($kategori !== '') {
            $query->whereHas('soal', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        if ($examModeId > 0 && Schema::hasTable('exam_modes')) {
            $defaultSkdId = ExamMode::defaultSkd()?->id;
            $query->whereHas('soal', function ($q) use ($examModeId, $defaultSkdId) {
                $q->forExamMode($examModeId, $defaultSkdId);
            });
        }

        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $query->whereHas('soal', function ($q) use ($subCategoryId) {
                $q->forSubCategory($subCategoryId);
            });
        }

        if ($difficulty !== '' && Schema::hasColumn('soals', 'difficulty')) {
            $query->whereHas('soal', function ($q) use ($difficulty) {
                $q->where('difficulty', $difficulty);
            });
        }

        $audits = $query->orderBy('created_at', 'desc')->paginate(20);
        $examModes = $this->examModes();
        $subCategories = $this->subCategories();
        $pppkMode = $examModes->firstWhere('code', 'pppk');
        $skbMode = $examModes->firstWhere('code', 'skb');
        $quickFilters = [
            'pppk_farmasi' => ['exam_mode_id' => $pppkMode->id ?? null],
            'skb_farmasi' => ['exam_mode_id' => $skbMode->id ?? null],
        ];

        return view('livewire.admin.audit-soal.index-page', compact('audits', 'kategori', 'examModeId', 'subCategoryId', 'difficulty', 'examModes', 'subCategories', 'quickFilters'));
    }

    private function examModes()
    {
        return Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();
    }

    private function subCategories()
    {
        $examModeId = (int) $this->examModeId;

        return ($examModeId > 0 && Schema::hasTable('sub_categories'))
            ? SubCategory::query()->where('exam_mode_id', $examModeId)->byKind('formasi')->orderBy('name')->get(['id', 'name', 'exam_mode_id', 'kind'])
            : collect();
    }
}
