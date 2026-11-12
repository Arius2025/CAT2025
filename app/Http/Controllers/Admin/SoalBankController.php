<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\ExamMode;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Support\ExamStructureSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Jobs\FixKategoriAiJob;
use App\Services\AISoalService;
use App\Services\SoalBackupService;

class SoalBankController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $kategori = strtoupper(trim((string) $request->query('kategori', '')));
        $examModeId = (int) $request->query('exam_mode_id', 0);
        $subCategoryId = (int) $request->query('sub_category_id', 0);
        $subTopik = trim((string) $request->query('sub_topik', ''));
        $difficulty = trim((string) $request->query('difficulty', ''));
        $standarTahun = trim((string) $request->query('standar_tahun', ''));

        $query = Soal::query()->with(['examMode', 'subCategory'])->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
                $q->orWhere('teks_soal', 'like', '%' . $search . '%')
                    ->orWhere('pembahasan', 'like', '%' . $search . '%')
                    ->orWhere('sub_topik', 'like', '%' . $search . '%');
            });
        }
        if ($kategori !== '') {
            if ($kategori === 'FIGURAL') {
                $query->where('kategori', 'TIU')
                    ->where(function ($q) {
                        if (Schema::hasColumn('soals', 'prompt_gambar_soal')) {
                            $q->whereNotNull('prompt_gambar_soal');
                        }
                        $q->orWhere('sub_topik', 'LIKE', '%Figural%')
                            ->orWhere('sub_topik', 'LIKE', '%Spasial%')
                            ->orWhere('sub_topik', 'LIKE', '%Visual%')
                            ->orWhere('sub_topik', 'LIKE', '%Gambar%')
                            ->orWhere('sub_topik', 'LIKE', '%Pola%');
                    });
            } else {
                $query->where('kategori', $kategori);
            }
        }
        if ($subTopik !== '') {
            $query->where('sub_topik', 'like', '%' . $subTopik . '%');
        }
        if ($examModeId > 0) {
            $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
            $query->forExamMode($examModeId, $defaultSkdId);
        }
        if ($subCategoryId > 0) {
            $query->forSubCategory($subCategoryId);
        }
        if ($difficulty !== '' && Schema::hasColumn('soals', 'difficulty')) {
            $query->where('difficulty', $difficulty);
        }
        if ($standarTahun !== '' && Schema::hasColumn('soals', 'standar_tahun')) {
            if ($standarTahun === '2027') {
                $query->where('standar_tahun', '2027');
            } elseif ($standarTahun === 'pra-2027') {
                $query->where(function ($q) {
                    $q->whereNull('standar_tahun')->orWhere('standar_tahun', '!=', '2027');
                });
            }
        }

        $soals = $query->paginate(20)->appends($request->query());
        $examModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();
        $subCategories = ($examModeId > 0 && Schema::hasTable('sub_categories'))
            ? SubCategory::query()->where('exam_mode_id', $examModeId)->byKind('formasi')->orderBy('name')->get(['id', 'name', 'exam_mode_id', 'kind'])
            : collect();
        $selectedMode = $examModes->firstWhere('id', $examModeId);

        $skdModeId = $examModes->firstWhere('code', 'skd')?->id ?? 1;
        $skbModeId = $examModes->firstWhere('code', 'skb')?->id ?? 2;
        $pppkModeId = $examModes->firstWhere('code', 'pppk')?->id ?? 3;
        $modeStats = [
            'total' => Soal::count(),
            'standar_2027' => Schema::hasColumn('soals', 'standar_tahun') ? Soal::where('standar_tahun', '2027')->count() : 0,
            'pra_2027' => Schema::hasColumn('soals', 'standar_tahun') ? Soal::where(function($q) { $q->whereNull('standar_tahun')->orWhere('standar_tahun', '!=', '2027'); })->count() : 0,
            'unlabeled' => Soal::whereNull('sub_topik')->orWhere('sub_topik', '')->count(),
            'skd' => Soal::where('exam_mode_id', $skdModeId)->orWhereNull('exam_mode_id')->count(),
            'skb' => Soal::where('exam_mode_id', $skbModeId)->count(),
            'pppk' => Soal::where('exam_mode_id', $pppkModeId)->count(),
            'figural' => Schema::hasColumn('soals', 'prompt_gambar_soal')
                ? Soal::whereNotNull('prompt_gambar_soal')->count()
                : Soal::where('sub_topik', 'LIKE', '%Figural%')->count(),
            'unlabeled' => Soal::whereNull('sub_topik')->orWhere('sub_topik', '')->count(),
        ];

        $categoryOptions = collect();
        if ($examModeId > 0) {
            $categoryOptions = ExamStructureSupport::categoryOptions($examModeId, $selectedMode?->code)->values();
        } else {
            $seen = [];
            foreach ($examModes as $mode) {
                foreach (ExamStructureSupport::categoryOptions((int) $mode->id, (string) $mode->code) as $row) {
                    $key = strtoupper(trim((string) ($row->value ?? '')));
                    if ($key === '' || isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $categoryOptions->push($row);
                }
            }
            if (!$categoryOptions->firstWhere('value', 'FIGURAL')) {
                $categoryOptions->push((object) ['value' => 'FIGURAL', 'label' => 'Figural', 'score_type' => 'objective']);
            }
        }

        return view('admin.soal.index', compact(
            'soals',
            'examModes',
            'subCategories',
            'modeStats',
            'categoryOptions',
            'search',
            'kategori',
            'examModeId',
            'subCategoryId',
            'subTopik',
            'difficulty',
            'standarTahun',
            'selectedMode'
        ));
    }

    public function export(Request $request, SoalBackupService $backupService)
    {
        $format = strtolower((string) $request->query('format', 'json'));
        $filters = $request->all();

        if ($format === 'csv') {
            return $backupService->exportCsv($filters);
        }

        return $backupService->exportJson($filters);
    }

    public function import(Request $request, SoalBackupService $backupService)
    {
        $request->validate([
            'backup_file' => 'required|file|max:20480',
            'standar_tahun' => 'nullable|in:2027,pra-2027,keep',
            'default_exam_mode_id' => 'nullable|integer',
        ], [
            'backup_file.required' => 'Silakan pilih berkas backup (.json atau .csv) terlebih dahulu.',
            'backup_file.max' => 'Ukuran berkas backup maksimal 20 MB.',
        ]);

        $options = [
            'standar_tahun' => $request->input('standar_tahun', '2027'),
            'default_exam_mode_id' => $request->input('default_exam_mode_id'),
        ];

        $result = $backupService->import($request->file('backup_file'), $options);

        $imported = $result['imported'] ?? 0;
        $duplicates = $result['skipped_duplicates'] ?? 0;
        $errors = $result['errors'] ?? 0;
        $total = $result['total'] ?? 0;

        $msgParts = [];
        $msgParts[] = "{$imported} butir soal baru berhasil diimpor.";
        if ($duplicates > 0) {
            $msgParts[] = "{$duplicates} butir soal duplikat dilewati secara otomatis (sudah ada di database).";
        }
        if ($errors > 0) {
            $msgParts[] = "{$errors} baris dilewati karena format tidak valid.";
        }

        $flashType = ($imported > 0 || ($imported === 0 && $duplicates > 0 && $errors === 0)) ? 'success' : 'error';

        return redirect()->route('admin.soal-bank.index')
            ->with($flashType, implode(' ', $msgParts))
            ->with('import_stats', $result);
    }

    public function create()
    {
        $examModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();
        $defaultMode = $examModes->firstWhere('code', 'skd') ?? $examModes->first();
        $subCategories = (Schema::hasTable('sub_categories') && $defaultMode)
            ? SubCategory::query()->where('exam_mode_id', $defaultMode->id)->byKind('formasi')->orderBy('name')->get(['id', 'name', 'exam_mode_id', 'kind'])
            : collect();
        $categoryOptions = $defaultMode
            ? ExamStructureSupport::categoryOptions((int) $defaultMode->id, (string) $defaultMode->code)->values()
            : collect([(object) ['value' => 'TWK', 'label' => 'TWK'], (object) ['value' => 'TIU', 'label' => 'TIU'], (object) ['value' => 'TKP', 'label' => 'TKP'], (object) ['value' => 'FIGURAL', 'label' => 'Figural']]);

        return view('admin.soal.create', compact('examModes', 'subCategories', 'defaultMode', 'categoryOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSoal($request);
        $soal = new Soal();
        $this->fillAndSave($soal, $validated);

        return redirect()
            ->route('admin.soal.edit', $soal->id)
            ->with('success', 'Soal baru berhasil dibuat.');
    }

    public function edit(Request $request, int $id)
    {
        $soal = Soal::findOrFail($id);
        $examModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();
        $selectedMode = $examModes->firstWhere('id', $soal->exam_mode_id)
            ?? $examModes->firstWhere('code', 'skd')
            ?? $examModes->first();
        $subCategories = (Schema::hasTable('sub_categories') && !empty($soal->exam_mode_id))
            ? SubCategory::query()->where('exam_mode_id', $soal->exam_mode_id)->byKind('formasi')->orderBy('name')->get(['id', 'name', 'exam_mode_id', 'kind'])
            : collect();
        $categoryOptions = $selectedMode
            ? ExamStructureSupport::categoryOptions((int) $selectedMode->id, (string) $selectedMode->code)->values()
            : collect([(object) ['value' => 'TWK', 'label' => 'TWK'], (object) ['value' => 'TIU', 'label' => 'TIU'], (object) ['value' => 'TKP', 'label' => 'TKP'], (object) ['value' => 'FIGURAL', 'label' => 'Figural']]);
        return view('admin.soal.edit', compact('soal', 'examModes', 'subCategories', 'selectedMode', 'categoryOptions'));
    }

    public function update(Request $request, int $id)
    {
        $soal = Soal::findOrFail($id);
        $validated = $this->validateSoal($request);
        $this->fillAndSave($soal, $validated);

        if ($request->expectsJson()) {
            $soal->load(['examMode', 'subCategory']);

            return response()->json([
                'ok' => true,
                'message' => 'Soal berhasil disimpan.',
                'soal' => [
                    'id' => (int) $soal->id,
                    'kategori' => (string) $soal->kategori,
                    'sub_topik' => (string) ($soal->sub_topik ?? ''),
                    'difficulty' => (string) ($soal->difficulty ?? ''),
                    'jawaban_benar' => (string) ($soal->jawaban_benar ?? ''),
                    'exam_mode_id' => $soal->exam_mode_id ? (int) $soal->exam_mode_id : null,
                    'exam_mode_name' => (string) ($soal->examMode->name ?? ''),
                    'exam_mode_color' => (string) ($soal->examMode->color ?? '#aaa'),
                    'sub_category_id' => $soal->sub_category_id ? (int) $soal->sub_category_id : null,
                    'sub_category_name' => (string) ($soal->subCategory->name ?? ''),
                    'teks_soal' => (string) $soal->teks_soal,
                ],
            ]);
        }

        return redirect()
            ->route('admin.audit-soal.index', ['soal_id' => $soal->id])
            ->with('success', 'Soal berhasil disimpan.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:soals,id',
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->unique()->values();

        if (Schema::hasTable('bookmarks')) {
            Bookmark::query()->whereIn('question_id', $ids)->delete();
        }

        Soal::query()->whereIn('id', $ids)->delete();

        return redirect()
            ->route('admin.soal-bank.index')
            ->with('success', $ids->count() . ' soal berhasil dihapus.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:soals,id',
            'bulk_kategori' => 'nullable|string|max:50',
            'bulk_sub_topik' => 'nullable|string|max:120',
            'bulk_clear_sub_topik' => 'nullable|boolean',
            'bulk_difficulty' => 'nullable|in:easy,medium,hard',
            'bulk_standar_tahun' => 'nullable|in:2027,pra-2027',
            'bulk_exam_mode_id' => 'nullable|integer',
            'bulk_sub_category_id' => 'nullable|integer',
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $payload = [];

        if ($request->has('bulk_kategori') && !empty($validated['bulk_kategori'])) {
            $payload['kategori'] = $validated['bulk_kategori'];
        }
        if ($request->boolean('bulk_clear_sub_topik')) {
            $payload['sub_topik'] = null;
        } elseif ($request->has('bulk_sub_topik') && trim((string) ($validated['bulk_sub_topik'] ?? '')) !== '') {
            $payload['sub_topik'] = trim((string) $validated['bulk_sub_topik']);
        }
        if (Schema::hasColumn('soals', 'difficulty') && $request->has('bulk_difficulty')) {
            $payload['difficulty'] = $validated['bulk_difficulty'] ?: null;
        }
        if (Schema::hasColumn('soals', 'standar_tahun') && $request->has('bulk_standar_tahun') && !empty($validated['bulk_standar_tahun'])) {
            $payload['standar_tahun'] = $validated['bulk_standar_tahun'];
        }
        if (Schema::hasColumn('soals', 'exam_mode_id') && $request->has('bulk_exam_mode_id')) {
            $payload['exam_mode_id'] = !empty($validated['bulk_exam_mode_id']) ? (int) $validated['bulk_exam_mode_id'] : null;
        }
        if (Schema::hasColumn('soals', 'sub_category_id') && $request->has('bulk_sub_category_id')) {
            $payload['sub_category_id'] = !empty($validated['bulk_sub_category_id']) ? (int) $validated['bulk_sub_category_id'] : null;
        }

        if (empty($payload)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Belum ada perubahan massal yang dipilih.',
                    'updated_count' => 0,
                ]);
            }
            return redirect()
                ->route('admin.soal-bank.index')
                ->with('success', 'Belum ada perubahan massal yang dipilih.');
        }

        if (Schema::hasColumn('soals', 'audit_status')) {
            $payload['audit_status'] = 'needs_reaudit';
            $payload['audited_at'] = null;
            $payload['audited_by'] = null;
            $payload['last_audit_source'] = 'bulk_edit';
        }

        Soal::query()->whereIn('id', $ids)->update($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $ids->count() . ' soal berhasil diperbarui.',
                'updated_count' => $ids->count(),
            ]);
        }

        return redirect()
            ->route('admin.soal-bank.index')
            ->with('success', $ids->count() . ' soal berhasil diperbarui.');
    }

    /**
     * Auto-fix kategori soal PPPK/SKB yang masih nyasar ke TWK/TIU/TKP (legacy).
     * Runs async via queue to avoid timeouts.
     */
    public function fixKategoriAi(Request $request)
    {
        $validated = $request->validate([
            'exam_mode_id' => 'required|integer',
            'provider' => 'nullable|in:groq,ollama_qwen',
            'model' => 'nullable|string|max:120',
            'limit' => 'nullable|integer|min:1|max:2000',
            'runner' => 'nullable|in:step,queue',
        ]);

        $examModeId = (int) $validated['exam_mode_id'];
        $provider = (string) ($validated['provider'] ?? 'groq');
        $model = isset($validated['model']) && is_string($validated['model']) && trim($validated['model']) !== ''
            ? trim($validated['model'])
            : null;
        $limit = (int) ($validated['limit'] ?? 400);
        if ($limit < 1) $limit = 1;
        if ($limit > 2000) $limit = 2000;

        $runner = (string) ($validated['runner'] ?? 'step');
        if (!in_array($runner, ['step', 'queue'], true)) $runner = 'step';

        $mode = Schema::hasTable('exam_modes') ? ExamMode::query()->find($examModeId) : null;
        $modeCode = strtolower((string) ($mode->code ?? ''));
        if ($modeCode === '' || $modeCode === 'skd') {
            return response()->json(['ok' => false, 'error' => 'Pilih mode PPPK atau SKB dulu.'], 400);
        }

        $allowed = ExamStructureSupport::categoryOptions($examModeId, $modeCode)
            ->pluck('value')
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->filter(fn ($v) => $v !== '' && $v !== 'FIGURAL')
            ->unique()
            ->values()
            ->all();

        if (empty($allowed)) {
            return response()->json(['ok' => false, 'error' => 'Kategori mode ini belum ada. Rapikan di Struktur Ujian.'], 400);
        }

        $legacyCats = ['TWK', 'TIU', 'TKP'];
        $q = Soal::query()
            ->where('exam_mode_id', $examModeId)
            ->where(function ($qq) use ($legacyCats) {
                $qq->whereNull('kategori')->orWhere('kategori', '')->orWhereIn('kategori', $legacyCats);
            });
        $total = min((int) $q->count(), $limit);

        $jobId = (string) Str::uuid();
        $key = 'ai_fix_kategori:' . $jobId;
        Cache::put($key, [
            'status' => 'processing',
            'message' => $runner === 'queue' ? 'Permintaan diterima. Menunggu worker...' : 'Permintaan diterima. Siap diproses bertahap...',
            'user_id' => auth()->id(),
            'queued_at' => now()->toISOString(),
            'runner' => $runner,
            'exam_mode_id' => $examModeId,
            'mode_code' => $modeCode,
            'provider' => $provider,
            'model' => $model,
            'allowed' => $allowed,
            'cursor' => ['last_id' => 0, 'batch' => 1, 'limit' => $limit],
            'progress' => ['done' => 0, 'total' => $total, 'changed' => 0, 'errors' => 0],
            'log' => [],
        ], now()->addHour());

        $jobs = (array) session('ai_fix_kategori_jobs', []);
        $jobs[] = $jobId;
        session(['ai_fix_kategori_jobs' => array_values(array_unique($jobs))]);

        if ($runner === 'queue') {
            FixKategoriAiJob::dispatch($jobId, $examModeId, $provider, $model, $limit, auth()->id());
        }

        return response()->json(['ok' => true, 'job_id' => $jobId, 'runner' => $runner, 'total' => $total]);
    }

    public function fixKategoriAiStatus(Request $request)
    {
        $jobId = (string) $request->query('id', '');
        if ($jobId === '') return response()->json(['status' => 'invalid'], 400);

        $allowed = in_array($jobId, (array) session('ai_fix_kategori_jobs', []), true);
        if (!$allowed) return response()->json(['status' => 'not_found'], 404);

        $data = Cache::get('ai_fix_kategori:' . $jobId);
        if (!is_array($data)) return response()->json(['status' => 'unknown'], 404);

        return response()->json($data);
    }

    /**
     * Step runner: processes 1 batch and returns updated status.
     * This avoids dependence on queue workers and reduces risk of long requests.
     */
    public function fixKategoriAiStep(Request $request, AISoalService $ai)
    {
        $validated = $request->validate([
            'job_id' => 'required|string|max:64',
        ]);
        $jobId = (string) $validated['job_id'];
        $allowedSession = in_array($jobId, (array) session('ai_fix_kategori_jobs', []), true);
        if (!$allowedSession) return response()->json(['ok' => false, 'error' => 'Job tidak ditemukan.'], 404);

        $key = 'ai_fix_kategori:' . $jobId;
        $data = Cache::get($key);
        if (!is_array($data)) return response()->json(['ok' => false, 'error' => 'Status job hilang.'], 404);

        if (($data['runner'] ?? 'step') !== 'step') {
            return response()->json(['ok' => false, 'error' => 'Job ini bukan runner step.'], 400);
        }

        if (($data['status'] ?? '') !== 'processing') {
            return response()->json(['ok' => true, 'status' => $data['status'] ?? 'unknown']);
        }

        $examModeId = (int) ($data['exam_mode_id'] ?? 0);
        $modeCode = (string) ($data['mode_code'] ?? '');
        $provider = (string) ($data['provider'] ?? 'groq');
        $model = isset($data['model']) ? (string) $data['model'] : null;
        $allowed = is_array($data['allowed'] ?? null) ? $data['allowed'] : [];
        $cursor = is_array($data['cursor'] ?? null) ? $data['cursor'] : ['last_id' => 0, 'batch' => 1, 'limit' => 400];

        $lastId = (int) ($cursor['last_id'] ?? 0);
        $batch = (int) ($cursor['batch'] ?? 1);
        if ($batch < 1) $batch = 1;
        if ($batch > 3) $batch = 3;
        $limit = (int) ($cursor['limit'] ?? 400);
        if ($limit < 1) $limit = 1;
        if ($limit > 2000) $limit = 2000;

        $legacyCats = ['TWK', 'TIU', 'TKP'];
        $rows = Soal::query()
            ->where('exam_mode_id', $examModeId)
            ->where('id', '>', $lastId)
            ->where(function ($q) use ($legacyCats) {
                $q->whereNull('kategori')->orWhere('kategori', '')->orWhereIn('kategori', $legacyCats);
            })
            ->orderBy('id')
            ->limit($batch)
            ->get(['id', 'kategori', 'teks_soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'sub_topik']);

        $progress = is_array($data['progress'] ?? null) ? $data['progress'] : ['done' => 0, 'total' => 0, 'changed' => 0, 'errors' => 0];
        $done = (int) ($progress['done'] ?? 0);
        $total = (int) ($progress['total'] ?? 0);
        $changed = (int) ($progress['changed'] ?? 0);
        $errors = (int) ($progress['errors'] ?? 0);
        $log = is_array($data['log'] ?? null) ? $data['log'] : [];

        if ($rows->isEmpty() || ($done >= $total && $total > 0)) {
            $data['status'] = 'success';
            $data['message'] = "Selesai. Diubah: {$changed}, error: {$errors}.";
            $data['finished_at'] = now()->toISOString();
            Cache::put($key, $data, now()->addHour());
            return response()->json(['ok' => true, 'status' => 'success']);
        }

        foreach ($rows as $soal) {
            $done++;
            $lastId = (int) $soal->id;
            try {
                $new = $ai->classifyKategoriSoal($soal, $allowed, $modeCode, $provider, $model);
                $new = is_string($new) ? strtoupper(trim($new)) : null;
                if ($new && in_array($new, $allowed, true) && (string) $soal->kategori !== $new) {
                    Soal::query()->where('id', (int) $soal->id)->update(['kategori' => $new]);
                    $changed++;
                    $log[] = '#'.$soal->id.' -> '.$new;
                }
            } catch (\Throwable $e) {
                $errors++;
                $log[] = '#'.$soal->id.' error: '.substr($e->getMessage(), 0, 90);
            }
        }

        if (count($log) > 20) $log = array_slice($log, -20);
        $data['cursor'] = ['last_id' => $lastId, 'batch' => $batch, 'limit' => $limit];
        $data['progress'] = ['done' => $done, 'total' => $total, 'changed' => $changed, 'errors' => $errors];
        $data['log'] = $log;
        $data['updated_at'] = now()->toISOString();
        Cache::put($key, $data, now()->addHour());

        return response()->json(['ok' => true, 'status' => 'processing']);
    }

    private function validateSoal(Request $request): array
    {
        return $request->validate([
            'kategori' => 'required|string|max:50',
            'sub_topik' => 'nullable|string|max:120',
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'teks_soal' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'opsi_e' => 'required|string',
            'jawaban_benar' => 'nullable|in:A,B,C,D,E',
            'pembahasan' => 'nullable|string',
            'bobot_tkp_json' => 'nullable|string',
            'standar_tahun' => 'nullable|in:2027,pra-2027',
        ]);
    }

    private function fillAndSave(Soal $soal, array $validated): void
    {
        $jawaban = $validated['jawaban_benar'] ?? null;
        if (is_string($jawaban)) $jawaban = strtoupper(trim($jawaban));

        $bobotArr = null;
        $rawBobot = trim((string) ($validated['bobot_tkp_json'] ?? ''));
        if ($rawBobot !== '') {
            $decoded = json_decode($rawBobot, true);
            if (!is_array($decoded)) {
                throw ValidationException::withMessages([
                    'bobot_tkp_json' => 'Format Bobot TKP harus JSON valid.',
                ]);
            }
            $bobotArr = $decoded;
        }

        $fill = [
            'kategori' => $validated['kategori'],
            'sub_topik' => $validated['sub_topik'] ?? null,
            'teks_soal' => $validated['teks_soal'],
            'opsi_a' => $validated['opsi_a'],
            'opsi_b' => $validated['opsi_b'],
            'opsi_c' => $validated['opsi_c'],
            'opsi_d' => $validated['opsi_d'],
            'opsi_e' => $validated['opsi_e'],
            'jawaban_benar' => $jawaban,
            'pembahasan' => $validated['pembahasan'] ?? null,
        ];

        if (Schema::hasColumn('soals', 'standar_tahun')) {
            $fill['standar_tahun'] = $validated['standar_tahun'] ?? ($soal->standar_tahun ?: '2027');
        }

        if (Schema::hasColumn('soals', 'exam_mode_id')) {
            $fill['exam_mode_id'] = !empty($validated['exam_mode_id']) ? (int) $validated['exam_mode_id'] : null;
        }
        if (Schema::hasColumn('soals', 'sub_category_id')) {
            $fill['sub_category_id'] = !empty($validated['sub_category_id']) ? (int) $validated['sub_category_id'] : null;
        }
        if (Schema::hasColumn('soals', 'difficulty')) {
            $fill['difficulty'] = $validated['difficulty'] ?? null;
        }
        if (!$soal->exists && Schema::hasColumn('soals', 'created_by')) {
            $fill['created_by'] = auth()->id();
        }

        $soal->fill($fill);

        // TKP can use weights; keep it optional but editable.
        if ($bobotArr !== null) {
            $soal->bobot_tkp = $bobotArr;
        }

        // If non-TKP, ensure bobot doesn't accidentally linger unless user supplies it.
        if ($validated['kategori'] !== 'TKP' && $rawBobot === '') {
            $soal->bobot_tkp = null;
        }

        if (Schema::hasColumn('soals', 'audit_status') && $soal->exists) {
            $soal->audit_status = 'needs_reaudit';
            $soal->audited_at = null;
            $soal->audited_by = null;
            $soal->last_audit_source = 'manual_edit';
        }

        $soal->save();
    }
}

