<?php

namespace App\Support;

use App\Models\Bookmark;
use App\Models\ExamMode;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Models\Ujian;
use App\Models\User;
use App\Models\UserSoalHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExamDashboardSupport
{
    public static function startExamData($user): array
    {
        $examModes = ExamStructureSupport::modes();
        $preferredExamModeIds = method_exists($user, 'preferredExamModeIds') ? $user->preferredExamModeIds() : [];
        $preferredFormasiNames = method_exists($user, 'preferredFormasiNames') ? $user->preferredFormasiNames() : [];

        if (!empty($preferredExamModeIds)) {
            $examModes = collect($examModes)
                ->filter(fn ($mode) => in_array((int) ($mode->id ?? 0), $preferredExamModeIds, true))
                ->values();
        }

        $defaultExamMode = !empty($preferredExamModeIds)
            ? (collect($examModes)->firstWhere('id', $preferredExamModeIds[0]) ?: ExamStructureSupport::defaultMode())
            : ExamStructureSupport::defaultMode();

        $defaultSubCategories = $defaultExamMode
            ? ExamStructureSupport::subCategories((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''), 'formasi')
            : collect();

        if (!empty($preferredFormasiNames)) {
            $defaultSubCategories = collect($defaultSubCategories)
                ->filter(fn ($sub) => in_array(trim((string) ($sub->name ?? '')), $preferredFormasiNames, true))
                ->values();
        }

        $defaultModePackages = $defaultExamMode
            ? ExamStructureSupport::packages((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''))
            : collect();

        return compact(
            'examModes',
            'defaultExamMode',
            'defaultSubCategories',
            'defaultModePackages'
        );
    }

    public static function dashboardData($user): array
    {
        $withExamMeta = Schema::hasColumn('ujians', 'exam_mode_id') || Schema::hasColumn('ujians', 'sub_category_id');

        $progresUjian = Ujian::when($withExamMeta, fn ($q) => $q->with(['examMode', 'subCategory']))
            ->where('user_id', $user->id)
            ->where('is_selesai', false)
            ->orderBy('updated_at', 'desc')
            ->get();

        $ujianSelesai = Ujian::where('user_id', $user->id)
            ->where('is_selesai', true)
            ->selectRaw('*, ' . ExamRankingSupport::totalScoreSelectExpression())
            ->get();

        $stats = [
            'total_ujian' => $ujianSelesai->count(),
            'skor_tertinggi' => $ujianSelesai->max('total_skor') ?? 0,
            'rata_rata' => $ujianSelesai->isEmpty() ? 0 : round($ujianSelesai->avg('total_skor')),
            'bookmark_count' => Schema::hasTable('bookmarks')
                ? Bookmark::query()->where('user_id', $user->id)->count()
                : 0,
            'total_soal_selesai' => $ujianSelesai->sum(function ($u) {
                $jawaban = is_array($u->jawaban_user) ? $u->jawaban_user : json_decode($u->jawaban_user, true) ?? [];
                return count(array_filter($jawaban));
            }),
        ];

        $recentActivity = Ujian::when($withExamMeta, fn ($q) => $q->with(['examMode', 'subCategory']))
            ->where('user_id', $user->id)
            ->where('is_selesai', true)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($u) {
                $summary = is_array($u->result_summary ?? null) ? $u->result_summary : [];
                if (array_key_exists('is_pass', $summary)) {
                    $u->is_lulus = (bool) $summary['is_pass'];
                } else {
                    $u->is_lulus = ($u->skor_twk >= 65 && $u->skor_tiu >= 80 && $u->skor_tkp >= 166);
                }
                $u->total_skor = (int) ($u->total_score ?? ($u->skor_twk + $u->skor_tiu + $u->skor_tkp));
                return $u;
            });

        $totalsByKat = Cache::remember('bank_soal_sig_totals', 300, function () {
            $sets = ['TWK' => [], 'TIU' => [], 'TKP' => []];
            Soal::query()
                ->select(['id', 'kategori', 'teks_soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban_benar', 'bobot_tkp', 'hash_soal'])
                ->whereIn('kategori', ['TWK', 'TIU', 'TKP'])
                ->chunk(500, function ($rows) use (&$sets) {
                    foreach ($rows as $s) {
                        $kat = (string) $s->kategori;
                        if (!isset($sets[$kat])) {
                            continue;
                        }
                        $sig = !empty($s->hash_soal) ? (string) $s->hash_soal : sha1(implode('|', [
                            self::normalizeSoalText($s->teks_soal),
                            self::normalizeSoalText($s->opsi_a),
                            self::normalizeSoalText($s->opsi_b),
                            self::normalizeSoalText($s->opsi_c),
                            self::normalizeSoalText($s->opsi_d),
                            self::normalizeSoalText($s->opsi_e),
                            ((string) $kat === 'TKP')
                                ? json_encode($s->bobot_tkp ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                : self::normalizeSoalText($s->jawaban_benar),
                        ]));
                        $sets[$kat][$sig] = true;
                    }
                });

            return [
                'TWK' => count($sets['TWK']),
                'TIU' => count($sets['TIU']),
                'TKP' => count($sets['TKP']),
            ];
        });

        $usedByKat = UserSoalHistory::query()
            ->where('user_id', $user->id)
            ->select('kategori', DB::raw('COUNT(DISTINCT signature) as used'))
            ->groupBy('kategori')
            ->pluck('used', 'kategori')
            ->toArray();

        $bankSoalStats = [
            'TWK' => ['total' => (int) ($totalsByKat['TWK'] ?? 0), 'used' => (int) ($usedByKat['TWK'] ?? 0), 'fresh' => max(0, (int) ($totalsByKat['TWK'] ?? 0) - (int) ($usedByKat['TWK'] ?? 0)), 'need' => 30],
            'TIU' => ['total' => (int) ($totalsByKat['TIU'] ?? 0), 'used' => (int) ($usedByKat['TIU'] ?? 0), 'fresh' => max(0, (int) ($totalsByKat['TIU'] ?? 0) - (int) ($usedByKat['TIU'] ?? 0)), 'need' => 35],
            'TKP' => ['total' => (int) ($totalsByKat['TKP'] ?? 0), 'used' => (int) ($usedByKat['TKP'] ?? 0), 'fresh' => max(0, (int) ($totalsByKat['TKP'] ?? 0) - (int) ($usedByKat['TKP'] ?? 0)), 'need' => 45],
        ];

        ['examModes' => $examModes, 'defaultExamMode' => $defaultExamMode, 'defaultSubCategories' => $defaultSubCategories, 'defaultModePackages' => $defaultModePackages] = self::startExamData($user);
        $dynamicExamReady = ExamStructureSupport::schemaReady();
        $defaultCategories = ExamStructureSupport::categoryOptions((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''));
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $availableSubTopik = [];
        foreach ($defaultCategories as $category) {
            $kategori = strtoupper(trim((string) ($category->value ?? '')));
            if ($kategori === '' || $kategori === 'FIGURAL') {
                continue;
            }
            $availableSubTopik[$kategori] = Soal::query()
                ->where('kategori', $kategori)
                ->forExamMode($defaultExamMode?->id, $defaultSkdId)
                ->when(Schema::hasColumn('soals', 'standar_tahun'), fn ($q) => $q->where('standar_tahun', '2027'))
                ->whereNotNull('sub_topik')
                ->distinct()
                ->orderBy('sub_topik')
                ->pluck('sub_topik')
                ->map(fn ($row) => trim((string) $row))
                ->filter()
                ->values()
                ->all();
        }

        $questionHubStats = QuestionStatsSupport::dashboardSnapshot()['questionHubStats'];

        $structureStats = [
            'mode_count' => collect($examModes)->count(),
            'sub_category_count' => Schema::hasTable('sub_categories') ? SubCategory::query()->count() : collect($defaultSubCategories)->count(),
            'package_count' => collect($defaultModePackages)->count(),
            'dynamic_ready' => $dynamicExamReady,
        ];

        $masterAdminAlerts = ['pending_users' => 0];
        if (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() && Schema::hasColumn('users', 'is_active')) {
            $masterAdminAlerts['pending_users'] = User::query()
                ->where('is_active', false)
                ->when(Schema::hasColumn('users', 'activated_at'), fn ($q) => $q->whereNull('activated_at'))
                ->count();
        }

        return compact(
            'progresUjian',
            'stats',
            'recentActivity',
            'bankSoalStats',
            'availableSubTopik',
            'examModes',
            'defaultExamMode',
            'defaultSubCategories',
            'defaultModePackages',
            'dynamicExamReady',
            'questionHubStats',
            'structureStats',
            'masterAdminAlerts'
        );
    }

    private static function normalizeSoalText($value): string
    {
        $s = (string) ($value ?? '');
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
        return preg_replace('/\s+/u', ' ', trim($s)) ?? '';
    }
}
