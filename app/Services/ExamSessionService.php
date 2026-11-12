<?php

namespace App\Services;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Models\Ujian;
use App\Models\User;
use App\Models\UserSoalHistory;
use App\Support\ExamStructureSupport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExamSessionService
{
    private function requestedKategoriCandidates(string $kategori): array
    {
        $kategori = strtoupper(trim((string) $kategori));
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

    private function buildQuestionRuntimeMap(Collection $soals): array
    {
        return $soals->mapWithKeys(function ($row) {
            return [
                (string) $row->id => strtoupper(trim((string) ($row->requested_kategori ?? $row->kategori ?? ''))),
            ];
        })->all();
    }

    private function resolveRequestedKategoriForStored(string $storedKategori, array $sectionKeys = [], ?string $runtimeKategori = null): string
    {
        $runtimeKategori = strtoupper(trim((string) ($runtimeKategori ?? '')));
        if ($runtimeKategori !== '' && in_array($runtimeKategori, $sectionKeys, true)) {
            return $runtimeKategori;
        }

        $storedKategori = strtoupper(trim((string) $storedKategori));
        if (in_array($storedKategori, $sectionKeys, true)) {
            return $storedKategori;
        }

        foreach ($sectionKeys as $sectionKey) {
            if (in_array($storedKategori, $this->requestedKategoriCandidates($sectionKey), true)) {
                return $sectionKey;
            }
        }

        return $storedKategori;
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

    private function getUserUsedSoalSets(int $userId): array
    {
        $rows = UserSoalHistory::query()
            ->where('user_id', $userId)
            ->select(['soal_id', 'signature'])
            ->get();

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

    private function touchUserSoalHistory(int $userId, int $ujianId, Collection $soals): void
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

        DB::table('user_soal_histories')->upsert(
            $rows,
            ['user_id', 'signature'],
            ['soal_id', 'kategori', 'ujian_id', 'used_at', 'updated_at']
        );
    }

    private function pickUniqueRandom(
        string $kategori,
        int $targetCount,
        array &$usedSignatures,
        array $soalTerpakaiUserIds = [],
        array $soalTerpakaiUserSignatures = [],
        string $subTopikFilter = '',
        ?int $examModeId = null,
        ?int $subCategoryId = null,
        ?int $defaultSkdId = null,
        ?string $difficulty = null
    ): Collection {
        $picked = collect();
        $attempt = 0;

        while ($picked->count() < $targetCount && $attempt < 12) {
            $need = $targetCount - $picked->count();
            $take = min(max($need * 4, $need + 8), 220);
            $batch = collect();
            $freshIds = [];

            if (!empty($soalTerpakaiUserIds)) {
                $unusedQuery = $this->applyRequestedKategoriFilter(
                    Soal::siapUjian(),
                    $kategori
                )
                    ->whereNotIn('id', $soalTerpakaiUserIds)
                    ->forExamMode($examModeId, $defaultSkdId)
                    ->forSubCategory($subCategoryId);
                if ($subTopikFilter !== '') $unusedQuery->where('sub_topik', $subTopikFilter);
                if ($difficulty) $unusedQuery->where('difficulty', $difficulty);
                if (Schema::hasColumn('soals', 'standar_tahun')) {
                    $unusedQuery->orderByRaw("CASE WHEN standar_tahun = '2027' THEN 0 ELSE 1 END");
                }
                $batch = $unusedQuery->inRandomOrder()->take($take)->get();
                $freshIds = $batch->pluck('id')->map(fn ($id) => (int) $id)->all();
            }

            if ($batch->count() < $take && !empty($soalTerpakaiUserIds)) {
                $moreTake = $take - $batch->count();
                $oldestIds = UserSoalHistory::query()
                    ->where('user_id', auth()->id())
                    ->where('kategori', $kategori)
                    ->orderBy('used_at', 'asc')
                    ->limit($moreTake * 3)
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
                    if ($difficulty) $usedBatch->where('difficulty', $difficulty);
                    if (Schema::hasColumn('soals', 'standar_tahun')) {
                        $usedBatch->orderByRaw("CASE WHEN standar_tahun = '2027' THEN 0 ELSE 1 END");
                    }
                    $batch = $batch->concat($usedBatch->get());
                }
            }

            if (empty($soalTerpakaiUserIds)) {
                $fb = $this->applyRequestedKategoriFilter(
                    Soal::siapUjian(),
                    $kategori
                )
                    ->forExamMode($examModeId, $defaultSkdId)
                    ->forSubCategory($subCategoryId);
                if ($subTopikFilter !== '') $fb->where('sub_topik', $subTopikFilter);
                if ($difficulty) $fb->where('difficulty', $difficulty);
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

        return $picked;
    }

    private function resolvePackageConfig(?ExamMode $examMode = null, ?string $paket = null): ?object
    {
        $mode = $examMode ?: ExamStructureSupport::defaultMode();
        return ExamStructureSupport::packageConfig($mode?->id, $mode?->code ?? 'skd', $paket);
    }

    private function durationSecondsFromConfig(?object $packageConfig, ?string $paket = null): int
    {
        $minutes = (int) ($packageConfig->duration_minutes ?? 0);
        if ($minutes > 0) {
            return $minutes * 60;
        }
        return strtoupper(trim((string) ($paket ?? ''))) === 'D' ? 6000 : 1800;
    }

    private function calculateQuestionScore(Soal $soal, string $selectedAnswer, array $sectionConfig): array
    {
        $scoreType = (string) ($sectionConfig['score_type'] ?? 'objective');
        $selected = strtoupper(trim($selectedAnswer));
        $score = 0;
        $isPositive = false;

        if ($scoreType === 'weighted_option' || $scoreType === 'weighted_scale') {
            if (is_array($soal->bobot_tkp) && isset($soal->bobot_tkp[$selected])) {
                $score = (int) $soal->bobot_tkp[$selected];
            } else {
                $min = (int) ($sectionConfig['min_option_score'] ?? 1);
                $max = (int) ($sectionConfig['max_option_score'] ?? ($scoreType === 'weighted_option' ? 5 : 4));
                $map = ['A' => $max, 'B' => max($min, $max - 1), 'C' => max($min, $max - 2), 'D' => $min + 1, 'E' => $min];
                $score = (int) ($map[$selected] ?? 0);
            }
            $isPositive = $score >= max(1, ((int) ($sectionConfig['max_option_score'] ?? 4) - 1));
            return ['score' => $score, 'is_positive' => $isPositive];
        }

        $kunci = $this->normalizeJawabanKey($soal->jawaban_benar);
        if ($kunci !== '' && $kunci === $selected) {
            $score = (int) ($sectionConfig['correct_score'] ?? 5);
            $isPositive = true;
        } else {
            $score = (int) ($sectionConfig['wrong_score'] ?? 0);
        }

        return ['score' => $score, 'is_positive' => $isPositive];
    }

    private function evaluatePassingRules(array $breakdown, array $passingRules): array
    {
        $logic = (string) ($passingRules['logic'] ?? 'rank_only');
        $passed = true;
        $messages = [];

        foreach ((array) ($passingRules['section_min'] ?? []) as $sectionKey => $min) {
            $score = (int) ($breakdown[$sectionKey]['score'] ?? 0);
            $min = (int) $min;
            if ($min > 0 && $score < $min) {
                $passed = false;
                $messages[] = $sectionKey . ' belum mencapai ambang ' . $min;
            }
        }

        foreach ((array) ($passingRules['group_min'] ?? []) as $group) {
            $sections = (array) ($group['sections'] ?? []);
            $label = (string) ($group['label'] ?? implode(' + ', $sections));
            $min = (int) ($group['min'] ?? 0);
            $score = 0;
            foreach ($sections as $sectionKey) {
                $score += (int) ($breakdown[$sectionKey]['score'] ?? 0);
            }
            if ($min > 0 && $score < $min) {
                $passed = false;
                $messages[] = $label . ' belum mencapai ambang ' . $min;
            }
        }

        if ($logic === 'rank_only') {
            $passed = true;
            $messages = [];
        }

        return [
            'is_pass' => $passed,
            'messages' => $messages,
            'logic' => $logic,
            'notes' => (string) ($passingRules['notes'] ?? ''),
        ];
    }

    public function inferPaket(?SubCategory $subCategory = null, ?ExamMode $examMode = null, ?string $paket = null): string
    {
        $candidate = strtoupper(trim((string) ($paket ?? '')));
        $packages = ExamStructureSupport::packages($examMode?->id, $examMode?->code ?? 'skd');
        if ($candidate !== '' && $packages->firstWhere('code', $candidate)) {
            return $candidate;
        }

        $name = strtoupper(trim((string) ($subCategory?->name ?? '')));
        if ($name === 'TWK') return 'A';
        if ($name === 'TIU') return 'B';
        if ($name === 'TKP') return 'C';

        return (string) (($packages->firstWhere('code', 'D')->code ?? null) ?: ($packages->first()->code ?? 'D'));
    }

    public function createExamForUser(
        User $user,
        string $paket,
        string $namaPeserta,
        string $subTopikFilter = '',
        ?ExamMode $examMode = null,
        ?SubCategory $subCategory = null,
        ?string $difficulty = null
    ): array {
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $used = [];
        $soals = collect();
        $packageConfig = $this->resolvePackageConfig($examMode, $paket);
        $sections = array_values(array_filter((array) ($packageConfig->sections ?? []), function ($section) {
            return !empty($section['kategori']) && (int) ($section['count'] ?? 0) > 0;
        }));

        $sets = $this->getUserUsedSoalSets((int) $user->id);
        $usedIds = $sets['ids'] ?? [];
        $usedSigs = $sets['sigs'] ?? [];

        $singleSection = count($sections) === 1;
        foreach ($sections as $section) {
            $kategori = strtoupper(trim((string) ($section['kategori'] ?? '')));
            $count = (int) ($section['count'] ?? 0);
            if ($kategori === '' || $count <= 0) continue;
            $sectionTopik = $singleSection ? $subTopikFilter : '';
            $soals = $soals->concat($this->pickUniqueRandom($kategori, $count, $used, $usedIds, $usedSigs, $sectionTopik, $examMode?->id, $subCategory?->id, $defaultSkdId, $difficulty));
        }

        $soals = $soals->map(function ($row) {
            $row->setAttribute('requested_kategori', strtoupper(trim((string) ($row->requested_kategori ?? $row->kategori ?? ''))));
            return $row;
        })->values();

        if ($soals->isEmpty()) {
            throw new \RuntimeException('Belum ada butir soal berstandar resmi CASN 2027 yang siap untuk kategori/mode ini. Silakan generate soal standar 2027 terlebih dahulu di menu Generator Soal.');
        }

        $jawabanUser = $soals->pluck('id')->unique()->mapWithKeys(fn ($id) => [$id => null])->toArray();
        $payload = [
            'user_id' => $user->id,
            'nama_peserta' => $namaPeserta,
            'paket' => $paket,
            'is_selesai' => false,
            'sisa_waktu' => $this->durationSecondsFromConfig($packageConfig, $paket),
            'jawaban_user' => $jawabanUser,
            'skor_twk' => 0,
            'skor_tiu' => 0,
            'skor_tkp' => 0,
            'total_score' => 0,
        ];

        if (Schema::hasColumn('ujians', 'sub_topik_filter')) {
            $payload['sub_topik_filter'] = $subTopikFilter !== '' ? $subTopikFilter : null;
        }
        if (Schema::hasColumn('ujians', 'exam_mode_id')) {
            $payload['exam_mode_id'] = $examMode?->id;
        }
        if (Schema::hasColumn('ujians', 'sub_category_id')) {
            $payload['sub_category_id'] = $subCategory?->id;
        }
        if (Schema::hasColumn('ujians', 'exam_mode_package_id')) {
            $payload['exam_mode_package_id'] = $packageConfig->id ?? null;
        }
        if (Schema::hasColumn('ujians', 'duration_minutes')) {
            $payload['duration_minutes'] = (int) ($packageConfig->duration_minutes ?? 0) ?: null;
        }
        if (Schema::hasColumn('ujians', 'package_snapshot')) {
            $payload['package_snapshot'] = [
                'code' => $packageConfig->code ?? $paket,
                'name' => $packageConfig->name ?? $paket,
                'description' => $packageConfig->description ?? null,
                'duration_minutes' => $packageConfig->duration_minutes ?? null,
                'total_questions' => $packageConfig->total_questions ?? null,
                'sections' => $packageConfig->sections ?? [],
                'passing_rules' => $packageConfig->passing_rules ?? [],
                'score_rules' => $packageConfig->score_rules ?? [],
                'question_runtime_map' => $this->buildQuestionRuntimeMap($soals),
            ];
        }

        $ujian = Ujian::create($payload);
        $this->touchUserSoalHistory((int) $user->id, (int) $ujian->id, $soals);

        return [$ujian, $soals->values()];
    }

    public function scoreExam(Ujian $ujian): array
    {
        $jawaban = $ujian->jawaban_user ?? [];
        $soalIds = array_keys($jawaban);
        $allSoals = Soal::whereIn('id', $soalIds)->get()->keyBy('id');
        $analitik = [];
        $answers = [];

        $examMode = Schema::hasColumn('ujians', 'exam_mode_id') && $ujian->exam_mode_id ? ExamMode::find($ujian->exam_mode_id) : ExamMode::defaultSkd();
        $snapshot = is_array($ujian->package_snapshot) ? $ujian->package_snapshot : [];
        $packageConfig = !empty($snapshot)
            ? (object) $snapshot
            : $this->resolvePackageConfig($examMode, $ujian->paket);
        $sectionConfigs = [];
        foreach ((array) ($packageConfig->sections ?? []) as $section) {
            $sectionConfigs[strtoupper((string) ($section['kategori'] ?? ''))] = $section;
        }
        $sectionKeys = array_keys($sectionConfigs);
        $runtimeMap = is_array($snapshot['question_runtime_map'] ?? null) ? $snapshot['question_runtime_map'] : [];

        $scoreBreakdown = [];
        foreach ($sectionConfigs as $kategori => $section) {
            $scoreBreakdown[$kategori] = [
                'label' => (string) ($section['label'] ?? $kategori),
                'score' => 0,
                'count' => 0,
                'pass_min' => $section['pass_min'] ?? null,
            ];
        }

        $twk = 0;
        $tiu = 0;
        $tkp = 0;

        foreach ($jawaban as $soalId => $pil) {
            if (!$allSoals->has($soalId) || is_null($pil)) continue;

            $s = $allSoals->get($soalId);
            $storedKategori = strtoupper((string) $s->kategori);
            $runtimeKategori = strtoupper((string) ($runtimeMap[(string) $s->id] ?? ''));
            $kategori = $this->resolveRequestedKategoriForStored($storedKategori, $sectionKeys, $runtimeKategori);
            $sectionConfig = $sectionConfigs[$kategori] ?? ['label' => $kategori, 'score_type' => ($storedKategori === 'TKP' ? 'weighted_option' : 'objective')];
            $result = $this->calculateQuestionScore($s, (string) $pil, $sectionConfig);
            $scorePoin = (int) ($result['score'] ?? 0);
            $isBenar = (bool) ($result['is_positive'] ?? false);

            if (!isset($scoreBreakdown[$kategori])) {
                $scoreBreakdown[$kategori] = [
                    'label' => (string) ($sectionConfig['label'] ?? $kategori),
                    'score' => 0,
                    'count' => 0,
                    'pass_min' => $sectionConfig['pass_min'] ?? null,
                ];
            }
            $scoreBreakdown[$kategori]['score'] += $scorePoin;
            $scoreBreakdown[$kategori]['count']++;

            if ($kategori === 'TWK') $twk += $scorePoin;
            if ($kategori === 'TIU') $tiu += $scorePoin;
            if ($kategori === 'TKP') $tkp += $scorePoin;

            $topik = $s->sub_topik ?: 'Lainnya';
            if (!isset($analitik[$kategori])) $analitik[$kategori] = [];
            if (!isset($analitik[$kategori][$topik])) {
                $analitik[$kategori][$topik] = ['benar' => 0, 'total' => 0];
            }
            $analitik[$kategori][$topik]['total']++;
            if ($isBenar) $analitik[$kategori][$topik]['benar']++;

            $answers[] = [
                'question_id' => (int) $s->id,
                'selected_answer' => strtoupper((string) $pil),
                'correct_answer' => $this->normalizeJawabanKey($s->jawaban_benar),
                'is_answered' => !is_null($pil) && trim((string) $pil) !== '',
                'is_correct' => $isBenar,
                'score' => $scorePoin,
                'kategori' => $kategori,
                'sub_topik' => $topik,
            ];
        }

        $analitikFinal = [];
        foreach ($analitik as $kat => $topiks) {
            $analitikFinal[$kat] = [];
            foreach ($topiks as $t => $data) {
                $analitikFinal[$kat][$t] = $data['total'] > 0 ? round(($data['benar'] / $data['total']) * 100) : 0;
            }
        }

        $passingResult = $this->evaluatePassingRules($scoreBreakdown, (array) ($packageConfig->passing_rules ?? []));
        $totalScore = array_sum(array_map(fn ($row) => (int) ($row['score'] ?? 0), $scoreBreakdown));

        return [
            'skor_twk' => $twk,
            'skor_tiu' => $tiu,
            'skor_tkp' => $tkp,
            'analitik_topik' => $analitikFinal,
            'score_breakdown' => $scoreBreakdown,
            'result_summary' => [
                'is_pass' => (bool) ($passingResult['is_pass'] ?? false),
                'messages' => $passingResult['messages'] ?? [],
                'logic' => $passingResult['logic'] ?? 'rank_only',
                'notes' => $passingResult['notes'] ?? '',
                'package_name' => $packageConfig->name ?? $ujian->paket,
                'package_code' => $packageConfig->code ?? $ujian->paket,
            ],
            'package_snapshot' => [
                'code' => $packageConfig->code ?? $ujian->paket,
                'name' => $packageConfig->name ?? $ujian->paket,
                'description' => $packageConfig->description ?? null,
                'duration_minutes' => $packageConfig->duration_minutes ?? null,
                'total_questions' => $packageConfig->total_questions ?? null,
                'sections' => $packageConfig->sections ?? [],
                'passing_rules' => $packageConfig->passing_rules ?? [],
                'score_rules' => $packageConfig->score_rules ?? [],
            ],
            'waktu_total_detik' => $this->durationSecondsFromConfig($packageConfig, $ujian->paket),
            'total_skor' => $totalScore,
            'answers' => $answers,
        ];
    }
}

