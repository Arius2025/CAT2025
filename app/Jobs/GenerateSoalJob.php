<?php

namespace App\Jobs;

use App\Services\AISoalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GenerateSoalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Biar worker punya waktu cukup untuk cold start model + output panjang
    public $timeout = 900;

    // Prevent repeated retries that can leave UI stuck on "processing".
    public $tries = 1;

    public function __construct(
        public string $jobId,
        public ?string $kategori,
        public int $jumlah,
        public string $provider,
        public ?string $model,
        public ?int $userId,
        public ?string $pdfPath = null,
        public ?string $fokusTopik = null,
        public ?int $examModeId = null,
        public ?int $subCategoryId = null,
        public ?string $difficulty = 'medium',
        public bool $autoDetectPdf = false,
    ) {
    }

    public function handle(AISoalService $aiSoalService): void
    {
        $kategori = is_string($this->kategori) ? trim($this->kategori) : '';
        $fokusTopik = $this->fokusTopik;
        $examModeId = $this->examModeId;
        $subCategoryId = $this->subCategoryId;

        if ($this->autoDetectPdf && $this->pdfPath) {
            $profile = $aiSoalService->detectGenerateProfileFromPdf($this->pdfPath, $this->provider);
            $examModeId = (int) ($profile['exam_mode_id'] ?? $examModeId ?? 0) ?: null;
            $subCategoryId = (int) ($profile['sub_category_id'] ?? $subCategoryId ?? 0) ?: null;
            $kategori = trim((string) ($profile['kategori'] ?? $kategori));
            $fokusTopik = trim((string) ($profile['fokus_topik'] ?? $fokusTopik ?? '')) ?: $fokusTopik;

            $existing = Cache::get($this->cacheKey());
            if (!is_array($existing)) $existing = [];
            $existing['status'] = 'processing';
            $existing['message'] = 'Profil PDF terdeteksi otomatis. Melanjutkan generate di background...';
            $existing['user_id'] = $this->userId;
            $existing['queued_at'] = $existing['queued_at'] ?? now()->toISOString();
            $existing['auto_detected'] = [
                'exam_mode_id' => $examModeId,
                'sub_category_id' => $subCategoryId,
                'kategori' => $kategori,
                'fokus_topik' => $fokusTopik,
                'reason' => $profile['reason'] ?? null,
                'source' => $profile['source'] ?? null,
            ];
            if (!isset($existing['request']) || !is_array($existing['request'])) $existing['request'] = [];
            $existing['request']['pdf_path'] = $this->pdfPath;
            $existing['request']['exam_mode_id'] = $examModeId;
            $existing['request']['sub_category_id'] = $subCategoryId;
            $existing['request']['kategori'] = $kategori;
            $existing['request']['fokus_topik'] = $fokusTopik;
            Cache::put($this->cacheKey(), $existing, now()->addHour());
        }

        if ($kategori === '') {
            Cache::put($this->cacheKey(), [
                'status' => 'error',
                'message' => 'Kategori generate belum terisi dan auto-detect PDF gagal menentukan kategori.',
                'user_id' => $this->userId,
            ], now()->addHour());
            return;
        }

        $result = $aiSoalService->generateSoal($kategori, $this->jumlah, $this->provider, $this->model, $this->pdfPath, $fokusTopik, $this->jobId, $examModeId, $subCategoryId, $this->difficulty, $this->userId);

        if ($result === true) {
            $existing = Cache::get($this->cacheKey());
            if (!is_array($existing)) $existing = [];
            $existing['status'] = 'success';
            $existing['message'] = "Sukses! Soal {$kategori} berhasil dibuat.";
            $existing['user_id'] = $this->userId;
            $existing['queue_name'] = $existing['queue_name'] ?? $this->queue;
            Cache::put($this->cacheKey(), $existing, now()->addHour());
            return;
        }

        $existing = Cache::get($this->cacheKey());
        if (!is_array($existing)) $existing = [];
        $existing['status'] = 'error';
        $existing['message'] = "Gagal: " . (string) $result;
        $existing['user_id'] = $this->userId;
        $existing['queue_name'] = $existing['queue_name'] ?? $this->queue;
        Cache::put($this->cacheKey(), $existing, now()->addHour());
    }

    public function failed(\Throwable $e): void
    {
        $existing = Cache::get($this->cacheKey());
        if (!is_array($existing)) $existing = [];
        $existing['status'] = 'error';
        $existing['message'] = 'Exception: ' . $e->getMessage();
        $existing['user_id'] = $this->userId;
        $existing['queue_name'] = $existing['queue_name'] ?? $this->queue;
        Cache::put($this->cacheKey(), $existing, now()->addHour());
    }

    private function cacheKey(): string
    {
        return 'ai_generate:' . $this->jobId;
    }
}
