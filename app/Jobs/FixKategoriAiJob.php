<?php

namespace App\Jobs;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Services\AISoalService;
use App\Support\ExamStructureSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class FixKategoriAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;
    public $tries = 1;

    public function __construct(
        public string $jobId,
        public int $examModeId,
        public string $provider = 'groq',
        public ?string $model = null,
        public int $limit = 400,
        public ?int $userId = null,
    ) {
    }

    public function handle(AISoalService $ai): void
    {
        $key = $this->cacheKey();

        $mode = Schema::hasTable('exam_modes')
            ? ExamMode::query()->find($this->examModeId)
            : null;
        $modeCode = strtolower((string) ($mode->code ?? ''));
        if ($modeCode === '' || $modeCode === 'skd') {
            Cache::put($key, [
                'status' => 'error',
                'message' => 'Mode ujian tidak valid untuk auto-fix kategori (butuh PPPK atau SKB).',
                'user_id' => $this->userId,
            ], now()->addHour());
            return;
        }

        $options = ExamStructureSupport::categoryOptions($this->examModeId, $modeCode)
            ->pluck('value')
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->filter(fn ($v) => $v !== '' && $v !== 'FIGURAL')
            ->unique()
            ->values()
            ->all();

        if (empty($options)) {
            Cache::put($key, [
                'status' => 'error',
                'message' => 'Kategori untuk mode ini belum ada. Rapikan dulu di Struktur Ujian.',
                'user_id' => $this->userId,
            ], now()->addHour());
            return;
        }

        $legacyCats = ['TWK', 'TIU', 'TKP'];

        $query = Soal::query()
            ->where('exam_mode_id', $this->examModeId)
            ->where(function ($q) use ($legacyCats) {
                $q->whereNull('kategori')
                    ->orWhere('kategori', '')
                    ->orWhereIn('kategori', $legacyCats);
            })
            ->orderBy('id');

        $total = (int) (clone $query)->count();
        $total = min($total, max(1, $this->limit));

        $done = 0;
        $changed = 0;
        $errors = 0;
        $log = [];

        Cache::put($key, [
            'status' => 'processing',
            'message' => 'Memulai auto-fix kategori...',
            'user_id' => $this->userId,
            'mode_code' => $modeCode,
            'provider' => $this->provider,
            'model' => $this->model,
            'allowed' => $options,
            'progress' => ['done' => 0, 'total' => $total, 'changed' => 0, 'errors' => 0],
            'log' => [],
            'started_at' => now()->toISOString(),
        ], now()->addHour());

        $rows = (clone $query)->limit($this->limit)->get(['id', 'kategori', 'teks_soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'sub_topik']);

        foreach ($rows as $soal) {
            $done++;
            try {
                $new = $ai->classifyKategoriSoal($soal, $options, $modeCode, $this->provider, $this->model);
                $new = is_string($new) ? strtoupper(trim($new)) : null;
                if ($new && in_array($new, $options, true) && (string) $soal->kategori !== $new) {
                    Soal::query()->where('id', (int) $soal->id)->update(['kategori' => $new]);
                    $changed++;
                    $log[] = '#'.$soal->id.' -> '.$new;
                }
            } catch (\Throwable $e) {
                $errors++;
                $msg = $e->getMessage();
                $log[] = '#'.$soal->id.' error: '.substr($msg, 0, 90);
            }

            if (count($log) > 20) $log = array_slice($log, -20);
            Cache::put($key, [
                'status' => 'processing',
                'message' => 'Memproses...',
                'user_id' => $this->userId,
                'mode_code' => $modeCode,
                'provider' => $this->provider,
                'model' => $this->model,
                'allowed' => $options,
                'progress' => ['done' => $done, 'total' => $total, 'changed' => $changed, 'errors' => $errors],
                'log' => $log,
                'updated_at' => now()->toISOString(),
            ], now()->addHour());
        }

        Cache::put($key, [
            'status' => 'success',
            'message' => "Selesai. Diubah: {$changed}, error: {$errors}.",
            'user_id' => $this->userId,
            'mode_code' => $modeCode,
            'provider' => $this->provider,
            'model' => $this->model,
            'allowed' => $options,
            'progress' => ['done' => $done, 'total' => $total, 'changed' => $changed, 'errors' => $errors],
            'log' => $log,
            'finished_at' => now()->toISOString(),
        ], now()->addHour());
    }

    public function failed(\Throwable $e): void
    {
        Cache::put($this->cacheKey(), [
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage(),
            'user_id' => $this->userId,
        ], now()->addHour());
    }

    private function cacheKey(): string
    {
        return 'ai_fix_kategori:' . $this->jobId;
    }
}

