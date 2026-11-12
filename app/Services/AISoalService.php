<?php

namespace App\Services;

use App\Models\ExamMode;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Support\ExamStructureSupport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\Response;

class AISoalService
{
    private const ALLOWED_OLLAMA_MODELS = [
        'qwen2.5-coder:7b',
        'deepseek-coder-v2:lite',
        'mistral:latest',
        'llama3.2:3b',
        'codestral:latest',
    ];

    private function resolveDbKategori(string $kategori): string
    {
        $kategori = strtoupper(trim($kategori));
        // Figural is stored as TIU in DB (distinguished by sub_topik/prompt fields).
        if ($kategori === 'FIGURAL') return 'TIU';
        $fallbackMap = [
            'TEKNIS' => 'TIU',
            'SKB_TEKNIS' => 'TIU',
            'SKB_REGULASI' => 'TIU',
            'SKB_STUDI_KASUS' => 'TIU',
            'MANAJERIAL' => 'TKP',
            'SOSIAL_KULTURAL' => 'TKP',
            'WAWANCARA' => 'TKP',
        ];

        try {
            $column = \Illuminate\Support\Facades\DB::selectOne("SHOW COLUMNS FROM soals LIKE 'kategori'");
            $type = strtolower((string) ($column->Type ?? ''));

            if (str_starts_with($type, 'enum(')) {
                preg_match_all("/'([^']+)'/", $type, $matches);
                $allowed = array_map('strtoupper', $matches[1] ?? []);
                if (!in_array($kategori, $allowed, true)) {
                    return $fallbackMap[$kategori] ?? 'TIU';
                }
                return $kategori;
            }
        } catch (\Throwable $e) {
            // Jika inspeksi skema gagal, jangan paksa fallback.
            // Lebih baik simpan kategori asli (DB modern sudah VARCHAR).
        }

        return $kategori;
    }

    private function normalizeOllamaBaseUrl(string $raw): string
    {
        $base = trim((string) $raw);
        if ($base === '') {
            return 'https://ollama.rsbaladhikahusada.com';
        }

        $base = rtrim($base, '/');
        $base = preg_replace('#/api/(tags|chat|ps)$#i', '', $base);
        $base = preg_replace('#/api$#i', '', $base);
        return rtrim((string) $base, '/');
    }

    private function normalizeSoalText($value): string
    {
        $s = (string) ($value ?? '');
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (function_exists('mb_strtolower')) {
            $s = mb_strtolower($s, 'UTF-8');
        } else {
            $s = strtolower($s);
        }
        $s = preg_replace('/\s+/u', ' ', trim($s));
        return $s ?? '';
    }

    private function normalizeJawabanKey($value): string
    {
        $s = strtoupper((string) ($value ?? ''));
        if (preg_match('/[A-E]/', $s, $m)) return $m[0];
        return 'A';
    }

    private function computeHashSoal(array $item, string $kategori): string
    {
        $parts = [
            $this->normalizeSoalText($item['teks_soal'] ?? ''),
            $this->normalizeSoalText($item['opsi_a'] ?? ''),
            $this->normalizeSoalText($item['opsi_b'] ?? ''),
            $this->normalizeSoalText($item['opsi_c'] ?? ''),
            $this->normalizeSoalText($item['opsi_d'] ?? ''),
            $this->normalizeSoalText($item['opsi_e'] ?? ''),
            $kategori,
        ];
        return sha1(implode('|', $parts));
    }

    private function hasGroqFallback(): bool
    {
        $apiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        return $apiKey !== '';
    }

    /**
     * Classify an existing question into one of the allowed categories for the given exam mode.
     * Returns category string (must be in $allowed) or null.
     */
    public function classifyKategoriSoal(Soal $soal, array $allowed, string $modeCode, string $provider = 'groq', ?string $model = null): ?string
    {
        $allowed = array_values(array_unique(array_filter(array_map(function ($v) {
            $s = strtoupper(trim((string) $v));
            return $s !== '' ? $s : null;
        }, $allowed))));
        if (empty($allowed)) return null;

        $modeCode = strtolower(trim((string) $modeCode));
        $provider = $provider === 'ollama_qwen' ? 'ollama_qwen' : 'groq';

        $groqApiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        $groqModel = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));

        $openAiApiKey = preg_replace('/\s+/', '', (string) config('services.openai.api_key', env('OPENAI_API_KEY', '')));
        $openAiModel = (string) config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-mini'));

        $ollamaBaseUrl = $this->normalizeOllamaBaseUrl((string) config('services.ollama.base_url', env('OLLAMA_BASE_URL', 'https://ollama.rsbaladhikahusada.com')));
        $ollamaModel = (string) config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b'));
        $ollamaAccessClientId = (string) config('services.ollama.access_client_id', env('OLLAMA_ACCESS_CLIENT_ID', ''));
        $ollamaAccessClientSecret = (string) config('services.ollama.access_client_secret', env('OLLAMA_ACCESS_CLIENT_SECRET', ''));

        $prompt = "Kamu adalah sistem klasifikasi kategori soal.\n"
            . "Mode ujian: " . strtoupper($modeCode) . "\n"
            . "Pilih tepat 1 kategori dari daftar berikut saja: " . implode(', ', $allowed) . "\n"
            . "Balas JSON valid saja: {\"kategori\":\"<SALAH_SATU_DARI_DAFTAR>\"} tanpa markdown.\n\n"
            . "Soal:\n" . trim((string) $soal->teks_soal) . "\n\n"
            . "Opsi:\n"
            . "A. " . trim((string) $soal->opsi_a) . "\n"
            . "B. " . trim((string) $soal->opsi_b) . "\n"
            . "C. " . trim((string) $soal->opsi_c) . "\n"
            . "D. " . trim((string) $soal->opsi_d) . "\n"
            . "E. " . trim((string) $soal->opsi_e) . "\n\n"
            . "Sub topik (jika ada): " . trim((string) ($soal->sub_topik ?? '')) . "\n";

        try {
            if ($provider === 'ollama_qwen') {
                $url = rtrim($ollamaBaseUrl, '/') . "/api/chat";
                $headers = ['Content-Type' => 'application/json'];
                if ($ollamaAccessClientId !== '' && $ollamaAccessClientSecret !== '') {
                    $headers['CF-Access-Client-Id'] = $ollamaAccessClientId;
                    $headers['CF-Access-Client-Secret'] = $ollamaAccessClientSecret;
                }
                $selectedModel = (is_string($model) && $model !== '') ? $model : $ollamaModel;
                $res = Http::withOptions(['verify' => false])
                    ->connectTimeout(10)
                    ->timeout(75)
                    ->withHeaders($headers)
                    ->post($url, [
                        'model' => $selectedModel,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'stream' => false,
                        'format' => 'json',
                        'keep_alive' => '30m',
                        'options' => [
                            'temperature' => 0.0,
                            'num_predict' => 120,
                        ],
                    ]);

                if ($res->failed()) {
                    if ($this->hasGroqFallback()) {
                        return $this->classifyKategoriSoal($soal, $allowed, $modeCode, 'groq', null);
                    }
                    return null;
                }

                $content = (string) ($res->json('message.content') ?? '');
                $content = preg_replace('/```json|```/i', '', $content);
                $parsed = json_decode(trim($content), true);
                $kat = strtoupper(trim((string) ($parsed['kategori'] ?? '')));
                return in_array($kat, $allowed, true) ? $kat : null;
            }

            if ($provider === 'groq') {
                if ($groqApiKey === '') return null;
                $url = "https://api.groq.com/openai/v1/chat/completions";
                $res = Http::withOptions(['verify' => false])
                    ->connectTimeout(10)
                    ->timeout(45)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $groqApiKey,
                        'Content-Type' => 'application/json',
                    ])->post($url, [
                        'model' => $groqModel,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0.0,
                        'top_p' => 0.9,
                        'response_format' => ['type' => 'json_object'],
                    ]);
                if ($res->failed()) return null;

                $content = (string) ($res->json('choices.0.message.content') ?? '');
                $content = preg_replace('/```json|```/i', '', $content);
                $parsed = json_decode(trim($content), true);
                $kat = strtoupper(trim((string) ($parsed['kategori'] ?? '')));
                return in_array($kat, $allowed, true) ? $kat : null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function jobCacheKey(?string $jobId): ?string
    {
        if (!is_string($jobId) || trim($jobId) === '') return null;
        return 'ai_generate:' . trim($jobId);
    }

    private function persistentGenerateJobPath(?string $jobId): ?string
    {
        if (!is_string($jobId) || trim($jobId) === '') return null;
        return 'ai_generate_jobs/' . trim($jobId) . '.json';
    }

    private function mergePersistentGenerateJob(?string $jobId, array $patch): void
    {
        $path = $this->persistentGenerateJobPath($jobId);
        if (!$path) return;

        $current = [];
        try {
            if (Storage::disk('local')->exists($path)) {
                $decoded = json_decode((string) Storage::disk('local')->get($path), true);
                if (is_array($decoded)) $current = $decoded;
            }
        } catch (\Throwable $e) {
            // ignore persistence read errors
        }

        $merged = array_replace_recursive($current, $patch);
        try {
            Storage::disk('local')->put($path, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            // ignore persistence write errors
        }
    }

    private function pushJobLog(?string $jobId, string $line): void
    {
        $key = $this->jobCacheKey($jobId);
        if (!$key) return;

        $data = Cache::get($key);
        if (!is_array($data)) $data = [];
        $log = $data['log'] ?? [];
        if (!is_array($log)) $log = [];

        $ts = date('H:i:s');
        $log[] = '[' . $ts . '] ' . $line;
        if (count($log) > 25) $log = array_slice($log, -25);
        $data['log'] = $log;
        Cache::put($key, $data, now()->addHour());
        $this->mergePersistentGenerateJob($jobId, ['log' => $log, 'updated_at' => now()->toISOString()]);
    }

    private function setJobProgress(?string $jobId, array $patch): void
    {
        $key = $this->jobCacheKey($jobId);
        if (!$key) return;

        $data = Cache::get($key);
        if (!is_array($data)) $data = [];
        foreach ($patch as $k => $v) {
            $data[$k] = $v;
        }
        Cache::put($key, $data, now()->addHour());
        $patch['updated_at'] = now()->toISOString();
        $this->mergePersistentGenerateJob($jobId, $patch);
    }

    private function extractPdfText(?string $pdfPath, int $limit = 12000): string
    {
        if (!is_string($pdfPath) || trim($pdfPath) === '') return '';
        if (!Storage::disk('local')->exists($pdfPath)) return '';

        try {
            $fullPath = Storage::disk('local')->path($pdfPath);
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($fullPath);
            $text = trim((string) $pdf->getText());
            if ($text === '') return '';

            if (function_exists('mb_substr')) {
                return mb_substr($text, 0, $limit, 'UTF-8');
            }

            return substr($text, 0, $limit);
        } catch (\Throwable $e) {
            Log::error('PDF Parse error: ' . $e->getMessage());
            return '';
        }
    }

    private function detectPdfProfileHeuristically(string $text): array
    {
        $hay = function_exists('mb_strtolower')
            ? mb_strtolower($text, 'UTF-8')
            : strtolower($text);

        $contains = function (array $keywords) use ($hay): int {
            $score = 0;
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($hay, $keyword)) {
                    $score++;
                }
            }
            return $score;
        };

        $modeScores = [
            'skd' => $contains(['skd', 'twk', 'tiu', 'tkp', 'pancasila', 'uud 1945', 'bhinneka', 'silogisme', 'deret angka', 'figural']),
            'skb' => $contains(['skb', 'materi pokok', 'bidang jabatan', 'kompetensi bidang', 'regulasi profesi', 'studi kasus bidang']),
            'pppk' => $contains(['pppk', 'kompetensi teknis', 'manajerial', 'sosial kultural', 'sosio-kultural', 'wawancara']),
        ];

        $explicitPppk = $contains(['pppk']);
        $explicitSkb = $contains(['skb']);
        $explicitSkd = $contains(['skd']);
        if ($explicitPppk > 0) $modeScores['pppk'] += 3;
        if ($explicitSkb > 0) $modeScores['skb'] += 3;
        if ($explicitSkd > 0) $modeScores['skd'] += 3;

        arsort($modeScores);
        $modeCode = (string) array_key_first($modeScores);
        if (($modeScores[$modeCode] ?? 0) <= 0) $modeCode = 'skd';

        $isFarmasi = $contains(['farmasi', 'farmakologi', 'farmakoterapi', 'kefarmasian', 'apoteker', 'resep', 'obat', 'swamedikasi']) > 0;
        $itBasic = $contains(['manajemen informatika', 'database dasar', 'jaringan dasar', 'troubleshooting', 'maintenance', 'sql dasar', 'perawatan perangkat']) > 0;
        $itAdvanced = $contains(['teknik informatika', 'arsitektur aplikasi', 'rekayasa perangkat lunak', 'keamanan siber', 'integrasi sistem', 'cloud computing', 'tata kelola ti']) > 0;
        $itGeneral = $contains(['informatika', 'pemrograman', 'database', 'algoritma', 'jaringan komputer', 'sistem informasi', 'software']) > 0;

        $subCategoryName = null;
        if ($modeCode !== 'skd') {
            if ($isFarmasi) {
                $subCategoryName = 'D3 Farmasi';
            } elseif ($itAdvanced) {
                $subCategoryName = 'S1 Teknik Informatika';
            } elseif ($itBasic || $itGeneral) {
                $subCategoryName = 'D3 Manajemen Informatika';
            }
        }

        $kategori = 'TWK';
        $fokusTopik = null;

        if ($modeCode === 'skd') {
            $skdScores = [
                'TWK' => $contains(['twk', 'pancasila', 'uud 1945', 'nasionalisme', 'bela negara', 'nkri', 'sejarah indonesia', 'integritas', 'anti korupsi']),
                'TIU' => $contains(['tiu', 'silogisme', 'analogi', 'deret', 'aritmetika', 'numerik', 'figural', 'logika', 'kuantitatif']),
                'TKP' => $contains(['tkp', 'pelayanan publik', 'profesionalisme', 'jejaring kerja', 'sosial budaya', 'anti radikalisme', 'studi kasus kerja']),
            ];
            arsort($skdScores);
            $kategori = (string) array_key_first($skdScores);
            if (($skdScores[$kategori] ?? 0) <= 0) $kategori = 'TWK';

            if ($kategori === 'TWK') $fokusTopik = 'Pilar Negara (Pancasila & UUD 1945)';
            if ($kategori === 'TIU') $fokusTopik = 'Kemampuan Numerik (Berhitung Dasar, Deret, Kuanti Perbandingan, Soal Cerita)';
            if ($kategori === 'TKP') $fokusTopik = 'Pelayanan Publik';
        } elseif ($modeCode === 'pppk') {
            $pppkScores = [
                'TEKNIS' => $contains(['kompetensi teknis', 'materi teknis', 'farmasi', 'informatika', 'pemrograman', 'farmakologi', 'database', 'jaringan']),
                'MANAJERIAL' => $contains(['manajerial', 'kepemimpinan', 'pengambilan keputusan', 'kolaborasi', 'orientasi pelayanan']),
                'SOSIAL_KULTURAL' => $contains(['sosial kultural', 'sosio-kultural', 'keberagaman', 'komunikasi publik', 'sensitivitas budaya']),
                'WAWANCARA' => $contains(['wawancara', 'motivasi', 'komitmen pelayanan', 'kesiapan penempatan']),
            ];
            arsort($pppkScores);
            $kategori = (string) array_key_first($pppkScores);
            if (($pppkScores[$kategori] ?? 0) <= 0) $kategori = 'TEKNIS';
            $fokusTopik = $isFarmasi ? 'Farmakologi Dasar' : ($itAdvanced ? 'Analisis dan Desain Sistem' : 'Database SQL Dasar');
        } else {
            $skbScores = [
                'SKB_TEKNIS' => $contains(['teknis', 'materi inti', 'kompetensi bidang', 'pemahaman dasar jabatan', 'pengetahuan spesifik bidang']),
                'SKB_REGULASI' => $contains(['regulasi', 'sop', 'undang-undang', 'aturan', 'standar profesi', 'kebijakan']),
                'SKB_STUDI_KASUS' => $contains(['studi kasus', 'kasus', 'analisis kasus', 'interaksi obat', 'monitoring terapi', 'troubleshooting kasus']),
            ];
            arsort($skbScores);
            $kategori = (string) array_key_first($skbScores);
            if (($skbScores[$kategori] ?? 0) <= 0) $kategori = 'SKB_TEKNIS';
            $fokusTopik = $isFarmasi ? 'Pelayanan Kefarmasian' : ($itAdvanced ? 'Rekayasa Perangkat Lunak' : 'Troubleshooting Aplikasi');
        }

        return [
            'exam_mode_code' => $modeCode,
            'sub_category_name' => $subCategoryName,
            'kategori' => $kategori,
            'fokus_topik' => $fokusTopik,
            'confidence' => 'heuristic',
            'source' => 'heuristic',
        ];
    }

    public function detectGenerateProfileFromPdf(?string $pdfPath, string $provider = 'groq'): array
    {
        $text = $this->extractPdfText($pdfPath, 12000);
        $fallback = $this->detectPdfProfileHeuristically($text);

        if (trim($text) === '') {
            return array_merge($fallback, [
                'reason' => 'PDF kosong atau teks tidak berhasil diekstrak.',
                'pdf_excerpt' => '',
            ]);
        }

        $provider = $provider === 'ollama_qwen' ? 'ollama_qwen' : 'groq';
        $detected = $fallback;

        try {
            if ($provider === 'groq' && $this->hasGroqFallback()) {
                $groqApiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
                $groqModel = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));
                $prompt = "Analisis potongan modul/PDF berikut dan pilih profil generate soal paling cocok.\n"
                    . "Balas JSON valid saja dengan struktur:\n"
                    . "{\"exam_mode_code\":\"skd|skb|pppk\",\"sub_category_name\":\"D3 Farmasi|D3 Manajemen Informatika|S1 Teknik Informatika|null\",\"kategori\":\"TWK|TIU|TKP|FIGURAL|TEKNIS|MANAJERIAL|SOSIAL_KULTURAL|WAWANCARA|SKB_TEKNIS|SKB_REGULASI|SKB_STUDI_KASUS\",\"fokus_topik\":\"...\",\"reason\":\"...\"}\n"
                    . "Aturan:\n"
                    . "1. Pilih sub_category_name null untuk SKD.\n"
                    . "2. Untuk bidang IT pilih salah satu: D3 Manajemen Informatika atau S1 Teknik Informatika.\n"
                    . "3. Jangan pilih kategori lintas mode.\n\n"
                    . "Potongan PDF:\n---\n{$text}\n---";

                $res = Http::withOptions(['verify' => false])
                    ->connectTimeout(10)
                    ->timeout(50)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $groqApiKey,
                        'Content-Type' => 'application/json',
                    ])->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => $groqModel,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0.1,
                        'top_p' => 0.9,
                        'response_format' => ['type' => 'json_object'],
                        'max_tokens' => 350,
                    ]);

                if ($res->successful()) {
                    $content = (string) ($res->json('choices.0.message.content') ?? '');
                    $parsed = json_decode(trim(preg_replace('/```json|```/i', '', $content)), true);
                    if (is_array($parsed)) {
                        $detected = array_merge($detected, [
                            'exam_mode_code' => strtolower(trim((string) ($parsed['exam_mode_code'] ?? $detected['exam_mode_code'] ?? 'skd'))),
                            'sub_category_name' => (($parsed['sub_category_name'] ?? null) === null || trim((string) ($parsed['sub_category_name'] ?? '')) === '') ? null : trim((string) $parsed['sub_category_name']),
                            'kategori' => strtoupper(trim((string) ($parsed['kategori'] ?? $detected['kategori'] ?? 'TWK'))),
                            'fokus_topik' => trim((string) ($parsed['fokus_topik'] ?? $detected['fokus_topik'] ?? '')) ?: null,
                            'reason' => trim((string) ($parsed['reason'] ?? '')),
                            'confidence' => 'ai',
                            'source' => 'groq',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('PDF auto-detect fallback to heuristic: ' . $e->getMessage());
        }

        $modeCode = in_array(($detected['exam_mode_code'] ?? ''), ['skd', 'skb', 'pppk'], true)
            ? (string) $detected['exam_mode_code']
            : 'skd';

        $mode = ExamStructureSupport::modes()->firstWhere('code', $modeCode);
        $examModeId = (int) ($mode->id ?? 0);

        $allowedCategories = ExamStructureSupport::categoryOptions($examModeId, $modeCode)
            ->pluck('value')
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->all();

        $kategori = strtoupper(trim((string) ($detected['kategori'] ?? '')));
        if (!in_array($kategori, $allowedCategories, true)) {
            $kategori = (string) ($allowedCategories[0] ?? ($modeCode === 'skd' ? 'TWK' : ($modeCode === 'pppk' ? 'TEKNIS' : 'SKB_TEKNIS')));
        }

        $subCategoryId = null;
        $subCategoryName = trim((string) ($detected['sub_category_name'] ?? ''));
        if ($modeCode !== 'skd' && $subCategoryName !== '') {
            $sub = ExamStructureSupport::subCategories($examModeId, $modeCode, 'formasi')
                ->first(function ($row) use ($subCategoryName) {
                    return strtolower(trim((string) ($row->name ?? ''))) === strtolower($subCategoryName);
                });
            $subCategoryId = $sub?->id ? (int) $sub->id : null;
            $subCategoryName = $sub?->name ? (string) $sub->name : $subCategoryName;
        } else {
            $subCategoryName = null;
        }

        return [
            'exam_mode_id' => $examModeId ?: null,
            'exam_mode_code' => $modeCode,
            'exam_mode_name' => (string) ($mode->name ?? strtoupper($modeCode)),
            'sub_category_id' => $subCategoryId,
            'sub_category_name' => $subCategoryName,
            'kategori' => $kategori,
            'fokus_topik' => trim((string) ($detected['fokus_topik'] ?? '')) ?: null,
            'reason' => trim((string) ($detected['reason'] ?? '')) ?: 'Profil dipilih otomatis dari isi PDF.',
            'confidence' => (string) ($detected['confidence'] ?? 'heuristic'),
            'source' => (string) ($detected['source'] ?? 'heuristic'),
            'pdf_excerpt' => $text,
        ];
    }

    private function verifyKunciDanPembahasan(
        string $kategori,
        array $item,
        string $provider,
        ?string $model,
        string $groqApiKey,
        string $groqModel,
        string $ollamaBaseUrl,
        string $ollamaModel,
        string $ollamaAccessClientId,
        string $ollamaAccessClientSecret,
        bool $force = false
    ): ?array {
        // TKP doesn't have single absolute correct; keep original + weights logic.
        if ($kategori === 'TKP') return null;

        if (!$force) {
            $verifyEnabledEnv = (string) env('AI_VERIFY_ANSWERS', '');
            $verifyEnabled = $verifyEnabledEnv === '' ? ($provider === 'ollama_qwen') : in_array(strtolower($verifyEnabledEnv), ['1', 'true', 'yes', 'on'], true);
            if (!$verifyEnabled) return null;
        }

        $prompt = "Bertindaklah sebagai Lead Asesor Penguji Naskah CAT CASN BKN Standar Kisi-Kisi Resmi 2027. Diberikan 1 butir soal pilihan ganda kategori {$kategori}. Tentukan jawaban paling tepat dan validasi pembahasannya secara objektif.\n"
            . "WAJIB output JSON murni (tanpa markdown): {\"jawaban_benar\":\"A\",\"pembahasan\":\"...\"}\n"
            . "Pembahasan ringkas 1-3 kalimat (<= 350 karakter memuat alasan kunci tepat dan mengapa opsi lain gugur). Jangan buat soal baru.\n\n"
            . "SOAL:\n" . ($item['teks_soal'] ?? '') . "\n\n"
            . "A. " . ($item['opsi_a'] ?? '') . "\n"
            . "B. " . ($item['opsi_b'] ?? '') . "\n"
            . "C. " . ($item['opsi_c'] ?? '') . "\n"
            . "D. " . ($item['opsi_d'] ?? '') . "\n"
            . "E. " . ($item['opsi_e'] ?? '') . "\n";

        try {
            if ($provider === 'ollama_qwen') {
                $url = $this->normalizeOllamaBaseUrl($ollamaBaseUrl) . "/api/chat";
                $headers = ['Content-Type' => 'application/json'];
                if ($ollamaAccessClientId !== '' && $ollamaAccessClientSecret !== '') {
                    $headers['CF-Access-Client-Id'] = $ollamaAccessClientId;
                    $headers['CF-Access-Client-Secret'] = $ollamaAccessClientSecret;
                }

                $allowedOllamaModels = self::ALLOWED_OLLAMA_MODELS;
                // For verification prefer a stronger model if user picked one, else default env model.
                $selectedModel = (is_string($model) && in_array($model, $allowedOllamaModels, true)) ? $model : $ollamaModel;

                $res = Http::withOptions(['verify' => false])
                    ->connectTimeout(10)
                    ->timeout(120)
                    ->withHeaders($headers)
                    ->post($url, [
                        'model' => $selectedModel,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'stream' => false,
                        'format' => 'json',
                        'keep_alive' => '30m',
                        'options' => [
                            'temperature' => 0.0,
                            'top_p' => 0.9,
                            'repeat_penalty' => 1.1,
                            'num_predict' => 350,
                        ],
                    ]);

                if ($res->failed()) {
                    return $this->hasGroqFallback()
                        ? $this->verifyKunciDanPembahasan($kategori, $item, 'groq', null, $groqApiKey, $groqModel, $ollamaBaseUrl, $ollamaModel, $ollamaAccessClientId, $ollamaAccessClientSecret, true)
                        : null;
                }
                $content = (string) ($res->json()['message']['content'] ?? '');
                $content = preg_replace('/```json|```/', '', $content);
                $parsed = json_decode(trim($content), true);
                if (!is_array($parsed)) {
                    return $this->hasGroqFallback()
                        ? $this->verifyKunciDanPembahasan($kategori, $item, 'groq', null, $groqApiKey, $groqModel, $ollamaBaseUrl, $ollamaModel, $ollamaAccessClientId, $ollamaAccessClientSecret, true)
                        : null;
                }

                $kunci = $this->normalizeJawabanKey($parsed['jawaban_benar'] ?? '');
                $pemb = trim((string) ($parsed['pembahasan'] ?? ''));
                if ($pemb === '') return null;
                return ['jawaban_benar' => $kunci, 'pembahasan' => $pemb];
            }

            if ($provider === 'groq') {
                if ($groqApiKey === '') return null;
                $url = "https://api.groq.com/openai/v1/chat/completions";
                $res = Http::withOptions(['verify' => false])
                    ->connectTimeout(10)
                    ->timeout(60)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $groqApiKey,
                        'Content-Type' => 'application/json',
                    ])->post($url, [
                        'model' => $groqModel,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0.0,
                        'top_p' => 0.9,
                        'response_format' => ['type' => 'json_object']
                    ]);
                if ($res->failed()) return null;

                $content = (string) ($res->json()['choices'][0]['message']['content'] ?? '');
                $content = preg_replace('/```json|```/', '', $content);
                $parsed = json_decode(trim($content), true);
                if (!is_array($parsed)) return null;

                $kunci = $this->normalizeJawabanKey($parsed['jawaban_benar'] ?? '');
                $pemb = trim((string) ($parsed['pembahasan'] ?? ''));
                if ($pemb === '') return null;
                return ['jawaban_benar' => $kunci, 'pembahasan' => $pemb];
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Audit an existing question: ask AI to determine the best answer + short explanation,
     * then compare with DB key and check pembahasan quality.
     */
    public function auditExistingSoal(Soal $soal, string $provider = 'ollama_qwen', ?string $model = null): array
    {
        $kategori = (string) $soal->kategori;
        $dbKey = $this->normalizeJawabanKey($soal->jawaban_benar);

        // Basic pembahasan quality check.
        $pemb = trim((string) ($soal->pembahasan ?? ''));
        $pembOk = ($pemb !== '' && $pemb !== 'Pembahasan tidak tersedia.' && mb_strlen($pemb) >= 18);

        if ($kategori === 'TKP') {
            // TKP: no single absolute answer; only flag pembahasan missing.
            return [
                'status' => $pembOk ? 'match' : 'invalid',
                'db_key' => $dbKey !== '' ? $dbKey : null,
                'ai_key' => null,
                'pembahasan_ok' => $pembOk,
                'needs_revision' => false,
                'revision_reason' => null,
                'proposed' => null,
                'ai_pembahasan' => null,
                'notes' => $pembOk ? 'TKP: skip kunci audit (subjektif).' : 'TKP: pembahasan kosong/kurang.',
            ];
        }

        $groqApiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        $groqModel = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));
        $ollamaBaseUrl = $this->normalizeOllamaBaseUrl((string) config('services.ollama.base_url', env('OLLAMA_BASE_URL', 'https://ollama.rsbaladhikahusada.com')));
        $ollamaModel = (string) config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b'));
        $ollamaAccessClientId = (string) config('services.ollama.access_client_id', env('OLLAMA_ACCESS_CLIENT_ID', ''));
        $ollamaAccessClientSecret = (string) config('services.ollama.access_client_secret', env('OLLAMA_ACCESS_CLIENT_SECRET', ''));

        $item = [
            'teks_soal' => $soal->teks_soal,
            'opsi_a' => $soal->opsi_a,
            'opsi_b' => $soal->opsi_b,
            'opsi_c' => $soal->opsi_c,
            'opsi_d' => $soal->opsi_d,
            'opsi_e' => $soal->opsi_e,
        ];

        $verified = $this->verifyKunciDanPembahasan(
            $kategori,
            $item,
            $provider,
            $model,
            $groqApiKey,
            $groqModel,
            $ollamaBaseUrl,
            $ollamaModel,
            $ollamaAccessClientId,
            $ollamaAccessClientSecret,
            true
        );

        if (!is_array($verified)) {
            return [
                'status' => 'invalid',
                'db_key' => $dbKey !== '' ? $dbKey : null,
                'ai_key' => null,
                'pembahasan_ok' => $pembOk,
                'needs_revision' => false,
                'revision_reason' => null,
                'proposed' => null,
                'ai_pembahasan' => null,
                'notes' => 'AI audit gagal (format/timeout).',
            ];
        }

        $aiKey = $this->normalizeJawabanKey($verified['jawaban_benar'] ?? '');
        $aiPemb = trim((string) ($verified['pembahasan'] ?? ''));
        $status = ($dbKey !== '' && $aiKey !== '' && $dbKey === $aiKey) ? 'match' : 'mismatch';

        $needsRevision = false;
        $revisionReason = null;
        $proposed = null;

        // Heuristic: pembahasan indicates no option matches (needs full revision)
        $hint = ($aiPemb . "\n" . (string) ($verified['notes'] ?? ''));
        if ($status === 'mismatch' && preg_match('/tidak ada jawaban|tidak ada di (opsi|pilihan)|paling dekat|opsi tidak memuat|tidak tersedia di opsi/i', $hint)) {
            $needsRevision = true;
            $revisionReason = 'AI mendeteksi jawaban tidak ada di opsi (perlu revisi opsi/soal).';
        }

        if ($needsRevision) {
            $proposal = $this->suggestFullRevision($kategori, $soal, $provider, $model);
            if (is_array($proposal) && !empty($proposal['proposed'])) {
                $proposed = $proposal['proposed'];
                $revisionReason = $proposal['reason'] ?? $revisionReason;
            }
        }

        return [
            'status' => $status,
            'db_key' => $dbKey !== '' ? $dbKey : null,
            'ai_key' => $aiKey !== '' ? $aiKey : null,
            'pembahasan_ok' => $pembOk,
            'needs_revision' => (bool) $needsRevision,
            'revision_reason' => $revisionReason,
            'proposed' => $proposed,
            'ai_pembahasan' => $aiPemb !== '' ? $aiPemb : null,
            'notes' => $needsRevision ? 'Perlu revisi penuh (soal/opsi) sebelum diterapkan.' : ($status === 'match' ? 'Kunci cocok.' : 'Kunci berbeda (AI vs DB).'),
        ];
    }

    private function suggestFullRevision(string $kategori, Soal $soal, string $provider, ?string $model): ?array
    {
        // Only for objective categories.
        if (!in_array($kategori, ['TWK', 'TIU'], true)) return null;

        $groqApiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        $groqModel = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));

        $payload = [
            'kategori' => $kategori,
            'teks_soal' => (string) $soal->teks_soal,
            'opsi_a' => (string) $soal->opsi_a,
            'opsi_b' => (string) $soal->opsi_b,
            'opsi_c' => (string) $soal->opsi_c,
            'opsi_d' => (string) $soal->opsi_d,
            'opsi_e' => (string) $soal->opsi_e,
            'jawaban_db' => (string) $soal->jawaban_benar,
            'pembahasan_db' => (string) ($soal->pembahasan ?? ''),
        ];

        $prompt = "Anda adalah validator soal CPNS.\n"
            . "Tugas: cek apakah ada 1 jawaban yang benar dan NILAINYA benar-benar ada di opsi A-E.\n"
            . "Jika jawaban benar tidak ada di opsi (mis. pembahasan menyebut angka yang tidak ada), maka needs_revision=true dan buat revisi MINIMAL agar konsisten.\n"
            . "Jika sudah konsisten, needs_revision=false.\n\n"
            . "WAJIB output 1 JSON object saja tanpa markdown:\n"
            . "{\n"
            . "  \"needs_revision\": true|false,\n"
            . "  \"reason\": \"...\",\n"
            . "  \"proposed\": {\n"
            . "    \"teks_soal\": \"...\",\n"
            . "    \"opsi_a\": \"...\",\n"
            . "    \"opsi_b\": \"...\",\n"
            . "    \"opsi_c\": \"...\",\n"
            . "    \"opsi_d\": \"...\",\n"
            . "    \"opsi_e\": \"...\",\n"
            . "    \"jawaban_benar\": \"A\",\n"
            . "    \"pembahasan\": \"...\"\n"
            . "  }\n"
            . "}\n\n"
            . "DATA SOAL:\n" . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            if ($provider !== 'groq' || $groqApiKey === '') return null;

            $res = Http::withOptions(['verify' => false])
                ->connectTimeout(10)
                ->timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $groqApiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $groqModel,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Balas hanya JSON valid tanpa teks lain.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.0,
                    'top_p' => 0.9,
                    'max_tokens' => 1800,
                ]);
            if ($res->failed()) return null;

            $raw = (string) ($res->json()['choices'][0]['message']['content'] ?? '');
            $data = $this->decodeJsonObject($raw);
            if (!is_array($data)) return null;

            $needs = (bool) ($data['needs_revision'] ?? false);
            $reason = trim((string) ($data['reason'] ?? ''));
            $prop = is_array($data['proposed'] ?? null) ? $data['proposed'] : null;
            if (!$needs || !$prop) return ['needs_revision' => false, 'reason' => $reason, 'proposed' => null];

            $k = $this->normalizeJawabanKey($prop['jawaban_benar'] ?? '');
            $pemb = trim((string) ($prop['pembahasan'] ?? ''));
            $out = [
                'teks_soal' => trim((string) ($prop['teks_soal'] ?? '')),
                'opsi_a' => trim((string) ($prop['opsi_a'] ?? '')),
                'opsi_b' => trim((string) ($prop['opsi_b'] ?? '')),
                'opsi_c' => trim((string) ($prop['opsi_c'] ?? '')),
                'opsi_d' => trim((string) ($prop['opsi_d'] ?? '')),
                'opsi_e' => trim((string) ($prop['opsi_e'] ?? '')),
                'jawaban_benar' => $k,
                'pembahasan' => $pemb,
            ];

            return [
                'needs_revision' => true,
                'reason' => $reason !== '' ? $reason : 'Perlu revisi penuh agar konsisten.',
                'proposed' => $out,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Generate soal dengan fitur MULTI-KEY ROTATION dan ANTI-DUPLIKASI (HASH).
     */
    public function generateSoal($kategori, $jumlah = 5, $provider = 'groq', $model = null, $pdfPath = null, $fokusTopik = null, $jobId = null, $examModeId = null, $subCategoryId = null, $difficulty = 'medium', $createdBy = null)
    {
        // ==========================================
        // 1. KONFIGURASI PROVIDER / API KEY
        // ==========================================

        // NOTE: Jangan hardcode API key di source code. Isi lewat `.env` / `config/services.php`.

        $groqApiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        $groqModel = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));

        $openAiApiKey = preg_replace('/\s+/', '', (string) config('services.openai.api_key', env('OPENAI_API_KEY', '')));
        $openAiModel = (string) config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-mini'));

        $ollamaBaseUrl = $this->normalizeOllamaBaseUrl((string) config('services.ollama.base_url', env('OLLAMA_BASE_URL', 'https://ollama.rsbaladhikahusada.com')));
        $ollamaModel = (string) config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b'));
        $ollamaAccessClientId = (string) config('services.ollama.access_client_id', env('OLLAMA_ACCESS_CLIENT_ID', ''));
        $ollamaAccessClientSecret = (string) config('services.ollama.access_client_secret', env('OLLAMA_ACCESS_CLIENT_SECRET', ''));

        $examMode = null;
        $subCategory = null;
        if (Schema::hasTable('exam_modes') && !empty($examModeId)) {
            $examMode = ExamMode::query()->find((int) $examModeId);
        }
        if (!$examMode && Schema::hasTable('exam_modes')) {
            $examMode = ExamMode::defaultSkd();
        }
        if (!empty($subCategoryId) && $examMode && Schema::hasTable('sub_categories')) {
            $subCategory = SubCategory::query()
                ->where('exam_mode_id', $examMode->id)
                ->find((int) $subCategoryId);
        }

        // ==========================================
        // 2. PROMPT CHAIN OF THOUGHT KETAT
        // ==========================================

                $subTopikTWK = [
            'TWK - Nasionalisme & Integritas: Nilai Kejujuran, Etika Pelayanan Publik & Pencegahan Benturan Kepentingan di Birokrasi',
            'TWK - Bela Negara: Peran Warga Negara Hadapi Ancaman Siber, Disinformasi, Perang Asimetris & Kedaulatan Digital/Ekonomi',
            'TWK - Pilar Negara: Penalaran Filosofis Pancasila (Studi Kasus Pengamalan Sila) & Harmonisasi Bhinneka Tunggal Ika',
            'TWK - Pilar Negara: Konstruksi Konstitusi UUD NRI 1945 & Wawasan Kebangsaan NKRI',
            'TWK - Bahasa Indonesia Kedinasan: Kaidah EYD V Terbaru, Koherensi Telaah Naskah Dinas & Ketepatan Diksi Logis',
        ];
        $subTopikTIU = [
            'TIU - Kemampuan Verbal: Analogi Pasangan Kata Tingkat Lanjut (Relasi Fungsi & Sebab-Akibat Kompleks)',
            'TIU - Kemampuan Verbal: Silogisme Logis 2-3 Premis (Kuantor Universal & Partikular)',
            'TIU - Kemampuan Verbal: Penalaran Analitis Kondisi Bersyarat (Urutan Meja, Jadwal & Kriteria Eliminasi)',
            'TIU - Kemampuan Numerik: Berhitung Cepat (Pecahan, Desimal, Persentase Terapan)',
            'TIU - Kemampuan Numerik: Deret Angka Pola Bertingkat, Kombinasi Ganda & Deret Fibonacci/Huruf',
            'TIU - Kemampuan Numerik: Perbandingan Kuantitatif Menentukan Hubungan Nilai X dan Y dari Persamaan Bersyarat',
            'TIU - Kemampuan Numerik: Analisis Data Tabel & Grafik (Perhitungan Persentase, Rasio, Perbandingan & Selisih Data Tabel)',
            'TIU - Kemampuan Numerik: Soal Cerita Aljabar Terapan, Kecepatan-Jarak-Waktu Berpapasan/Menyusul & Peluang',
            'TIU - Kemampuan Figural: Analogi Pola Gambar, Serial Perubahan Elemen/Rotasi Sudut & Konsistensi Spasial',
        ];
        $subTopikTKP = [
            'TKP - Pelayanan Publik: Orientasi Kepuasan Masyarakat, Empati Prima & Kepatuhan SOP Tegas',
            'TKP - Jejaring Kerja: Kolaborasi Antar-Instansi, Kepemimpinan Inklusif & Resolusi Konflik Lintas Unit',
            'TKP - Sosial Budaya: Toleransi Keberagaman & Adaptasi Kultur Kerja Birokrasi Heterogen',
            'TKP - Teknologi Informasi & Komunikasi: Digital Mindset Birokrasi, Automasi Data, Keamanan Informasi & SPBE',
            'TKP - Profesionalisme: Akuntabilitas Kinerja Tinggi, Integritas Jabatan & Manajemen Waktu di Bawah Tekanan',
            'TKP - Anti-Radikalisme: Deteksi Dini Sikap Intoleran & Loyalitas Penuh terhadap Ideologi Pancasila & NKRI',
        ];
        $subTopikFigural = ['Figural (Analogi Gambar)', 'Figural (Ketidaksamaan Gambar)', 'Figural (Serial/Pola Berlanjut)', 'Figural (Rotasi 3D)', 'Figural (Jaring-jaring Kubus)'];
        $subTopikTeknis = [
            'SKB Teknis 30%: Regulasi Sektoral Nasional, Tugas Pokok & Fungsi Jabatan Fungsional Terkait',
            'SKB Teknis 70%: Prosedur Teknis Operasional, Telaah Kasus & Investigasi Masalah Lapangan',
            'SKB Teknis: Penerapan Alat Kerja, SOP Lapangan & Penegakan Standar Mutu Layanan',
            'SKB Teknis: Mitigasi Risiko Teknis, Evaluasi Kinerja & Etika Profesi Formasi',
        ];
        $subTopikManajerial = ['Integritas Birokrasi', 'Kerjasama Tim Lintas Sektor', 'Pengambilan Keputusan Berbasis Analisis', 'Orientasi Pelayanan Publik Prima', 'Pengelolaan Perubahan & Adaptabilitas'];
        $subTopikSosial = ['Perekat Bangsa & Toleransi Sosial', 'Pelayanan Inklusif Ramah Keberagaman', 'Etika Profesi & Netralitas Aparatur', 'Komunikasi Publik & Kepekaan Budaya'];
        $subTopikWawancara = ['Integritas Moralitas ASN', 'Komitmen Loyalitas Pelayanan Publik', 'Motivasi Berprestasi & Etika Kerja', 'Kesiapan Penempatan Seluruh Wilayah NKRI'];
        $subTopikSkb = [
            'SKB 30% Umum: Regulasi Sektoral, Kebijakan Nasional & Etika Profesi',
            'SKB 70% Khusus: Prosedur Teknis Operasional & Pemecahan Masalah Lapangan',
            'SKB: Investigasi Kasus Lapangan, Analisis Solutif & Penegakan SOP',
            'SKB: Implementasi Standar Layanan Spesifik Formasi Jabatan',
        ];
        $subCategoryLabel = $subCategory?->name ?: null;
        $farmasiTopikKhusus = ['Pelayanan Kefarmasian Dasar', 'Penyiapan dan Peracikan Obat', 'Farmakologi Dasar', 'Farmakoterapi Dasar', 'Farmasi Klinis', 'Pelayanan Informasi Obat', 'Resep dan Dosis', 'Penyimpanan Obat', 'Etika Pelayanan Farmasi'];
        $itTerampilTopikKhusus = ['Dasar Pemrograman', 'Database SQL Dasar', 'Jaringan Komputer Dasar', 'Perawatan Perangkat TI', 'Troubleshooting Aplikasi', 'Analisis Kebutuhan Sistem Sederhana', 'Keamanan Informasi Dasar', 'Dokumentasi Teknis'];
        $itAhliTopikKhusus = ['Analisis dan Desain Sistem', 'Rekayasa Perangkat Lunak', 'Arsitektur Aplikasi', 'Keamanan Siber', 'Basis Data Lanjut', 'Integrasi Sistem', 'Cloud Computing', 'Tata Kelola TI'];
        $isFarmasiContext = (bool) preg_match('/farmasi/i', (string) ($subCategoryLabel ?? ''));
        $isItTerampilContext = (bool) preg_match('/d3 manajemen informatika|manajemen informatika d3/i', (string) ($subCategoryLabel ?? ''));
        $isItAhliContext = (bool) preg_match('/s1 teknik informatika|teknik informatika s1/i', (string) ($subCategoryLabel ?? ''));
        $formasiDirective = match (true) {
            $isFarmasiContext => 'Gunakan konteks D3 Farmasi secara konsisten. Hindari topik pemrograman, jaringan, audit TI, cloud, atau software engineering.',
            $isItTerampilContext => 'Gunakan konteks D3 Manajemen Informatika yang bersifat terampil dan operasional. Fokus pada troubleshooting, jaringan dasar, database dasar, maintenance, dokumentasi, dan dukungan sistem. Hindari topik farmasi dan hindari arsitektur enterprise tingkat ahli.',
            $isItAhliContext => 'Gunakan konteks S1 Teknik Informatika yang bersifat analitis dan profesional. Fokus pada rekayasa perangkat lunak, desain sistem, arsitektur, keamanan, integrasi, dan tata kelola TI. Hindari topik farmasi dan hindari soal operasional sederhana yang khas level terampil.',
            default => '',
        };

        $selectedSubTopik = $fokusTopik ?: match ($kategori) {
            'TWK' => $subTopikTWK[array_rand($subTopikTWK)],
            'TIU' => $subTopikTIU[array_rand($subTopikTIU)],
            'TKP' => $subTopikTKP[array_rand($subTopikTKP)],
            'FIGURAL' => $subTopikFigural[array_rand($subTopikFigural)],
            'TEKNIS' => ($isFarmasiContext
                ? $farmasiTopikKhusus[array_rand($farmasiTopikKhusus)]
                : ($isItTerampilContext
                    ? $itTerampilTopikKhusus[array_rand($itTerampilTopikKhusus)]
                    : ($isItAhliContext
                        ? $itAhliTopikKhusus[array_rand($itAhliTopikKhusus)]
                        : $subTopikTeknis[array_rand($subTopikTeknis)]))),
            'MANAJERIAL' => $subTopikManajerial[array_rand($subTopikManajerial)],
            'SOSIAL_KULTURAL' => $subTopikSosial[array_rand($subTopikSosial)],
            'WAWANCARA' => $subTopikWawancara[array_rand($subTopikWawancara)],
            'SKB_TEKNIS', 'SKB_REGULASI', 'SKB_STUDI_KASUS' => ($isFarmasiContext
                ? $farmasiTopikKhusus[array_rand($farmasiTopikKhusus)]
                : ($isItTerampilContext
                    ? $itTerampilTopikKhusus[array_rand($itTerampilTopikKhusus)]
                    : ($isItAhliContext
                        ? $itAhliTopikKhusus[array_rand($itAhliTopikKhusus)]
                        : $subTopikSkb[array_rand($subTopikSkb)]))),
            default => ""
        };

        // Simpan ke kolom kategori lama secara aman untuk kompatibilitas DB lama/hosting lama.
        $dbKategori = $this->resolveDbKategori($kategori);

        $isFigural = ($kategori === 'FIGURAL' || ($kategori === 'TIU' && preg_match('/Figural|Spasial|Gambar|Visual|Pola/i', $selectedSubTopik)));
        $examModeLabel = $examMode?->name ?: 'SKD';
        $difficultyLabel = in_array($difficulty, ['easy', 'medium', 'hard'], true) ? $difficulty : 'medium';
        
        $instruksiSpesifik = match ($kategori) {
            'TWK' => "STANDAR RESMI CASN 2027 - SKD TWK (Passing Grade: 65, Skor Benar: 5, Salah/Kosong: 0):
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: WAJIB bertipe High Order Thinking Skills (HOTS) berbasis narasi studi kasus riil (problem-solving) kebangsaan, etika birokrasi, pencegahan benturan kepentingan, ancaman kedaulatan digital/siber, atau penalaran konstitusi UUD NRI 1945 & nilai Pancasila.
PANTANGAN KERAS: DILARANG membuat soal hafalan pasal mentah, angka/nomor UU, atau tanggal sejarah tanpa konteks masalah.",

            'TIU' => "STANDAR RESMI CASN 2027 - SKD TIU (Passing Grade: 80, Skor Benar: 5, Salah/Kosong: 0):
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu & Pedoman Format Khusus:
1. TABEL DATA (Jika topik Tabel/Data): Sajikan data menggunakan tabel Markdown yang rapi (| Kolom 1 | Kolom 2 | ...) di teks_soal, diikuti pertanyaan analitis tuntas (persentase, rasio, selisih).
2. ANALOGI KATA (Jika topik Analogi): Gunakan format relasi presisi 'KATA1 : KATA2 = KATA3 : ?' (atau KATA3 : [opsi]).
3. SILOGISME (Jika topik Silogisme): Gunakan format standar 'Premis 1: ...\\nPremis 2: ...\\nKesimpulan yang tepat adalah...'.
4. DERET ANGKA/HURUF: Gunakan deret dengan pemisah koma 'a, b, c, d, ...'.
5. PERBANDINGAN KUANTITATIF: Sajikan persamaan variabel 'x = ... dan y = ... Tentukan hubungan nilai x dan y!'.
Kunci jawaban WAJIB mutlak tunggal dan terbukti secara deduktif matematis/logika formal tanpa multi-tafsir.",

            'TKP' => "STANDAR RESMI CASN 2027 - SKD TKP (Passing Grade: 166, Skor Bertingkat 1 s/d 5):
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Berbentuk Situational Judgment Test (SJT) dengan dilema dilematis di tempat kerja ASN birokrasi modern (Pelayanan Publik, Jejaring Kerja, Sosial Budaya, TIK/SPBE, Profesionalisme, Anti-Radikalisme).
Seluruh opsi (A–E) tampak positif dan logis, namun memiliki gradasi kematangan emosi, efektivitas tindakan, integritas, dan kepatuhan regulasi.
AI WAJIB menyertakan 'bobot_tkp' dengan skor unik 1, 2, 3, 4, 5 untuk masing-masing opsi A, B, C, D, E. Opsi bernilai 5 otomatis menjadi kunci jawaban terbaik.",

            'FIGURAL' => "STANDAR RESMI CASN 2027 - SKD TIU FIGURAL:
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Puzzle logika visual (analogi gambar, serial rotasi elemen/sudut, atau ketidaksamaan) yang dideskripsikan secara presisi lewat prompt gambar.",

            'TEKNIS' => "STANDAR RESMI CASN 2027 - SKB TEKNIS FORMASI (Skor Benar: 5, Salah/Kosong: 0):
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Mengadopsi komposisi BKN 2027: 30% regulasi sektoral & tupoksi umum, 70% prosedur teknis operasional & pemecahan kasus nyata lapangan formasi.",

            'MANAJERIAL' => "STANDAR KOMPETENSI MANAJERIAL ASN 2027:
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Skenario kerja birokrasi yang menguji integritas, kerjasama tim, komunikasi, orientasi hasil, dan adaptasi perubahan.",

            'SOSIAL_KULTURAL' => "STANDAR KOMPETENSI SOSIAL KULTURAL ASN 2027:
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Skenario pelayanan publik masyarakat majemuk yang menguji kepekaan budaya, empati, inklusivitas, dan peran perekat NKRI.",

            'WAWANCARA' => "STANDAR WAWANCARA KEPRIBADIAN ASN 2027:
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Pertanyaan situasi reflektif integritas moral, komitmen pelayanan publik, dan loyalitas pada negara.",

            'SKB_TEKNIS' => "STANDAR SKB KOMPETENSI KHUSUS 2027 (70% Bobot BKN):
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Prosedur operasional teknis, implementasi instrumen kerja, investigasi masalah, dan penegakan SOP formasi.",

            'SKB_REGULASI' => "STANDAR SKB KOMPETENSI UMUM 2027 (30% Bobot BKN):
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Regulasi sektoral nasional, etika profesi, tupoksi fungsional, dan kepatuhan hukum teknis formasi.",

            'SKB_STUDI_KASUS' => "STANDAR SKB STUDI KASUS OPERASIONAL 2027:
Topik Materi 2027: {$selectedSubTopik}.
Karakteristik Mutu: Kasus pemecahan masalah (troubleshooting/kasus pasien/audit) yang menuntut analisis data dan ketepatan keputusan teknis.",

            default => "Topik spesifik: {$selectedSubTopik}. Standar regulasi resmi seleksi CASN 2027 (KemenPAN-RB & BKN), objektif, mendalam, dan berkualitas tinggi."
        };

        $instruksiMode = "Mode ujian aktif: {$examModeLabel}. Tingkat kesulitan target: {$difficultyLabel}.";
        if ($subCategoryLabel) {
            $instruksiMode .= " Sub kategori aktif: {$subCategoryLabel}. Selaraskan konteks soal dengan sub kategori ini tanpa mengubah format kategori dasar {$dbKategori}.";
        } elseif ($examMode && strtolower((string) $examMode->code) !== 'skd') {
            $instruksiMode .= " Selaraskan konteks soal dengan karakter mode ini sambil tetap menjaga format kategori dasar {$dbKategori}.";
        }
        $instruksiSpesifik = trim($instruksiMode . ' ' . $formasiDirective . ' ' . $instruksiSpesifik);

        if ($kategori === 'TKP') {
            $jsonStructure = "{ \"soal\": [ { \"sub_topik\": \"...\", \"teks_soal\": \"...\", \"opsi_a\": \"...\", \"opsi_b\": \"...\", \"opsi_c\": \"...\", \"opsi_d\": \"...\", \"opsi_e\": \"...\", \"jawaban_benar\": \"C\", \"bobot_tkp\": { \"A\": 4, \"B\": 2, \"C\": 5, \"D\": 3, \"E\": 1 }, \"pembahasan\": \"Analisis gradasi bobot nilai: Opsi C bernilai 5 karena paling solutif, etis, dan akuntabel. Opsi A (4) ..., Opsi D (3) ..., Opsi B (2) ..., Opsi E (1) ...\" } ] }";
        } elseif ($isFigural) {
            $instruksiSpesifik .= " 
            STRATEGI KHUSUS SOAL FIGURAL:
            1. Teks Soal: Sangat singkat (Contoh: 'Manakah gambar yang melengkapi pola berikut?' atau 'Pilih satu gambar yang paling berbeda.').
            2. Teks Opsi A-E: Biarkan deskripsi sangat singkat (Contoh: 'Opsi A', 'Opsi B', dst) karena yang penting adalah visualnya.
            3. PROMPT GENERATOR (6 BUAH):
               - prompt_gambar_soal: Deskripsi sangat mendalam (elemen, garis, warna, sudut, posisi) dari 'soal' asli yang menjadi teka-tekinya.
               - prompt_gambar_a s/d e: Deskripsi visual pilihan jawaban. Salah satunya harus matching secara logis dengan pola soal sebagai jawaban benar.
               - WAJIB instruksikan desainer untuk MENAMBAHKAN HURUF 'A', 'B', 'C', 'D', atau 'E' besar dengan jelas di dalam tiap file gambar opsi agar tidak tertukar.";

            $jsonStructure = "{ \"soal\": [ { \"sub_topik\": \"...\", \"teks_soal\": \"...\", \"opsi_a\": \"Opsi A\", \"opsi_b\": \"Opsi B\", \"opsi_c\": \"Opsi C\", \"opsi_d\": \"Opsi D\", \"opsi_e\": \"Opsi E\", \"jawaban_benar\": \"A\", \"pembahasan\": \"...\", \"prompt_gambar_soal\": \"[Deskripsi visual sangat teknis dan presisi...]\", \"prompt_gambar_a\": \"[Deskripsi visual opsi A + Huruf A...]\", \"prompt_gambar_b\": \"[Deskripsi visual opsi B + Huruf B...]\", \"prompt_gambar_c\": \"[Deskripsi visual opsi C + Huruf C...]\", \"prompt_gambar_d\": \"[Deskripsi visual opsi D + Huruf D...]\", \"prompt_gambar_e\": \"[Deskripsi visual opsi E + Huruf E...]\" } ] }";
        } else {
            $jsonStructure = "{ \"soal\": [ { \"sub_topik\": \"...\", \"teks_soal\": \"...\", \"opsi_a\": \"...\", \"opsi_b\": \"...\", \"opsi_c\": \"...\", \"opsi_d\": \"...\", \"opsi_e\": \"...\", \"jawaban_benar\": \"A\", \"pembahasan\": \"Penjelasan komprehensif memuat: (1) Inti pokok masalah, (2) Landasan hukum/teori/rumus yang berlaku, (3) Alasan opsi benar tepat, dan (4) Analisis mengapa opsi lain gugur/kurang tepat.\" } ] }";
        }

        if ($pdfPath) {
            $pdfText = $this->extractPdfText($pdfPath, 10000);
            if ($pdfText !== '') {
                $instruksiSpesifik .= "\n\nPENTING JANGAN DIABAIKAN: Buatlah soal DENGAN REFERENSI UTAMA secara eksklusif dari dokumen/modul berikut ini:\n---\n{$pdfText}\n---\n";
            }
        }

        $promptTemplate = "Bertindaklah sebagai Senior Psychometrician & Lead Asesor Penyusun Bank Soal CASN BKN Republik Indonesia. Tugas Anda adalah memproduksi %d butir naskah soal seleksi CAT yang sepenuhnya mengadopsi standar regulasi dan kisi-kisi resmi CASN 2027 (KemenPAN-RB & BKN) dengan Protokol Zero-Defect & Reverse-Solving (Anti-Kesalahan).
Kategori Target: {$kategori}.
Instruksi Kisi-Kisi 2027:
{$instruksiSpesifik}

PROTOKOL ZERO-DEFECT & STANDAR MUTU SOAL 2027:
1. METODE REVERSE-SOLVING (Kunci Dihitung Dahulu): Untuk soal Numerik, Aljabar, Silogisme, atau Penalaran Analitis, selesaikan dan buktikan perhitungannya secara matematis/logika terlebih dahulu. Pastikan hasilnya bilangan rasional tuntas tanpa koma tak berujung, baru kemudian susun narasi soal cerita berdasarkan variabel terbukti tersebut.
2. ELIMINASI AMBIGUITAS DISTRAKTOR: 4 opsi salah harus dirancang berdasarkan kesalahan umum (predictable misconception), namun terbukti 100% salah dan digugurkan secara objektif pada pembahasan. Kunci jawaban wajib tunggal dan mutlak benar.
3. UNTUK TWK: Wajib tipe High Order Thinking Skills (HOTS) berbasis studi kasus pemecahan masalah (integritas birokrasi, pencegahan korupsi/benturan kepentingan, bela negara siber & kedaulatan digital, penalaran konstitusi). DILARANG membuat hafalan nomor pasal mentah atau tanggal.
4. UNTUK TKP: Format Situational Judgment Test (SJT) dilema dunia kerja ASN dengan gradasi kematangan emosi/profesionalisme. Wajib pembobotan 1 sampai 5 unik pada masing-masing opsi A-E tanpa ada skor kembar. Opsi skor 5 adalah kunci jawaban.
5. UNTUK SKB TEKNIS: Menyelaraskan 30% regulasi sektoral/tupoksi umum dan 70% prosedur teknis operasional & SOP lapangan formasi.
6. PEMBAHASAN KOMPREHENSIF: Wajib memuat (1) Inti pokok masalah, (2) Rumus/landasan hukum yang berlaku, (3) Pembuktian mengapa kunci benar, dan (4) Analisis mengapa opsi lain gugur/kurang tepat.
7. BAHASA BAKU: Menggunakan Bahasa Indonesia kedinasan baku sesuai EYD V, kalimat efektif, tanpa typo.

WAJIB OUTPUT JSON MURNI (tanpa markdown tambahan di luar blok json, tanpa teks pembuka/penutup):
{$jsonStructure}";

        if ($provider === 'ollama_qwen') {
            $promptTemplate = "Bertindak sebagai Asesor Bank Soal CAT ASN BKN Standar CASN 2027. Buat %d butir soal {$kategori} dalam JSON valid murni.
Konteks: {$instruksiMode}
Kisi-Kisi 2027: {$selectedSubTopik}.
{$instruksiSpesifik}
Aturan Mutu 2027:
1. Narasi kontekstual profesional gaya HOTS/SJT berstandar BKN 2027, tanpa typo, opsi A-E lengkap.
2. Pembahasan tajam memuat alasan kunci jawaban dan eliminasi opsi lain.
3. Output HANYA JSON murni persis sesuai struktur ini:
{$jsonStructure}";
        }

        // Untuk Ollama di balik Cloudflare, request panjang sering kena HTTP 524 (timeout ~100 detik).
        // Solusi: generate per-soal (chunk kecil) supaya tiap request cepat selesai.
        $chunkSize = ($provider === 'ollama_qwen') ? 1 : (int) $jumlah;
        $prompt = sprintf($promptTemplate, $chunkSize);

        // Support "step mode" (chunking) where controller calls generateSoal multiple times
        // with the same jobId. In that case, don't reset progress; continue from existing done/total.
        $baseDone = 0;
        $targetTotal = (int) $jumlah;
        if (is_string($jobId) && trim($jobId) !== '') {
            try {
                $existing = Cache::get('ai_generate:' . trim($jobId));
                if (is_array($existing) && is_array($existing['progress'] ?? null)) {
                    $baseDone = (int) ($existing['progress']['done'] ?? 0);
                    $targetTotal = (int) ($existing['progress']['total'] ?? $targetTotal);
                    if ($targetTotal <= 0) $targetTotal = (int) $jumlah;
                }
            } catch (\Throwable $e) {
                // ignore cache errors
            }
        }

        $generatedCount = 0;
        $maxAttempts = 3; // Looping maksimal untuk validasi unik jika teridentifikasi duplikat

        $this->setJobProgress($jobId, [
            'status' => 'processing',
            'message' => 'Menyiapkan prompt & menghubungi AI...',
            'progress' => ['done' => (int) $baseDone, 'total' => (int) $targetTotal],
        ]);
        $this->pushJobLog($jobId, "Start generate: kategori={$kategori} jumlah={$jumlah} provider={$provider} (done={$baseDone}/{$targetTotal})");

        // ==========================================
        // 3. LOGIKA GENERATE METODE RETRY / LOOPING
        // ==========================================

        // Untuk Ollama (chunk 1), total attempt bisa lebih banyak agar target jumlah tercapai.
        $maxOllamaTurns = ($provider === 'ollama_qwen') ? max(3, $jumlah * $maxAttempts) : $maxAttempts;

        for ($attempt = 1; $attempt <= $maxOllamaTurns; $attempt++) {
            try {
                $response = null;
                $errorMsg = "";
                $lastUrl = "";
                $temperature = ($attempt === 1 ? 0.65 : 0.85);

                if ($provider === 'ollama_qwen') {
                    // --- OLLAMA (LOCAL, GRATIS - TANPA API KEY) ---
                    $url = $this->normalizeOllamaBaseUrl($ollamaBaseUrl) . "/api/chat";
                    $lastUrl = $url;

                    $headers = [
                        'Content-Type' => 'application/json',
                    ];
                    if ($ollamaAccessClientId !== '' && $ollamaAccessClientSecret !== '') {
                        $headers['CF-Access-Client-Id'] = $ollamaAccessClientId;
                        $headers['CF-Access-Client-Secret'] = $ollamaAccessClientSecret;
                    }

                    $allowedOllamaModels = self::ALLOWED_OLLAMA_MODELS;
                    $selectedModel = (is_string($model) && in_array($model, $allowedOllamaModels, true))
                        ? $model
                        : $ollamaModel;

                    $fallbackModel = match ($selectedModel) {
                        'codestral:latest', 'deepseek-coder-v2:lite' => 'qwen2.5-coder:7b',
                        'qwen2.5-coder:7b' => 'llama3.2:3b',
                        default => null,
                    };

                    $response = Http::withOptions(['verify' => false, 'stream' => true])
                        ->connectTimeout(10)
                        ->timeout(240)
                        ->withHeaders($headers)
                        ->post($url, [
                            'model' => $selectedModel,
                            'messages' => [['role' => 'user', 'content' => $prompt]],
                            'stream' => true,
                            'format' => 'json',
                            'keep_alive' => '15m',
                            'options' => [
                                'temperature' => $temperature,
                                'top_p' => 0.9,
                                'repeat_penalty' => 1.15,
                                'num_predict' => 420,
                            ],
                        ]);
                    if ($response->failed()) {
                        $errorMsg = $response->body();
                    }
                } elseif ($provider == 'groq') {
                    // --- GROQ (Single Key) ---
                    if (empty($groqApiKey)) {
                        return "GROQ_API_KEY belum di-set. Pilih provider 'ollama_qwen' (gratis) atau isi GROQ_API_KEY di .env.";
                    }
                    $url = "https://api.groq.com/openai/v1/chat/completions";
                    $lastUrl = $url;
                    $response = Http::withOptions(['verify' => false])
                        ->connectTimeout(10)
                        ->timeout(120)
                        ->withHeaders([
                        'Authorization' => 'Bearer ' . $groqApiKey,
                        'Content-Type' => 'application/json',
                    ])->post($url, [
                        'model' => $groqModel,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Anda HARUS membalas dengan 1 objek JSON valid saja. Tanpa markdown, tanpa teks lain.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => $temperature,
                        'top_p' => 0.9,
                        'max_tokens' => 2200,
                    ]);

                    if ($response->failed()) {
                        $errorMsg = $response->body();
                    }
                } elseif ($provider == 'openai') {
                    // --- OPENAI (ChatGPT) ---
                    if (empty($openAiApiKey)) {
                        return "OPENAI_API_KEY belum di-set. Silakan isi OPENAI_API_KEY di .env.";
                    }
                    $url = "https://api.openai.com/v1/chat/completions";
                    $lastUrl = $url;
                    $response = Http::withOptions(['verify' => false])
                        ->connectTimeout(10)
                        ->timeout(120)
                        ->withHeaders([
                        'Authorization' => 'Bearer ' . $openAiApiKey,
                        'Content-Type' => 'application/json',
                    ])->post($url, [
                        'model' => $openAiModel,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Anda HARUS membalas dengan 1 objek JSON valid saja. Tanpa markdown, tanpa teks lain.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => $temperature,
                        'top_p' => 0.9,
                        'max_tokens' => 2200,
                        'response_format' => ['type' => 'json_object']
                    ]);

                    if ($response->failed()) {
                        $errorMsg = $response->body();
                    }
                } else {
                    return "Provider tidak dikenal: {$provider}";
                }

                if (!$response || $response->failed()) {
                    $status = $response ? $response->status() : 0;
                    $body = $errorMsg ?: ($response ? $response->body() : '');
                    $body = is_string($body) ? trim($body) : '';
                    if ($body === '' && $response) {
                        $body = json_encode($response->json());
                    }
                    $body = (string) $body;

                    if (str_contains($body, 'quota') || str_contains($body, 'QUOTA')) {
                        return "Limit habis (quota).";
                    }

                    if ($provider === 'ollama_qwen' && $this->hasGroqFallback()) {
                        $this->pushJobLog($jobId, "Ollama gagal/timeout. Pindah ke Groq backup.");
                        return $this->generateSoal($kategori, $jumlah, 'groq', null, $pdfPath, $fokusTopik, $jobId, $examModeId, $subCategoryId, $difficulty, $createdBy);
                    }

                    $snippet = substr($body, 0, 300);
                    $endpointNote = $lastUrl !== '' ? " [endpoint: {$lastUrl}]" : '';
                    return "Gagal API (HTTP {$status}): " . ($snippet !== '' ? $snippet : 'No response body') . $endpointNote;
                }

                // ==========================================
                // 4. PARSING AMAN (TRY-CATCH) & BERSIHKAN TEKS
                // ==========================================
                $this->pushJobLog($jobId, "AI response OK. Parsing JSON...");
                $this->setJobProgress($jobId, ['message' => 'Menerima respons AI, parsing JSON...']);

                $responseText = ($provider === 'ollama_qwen')
                    ? $this->readOllamaStreamedContent($response)
                    : (($response->json()['choices'][0]['message']['content'] ?? ''));

                // Decode JSON robustly (LLM sometimes adds text/markdown around JSON)
                $data = $this->decodeJsonObject((string) $responseText);

                if (!isset($data['soal']) || !is_array($data['soal'])) {
                    $snippet = trim((string) $responseText);
                    if (function_exists('mb_substr')) $snippet = mb_substr($snippet, 0, 240, 'UTF-8');
                    else $snippet = substr($snippet, 0, 240);
                    $this->pushJobLog($jobId, "JSON invalid. Snippet: " . $snippet);
                    throw new \Exception("Format JSON dari AI tidak valid atau tidak punya key 'soal'.");
                }

                // ==========================================
                // 5. VALIDASI & INSERT DENGAN ANTI-DUPLIKASI
                // ==========================================

                foreach ($data['soal'] as $item) {
                    if ($generatedCount >= $jumlah) break;

                    // Hash berbasis konten (teks + opsi) agar duplikat tidak lolos walau ID/opsi beda.
                    $hashSoal = $this->computeHashSoal($item, $dbKategori);

                    // Pengecekan Duplikasi
                    $duplikat = Soal::where('hash_soal', $hashSoal)->exists();
                    if ($duplikat) {
                        // Sesuai konfirmasi user: hapus/abaikan soal baru yang terdeteksi kembar
                        Log::info("Soal duplikat terdeteksi, membuang soal ini... (Hash: $hashSoal)");
                        continue;
                    }

                    // Normalisasi kunci jawaban
                    $kunci = $this->normalizeJawabanKey($item['jawaban_benar'] ?? 'A');

                    // Optional: verifikasi kunci & pembahasan untuk TWK/TIU agar tidak banyak yang salah.
                    $verified = $this->verifyKunciDanPembahasan(
                        $kategori,
                        $item,
                        $provider,
                        $model,
                        $groqApiKey,
                        $groqModel,
                        $ollamaBaseUrl,
                        $ollamaModel,
                        $ollamaAccessClientId,
                        $ollamaAccessClientSecret
                    );
                    if (is_array($verified)) {
                        $kunci = $this->normalizeJawabanKey($verified['jawaban_benar'] ?? $kunci);
                        $item['pembahasan'] = $verified['pembahasan'] ?? ($item['pembahasan'] ?? null);
                    }

                    // Tentukan bobot TKP (Standar CASN 2027: Bobot 1-5 per opsi A-E)
                    $bobot = null;
                    if ($kategori === 'TKP') {
                        if (!empty($item['bobot_tkp']) && is_array($item['bobot_tkp'])) {
                            $rawWeights = $item['bobot_tkp'];
                            $bobot = [
                                'A' => (int) ($rawWeights['A'] ?? $rawWeights['a'] ?? 0),
                                'B' => (int) ($rawWeights['B'] ?? $rawWeights['b'] ?? 0),
                                'C' => (int) ($rawWeights['C'] ?? $rawWeights['c'] ?? 0),
                                'D' => (int) ($rawWeights['D'] ?? $rawWeights['d'] ?? 0),
                                'E' => (int) ($rawWeights['E'] ?? $rawWeights['e'] ?? 0),
                            ];

                            // Kunci jawaban otomatis mengikuti opsi bernilai bobot tertinggi (5)
                            $maxVal = max($bobot);
                            if ($maxVal > 0) {
                                $bestKey = array_search($maxVal, $bobot, true);
                                if ($bestKey) {
                                    $kunci = $bestKey;
                                }
                            } else {
                                $bobot = null;
                            }
                        }

                        // Fallback distribusi unik 1-5 jika AI tidak mengeluarkan bobot_tkp lengkap
                        if (!$bobot || count(array_unique(array_values($bobot))) < 3) {
                            $k = $kunci ?: 'A';
                            $remainingScores = [4, 3, 2, 1];
                            shuffle($remainingScores);
                            $bobot = [];
                            foreach (['A', 'B', 'C', 'D', 'E'] as $opt) {
                                if ($opt === $k) {
                                    $bobot[$opt] = 5;
                                } else {
                                    $bobot[$opt] = array_pop($remainingScores) ?? 1;
                                }
                            }
                        }
                    }

                    $payload = [
                        'kategori' => $dbKategori,
                        'sub_topik' => $item['sub_topik'] ?? $selectedSubTopik,
                        'teks_soal' => $item['teks_soal'] ?? '-',
                        'opsi_a' => $item['opsi_a'] ?? '-',
                        'opsi_b' => $item['opsi_b'] ?? '-',
                        'opsi_c' => $item['opsi_c'] ?? '-',
                        'opsi_d' => $item['opsi_d'] ?? '-',
                        'opsi_e' => $item['opsi_e'] ?? '-',
                        'jawaban_benar' => $kunci,
                        'pembahasan' => $item['pembahasan'] ?? 'Pembahasan tidak tersedia.',
                        'bobot_tkp' => $bobot,
                        'hash_soal' => $hashSoal,
                        'prompt_gambar_soal' => $item['prompt_gambar_soal'] ?? null,
                        'prompt_gambar_a' => $item['prompt_gambar_a'] ?? null,
                        'prompt_gambar_b' => $item['prompt_gambar_b'] ?? null,
                        'prompt_gambar_c' => $item['prompt_gambar_c'] ?? null,
                        'prompt_gambar_d' => $item['prompt_gambar_d'] ?? null,
                        'prompt_gambar_e' => $item['prompt_gambar_e'] ?? null,
                    ];

                    if (Schema::hasColumn('soals', 'exam_mode_id')) {
                        $payload['exam_mode_id'] = $examMode?->id ?: ($examModeId ?: null);
                    }
                    if (Schema::hasColumn('soals', 'sub_category_id')) {
                        $payload['sub_category_id'] = $subCategory?->id ?: ($subCategoryId ?: null);
                    }
                    if (Schema::hasColumn('soals', 'difficulty')) {
                        $payload['difficulty'] = $difficultyLabel;
                    }
                    if (Schema::hasColumn('soals', 'created_by')) {
                        $payload['created_by'] = $createdBy ?: null;
                    }
                    if (Schema::hasColumn('soals', 'audit_status')) {
                        $payload['audit_status'] = 'pending';
                        $payload['audited_at'] = null;
                        $payload['audited_by'] = null;
                        $payload['last_audit_source'] = 'ai_generate';
                        $payload['last_audit_score'] = null;
                    }
                    if (Schema::hasColumn('soals', 'standar_tahun')) {
                        $payload['standar_tahun'] = '2027';
                    }

                    Soal::create($payload);

                    $generatedCount++;
                    $this->setJobProgress($jobId, [
                        'message' => "Membuat soal " . ($baseDone + $generatedCount) . "/{$targetTotal} ({$kategori})...",
                        'progress' => ['done' => (int) ($baseDone + $generatedCount), 'total' => (int) $targetTotal],
                    ]);
                    $teksShort = $this->normalizeSoalText($item['teks_soal'] ?? '');
                    if (function_exists('mb_strlen') && mb_strlen($teksShort, 'UTF-8') > 80) {
                        $teksShort = mb_substr($teksShort, 0, 80, 'UTF-8') . '...';
                    } elseif (strlen($teksShort) > 80) {
                        $teksShort = substr($teksShort, 0, 80) . '...';
                    }
                    $this->pushJobLog($jobId, "Insert soal #{$generatedCount}: {$teksShort}");
                }

                // ==========================================
                // 6. CEK APAKAH TARGET TERPENUHI ATAU HARUS LOOPING
                // ==========================================

                if ($generatedCount >= $jumlah) {
                    $this->pushJobLog($jobId, "Selesai. Total dibuat: {$generatedCount}");
                    return true; // Sukses memenuhi target jumlah
                } else if ($attempt < $maxOllamaTurns) {
                    // Target belum tercapai (karena ada yang duplikat/dibuang), AI akan digenerate ulang
                    Log::info("Target $jumlah belum terpenuhi ($generatedCount). Re-generating (Attempt: " . ($attempt + 1) . ")");
                    $this->pushJobLog($jobId, "Belum cukup ({$generatedCount}/{$jumlah}), retry attempt " . ($attempt + 1));
                    $jumlahSisa = $jumlah - $generatedCount;
                    // Untuk Ollama: tetap chunk kecil supaya tidak timeout.
                    $nextChunk = ($provider === 'ollama_qwen') ? 1 : $jumlahSisa;
                    $prompt = sprintf($promptTemplate, $nextChunk);
                }
            } catch (\Exception $e) {
                if ($provider === 'ollama_qwen' && $this->hasGroqFallback()) {
                    $this->pushJobLog($jobId, "Ollama error: " . $e->getMessage() . ". Pindah ke Groq backup.");
                    return $this->generateSoal($kategori, $jumlah, 'groq', null, $pdfPath, $fokusTopik, $jobId, $examModeId, $subCategoryId, $difficulty, $createdBy);
                }
                if ($attempt < $maxOllamaTurns) {
                    Log::warning("Error parsing JSON AI: " . $e->getMessage() . ". Retrying...");
                    $this->pushJobLog($jobId, "Error: " . $e->getMessage() . " (retry)");
                    continue; // Coba lagi jika parse gagal 
                }
                $this->pushJobLog($jobId, "Gagal: " . $e->getMessage());
                return "Exception: " . $e->getMessage();
            }
        }

        // Return true as long as we generated at least 1 question
        if ($generatedCount > 0) {
            $this->pushJobLog($jobId, "Selesai sebagian: {$generatedCount} soal dibuat.");
            return true;
        }
        $this->pushJobLog($jobId, "Gagal generate soal setelah {$maxOllamaTurns} percobaan.");
        return "Gagal generate soal unik setelah $maxOllamaTurns kali percobaan.";
    }

    private function readOllamaStreamedContent(Response $response): string
    {
        // Ollama stream response is NDJSON: one JSON object per line.
        // We reconstruct final `message.content` by concatenating chunks.
        $body = (string) $response->body();
        if ($body === '') return '';

        $content = '';
        $lines = preg_split("/\r?\n/", trim($body));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $chunk = json_decode($line, true);
            if (!is_array($chunk)) continue;

            if (isset($chunk['message']['content']) && is_string($chunk['message']['content'])) {
                $content .= $chunk['message']['content'];
            } elseif (isset($chunk['response']) && is_string($chunk['response'])) {
                // In case backend returns /api/generate style chunks
                $content .= $chunk['response'];
            }

            if (!empty($chunk['done'])) break;
        }

        return $content;
    }

    /**
     * Groq/LLM kadang membungkus JSON dengan teks/markdown.
     * Fungsi ini mencoba "menyelamatkan" objek JSON pertama yang valid.
     */
    private function decodeJsonObject(string $raw): array
    {
        $text = trim($raw);
        if ($text === '') return [];

        // Remove common code fences
        $text = preg_replace('/```(?:json)?/i', '', $text);
        $text = trim((string) $text);

        $data = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        // Attempt to extract the largest JSON-looking object inside the response.
        if (preg_match('/\\{[\\s\\S]*\\}/', $text, $m)) {
            $candidate = trim((string) ($m[0] ?? ''));
            $data2 = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data2)) {
                return $data2;
            }
        }

        return [];
    }
}





