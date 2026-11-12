<?php

namespace App\Services;

use App\Models\ExamAnswer;
use App\Models\ExamMode;
use App\Models\Ujian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LearningAnalysisService
{
    private const MIN_TOPIC_ATTEMPTS = 3;

    public function __construct(
        private readonly ExamSessionService $examSessionService
    ) {
    }

    public function dashboard(User $user): array
    {
        $this->ensureAnswerHistory($user);

        $exams = $this->completedExams($user);
        $answers = $this->answeredRows($user);

        $summary = $this->buildSummary($exams, $answers);
        $categories = $this->buildCategoryStats($answers);
        $history = $this->buildHistory($exams, $answers);
        $modes = $this->buildModeStats($exams, $answers);
        $recommendations = $this->buildRecommendations($categories, $history, $modes, $answers);

        return [
            'summary' => $summary,
            'categories' => $categories,
            'history' => $history,
            'modes' => $modes,
            'recommendations' => $recommendations,
        ];
    }

    public function persistExamAnswers(Ujian $ujian, array $answers): void
    {
        if (!Schema::hasTable('exam_answers')) {
            return;
        }

        $answeredAt = $ujian->updated_at ?: now();
        $rows = [];

        foreach ($answers as $answer) {
            $questionId = (int) ($answer['question_id'] ?? 0);
            if ($questionId <= 0) {
                continue;
            }

            $rows[] = [
                'exam_id' => (int) $ujian->id,
                'user_id' => (int) $ujian->user_id,
                'question_id' => $questionId,
                'exam_mode_id' => !empty($ujian->exam_mode_id) ? (int) $ujian->exam_mode_id : null,
                'sub_category_id' => !empty($ujian->sub_category_id) ? (int) $ujian->sub_category_id : null,
                'kategori' => (string) ($answer['kategori'] ?? ''),
                'sub_topik' => $this->cleanLabel($answer['sub_topik'] ?? null),
                'selected_answer' => $this->cleanLabel($answer['selected_answer'] ?? null),
                'correct_answer' => $this->cleanLabel($answer['correct_answer'] ?? null),
                'is_answered' => (bool) ($answer['is_answered'] ?? false),
                'is_correct' => (bool) ($answer['is_correct'] ?? false),
                'score' => (int) ($answer['score'] ?? 0),
                'answered_at' => $answeredAt,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($rows)) {
            return;
        }

        DB::table('exam_answers')->upsert(
            $rows,
            ['exam_id', 'question_id'],
            ['user_id', 'exam_mode_id', 'sub_category_id', 'kategori', 'sub_topik', 'selected_answer', 'correct_answer', 'is_answered', 'is_correct', 'score', 'answered_at', 'updated_at']
        );
    }

    private function ensureAnswerHistory(User $user): void
    {
        if (!Schema::hasTable('exam_answers')) {
            return;
        }

        $completedExams = Ujian::query()
            ->where('user_id', $user->id)
            ->where('is_selesai', true)
            ->orderBy('id')
            ->get();

        if ($completedExams->isEmpty()) {
            return;
        }

        $existingExamIds = ExamAnswer::query()
            ->where('user_id', $user->id)
            ->select('exam_id')
            ->distinct()
            ->pluck('exam_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existingMap = array_fill_keys($existingExamIds, true);

        foreach ($completedExams as $exam) {
            if (isset($existingMap[(int) $exam->id])) {
                continue;
            }

            $scored = $this->examSessionService->scoreExam($exam);
            $this->persistExamAnswers($exam, (array) ($scored['answers'] ?? []));
        }
    }

    private function completedExams(User $user): Collection
    {
        return Ujian::query()
            ->with(['examMode', 'subCategory'])
            ->where('user_id', $user->id)
            ->where('is_selesai', true)
            ->orderBy('created_at')
            ->get()
            ->map(function (Ujian $exam) {
                $exam->analysis_mode_label = $this->modeLabel($exam->examMode, $exam->exam_mode_id);
                $exam->analysis_mode_code = $this->modeCode($exam->examMode, $exam->exam_mode_id);
                $exam->analysis_score = (int) ($exam->total_score ?? (($exam->skor_twk ?? 0) + ($exam->skor_tiu ?? 0) + ($exam->skor_tkp ?? 0)));
                return $exam;
            });
    }

    private function answeredRows(User $user): Collection
    {
        if (!Schema::hasTable('exam_answers')) {
            return collect();
        }

        $defaultSkd = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd() : null;

        return ExamAnswer::query()
            ->where('user_id', $user->id)
            ->where('is_answered', true)
            ->with(['exam:id,created_at,paket,exam_mode_id,sub_category_id', 'examMode:id,name,code', 'subCategory:id,name'])
            ->orderBy('answered_at')
            ->get()
            ->map(function (ExamAnswer $row) use ($defaultSkd) {
                $row->analysis_mode_label = $this->modeLabel($row->examMode, $row->exam_mode_id, $defaultSkd);
                $row->analysis_mode_code = $this->modeCode($row->examMode, $row->exam_mode_id, $defaultSkd);
                $row->analysis_topic_label = $this->topicLabel($row->kategori, $row->sub_topik);
                $row->analysis_topic_key = mb_strtolower($row->analysis_topic_label);
                $row->analysis_answered_date = optional($row->answered_at ?: optional($row->exam)->created_at)->copy();
                return $row;
            });
    }

    private function buildSummary(Collection $exams, Collection $answers): array
    {
        $totalAnswered = $answers->count();
        $correct = $answers->where('is_correct', true)->count();
        $incorrect = max(0, $totalAnswered - $correct);
        $averageScore = $exams->isEmpty() ? 0 : round($exams->avg('analysis_score'), 1);

        return [
            'total_questions' => $totalAnswered,
            'total_correct' => $correct,
            'total_wrong' => $incorrect,
            'accuracy' => $this->percentage($correct, $totalAnswered),
            'average_score' => $averageScore,
            'total_sessions' => $exams->count(),
        ];
    }

    private function buildCategoryStats(Collection $answers): array
    {
        $rows = $answers
            ->groupBy('analysis_topic_key')
            ->map(function (Collection $group) {
                $first = $group->first();
                $total = $group->count();
                $correct = $group->where('is_correct', true)->count();
                $accuracy = $this->percentage($correct, $total);

                return [
                    'key' => (string) $first->analysis_topic_key,
                    'label' => (string) $first->analysis_topic_label,
                    'kategori' => (string) ($first->kategori ?? ''),
                    'sub_topik' => (string) ($first->sub_topik ?? ''),
                    'mode_label' => (string) ($first->analysis_mode_label ?? 'SKD'),
                    'total' => $total,
                    'correct' => $correct,
                    'wrong' => max(0, $total - $correct),
                    'accuracy' => $accuracy,
                    'status' => $this->statusForAccuracy($accuracy, $total),
                    'enough_data' => $total >= self::MIN_TOPIC_ATTEMPTS,
                ];
            })
            ->sortByDesc(fn ($row) => (($row['accuracy'] ?? 0) * 100000) + ($row['total'] ?? 0))
            ->values();

        return [
            'all' => $rows->all(),
            'strongest' => $rows
                ->filter(fn ($row) => $row['enough_data'])
                ->take(5)
                ->values()
                ->all(),
            'weakest' => $rows
                ->filter(fn ($row) => $row['enough_data'])
                ->sortBy(fn ($row) => (($row['accuracy'] ?? 0) * 100000) - ($row['total'] ?? 0))
                ->take(5)
                ->values()
                ->all(),
            'insufficient' => $rows
                ->filter(fn ($row) => !$row['enough_data'])
                ->take(5)
                ->values()
                ->all(),
        ];
    }

    private function buildHistory(Collection $exams, Collection $answers): array
    {
        $answersByExam = $answers->groupBy('exam_id');

        $examSeries = $exams->map(function (Ujian $exam) use ($answersByExam) {
            $group = $answersByExam->get((int) $exam->id, collect());
            $answered = $group->count();
            $correct = $group->where('is_correct', true)->count();
            $accuracy = $this->percentage($correct, $answered);

            return [
                'exam_id' => (int) $exam->id,
                'date' => optional($exam->created_at)->format('Y-m-d'),
                'label' => optional($exam->created_at)->format('d M'),
                'score' => (int) $exam->analysis_score,
                'accuracy' => $accuracy,
                'mode_label' => (string) $exam->analysis_mode_label,
                'paket' => (string) ($exam->paket ?? ''),
                'answered' => $answered,
                'correct' => $correct,
            ];
        })->values();

        $weekly = $examSeries
            ->groupBy(function ($row) {
                return Carbon::parse($row['date'])->startOfWeek()->format('Y-m-d');
            })
            ->map(function (Collection $group, string $weekStart) {
                return [
                    'week_start' => $weekStart,
                    'label' => Carbon::parse($weekStart)->translatedFormat('d M'),
                    'accuracy' => round($group->avg('accuracy'), 1),
                    'score' => round($group->avg('score'), 1),
                    'sessions' => $group->count(),
                ];
            })
            ->values();

        return [
            'timeline' => $examSeries->all(),
            'weekly' => $weekly->all(),
            'last_ten' => $examSeries->slice(-10)->values()->all(),
        ];
    }

    private function buildModeStats(Collection $exams, Collection $answers): array
    {
        $answersByMode = $answers->groupBy('analysis_mode_label');
        $examsByMode = $exams->groupBy('analysis_mode_label');

        return $examsByMode->map(function (Collection $modeExams, string $modeLabel) use ($answersByMode) {
            $modeAnswers = $answersByMode->get($modeLabel, collect());
            $answered = $modeAnswers->count();
            $correct = $modeAnswers->where('is_correct', true)->count();
            $topics = $this->buildCategoryStats($modeAnswers);

            return [
                'mode_label' => $modeLabel,
                'mode_code' => (string) ($modeAnswers->first()->analysis_mode_code ?? $modeExams->first()->analysis_mode_code ?? 'skd'),
                'accuracy' => $this->percentage($correct, $answered),
                'average_score' => round($modeExams->avg('analysis_score'), 1),
                'total_attempts' => $modeExams->count(),
                'total_questions' => $answered,
                'strongest_topic' => $topics['strongest'][0] ?? null,
                'weakest_topic' => $topics['weakest'][0] ?? null,
            ];
        })
        ->sortByDesc('total_attempts')
        ->values()
        ->all();
    }

    private function buildRecommendations(array $categories, array $history, array $modes, Collection $answers): array
    {
        $insights = [];
        $suggestions = [];

        $weakest = collect($categories['weakest'] ?? [])->take(3);
        foreach ($weakest as $row) {
            $suggestions[] = sprintf(
                'Fokus latihan %s karena akurasi masih %s%% dari %d soal.',
                $row['label'],
                number_format((float) $row['accuracy'], 1),
                (int) $row['total']
            );
        }

        $recentWeak = $answers
            ->sortByDesc(fn ($row) => optional($row->analysis_answered_date)->timestamp ?? 0)
            ->take(40)
            ->groupBy('analysis_topic_key')
            ->map(function (Collection $group) {
                $correct = $group->where('is_correct', true)->count();
                return [
                    'label' => (string) ($group->first()->analysis_topic_label ?? 'Topik'),
                    'total' => $group->count(),
                    'accuracy' => $this->percentage($correct, $group->count()),
                ];
            })
            ->filter(fn ($row) => $row['total'] >= 3 && $row['accuracy'] < 60)
            ->sortBy('accuracy')
            ->values();

        if ($recentWeak->isNotEmpty()) {
            $topic = $recentWeak->first();
            $insights[] = 'Belakangan ini Anda cukup sering salah di ' . $topic['label'] . '.';
        }

        $timeline = collect($history['timeline'] ?? []);
        if ($timeline->count() >= 6) {
            $lastAvg = $timeline->slice(-3)->avg('accuracy');
            $prevAvg = $timeline->slice(-6, 3)->avg('accuracy');
            if ($lastAvg + 5 < $prevAvg) {
                $insights[] = 'Tren akurasi 3 sesi terakhir sedang menurun dibanding 3 sesi sebelumnya.';
                $suggestions[] = 'Kurangi campur materi dulu dan ulangi topik yang paling lemah sampai stabil lagi.';
            } elseif ($lastAvg > $prevAvg + 5) {
                $insights[] = 'Performa terbaru menunjukkan tren naik yang konsisten.';
            }
        }

        $tiuNumeric = $this->keywordAccuracy($answers, ['numerik', 'aritmetika', 'deret', 'angka']);
        $tiuVerbal = $this->keywordAccuracy($answers, ['verbal', 'analogi', 'sinonim', 'antonim']);
        if ($tiuNumeric['total'] >= 3 && $tiuVerbal['total'] >= 3 && $tiuNumeric['accuracy'] >= 75 && $tiuVerbal['accuracy'] < 60) {
            $insights[] = 'Anda cenderung lebih kuat di numerik, tetapi masih tertahan di verbal.';
            $suggestions[] = 'Tambah sesi singkat khusus verbal 10-15 soal per hari agar gap TIU cepat tertutup.';
        }

        if (count($modes) >= 2) {
            $sortedModes = collect($modes)->sortBy('accuracy')->values();
            $weakMode = $sortedModes->first();
            $strongMode = $sortedModes->last();
            if (($strongMode['accuracy'] ?? 0) >= (($weakMode['accuracy'] ?? 0) + 10)) {
                $insights[] = 'Mode terkuat Anda saat ini ' . $strongMode['mode_label'] . ', sedangkan yang paling lemah ' . $weakMode['mode_label'] . '.';
                $suggestions[] = 'Naikkan porsi latihan di mode ' . $weakMode['mode_label'] . ' sambil mempertahankan ritme di ' . $strongMode['mode_label'] . '.';
            }
        }

        if ($weakest->isEmpty() && empty($suggestions)) {
            $suggestions[] = 'Data belum cukup banyak. Lanjutkan beberapa sesi lagi agar rekomendasi makin akurat.';
        }

        $studyPlan = $weakest->map(function ($row) {
            return [
                'topic' => $row['label'],
                'target' => 'Kerjakan 20 soal terfokus dan review pembahasan sampai akurasi melewati 75%.',
            ];
        })->values()->all();

        return [
            'insights' => array_values(array_unique($insights)),
            'suggestions' => array_values(array_unique($suggestions)),
            'study_plan' => $studyPlan,
        ];
    }

    private function keywordAccuracy(Collection $answers, array $keywords): array
    {
        $group = $answers->filter(function ($row) use ($keywords) {
            $haystack = mb_strtolower(trim((string) (($row->kategori ?? '') . ' ' . ($row->sub_topik ?? ''))));
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, mb_strtolower($keyword))) {
                    return true;
                }
            }
            return false;
        });

        $total = $group->count();
        $correct = $group->where('is_correct', true)->count();

        return [
            'total' => $total,
            'accuracy' => $this->percentage($correct, $total),
        ];
    }

    private function topicLabel(?string $kategori = null, ?string $subTopik = null): string
    {
        $kategori = strtoupper(trim((string) ($kategori ?? '')));
        $subTopik = $this->cleanLabel($subTopik);

        if ($subTopik !== null && $subTopik !== '' && strcasecmp($subTopik, $kategori) !== 0) {
            return trim($kategori . ' ' . $subTopik);
        }

        return $kategori !== '' ? $kategori : 'Topik Lainnya';
    }

    private function modeLabel(?ExamMode $mode = null, ?int $modeId = null, ?ExamMode $defaultSkd = null): string
    {
        $defaultSkd = $defaultSkd ?: (Schema::hasTable('exam_modes') ? ExamMode::defaultSkd() : null);
        if ($mode) {
            return (string) $mode->name;
        }
        if ($modeId && $defaultSkd && (int) $defaultSkd->id === (int) $modeId) {
            return (string) $defaultSkd->name;
        }
        return (string) ($defaultSkd?->name ?? 'SKD');
    }

    private function modeCode(?ExamMode $mode = null, ?int $modeId = null, ?ExamMode $defaultSkd = null): string
    {
        $defaultSkd = $defaultSkd ?: (Schema::hasTable('exam_modes') ? ExamMode::defaultSkd() : null);
        if ($mode) {
            return strtolower((string) $mode->code);
        }
        if ($modeId && $defaultSkd && (int) $defaultSkd->id === (int) $modeId) {
            return strtolower((string) $defaultSkd->code);
        }
        return strtolower((string) ($defaultSkd?->code ?? 'skd'));
    }

    private function percentage(int|float $numerator, int|float $denominator): float
    {
        if ((float) $denominator <= 0) {
            return 0.0;
        }

        return round((((float) $numerator) / ((float) $denominator)) * 100, 1);
    }

    private function statusForAccuracy(float $accuracy, int $attempts): string
    {
        if ($attempts < self::MIN_TOPIC_ATTEMPTS) {
            return 'insufficient';
        }
        if ($accuracy >= 80) {
            return 'strong';
        }
        if ($accuracy >= 60) {
            return 'medium';
        }
        return 'weak';
    }

    private function cleanLabel($value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value !== '' ? $value : null;
    }
}
