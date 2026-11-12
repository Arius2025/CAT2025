<?php

namespace App\Jobs;

use App\Models\Soal;
use App\Models\SoalAudit;
use App\Services\AISoalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AuditSoalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function __construct(
        public int $soalId,
        public string $provider = 'ollama_qwen',
        public ?string $model = null,
        public ?string $jobId = null,
    ) {
    }

    public function handle(AISoalService $ai): void
    {
        $soal = Soal::find($this->soalId);
        if (!$soal) return;

        try {
            // Reuse the verification logic (TWK/TIU) via a small "audit" call.
            $result = $ai->auditExistingSoal($soal, $this->provider, $this->model);

            $audit = SoalAudit::firstOrNew([
                'soal_id' => $soal->id,
                'provider' => $this->provider,
                'model' => $this->model,
            ]);

            $audit->db_key = $result['db_key'] ?? null;
            $audit->ai_key = $result['ai_key'] ?? null;
            $audit->pembahasan_ok = (bool) ($result['pembahasan_ok'] ?? false);
            $audit->needs_revision = (bool) ($result['needs_revision'] ?? false);
            $audit->revision_reason = $result['revision_reason'] ?? null;
            $audit->ai_pembahasan = $result['ai_pembahasan'] ?? null;
            $audit->notes = $result['notes'] ?? null;
            $audit->status = $result['status'] ?? 'unknown';

            $prop = is_array($result['proposed'] ?? null) ? $result['proposed'] : null;
            if ($prop) {
                $audit->proposed_teks_soal = $prop['teks_soal'] ?? null;
                $audit->proposed_opsi_a = $prop['opsi_a'] ?? null;
                $audit->proposed_opsi_b = $prop['opsi_b'] ?? null;
                $audit->proposed_opsi_c = $prop['opsi_c'] ?? null;
                $audit->proposed_opsi_d = $prop['opsi_d'] ?? null;
                $audit->proposed_opsi_e = $prop['opsi_e'] ?? null;
                $audit->proposed_jawaban_benar = $prop['jawaban_benar'] ?? null;
                $audit->proposed_pembahasan = $prop['pembahasan'] ?? null;
            }
            $audit->audited_at = now();
            $audit->save();

            $this->progressDone(null);
        } catch (\Throwable $e) {
            // Record audit as error but don't fail the queue job (so progress can complete).
            $audit = SoalAudit::firstOrNew([
                'soal_id' => $this->soalId,
                'provider' => $this->provider,
                'model' => $this->model,
            ]);
            $audit->status = 'error';
            $audit->notes = 'Exception: ' . $e->getMessage();
            $audit->audited_at = now();
            $audit->save();

            $this->progressDone($e->getMessage());
        }
    }

    public function failed(\Throwable $e): void
    {
        $audit = SoalAudit::firstOrNew([
            'soal_id' => $this->soalId,
            'provider' => $this->provider,
            'model' => $this->model,
        ]);
        $audit->status = 'error';
        $audit->notes = 'Exception: ' . $e->getMessage();
        $audit->audited_at = now();
        $audit->save();
    }

    private function progressDone(?string $errMessage): void
    {
        if (!$this->jobId) return;
        $base = 'ai_audit:' . $this->jobId;
        try {
            Cache::increment($base . ':done', 1);
            Cache::put($base . ':updated_at', now()->toISOString(), now()->addHour());
            Cache::put($base . ':last_soal_id', (int) $this->soalId, now()->addHour());
            if ($errMessage !== null && $errMessage !== '') {
                Cache::increment($base . ':errors', 1);
                Cache::put($base . ':last_error', substr((string) $errMessage, 0, 220), now()->addHour());
            }
        } catch (\Throwable $e) {
            // ignore cache errors
        }
    }
}
