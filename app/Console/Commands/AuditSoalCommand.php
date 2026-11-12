<?php

namespace App\Console\Commands;

use App\Jobs\AuditSoalJob;
use App\Models\Soal;
use App\Models\SoalAudit;
use Illuminate\Console\Command;

class AuditSoalCommand extends Command
{
    protected $signature = 'soal:audit
        {--kategori= : TWK|TIU|TKP|ALL}
        {--provider=ollama_qwen : ollama_qwen|groq}
        {--model= : model (e.g. qwen2.5-coder:7b)}
        {--limit=0 : limit number of questions (0 = all)}
        {--only-missing : audit only questions without existing audit record for this provider/model}';

    protected $description = 'Audit soal existing: verifikasi kunci/pembahasan via AI, simpan ke tabel soal_audits.';

    public function handle(): int
    {
        $kategori = strtoupper((string) ($this->option('kategori') ?? 'ALL'));
        $provider = (string) ($this->option('provider') ?? 'ollama_qwen');
        $model = $this->option('model');
        $limit = (int) ($this->option('limit') ?? 0);
        $onlyMissing = (bool) $this->option('only-missing');

        if (!in_array($kategori, ['TWK', 'TIU', 'TKP', 'ALL'], true)) {
            $this->error('kategori harus TWK|TIU|TKP|ALL');
            return self::FAILURE;
        }

        $q = Soal::query();
        if ($kategori !== 'ALL') $q->where('kategori', $kategori);

        if ($onlyMissing) {
            $q->whereDoesntHave('audits', function ($sub) use ($provider, $model) {
                $sub->where('provider', $provider);
                if ($model === null || $model === '') {
                    $sub->whereNull('model');
                } else {
                    $sub->where('model', $model);
                }
            });
        }

        if ($limit > 0) $q->limit($limit);

        $ids = $q->pluck('id')->all();
        if (count($ids) === 0) {
            $this->info('Tidak ada soal untuk diaudit (filter menghasilkan 0).');
            return self::SUCCESS;
        }

        foreach ($ids as $id) {
            AuditSoalJob::dispatch((int) $id, $provider, is_string($model) && $model !== '' ? $model : null);
        }

        $this->info('Queued audit jobs: ' . count($ids));
        $this->info('Provider: ' . $provider . ' | Model: ' . ($model ?: '(null)'));
        $this->info('Lihat hasil di tabel soal_audits (status match/mismatch).');

        // Quick summary for already existing audits
        $match = SoalAudit::where('provider', $provider)
            ->when($model, fn ($x) => $x->where('model', $model), fn ($x) => $x->whereNull('model'))
            ->where('status', 'match')
            ->count();
        $mismatch = SoalAudit::where('provider', $provider)
            ->when($model, fn ($x) => $x->where('model', $model), fn ($x) => $x->whereNull('model'))
            ->where('status', 'mismatch')
            ->count();
        $this->line("Current audits: match={$match} mismatch={$mismatch}");

        return self::SUCCESS;
    }
}
