<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamMode;
use App\Models\ExamModePackage;
use App\Models\SubCategory;
use App\Support\ExamStructureSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ExamStructureController extends Controller
{
    public function index()
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
                'formasi' => Schema::hasColumn('sub_categories', 'kind')
                    ? SubCategory::query()->where('kind', 'formasi')->count()
                    : 0,
                'topik' => Schema::hasColumn('sub_categories', 'kind')
                    ? SubCategory::query()->where('kind', 'topik')->count()
                    : 0,
            ]
            : ['all' => $subCategories->count(), 'formasi' => 0, 'topik' => 0];

        return view('admin.exam_structure.index', compact(
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

            if (Schema::hasTable('exam_mode_packages')) {
                foreach (ExamStructureSupport::defaultPackages($code) as $pkg) {
                    ExamModePackage::query()->firstOrCreate(
                        ['exam_mode_id' => $mode->id, 'code' => $pkg['code']],
                        [
                            'name' => $pkg['name'],
                            'description' => $pkg['description'] ?? null,
                            'duration_minutes' => $pkg['duration_minutes'] ?? null,
                            'total_questions' => $pkg['total_questions'] ?? null,
                            'sections' => $pkg['sections'] ?? [],
                            'passing_rules' => $pkg['passing_rules'] ?? [],
                            'score_rules' => $pkg['score_rules'] ?? [],
                            'is_active' => $pkg['is_active'] ?? true,
                            'sort_order' => $pkg['sort_order'] ?? 0,
                        ]
                    );
                }
            }
        }
    }

    public function storeMode(Request $request)
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel exam_modes belum tersedia. Jalankan migrate dulu agar data bisa disimpan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:exam_modes,code',
            'color' => 'nullable|string|max:32',
            'has_figural' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        ExamMode::create([
            'name' => $validated['name'],
            'code' => strtolower(trim($validated['code'])),
            'color' => $validated['color'] ?? null,
            'has_figural' => (bool) ($validated['has_figural'] ?? false),
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
        ]);

        return back()->with('success', 'Mode ujian berhasil ditambahkan.');
    }

    public function updateMode(Request $request, int $id)
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel exam_modes belum tersedia. Jalankan migrate dulu agar data bisa disimpan.');
        }

        $mode = ExamMode::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:exam_modes,code,' . $mode->id,
            'color' => 'nullable|string|max:32',
            'has_figural' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $mode->update([
            'name' => $validated['name'],
            'code' => strtolower(trim($validated['code'])),
            'color' => $validated['color'] ?? null,
            'has_figural' => (bool) ($validated['has_figural'] ?? false),
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : $mode->is_active,
        ]);

        return back()->with('success', 'Mode ujian berhasil diperbarui.');
    }

    public function destroyMode(int $id)
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel exam_modes belum tersedia. Jalankan migrate dulu agar data bisa disimpan.');
        }

        ExamMode::findOrFail($id)->delete();
        return back()->with('success', 'Mode ujian berhasil dihapus.');
    }

    public function storeSubCategory(Request $request)
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel sub_categories belum tersedia. Jalankan migrate dulu agar data bisa disimpan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exam_mode_id' => 'required|integer|exists:exam_modes,id',
            'kind' => 'nullable|string|in:formasi,topik',
        ]);

        SubCategory::create($validated);
        return back()->with('success', 'Sub kategori berhasil ditambahkan.');
    }

    public function updateSubCategory(Request $request, int $id)
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel sub_categories belum tersedia. Jalankan migrate dulu agar data bisa disimpan.');
        }

        $row = SubCategory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exam_mode_id' => 'required|integer|exists:exam_modes,id',
            'kind' => 'nullable|string|in:formasi,topik',
        ]);

        $row->update($validated);
        return back()->with('success', 'Sub kategori berhasil diperbarui.');
    }

    public function destroySubCategory(int $id)
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel sub_categories belum tersedia. Jalankan migrate dulu agar data bisa disimpan.');
        }

        SubCategory::findOrFail($id)->delete();
        return back()->with('success', 'Sub kategori berhasil dihapus.');
    }

    public function normalizeSubCategories()
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel sub_categories belum tersedia. Jalankan migrate dulu agar perapihan database bisa dijalankan.');
        }

        $this->syncDefaultStructures();

        $canonicalFormasi = [
            'skd' => ['TWK', 'TIU', 'TKP'],
            'skb' => ['D3 Farmasi', 'D3 Manajemen Informatika', 'S1 Teknik Informatika', 'S1 Keperawatan', 'D4 Anastesi'],
            'pppk' => ['D3 Farmasi', 'D3 Manajemen Informatika', 'S1 Teknik Informatika', 'S1 Keperawatan', 'D4 Anastesi'],
        ];

        $curatedTopik = [
            'skd' => ['Semua Materi SKD'],
            'skb' => [
                'Farmakologi Klinik',
                'Farmasi Klinik',
                'Pelayanan Kefarmasian',
                'Regulasi Kefarmasian',
                'Studi Kasus Pasien',
                'Asuhan Keperawatan Komprehensif',
                'Patient Safety',
                'Persiapan Alat Anestesi',
                'Monitoring Pasien Anestesi',
            ],
            'pppk' => [
                'Dasar Profesi Apoteker',
                'Kompetensi Teknis Umum',
                'Farmasi Klinis',
                'Pelayanan Kefarmasian',
                'Monitoring Efek Samping Obat',
                'Regulasi Obat',
                'Resep dan Dosis',
                'Asuhan Keperawatan Medikal Bedah',
                'Keperawatan Gawat Darurat',
                'Persiapan Tindakan Anestesi',
                'Monitoring Pasien Perioperatif',
            ],
        ];

        $renameMap = [
            'PPPK Teknis (Apoteker/Farmasi)' => 'Dasar Profesi Apoteker',
            'PPPK Teknis Umum' => 'Kompetensi Teknis Umum',
            'PPPK Apoteker/Farmasi' => 'Dasar Profesi Apoteker',
        ];

        $junkIfUnused = [
            'SKB Apoteker/Farmasi',
            'SKB Teknis (Administrasi)',
            'SKB Teknis (Apoteker/Farmasi)',
            'SKB Teknis (IT/Informatika)',
            'SKB Teknis (Kesehatan Umum)',
            'PPPK Guru',
            'PPPK Teknis',
            'PPPK Tenaga Administrasi',
            'PPPK Tenaga Guru',
            'PPPK Tenaga Kesehatan',
            'PPPK Umum',
        ];

        $modeMap = ExamMode::query()->pluck('id', 'code');

        DB::transaction(function () use ($canonicalFormasi, $curatedTopik, $renameMap, $junkIfUnused, $modeMap) {
            foreach ($renameMap as $from => $to) {
                SubCategory::query()->where('name', $from)->update(['name' => $to, 'kind' => 'topik']);
            }

            foreach ($canonicalFormasi as $modeCode => $names) {
                $modeId = $modeMap->get($modeCode);
                if (!$modeId) {
                    continue;
                }

                foreach ($names as $name) {
                    SubCategory::query()->updateOrCreate(
                        ['exam_mode_id' => $modeId, 'name' => $name],
                        ['kind' => 'formasi']
                    );
                }
            }

            foreach ($curatedTopik as $modeCode => $names) {
                $modeId = $modeMap->get($modeCode);
                if (!$modeId) {
                    continue;
                }

                foreach ($names as $name) {
                    SubCategory::query()->updateOrCreate(
                        ['exam_mode_id' => $modeId, 'name' => $name],
                        ['kind' => 'topik']
                    );
                }
            }

            if (Schema::hasColumn('sub_categories', 'kind')) {
                SubCategory::query()
                    ->whereIn('name', collect($canonicalFormasi)->flatten()->values()->all())
                    ->update(['kind' => 'formasi']);

                SubCategory::query()
                    ->whereNotIn('name', collect($canonicalFormasi)->flatten()->values()->all())
                    ->whereNull('kind')
                    ->update(['kind' => 'topik']);
            }

            $duplicates = SubCategory::query()
                ->select('exam_mode_id', 'name', DB::raw('COUNT(*) as total'))
                ->groupBy('exam_mode_id', 'name')
                ->having('total', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                $rows = SubCategory::query()
                    ->where('exam_mode_id', $dup->exam_mode_id)
                    ->where('name', $dup->name)
                    ->orderBy('id')
                    ->get();

                $usedIds = array_unique(array_merge(
                    Schema::hasColumn('soals', 'sub_category_id')
                        ? \App\Models\Soal::query()->whereIn('sub_category_id', $rows->pluck('id'))->pluck('sub_category_id')->map(fn ($id) => (int) $id)->all()
                        : [],
                    Schema::hasColumn('ujians', 'sub_category_id')
                        ? \App\Models\Ujian::query()->whereIn('sub_category_id', $rows->pluck('id'))->pluck('sub_category_id')->map(fn ($id) => (int) $id)->all()
                        : []
                ));

                $keeper = $rows->firstWhere('id', $usedIds[0] ?? null) ?: $rows->first();
                $dropIds = $rows->pluck('id')->reject(fn ($id) => (int) $id === (int) $keeper->id)->values();

                if ($dropIds->isEmpty()) {
                    continue;
                }

                if (Schema::hasColumn('soals', 'sub_category_id')) {
                    \App\Models\Soal::query()->whereIn('sub_category_id', $dropIds)->update(['sub_category_id' => $keeper->id]);
                }
                if (Schema::hasColumn('ujians', 'sub_category_id')) {
                    \App\Models\Ujian::query()->whereIn('sub_category_id', $dropIds)->update(['sub_category_id' => $keeper->id]);
                }

                SubCategory::query()->whereIn('id', $dropIds)->delete();
            }

            $usedIds = array_unique(array_merge(
                Schema::hasColumn('soals', 'sub_category_id')
                    ? \App\Models\Soal::query()->whereNotNull('sub_category_id')->pluck('sub_category_id')->map(fn ($id) => (int) $id)->all()
                    : [],
                Schema::hasColumn('ujians', 'sub_category_id')
                    ? \App\Models\Ujian::query()->whereNotNull('sub_category_id')->pluck('sub_category_id')->map(fn ($id) => (int) $id)->all()
                    : []
            ));

            SubCategory::query()
                ->whereIn('name', $junkIfUnused)
                ->whereNotIn('id', $usedIds)
                ->delete();
        });

        return back()->with('success', 'Database sub kategori berhasil dirapikan. Formasi utama dipertahankan, topik teknis yang penting disimpan, dan entri generik yang tidak dipakai dibersihkan.');
    }

    public function storePackage(Request $request)
    {
        if (!ExamStructureSupport::packageSchemaReady()) {
            return back()->with('success', 'Tabel exam_mode_packages belum tersedia. Jalankan migrate dulu agar konfigurasi sesi bisa disimpan.');
        }

        $validated = $this->validatePackage($request);
        ExamModePackage::create($validated);

        return back()->with('success', 'Konfigurasi sesi/mode berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, int $id)
    {
        if (!ExamStructureSupport::packageSchemaReady()) {
            return back()->with('success', 'Tabel exam_mode_packages belum tersedia. Jalankan migrate dulu agar konfigurasi sesi bisa disimpan.');
        }

        $row = ExamModePackage::findOrFail($id);
        $validated = $this->validatePackage($request, $row->id);
        $row->update($validated);

        return back()->with('success', 'Konfigurasi sesi/mode berhasil diperbarui.');
    }

    public function destroyPackage(int $id)
    {
        if (!ExamStructureSupport::packageSchemaReady()) {
            return back()->with('success', 'Tabel exam_mode_packages belum tersedia. Jalankan migrate dulu agar konfigurasi sesi bisa disimpan.');
        }

        ExamModePackage::findOrFail($id)->delete();
        return back()->with('success', 'Konfigurasi sesi/mode berhasil dihapus.');
    }

    public function applyPreset(string $preset)
    {
        if (!ExamStructureSupport::schemaReady()) {
            return back()->with('success', 'Tabel mode ujian belum tersedia. Jalankan migrate dulu agar preset bisa disimpan.');
        }

        $preset = strtolower(trim($preset));
        if (!in_array($preset, ['pppk-apoteker'], true)) {
            return back()->with('success', 'Preset tidak dikenali.');
        }

        $modeCode = 'pppk';
        $modeName = 'PPPK';
        $modeColor = '#f59e0b';

        $mode = ExamMode::query()->firstOrCreate(
            ['code' => $modeCode],
            ['name' => $modeName, 'color' => $modeColor, 'has_figural' => false, 'is_active' => true]
        );

        $subNames = [
            'D3 Farmasi',
            'D3 Manajemen Informatika',
            'S1 Teknik Informatika',
        ];

        foreach ($subNames as $name) {
            SubCategory::query()->updateOrCreate([
                'exam_mode_id' => $mode->id,
                'name' => $name,
            ], [
                'kind' => 'formasi',
            ]);
        }

        if (Schema::hasTable('exam_mode_packages')) {
            foreach (ExamStructureSupport::defaultPackages($modeCode) as $pkg) {
                ExamModePackage::query()->updateOrCreate(
                    ['exam_mode_id' => $mode->id, 'code' => $pkg['code']],
                    [
                        'name' => $pkg['name'],
                        'description' => $pkg['description'] ?? null,
                        'duration_minutes' => $pkg['duration_minutes'] ?? null,
                        'total_questions' => $pkg['total_questions'] ?? null,
                        'sections' => $pkg['sections'] ?? [],
                        'passing_rules' => $pkg['passing_rules'] ?? [],
                        'score_rules' => $pkg['score_rules'] ?? [],
                        'is_active' => $pkg['is_active'] ?? true,
                        'sort_order' => $pkg['sort_order'] ?? 0,
                    ]
                );
            }
        }

        return back()->with('success', 'Preset PPPK Farmasi berhasil diterapkan.');
    }

    private function validatePackage(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'exam_mode_id' => 'required|integer|exists:exam_modes,id',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'total_questions' => 'nullable|integer|min:1|max:500',
            'sections_json' => 'nullable|string',
            'passing_rules_json' => 'nullable|string',
            'score_rules_json' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active' => 'nullable|boolean',
        ]);

        $dup = ExamModePackage::query()
            ->where('exam_mode_id', (int) $validated['exam_mode_id'])
            ->where('code', strtoupper(trim((string) $validated['code'])));
        if ($ignoreId) {
            $dup->where('id', '!=', $ignoreId);
        }
        if ($dup->exists()) {
            throw ValidationException::withMessages(['code' => 'Kode sesi sudah dipakai pada mode ini.']);
        }

        return [
            'exam_mode_id' => (int) $validated['exam_mode_id'],
            'code' => strtoupper(trim((string) $validated['code'])),
            'name' => trim((string) $validated['name']),
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'duration_minutes' => !empty($validated['duration_minutes']) ? (int) $validated['duration_minutes'] : null,
            'total_questions' => !empty($validated['total_questions']) ? (int) $validated['total_questions'] : null,
            'sections' => $this->decodeJsonField($validated['sections_json'] ?? '[]', []),
            'passing_rules' => $this->decodeJsonField($validated['passing_rules_json'] ?? '{}', []),
            'score_rules' => $this->decodeJsonField($validated['score_rules_json'] ?? '{}', []),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    private function decodeJsonField(string $raw, array $fallback): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return $fallback;
        }

        $parsed = json_decode($raw, true);
        return is_array($parsed) ? $parsed : $fallback;
    }
}


