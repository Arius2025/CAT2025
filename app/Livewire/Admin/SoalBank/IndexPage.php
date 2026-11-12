<?php

namespace App\Livewire\Admin\SoalBank;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Support\ExamStructureSupport;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'kategori', except: '')]
    public string $kategori = '';

    #[Url(as: 'exam_mode_id', except: '')]
    public string $examModeId = '';

    #[Url(as: 'sub_category_id', except: '')]
    public string $subCategoryId = '';

    #[Url(as: 'sub_topik', except: '')]
    public string $subTopik = '';

    #[Url(as: 'difficulty', except: '')]
    public string $difficulty = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedKategori(): void { $this->resetPage(); }
    public function updatedSubTopik(): void { $this->resetPage(); }
    public function updatedDifficulty(): void { $this->resetPage(); }

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

    public function applyQuickFilter(string $key): void
    {
        if ($key === 'farmasi') {
            $this->search = 'farmasi';
            return;
        }

        $modeCode = $key === 'pppk_farmasi' ? 'pppk' : ($key === 'skb_farmasi' ? 'skb' : '');
        $mode = $this->examModes()->firstWhere('code', $modeCode);
        $this->examModeId = $mode ? (string) $mode->id : '';
        $this->subCategoryId = '';
        $this->search = 'farmasi';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->kategori = '';
        $this->examModeId = '';
        $this->subCategoryId = '';
        $this->subTopik = '';
        $this->difficulty = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Soal::query()->with(['examMode', 'subCategory'])->orderByDesc('id');

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                if (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('teks_soal', 'like', '%' . $term . '%')
                    ->orWhere('pembahasan', 'like', '%' . $term . '%')
                    ->orWhere('sub_topik', 'like', '%' . $term . '%');
            });
        }
        if (trim($this->kategori) !== '') {
            $kat = strtoupper(trim($this->kategori));
            if ($kat === 'FIGURAL') {
                $query->where('kategori', 'TIU')
                    ->where(function ($q) {
                        if (Schema::hasColumn('soals', 'prompt_gambar_soal')) {
                            $q->whereNotNull('prompt_gambar_soal');
                        }
                        $q->orWhere('sub_topik', 'LIKE', '%Figural%')
                            ->orWhere('sub_topik', 'LIKE', '%Spasial%')
                            ->orWhere('sub_topik', 'LIKE', '%Visual%')
                            ->orWhere('sub_topik', 'LIKE', '%Gambar%')
                            ->orWhere('sub_topik', 'LIKE', '%Pola%');
                    });
            } else {
                $query->where('kategori', $kat);
            }
        }
        if (trim($this->subTopik) !== '') {
            $query->where('sub_topik', 'like', '%' . trim($this->subTopik) . '%');
        }
        if ($this->examModeId !== '') {
            $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
            $query->forExamMode((int) $this->examModeId, $defaultSkdId);
        }
        if ($this->subCategoryId !== '') {
            $query->forSubCategory((int) $this->subCategoryId);
        }
        if (trim($this->difficulty) !== '' && Schema::hasColumn('soals', 'difficulty')) {
            $query->where('difficulty', trim($this->difficulty));
        }

        $soals = $query->paginate(20);
        $examModes = $this->examModes();
        $subCategories = $this->subCategories();
        $pppkMode = $examModes->firstWhere('code', 'pppk');
        $skbMode = $examModes->firstWhere('code', 'skb');
        $quickFilters = [
            'farmasi' => ['q' => 'farmasi'],
            'pppk_farmasi' => [
                'exam_mode_id' => $pppkMode->id ?? null,
                'q' => 'farmasi',
            ],
            'skb_farmasi' => [
                'exam_mode_id' => $skbMode->id ?? null,
                'q' => 'farmasi',
            ],
        ];

        $skdModeId = $examModes->firstWhere('code', 'skd')?->id ?? 1;
        $skbModeId = $examModes->firstWhere('code', 'skb')?->id ?? 2;
        $pppkModeId = $examModes->firstWhere('code', 'pppk')?->id ?? 3;

        $modeStats = [
            'total' => Soal::count(),
            'skd' => Soal::where('exam_mode_id', $skdModeId)->orWhereNull('exam_mode_id')->count(),
            'skb' => Soal::where('exam_mode_id', $skbModeId)->count(),
            'pppk' => Soal::where('exam_mode_id', $pppkModeId)->count(),
            'figural' => Schema::hasColumn('soals', 'prompt_gambar_soal')
                ? Soal::whereNotNull('prompt_gambar_soal')->count()
                : Soal::where('sub_topik', 'LIKE', '%Figural%')->count(),
            'unlabeled' => Soal::whereNull('sub_topik')->orWhere('sub_topik', '')->count(),
        ];

        $categoryOptions = collect();
        if ($this->examModeId !== '') {
            $modeId = (int) $this->examModeId;
            $modeCode = $examModes->firstWhere('id', $modeId)?->code;
            $categoryOptions = ExamStructureSupport::categoryOptions($modeId ?: null, $modeCode)->values();
        } else {
            $seen = [];
            foreach ($examModes as $m) {
                $rows = ExamStructureSupport::categoryOptions((int) $m->id, (string) $m->code);
                foreach ($rows as $row) {
                    $key = strtoupper(trim((string) ($row->value ?? $row->value ?? '')));
                    if ($key === '' || isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $categoryOptions->push($row);
                }
            }
            if (!$categoryOptions->firstWhere('value', 'FIGURAL')) {
                $categoryOptions->push((object) ['value' => 'FIGURAL', 'label' => 'Figural', 'score_type' => 'objective']);
            }
        }

        return view('livewire.admin.soal-bank.index-page', compact('soals', 'examModes', 'subCategories', 'quickFilters', 'modeStats', 'categoryOptions'));
    }

    private function examModes()
    {
        return Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();
    }

    private function subCategories()
    {
        return ($this->examModeId !== '' && Schema::hasTable('sub_categories'))
            ? SubCategory::query()->where('exam_mode_id', (int) $this->examModeId)->byKind('formasi')->orderBy('name')->get(['id', 'name', 'exam_mode_id', 'kind'])
            : collect();
    }
}
