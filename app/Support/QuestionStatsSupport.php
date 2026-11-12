<?php

namespace App\Support;

use App\Models\ExamMode;
use App\Models\QuestionAudit;
use App\Models\Soal;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Schema;

class QuestionStatsSupport
{
    public static function dashboardSnapshot(): array
    {
        $totalUnique = self::totalUnique();
        $categoryCounts = self::categoryCounts();
        $modeCounts = self::modeCounts();
        $auditStatusCounts = self::auditStatusCounts();
        $figuralCount = self::figuralCount();
        $uncategorizedCount = self::uncategorizedCount();
        $farmasiCount = self::farmasiCount();

        return [
            'stats' => [
                'TWK' => (int) ($categoryCounts['TWK'] ?? 0),
                'TIU' => (int) ($categoryCounts['TIU'] ?? 0),
                'TKP' => (int) ($categoryCounts['TKP'] ?? 0),
                'SKB' => (int) ($modeCounts['by_code']['skb'] ?? 0),
                'FIGURAL' => (int) $figuralCount,
                'total_unique' => (int) $totalUnique,
            ],
            'modeQuestionStats' => [
                'total' => (int) $totalUnique,
                'skd' => (int) ($modeCounts['by_code']['skd'] ?? 0),
                'skb' => (int) ($modeCounts['by_code']['skb'] ?? 0),
                'pppk' => (int) ($modeCounts['by_code']['pppk'] ?? 0),
                'farmasi' => (int) $farmasiCount,
                'figural' => (int) $figuralCount,
                'unlabeled' => (int) $uncategorizedCount,
                'dynamic' => $modeCounts['by_code'],
            ],
            'questionHubStats' => [
                'total' => (int) $totalUnique,
                'skd' => (int) ($modeCounts['by_code']['skd'] ?? 0),
                'skb' => (int) ($modeCounts['by_code']['skb'] ?? 0),
                'pppk' => (int) ($modeCounts['by_code']['pppk'] ?? 0),
                'farmasi' => (int) $farmasiCount,
                'figural' => (int) $figuralCount,
                'unlabeled' => (int) $uncategorizedCount,
            ],
            'auditStatusStats' => $auditStatusCounts,
            'categoryCounts' => $categoryCounts,
            'modeCounts' => $modeCounts,
            'uncategorizedCount' => (int) $uncategorizedCount,
            'totalUnique' => (int) $totalUnique,
        ];
    }

    public static function totalUnique(): int
    {
        return (int) Soal::query()->distinct()->count('id');
    }

    public static function categoryCounts(): array
    {
        return Soal::query()
            ->selectRaw("UPPER(TRIM(COALESCE(kategori, 'UNSPECIFIED'))) as kategori_key")
            ->selectRaw('COUNT(DISTINCT id) as total')
            ->groupBy('kategori_key')
            ->get()
            ->reduce(function (array $carry, $row) {
                $key = trim((string) ($row->kategori_key ?? ''));
                $key = $key !== '' ? $key : 'UNSPECIFIED';
                $carry[$key] = (int) ($row->total ?? 0);
                return $carry;
            }, []);
    }

    public static function modeCounts(): array
    {
        $modes = ExamStructureSupport::modes();
        $byCode = [];
        $byLabel = [];

        if (Schema::hasColumn('soals', 'exam_mode_id')) {
            $rows = Soal::query()
                ->selectRaw('COALESCE(exam_mode_id, 0) as exam_mode_key')
                ->selectRaw('COUNT(DISTINCT id) as total')
                ->groupBy('exam_mode_key')
                ->get()
                ->pluck('total', 'exam_mode_key');

            $nullBucket = (int) ($rows[0] ?? 0);
            foreach ($modes as $mode) {
                $modeId = (int) ($mode->id ?? 0);
                $code = strtolower(trim((string) ($mode->code ?? '')));
                $label = (string) ($mode->name ?? strtoupper($code));
                if ($code === '') {
                    continue;
                }

                $total = (int) ($rows[$modeId] ?? 0);
                if ($code === 'skd') {
                    $total += $nullBucket;
                }

                $byCode[$code] = $total;
                $byLabel[$label] = $total;
            }
        } else {
            foreach ($modes as $mode) {
                $code = strtolower(trim((string) ($mode->code ?? '')));
                $label = (string) ($mode->name ?? strtoupper($code));
                if ($code === '') {
                    continue;
                }

                $total = $code === 'skd' ? self::totalUnique() : 0;
                $byCode[$code] = $total;
                $byLabel[$label] = $total;
            }
        }

        return [
            'by_code' => $byCode,
            'by_label' => $byLabel,
        ];
    }

    public static function auditStatusCounts(): array
    {
        $counts = [
            'pending' => 0,
            'audited' => 0,
            'revised' => 0,
            'rejected' => 0,
            'needs_reaudit' => 0,
            'flagged' => 0,
        ];

        if (Schema::hasColumn('soals', 'audit_status')) {
            $grouped = Soal::query()
                ->selectRaw("LOWER(COALESCE(NULLIF(TRIM(audit_status), ''), 'pending')) as audit_status_key")
                ->selectRaw('COUNT(DISTINCT id) as total')
                ->groupBy('audit_status_key')
                ->get();

            foreach ($grouped as $row) {
                $key = (string) ($row->audit_status_key ?? 'pending');
                $counts[$key] = (int) ($row->total ?? 0);
            }
        } else {
            $counts['pending'] = self::totalUnique();
        }

        if (Schema::hasTable('question_audits')) {
            $flaggedQuestionCount = (int) QuestionAudit::query()
                ->whereIn('status', ['flagged', 'rejected', 'wrong', 'suspicious'])
                ->whereNotNull('question_id')
                ->distinct()
                ->count('question_id');

            $counts['flagged'] = max((int) ($counts['flagged'] ?? 0), $flaggedQuestionCount);
        }

        return $counts;
    }

    public static function uncategorizedCount(): int
    {
        return (int) Soal::query()
            ->where(function ($query) {
                $query->whereNull('sub_topik')
                    ->orWhere('sub_topik', '');
            })
            ->distinct()
            ->count('id');
    }

    public static function figuralCount(): int
    {
        return (int) Soal::query()
            ->where(function ($query) {
                $query->where('kategori', 'FIGURAL');
                if (Schema::hasColumn('soals', 'prompt_gambar_soal')) {
                    $query->orWhereNotNull('prompt_gambar_soal');
                }
                if (Schema::hasColumn('soals', 'gambar_path')) {
                    $query->orWhereNotNull('gambar_path');
                }
            })
            ->distinct()
            ->count('id');
    }

    public static function farmasiCount(): int
    {
        if (Schema::hasColumn('soals', 'sub_category_id') && Schema::hasTable('sub_categories')) {
            $farmasiSubIds = SubCategory::query()
                ->where('name', 'like', '%farmasi%')
                ->orWhere('name', 'like', '%apoteker%')
                ->pluck('id')
                ->all();

            if (!empty($farmasiSubIds)) {
                return (int) Soal::query()
                    ->whereIn('sub_category_id', $farmasiSubIds)
                    ->distinct()
                    ->count('id');
            }
        }

        return (int) Soal::query()
            ->where(function ($query) {
                $query->where('sub_topik', 'like', '%farmasi%')
                    ->orWhere('teks_soal', 'like', '%farmasi%');
            })
            ->distinct()
            ->count('id');
    }
}
