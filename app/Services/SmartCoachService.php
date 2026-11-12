<?php

namespace App\Services;

use App\Models\ExamAnswer;
use App\Models\ExamMode;
use App\Models\ExpertRule;
use App\Models\RecommendationLog;
use App\Models\Soal;
use App\Models\StudyPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SmartCoachService
{
    public function __construct(
        private readonly LearningAnalysisService $analysisService
    ) {
    }

    public function dashboard(User $user, bool $forceRefresh = false): array
    {
        $cacheKey = 'coach:dashboard:' . $user->id;
        $ttl = now()->addMinutes((int) config('coach.cache_ttl_minutes', 15));

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($user) {
            return $this->buildDashboard($user);
        });
    }

    public function regenerateStudyPlan(User $user): array
    {
        $dashboard = $this->buildDashboard($user, true);
        Cache::forget('coach:dashboard:' . $user->id);

        return $dashboard['study_plan'];
    }

    public function expertRules(): Collection
    {
        return ExpertRule::query()
            ->orderBy('priority')
            ->orderBy('code')
            ->get();
    }

    public function createExpertRule(array $payload): ExpertRule
    {
        return ExpertRule::query()->create($payload);
    }

    private function buildDashboard(User $user, bool $forcePlanRegenerate = false): array
    {
        $analysis = $this->analysisService->dashboard($user);
        $answers = $this->answeredRows($user);
        $completedExams = collect($analysis['history']['timeline'] ?? []);
        $topicBankFrequency = $this->questionBankTopicFrequency();
        $status = $this->buildStatus($analysis, $answers, $completedExams);
        $priorities = $this->buildPriorities($analysis, $answers, $topicBankFrequency);
        $expert = $this->buildExpertRecommendations($user, $analysis, $priorities, $status);
        $studyPlan = $this->ensureStudyPlan($user, $priorities, $forcePlanRegenerate);
        $prediction = $this->buildPrediction($analysis, $status, $priorities);

        $this->logRecommendations($user, 'spk', collect($priorities['items'] ?? [])->pluck('reason_text')->filter()->take(3)->all());
        $this->logRecommendations($user, 'expert', collect($expert['items'] ?? [])->pluck('text')->filter()->take(3)->all());

        return [
            'status' => $status,
            'priorities' => $priorities,
            'recommendations' => $expert,
            'study_plan' => $studyPlan,
            'prediction' => $prediction,
            'analysis_snapshot' => [
                'summary' => $analysis['summary'] ?? [],
                'modes' => $analysis['modes'] ?? [],
                'history' => $analysis['history'] ?? [],
            ],
        ];
    }

    private function buildStatus(array $analysis, Collection $answers, Collection $completedExams): array
    {
        $modes = collect($analysis['modes'] ?? []);
        $historyTimeline = collect($analysis['history']['timeline'] ?? []);
        $activeModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get()
            : collect();

        $cards = $activeModes->map(function (ExamMode $mode) use ($modes, $historyTimeline) {
            $modeStat = $modes->firstWhere('mode_code', strtolower((string) $mode->code))
                ?: $modes->firstWhere('mode_label', $mode->name);
            $modeTimeline = $historyTimeline->filter(fn ($row) => strtolower((string) ($row['mode_label'] ?? '')) === strtolower($mode->name))->values();
            $trend = $this->trendForTimeline($modeTimeline);
            $accuracy = (float) ($modeStat['accuracy'] ?? 0);
            $attempts = (int) ($modeStat['total_attempts'] ?? 0);
            $volumeScore = min(100, $attempts * 12);
            $trendScore = max(0, min(100, 50 + ($trend['delta'] * 3)));
            $readiness = round(($accuracy * 0.65) + ($volumeScore * 0.20) + ($trendScore * 0.15), 1);

            return [
                'mode_id' => (int) $mode->id,
                'mode_code' => strtolower((string) $mode->code),
                'mode_label' => (string) $mode->name,
                'accuracy' => $accuracy,
                'attempts' => $attempts,
                'average_score' => (float) ($modeStat['average_score'] ?? 0),
                'trend' => $trend,
                'readiness' => $readiness,
                'status' => $this->statusBand($readiness),
            ];
        })->values();

        $overallTrend = $this->trendForTimeline($historyTimeline);
        $consistency = $this->consistencyScore($answers);

        return [
            'modes' => $cards->all(),
            'trend' => [
                'direction' => $overallTrend['direction'],
                'label' => $overallTrend['label'],
                'delta' => $overallTrend['delta'],
            ],
            'consistency' => $consistency,
            'last_activity_days' => $this->inactiveDays($answers, $completedExams),
        ];
    }

    private function buildPriorities(array $analysis, Collection $answers, array $topicBankFrequency): array
    {
        $allTopics = collect($analysis['categories']['all'] ?? []);
        $recentWindow = now()->subDays((int) config('coach.recent_window_days', 14));
        $weights = config('coach.priority_weights', []);
        $maxBankFrequency = max(1, collect($topicBankFrequency)->max('count') ?: 1);

        $items = $allTopics->map(function (array $topic) use ($answers, $recentWindow, $weights, $topicBankFrequency, $maxBankFrequency) {
            $labelKey = mb_strtolower((string) ($topic['label'] ?? ''));
            $topicAnswers = $answers->filter(fn ($row) => (string) ($row->analysis_topic_key ?? '') === $labelKey);
            $recentWrong = $topicAnswers
                ->filter(fn ($row) => (bool) !$row->is_correct && ($row->analysis_answered_date?->gte($recentWindow) ?? false))
                ->count();
            $lastAnsweredAt = $topicAnswers->max(fn ($row) => optional($row->analysis_answered_date)->timestamp ?: 0);
            $daysSince = $lastAnsweredAt ? now()->diffInDays(Carbon::createFromTimestamp($lastAnsweredAt)) : 30;
            $bankCount = (int) ($topicBankFrequency[$labelKey]['count'] ?? 0);
            $topicFrequency = round(($bankCount / $maxBankFrequency) * 100, 1);
            $lowAccuracy = max(0, 100 - (float) ($topic['accuracy'] ?? 0));
            $recentWrongScore = min(100, $recentWrong * 12);
            $staleness = min(100, $daysSince * 5);

            $priorityScore = round(
                ($lowAccuracy * (float) ($weights['low_accuracy'] ?? 0.50)) +
                ($recentWrongScore * (float) ($weights['recent_wrong_count'] ?? 0.30)) +
                ($topicFrequency * (float) ($weights['topic_frequency'] ?? 0.20)) +
                ($staleness * (float) ($weights['staleness'] ?? 0.15)),
                1
            );

            $reasons = [];
            if (($topic['accuracy'] ?? 0) < 70) {
                $reasons[] = 'akurasi rendah';
            }
            if ($recentWrong >= 3) {
                $reasons[] = 'sering salah baru-baru ini';
            }
            if ($topicFrequency >= 50) {
                $reasons[] = 'topik penting';
            }
            if ($daysSince >= 10) {
                $reasons[] = 'sudah lama tidak dilatih';
            }

            return [
                'topic_key' => $labelKey,
                'topic_label' => (string) ($topic['label'] ?? 'Topik'),
                'mode_label' => (string) ($topic['mode_label'] ?? ''),
                'kategori' => (string) ($topic['kategori'] ?? ''),
                'sub_topik' => (string) ($topic['sub_topik'] ?? ''),
                'accuracy' => (float) ($topic['accuracy'] ?? 0),
                'attempts' => (int) ($topic['total'] ?? 0),
                'recent_wrong_count' => $recentWrong,
                'topic_frequency' => $topicFrequency,
                'days_since_practice' => $daysSince,
                'priority_score' => $priorityScore,
                'reasons' => $reasons,
                'reason_text' => implode(', ', $reasons),
                'status' => $this->statusBand($priorityScore >= 70 ? 30 : ($priorityScore >= 45 ? 60 : 85)),
            ];
        })
        ->filter(fn (array $row) => $row['attempts'] > 0)
        ->sortByDesc('priority_score')
        ->values();

        return [
            'weights' => $weights,
            'items' => $items->take(7)->values()->all(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function buildExpertRecommendations(User $user, array $analysis, array $priorities, array $status): array
    {
        $globalContext = [
            'trend_delta' => (float) ($status['trend']['delta'] ?? 0),
            'recent_sessions' => count($analysis['history']['timeline'] ?? []),
            'inactive_days' => (int) ($status['last_activity_days'] ?? 0),
            'consistency_score' => (float) ($status['consistency']['score'] ?? 0),
        ];

        $contexts = collect($priorities['items'] ?? [])->map(function (array $item) {
            return [
                'scope' => 'topic',
                'topic_label' => $item['topic_label'],
                'accuracy' => $item['accuracy'],
                'attempts' => $item['attempts'],
                'mode_label' => $item['mode_label'],
                'kategori' => $item['kategori'],
                'sub_topik' => $item['sub_topik'],
                'search_text' => mb_strtolower(trim($item['topic_label'] . ' ' . $item['kategori'] . ' ' . $item['sub_topik'] . ' ' . $item['mode_label'])),
            ];
        })->push(array_merge(['scope' => 'global'], $globalContext));

        $items = [];
        $rules = ExpertRule::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        foreach ($rules as $rule) {
            $condition = is_array($rule->condition_json) ? $rule->condition_json : [];
            $scope = (string) ($condition['scope'] ?? 'global');

            foreach ($contexts as $context) {
                if (($context['scope'] ?? 'global') !== $scope) {
                    continue;
                }

                if (!$this->matchesRule($context, $condition)) {
                    continue;
                }

                $items[] = [
                    'code' => $rule->code,
                    'text' => $this->interpolateRuleText($rule->result_text, $context),
                    'scope' => $scope,
                ];
                break;
            }
        }

        if (empty($items)) {
            $top = $priorities['items'][0] ?? null;
            $items[] = [
                'code' => 'fallback_priority_focus',
                'scope' => 'global',
                'text' => $top
                    ? 'Mulai minggu ini dari ' . $top['topic_label'] . ' karena itu memberi dampak perbaikan tercepat untuk akurasi Anda.'
                    : 'Tambahkan beberapa sesi lagi agar saran mentor makin tajam dan personal.',
            ];
        }

        return [
            'items' => array_values(array_unique($items, SORT_REGULAR)),
            'generated_at' => now()->toIso8601String(),
            'user_id' => (int) $user->id,
        ];
    }

    private function ensureStudyPlan(User $user, array $priorities, bool $forceRegenerate): array
    {
        $days = max(3, (int) config('coach.plan_days', 7));
        $today = now()->startOfDay();
        $existing = StudyPlan::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$today->toDateString(), $today->copy()->addDays($days - 1)->toDateString()])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        if ($forceRegenerate || $existing->isEmpty()) {
            StudyPlan::query()
                ->where('user_id', $user->id)
                ->whereBetween('date', [$today->toDateString(), $today->copy()->addDays($days - 1)->toDateString()])
                ->delete();

            $priorityItems = collect($priorities['items'] ?? [])->values();
            $created = collect();

            for ($offset = 0; $offset < $days; $offset++) {
                $date = $today->copy()->addDays($offset);
                $focus = $priorityItems->get($offset % max(1, $priorityItems->count()));
                $secondary = $priorityItems->get(($offset + 1) % max(1, $priorityItems->count()));

                $tasks = collect([
                    $focus
                        ? 'Kerjakan 20 soal ' . $focus['topic_label'] . ' lalu catat pola salah yang berulang.'
                        : 'Kerjakan 15 soal campuran dari mode favorit Anda.',
                    'Review minimal 10 soal yang salah dari sesi sebelumnya.',
                    $secondary
                        ? 'Tambah 10 soal penguat untuk ' . $secondary['topic_label'] . '.'
                        : 'Tutup sesi dengan 5 menit rangkum konsep inti yang masih keliru.',
                ])->filter()->values();

                foreach ($tasks as $taskText) {
                    $created->push(StudyPlan::query()->create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'task_text' => $taskText,
                        'status' => 'pending',
                    ]));
                }
            }

            $existing = $created;
        }

        return [
            'days' => $existing
                ->groupBy(fn (StudyPlan $plan) => $plan->date->toDateString())
                ->map(function (Collection $group, string $date) {
                    return [
                        'date' => $date,
                        'label' => Carbon::parse($date)->locale('id')->translatedFormat('l, d M'),
                        'tasks' => $group->map(fn (StudyPlan $plan) => [
                            'id' => (int) $plan->id,
                            'task_text' => $plan->task_text,
                            'status' => $plan->status,
                        ])->values()->all(),
                    ];
                })
                ->values()
                ->all(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function buildPrediction(array $analysis, array $status, array $priorities): array
    {
        $summary = $analysis['summary'] ?? [];
        $timeline = collect($analysis['history']['timeline'] ?? []);
        $trend = $this->trendForTimeline($timeline);
        $consistencyScore = (float) ($status['consistency']['score'] ?? 0);
        $volumeScore = min(100, ((int) ($summary['total_sessions'] ?? 0)) * 10);
        $accuracy = (float) ($summary['accuracy'] ?? 0);
        $weights = config('coach.prediction', []);

        $readinessScore = round(
            ($accuracy * (float) ($weights['readiness_accuracy_weight'] ?? 0.55)) +
            ($consistencyScore * (float) ($weights['readiness_consistency_weight'] ?? 0.15)) +
            ($volumeScore * (float) ($weights['readiness_volume_weight'] ?? 0.15)) +
            (max(0, min(100, 50 + ($trend['delta'] * 3))) * (float) ($weights['readiness_trend_weight'] ?? 0.15)),
            1
        );

        $level = $readinessScore >= 75 ? 'tinggi' : ($readinessScore >= 55 ? 'sedang' : 'rendah');
        $message = match ($level) {
            'tinggi' => 'Siap tryout full mode dengan tetap menjaga review topik prioritas.',
            'sedang' => 'Peluang sudah mulai terbentuk, tetapi masih butuh fokus 1-2 minggu pada prioritas teratas.',
            default => 'Fokus dulu pada topik terlemah dan ritme belajar harian sebelum mengejar tryout penuh.',
        };

        $topPriority = $priorities['items'][0]['topic_label'] ?? null;

        return [
            'readiness_score' => $readinessScore,
            'level' => $level,
            'estimate' => 'Peluang lolos ' . $level,
            'message' => $message,
            'recommended_window' => $readinessScore >= 75 ? 'Siap tryout full mode' : ($readinessScore >= 55 ? 'Butuh fokus 2 minggu lagi' : 'Perlu penguatan dasar 2-3 minggu'),
            'focus_topic' => $topPriority,
            'confidence_note' => 'Ini adalah estimasi berbasis histori latihan, bukan jaminan hasil ujian resmi.',
        ];
    }

    private function answeredRows(User $user): Collection
    {
        if (!Schema::hasTable('exam_answers')) {
            return collect();
        }

        return ExamAnswer::query()
            ->where('user_id', $user->id)
            ->where('is_answered', true)
            ->with(['exam:id,created_at', 'examMode:id,name,code', 'subCategory:id,name'])
            ->orderBy('answered_at')
            ->get()
            ->map(function (ExamAnswer $row) {
                $row->analysis_topic_label = trim((string) ($row->kategori . ' ' . ($row->sub_topik ?? '')));
                $row->analysis_topic_key = mb_strtolower(trim((string) ($row->analysis_topic_label ?: $row->kategori ?: 'topik lainnya')));
                $row->analysis_answered_date = optional($row->answered_at ?: optional($row->exam)->created_at)->copy();
                return $row;
            });
    }

    private function questionBankTopicFrequency(): array
    {
        if (!Schema::hasTable('soals')) {
            return [];
        }

        return Soal::query()
            ->selectRaw("LOWER(TRIM(CONCAT(COALESCE(kategori, ''), ' ', COALESCE(sub_topik, '')))) as topic_key, COUNT(*) as aggregate_count")
            ->groupBy(DB::raw("LOWER(TRIM(CONCAT(COALESCE(kategori, ''), ' ', COALESCE(sub_topik, ''))))"))
            ->get()
            ->mapWithKeys(fn ($row) => [(string) $row->topic_key => ['count' => (int) $row->aggregate_count]])
            ->all();
    }

    private function trendForTimeline(Collection $timeline): array
    {
        if ($timeline->count() < 4) {
            return ['direction' => 'flat', 'delta' => 0.0, 'label' => 'Belum cukup data'];
        }

        $recent = $timeline->slice(-3);
        $previous = $timeline->slice(-6, 3);
        $delta = round(((float) $recent->avg('accuracy')) - ((float) $previous->avg('accuracy')), 1);

        return [
            'direction' => $delta >= 4 ? 'up' : ($delta <= -4 ? 'down' : 'flat'),
            'delta' => $delta,
            'label' => $delta >= 4 ? 'Naik' : ($delta <= -4 ? 'Turun' : 'Stabil'),
        ];
    }

    private function consistencyScore(Collection $answers): array
    {
        $windowDays = 14;
        $windowStart = now()->subDays($windowDays - 1)->startOfDay();
        $activeDays = $answers
            ->filter(fn ($row) => $row->analysis_answered_date?->gte($windowStart))
            ->groupBy(fn ($row) => $row->analysis_answered_date?->format('Y-m-d'))
            ->count();
        $score = round(min(100, ($activeDays / $windowDays) * 100), 1);

        return [
            'score' => $score,
            'active_days' => $activeDays,
            'window_days' => $windowDays,
            'label' => $score >= 70 ? 'Konsisten' : ($score >= 40 ? 'Cukup konsisten' : 'Belum stabil'),
            'status' => $this->statusBand($score),
        ];
    }

    private function inactiveDays(Collection $answers, Collection $completedExams): int
    {
        $lastAnswerAt = $answers->max(fn ($row) => optional($row->analysis_answered_date)->timestamp ?: 0);
        $lastExamAt = $completedExams->max(fn ($row) => !empty($row['date']) ? Carbon::parse($row['date'])->timestamp : 0);
        $timestamp = max((int) $lastAnswerAt, (int) $lastExamAt);

        if ($timestamp <= 0) {
            return 999;
        }

        return now()->diffInDays(Carbon::createFromTimestamp($timestamp));
    }

    private function matchesRule(array $context, array $condition): bool
    {
        $match = $condition['match'] ?? [];
        $keywords = collect($match['keywords'] ?? [])->filter()->map(fn ($v) => mb_strtolower((string) $v))->values();
        if ($keywords->isNotEmpty()) {
            $haystack = mb_strtolower((string) ($context['search_text'] ?? $context['topic_label'] ?? ''));
            $keywordMatched = $keywords->contains(fn ($keyword) => str_contains($haystack, $keyword));
            if (!$keywordMatched) {
                return false;
            }
        }

        foreach (($condition['conditions'] ?? []) as $ruleCheck) {
            $field = (string) ($ruleCheck['field'] ?? '');
            $operator = (string) ($ruleCheck['operator'] ?? '=');
            $expected = $ruleCheck['value'] ?? null;
            $actual = $context[$field] ?? null;

            if (!$this->compare($actual, $operator, $expected)) {
                return false;
            }
        }

        return true;
    }

    private function compare(mixed $actual, string $operator, mixed $expected): bool
    {
        return match ($operator) {
            '>' => (float) $actual > (float) $expected,
            '>=' => (float) $actual >= (float) $expected,
            '<' => (float) $actual < (float) $expected,
            '<=' => (float) $actual <= (float) $expected,
            '!=' => (string) $actual !== (string) $expected,
            'in' => in_array($actual, is_array($expected) ? $expected : [$expected], true),
            default => (string) $actual === (string) $expected,
        };
    }

    private function interpolateRuleText(string $text, array $context): string
    {
        $replacements = [];
        foreach ($context as $key => $value) {
            $replacements['{' . $key . '}'] = is_float($value) ? number_format($value, 1) : (string) $value;
        }

        return strtr($text, $replacements);
    }

    private function statusBand(float $score): string
    {
        if ($score >= 75) {
            return 'strong';
        }
        if ($score >= 55) {
            return 'medium';
        }
        return 'urgent';
    }

    private function logRecommendations(User $user, string $type, array $lines): void
    {
        if (!Schema::hasTable('recommendation_logs')) {
            return;
        }

        $today = now()->toDateString();
        $alreadyLogged = RecommendationLog::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereDate('generated_at', $today)
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        foreach (array_slice(array_values(array_filter($lines)), 0, 3) as $line) {
            RecommendationLog::query()->create([
                'user_id' => $user->id,
                'generated_at' => now(),
                'type' => $type,
                'recommendation_text' => $line,
            ]);
        }
    }
}
