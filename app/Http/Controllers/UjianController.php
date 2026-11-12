<?php

namespace App\Http\Controllers;

use App\Models\ExamMode;
use App\Models\Bookmark;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Models\Ujian;
use App\Support\ExamRankingSupport;
use App\Support\ExamStructureSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserSoalHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Support\ExamDashboardSupport;

class UjianController extends Controller
{
    private static ?bool $soalKategoriIsEnum = null;

    private function soalKategoriIsEnum(): bool
    {
        if (self::$soalKategoriIsEnum !== null) return self::$soalKategoriIsEnum;
        try {
            $col = \Illuminate\Support\Facades\DB::selectOne("SHOW COLUMNS FROM soals LIKE 'kategori'");
            $type = strtolower((string) ($col->Type ?? ''));
            self::$soalKategoriIsEnum = str_starts_with($type, 'enum(');
        } catch (\Throwable $e) {
            self::$soalKategoriIsEnum = false;
        }
        return (bool) self::$soalKategoriIsEnum;
    }

    private function requestedKategoriCandidates(string $kategori): array
    {
        $kategori = strtoupper(trim((string) $kategori));
        // Figural is stored as TIU in DB (distinguished by sub_topik/prompt).
        if ($kategori === 'FIGURAL') return ['TIU'];

        // Modern schema: kategori is VARCHAR so we can trust the requested category directly.
        if (!$this->soalKategoriIsEnum()) return [$kategori];

        $map = [
            'TEKNIS' => ['TEKNIS', 'TIU'],
            'SKB_TEKNIS' => ['SKB_TEKNIS', 'TIU'],
            'SKB_REGULASI' => ['SKB_REGULASI', 'TIU'],
            'SKB_STUDI_KASUS' => ['SKB_STUDI_KASUS', 'TIU'],
            'MANAJERIAL' => ['MANAJERIAL', 'TKP'],
            'SOSIAL_KULTURAL' => ['SOSIAL_KULTURAL', 'TKP'],
            'WAWANCARA' => ['WAWANCARA', 'TKP'],
        ];

        return $map[$kategori] ?? [$kategori];
    }

    private function applyRequestedKategoriFilter($query, string $kategori)
    {
        $candidates = $this->requestedKategoriCandidates($kategori);
        return count($candidates) > 1
            ? $query->whereIn('kategori', $candidates)
            : $query->where('kategori', $candidates[0]);
    }

    private function markPickedKategori(\Illuminate\Support\Collection $rows, string $requestedKategori): \Illuminate\Support\Collection
    {
        return $rows->map(function ($row) use ($requestedKategori) {
            if ($row) {
                $row->setAttribute('requested_kategori', strtoupper(trim((string) $requestedKategori)));
            }
            return $row;
        });
    }

    private function totalScoreSelectExpression(): string
    {
        return ExamRankingSupport::totalScoreSelectExpression();
    }

    private function resolveExamMode(?int $examModeId = null, ?string $examModeCode = null): ?ExamMode
    {
        if (!Schema::hasTable('exam_modes')) {
            return null;
        }

        if ($examModeId) {
            $mode = ExamMode::query()->find($examModeId);
            if ($mode) return $mode;
        }

        $code = strtolower(trim((string) ($examModeCode ?? '')));
        if ($code !== '') {
            $mode = ExamMode::query()->where('code', $code)->first();
            if ($mode) return $mode;
        }

        return ExamMode::defaultSkd();
    }

    private function sanitizedExamFilters(Request $request): array
    {
        $user = auth()->user();
        if (!Schema::hasTable('exam_modes')) {
            return [
                'exam_mode' => null,
                'exam_mode_id' => null,
                'sub_category' => null,
                'sub_category_id' => null,
            ];
        }

        $mode = $this->resolveExamMode(
            $request->filled('exam_mode_id')
                ? (int) $request->input('exam_mode_id')
                : ((Schema::hasColumn('users', 'preferred_exam_mode_id') && $user?->preferred_exam_mode_id) ? (int) $user->preferred_exam_mode_id : null),
            $request->input('exam_mode_code')
        );

        $subCategoryId = $request->filled('sub_category_id')
            ? (int) $request->input('sub_category_id')
            : ((Schema::hasColumn('users', 'preferred_sub_category_id') && $user?->preferred_sub_category_id) ? (int) $user->preferred_sub_category_id : 0);
        $subCategory = null;

        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $subCategory = SubCategory::query()
                ->when($mode, fn ($q) => $q->where('exam_mode_id', $mode->id))
                ->find($subCategoryId);
        }

        return [
            'exam_mode' => $mode,
            'exam_mode_id' => $mode?->id,
            'sub_category' => $subCategory,
            'sub_category_id' => $subCategory?->id,
        ];
    }

    private function formasiRequiredForMode(?string $modeCode = null): bool
    {
        return in_array(strtolower(trim((string) ($modeCode ?? ''))), ['skb', 'pppk'], true);
    }

    private function requiredFormasiMessage(?string $modeCode = null): string
    {
        return $this->formasiRequiredForMode($modeCode)
            ? 'Formasi wajib dipilih untuk mode SKB dan PPPK supaya sesi dan stok soalnya tepat.'
            : '';
    }

    private function normalizeJawabanKey($value): string
    {
        $s = strtoupper((string) ($value ?? ''));
        if (preg_match('/[A-E]/', $s, $m)) return $m[0];
        return '';
    }

    private function normalizeSoalText($value): string
    {
        $s = (string) ($value ?? '');
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (function_exists('mb_strtolower')) {
            $s = mb_strtolower($s, 'UTF-8');
        } else {
            $s = strtolower($s);
        }
        $s = preg_replace('/\s+/u', ' ', trim($s));
        return $s ?? '';
    }

    /**
     * Signature used to dedupe questions inside one exam session.
     * Prefers DB-level hash_soal when available; otherwise computes a stable hash from content.
     */
    private function soalSignature(Soal $soal): string
    {
        if (!empty($soal->hash_soal)) return (string) $soal->hash_soal;

        $parts = [
            $this->normalizeSoalText($soal->teks_soal),
            $this->normalizeSoalText($soal->opsi_a),
            $this->normalizeSoalText($soal->opsi_b),
            $this->normalizeSoalText($soal->opsi_c),
            $this->normalizeSoalText($soal->opsi_d),
            $this->normalizeSoalText($soal->opsi_e),
        ];

        if ((string) $soal->kategori === 'TKP') {
            $parts[] = json_encode($soal->bobot_tkp ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $parts[] = $this->normalizeSoalText($soal->jawaban_benar);
        }

        return sha1(implode('|', $parts));
    }

    /**
     * Pick random questions while ensuring uniqueness by signature. Memprioritaskan soal baru (fresh) yang belum pernah dijawab.
     */
    private function pickUniqueRandom(
        string $kategori,
        int $targetCount,
        array &$usedSignatures,
        array $soalTerpakaiUserIds = [],
        array $soalTerpakaiUserSignatures = [],
        string $subTopikFilter = '',
        ?int $examModeId = null,
        ?int $subCategoryId = null,
        ?int $defaultSkdId = null
    ): \Illuminate\Support\Collection
    {
        $picked = collect();
        $attempt = 0;

        while ($picked->count() < $targetCount && $attempt < 12) {
            $need = $targetCount - $picked->count();
            $take = min(max($need * 4, $need + 8), 220);

            $batch = collect();
            $freshIds = [];

            // 1) Prioritas utama: soal yang belum pernah dipakai user (by ID).
            if (!empty($soalTerpakaiUserIds)) {
                $unusedQuery = $this->applyRequestedKategoriFilter(
                    Soal::siapUjian(),
                    $kategori
                )
                    ->whereNotIn('id', $soalTerpakaiUserIds)
                    ->forExamMode($examModeId, $defaultSkdId)
                    ->forSubCategory($subCategoryId);
                if ($subTopikFilter !== '') $unusedQuery->where('sub_topik', $subTopikFilter);
                if (Schema::hasColumn('soals', 'standar_tahun')) {
                    $unusedQuery->orderByRaw("CASE WHEN standar_tahun = '2027' THEN 0 ELSE 1 END");
                }
                $batch = $unusedQuery->inRandomOrder()->take($take)->get();
                $freshIds = $batch->pluck('id')->map(fn ($id) => (int) $id)->all();
            }

            // 2) Jika belum cukup, fallback ke soal yang pernah dipakai tapi yang paling lama (LRU).
            // Tujuan: rotasi maksimal; soal yang baru dipakai akan muncul paling akhir.
            if ($batch->count() < $take && !empty($soalTerpakaiUserIds)) {
                $moreTake = $take - $batch->count();
                $oldestIds = UserSoalHistory::query()
                    ->where('user_id', auth()->id())
                    ->where('kategori', $kategori)
                    ->orderBy('used_at', 'asc')
                    ->limit($moreTake * 3) // ambil lebih untuk filter signature
                    ->pluck('soal_id')
                    ->filter()
                    ->values()
                    ->toArray();

                if (!empty($oldestIds)) {
                    $usedBatch = $this->applyRequestedKategoriFilter(
                        Soal::siapUjian(),
                        $kategori
                    )
                        ->whereIn('id', $oldestIds)
                        ->forExamMode($examModeId, $defaultSkdId)
                        ->forSubCategory($subCategoryId);
                    if ($subTopikFilter !== '') $usedBatch->where('sub_topik', $subTopikFilter);
                    if (Schema::hasColumn('soals', 'standar_tahun')) {
                        $usedBatch->orderByRaw("CASE WHEN standar_tahun = '2027' THEN 0 ELSE 1 END");
                    }
                    $moreItems = $usedBatch->get();
                    $batch = $batch->concat($moreItems);
                }
            }

            // Fallback (jika user baru & array kosong)
            if (empty($soalTerpakaiUserIds)) {
                $fb = $this->applyRequestedKategoriFilter(
                    Soal::siapUjian(),
                    $kategori
                )
                    ->forExamMode($examModeId, $defaultSkdId)
                    ->forSubCategory($subCategoryId);
                if ($subTopikFilter !== '') $fb->where('sub_topik', $subTopikFilter);
                if (Schema::hasColumn('soals', 'standar_tahun')) {
                    $fb->orderByRaw("CASE WHEN standar_tahun = '2027' THEN 0 ELSE 1 END");
                }
                $batch = $fb->inRandomOrder()->take($take)->get();
            }

            if ($batch->isEmpty()) break;

            foreach ($batch as $s) {
                $sig = $this->soalSignature($s);
                if (isset($usedSignatures[$sig])) continue;
                $isFreshCandidate = in_array((int) $s->id, $freshIds, true);
                // Fresh questions should avoid signatures the user has seen before.
                // Repeat/fallback questions may reuse them to fill the remaining quota.
                if (
                    $isFreshCandidate
                    && !empty($soalTerpakaiUserSignatures)
                    && isset($soalTerpakaiUserSignatures[$sig])
                ) continue;
                $usedSignatures[$sig] = true;
                $picked->push($s);
                if ($picked->count() >= $targetCount) break;
            }

            $attempt++;
        }

        return $this->markPickedKategori($picked, $kategori);
    }

    private function getUserUsedSoalSets(int $userId): array
    {
        // Signatures protect against duplicate content with different IDs.
        $rows = UserSoalHistory::query()
            ->where('user_id', $userId)
            ->select(['soal_id', 'signature'])
            ->get();

        // Backfill (one-time): if history table is empty, derive from past exams so anti-repeat works immediately.
        if ($rows->isEmpty()) {
            $legacyIds = \App\Models\Ujian::where('user_id', $userId)
                ->get()
                ->flatMap(function ($u) {
                    $arr = is_array($u->jawaban_user) ? $u->jawaban_user : (json_decode($u->jawaban_user, true) ?? []);
                    return array_keys($arr ?? []);
                })
                ->unique()
                ->filter()
                ->map(fn($id) => (int) $id)
                ->values()
                ->toArray();

            if (!empty($legacyIds)) {
                $soals = Soal::whereIn('id', $legacyIds)->get();
                $this->touchUserSoalHistory($userId, 0, $soals);
                $rows = UserSoalHistory::query()
                    ->where('user_id', $userId)
                    ->select(['soal_id', 'signature'])
                    ->get();
            }
        }

        $ids = [];
        $sigs = [];
        foreach ($rows as $r) {
            if (!empty($r->soal_id)) $ids[] = (int) $r->soal_id;
            $sig = (string) ($r->signature ?? '');
            if ($sig !== '') $sigs[$sig] = true;
        }

        return [
            'ids' => array_values(array_unique($ids)),
            'sigs' => $sigs,
        ];
    }

    private function touchUserSoalHistory(int $userId, int $ujianId, \Illuminate\Support\Collection $soals): void
    {
        $now = now();
        $rows = [];
        foreach ($soals as $s) {
            if (!$s instanceof Soal) continue;
            $sig = $this->soalSignature($s);
            if ($sig === '') continue;
            $rows[] = [
                'user_id' => $userId,
                'soal_id' => (int) $s->id,
                'kategori' => (string) $s->kategori,
                'signature' => $sig,
                'ujian_id' => $ujianId > 0 ? $ujianId : null,
                'used_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (empty($rows)) return;

        // Upsert by (user_id, signature) so duplicate content won't be repeated even if IDs differ.
        DB::table('user_soal_histories')->upsert(
            $rows,
            ['user_id', 'signature'],
            ['soal_id', 'kategori', 'ujian_id', 'used_at', 'updated_at']
        );
    }

    private function resolvePackageConfig(?int $examModeId, string $paket): ?object
    {
        $mode = collect(ExamStructureSupport::modes())->firstWhere('id', $examModeId) ?: ExamStructureSupport::defaultMode();
        return ExamStructureSupport::packageConfig($examModeId, $mode->code ?? 'skd', $paket);
    }

    private function expectedCountsFromConfig(?object $packageConfig): array
    {
        $expected = [];
        foreach ((array) ($packageConfig->sections ?? []) as $section) {
            $kategori = strtoupper(trim((string) ($section['kategori'] ?? '')));
            $count = (int) ($section['count'] ?? 0);
            if ($kategori === '' || $count <= 0) continue;
            $expected[$kategori] = ($expected[$kategori] ?? 0) + $count;
        }
        return $expected;
    }

    private function countPickedForRequestedKategori(\Illuminate\Support\Collection $soalsRaw, string $requestedKategori): int
    {
        $requestedKategori = strtoupper(trim((string) $requestedKategori));
        return $soalsRaw->filter(function ($row) use ($requestedKategori) {
            $runtime = strtoupper(trim((string) ($row->requested_kategori ?? $row->kategori ?? '')));
            return $runtime === $requestedKategori;
        })->count();
    }

    private function availableCountForRequestedKategori(string $requestedKategori, ?int $examModeId = null, ?int $subCategoryId = null, ?int $defaultSkdId = null): int
    {
        return (int) $this->applyRequestedKategoriFilter(
            Soal::siapUjian(),
            $requestedKategori
        )
            ->forExamMode($examModeId, $defaultSkdId)
            ->forSubCategory($subCategoryId)
            ->count();
    }

    private function analyzePackageAvailability(
        ?object $packageConfig,
        \Illuminate\Support\Collection $soalsRaw,
        ?int $examModeId = null,
        ?int $subCategoryId = null
    ): array {
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $expected = $this->expectedCountsFromConfig($packageConfig);
        $totalShortages = [];
        $uniqueShortages = [];

        foreach ($expected as $kat => $need) {
            if ($need <= 0) {
                continue;
            }
            $uniqueHave = $this->countPickedForRequestedKategori($soalsRaw, $kat);
            $totalHave = $this->availableCountForRequestedKategori($kat, $examModeId, $subCategoryId, $defaultSkdId);

            if ($totalHave < $need) {
                $totalShortages[$kat] = ['need' => $need, 'have' => $totalHave];
                continue;
            }

            if ($uniqueHave < $need) {
                $uniqueShortages[$kat] = ['need' => $need, 'have' => $uniqueHave, 'repeat' => max(0, $need - $uniqueHave)];
            }
        }

        return [
            'expected' => $expected,
            'total_shortages' => $totalShortages,
            'unique_shortages' => $uniqueShortages,
        ];
    }

    private function buildQuestionRuntimeMap(\Illuminate\Support\Collection $soalsRaw): array
    {
        return $soalsRaw->mapWithKeys(function ($row) {
            return [
                (string) $row->id => strtoupper(trim((string) ($row->requested_kategori ?? $row->kategori ?? ''))),
            ];
        })->all();
    }

    private function packageSnapshotPayload(?object $packageConfig, string $paket, \Illuminate\Support\Collection $soalsRaw): array
    {
        return [
            'code' => $packageConfig->code ?? $paket,
            'name' => $packageConfig->name ?? $paket,
            'description' => $packageConfig->description ?? null,
            'duration_minutes' => $packageConfig->duration_minutes ?? null,
            'total_questions' => $packageConfig->total_questions ?? null,
            'sections' => $packageConfig->sections ?? [],
            'passing_rules' => $packageConfig->passing_rules ?? [],
            'score_rules' => $packageConfig->score_rules ?? [],
            'question_runtime_map' => $this->buildQuestionRuntimeMap($soalsRaw),
        ];
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
            $missingCount = count($missing);
            $repeatEstimate = (int) $sections
                ->filter(fn ($row) => !($row['ready'] ?? false))
                ->sum(fn ($row) => max(0, ((int) ($row['need'] ?? 0)) - ((int) ($row['available'] ?? 0))));

            return [
                'code' => (string) ($pkg->code ?? ''),
                'name' => (string) ($pkg->name ?? ($pkg->code ?? 'Sesi')),
                'ready' => $ready,
                'hard_blocked' => !$ready,
                'sections' => $sections->all(),
                'missing' => $missing,
                'missing_count' => $missingCount,
                'repeat_estimate' => $repeatEstimate,
            ];
        })->all();
    }

    private function durationSecondsFromConfig(?object $packageConfig, string $paket): int
    {
        $minutes = (int) ($packageConfig->duration_minutes ?? 0);
        if ($minutes > 0) {
            return $minutes * 60;
        }
        return strtoupper((string) $paket) === 'D' ? 6000 : 1800;
    }

    /**
     * Collect questions for a given paket with content dedupe across the entire exam.
     */
    private function collectSoalsForPaket(
        string $paket,
        string $subTopikFilter = '',
        ?int $examModeId = null,
        ?int $subCategoryId = null,
        bool $isUjiCoba = false
    ): \Illuminate\Support\Collection
    {
        $paket = strtoupper((string) $paket);
        $used = [];
        $soals = collect();
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $packageConfig = $this->resolvePackageConfig($examModeId, $paket);
        $sections = array_values(array_filter((array) ($packageConfig->sections ?? []), function ($section) {
            return !empty($section['kategori']) && (int) ($section['count'] ?? 0) > 0;
        }));

        $user = auth()->user();
        $soalTerpakaiUserIds = [];
        $soalTerpakaiUserSigs = [];
        if ($user) {
            $sets = $this->getUserUsedSoalSets((int) $user->id);
            $soalTerpakaiUserIds = $sets['ids'] ?? [];
            $soalTerpakaiUserSigs = $sets['sigs'] ?? [];
        }

        $singleSection = count($sections) === 1;
        foreach ($sections as $section) {
            $kategori = strtoupper(trim((string) ($section['kategori'] ?? '')));
            $count = (int) ($section['count'] ?? 0);
            if ($kategori === '' || $count <= 0) continue;
            $sectionTopikFilter = $singleSection ? $subTopikFilter : '';
            if ($isUjiCoba) {
                $batch = $this->applyRequestedKategoriFilter(
                    Soal::siapUjian(),
                    $kategori
                )
                    ->forExamMode($examModeId, $defaultSkdId)
                    ->forSubCategory($subCategoryId);
                if ($sectionTopikFilter !== '') $batch->where('sub_topik', $sectionTopikFilter);
                if (Schema::hasColumn('soals', 'standar_tahun')) {
                    $batch->orderByRaw("CASE WHEN standar_tahun = '2027' THEN 0 ELSE 1 END");
                }
                
                // Fetch fixed set of questions and shuffle them in PHP
                $pickedForSection = $batch->orderBy('id', 'asc')->take($count)->get()->shuffle();
                $pickedForSection = $this->markPickedKategori($pickedForSection, $kategori);
                $soals = $soals->concat($pickedForSection);
            } else {
                $soals = $soals->concat($this->pickUniqueRandom($kategori, $count, $used, $soalTerpakaiUserIds, $soalTerpakaiUserSigs, $sectionTopikFilter, $examModeId, $subCategoryId, $defaultSkdId));
            }
        }

        return $soals->values();
    }

    private function collectSoalsForPaketWithFallback(
        string $paket,
        string $subTopikFilter = '',
        ?int $examModeId = null,
        ?int $subCategoryId = null,
        bool $isUjiCoba = false
    ): \Illuminate\Support\Collection {
        $attempts = [
            [$subTopikFilter, $examModeId, $subCategoryId],
        ];

        if ($subTopikFilter !== '') {
            $attempts[] = ['', $examModeId, $subCategoryId];
        }
        if ($subCategoryId) {
            $attempts[] = ['', $examModeId, null];
        }
        if ($examModeId || $subCategoryId) {
            $attempts[] = ['', null, null];
        }

        foreach ($attempts as [$attemptTopik, $attemptMode, $attemptSubCategory]) {
            $rows = $this->collectSoalsForPaket($paket, (string) $attemptTopik, $attemptMode, $attemptSubCategory, $isUjiCoba);
            if ($rows->isNotEmpty()) {
                return $rows;
            }
        }

        return collect();
    }

    /**
     * Halaman Utama: Dashboard dengan Statistik & Progres
     */
    public function index()
    {
        return view('ujian.index_dashboard', ExamDashboardSupport::dashboardData(auth()->user()));
    }

    /**
     * Return the latest distinct sub_topik lists per kategori (TWK/TIU/TKP).
     * Used by the start-exam modal so options update without full page refresh.
     */
    public function subTopikOptions(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['ok' => false, 'error' => 'unauthorized'], 401);

        $filters = $this->sanitizedExamFilters($request);
        $force = (string) $request->query('force', '') !== '';
        $cacheKey = 'sub_topik_options_v2:' . ((int) ($filters['exam_mode_id'] ?? 0)) . ':' . ((int) ($filters['sub_category_id'] ?? 0));
        if ($force) {
            Cache::forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, 10, function () use ($filters) {
            $out = [];
            $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
            $categories = ExamStructureSupport::categoryOptions($filters['exam_mode_id'], $filters['exam_mode']?->code);
            foreach ($categories as $category) {
                $k = strtoupper(trim((string) ($category->value ?? '')));
                if ($k === '' || $k === 'FIGURAL') {
                    continue;
                }
                $raw = Soal::where('kategori', $k)
                    ->forExamMode($filters['exam_mode_id'], $defaultSkdId)
                    ->forSubCategory($filters['sub_category_id'])
                    ->whereNotNull('sub_topik')
                    ->distinct()
                    ->orderBy('sub_topik')
                    ->pluck('sub_topik')
                    ->values()
                    ->toArray();

                // Trim + remove blanks + de-dup (DB distinct isn't enough when whitespace differs).
                $clean = [];
                foreach ($raw as $t) {
                    $s = trim((string) $t);
                    if ($s === '') continue;
                    $clean[$s] = true;
                }
                $out[$k] = array_values(array_keys($clean));
            }
            return $out;
        });
        $counts = [];
        foreach ($data as $kategori => $rows) {
            $counts[$kategori] = is_array($rows) ? count($rows) : 0;
        }

        return response()->json(['ok' => true, 'data' => $data, 'counts' => $counts]);
    }

    public function sessionStockOptions(Request $request)
    {
        $filters = $this->sanitizedExamFilters($request);
        $mode = $filters['exam_mode'] ?? ExamStructureSupport::defaultMode();
        $requiresFormasi = $this->formasiRequiredForMode($mode->code ?? null);

        return response()->json([
            'ok' => true,
            'requires_sub_category' => $requiresFormasi,
            'required_message' => $this->requiredFormasiMessage($mode->code ?? null),
            'data' => $this->buildSessionStockData(
                $filters['exam_mode_id'],
                $mode->code ?? 'skd',
                $filters['sub_category_id']
            ),
        ]);
    }

    /**
     * Halaman Ranking / Leaderboard
     * Method ini yang dicari oleh error "Method dashboard does not exist"
     */
    public function dashboard()
    {
        $search = trim((string) request()->query('q', ''));
        $paket = strtoupper(trim((string) request()->query('paket', '')));
        $examModeId = (int) request()->query('exam_mode_id', 0);
        $subCategoryId = (int) request()->query('sub_category_id', 0);
        if (!in_array($paket, ['A', 'B', 'C', 'D'], true)) {
            $paket = '';
        }

        $examModes = ExamStructureSupport::modes()->values();
        $selectedMode = $examModeId > 0 ? $examModes->firstWhere('id', $examModeId) : null;
        $subCategories = ExamStructureSupport::subCategories($examModeId ?: null, $selectedMode?->code ?? null, 'formasi')->values();
        if ($subCategoryId > 0 && !$subCategories->contains(fn ($sub) => (int) $sub->id === $subCategoryId)) {
            $subCategoryId = 0;
        }

        $rankings = Ujian::query()
            ->when(Schema::hasColumn('ujians', 'exam_mode_id') || Schema::hasColumn('ujians', 'sub_category_id'), fn ($q) => $q->with(['examMode', 'subCategory']))
            ->where('is_selesai', true)
            ->when($examModeId > 0 && Schema::hasColumn('ujians', 'exam_mode_id'), fn ($q) => $q->where('exam_mode_id', $examModeId))
            ->when($subCategoryId > 0 && Schema::hasColumn('ujians', 'sub_category_id'), fn ($q) => $q->where('sub_category_id', $subCategoryId))
            ->when($search !== '', fn ($q) => $q->where('nama_peserta', 'like', '%' . $search . '%'))
            ->when($paket !== '', fn ($q) => $q->where('paket', $paket))
            ->selectRaw('*, ' . ExamRankingSupport::totalScoreSelectExpression())
            ->orderByDesc('total_skor')
            ->orderByDesc('skor_tkp')
            ->orderByDesc('skor_tiu')
            ->get();

        $defaultMode = ExamStructureSupport::defaultMode();
        $modeStats = $examModes->map(function ($mode) use ($rankings, $defaultMode) {
            $count = $rankings->filter(function ($row) use ($mode, $defaultMode) {
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
        })->filter(fn ($item) => $item->count > 0)->values();

        return view('ujian.dashboard', compact('search', 'paket', 'examModeId', 'subCategoryId', 'examModes', 'selectedMode', 'subCategories', 'rankings', 'modeStats'));
    }

    /**
     * Quick-start exam session for mobile dock CTA.
     * Creates a new exam immediately with sensible defaults (no modal step).
     */
    public function quickStart(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $paket = strtoupper((string) $request->query('paket', 'D'));
        if (!in_array($paket, ['A', 'B', 'C', 'D'], true)) $paket = 'D';

        $subTopikFilter = (string) ($request->query('sub_topik', '') ?? '');
        $filters = $this->sanitizedExamFilters($request);
        $modeCode = strtolower((string) ($filters['exam_mode']?->code ?? ''));
        if ($this->formasiRequiredForMode($modeCode) && empty($filters['sub_category_id'])) {
            return redirect()->route('ujian.index')->withInput($request->all())->with('error', $this->requiredFormasiMessage($modeCode));
        }
        $soalsRaw = $this->collectSoalsForPaketWithFallback($paket, $subTopikFilter, $filters['exam_mode_id'], $filters['sub_category_id']);
        if ($soalsRaw->isEmpty()) {
            return redirect()->route('ujian.index')->with('error', 'Database soal kosong atau kategori tidak ditemukan!');
        }

        $packageConfig = $this->resolvePackageConfig($filters['exam_mode_id'], $paket);
        $availability = $this->analyzePackageAvailability($packageConfig, $soalsRaw, $filters['exam_mode_id'], $filters['sub_category_id']);
        $topikText = $subTopikFilter ? " (Topik: {$subTopikFilter})" : "";

        if (!empty($availability['total_shortages'])) {
            $firstKat = array_key_first($availability['total_shortages']);
            $short = $availability['total_shortages'][$firstKat];
            return redirect()->route('ujian.index')->withInput($request->all())->with('error', "Stok soal kategori {$firstKat}{$topikText} belum cukup ({$short['have']}/{$short['need']}). Tambah/generate soal dulu sebelum mulai.");
        }

        if (!empty($availability['unique_shortages'])) {
            $firstKat = array_key_first($availability['unique_shortages']);
            $short = $availability['unique_shortages'][$firstKat];
            return redirect()->route('ujian.index')->withInput($request->all())->with('error', "Soal unik kategori {$firstKat}{$topikText} belum cukup ({$short['have']}/{$short['need']}). Kamu masih bisa lanjut dengan soal berulang.");
        }

        $jawabanUser = $soalsRaw->pluck('id')->unique()->mapWithKeys(fn($id) => [$id => null])->toArray();

        $payload = [
            'user_id'      => $user->id,
            'nama_peserta' => $user->name,
            'paket'        => $paket,
            'is_selesai'   => false,
            'sisa_waktu'   => $this->durationSecondsFromConfig($packageConfig, $paket),
            'jawaban_user' => $jawabanUser,
            'skor_twk'     => 0,
            'skor_tiu'     => 0,
            'skor_tkp'     => 0,
        ];
        if (Schema::hasColumn('ujians', 'sub_topik_filter')) {
            $payload['sub_topik_filter'] = $subTopikFilter !== '' ? $subTopikFilter : null;
        }
        if (Schema::hasColumn('ujians', 'exam_mode_id')) {
            $payload['exam_mode_id'] = $filters['exam_mode_id'] ?: null;
        }
        if (Schema::hasColumn('ujians', 'sub_category_id')) {
            $payload['sub_category_id'] = $filters['sub_category_id'] ?: null;
        }
        if (Schema::hasColumn('ujians', 'exam_mode_package_id')) {
            $payload['exam_mode_package_id'] = $packageConfig->id ?? null;
        }
        if (Schema::hasColumn('ujians', 'duration_minutes')) {
            $payload['duration_minutes'] = (int) ($packageConfig->duration_minutes ?? 0) ?: null;
        }
        if (Schema::hasColumn('ujians', 'package_snapshot')) {
            $payload['package_snapshot'] = $this->packageSnapshotPayload($packageConfig, $paket, $soalsRaw);
        }
        $ujian = Ujian::create($payload);

        // Record all shown questions for rotation (anti-repeat across exams).
        $this->touchUserSoalHistory((int) $user->id, (int) $ujian->id, $soalsRaw);

        return redirect()->route('ujian.show', $ujian->id);
    }

    /**
     * Logika Membuat Ujian Baru
     */
    public function buatUjian(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'paket' => 'required|in:A,B,C,D',
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'allow_partial' => 'nullable|boolean',
        ]);

        $subTopikFilter = (string) ($request->input('sub_topik', '') ?? '');
        $allowPartial = (bool) $request->boolean('allow_partial');
        $filters = $this->sanitizedExamFilters($request);
        $modeCode = strtolower((string) ($filters['exam_mode']?->code ?? ''));
        if ($this->formasiRequiredForMode($modeCode) && empty($filters['sub_category_id'])) {
            return redirect()->back()->withInput()->with('error', $this->requiredFormasiMessage($modeCode));
        }
        
        $isUjiCoba = $request->boolean('is_uji_coba');
        $soalsRaw = $this->collectSoalsForPaketWithFallback($request->paket, $subTopikFilter, $filters['examModeId'] ?? $filters['exam_mode_id'], $filters['subCategoryId'] ?? $filters['sub_category_id'], $isUjiCoba);

        if ($soalsRaw->isEmpty()) {
            return redirect()->back()->withInput()->with('error', 'Database soal kosong atau kategori tidak ditemukan!');
        }

        $paket = strtoupper((string) $request->paket);
        $packageConfig = $this->resolvePackageConfig($filters['exam_mode_id'], $paket);
        $availability = $this->analyzePackageAvailability($packageConfig, $soalsRaw, $filters['exam_mode_id'], $filters['sub_category_id']);
        $topikText = $subTopikFilter ? " (Topik: {$subTopikFilter})" : "";

        if (!empty($availability['total_shortages'])) {
            $firstKat = array_key_first($availability['total_shortages']);
            $short = $availability['total_shortages'][$firstKat];
            return redirect()->back()->withInput()->with('error', "Stok soal kategori {$firstKat}{$topikText} belum cukup ({$short['have']}/{$short['need']}). Tambah/generate soal dulu sebelum mulai.");
        }

        if (!$allowPartial && !empty($availability['unique_shortages'])) {
            $firstKat = array_key_first($availability['unique_shortages']);
            $short = $availability['unique_shortages'][$firstKat];
            return redirect()->back()->withInput()->with('error', "Soal unik kategori {$firstKat}{$topikText} belum cukup ({$short['have']}/{$short['need']}). Kamu bisa pilih Tetap Lanjut untuk memakai soal berulang.");
        }

        // 2. Inisialisasi jawaban (Key ID Soal => Null)
        $jawabanUser = $soalsRaw->pluck('id')->unique()->mapWithKeys(fn($id) => [$id => null])->toArray();

        // 3. Simpan ke database dengan inisialisasi skor 0 (PENTING)
        $payload = [
            'user_id'      => auth()->id(),
            'nama_peserta' => $request->nama,
            'paket'        => $request->paket,
            'is_selesai'   => false,
            'sisa_waktu'   => $this->durationSecondsFromConfig($packageConfig, $request->paket),
            'jawaban_user' => $jawabanUser,
            'skor_twk'     => 0,
            'skor_tiu'     => 0,
            'skor_tkp'     => 0,
            'is_uji_coba'  => $isUjiCoba,
        ];
        if (Schema::hasColumn('ujians', 'sub_topik_filter')) {
            $payload['sub_topik_filter'] = $subTopikFilter !== '' ? $subTopikFilter : null;
        }
        if (Schema::hasColumn('ujians', 'exam_mode_id')) {
            $payload['exam_mode_id'] = $filters['exam_mode_id'] ?: null;
        }
        if (Schema::hasColumn('ujians', 'sub_category_id')) {
            $payload['sub_category_id'] = $filters['sub_category_id'] ?: null;
        }
        if (Schema::hasColumn('ujians', 'exam_mode_package_id')) {
            $payload['exam_mode_package_id'] = $packageConfig->id ?? null;
        }
        if (Schema::hasColumn('ujians', 'duration_minutes')) {
            $payload['duration_minutes'] = (int) ($packageConfig->duration_minutes ?? 0) ?: null;
        }
        if (Schema::hasColumn('ujians', 'package_snapshot')) {
            $payload['package_snapshot'] = $this->packageSnapshotPayload($packageConfig, $request->paket, $soalsRaw);
        }
        $ujian = Ujian::create($payload);

        // Record all shown questions for rotation (anti-repeat across exams).
        $uid = (int) auth()->id();
        if ($uid > 0) $this->touchUserSoalHistory($uid, (int) $ujian->id, $soalsRaw);

        return redirect()->route('ujian.show', $ujian->id);
    }

    /**
     * Halaman Ujian Berlangsung
     */
    public function show($id)
    {
        $ujian = Ujian::findOrFail($id);

        // Jika ujian sudah selesai, lempar ke halaman hasil
        if ($ujian->is_selesai) {
            return redirect()->route('ujian.hasil', $id)->with('error', 'Ujian ini sudah selesai.');
        }

        $jawaban = $ujian->jawaban_user;

        // Proteksi data rusak
        if (empty($jawaban)) {
            return redirect()->route('ujian.index')->with('error', 'Data soal rusak. Silakan hapus dan buat ujian baru.');
        }

        $soalIds = array_keys($jawaban);

        // Ambil soal dan urutkan sesuai urutan ID di array jawaban_user (agar posisi soal tidak acak ulang saat refresh)
        $soals = Soal::whereIn('id', $soalIds)
            ->orderByRaw("FIELD(id, " . implode(',', $soalIds) . ")")
            ->get();

        $bookmarkedSoalIds = Schema::hasTable('bookmarks')
            ? Bookmark::query()
                ->where('user_id', auth()->id())
                ->whereIn('question_id', $soalIds)
                ->pluck('question_id')
                ->map(fn ($qid) => (int) $qid)
                ->values()
                ->all()
            : [];

        $pesertaLomba = [];
        if (Schema::hasColumn('ujians', 'is_uji_coba') && $ujian->is_uji_coba) {
            $pesertaLomba = Ujian::where('exam_mode_id', $ujian->exam_mode_id)
                ->where('sub_category_id', $ujian->sub_category_id)
                ->where('paket', $ujian->paket)
                ->where('is_uji_coba', true)
                ->where('created_at', '>=', now()->subHours(12)) // Active recently
                ->orderBy('id', 'desc')
                ->get(['id', 'nama_peserta', 'is_selesai', 'sisa_waktu']);
        }

        return view('ujian.running', compact('ujian', 'soals', 'bookmarkedSoalIds', 'pesertaLomba'));
    }

    /**
     * Simpan Jawaban via AJAX
     */
    public function simpanJawaban(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);
        
        $jawaban = (array) ($ujian->jawaban_user ?? []);
        
        // Simpan jawaban (paksa Uppercase agar matching kunci jawaban aman)
        $soalId = $request->input('soal_id', $request->input('id_soal'));
        $pilihan = $request->input('pilihan', $request->input('jawaban'));
        if ($soalId) {
            $jawaban[$soalId] = $pilihan ? strtoupper(trim((string) $pilihan)) : null;
        }

        $updatePayload = [
            'jawaban_user' => $jawaban,
        ];
        if ($request->has('sisa_waktu') && $request->sisa_waktu !== null && $request->sisa_waktu !== '') {
            $updatePayload['sisa_waktu'] = (int) $request->sisa_waktu;
        }

        $ujian->update($updatePayload);

        return response()->json(['success' => true]);
    }

    /**
     * Hitung Skor Akhir (Finish Exam)
     */
    public function selesai($id)
    {
        $ujian = Ujian::findOrFail($id);

        if ($ujian->is_selesai) return redirect()->route('ujian.hasil', $id);

        $scored = app(\App\Services\ExamSessionService::class)->scoreExam($ujian);
        $waktuTotalDetik = (int) ($scored['waktu_total_detik'] ?? ($ujian->paket !== 'D' ? 1800 : 6000));

        $payload = [
            'skor_twk' => $scored['skor_twk'],
            'skor_tiu' => $scored['skor_tiu'],
            'skor_tkp' => $scored['skor_tkp'],
            'is_selesai' => true,
            'analitik_topik' => $scored['analitik_topik'],
            'waktu_pengerjaan' => max(0, $waktuTotalDetik - $ujian->sisa_waktu),
        ];
        if (Schema::hasColumn('ujians', 'total_score')) $payload['total_score'] = $scored['total_skor'] ?? 0;
        if (Schema::hasColumn('ujians', 'score_breakdown')) $payload['score_breakdown'] = $scored['score_breakdown'] ?? [];
        if (Schema::hasColumn('ujians', 'result_summary')) $payload['result_summary'] = $scored['result_summary'] ?? [];
        if (Schema::hasColumn('ujians', 'package_snapshot')) $payload['package_snapshot'] = $scored['package_snapshot'] ?? [];
        if (Schema::hasColumn('ujians', 'duration_minutes')) $payload['duration_minutes'] = intdiv($waktuTotalDetik, 60);
        $ujian->update($payload);
        app(\App\Services\LearningAnalysisService::class)->persistExamAnswers($ujian->fresh(), (array) ($scored['answers'] ?? []));

        return redirect()->route('ujian.hasil', $id);
    }

    /**
     * Halaman Detail Hasil Individu
     */
    public function hasil($id)
    {
        $ujian = Ujian::findOrFail($id);
        $resultSummary = is_array($ujian->result_summary ?? null) ? $ujian->result_summary : [];
        $isLulus = array_key_exists('is_pass', $resultSummary)
            ? (bool) $resultSummary['is_pass']
            : ($ujian->skor_twk >= 65 && $ujian->skor_tiu >= 80 && $ujian->skor_tkp >= 166);

        // Ambil detail soal untuk pembahasan
        $soalIds = array_keys($ujian->jawaban_user);
        $details = Soal::whereIn('id', $soalIds)
                    ->orderByRaw("FIELD(id, " . implode(',', $soalIds) . ")")
                    ->get();

        $bookmarkedSoalIds = Schema::hasTable('bookmarks')
            ? Bookmark::query()
                ->where('user_id', auth()->id())
                ->whereIn('question_id', $soalIds)
                ->pluck('question_id')
                ->map(fn ($qid) => (int) $qid)
                ->values()
                ->all()
            : [];

        return view('ujian.hasil', compact('ujian', 'isLulus', 'details', 'bookmarkedSoalIds', 'resultSummary')); 
    }

    /**
     * Hapus Data Ujian
     */
    public function destroy($id)
    {
        $ujian = Ujian::findOrFail($id);
        if (!$ujian->is_selesai && Schema::hasTable('user_soal_histories')) {
            UserSoalHistory::query()
                ->where('user_id', (int) $ujian->user_id)
                ->where('ujian_id', (int) $ujian->id)
                ->delete();
        }
        $ujian->delete();

        return redirect()->route('ujian.index')->with('success', 'Ujian berhasil dihapus');
    }

    /**
     * Halaman Leaderboard
     */
    public function leaderboard(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $paket = strtoupper(trim((string) $request->query('paket', 'D')));
        $examModeId = (int) $request->query('exam_mode_id', 0);
        $subCategoryId = (int) $request->query('sub_category_id', 0);
        if (!in_array($paket, ['A', 'B', 'C', 'D'], true)) {
            $paket = 'D';
        }

        $examModes = ExamStructureSupport::modes()->values();
        $selectedMode = $examModeId > 0 ? $examModes->firstWhere('id', $examModeId) : null;
        $subCategories = ExamStructureSupport::subCategories($examModeId ?: null, $selectedMode?->code ?? null, 'formasi')->values();
        if ($subCategoryId > 0 && !$subCategories->contains(fn ($sub) => (int) $sub->id === $subCategoryId)) {
            $subCategoryId = 0;
        }

        $rankings = Ujian::query()
            ->when(Schema::hasColumn('ujians', 'exam_mode_id') || Schema::hasColumn('ujians', 'sub_category_id'), fn ($q) => $q->with(['examMode', 'subCategory']))
            ->where('is_selesai', true)
            ->where('paket', $paket)
            ->when($examModeId > 0 && Schema::hasColumn('ujians', 'exam_mode_id'), fn ($q) => $q->where('exam_mode_id', $examModeId))
            ->when($subCategoryId > 0 && Schema::hasColumn('ujians', 'sub_category_id'), fn ($q) => $q->where('sub_category_id', $subCategoryId))
            ->when($search !== '', fn ($q) => $q->where('nama_peserta', 'like', '%' . $search . '%'))
            ->selectRaw('*, ' . ExamRankingSupport::totalScoreSelectExpression())
            ->orderByDesc('total_skor')
            ->orderByDesc('skor_tkp')
            ->orderByDesc('skor_tiu')
            ->take(50)
            ->get();

        $defaultMode = ExamStructureSupport::defaultMode();
        $modeStats = $examModes->map(function ($mode) use ($rankings, $defaultMode) {
            $count = $rankings->filter(function ($row) use ($mode, $defaultMode) {
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
        })->filter(fn ($item) => $item->count > 0)->values();

        return view('ujian.leaderboard', compact('search', 'paket', 'examModeId', 'subCategoryId', 'examModes', 'selectedMode', 'subCategories', 'rankings', 'modeStats'));
    }
}










