<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AISoalService;
use App\Services\QuestionQualityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class BulkGenerateSoalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;
    public $tries = 1;

    public function __construct(
        public string $jobId,
        public array $requestPayload,
        public array $plan,
        public string $outputType,
        public ?int $userId,
    ) {
        $this->queue = 'generate';
    }

    public function handle(AISoalService $aiService, QuestionQualityService $qualityService): void
    {
        $totalTarget = (int) ($this->requestPayload['total_questions'] ?? 0);
        $done = 0;
        $user = $this->userId ? User::find($this->userId) : null;
        $summary = [
            'generated' => 0,
            'approved' => 0,
            'revised' => 0,
            'flagged' => 0,
            'rejected' => 0,
        ];

        foreach ($this->plan as $step) {
            $difficulty = (string) ($step['difficulty'] ?? 'medium');
            $jumlah = (int) ($step['jumlah'] ?? 0);
            if ($jumlah <= 0) {
                continue;
            }

            $this->putState([
                'status' => 'processing',
                'message' => "Generate {$jumlah} soal tingkat {$difficulty}...",
                'progress' => ['done' => $done, 'total' => $totalTarget],
            ]);

            if ($this->outputType === 'ready') {
                $result = $qualityService->generateWithQc([
                    'kategori' => $this->requestPayload['kategori'],
                    'total' => $jumlah,
                    'provider' => $this->requestPayload['provider'],
                    'model' => $this->requestPayload['model'],
                    'fokus_topik' => $this->requestPayload['fokus_topik'],
                    'exam_mode_id' => $this->requestPayload['exam_mode_id'],
                    'sub_category_id' => $this->requestPayload['sub_category_id'],
                    'difficulty' => $difficulty,
                ], $user);
            } else {
                $result = $aiService->generateSoal(
                    (string) $this->requestPayload['kategori'],
                    $jumlah,
                    (string) $this->requestPayload['provider'],
                    $this->requestPayload['model'],
                    null,
                    $this->requestPayload['fokus_topik'],
                    null,
                    $this->requestPayload['exam_mode_id'],
                    $this->requestPayload['sub_category_id'],
                    $difficulty,
                    $this->userId
                );

                $result = $result === true
                    ? ['ok' => true, 'summary' => ['question_ids' => [], 'approved' => 0, 'revised' => 0, 'flagged' => 0, 'rejected' => 0]]
                    : ['ok' => false, 'message' => (string) $result];
            }

            if (empty($result['ok'])) {
                $this->putState([
                    'status' => 'error',
                    'message' => (string) ($result['message'] ?? 'Generate massal gagal.'),
                    'progress' => ['done' => $done, 'total' => $totalTarget],
                ]);
                return;
            }

            $done += $jumlah;
            $summary['generated'] += $jumlah;
            $batchSummary = is_array($result['summary'] ?? null) ? $result['summary'] : [];
            foreach (['approved', 'revised', 'flagged', 'rejected'] as $key) {
                $summary[$key] += (int) ($batchSummary[$key] ?? 0);
            }

            $this->putState([
                'status' => 'processing',
                'message' => "Selesai batch {$difficulty}.",
                'progress' => ['done' => min($done, $totalTarget), 'total' => $totalTarget],
                'bulk_summary' => $summary,
            ]);
        }

        $this->putState([
            'status' => 'success',
            'message' => 'Generate soal massal selesai.',
            'progress' => ['done' => min($done, $totalTarget), 'total' => $totalTarget],
            'bulk_summary' => $summary,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $this->putState([
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage(),
        ]);
    }

    private function putState(array $payload): void
    {
        $key = 'ai_generate:' . $this->jobId;
        $current = Cache::get($key);
        if (!is_array($current)) {
            $current = [];
        }
        Cache::put($key, array_replace_recursive($current, $payload), now()->addHours(2));
    }
}
