<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class ExamRankingSupport
{
    public static function totalScoreSelectExpression(): string
    {
        return Schema::hasColumn('ujians', 'total_score')
            ? 'COALESCE(total_score, (skor_twk + skor_tiu + skor_tkp)) as total_skor'
            : '(skor_twk + skor_tiu + skor_tkp) as total_skor';
    }

    public static function buildScoreMeta(object $row): array
    {
        $breakdown = is_array($row->score_breakdown ?? null) ? $row->score_breakdown : [];
        $summary = is_array($row->result_summary ?? null) ? $row->result_summary : [];
        $chips = [];

        if (!empty($breakdown)) {
            foreach ($breakdown as $sectionKey => $section) {
                $score = (int) ($section['score'] ?? 0);
                $label = (string) ($section['label'] ?? $sectionKey);
                $min = $section['pass_min'] ?? null;
                $chips[] = [
                    'label' => $label,
                    'score' => $score,
                    'state' => is_null($min) ? 'neutral' : ($score >= (int) $min ? 'pass' : 'fail'),
                ];
            }
        } else {
            $chips = [
                ['label' => 'TWK', 'score' => (int) ($row->skor_twk ?? 0), 'state' => ((int) ($row->skor_twk ?? 0) >= 65 ? 'pass' : 'fail')],
                ['label' => 'TIU', 'score' => (int) ($row->skor_tiu ?? 0), 'state' => ((int) ($row->skor_tiu ?? 0) >= 80 ? 'pass' : 'fail')],
                ['label' => 'TKP', 'score' => (int) ($row->skor_tkp ?? 0), 'state' => ((int) ($row->skor_tkp ?? 0) >= 166 ? 'pass' : 'fail')],
            ];
        }

        $passed = array_key_exists('passed', $summary)
            ? (bool) $summary['passed']
            : (((int) ($row->skor_twk ?? 0) >= 65) && ((int) ($row->skor_tiu ?? 0) >= 80) && ((int) ($row->skor_tkp ?? 0) >= 166));

        return ['chips' => $chips, 'passed' => $passed, 'status' => $passed ? 'LULUS' : 'BELUM'];
    }

    public static function modeName(object $row): string
    {
        return (Schema::hasColumn('ujians', 'exam_mode_id') && isset($row->examMode) && $row->examMode)
            ? (string) $row->examMode->name
            : 'Mode default';
    }

    public static function subCategoryName(object $row): ?string
    {
        return (Schema::hasColumn('ujians', 'sub_category_id') && isset($row->subCategory) && $row->subCategory)
            ? (string) $row->subCategory->name
            : null;
    }

    public static function isFarmasiContext(?string $modeName, ?string $subCategoryName): bool
    {
        $haystack = strtolower(trim((string) $modeName . ' ' . (string) $subCategoryName));

        return str_contains($haystack, 'farmasi') || str_contains($haystack, 'apoteker');
    }
}
