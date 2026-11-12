<?php

namespace App\Jobs;

use App\Models\Soal;
use App\Services\QuestionQualityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AuditTiuQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<int> $questionIds
     */
    public function __construct(
        public array $questionIds,
        public string $provider = 'groq',
        public ?string $model = null
    ) {
    }

    public function handle(QuestionQualityService $qualityService): void
    {
        Soal::query()
            ->whereIn('id', array_values(array_unique(array_map('intval', $this->questionIds))))
            ->orderBy('id')
            ->chunkById(20, function ($rows) use ($qualityService) {
                foreach ($rows as $soal) {
                    $qualityService->runAudit($soal, 'existing_tiu', $this->provider, $this->model);
                }
            });
    }
}
