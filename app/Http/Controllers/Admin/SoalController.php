<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSoalJob;
use App\Jobs\CategorizeOldSoalJob;
use App\Models\ExamMode;
use App\Services\AISoalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Support\QuestionStatsSupport;

class SoalController extends Controller
{
    private const ALLOWED_OLLAMA_MODELS = 'qwen2.5-coder:7b,deepseek-coder-v2:lite,mistral:latest,llama3.2:3b,codestral:latest';
    private const OLLAMA_MODELS = ['qwen2.5-coder:7b', 'llama3.2:3b', 'deepseek-coder-v2:lite', 'codestral:latest', 'mistral:latest'];
    private const GROQ_FAST_MAX = 10;
    private const GENERATE_QUEUE = 'generate';
    private const PDF_GENERATE_QUEUE = 'pdf-generate';

    protected $aiService;

    public function __construct(AISoalService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $user = auth()->user();
        $preferredExamModeIds = $user && method_exists($user, 'preferredExamModeIds') ? $user->preferredExamModeIds() : [];
        $preferredFormasiNames = $user && method_exists($user, 'preferredFormasiNames') ? $user->preferredFormasiNames() : [];
        [
            'stats' => $stats,
            'modeQuestionStats' => $modeQuestionStats,
            'auditStatusStats' => $auditStatusStats,
            'uncategorizedCount' => $uncategorizedCount,
        ] = QuestionStatsSupport::dashboardSnapshot();
        $examModes = \App\Support\ExamStructureSupport::modes();

        if (!empty($preferredExamModeIds)) {
            $examModes = collect($examModes)
                ->filter(fn ($mode) => in_array((int) ($mode->id ?? 0), $preferredExamModeIds, true))
                ->values();
        }

        $defaultExamMode = !empty($preferredExamModeIds)
            ? (collect($examModes)->firstWhere('id', $preferredExamModeIds[0]) ?: collect($examModes)->first() ?: \App\Support\ExamStructureSupport::defaultMode())
            : \App\Support\ExamStructureSupport::defaultMode();

        $subCategories = $defaultExamMode
            ? \App\Support\ExamStructureSupport::subCategories((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''), 'formasi')
            : collect();

        if (($defaultExamMode->code ?? '') !== 'skd' && !empty($preferredFormasiNames)) {
            $subCategories = collect($subCategories)
                ->filter(fn ($sub) => in_array(trim((string) ($sub->name ?? '')), $preferredFormasiNames, true))
                ->values();
        }

        $modePackages = $defaultExamMode
            ? \App\Support\ExamStructureSupport::packages((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''))
            : collect();

        $categoryOptions = $defaultExamMode
            ? \App\Support\ExamStructureSupport::categoryOptions((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''))
            : collect();

        $focusTopicPresets = \App\Support\ExamStructureSupport::focusTopicPresets();
        $dynamicExamReady = \App\Support\ExamStructureSupport::schemaReady();
        $recentPdfJobs = $this->recentPersistentPdfJobs(auth()->id());

        return view('admin.generate_soal', compact(
            'stats',
            'modeQuestionStats',
            'auditStatusStats',
            'uncategorizedCount',
            'examModes',
            'defaultExamMode',
            'subCategories',
            'modePackages',
            'categoryOptions',
            'focusTopicPresets',
            'dynamicExamReady',
            'recentPdfJobs'
        ));
    }

    public function indexPdf()
    {
        $recentPdfJobs = $this->recentPersistentPdfJobs(auth()->id());

        return view('admin.generate_soal_pdf', compact('recentPdfJobs'));
    }

    public function indexBulk()
    {
        $examModes = \App\Support\ExamStructureSupport::modes();
        $defaultExamMode = \App\Support\ExamStructureSupport::defaultMode();
        $subCategories = $defaultExamMode
            ? \App\Support\ExamStructureSupport::subCategories((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''), 'formasi')
            : collect();
        $categoryOptions = $defaultExamMode
            ? \App\Support\ExamStructureSupport::categoryOptions((int) ($defaultExamMode->id ?? 0), (string) ($defaultExamMode->code ?? ''))
            : collect();

        $categoryOptionMap = collect($examModes)->mapWithKeys(function ($mode) {
            $options = \App\Support\ExamStructureSupport::categoryOptions((int) ($mode->id ?? 0), (string) ($mode->code ?? ''))
                ->values()
                ->map(fn ($row) => [
                    'value' => (string) ($row->value ?? ''),
                    'label' => (string) ($row->label ?? $row->value ?? ''),
                ])
                ->all();

            return [(string) ($mode->id ?? '') => $options];
        })->all();

        $promptToken = trim((string) request()->query('prompt_token', session('prompt_token', '')));
        $importToken = trim((string) request()->query('import_token', session('import_token', '')));
        $promptPreview = $promptToken !== '' ? Cache::get($this->bulkPromptCacheKey($promptToken)) : null;
        $importPreview = $importToken !== '' ? Cache::get($this->bulkImportCacheKey($importToken)) : null;

        return view('admin.generate_bulk', compact(
            'examModes',
            'defaultExamMode',
            'subCategories',
            'categoryOptions',
            'categoryOptionMap',
            'promptToken',
            'promptPreview',
            'importToken',
            'importPreview'
        ));
    }

    public function generateBulk(Request $request)
    {
        $examModeRule = Schema::hasTable('exam_modes') ? 'required|integer|exists:exam_modes,id' : 'required|integer';
        $subCategoryRule = Schema::hasTable('sub_categories') ? 'nullable|integer|exists:sub_categories,id' : 'nullable|integer';

        $validated = $request->validate([
            'exam_mode_id' => $examModeRule,
            'sub_category_id' => $subCategoryRule,
            'kategori' => 'required|string|max:50',
            'difficulty' => 'required|in:easy,medium,hard,mix',
            'total_questions' => 'required|integer|in:10,20,50,100',
            'fokus_topik' => 'nullable|string|max:120',
        ]);

        $examMode = ExamMode::query()->find((int) $validated['exam_mode_id']);
        $subCategory = !empty($validated['sub_category_id'])
            ? SubCategory::query()->find((int) $validated['sub_category_id'])
            : null;

        $payload = [
            'kategori' => trim((string) $validated['kategori']),
            'exam_mode_id' => (int) $validated['exam_mode_id'],
            'sub_category_id' => !empty($validated['sub_category_id']) ? (int) $validated['sub_category_id'] : null,
            'difficulty' => (string) $validated['difficulty'],
            'total_questions' => (int) $validated['total_questions'],
            'fokus_topik' => trim((string) ($validated['fokus_topik'] ?? '')) ?: null,
            'exam_mode_name' => (string) ($examMode->name ?? ''),
            'exam_mode_code' => (string) ($examMode->code ?? ''),
            'sub_category_name' => (string) ($subCategory->name ?? ''),
        ];

        $prompt = $this->buildGeminiBulkPrompt($payload);
        $token = (string) Str::uuid();
        Cache::put($this->bulkPromptCacheKey($token), [
            'request' => $payload,
            'prompt' => $prompt,
        ], now()->addHours(2));

        return redirect()
            ->route('admin.soal.bulk.index')
            ->withInput()
            ->with('success', 'Prompt Gemini berhasil dibuat.')
            ->with('prompt_token', $token)
            ->withFragment('prompt-output');
    }

    public function previewBulkImport(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('admin.soal.bulk.index');
        }

        $validated = $request->validate([
            'prompt_token' => 'required|string',
            'import_payload' => 'required|string',
        ]);

        $promptPreview = Cache::get($this->bulkPromptCacheKey((string) $validated['prompt_token']));
        if (!is_array($promptPreview) || !is_array($promptPreview['request'] ?? null)) {
            return back()->with('error', 'Prompt Gemini sudah kedaluwarsa. Generate ulang prompt dulu.');
        }

        $rows = $this->parseGeminiBulkRows((string) $validated['import_payload'], $promptPreview['request']);
        $token = (string) Str::uuid();
        Cache::put($this->bulkImportCacheKey($token), [
            'request' => $promptPreview['request'],
            'rows' => $rows,
            'raw' => (string) $validated['import_payload'],
        ], now()->addHours(2));

        return redirect()
            ->route('admin.soal.bulk.index', [
                'prompt_token' => $validated['prompt_token'],
                'import_token' => $token,
            ])
            ->withInput([
                'import_payload' => (string) $validated['import_payload'],
            ])
            ->with('success', 'Preview JSON Gemini siap diperiksa.');
    }

    public function storeBulkImport(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('admin.soal.bulk.index');
        }

        $validated = $request->validate([
            'import_token' => 'required|string',
            'apply_rows' => 'nullable|array',
        ]);

        $preview = Cache::get($this->bulkImportCacheKey((string) $validated['import_token']));
        if (!is_array($preview) || !is_array($preview['rows'] ?? null) || !is_array($preview['request'] ?? null)) {
            return back()->with('error', 'Preview import sudah kedaluwarsa. Paste ulang JSON Gemini.');
        }

        $selectedRows = collect(array_keys($validated['apply_rows'] ?? []))
            ->map(fn ($index) => (int) $index)
            ->filter(fn ($index) => $index >= 0)
            ->values();

        if ($selectedRows->isEmpty()) {
            return back()->with('error', 'Pilih minimal satu soal valid untuk disimpan.');
        }

        $saved = 0;
        $requestConfig = $preview['request'];

        foreach ($preview['rows'] as $index => $row) {
            if (!$selectedRows->contains((int) $index) || empty($row['valid'])) {
                continue;
            }

            $payload = [
                'kategori' => ($row['question_type'] ?? 'objective') === 'tkp' ? 'TKP' : (string) $requestConfig['kategori'],
                'sub_topik' => (string) ($row['sub_topik'] ?: ($requestConfig['fokus_topik'] ?? null)),
                'teks_soal' => (string) $row['soal'],
                'opsi_a' => (string) $row['A'],
                'opsi_b' => (string) $row['B'],
                'opsi_c' => (string) $row['C'],
                'opsi_d' => (string) $row['D'],
                'opsi_e' => (string) $row['E'],
                'jawaban_benar' => ($row['question_type'] ?? 'objective') === 'tkp' ? null : (string) $row['jawaban'],
                'pembahasan' => (string) $row['pembahasan'],
            ];

            if (Schema::hasColumn('soals', 'bobot_tkp')) {
                $payload['bobot_tkp'] = ($row['question_type'] ?? 'objective') === 'tkp'
                    ? ($row['score_map'] ?? null)
                    : null;
            }

            if (Schema::hasColumn('soals', 'exam_mode_id')) {
                $payload['exam_mode_id'] = !empty($requestConfig['exam_mode_id']) ? (int) $requestConfig['exam_mode_id'] : null;
            }
            if (Schema::hasColumn('soals', 'sub_category_id')) {
                $payload['sub_category_id'] = !empty($requestConfig['sub_category_id']) ? (int) $requestConfig['sub_category_id'] : null;
            }
            if (Schema::hasColumn('soals', 'difficulty')) {
                $payload['difficulty'] = (string) ($row['difficulty'] ?: $requestConfig['difficulty']);
            }
            if (Schema::hasColumn('soals', 'created_by')) {
                $payload['created_by'] = auth()->id();
            }
            if (Schema::hasColumn('soals', 'audit_status')) {
                $payload['audit_status'] = 'pending';
                $payload['audited_at'] = null;
                $payload['audited_by'] = null;
                $payload['last_audit_source'] = 'ai_bulk_import';
                $payload['last_audit_score'] = null;
            }
            if (Schema::hasColumn('soals', 'provider')) {
                $payload['provider'] = 'bulk_ai_import';
            }
            if (Schema::hasColumn('soals', 'standar_tahun')) {
                $payload['standar_tahun'] = '2027';
            }

            Soal::create($payload);
            $saved++;
        }

        return redirect()
            ->route('admin.soal.bulk.index')
            ->with('success', $saved . ' soal dari AI (ChatGPT/Gemini) berhasil disimpan ke database.');
    }

    public function pdfJobs(Request $request)
    {
        $jobs = $this->recentPersistentPdfJobs(auth()->id());
        $status = strtolower(trim((string) $request->query('status', '')));
        if (in_array($status, ['processing', 'success', 'error'], true)) {
            $jobs = array_values(array_filter($jobs, function ($job) use ($status) {
                $st = strtolower((string) ($job['status'] ?? 'unknown'));
                if ($status === 'error') return in_array($st, ['error', 'failed', 'unknown'], true);
                return $st === $status;
            }));
        }

        return response()->json([
            'ok' => true,
            'jobs' => $jobs,
        ]);
    }

    private function persistentGenerateJobPath(string $jobId): string
    {
        return 'ai_generate_jobs/' . trim($jobId) . '.json';
    }

    private function writePersistentGenerateJob(string $jobId, array $payload): void
    {
        $path = $this->persistentGenerateJobPath($jobId);
        $current = [];
        if (Storage::disk('local')->exists($path)) {
            $decoded = json_decode((string) Storage::disk('local')->get($path), true);
            if (is_array($decoded)) $current = $decoded;
        }
        $merged = array_replace_recursive($current, $payload);
        Storage::disk('local')->put($path, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function readPersistentGenerateJob(string $jobId): ?array
    {
        $path = $this->persistentGenerateJobPath($jobId);
        if (!Storage::disk('local')->exists($path)) return null;
        $decoded = json_decode((string) Storage::disk('local')->get($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function recentPersistentPdfJobs(?int $userId, int $limit = 12): array
    {
        $files = Storage::disk('local')->files('ai_generate_jobs');
        $rows = [];
        foreach ($files as $file) {
            if (!str_ends_with($file, '.json')) continue;
            $decoded = json_decode((string) Storage::disk('local')->get($file), true);
            if (!is_array($decoded)) continue;
            if ((int) ($decoded['user_id'] ?? 0) !== (int) ($userId ?? 0)) continue;
            if (empty($decoded['request']['pdf_path']) && empty($decoded['pdf_path'])) continue;
            $jobId = (string) ($decoded['job_id'] ?? basename($file, '.json'));
            $cache = Cache::get('ai_generate:' . $jobId);
            if (is_array($cache)) {
                $decoded = array_replace_recursive($decoded, $cache);
            }
            $decoded['job_id'] = $jobId;
            $rows[] = $decoded;
        }

        usort($rows, function ($a, $b) {
            return strcmp((string) ($b['queued_at'] ?? $b['updated_at'] ?? ''), (string) ($a['queued_at'] ?? $a['updated_at'] ?? ''));
        });

        return array_slice($rows, 0, $limit);
    }

    public function generate(Request $request)
    {
        $examModeRule = Schema::hasTable('exam_modes') ? 'nullable|integer|exists:exam_modes,id' : 'nullable|integer';
        $subCategoryRule = Schema::hasTable('sub_categories') ? 'nullable|integer|exists:sub_categories,id' : 'nullable|integer';

        $request->validate([
            'kategori' => 'nullable|string|max:50',
            'jumlah'   => 'required|integer|min:1|max:20',
            'provider' => 'required|in:ollama_qwen,groq',
            'model' => 'nullable|in:' . self::ALLOWED_OLLAMA_MODELS,
            'ollama_fast' => 'nullable|boolean',
            'referensi_pdf' => 'nullable|file|mimes:pdf|max:2048',
            'auto_detect_pdf' => 'nullable|boolean',
            'fokus_topik' => 'nullable|string|max:100',
            'fast_mode' => 'nullable|boolean',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'exam_mode_id' => $examModeRule,
            'sub_category_id' => $subCategoryRule,
            'topik_sub_category_id' => $subCategoryRule,
        ]);

        $pdfPath = null;
        if ($request->hasFile('referensi_pdf')) {
            $pdfPath = $request->file('referensi_pdf')->store('referensi_pdf', 'local');
        }

        $provider = (string) $request->provider;
        $autoDetectPdf = $pdfPath !== null && (bool) $request->boolean('auto_detect_pdf', true);
        $ollamaFast = (bool) $request->boolean('ollama_fast', false);
        $selectedOllamaModel = ($provider === 'ollama_qwen' && $ollamaFast) ? 'llama3.2:3b' : (is_string($request->model) ? $request->model : null);
        $jumlah = (int) $request->jumlah;
        $kategori = trim((string) $request->kategori);
        $fokusTopik = $request->input('fokus_topik');
        $difficulty = (string) ($request->input('difficulty', 'medium') ?: 'medium');
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $examModeId = (int) ($request->input('exam_mode_id') ?: $defaultSkdId ?: 0);
        $subCategoryId = (int) ($request->input('sub_category_id') ?: 0);
        $topikSubCategoryId = (int) ($request->input('topik_sub_category_id') ?: 0);
        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $subCategory = SubCategory::query()->where('exam_mode_id', $examModeId)->find($subCategoryId);
            if (!$subCategory) {
                return back()->withInput()->with('error', 'Sub kategori tidak sesuai dengan mode ujian yang dipilih.');
            }
        }
        $topikSubCategory = null;
        if ($topikSubCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $topikSubCategory = SubCategory::query()->where('exam_mode_id', $examModeId)->find($topikSubCategoryId);
            if (!$topikSubCategory) {
                return back()->withInput()->with('error', 'Topik teknis tidak sesuai dengan mode ujian yang dipilih.');
            }
            if (Schema::hasColumn('sub_categories', 'kind') && ($topikSubCategory->kind ?? null) !== 'topik') {
                return back()->withInput()->with('error', 'Pilihan Topik Teknis belum bertipe topik. Rapikan dulu di Struktur Ujian.');
            }
        }
        if ((!is_string($fokusTopik) || trim($fokusTopik) === '') && $topikSubCategory) {
            $fokusTopik = (string) $topikSubCategory->name;
        }

        if (!$autoDetectPdf && $kategori === '') {
            return back()->withInput()->with('error', 'Kategori soal wajib dipilih jika auto-detect PDF tidak dipakai.');
        }

        // Mode cepat: jalankan langsung tanpa queue.
        // Cocok untuk jumlah kecil-menengah (mis. 1-10) tanpa PDF.
        $fastMode = (bool) $request->boolean('fast_mode', false);
        $canFast = ($provider === 'groq')
            && ($pdfPath === null)
            && ($jumlah <= self::GROQ_FAST_MAX);

        if ($fastMode && $canFast) {
            set_time_limit(0);
            $usedModel = $provider === 'groq' ? null : $selectedOllamaModel;
            $result = $this->aiService->generateSoal(
                $kategori,
                $jumlah,
                $provider,
                $usedModel,
                null,
                $fokusTopik,
                null,
                $examModeId,
                $subCategoryId,
                $difficulty,
                auth()->id()
            );
            if ($result === true) {
                return back()->withInput()->with('success', "Selesai generate {$jumlah} soal ({$kategori}) via {$provider} (mode cepat).");
            }

            $msg = is_string($result) ? $result : 'Gagal generate (unknown error).';
            return back()->withInput()->with('error', 'Gagal: ' . $msg);
        }

        // Jalankan "step mode" (chunking) tanpa worker queue khusus untuk Groq.
        if ($provider === 'groq' && $pdfPath === null) {
            $jobId = (string) Str::uuid();
            Cache::put('ai_generate:' . $jobId, [
                'status' => 'processing',
                'message' => 'Permintaan generate diterima. Sedang diproses...',
                'user_id' => auth()->id(),
                'queued_at' => now()->toISOString(),
                'runner' => 'step',
                'request' => [
                    'kategori' => $kategori,
                    'jumlah' => $jumlah,
                    'provider' => $provider,
                    'model' => $provider === 'groq' ? null : $selectedOllamaModel,
                    'ollama_fast' => $ollamaFast,
                    'fokus_topik' => $fokusTopik,
                    'exam_mode_id' => $examModeId,
                    'sub_category_id' => $subCategoryId,
                    'difficulty' => $difficulty,
                ],
                'progress' => ['done' => 0, 'total' => (int) $jumlah],
                'log' => [],
            ], now()->addHour());

            $jobs = (array) session('ai_generate_jobs', []);
            $jobs[] = $jobId;
            session(['ai_generate_jobs' => array_values(array_unique($jobs))]);

            return back()->withInput()->with('processing_job_id', $jobId)->with('success', 'Permintaan generate diterima. Sedang diproses...');
        }

        // Untuk Ollama atau request panjang, selalu proses async via queue supaya browser tidak menunggu inference langsung.
        $jobId = (string) Str::uuid();
        $queueName = $pdfPath !== null ? self::PDF_GENERATE_QUEUE : self::GENERATE_QUEUE;

        $jobPayload = [
            'status' => 'processing',
            'message' => 'Sedang diproses...',
            'user_id' => auth()->id(),
            'queued_at' => now()->toISOString(),
            'runner' => 'queue',
            'queue_name' => $queueName,
            'job_id' => $jobId,
            'request' => [
                'kategori' => $kategori !== '' ? $kategori : null,
                'jumlah' => $jumlah,
                'provider' => $provider,
                'model' => $provider === 'groq' ? null : $selectedOllamaModel,
                'ollama_fast' => $ollamaFast,
                'fokus_topik' => $fokusTopik,
                'exam_mode_id' => $examModeId,
                'sub_category_id' => $subCategoryId,
                'difficulty' => $difficulty,
                'auto_detect_pdf' => $autoDetectPdf,
                'pdf_path' => $pdfPath,
            ],
        ];
        Cache::put('ai_generate:' . $jobId, $jobPayload, now()->addHour());
        $this->writePersistentGenerateJob($jobId, $jobPayload);

        $jobs = (array) session('ai_generate_jobs', []);
        $jobs[] = $jobId;
        session(['ai_generate_jobs' => array_values(array_unique($jobs))]);

        GenerateSoalJob::dispatch(
            $jobId,
            $kategori !== '' ? $kategori : null,
            $jumlah,
            $provider,
            $provider === 'groq' ? null : $selectedOllamaModel,
            auth()->id(),
            $pdfPath,
            $fokusTopik,
            $examModeId,
            $subCategoryId,
            $difficulty,
            $autoDetectPdf
        )->onQueue($queueName);
        $response = back()->withInput()->with('success', $autoDetectPdf
            ? 'PDF diterima. Sistem akan mendeteksi mode, formasi, dan kategori otomatis lalu generate di background.'
            : 'Permintaan generate diterima. Sedang diproses...');

        if (!$autoDetectPdf) {
            $response = $response->with('processing_job_id', $jobId);
        }

        return $response;
    }

    public function categorizeOld(Request $request)
    {
        $limit = (int) $request->input('limit', 20);
        if ($limit < 1) $limit = 1;
        if ($limit > 500) $limit = 500;

        $provider = (string) $request->input('provider', 'groq');
        if (!in_array($provider, ['groq', 'ollama_qwen'], true)) $provider = 'groq';

        $model = $request->input('model');
        $model = is_string($model) && trim($model) !== '' ? trim($model) : null;
        // IMPORTANT: `model` field in the UI is for Ollama. For Groq we ignore it.
        if ($provider === 'groq') {
            $model = null;
        }

        $jobId = (string) Str::uuid();
        $key = 'ai_categorize:' . $jobId;

        $total = (int) Soal::whereNull('sub_topik')->orWhere('sub_topik', '')->take($limit)->count();

        Cache::put($key, [
            'status' => 'processing',
            'message' => 'Permintaan auto-label diterima. Sedang diproses...',
            'user_id' => auth()->id(),
            'queued_at' => now()->toISOString(),
            'provider' => $provider,
            'model' => $model,
            'runner' => $provider === 'groq' ? 'step' : 'queue',
            'progress' => ['done' => 0, 'total' => $total, 'errors' => 0],
            'log' => [],
            'last_error' => null,
            'cursor' => ['last_id' => 0, 'limit' => $limit, 'batch' => 5],
        ], now()->addHour());

        // Groq mode: no worker needed. The browser will call /categorize-step repeatedly (chunking).
        // Ollama mode: keep queue worker (to avoid long request / timeouts).
        if ($provider === 'ollama_qwen') {
            CategorizeOldSoalJob::dispatch($jobId, $limit, 'ollama_qwen', $model);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'job_id' => $jobId, 'total' => $total, 'runner' => $provider === 'groq' ? 'step' : 'queue']);
        }

        return back()->with('categorize_job_id', $jobId)->with('success', 'Auto-label dimulai. Lihat progres di panel.');
    }

    public function status(Request $request)
    {
        $jobId = (string) $request->query('id', '');
        if ($jobId === '') {
            return response()->json(['status' => 'invalid'], 400);
        }

        $allowed = in_array($jobId, (array) session('ai_generate_jobs', []), true);
        $data = Cache::get('ai_generate:' . $jobId);
        if (!is_array($data)) {
            $data = $this->readPersistentGenerateJob($jobId);
        }
        if (!is_array($data)) {
            return response()->json(['status' => 'unknown'], 404);
        }
        if (!$allowed && (int) ($data['user_id'] ?? 0) !== (int) auth()->id()) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $runner = (string) ($data['runner'] ?? 'queue');
        $needle = '%' . $jobId . '%';
        $jobRow = null;
        $queueState = $runner === 'step' ? 'step' : 'missing';
        if ($runner !== 'step') {
            $jobRow = DB::table('jobs')
                ->select(['id', 'queue', 'attempts', 'reserved_at', 'available_at', 'created_at'])
                ->where('payload', 'like', $needle)
                ->orderBy('id')
                ->first();

            if ($jobRow) {
                if (!empty($jobRow->reserved_at)) {
                    $queueState = 'running';
                } elseif ((int) $jobRow->available_at > time()) {
                    $queueState = 'scheduled';
                } else {
                    $queueState = 'queued';
                }
            }
        }

        // If a job got stuck at "processing" (worker down / failed_jobs), detect and surface it.
        if (($data['status'] ?? '') === 'processing') {
            $inJobs = (bool) $jobRow;
            $failedRow = DB::table('failed_jobs')->where('payload', 'like', $needle)->orderByDesc('failed_at')->first();

            if ($failedRow) {
                $firstLine = '';
                $ex = (string) ($failedRow->exception ?? '');
                if ($ex !== '') {
                    $firstLine = strtok($ex, "\n") ?: '';
                }
                $msg = $firstLine !== '' ? $firstLine : 'Job gagal (lihat failed_jobs).';

                $data = [
                    'status' => 'error',
                    'message' => $msg,
                    'user_id' => $data['user_id'] ?? null,
                    'queued_at' => $data['queued_at'] ?? null,
                ];
                Cache::put('ai_generate:' . $jobId, $data, now()->addHour());
            } elseif (!$inJobs && $runner !== 'step') {
                // Not in queue table and not failed: likely worker not running, queue cleared, or cache out-of-sync.
                $queuedAt = $data['queued_at'] ?? null;
                $queueName = (string) ($data['queue_name'] ?? ($jobRow->queue ?? 'default'));
                $msg = "Masih menunggu worker queue berjalan untuk antrean {$queueName}. Pastikan service queue aktif (database queue).";
                if ($queuedAt) $msg .= ' (queued_at: ' . $queuedAt . ')';
                $data['message'] = $msg;
            }
        }

        return response()->json([
            'job_id' => $jobId,
            'status' => $data['status'] ?? 'unknown',
            'message' => $data['message'] ?? '',
            'queued_at' => $data['queued_at'] ?? null,
            'runner' => $runner,
            'auto_detected' => $data['auto_detected'] ?? null,
            'progress' => $data['progress'] ?? null,
            'log' => $data['log'] ?? null,
            'queue' => [
                'state' => $queueState,
                'name' => $jobRow->queue ?? ($data['queue_name'] ?? null),
                'attempts' => $jobRow->attempts ?? null,
                'reserved_at' => $jobRow->reserved_at ?? null,
                'available_at' => $jobRow->available_at ?? null,
                'created_at' => $jobRow->created_at ?? null,
                'job_id' => $jobRow->id ?? null,
            ],
        ]);
    }

    public function step(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|string|min:8|max:80',
        ]);
        $jobId = (string) $validated['job_id'];

        $allowed = in_array($jobId, (array) session('ai_generate_jobs', []), true);
        if (!$allowed) return response()->json(['ok' => false, 'error' => 'not_found'], 404);

        $key = 'ai_generate:' . $jobId;
        $data = Cache::get($key);
        if (!is_array($data)) return response()->json(['ok' => false, 'error' => 'missing'], 404);

        if (($data['status'] ?? '') === 'canceled') return response()->json(['ok' => true, 'status' => 'canceled']);
        if (($data['status'] ?? '') === 'success') return response()->json(['ok' => true, 'status' => 'success']);
        if (($data['status'] ?? '') === 'error') return response()->json(['ok' => true, 'status' => 'error']);

        if (($data['runner'] ?? '') !== 'step') {
            return response()->json(['ok' => false, 'error' => 'not_step'], 400);
        }

        $req = is_array($data['request'] ?? null) ? $data['request'] : [];
        $provider = (string) ($req['provider'] ?? 'groq');
        if (!in_array($provider, ['groq', 'ollama_qwen'], true)) {
            return response()->json(['ok' => false, 'error' => 'provider'], 400);
        }

        $kategori = (string) ($req['kategori'] ?? 'TWK');
        $model = $req['model'] ?? null;
        $fokus = $req['fokus_topik'] ?? null;
        $examModeId = (int) ($req['exam_mode_id'] ?? 0);
        $subCategoryId = (int) ($req['sub_category_id'] ?? 0);
        $difficulty = (string) ($req['difficulty'] ?? 'medium');

        $prog = is_array($data['progress'] ?? null) ? $data['progress'] : [];
        $done = (int) ($prog['done'] ?? 0);
        $total = (int) ($prog['total'] ?? 0);
        if ($total <= 0) $total = (int) ($req['jumlah'] ?? 0);

        if ($total > 0 && $done >= $total) {
            $data['status'] = 'success';
            $data['message'] = 'Selesai.';
            Cache::put($key, $data, now()->addHour());
            return response()->json(['ok' => true, 'status' => 'success']);
        }

        $remaining = max(0, $total - $done);
        $batch = min(3, $remaining);
        if ($batch < 1) $batch = 1;

        set_time_limit(0); // Prevent PHP execution timeout during generate API call
        $result = $this->aiService->generateSoal($kategori, $batch, $provider, $model, null, $fokus, $jobId, $examModeId, $subCategoryId, $difficulty, auth()->id());
        if ($result !== true) {
            $data = Cache::get($key);
            if (!is_array($data)) $data = [];
            $data['status'] = 'error';
            $data['message'] = 'Gagal: ' . (is_string($result) ? $result : 'unknown error');
            Cache::put($key, $data, now()->addHour());
            return response()->json(['ok' => false, 'status' => 'error']);
        }

        // Finalize if reached total
        $data = Cache::get($key);
        if (is_array($data)) {
            $p2 = is_array($data['progress'] ?? null) ? $data['progress'] : [];
            $d2 = (int) ($p2['done'] ?? 0);
            $t2 = (int) ($p2['total'] ?? $total);
            if ($t2 > 0 && $d2 >= $t2) {
                $data['status'] = 'success';
                $data['message'] = "Sukses! Soal {$kategori} berhasil dibuat.";
                Cache::put($key, $data, now()->addHour());
            }
        }

        return response()->json(['ok' => true]);
    }

    public function categorizeStatus(Request $request)
    {
        $jobId = (string) $request->query('id', '');
        if ($jobId === '') {
            return response()->json(['status' => 'invalid'], 400);
        }

        $data = Cache::get('ai_categorize:' . $jobId);
        if (!is_array($data)) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $runner = (string) ($data['runner'] ?? 'queue');

        $needle = '%' . $jobId . '%';
        $jobRow = DB::table('jobs')
            ->select(['id', 'attempts', 'reserved_at', 'available_at', 'created_at'])
            ->where('payload', 'like', $needle)
            ->orderBy('id')
            ->first();

        $queueState = $runner === 'step' ? 'step' : 'missing';
        if ($jobRow) {
            if (!empty($jobRow->reserved_at)) {
                $queueState = 'running';
            } elseif ((int) $jobRow->available_at > time()) {
                $queueState = 'scheduled';
            } else {
                $queueState = 'queued';
            }
        }

        if (($data['status'] ?? '') === 'processing') {
            $inJobs = (bool) $jobRow;
            $failedRow = DB::table('failed_jobs')->where('payload', 'like', $needle)->orderByDesc('failed_at')->first();
            if ($failedRow) {
                $firstLine = '';
                $ex = (string) ($failedRow->exception ?? '');
                if ($ex !== '') $firstLine = strtok($ex, "\n") ?: '';
                $msg = $firstLine !== '' ? $firstLine : 'Job gagal (lihat failed_jobs).';
                $data['status'] = 'error';
                $data['message'] = $msg;
                $data['last_error'] = $msg;
                Cache::put('ai_categorize:' . $jobId, $data, now()->addHour());
            } elseif (!$inJobs) {
                $data['message'] = 'Masih menunggu worker queue berjalan. Pastikan service queue aktif (database queue).';
            } elseif ($queueState === 'queued') {
                // If it's been queued for a while, hint that worker may be down.
                $createdAt = $jobRow->created_at ?? null;
                $createdTs = $createdAt ? strtotime((string) $createdAt) : null;
                if ($createdTs && (time() - $createdTs) > 15) {
                    $data['message'] = 'Masih dalam antrian. Jika tidak bergerak, besar kemungkinan worker queue belum jalan.';
                }
            }
        }

        return response()->json([
            'status' => $data['status'] ?? 'unknown',
            'message' => $data['message'] ?? '',
            'queued_at' => $data['queued_at'] ?? null,
            'provider' => $data['provider'] ?? null,
            'model' => $data['model'] ?? null,
            'runner' => $runner,
            'progress' => $data['progress'] ?? null,
            'log' => $data['log'] ?? null,
            'last_error' => $data['last_error'] ?? null,
            'queue' => [
                'state' => $queueState,
                'attempts' => $jobRow->attempts ?? null,
                'reserved_at' => $jobRow->reserved_at ?? null,
                'available_at' => $jobRow->available_at ?? null,
                'created_at' => $jobRow->created_at ?? null,
                'job_id' => $jobRow->id ?? null,
            ],
        ]);
    }

    public function categorizeStep(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|string|min:8|max:80',
        ]);

        $jobId = (string) $validated['job_id'];
        $key = 'ai_categorize:' . $jobId;
        $data = Cache::get($key);
        if (!is_array($data)) return response()->json(['ok' => false, 'error' => 'Job tidak ditemukan'], 404);

        // Cooldown to avoid hitting Groq rate limit (429).
        $now = time();
        $cooldownUntil = (int) ($data['cooldown_until'] ?? 0);
        if ($cooldownUntil > $now) {
            $wait = max(1, $cooldownUntil - $now);
            $data['message'] = "Rate limit. Menunggu {$wait}s sebelum lanjut...";
            Cache::put($key, $data, now()->addHour());
            return response()->json(['ok' => true, 'cooldown' => $wait]);
        }
        $lastStepAt = (int) ($data['last_step_at'] ?? 0);
        if ($lastStepAt > 0 && ($now - $lastStepAt) < 3) {
            return response()->json(['ok' => true, 'cooldown' => 3 - ($now - $lastStepAt)]);
        }
        $data['last_step_at'] = $now;

        if (($data['status'] ?? '') === 'canceled') return response()->json(['ok' => true, 'status' => 'canceled']);
        if (($data['status'] ?? '') === 'success') return response()->json(['ok' => true, 'status' => 'success']);
        if (($data['provider'] ?? '') !== 'groq') return response()->json(['ok' => false, 'error' => 'Step hanya untuk Groq'], 400);

        $cursor = is_array($data['cursor'] ?? null) ? $data['cursor'] : [];
        $lastId = (int) ($cursor['last_id'] ?? 0);
        $limit = (int) ($cursor['limit'] ?? 20);
        $batch = (int) ($cursor['batch'] ?? 5);
        if ($batch < 1) $batch = 1;
        if ($batch > 10) $batch = 10;

        $apiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        // Always use GROQ_MODEL for Groq calls; do not accept qwen3:* (Ollama) here.
        $groqModel = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));
        if ($apiKey === '') {
            $data['status'] = 'error';
            $data['message'] = 'GROQ_API_KEY belum di-set.';
            $data['last_error'] = 'GROQ_API_KEY kosong';
            Cache::put($key, $data, now()->addHour());
            return response()->json(['ok' => false, 'error' => $data['message']], 400);
        }

        // Pick a batch within the same kategori to keep the choice list consistent.
        $first = Soal::where(function($qq) { $qq->whereNull('sub_topik')->orWhere('sub_topik', ''); })
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->first();
        if (!$first) {
            $data['status'] = 'success';
            $data['message'] = 'Selesai auto-label.';
            Cache::put($key, $data, now()->addHour());
            return response()->json(['ok' => true, 'status' => 'success']);
        }

        $items = Soal::where(function($qq) { $qq->whereNull('sub_topik')->orWhere('sub_topik', ''); })
            ->where('kategori', $first->kategori)
            ->where('id', '>=', (int) $first->id)
            ->orderBy('id')
            ->take($batch)
            ->get();

        if ($items->isEmpty()) {
            $data['status'] = 'success';
            $data['message'] = 'Selesai auto-label.';
            Cache::put($key, $data, now()->addHour());
            return response()->json(['ok' => true, 'status' => 'success']);
        }

        $prog = is_array($data['progress'] ?? null) ? $data['progress'] : ['done' => 0, 'total' => 0, 'errors' => 0];
        $done = (int) ($prog['done'] ?? 0);
        $errors = (int) ($prog['errors'] ?? 0);

        try {
            $labels = $this->askGroqToCategorizeBatch($first->kategori, $items, $apiKey, $groqModel);
            foreach ($items as $soal) {
                $sid = (int) $soal->id;
                $sub = is_array($labels) ? ($labels[$sid] ?? null) : null;
                if (is_string($sub) && trim($sub) !== '') {
                    $sub = substr(trim($sub), 0, 100);
                    $soal->update(['sub_topik' => $sub]);
                    $this->pushCategorizeLog($data, "Soal #{$sid} -> OK: {$sub}");
                } else {
                    $errors++;
                    $this->pushCategorizeLog($data, "Soal #{$sid} -> Gagal (label kosong)");
                    $data['last_error'] = 'Output AI tidak memuat label untuk beberapa soal.';
                }
                $done++;
                $lastId = max($lastId, $sid);
            }
            // reset cooldown on success
            $data['cooldown_sec'] = 0;
            $data['cooldown_until'] = 0;
        } catch (\Throwable $e) {
            $errors += (int) $items->count();
            $msg = $e->getMessage();
            $this->pushCategorizeLog($data, "Batch kategori {$first->kategori} gagal: " . substr($msg, 0, 160));
            $data['last_error'] = $msg;

            // If rate limited, backoff.
            if (str_contains($msg, 'HTTP 429')) {
                $sec = (int) ($data['cooldown_sec'] ?? 10);
                if ($sec < 10) $sec = 10;
                $sec = min(120, (int) round($sec * 1.6));
                $data['cooldown_sec'] = $sec;
                $data['cooldown_until'] = time() + $sec;
            } else {
                // small backoff to avoid hammering on repeated failures
                $data['cooldown_until'] = time() + 8;
            }
        }

        $data['progress'] = [
            'done' => $done,
            'total' => (int) (($prog['total'] ?? 0)),
            'errors' => $errors,
        ];
        $data['cursor'] = ['last_id' => $lastId, 'limit' => $limit, 'batch' => $batch];
        $data['message'] = 'Sedang diproses...';
        Cache::put($key, $data, now()->addHour());

        return response()->json(['ok' => true]);
    }

    private function pushCategorizeLog(array &$data, string $line): void
    {
        $log = $data['log'] ?? [];
        if (!is_array($log)) $log = [];
        $ts = date('H:i:s');
        $log[] = '[' . $ts . '] ' . $line;
        if (count($log) > 25) $log = array_slice($log, -25);
        $data['log'] = $log;
    }

    private function askGroqToCategorizeBatch(string $kategori, $items, string $apiKey, string $model): array
    {
        $subTopikTWK = 'Nasionalisme & Sejarah Kemerdekaan, Integritas & Anti Korupsi, Bela Negara & Ancaman Radikalisme, Pilar Negara (Pancasila & UUD 1945), Bahasa Indonesia & Pemahaman Bacaan, Lainnya';
        $subTopikTIU = 'Kemampuan Verbal (Analogi, Silogisme, Analitis), Kemampuan Numerik (Berhitung Dasar, Deret, Kuanti Perbandingan, Soal Cerita), Pemecahan Logika Sehari-hari, Lainnya';
        $subTopikTKP = 'Pelayanan Publik, Jejaring Kerja, Sosial Budaya, TIK, Profesionalisme, Anti Radikalisme, Lainnya';

        $pilihan = match($kategori) {
            'TWK' => $subTopikTWK,
            'TIU' => $subTopikTIU,
            'TKP' => $subTopikTKP,
            default => 'Lainnya',
        };

        $lines = [];
        foreach ($items as $soal) {
            $id = (int) ($soal->id ?? 0);
            $teks = strip_tags(html_entity_decode((string) ($soal->teks_soal ?? '')));
            $teks = preg_replace('/\s+/u', ' ', trim($teks));
            if (function_exists('mb_substr')) {
                if (mb_strlen($teks, 'UTF-8') > 420) $teks = mb_substr($teks, 0, 420, 'UTF-8') . '...';
            } else {
                if (strlen($teks) > 420) $teks = substr($teks, 0, 420) . '...';
            }
            $lines[] = "{$id}: {$teks}";
        }

        $prompt = "Tugas: klasifikasikan beberapa soal CPNS kategori {$kategori} ke sub-topik dari pilihan berikut saja: [{$pilihan}].\n"
            . "WAJIB output JSON MURNI (tanpa markdown) dengan format:\n"
            . "{\"labels\":[{\"id\":123,\"sub_topik\":\"...\"}]}\n"
            . "Syarat: sub_topik harus persis salah satu dari daftar pilihan. Tidak boleh kalimat lain.\n\n"
            . "Daftar Soal (format: id: teks):\n" . implode("\n", $lines);

        $res = Http::withOptions(['verify' => false])
            ->connectTimeout(10)
            ->timeout(45)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.1,
                'max_tokens' => 350,
            ]);

        if (!$res->successful()) {
            $http = $res->status();
            $snippet = substr((string) $res->body(), 0, 220);
            throw new \RuntimeException("Groq error HTTP {$http}: {$snippet}");
        }

        $raw = (string) ($res->json('choices.0.message.content') ?? '');
        $raw = trim($raw);
        $json = null;
        // Extract first JSON object if extra text exists.
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $json = substr($raw, $start, $end - $start + 1);
        } else {
            $json = $raw;
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !is_array($decoded['labels'] ?? null)) {
            $snip = substr($raw, 0, 220);
            throw new \RuntimeException("Groq output bukan JSON labels. Snippet: {$snip}");
        }

        $out = [];
        foreach ($decoded['labels'] as $row) {
            if (!is_array($row)) continue;
            $id = (int) ($row['id'] ?? 0);
            $sub = trim((string) ($row['sub_topik'] ?? ''));
            if ($id > 0 && $sub !== '') $out[$id] = $sub;
        }
        return $out;
    }

    /**
     * Reset all queued jobs (jobs + failed_jobs). Useful when queue is stuck.
     * Admin page is already behind auth middleware.
     */
    public function resetQueue(Request $request)
    {
        // Basic CSRF already enforced by Laravel for POST. Keep output simple.
        $jobs = (int) DB::table('jobs')->count();
        $failed = (int) DB::table('failed_jobs')->count();

        DB::table('jobs')->truncate();
        DB::table('failed_jobs')->truncate();

        return response()->json([
            'ok' => true,
            'deleted' => [
                'jobs' => $jobs,
                'failed_jobs' => $failed,
            ],
        ]);
    }

    /**
     * Quick connectivity test for Groq from the current server.
     * Helps confirm if API key/network is the issue (admin-only).
     */
    public function pingGroq(Request $request)
    {
        $apiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
        $model = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));

        if ($apiKey === '') {
            return response()->json(['ok' => false, 'error' => 'GROQ_API_KEY belum di-set'], 400);
        }

        $t0 = microtime(true);
        try {
            $res = Http::withOptions(['verify' => false])
                ->connectTimeout(8)
                ->timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Balas OK saja.']
                    ],
                    'temperature' => 0,
                    'top_p' => 0.9,
                ]);

            $ms = (int) round((microtime(true) - $t0) * 1000);

            if ($res->failed()) {
                return response()->json([
                    'ok' => false,
                    'http' => $res->status(),
                    'latency_ms' => $ms,
                    'body_snippet' => substr((string) $res->body(), 0, 400),
                    'model' => $model,
                ], 200);
            }

            $text = (string) ($res->json()['choices'][0]['message']['content'] ?? '');
            $text = trim($text);

            return response()->json([
                'ok' => true,
                'http' => $res->status(),
                'latency_ms' => $ms,
                'model' => $model,
                'reply_snippet' => substr($text, 0, 80),
            ], 200);
        } catch (\Throwable $e) {
            $ms = (int) round((microtime(true) - $t0) * 1000);
            return response()->json([
                'ok' => false,
                'http' => 0,
                'latency_ms' => $ms,
                'error' => $e->getMessage(),
                'model' => $model,
            ], 200);
        }
    }

    public function aiCheck()
    {
        return view('admin.ai_check', [
            'ollamaModels' => self::OLLAMA_MODELS,
            'defaultOllamaModel' => (string) config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b')),
            'defaultGroqModel' => (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.1-8b-instant')),
        ]);
    }

    public function retryPersistentJob(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|string|min:8|max:80',
        ]);

        $jobId = (string) $validated['job_id'];
        $existing = $this->readPersistentGenerateJob($jobId);
        if (!is_array($existing)) {
            return back()->with('error', 'Job PDF tidak ditemukan.');
        }
        if ((int) ($existing['user_id'] ?? 0) !== (int) auth()->id()) {
            return back()->with('error', 'Job PDF ini bukan milik akun Anda.');
        }

        $req = is_array($existing['request'] ?? null) ? $existing['request'] : [];
        $pdfPath = (string) ($req['pdf_path'] ?? $existing['pdf_path'] ?? '');
        if ($pdfPath === '' || !Storage::disk('local')->exists($pdfPath)) {
            return back()->with('error', 'File PDF lama tidak ditemukan lagi di server.');
        }

        $progress = is_array($existing['progress'] ?? null) ? $existing['progress'] : [];
        $done = max(0, (int) ($progress['done'] ?? 0));
        $total = max(0, (int) ($progress['total'] ?? ($req['jumlah'] ?? 0)));
        $remaining = $total > 0 ? max(1, $total - $done) : max(1, (int) ($req['jumlah'] ?? 1));

        $queueName = self::PDF_GENERATE_QUEUE;
        $payload = array_replace_recursive($existing, [
            'status' => 'processing',
            'message' => $done > 0
                ? "Melanjutkan generate PDF dari progres {$done}/{$total}..."
                : 'Mengulang generate PDF dari request yang tersimpan...',
            'queued_at' => now()->toISOString(),
            'runner' => 'queue',
            'queue_name' => $queueName,
            'request' => array_replace($req, [
                'jumlah' => $remaining,
                'pdf_path' => $pdfPath,
            ]),
        ]);
        Cache::put('ai_generate:' . $jobId, $payload, now()->addHour());
        $this->writePersistentGenerateJob($jobId, $payload);

        GenerateSoalJob::dispatch(
            $jobId,
            isset($req['kategori']) ? (is_string($req['kategori']) ? trim($req['kategori']) : null) : null,
            $remaining,
            (string) ($req['provider'] ?? 'groq'),
            isset($req['model']) && is_string($req['model']) && trim((string) $req['model']) !== '' ? (string) $req['model'] : null,
            auth()->id(),
            $pdfPath,
            isset($req['fokus_topik']) ? (string) $req['fokus_topik'] : null,
            !empty($req['exam_mode_id']) ? (int) $req['exam_mode_id'] : null,
            !empty($req['sub_category_id']) ? (int) $req['sub_category_id'] : null,
            (string) ($req['difficulty'] ?? 'medium'),
            (bool) ($req['auto_detect_pdf'] ?? true)
        )->onQueue($queueName);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Job PDF dimasukkan lagi ke background tanpa upload ulang.',
                'job_id' => $jobId,
            ]);
        }

        return back()->with('success', 'Job PDF dimasukkan lagi ke background tanpa upload ulang.');
    }

    public function testAi(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:groq,ollama_qwen',
            'mode' => 'required|in:ping,generate',
            'model' => 'nullable|string|max:120',
            'ollama_fast' => 'nullable|boolean',
            'kategori' => 'nullable|in:TWK,TIU,TKP,FIGURAL,TEKNIS,MANAJERIAL,SOSIAL_KULTURAL,WAWANCARA,SKB_TEKNIS,SKB_REGULASI,SKB_STUDI_KASUS',
        ]);

        $provider = (string) $validated['provider'];
        $mode = (string) $validated['mode'];
        $kategori = (string) ($validated['kategori'] ?? 'TIU');
        $t0 = microtime(true);

        try {
            if ($provider === 'groq') {
                $apiKey = preg_replace('/\s+/', '', (string) config('services.groq.api_key', env('GROQ_API_KEY', '')));
                $model = (string) config('services.groq.model', env('GROQ_MODEL', 'llama-3.1-8b-instant'));
                if ($apiKey === '') {
                    return response()->json(['ok' => false, 'error' => 'GROQ_API_KEY belum di-set'], 400);
                }

                $prompt = $mode === 'ping'
                    ? 'Balas OK saja.'
                    : "Buat 1 soal {$kategori} CPNS. Balas JSON valid dengan struktur {\"soal\":[{\"sub_topik\":\"...\",\"teks_soal\":\"...\",\"opsi_a\":\"...\",\"opsi_b\":\"...\",\"opsi_c\":\"...\",\"opsi_d\":\"...\",\"opsi_e\":\"...\",\"jawaban_benar\":\"A\",\"pembahasan\":\"...\"}]} tanpa markdown.";

                $res = Http::withOptions(['verify' => false])
                    ->connectTimeout(8)
                    ->timeout($mode === 'ping' ? 20 : 45)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => $mode === 'ping' ? 0 : 0.3,
                        'top_p' => 0.9,
                        'max_tokens' => $mode === 'ping' ? 40 : 1200,
                    ]);

                $ms = (int) round((microtime(true) - $t0) * 1000);
                if ($res->failed()) {
                    return response()->json([
                        'ok' => false,
                        'provider' => $provider,
                        'mode' => $mode,
                        'model' => $model,
                        'http' => $res->status(),
                        'latency_ms' => $ms,
                        'body_snippet' => substr((string) $res->body(), 0, 500),
                    ]);
                }

                $text = trim((string) ($res->json()['choices'][0]['message']['content'] ?? ''));
                $parsed = null;
                if ($mode === 'generate') {
                    $parsed = $this->extractJsonPayload($text);
                }

                return response()->json([
                    'ok' => $mode === 'ping' ? true : is_array($parsed),
                    'provider' => $provider,
                    'mode' => $mode,
                    'model' => $model,
                    'http' => $res->status(),
                    'latency_ms' => $ms,
                    'reply_snippet' => substr($text, 0, 300),
                    'parsed_ok' => $mode === 'generate' ? is_array($parsed) : true,
                    'sample' => $mode === 'generate' ? ($parsed['soal'][0] ?? null) : null,
                ]);
            }

            $baseUrl = $this->normalizeOllamaBaseUrl((string) config('services.ollama.base_url', env('OLLAMA_BASE_URL', 'https://ollama.rsbaladhikahusada.com')));
            $ollamaFast = (bool) $request->boolean('ollama_fast', false);
            $model = $ollamaFast
                ? 'llama3.2:3b'
                : (string) ($validated['model'] ?? config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b')));
            if (!in_array($model, self::OLLAMA_MODELS, true)) {
                $model = (string) config('services.ollama.model', env('OLLAMA_MODEL', 'qwen2.5-coder:7b'));
            }

            $headers = ['Content-Type' => 'application/json'];
            $clientId = (string) config('services.ollama.access_client_id', env('OLLAMA_ACCESS_CLIENT_ID', ''));
            $clientSecret = (string) config('services.ollama.access_client_secret', env('OLLAMA_ACCESS_CLIENT_SECRET', ''));
            if ($clientId !== '' && $clientSecret !== '') {
                $headers['CF-Access-Client-Id'] = $clientId;
                $headers['CF-Access-Client-Secret'] = $clientSecret;
            }

            if ($mode === 'ping') {
                $res = Http::withOptions(['verify' => false])
                    ->connectTimeout(8)
                    ->timeout(15)
                    ->withHeaders($headers)
                    ->get($baseUrl . '/api/tags');

                $ms = (int) round((microtime(true) - $t0) * 1000);
                if ($res->failed()) {
                    return response()->json([
                        'ok' => false,
                        'provider' => $provider,
                        'mode' => $mode,
                        'model' => $model,
                        'http' => $res->status(),
                        'latency_ms' => $ms,
                        'body_snippet' => substr((string) $res->body(), 0, 500),
                    ]);
                }

                $models = $res->json('models') ?? [];
                $hasModel = collect(is_array($models) ? $models : [])->contains(function ($row) use ($model) {
                    return (string) ($row['name'] ?? '') === $model || (string) ($row['model'] ?? '') === $model;
                });

                // Optional: detect whether the model is currently loaded/running (Ollama >= supports /api/ps).
                $loaded = null;
                try {
                    $ps = Http::withOptions(['verify' => false])
                        ->connectTimeout(4)
                        ->timeout(6)
                        ->withHeaders($headers)
                        ->get($baseUrl . '/api/ps');
                    if ($ps->successful()) {
                        $psModels = $ps->json('models') ?? [];
                        $loaded = collect(is_array($psModels) ? $psModels : [])->contains(function ($row) use ($model) {
                            return (string) ($row['name'] ?? '') === $model || (string) ($row['model'] ?? '') === $model;
                        });
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

                return response()->json([
                    'ok' => true,
                    'provider' => $provider,
                    'mode' => $mode,
                    'model' => $model,
                    'http' => $res->status(),
                    'latency_ms' => $ms,
                    'model_available' => $hasModel,
                    'model_loaded' => $loaded,
                    'models_count' => is_array($models) ? count($models) : 0,
                ]);
            }

            $prompt = "Buat 1 soal {$kategori} CPNS. Balas JSON valid dengan struktur {\"soal\":[{\"sub_topik\":\"...\",\"teks_soal\":\"...\",\"opsi_a\":\"...\",\"opsi_b\":\"...\",\"opsi_c\":\"...\",\"opsi_d\":\"...\",\"opsi_e\":\"...\",\"jawaban_benar\":\"A\",\"pembahasan\":\"...\"}]} tanpa markdown.";

            $res = Http::withOptions(['verify' => false])
                ->connectTimeout(8)
                ->timeout(45)
                ->withHeaders($headers)
                ->post($baseUrl . '/api/chat', [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'stream' => false,
                    'format' => 'json',
                    'keep_alive' => '15m',
                    'options' => [
                        'temperature' => 0.3,
                        'num_predict' => 420,
                    ],
                ]);
            $ms = (int) round((microtime(true) - $t0) * 1000);
            if ($res->failed()) {
                return response()->json([
                    'ok' => false,
                    'provider' => $provider,
                    'mode' => $mode,
                    'model' => $model,
                    'http' => $res->status(),
                    'latency_ms' => $ms,
                    'body_snippet' => substr((string) $res->body(), 0, 500),
                ]);
            }

            $text = trim((string) ($res->json('message.content') ?? ''));
            $parsed = null;
            if ($mode === 'generate') {
                $parsed = $this->extractJsonPayload($text);
            }

            return response()->json([
                'ok' => $mode === 'ping' ? true : is_array($parsed),
                'provider' => $provider,
                'mode' => $mode,
                'model' => $model,
                'http' => $res->status(),
                'latency_ms' => $ms,
                'reply_snippet' => substr($text, 0, 300),
                'parsed_ok' => $mode === 'generate' ? is_array($parsed) : true,
                'sample' => $mode === 'generate' ? ($parsed['soal'][0] ?? null) : null,
            ]);
        } catch (\Throwable $e) {
            $ms = (int) round((microtime(true) - $t0) * 1000);
            return response()->json([
                'ok' => false,
                'provider' => $provider,
                'mode' => $mode,
                'latency_ms' => $ms,
                'http' => 0,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Accept common user inputs like:
     * - https://host
     * - https://host/
     * - https://host/api
     * - https://host/api/tags
     * and normalize them into a clean base URL without trailing /api*.
     */
    private function normalizeOllamaBaseUrl(string $raw): string
    {
        $base = trim((string) $raw);
        if ($base === '') {
            return 'https://ollama.rsbaladhikahusada.com';
        }

        $base = rtrim($base, '/');

        // If user pastes a full endpoint, strip it back to host root.
        // Keep this conservative: only strip known Ollama API endpoints.
        $base = preg_replace('#/api/(tags|chat|ps)$#i', '', $base);
        $base = preg_replace('#/api$#i', '', $base);

        return rtrim((string) $base, '/');
    }

    private function bulkPromptCacheKey(string $token): string
    {
        return 'bulk_gemini_prompt:' . $token;
    }

    private function bulkImportCacheKey(string $token): string
    {
        return 'bulk_gemini_import:' . $token;
    }

    private function buildGeminiBulkPrompt(array $payload): string
    {
        $total = (int) ($payload['total_questions'] ?? 0);
        $difficulty = (string) ($payload['difficulty'] ?? 'medium');
        $kategori = strtoupper(trim((string) ($payload['kategori'] ?? '')));
        $modeName = ($payload['exam_mode_name'] ?: $payload['exam_mode_code'] ?: 'SKD');
        $subCat = ($payload['sub_category_name'] ?: 'Umum');
        $topik = ($payload['fokus_topik'] ?: 'Sesuai kisi-kisi standar CASN 2027');

        if ($kategori === 'TKP') {
            $jsonFormatTkp = <<<JSON
[
  {
    "kategori": "TKP",
    "sub_kategori": "Teknologi Informasi & Komunikasi",
    "standar_tahun": "2027",
    "tingkat_kesulitan": "{$difficulty}",
    "teks_soal": "Situasi kerja dilematis ASN modern yang menuntut efektivitas pelayanan prima dan adaptasi sistem SPBE...",
    "opsi_a": "Teks pernyataan opsi A...",
    "opsi_b": "Teks pernyataan opsi B...",
    "opsi_c": "Teks pernyataan opsi C...",
    "opsi_d": "Teks pernyataan opsi D...",
    "opsi_e": "Teks pernyataan opsi E...",
    "jawaban_benar": "D",
    "bobot_tkp": {
      "A": 3,
      "B": 2,
      "C": 4,
      "D": 5,
      "E": 1
    },
    "pembahasan": "Analisis gradasi bobot profesionalisme ASN: (1) Opsi D berbobot 5 karena menyelesaikan akar masalah secara solutif, proaktif, dan sesuai SOP regulasi 2027; (2) Opsi C berbobot 4 karena...; (3) Opsi A berbobot 3 karena...; (4) Opsi B berbobot 2 karena...; (5) Opsi E berbobot 1 karena bersifat pasif/menghindar."
  }
]
JSON;

            return "Bertindaklah sebagai Senior Psychometrician & Lead Asesor Penyusun Bank Soal CASN BKN Republik Indonesia. Tugas Anda adalah memproduksi {$total} butir naskah soal seleksi CAT SKD - TKP (Tes Karakteristik Pribadi) yang sepenuhnya mengadopsi standar regulasi dan kisi-kisi resmi terbaru CASN 2027 (KemenPAN-RB & BKN) dengan Protokol Zero-Defect (Anti-Kesalahan).\n\n"
                . "SPESIFIKASI UJIAN:\n"
                . "- Mode Ujian: {$modeName}\n"
                . "- Kategori Soal: TKP\n"
                . "- Formasi/Subkategori: {$subCat}\n"
                . "- Tingkat Kesulitan: {$difficulty}\n"
                . "- Fokus Kisi-Kisi 2027: {$topik}\n\n"
                . "STANDAR 6 DIMENSI PENILAIAN TKP CASN 2027 (Passing Grade: 166):\n"
                . "1. Pelayanan Publik (Kepuasan masyarakat, empati prima, responsif, integritas SOP pelayanan)\n"
                . "2. Jejaring Kerja (Kolaborasi lintas instansi/unit, inklusivitas, sinergi tim kerja)\n"
                . "3. Sosial Budaya (Toleransi keberagaman, adaptasi kultur heterogen, perekat persatuan bangsa)\n"
                . "4. TIK (Digital mindset birokrasi, pemanfaatan automasi data, keamanan informasi SPBE)\n"
                . "5. Profesionalisme (Akuntabilitas kinerja, ketahanan di bawah tekanan, integritas etika jabatan)\n"
                . "6. Anti-Radikalisme (Deteksi dini sikap intoleran, loyalitas terhadap ideologi Pancasila & NKRI)\n\n"
                . "PROTOKOL ZERO-DEFECT & ATURAN MUTU TKP:\n"
                . "1. FORMAT SITUATIONAL JUDGMENT TEST (SJT): Teks soal wajib berupa studi kasus dilematis yang realistis di lingkungan birokrasi ASN modern.\n"
                . "2. OPSI REALISTIS & HOMOGEN: Kelima opsi (A s/d E) harus tampak masuk akal dan bernuansa positif bagi orang awam, namun memiliki gradasi kematangan emosi, kepemimpinan, dan efektivitas regulatif yang jelas.\n"
                . "3. ATURAN SKOR BOBOT 1-5 MUTLAK TANPA KEMBAR: Setiap butir soal WAJIB memiliki pembobotan unik 1, 2, 3, 4, dan 5 yang terdistribusi ke opsi A, B, C, D, E (dilarang keras ada dua opsi dengan skor sama).\n"
                . "4. KUNCI JAWABAN TEPAT: Nilai field 'jawaban_benar' adalah huruf opsi yang memiliki bobot tertinggi (skor 5).\n"
                . "5. PEMBAHASAN LENGKAP: Wajib menjabarkan analisis gradasi nilai setiap opsi dari skor 5 hingga 1 secara objektif dan mendidik.\n"
                . "6. KELUARAN STRICT JSON ARRAY: Balas HANYA format JSON Array murni valid tanpa pembungkus markdown (tanpa ```json) dan tanpa teks pengantar atau penutup apapun.\n\n"
                . "CONTOH FORMAT KELUARAN JSON:\n"
                . $jsonFormatTkp;
        }

        $jsonFormat = <<<JSON
[
  {
    "kategori": "{$kategori}",
    "sub_kategori": "{$topik}",
    "standar_tahun": "2027",
    "tingkat_kesulitan": "{$difficulty}",
    "teks_soal": "Narasi studi kasus kontekstual yang jelas, padat, dan menyajikan seluruh parameter logika/data yang diperlukan...",
    "opsi_a": "Teks pernyataan opsi A",
    "opsi_b": "Teks pernyataan opsi B",
    "opsi_c": "Teks pernyataan opsi C",
    "opsi_d": "Teks pernyataan opsi D",
    "opsi_e": "Teks pernyataan opsi E",
    "jawaban_benar": "C",
    "pembahasan": "Langkah pengerjaan komprehensif: (1) Identifikasi inti masalah/variabel yang diketahui, (2) Landasan hukum/teori/rumus matematis yang berlaku, (3) Pembuktian langkah demi langkah hingga opsi C terbukti benar mutlak, (4) Analisis eliminasi mengapa opsi lain gugur/salah."
  }
]
JSON;

        $kisiKisiGuide = match ($kategori) {
            'TWK' => "STANDAR TWK CASN 2027 (Passing Grade: 65, Skor Benar: 5, Salah/Kosong: 0):\n"
                . "- Wajib High Order Thinking Skills (HOTS) berbasis studi kasus riil (problem-solving birokrasi & kebangsaan), BUKAN hafalan nomor pasal/tanggal mentah.\n"
                . "- Kisi-kisi: (1) Nasionalisme & Integritas: Penerapan etika pelayanan publik, mitigasi benturan kepentingan (conflict of interest); (2) Bela Negara: Peran menghadapi ancaman siber, disinformasi, perang asimetris, dan kedaulatan digital; (3) Pilar Negara: Penalaran filosofis Pancasila (studi kasus pengamalan sila), konstitusi UUD NRI 1945, NKRI, dan Bhinneka Tunggal Ika; (4) Bahasa Indonesia Kedinasan: Kaidah EYD V terbaru, koherensi paragraf naskah dinas, dan ketepatan diksi penalaran logis.",
            'TIU' => "STANDAR TIU CASN 2027 (Passing Grade: 80, Skor Benar: 5, Salah/Kosong: 0):\n"
                . "- Wajib Zero-Defect: Data numerik realistis, kalimat lugas tanpa ambiguitas logika, kunci jawaban mutlak tunggal dan terbukti secara matematis/logika formal 100%.\n"
                . "- ATURAN FORMAT VISUAL TIU (WAJIB DITERAPKAN SESUAI TOPIK SOAL):\n"
                . "  1. TABEL DATA (Untuk Analisis Informasi/Tabel/Grafik): Sajikan data tabel menggunakan format Markdown pipe-separated (| Kolom 1 | Kolom 2 | ...) yang rapi dan konsisten pada teks_soal, lalu letakkan pertanyaan inti di bawah tabel (contoh: persentase kenaikan, perbandingan rasio, selisih data, atau proyeksi nilai).\n"
                . "  2. ANALOGI KATA: Tuliskan pasangan kata dengan format 'KATA1 : KATA2 = KATA3 : ?' (atau KATA3 : [opsi]) yang memiliki relasi fungsi/sebab-akibat/tingkatan yang ketat.\n"
                . "  3. SILOGISME LOGIS: Tulis premis secara terpisah dan eksplisit: 'Premis 1: ...\\nPremis 2: ...\\nKesimpulan yang tepat adalah...'.\n"
                . "  4. PERBANDINGAN KUANTITATIF: Sajikan dua nilai/rumus bersyarat: 'x = ... dan y = ... Tentukan hubungan yang tepat antara nilai x dan y!'.\n"
                . "  5. DERET ANGKA/HURUF: Sajikan barisan pola dipisahkan koma: 'a, b, c, d, ...' dengan pola berulang atau bertingkat yang konsisten.",
            'TEKNIS', 'SKB_TEKNIS', 'SKB_REGULASI', 'SKB_STUDI_KASUS' => "STANDAR SKB TEKNIS FORMASI CASN 2027 (Skor Benar: 5, Salah/Kosong: 0):\n"
                . "- 30% Kompetensi Umum: Regulasi sektoral nasional, tugas pokok dan fungsi jabatan fungsional, etika profesi.\n"
                . "- 70% Kompetensi Khusus: Prosedur teknis operasional, telaah kasus dan investigasi kendala kerja, implementasi instrumen, dan penegakan SOP lapangan formasi {$subCat}.",
            default => "STANDAR RESMI CASN 2027 (KemenPAN-RB & BKN):\n"
                . "- Soal pilihan ganda berstandar seleksi aparatur sipil negara modern dengan penalaran analitis berbobot tinggi."
        };

        return "Bertindaklah sebagai Senior Psychometrician & Lead Asesor Penyusun Bank Soal CASN BKN Republik Indonesia. Tugas Anda adalah memproduksi {$total} butir naskah soal seleksi CAT ({$kategori}) yang sepenuhnya mengadopsi standar regulasi dan kisi-kisi resmi terbaru CASN 2027 (KemenPAN-RB & BKN) dengan Protokol Zero-Defect & Reverse-Solving (Anti-Kesalahan).\n\n"
            . "SPESIFIKASI UJIAN:\n"
            . "- Mode Ujian: {$modeName}\n"
            . "- Kategori Soal: {$kategori}\n"
            . "- Formasi/Subkategori: {$subCat}\n"
            . "- Tingkat Kesulitan: {$difficulty}\n"
            . "- Fokus Kisi-Kisi 2027: {$topik}\n\n"
            . "PANDUAN KISI-KISI STANDAR 2027:\n"
            . "{$kisiKisiGuide}\n\n"
            . "PROTOKOL ZERO-DEFECT & METODE REVERSE-SOLVING (WAJIB & KETAT):\n"
            . "1. METODE REVERSE-SOLVING (Kunci Dihitung Dahulu):\n"
            . "   - Untuk soal Numerik, Aljabar, Silogisme, atau Analitis: Selesaikan dan buktikan perhitungannya secara matematis/logika terlebih dahulu. Pastikan hasilnya merupakan bilangan rasional yang tuntas (tidak ada angka koma ganjil/pembulatan tak selesai). Baru kemudian susun narasi soal cerita berdasarkan variabel yang terbukti tersebut.\n"
            . "   - Kunci jawaban WAJIB tunggal dan mutlak benar tanpa kemungkinan multi-tafsir.\n"
            . "2. ELIMINASI AMBIGUITAS DISTRAKTOR (4 Opsi Pengecoh):\n"
            . "   - 4 opsi salah harus dirancang berdasarkan kesalahan perhitungan umum peserta (predictable misconception), namun secara objektif terbukti salah dan digugurkan secara tuntas pada pembahasan.\n"
            . "3. INTEGRITAS SOAL STUDI KASUS (HOTS 2027):\n"
            . "   - Seluruh variabel yang dibutuhkan peserta untuk menjawab soal wajib tertera lengkap di dalam teks soal (tidak boleh ada informasi menggantung).\n"
            . "   - Bahasa Indonesia kedinasan baku sesuai EYD V, kalimat efektif, tanpa kesalahan tik (typo).\n"
            . "4. STRUKTUR PEMBAHASAN SISTEMATIS:\n"
            . "   - Wajib memuat: (1) Inti pokok masalah, (2) Formula/landasan hukum/rumus, (3) Pembuktian langkah demi langkah kunci benar, dan (4) Analisis eliminasi mengapa opsi lain gugur.\n"
            . "5. FORMAT KELUARAN STRICT VALID JSON ONLY:\n"
            . "   - Keluarkan HANYA blok JSON Array valid (RFC 8259, diawali [ dan diakhiri ]) tanpa pembungkus markdown (tanpa ```json) dan tanpa teks pengantar atau penutup apapun.\n\n"
            . "CONTOH FORMAT KELUARAN JSON:\n"
            . $jsonFormat;
    }

    private function parseGeminiBulkRows(string $raw, array $requestConfig): array
    {
        $clean = trim((string) preg_replace('/```(?:json)?|```/i', '', $raw));
        $decoded = json_decode($clean, true);

        // Fallback: ekstrak substring JSON array [ ... ] bila terdapat teks narasi pengantar AI
        if (!is_array($decoded)) {
            $start = strpos($clean, '[');
            $end = strrpos($clean, ']');
            if ($start !== false && $end !== false && $end > $start) {
                $sub = substr($clean, $start, $end - $start + 1);
                $decoded = json_decode($sub, true);
            }
        }

        // Fallback: ekstrak substring JSON object { ... } bila dibungkus objek utama
        if (!is_array($decoded)) {
            $start = strpos($clean, '{');
            $end = strrpos($clean, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $sub = substr($clean, $start, $end - $start + 1);
                $decoded = json_decode($sub, true);
            }
        }

        $rows = null;
        if (is_array($decoded)) {
            $rows = array_is_list($decoded) ? $decoded : ($decoded['data'] ?? $decoded['questions'] ?? null);
        }

        $requestKategori = strtoupper(trim((string) ($requestConfig['kategori'] ?? '')));

        if (!is_array($rows)) {
            return [[
                'valid' => false,
                'errors' => ['JSON Gemini tidak valid atau bukan berupa array butir soal. Pastikan format teks JSON sudah benar.'],
                'soal' => '',
                'A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => '',
                'jawaban' => '',
                'score_map' => null,
                'question_type' => $requestKategori === 'TKP' ? 'tkp' : 'objective',
                'pembahasan' => '',
                'sub_topik' => '',
                'difficulty' => (string) ($requestConfig['difficulty'] ?? ''),
            ]];
        }

        $normalized = [];
        foreach ($rows as $row) {
            $item = is_array($row) ? $row : [];
            $scoreMap = $item['bobot_tkp'] ?? $item['skor'] ?? $item['scores'] ?? $item['score_map'] ?? null;
            $isTkp = $requestKategori === 'TKP' || is_array($scoreMap);
            $normalizedRow = [
                'soal' => trim((string) ($item['soal'] ?? $item['teks_soal'] ?? $item['question'] ?? '')),
                'A' => trim((string) ($item['A'] ?? $item['opsi_a'] ?? $item['a'] ?? '')),
                'B' => trim((string) ($item['B'] ?? $item['opsi_b'] ?? $item['b'] ?? '')),
                'C' => trim((string) ($item['C'] ?? $item['opsi_c'] ?? $item['c'] ?? '')),
                'D' => trim((string) ($item['D'] ?? $item['opsi_d'] ?? $item['d'] ?? '')),
                'E' => trim((string) ($item['E'] ?? $item['opsi_e'] ?? $item['e'] ?? '')),
                'jawaban' => $isTkp ? '' : strtoupper(trim((string) ($item['jawaban_benar'] ?? $item['kunci_jawaban'] ?? $item['jawaban'] ?? $item['kunci'] ?? $item['answer'] ?? ''))),
                'score_map' => is_array($scoreMap) ? $scoreMap : null,
                'question_type' => $isTkp ? 'tkp' : 'objective',
                'pembahasan' => trim((string) ($item['pembahasan'] ?? $item['explanation'] ?? '')),
                'sub_topik' => trim((string) ($item['sub_topik'] ?? $item['sub_kategori'] ?? $item['subtopic'] ?? '')),
                'difficulty' => trim((string) ($item['difficulty'] ?? $item['tingkat_kesulitan'] ?? $requestConfig['difficulty'] ?? '')),
            ];

            $errors = [];
            $requiredFields = ['soal', 'A', 'B', 'C', 'D', 'E', 'pembahasan'];
            if (!$isTkp) {
                $requiredFields[] = 'jawaban';
            }
            foreach ($requiredFields as $field) {
                if ($normalizedRow[$field] === '') {
                    $errors[] = "Field {$field} wajib diisi.";
                }
            }

            if ($isTkp) {
                if (!is_array($normalizedRow['score_map'])) {
                    $errors[] = 'Field skor wajib ada untuk soal TKP.';
                } else {
                    $scoreKeys = ['A', 'B', 'C', 'D', 'E'];
                    foreach ($scoreKeys as $key) {
                        if (!array_key_exists($key, $normalizedRow['score_map'])) {
                            $errors[] = "Skor opsi {$key} wajib ada.";
                            continue;
                        }
                        $val = (int) $normalizedRow['score_map'][$key];
                        if ($val < 1 || $val > 5) {
                            $errors[] = "Skor opsi {$key} harus 1 sampai 5.";
                        }
                        $normalizedRow['score_map'][$key] = $val;
                    }
                    // Di standar 2027, jika ada field jawaban/kunci_jawaban di TKP, abaikan atau gunakan sebagai penegas
                    if (empty($normalizedRow['jawaban']) && !empty($normalizedRow['score_map'])) {
                        $maxKey = array_search(max($normalizedRow['score_map']), $normalizedRow['score_map'], true);
                        if ($maxKey) {
                            $normalizedRow['jawaban'] = (string) $maxKey;
                        }
                    }
                }
            } elseif ($normalizedRow['jawaban'] !== '' && !preg_match('/^[A-E]$/', $normalizedRow['jawaban'])) {
                $errors[] = 'Jawaban harus A sampai E.';
            }

            $normalizedRow['valid'] = empty($errors);
            $normalizedRow['errors'] = $errors;
            $normalized[] = $normalizedRow;
        }

        $expectedCount = (int) ($requestConfig['total_questions'] ?? 0);
        if ($expectedCount > 0 && count($normalized) !== $expectedCount) {
            array_unshift($normalized, [
                'valid' => false,
                'errors' => ["Jumlah soal dari Gemini = " . count($normalized) . ", padahal diminta {$expectedCount}."],
                'soal' => '',
                'A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => '',
                'jawaban' => '',
                'score_map' => null,
                'question_type' => $requestKategori === 'TKP' ? 'tkp' : 'objective',
                'pembahasan' => '',
                'sub_topik' => '',
                'difficulty' => (string) ($requestConfig['difficulty'] ?? ''),
            ]);
        }

        return $normalized;
    }

    private function buildBulkDifficultyPlan(int $totalQuestions, string $difficulty): array
    {
        $steps = [];
        $addChunked = function (string $level, int $count) use (&$steps) {
            $remaining = max(0, $count);
            while ($remaining > 0) {
                $batch = min(10, $remaining);
                $steps[] = [
                    'difficulty' => $level,
                    'jumlah' => $batch,
                ];
                $remaining -= $batch;
            }
        };

        if ($difficulty !== 'mix') {
            $addChunked($difficulty, $totalQuestions);
            return $steps;
        }

        $easy = (int) floor($totalQuestions * 0.30);
        $medium = (int) floor($totalQuestions * 0.50);
        $hard = max(0, $totalQuestions - $easy - $medium);

        $addChunked('easy', $easy);
        $addChunked('medium', $medium);
        $addChunked('hard', $hard);

        return $steps;
    }

    private function extractJsonPayload(string $raw): ?array
    {
        $text = trim(preg_replace('/```(?:json)?|```/i', '', $raw));
        $data = json_decode($text, true);
        if (is_array($data)) {
            return $data;
        }
        if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
            $data = json_decode((string) $m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }
        return null;
    }
}
