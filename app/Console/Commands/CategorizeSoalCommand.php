<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Soal;
use App\Services\AISoalService;
use Illuminate\Support\Facades\Http;

class CategorizeSoalCommand extends Command
{
    protected $signature = 'soal:categorize {--limit=10} {--provider=groq} {--model=}';
    protected $description = 'Kategorikan soal lama yang belum punya sub_topik menggunakan klasifikasi AI';

    public function handle(AISoalService $aiService)
    {
        $limit = (int) $this->option('limit');
        $provider = $this->option('provider');
        $model = $this->option('model');

        $soals = Soal::whereNull('sub_topik')->orWhere('sub_topik', '')->take($limit)->get();

        if ($soals->isEmpty()) {
            $this->info("Semua soal sudah memiliki sub_topik. Database rapi!");
            return;
        }

        $this->info("Menemukan {$soals->count()} soal tanpa sub_topik. Mulai mengklasifikasi via {$provider}...");

        foreach ($soals as $soal) {
            $this->info("Menerawang Soal ID: {$soal->id} (Kategori: {$soal->kategori})");
            
            $subTopik = $this->askAIToCategorize($soal, $provider, $model);
            
            if ($subTopik) {
                // Pastikan output AI tidak terlalu panjang
                $subTopik = substr($subTopik, 0, 100);
                $soal->update(['sub_topik' => $subTopik]);
                $this->line("   -> Diklasifikasikan ke: <fg=green>{$subTopik}</>");
            } else {
                $this->error("   -> Gagal diklasifikasikan (API Error / Rate Limit).");
            }
        }
        
        $this->info("Proses ketegorisasi selesai.");
    }

    private function askAIToCategorize($soal, $provider, $modelChoice)
    {
        $subTopikTWK = 'Nasionalisme & Sejarah Kemerdekaan, Integritas & Anti Korupsi, Bela Negara & Ancaman Radikalisme, Pilar Negara (Pancasila & UUD 1945), Bahasa Indonesia & Pemahaman Bacaan, Lainnya';
        $subTopikTIU = 'Kemampuan Verbal (Analogi, Silogisme, Analitis), Kemampuan Numerik (Berhitung Dasar, Deret, Kuanti Perbandingan, Soal Cerita), Pemecahan Logika Sehari-hari, Lainnya';
        $subTopikTKP = 'Pelayanan Publik, Jejaring Kerja, Sosial Budaya, TIK, Profesionalisme, Anti Radikalisme, Lainnya';

        $pilihan = match($soal->kategori) {
            'TWK' => $subTopikTWK,
            'TIU' => $subTopikTIU,
            'TKP' => $subTopikTKP,
            default => 'Lainnya'
        };

        // Hilangkan tag HTML dari soal
        $teksBersih = strip_tags(html_entity_decode($soal->teks_soal));
        
        $prompt = "Tugas Anda adalah membaca cuplikan teks soal CPNS berikut dan tentukan masuk ke sub-topik mana dari pilihan berikut ini saja: [$pilihan].\n\nSyarat Mutlak: JAWAB DENGAN NAMA SUB-TOPIK SAJA, persis seperti ejaan aslinya, TANPA KALIMAT PENJELASAN LAIN.\n\nSoal:\n{$teksBersih}";

        if ($provider === 'ollama_qwen') {
            $url = rtrim(config('services.ollama.base_url', env('OLLAMA_BASE_URL', 'https://ollama.rsbaladhikahusada.com')), '/');
            $url .= '/api/chat';
            $model = $modelChoice ?: config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b'));
            
            $headers = ['Content-Type' => 'application/json'];
            $clientId = env('OLLAMA_ACCESS_CLIENT_ID');
            $clientSecret = env('OLLAMA_ACCESS_CLIENT_SECRET');

            if ($clientId && $clientSecret) {
                $headers['CF-Access-Client-Id'] = $clientId;
                $headers['CF-Access-Client-Secret'] = $clientSecret;
            }
            
            $response = Http::withOptions(['verify' => false])->withHeaders($headers)
                ->post($url, [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'stream' => false,
                    'options' => ['temperature' => 0.1]
                ]);

            if ($response->successful()) {
                $text = $response->json()['message']['content'] ?? '';
                return trim(str_replace(['"', "'", '[', ']'], '', $text));
            }
        } else {
            // Groq Default
            $apiKey = env('GROQ_API_KEY', config('services.groq.api_key'));
            $model = $modelChoice ?: env('GROQ_MODEL', config('services.groq.model', 'llama-3.3-70b-versatile'));
            $url = "https://api.groq.com/openai/v1/chat/completions";
            
            $response = Http::withOptions(['verify' => false])->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.1,
                'max_tokens' => 40,
            ]);

            if ($response->successful()) {
                $text = $response->json()['choices'][0]['message']['content'] ?? '';
                return trim(str_replace(['"', "'", '[', ']', '.', '\n'], '', $text));
            } else {
                \Illuminate\Support\Facades\Log::error("Groq categorization error", [$response->body()]);
            }
        }

        return null;
    }
}
