<?php

namespace App\Livewire\Ujian;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Support\ExamStructureSupport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StartExamModal extends Component
{
    public string $examModeId = '';
    public string $subCategoryId = '';
    public string $paket = '';
    public string $subTopik = '';
    public string $nama = '';
    public bool $forceContinueRecommended = false;
    public bool $forceContinueBlockedByTotal = false;

    public function mount(bool $forceContinueRecommended = false, bool $forceContinueBlockedByTotal = false): void
    {
        $this->forceContinueRecommended = $forceContinueRecommended;
        $this->forceContinueBlockedByTotal = $forceContinueBlockedByTotal;
        $this->examModeId = (string) old('exam_mode_id', '');
        $this->subCategoryId = (string) old('sub_category_id', '');
        $this->paket = strtoupper((string) old('paket', ''));
        $this->subTopik = (string) old('sub_topik', '');
        $this->nama = (string) old('nama', Auth::user()->name ?? '');
    }

    public function updatedExamModeId(): void
    {
        if (!$this->subCategories->contains(fn ($row) => (string) $row->id === $this->subCategoryId)) {
            $this->subCategoryId = '';
        }

        if (!$this->packages->contains(fn ($row) => strtoupper((string) ($row->code ?? '')) === $this->paket)) {
            $this->paket = '';
        }

        $this->subTopik = '';
        $this->dispatch('ujian-modal-step-focus', step: $this->requiresFormasi() ? 'sub-category' : 'paket');
    }

    public function updatedSubCategoryId(): void
    {
        if (!$this->packages->contains(fn ($row) => strtoupper((string) ($row->code ?? '')) === $this->paket)) {
            $this->paket = '';
        }

        $this->subTopik = '';
        $this->dispatch('ujian-modal-step-focus', step: 'paket');
    }

    public function updatedPaket(): void
    {
        if (!$this->focusTopicVisible()) {
            $this->subTopik = '';
        }

        $this->dispatch('ujian-modal-step-focus', step: $this->focusTopicVisible() ? 'focus-topic' : 'submit');
    }

    public function selectMode(int|string $modeId): void
    {
        $this->examModeId = (string) $modeId;
    }

    public function selectSubCategory(int|string $subCategoryId): void
    {
        $this->subCategoryId = (string) $subCategoryId;
    }

    public function selectPaket(string $paket): void
    {
        $this->paket = strtoupper(trim($paket));
    }

    #[Computed]
    public function examModes(): Collection
    {
        return ExamStructureSupport::modes()->values();
    }

    #[Computed]
    public function selectedMode(): ?object
    {
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
    public function packages(): Collection
    {
        $modeCode = strtolower((string) ($this->selectedMode?->code ?? ''));

        return ExamStructureSupport::packages(
            $this->examModeId !== '' ? (int) $this->examModeId : null,
            $modeCode !== '' ? $modeCode : null
        )->map(fn ($row) => $this->decoratePackageForUi($modeCode, $row))->values();
    }

    #[Computed]
    public function selectedPackage(): ?object
    {
        return $this->packages->first(fn ($row) => strtoupper((string) ($row->code ?? '')) === $this->paket);
    }

    #[Computed]
    public function sessionStockData(): Collection
    {
        return collect($this->buildSessionStockData(
            $this->examModeId !== '' ? (int) $this->examModeId : null,
            strtolower((string) ($this->selectedMode?->code ?? 'skd')),
            $this->subCategoryId !== '' ? (int) $this->subCategoryId : null
        ));
    }

    #[Computed]
    public function selectedStockRow(): ?array
    {
        return $this->sessionStockData->first(fn ($row) => strtoupper((string) ($row['code'] ?? '')) === $this->paket);
    }

    #[Computed]
    public function availableSubTopik(): array
    {
        $modeId = $this->examModeId !== '' ? (int) $this->examModeId : null;
        $subCategoryId = $this->subCategoryId !== '' ? (int) $this->subCategoryId : null;
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $rows = [];

        foreach (ExamStructureSupport::categoryOptions($modeId, $this->selectedMode?->code) as $category) {
            $kategori = strtoupper(trim((string) ($category->value ?? '')));
            if ($kategori === '' || $kategori === 'FIGURAL') {
                continue;
            }

            $raw = Soal::query()
                ->where('kategori', $kategori)
                ->forExamMode($modeId, $defaultSkdId)
                ->forSubCategory($subCategoryId)
                ->whereNotNull('sub_topik')
                ->distinct()
                ->orderBy('sub_topik')
                ->pluck('sub_topik')
                ->all();

            $clean = [];
            foreach ($raw as $topic) {
                $topic = trim((string) $topic);
                if ($topic === '') {
                    continue;
                }
                $clean[$topic] = true;
            }

            $rows[$kategori] = array_values(array_keys($clean));
        }

        return $rows;
    }

    public function requiresFormasi(): bool
    {
        return in_array(strtolower((string) ($this->selectedMode?->code ?? '')), ['pppk', 'skb'], true);
    }

    public function focusTopicVisible(): bool
    {
        return count((array) ($this->selectedPackage?->sections ?? [])) === 1;
    }

    public function currentKategori(): string
    {
        $sections = (array) ($this->selectedPackage?->sections ?? []);
        if (count($sections) !== 1) {
            return '';
        }

        return strtoupper(trim((string) ($sections[0]['kategori'] ?? '')));
    }

    public function focusTopicLabel(): string
    {
        $kategori = $this->currentKategori();
        $map = [
            'TWK' => 'Fokus Topik TWK',
            'TIU' => 'Fokus Topik TIU',
            'TKP' => 'Fokus Topik TKP',
            'TEKNIS' => 'Fokus Topik Teknis',
            'MANAJERIAL' => 'Fokus Topik Manajerial',
            'SOSIAL_KULTURAL' => 'Fokus Topik Sosio-Kultural',
            'WAWANCARA' => 'Fokus Topik Wawancara',
            'SKB_TEKNIS' => 'Fokus Topik SKB Teknis',
            'SKB_REGULASI' => 'Fokus Topik SKB Regulasi',
            'SKB_STUDI_KASUS' => 'Fokus Topik SKB Studi Kasus',
        ];

        return $map[$kategori] ?? 'Fokus Topik';
    }

    public function focusTopicOptions(): array
    {
        $kategori = $this->currentKategori();
        if ($kategori === '') {
            return [];
        }

        return [
            'recommended' => array_values(array_unique(array_filter($this->recommendedTopicsForCurrentModal($kategori)))),
            'existing' => array_values(array_unique(array_filter($this->availableSubTopik[$kategori] ?? []))),
        ];
    }

    public function startButtonDisabled(): bool
    {
        return $this->selectedStockBlocked() || $this->paket === '' || ($this->requiresFormasi() && $this->subCategoryId === '');
    }

    public function selectedStockBlocked(): bool
    {
        return (bool) ($this->selectedStockRow['ready'] ?? true) === false;
    }

    public function showForceButton(): bool
    {
        return !$this->selectedStockBlocked() && !$this->forceContinueBlockedByTotal && $this->forceContinueRecommended;
    }

    public function submitLabel(): string
    {
        if ($this->selectedStockBlocked()) return 'STOK BELUM CUKUP';
        if ($this->requiresFormasi() && $this->subCategoryId === '') return 'PILIH FORMASI DULU';
        if ($this->paket === '') return 'PILIH SESI DULU';
        return 'MULAI SEKARANG';
    }

    public function render()
    {
        return view('livewire.ujian.start-exam-modal');
    }

    private function decoratePackageForUi(string $modeCode, object $row): object
    {
        $profiles = [
            'pppk' => [
                'A' => ['name' => 'Teknis Formasi', 'description' => 'Sesi inti yang mengikuti kompetensi teknis sesuai formasi aktif.'],
                'B' => ['name' => 'Manajerial ASN', 'description' => 'Sesi manajerial untuk ritme kerja, komunikasi, kolaborasi, dan keputusan layanan.'],
                'C' => ['name' => 'Sosio-Kultural ASN', 'description' => 'Sesi sosial kultural untuk layanan publik, keberagaman, dan etika sosial.'],
                'D' => ['name' => 'Simulasi Lengkap PPPK', 'description' => 'Paket lengkap PPPK: teknis formasi, manajerial, sosio-kultural, dan wawancara.'],
            ],
            'skb' => [
                'A' => ['name' => 'Kompetensi Bidang Inti', 'description' => 'Sesi inti materi keahlian yang mengikuti formasi aktif.'],
                'B' => ['name' => 'Regulasi & Standar Profesi', 'description' => 'Sesi regulasi, SOP, standar profesi, dan kepatuhan bidang.'],
                'C' => ['name' => 'Studi Kasus Praktik', 'description' => 'Sesi analisis kasus lapangan dan penerapan keputusan teknis.'],
                'D' => ['name' => 'Simulasi Lengkap SKB', 'description' => 'Paket lengkap SKB yang menggabungkan bidang inti, regulasi, dan studi kasus.'],
            ],
        ];
        $legacyNames = [
            'pppk' => ['A' => ['Kompetensi Teknis'], 'B' => ['Manajerial'], 'C' => ['Sosio-Kultural'], 'D' => ['Full PPPK']],
            'skb' => ['A' => ['Materi Inti Bidang'], 'B' => ['Regulasi dan Analisis'], 'C' => ['Studi Kasus Bidang'], 'D' => ['Full SKB']],
        ];
        $code = strtoupper((string) ($row->code ?? ''));
        $profile = $profiles[$modeCode][$code] ?? null;
        if (!$profile) {
            return $row;
        }

        $currentName = trim((string) ($row->name ?? ''));
        $currentDescription = trim((string) ($row->description ?? ''));
        $allowedLegacy = $legacyNames[$modeCode][$code] ?? [];

        if ($currentName === '' || in_array($currentName, $allowedLegacy, true)) {
            $row->name = $profile['name'];
        }
        if ($currentDescription === '' || in_array($currentName, $allowedLegacy, true)) {
            $row->description = $profile['description'];
        }

        return $row;
    }

    private function resolveFocusPresetKey(?string $modeCode, ?string $subCategoryName): string
    {
        $mode = strtolower(trim((string) $modeCode));
        $sub = strtolower(trim((string) $subCategoryName));
        $detailedMap = [
            'pppk' => [
                '/farmakologi/i' => 'pppk_farmakologi',
                '/farmakoterapi/i' => 'pppk_farmakoterapi',
                '/farmasi klinis/i' => 'pppk_farmasi_klinis',
                '/pelayanan kefarmasian/i' => 'pppk_pelayanan_kefarmasian',
                '/resep|dosis/i' => 'pppk_resep_dosis',
                '/regulasi obat/i' => 'pppk_regulasi_obat',
            ],
            'skb' => [
                '/farmakologi klinik/i' => 'skb_farmakologi_klinik',
                '/farmasi klinik/i' => 'skb_farmasi_klinik',
                '/pelayanan kefarmasian/i' => 'skb_pelayanan_kefarmasian',
                '/analisis resep/i' => 'skb_analisis_resep',
                '/regulasi kefarmasian/i' => 'skb_regulasi_kefarmasian',
                '/studi kasus pasien/i' => 'skb_studi_kasus_pasien',
            ],
        ];

        foreach ($detailedMap[$mode] ?? [] as $pattern => $key) {
            if (@preg_match($pattern, $sub) === 1) {
                return $key;
            }
        }

        if ($mode === 'pppk' && str_contains($sub, 'd3 farmasi')) return 'pppk_asisten_apoteker';
        if ($mode === 'pppk' && str_contains($sub, 'd3 manajemen informatika')) return 'pppk_it_terampil';
        if ($mode === 'pppk' && str_contains($sub, 's1 teknik informatika')) return 'pppk_it_ahli';
        if ($mode === 'pppk' && str_contains($sub, 's1 keperawatan')) return 'pppk_keperawatan_s1';
        if ($mode === 'pppk' && str_contains($sub, 'd4 anastesi')) return 'pppk_anastesi_d4';
        if ($mode === 'skb' && str_contains($sub, 'd3 farmasi')) return 'skb_asisten_apoteker';
        if ($mode === 'skb' && str_contains($sub, 'd3 manajemen informatika')) return 'skb_it_terampil';
        if ($mode === 'skb' && str_contains($sub, 's1 teknik informatika')) return 'skb_it_ahli';
        if ($mode === 'skb' && str_contains($sub, 's1 keperawatan')) return 'skb_keperawatan_s1';
        if ($mode === 'skb' && str_contains($sub, 'd4 anastesi')) return 'skb_anastesi_d4';

        return $mode !== '' ? $mode : 'skd';
    }

    private function recommendedTopicsForCurrentModal(string $kategori): array
    {
        $subCategoryName = $this->subCategories->firstWhere('id', (int) $this->subCategoryId)?->name;
        $presetKey = $this->resolveFocusPresetKey($this->selectedMode?->code, $subCategoryName);
        $presets = ExamStructureSupport::focusTopicPresets();
        $rows = $presets[$presetKey] ?? $presets['skd'] ?? [];

        return is_array($rows[$kategori] ?? null) ? $rows[$kategori] : [];
    }

    private function buildSessionStockData(?int $examModeId = null, ?string $examModeCode = null, ?int $subCategoryId = null): array
    {
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $packages = ExamStructureSupport::packages($examModeId, $examModeCode)->values();

        return $packages->map(function ($pkg) use ($examModeId, $subCategoryId, $defaultSkdId) {
            $sections = collect((array) ($pkg->sections ?? []))
                ->map(function ($section) use ($examModeId, $subCategoryId, $defaultSkdId) {
                    $requestedKategori = strtoupper(trim((string) ($section['kategori'] ?? '')));
                    $need = (int) ($section['count'] ?? 0);
                    $available = $requestedKategori !== ''
                        ? $this->availableCountForRequestedKategori($requestedKategori, $examModeId, $subCategoryId, $defaultSkdId)
                        : 0;

                    return [
                        'kategori' => $requestedKategori,
                        'label' => (string) ($section['label'] ?? $requestedKategori),
                        'need' => $need,
                        'available' => $available,
                        'ready' => $need > 0 ? $available >= $need : true,
                    ];
                })
                ->values();

            $ready = $sections->every(fn ($row) => (bool) ($row['ready'] ?? false));
            $missing = $sections
                ->filter(fn ($row) => !($row['ready'] ?? false))
                ->map(fn ($row) => ($row['label'] ?? $row['kategori']) . ' ' . ($row['available'] ?? 0) . '/' . ($row['need'] ?? 0))
                ->values()
                ->all();

            return [
                'code' => (string) ($pkg->code ?? ''),
                'name' => (string) ($pkg->name ?? ($pkg->code ?? 'Sesi')),
                'ready' => $ready,
                'hard_blocked' => !$ready,
                'sections' => $sections->all(),
                'missing' => $missing,
                'missing_count' => count($missing),
                'repeat_estimate' => (int) $sections
                    ->filter(fn ($row) => !($row['ready'] ?? false))
                    ->sum(fn ($row) => max(0, ((int) ($row['need'] ?? 0)) - ((int) ($row['available'] ?? 0)))),
            ];
        })->all();
    }

    private function availableCountForRequestedKategori(string $requestedKategori, ?int $examModeId = null, ?int $subCategoryId = null, ?int $defaultSkdId = null): int
    {
        return (int) Soal::query()
            ->siapUjian()
            ->where('kategori', strtoupper(trim($requestedKategori)))
            ->forExamMode($examModeId, $defaultSkdId)
            ->forSubCategory($subCategoryId)
            ->count();
    }
}

