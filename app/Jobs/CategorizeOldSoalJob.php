<?php

namespace App\Jobs;

use App\Models\Soal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CategorizeOldSoalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $jobId;
    public int $limit;
    public string $provider;
    public ?string $model;

    public function __construct(string $jobId, int $limit = 20, string $provider = 'groq', ?string $model = null)
    {
        $this->jobId = $jobId;
        $this->limit = $limit;
        $this->provider = $provider;
        $this->model = $model;
    }

    private function cacheKey(): string
    {
        return 'ai_categorize:' . $this->jobId;
    }

    private function pushLog(string $line): void
    {
        $key = $this->cacheKey();
        $data = Cache::get($key);
        if (!is_array($data)) $data = [];
        $log = $data['log'] ?? [];
        if (!is_array($log)) $log = [];

        $ts = date('H:i:s');
        $log[] = '[' . $ts . '] ' . $line;
        if (count($log) > 25) $log = array_slice($log, -25);

        $data['log'] = $log;
        Cache::put($key, $data, now()->addHour());
    }

    private function patch(array $patch): void
    {
        $key = $this->cacheKey();
        $data = Cache::get($key);
        if (!is_array($data)) $data = [];
        foreach ($patch as $k => $v) $data[$k] = $v;
        Cache::put($key, $data, now()->addHour());
    }

    public function handle(): void
    {
        $soals = Soal::whereNull('sub_topik')->orWhere('sub_topik', '')->take($this->limit)->get();
        $total = (int) $soals->count();

        $this->patch([
            'status' => 'processing',
            'message' => $total > 0 ? 'Mulai auto-label sub topik...' : 'Tidak ada soal yang perlu dilabeli.',
            'progress' => ['done' => 0, 'total' => $total, 'errors' => 0],
        ]);

        if ($total === 0) {
            $this->patch(['status' => 'success', 'message' => 'Semua soal sudah punya sub topik.']);
            return;
        }

        $done = 0;
        $errors = 0;

        foreach ($soals as $soal) {
            $this->pushLog("Soal #{$soal->id} ({$soal->kategori})");

            try {
                $subTopik = $this->askAIToCategorize($soal);
                if ($subTopik) {
                    $subTopik = substr($subTopik, 0, 100);
                    $soal->update(['sub_topik' => $subTopik]);
                    $this->pushLog("  -> OK: {$subTopik}");
                } else {
                    $errors++;
                    $this->pushLog("  -> Gagal (API/limit).");
                    $this->patch(['last_error' => 'Gagal klasifikasi (API/limit).']);
                }
            } catch (\Throwable $e) {
                $errors++;
                $msg = $e->getMessage();
                $this->pushLog("  -> Error: " . substr($msg, 0, 140));
                $this->patch(['last_error' => $msg]);
            } finally {
                $done++;
                $this->patch(['progress' => ['done' => $done, 'total' => $total, 'errors' => $errors]]);
            }
        }

        $this->patch([
            'status' => $errors > 0 ? 'success' : 'success',
            'message' => "Selesai auto-label: {$done}/{$total} (error: {$errors}).",
        ]);
    }

    private function askAIToCategorize(Soal $soal): ?string
    {
        $subTopikTWK = 'Nasionalisme & Sejarah Kemerdekaan, Integritas & Anti Korupsi, Bela Negara & Ancaman Radikalisme, Pilar Negara (Pancasila & UUD 1945), Bahasa Indonesia & Pemahaman Bacaan, Lainnya';
        $subTopikTIU = 'Kemampuan Verbal (Analogi, Silogisme, Analitis), Kemampuan Numerik (Berhitung Dasar, Deret, Kuanti Perbandingan, Soal Cerita), Pemecahan Logika Sehari-hari, Lainnya';
        $subTopikTKP = 'Pelayanan Publik, Jejaring Kerja, Sosial Budaya, TIK, Profesionalisme, Anti Radikalisme, Lainnya';

        $pilihan = match ((string) $soal->kategori) {
            'TWK' => $subTopikTWK,
            'TIU' => $subTopikTIU,
            'TKP' => $subTopikTKP,
            default => 'Lainnya',
        };

        $teksBersih = strip_tags(html_entity_decode((string) $soal->teks_soal));

        $prompt = "Tugas Anda adalah membaca cuplikan teks soal CPNS berikut dan tentukan masuk ke sub-topik mana dari pilihan berikut ini saja: [$pilihan].\n\n"
            . "Syarat Mutlak: JAWAB DENGAN NAMA SUB-TOPIK SAJA, persis seperti ejaan aslinya, TANPA KALIMAT PENJELASAN LAIN.\n\n"
            . "Soal:\n{$teksBersih}";

        if ($this->provider === 'ollama_qwen') {
            $url = rtrim((string) config('services.ollama.base_url', env('OLLAMA_BASE_URL', 'https://ollama.rsbaladhikahusada.com')), '/');
            $url .= '/api/chat';
            $model = $this->model ?: (string) config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b'));

            $headers = ['Content-Type' => 'application/json'];
            $clientId = (string) env('OLLAMA_ACCESS_CLIENT_ID', '');
            $clientSecret = (string) env('OLLAMA_ACCESS_CLIENT_SECRET', '');
            if ($clientId !== '' && $clientSecret !== '') {
                $headers['CF-Access-Client-Id'] = $clientId;
                $headers['CF-Access-Client-Secret'] = $clientSecret;
            }

            $res = Http::withOptions(['verify' => false])
                ->connectTimeout(10)
                ->timeout(60)
                ->withHeaders($headers)
                ->post($url, [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'stream' => false,
                    'options' => ['temperature' => 0.1],
                ]);

            if (!$res->successful()) return null;
            $text = (string) ($res->json('message.content') ?? '');
            $text = trim(str_replace(['"', "'", '[', ']', '.', "\n", "\r"], '', $text));
            return $text !== '' ? $text : null;
        }

        // Groq
        $apiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        if ($apiKey === '') return null;
        $model = $this->model ?: (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));

        $res = Http::withOptions(['verify' => false])
            ->connectTimeout(10)
            ->timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.1,
                'max_tokens' => 40,
            ]);

        if (!$res->successful()) return null;
        $text = (string) ($res->json('choices.0.message.content') ?? '');
        $text = trim(str_replace(['"', "'", '[', ']', '.', "\n", "\r"], '', $text));
        return $text !== '' ? $text : null;
    }
}
