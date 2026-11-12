<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditExport;
use App\Models\ExamMode;
use App\Models\QuestionAudit;
use App\Models\QuestionUpdateLog;
use App\Models\SoalAudit;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Services\QuestionQualityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\AISoalService;
use App\Support\QuestionStatsSupport;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditSoalController extends Controller
{
    private const DUPLICATE_SIMILARITY_THRESHOLD = 80.0;

    private function exportCacheKey(string $token): string
    {
        return 'audit_export_preview:' . $token;
    }

    private function importCacheKey(string $token): string
    {
        return 'audit_import_preview:' . $token;
    }

    private function normalizeQuestionText(?string $text): string
    {
        $clean = strtolower(strip_tags((string) $text));
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';
        $clean = preg_replace('/[^[:alnum:]\s]/u', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;

        return trim($clean);
    }

    private function deleteNearDuplicateQuestions(Soal $soal, float $threshold = self::DUPLICATE_SIMILARITY_THRESHOLD): array
    {
        $baseText = $this->normalizeQuestionText($soal->teks_soal ?? '');
        if ($baseText === '') {
            return [];
        }

        $query = Soal::query()
            ->select(['id', 'teks_soal'])
            ->where('id', '!=', $soal->id)
            ->where('kategori', $soal->kategori);

        if (Schema::hasColumn('soals', 'exam_mode_id') && !empty($soal->exam_mode_id)) {
            $query->where('exam_mode_id', $soal->exam_mode_id);
        }

        if (Schema::hasColumn('soals', 'sub_category_id') && !empty($soal->sub_category_id)) {
            $query->where('sub_category_id', $soal->sub_category_id);
        }

        $candidates = $query->orderByDesc('id')->limit(150)->get();
        $duplicateIds = [];
        $highestSimilarity = 0.0;

        foreach ($candidates as $candidate) {
            $candidateText = $this->normalizeQuestionText($candidate->teks_soal ?? '');
            if ($candidateText === '') {
                continue;
            }

            similar_text($baseText, $candidateText, $percent);
            if ($percent >= $threshold) {
                $duplicateIds[] = (int) $candidate->id;
                $highestSimilarity = max($highestSimilarity, (float) $percent);
            }
        }

        if (empty($duplicateIds)) {
            return [];
        }

        SoalAudit::query()->whereIn('soal_id', $duplicateIds)->delete();
        QuestionAudit::query()->whereIn('question_id', $duplicateIds)->delete();
        QuestionUpdateLog::query()->whereIn('question_id', $duplicateIds)->delete();
        Soal::query()->whereIn('id', $duplicateIds)->delete();

        return [
            'deleted_ids' => $duplicateIds,
            'deleted_count' => count($duplicateIds),
            'highest_similarity' => round($highestSimilarity, 2),
        ];
    }

    private function applyAuditStatusScope($query, string $scope)
    {
        if (!Schema::hasColumn('soals', 'audit_status')) {
            return $query;
        }

        return match ($scope) {
            'pending_only' => $query->where(function ($q) {
                $q->whereNull('audit_status')
                    ->orWhere('audit_status', '')
                    ->orWhere('audit_status', 'pending');
            }),
            'needs_reaudit' => $query->where('audit_status', 'needs_reaudit'),
            'already_audited' => $query->where('audit_status', 'audited'),
            'revised' => $query->where('audit_status', 'revised'),
            default => $query->where(function ($q) {
                $q->whereNull('audit_status')
                    ->orWhere('audit_status', '')
                    ->orWhereIn('audit_status', ['pending', 'revised', 'needs_reaudit']);
            }),
        };
    }

    private function baseSoalQuery(Request $request)
    {
        $kategori = strtoupper(trim((string) $request->input('kategori', $request->query('kategori', ''))));
        $examModeId = (int) $request->input('exam_mode_id', $request->query('exam_mode_id', 0));
        $subCategoryId = (int) $request->input('sub_category_id', $request->query('sub_category_id', 0));
        $difficulty = trim((string) $request->input('difficulty', $request->query('difficulty', '')));

        $query = Soal::query()->with(['examMode', 'subCategory']);

        if ($kategori !== '') {
            $query->where('kategori', $kategori);
        }

        if ($examModeId > 0 && Schema::hasTable('exam_modes')) {
            $defaultSkdId = ExamMode::defaultSkd()?->id;
            $query->forExamMode($examModeId, $defaultSkdId);
        }

        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $query->forSubCategory($subCategoryId);
        }

        if ($difficulty !== '' && Schema::hasColumn('soals', 'difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        return $query;
    }

    private function exportSoalQuery(Request $request)
    {
        $validated = $request->validate([
            'source_scope' => 'required|string|in:latest_generated,selected,unchecked,flagged,TIU,TWK,TKP,SKB',
            'audit_filter' => 'nullable|string|in:active,pending_only,needs_reaudit,already_audited,all_questions,revised',
            'jumlah' => 'required|integer|min:1|max:100',
            'selected_ids' => 'nullable|string',
            'kategori' => 'nullable|string|max:32',
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'difficulty' => 'nullable|string|max:16',
        ]);

        $jumlah = (int) $validated['jumlah'];
        $sourceScope = (string) $validated['source_scope'];
        $auditFilter = (string) ($validated['audit_filter'] ?? 'active');
        $query = $this->baseSoalQuery($request);

        if ($auditFilter !== 'all_questions') {
            $this->applyAuditStatusScope($query, $auditFilter);
        }

        if (Schema::hasColumn('soals', 'audit_status')) {
            $query->orderByRaw("
                CASE
                    WHEN audit_status IS NULL OR audit_status = '' OR audit_status = 'pending' THEN 0
                    WHEN audit_status = 'needs_reaudit' THEN 1
                    WHEN audit_status = 'revised' THEN 2
                    WHEN audit_status = 'audited' THEN 3
                    WHEN audit_status = 'rejected' THEN 4
                    ELSE 5
                END
            ");
        }

        if ($sourceScope === 'selected') {
            $selectedIds = collect(preg_split('/[\s,]+/', (string) ($validated['selected_ids'] ?? '')))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            if ($selectedIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'selected_ids' => 'Isi ID soal yang ingin diexport dulu.',
                ]);
            }

            $query->whereIn('id', $selectedIds->all())->orderBy('id');
        } elseif ($sourceScope === 'latest_generated') {
            $query->whereNotNull('created_by')->orderByDesc('created_at');
        } elseif ($sourceScope === 'unchecked') {
            $this->applyAuditStatusScope($query, 'pending_only');
            $query->orderBy('id');
        } elseif ($sourceScope === 'flagged') {
            $flaggedIds = QuestionAudit::query()
                ->whereIn('status', ['flagged', 'rejected', 'wrong', 'suspicious', 'revised'])
                ->whereNotNull('question_id')
                ->latest()
                ->limit($jumlah * 3)
                ->pluck('question_id')
                ->unique()
                ->values();

            $query->whereIn('id', $flaggedIds->all())->orderBy('id');
        } elseif (in_array($sourceScope, ['TIU', 'TWK', 'TKP'], true)) {
            $query->where('kategori', $sourceScope)->orderBy('id');
        } elseif ($sourceScope === 'SKB') {
            $query->whereNotIn('kategori', ['TIU', 'TWK', 'TKP'])->orderBy('id');
        }

        return [$query, $validated];
    }

    private function buildQuestionPayload(Soal $soal): array
    {
        return [
            'id' => $soal->id,
            'kategori' => (string) $soal->kategori,
            'sub_topik' => (string) ($soal->sub_topik ?? ''),
            'mode_ujian' => (string) ($soal->examMode?->name ?? ''),
            'sub_kategori' => (string) ($soal->subCategory?->name ?? ''),
            'soal' => (string) $soal->teks_soal,
            'A' => (string) $soal->opsi_a,
            'B' => (string) $soal->opsi_b,
            'C' => (string) $soal->opsi_c,
            'D' => (string) $soal->opsi_d,
            'E' => (string) $soal->opsi_e,
            'jawaban' => (string) ($soal->jawaban_benar ?? ''),
            'pembahasan' => (string) ($soal->pembahasan ?? ''),
        ];
    }

    private function buildAuditPrompt(array $questionPayload): string
    {
        $json = json_encode($questionPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return "Tolong audit soal berikut.\n\n"
            . "Periksa:\n"
            . "1. Kebenaran soal\n"
            . "2. Kunci jawaban\n"
            . "3. Pembahasan\n"
            . "4. Typo / ambigu\n"
            . "5. Berikan revisi final\n\n"
            . "Balas dalam JSON dengan format:\n"
            . "[\n"
            . "  {\n"
            . "    \"id\": 101,\n"
            . "    \"status\": \"valid atau revisi\",\n"
            . "    \"jawaban_baru\": \"A/B/C/D/E atau kosong jika tetap\",\n"
            . "    \"pembahasan_baru\": \"...\",\n"
            . "    \"soal_baru\": \"...\",\n"
            . "    \"catatan\": \"alasan audit\"\n"
            . "  }\n"
            . "]\n\n"
            . $json;
    }

    private function parseImportRows(string $raw): array
    {
        $clean = trim(preg_replace('/```(?:json)?|```/i', '', $raw) ?? '');
        $decoded = json_decode($clean, true);

        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'import_payload' => 'JSON Gemini tidak valid. Pastikan format array JSON benar.',
            ]);
        }

        $rows = array_is_list($decoded)
            ? $decoded
            : ($decoded['results'] ?? $decoded['data'] ?? $decoded['questions'] ?? $decoded['audits'] ?? null);
        if (!is_array($rows)) {
            throw ValidationException::withMessages([
                'import_payload' => 'Format JSON harus berupa array hasil audit.',
            ]);
        }

        $firstRow = isset($rows[0]) && is_array($rows[0]) ? $rows[0] : null;
        if ($firstRow && !array_key_exists('id', $firstRow)) {
            $looksLikeGeneratedQuestions =
                array_key_exists('soal', $firstRow)
                && array_key_exists('A', $firstRow)
                && array_key_exists('B', $firstRow)
                && array_key_exists('C', $firstRow)
                && array_key_exists('D', $firstRow)
                && array_key_exists('E', $firstRow)
                && array_key_exists('jawaban', $firstRow)
                && array_key_exists('pembahasan', $firstRow);

            if ($looksLikeGeneratedQuestions) {
                throw ValidationException::withMessages([
                    'import_payload' => 'JSON ini adalah format generate soal baru, bukan hasil audit. Import payload seperti ini lewat halaman Generate Soal Massal (Gemini), bukan Audit Soal AI.',
                ]);
            }
        }

        $seen = [];
        $normalized = [];
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                $normalized[] = ['row' => $index + 1, 'valid' => false, 'errors' => ['Baris bukan object JSON yang valid.']];
                continue;
            }

            $id = (int) ($row['id'] ?? 0);
            $rawStatus = strtolower(trim((string) ($row['status'] ?? $row['qc_status'] ?? 'valid')));
            $status = match ($rawStatus) {
                'valid', 'approved', 'approve', 'ok', 'audited', 'reviewed' => 'valid',
                'revisi', 'revised', 'revision', 'fixed', 'corrected', 'update' => 'revisi',
                'flagged', 'wrong', 'suspicious', 'needs_reaudit', 'need_reaudit', 'reject', 'rejected' => 'revisi',
                default => $rawStatus,
            };
            $answer = strtoupper(trim((string) ($row['jawaban_baru'] ?? $row['answer_new'] ?? $row['suggested_answer'] ?? '')));
            $explanation = trim((string) ($row['pembahasan_baru'] ?? $row['explanation_new'] ?? $row['suggested_explanation'] ?? ''));
            $questionText = trim((string) ($row['soal_baru'] ?? $row['question_new'] ?? $row['suggested_question'] ?? ''));
            $notes = trim((string) ($row['catatan'] ?? $row['notes'] ?? $row['reason'] ?? ''));

            $errors = [];
            if ($id <= 0) {
                $errors[] = 'ID soal wajib ada dan valid.';
            } elseif (in_array($id, $seen, true)) {
                $errors[] = 'ID duplikat dalam satu import.';
            }
            $seen[] = $id;

            if (!in_array($status, ['valid', 'revisi'], true)) {
                $errors[] = 'Status harus valid atau revisi.';
            }

            if ($answer !== '' && !preg_match('/^[A-E]$/', $answer)) {
                $errors[] = 'Jawaban baru harus A/B/C/D/E.';
            }

            $soal = $id > 0 ? Soal::find($id) : null;
            if (!$soal) {
                $errors[] = 'Soal dengan ID ini tidak ditemukan.';
            }

            if ($status === 'revisi' && $answer === '' && $explanation === '' && $questionText === '') {
                $errors[] = 'Status revisi harus membawa minimal satu perubahan.';
            }

            $normalized[] = [
                'row' => $index + 1,
                'id' => $id,
                'status' => $status,
                'answer_new' => $answer,
                'explanation_new' => $explanation,
                'question_new' => $questionText,
                'notes' => strip_tags($notes),
                'soal' => $soal,
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        }

        return $normalized;
    }

    public function index(Request $request)
    {
        $kategori = strtoupper(trim((string) $request->query('kategori', '')));
        $examModeId = (int) $request->query('exam_mode_id', 0);
        $subCategoryId = (int) $request->query('sub_category_id', 0);
        $difficulty = trim((string) $request->query('difficulty', ''));
        $exportToken = trim((string) $request->query('export_token', ''));
        $importToken = trim((string) $request->query('import_token', ''));
        $auditFilter = trim((string) $request->query('audit_filter', 'active'));

        $query = SoalAudit::with('soal')
            ->where(function ($q) {
                $q->where('status', 'mismatch')
                    ->orWhere('pembahasan_ok', false);
            });

        if ($kategori !== '') {
            $query->whereHas('soal', fn ($q) => $q->where('kategori', $kategori));
        }

        if ($examModeId > 0 && Schema::hasTable('exam_modes')) {
            $defaultSkdId = ExamMode::defaultSkd()?->id;
            $query->whereHas('soal', fn ($q) => $q->forExamMode($examModeId, $defaultSkdId));
        }

        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $query->whereHas('soal', fn ($q) => $q->forSubCategory($subCategoryId));
        }

        if ($difficulty !== '' && Schema::hasColumn('soals', 'difficulty')) {
            $query->whereHas('soal', fn ($q) => $q->where('difficulty', $difficulty));
        }

        $audits = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        $examModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();
        $subCategories = ($examModeId > 0 && Schema::hasTable('sub_categories'))
            ? SubCategory::query()->where('exam_mode_id', $examModeId)->byKind('formasi')->orderBy('name')->get(['id', 'name', 'exam_mode_id', 'kind'])
            : collect();

        $auditStatusStats = QuestionStatsSupport::auditStatusCounts();
        $qcPendingCount = (int) ($auditStatusStats['pending'] ?? 0);
        $auditedCount = (int) ($auditStatusStats['audited'] ?? 0);
        $revisedCount = (int) ($auditStatusStats['revised'] ?? 0);
        $needsReauditCount = (int) ($auditStatusStats['needs_reaudit'] ?? 0);
        $flaggedCount = (int) ($auditStatusStats['flagged'] ?? 0);
        $recentExports = class_exists(AuditExport::class)
            ? AuditExport::query()
                ->with('user')
                ->latest()
                ->paginate(6, ['*'], 'exports_page')
                ->appends($request->query())
            : collect();

        $exportPreview = $exportToken !== '' ? Cache::get($this->exportCacheKey($exportToken)) : null;
        $importPreview = $importToken !== '' ? Cache::get($this->importCacheKey($importToken)) : null;

        return view('admin.audit_soal.index', compact(
            'audits',
            'kategori',
            'examModeId',
            'subCategoryId',
            'difficulty',
            'auditFilter',
            'examModes',
            'subCategories',
            'qcPendingCount',
            'auditedCount',
            'revisedCount',
            'needsReauditCount',
            'flaggedCount',
            'recentExports',
            'exportToken',
            'exportPreview',
            'importToken',
            'importPreview'
        ));
    }

    public function generateExport(Request $request)
    {
        [$query, $validated] = $this->exportSoalQuery($request);

        $rows = $query->limit((int) $validated['jumlah'])->get();
        if ($rows->isEmpty()) {
            return back()->with('error', 'Tidak ada soal yang cocok untuk diexport. Ubah sumber atau filter dulu.');
        }

        $payload = $rows->map(fn (Soal $soal) => $this->buildQuestionPayload($soal))->values()->all();
        $prompt = $this->buildAuditPrompt($payload);
        $token = (string) Str::uuid();
        $format = (string) $request->input('export_format', 'json');

        $preview = [
            'token' => $token,
            'source_scope' => (string) $validated['source_scope'],
            'format' => $format,
            'total_questions' => count($payload),
            'question_ids' => array_map(fn ($row) => (int) $row['id'], $payload),
            'prompt_text' => $prompt,
            'json_payload' => $payload,
            'filters' => [
                'kategori' => strtoupper(trim((string) $request->input('kategori', ''))),
                'exam_mode_id' => (int) $request->input('exam_mode_id', 0),
                'sub_category_id' => (int) $request->input('sub_category_id', 0),
                'difficulty' => trim((string) $request->input('difficulty', '')),
                'audit_filter' => trim((string) $request->input('audit_filter', 'active')),
                'selected_ids' => trim((string) $request->input('selected_ids', '')),
            ],
        ];

        Cache::put($this->exportCacheKey($token), $preview, now()->addMinutes(45));

        AuditExport::create([
            'user_id' => auth()->id(),
            'total_questions' => count($payload),
            'source_scope' => (string) $validated['source_scope'],
            'format' => $format,
            'filter_used' => $preview['filters'],
            'question_ids' => $preview['question_ids'],
        ]);

        return redirect()->route('admin.audit-soal.index', array_filter([
            'kategori' => $request->input('kategori'),
            'exam_mode_id' => $request->input('exam_mode_id'),
            'sub_category_id' => $request->input('sub_category_id'),
            'difficulty' => $request->input('difficulty'),
            'audit_filter' => $request->input('audit_filter'),
            'export_token' => $token,
        ]))->with('success', 'Preview export audit siap. Silakan copy atau download.');
    }

    public function downloadExport(Request $request, string $token, string $format): StreamedResponse
    {
        $preview = Cache::get($this->exportCacheKey($token));
        abort_unless(is_array($preview), 404, 'Preview export tidak ditemukan atau sudah kedaluwarsa.');

        $format = strtolower($format);
        abort_unless(in_array($format, ['txt', 'json'], true), 404);

        $date = now()->format('Y-m-d');
        $filename = "audit-soal-{$date}.{$format}";

        return response()->streamDownload(function () use ($preview, $format) {
            if ($format === 'txt') {
                echo (string) ($preview['prompt_text'] ?? '');
                return;
            }

            echo json_encode($preview['json_payload'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => $format === 'txt' ? 'text/plain; charset=UTF-8' : 'application/json; charset=UTF-8',
        ]);
    }

    public function previewImport(Request $request)
    {
        $validated = $request->validate([
            'import_payload' => 'required|string',
        ]);

        $rows = $this->parseImportRows((string) $validated['import_payload']);
        $token = (string) Str::uuid();

        $previewRows = collect($rows)->map(function (array $row) {
            $soal = $row['soal'];

            return [
                'row' => $row['row'],
                'id' => $row['id'],
                'status' => $row['status'],
                'valid' => $row['valid'],
                'errors' => $row['errors'],
                'notes' => $row['notes'],
                'answer_old' => $soal?->jawaban_benar,
                'answer_new' => $row['answer_new'],
                'explanation_old' => $soal?->pembahasan,
                'explanation_new' => $row['explanation_new'],
                'question_old' => $soal?->teks_soal,
                'question_new' => $row['question_new'],
            ];
        })->values()->all();

        Cache::put($this->importCacheKey($token), [
            'rows' => $previewRows,
            'raw' => (string) $validated['import_payload'],
        ], now()->addMinutes(45));

        return redirect()->route('admin.audit-soal.index', ['import_token' => $token])
            ->with('success', 'Preview import Gemini siap. Pilih baris yang mau diterapkan.');
    }

    public function applyImport(Request $request)
    {
        $validated = $request->validate([
            'import_token' => 'required|string',
            'apply_rows' => 'nullable|array',
        ]);

        $preview = Cache::get($this->importCacheKey((string) $validated['import_token']));
        if (!is_array($preview) || !is_array($preview['rows'] ?? null)) {
            return back()->with('error', 'Preview import sudah kedaluwarsa. Silakan paste ulang JSON Gemini.');
        }

        $selectedIds = collect(array_keys($validated['apply_rows'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($selectedIds->isEmpty()) {
            return back()->with('error', 'Pilih minimal satu baris valid untuk diterapkan.');
        }

        $applied = 0;
        $skipped = 0;
        $deletedDuplicates = 0;

        foreach ($preview['rows'] as $row) {
            $id = (int) ($row['id'] ?? 0);
            if (!$id || !$selectedIds->contains($id)) {
                continue;
            }
            if (empty($row['valid'])) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($row, &$applied, &$deletedDuplicates) {
                    $soal = Soal::find($row['id']);
                    if (!$soal) {
                        throw new \RuntimeException('Soal tidak ditemukan.');
                    }

                    $oldAnswer = (string) ($soal->jawaban_benar ?? '');
                    $oldExplanation = (string) ($soal->pembahasan ?? '');
                    $oldQuestion = (string) ($soal->teks_soal ?? '');

                    $answerNew = trim((string) ($row['answer_new'] ?? ''));
                    $explanationNew = trim((string) ($row['explanation_new'] ?? ''));
                    $questionNew = trim((string) ($row['question_new'] ?? ''));

                    if ($answerNew !== '') {
                        $soal->jawaban_benar = $answerNew;
                    }
                    if ($explanationNew !== '') {
                        $soal->pembahasan = strip_tags($explanationNew);
                    }
                    if ($questionNew !== '') {
                        $soal->teks_soal = strip_tags($questionNew);
                    }
                    if (Schema::hasColumn('soals', 'qc_status')) {
                        $soal->qc_status = 'reviewed';
                        $soal->qc_checked_at = now();
                    }
                    if (Schema::hasColumn('soals', 'audit_status')) {
                        $soal->audit_status = 'audited';
                        $soal->audited_at = now();
                        $soal->audited_by = auth()->id();
                        $soal->last_audit_source = 'gemini_import';
                        $soal->last_audit_score = 100;
                    }
                    $soal->save();

                    QuestionUpdateLog::create([
                        'question_id' => $soal->id,
                        'source' => 'gemini_import',
                        'old_answer' => $oldAnswer !== '' ? $oldAnswer : null,
                        'new_answer' => $answerNew !== '' ? $answerNew : null,
                        'old_explanation' => $oldExplanation !== '' ? $oldExplanation : null,
                        'new_explanation' => $explanationNew !== '' ? strip_tags($explanationNew) : null,
                        'old_question' => $oldQuestion !== '' ? $oldQuestion : null,
                        'new_question' => $questionNew !== '' ? strip_tags($questionNew) : null,
                        'imported_by' => auth()->id(),
                    ]);

                    $duplicateCleanup = $this->deleteNearDuplicateQuestions($soal);
                    $deletedDuplicates += (int) ($duplicateCleanup['deleted_count'] ?? 0);

                    $applied++;
                });
            } catch (\Throwable $e) {
                report($e);
                $skipped++;
            }
        }

        return redirect()->route('admin.audit-soal.index')->with(
            $applied > 0 ? 'success' : 'error',
            $applied > 0
                ? "Import Gemini selesai. {$applied} soal diperbarui"
                    . ($deletedDuplicates > 0 ? ", {$deletedDuplicates} soal mirip >= 80% dihapus." : '')
                    . ($skipped > 0 ? ", {$skipped} baris dilewati." : '.')
                : 'Tidak ada perubahan yang berhasil diterapkan.'
        );
    }

    public function apply($id)
    {
        $audit = SoalAudit::findOrFail($id);
        $soal = $audit->soal;

        if (!$soal) {
            return back()->with('error', 'Soal tidak ditemukan.');
        }

        // Terapkan saran AI ke soal
        $soal->jawaban_benar = $audit->ai_key ?? $soal->jawaban_benar;
        
        if ($audit->ai_pembahasan) {
            $soal->pembahasan = $audit->ai_pembahasan;
        }

        if (Schema::hasColumn('soals', 'audit_status')) {
            $soal->audit_status = 'audited';
            $soal->audited_at = now();
            $soal->audited_by = auth()->id();
            $soal->last_audit_source = 'manual_apply';
            $soal->last_audit_score = 95;
        }

        $soal->save();
        $duplicateCleanup = $this->deleteNearDuplicateQuestions($soal);

        // Hapus log audit karena sudah ditangani
        $audit->delete();

        return back()->with('success', 'Saran AI berhasil diterapkan pada soal.'
            . (!empty($duplicateCleanup['deleted_count']) ? ' ' . $duplicateCleanup['deleted_count'] . ' soal mirip >= 80% ikut dihapus.' : ''));
    }

    public function applyRevision($id)
    {
        $audit = SoalAudit::findOrFail($id);
        $soal = $audit->soal;

        if (!$soal) {
            return back()->with('error', 'Soal tidak ditemukan.');
        }
        if (!$audit->needs_revision) {
            return back()->with('error', 'Audit ini tidak memiliki usulan revisi penuh.');
        }

        $kunci = strtoupper((string) ($audit->proposed_jawaban_benar ?? ''));
        if (!preg_match('/^[A-E]$/', $kunci)) {
            return back()->with('error', 'Kunci usulan revisi tidak valid.');
        }

        // Apply proposed full revision.
        $soal->teks_soal = $audit->proposed_teks_soal ?? $soal->teks_soal;
        $soal->opsi_a = $audit->proposed_opsi_a ?? $soal->opsi_a;
        $soal->opsi_b = $audit->proposed_opsi_b ?? $soal->opsi_b;
        $soal->opsi_c = $audit->proposed_opsi_c ?? $soal->opsi_c;
        $soal->opsi_d = $audit->proposed_opsi_d ?? $soal->opsi_d;
        $soal->opsi_e = $audit->proposed_opsi_e ?? $soal->opsi_e;
        $soal->jawaban_benar = $kunci;
        if (!empty($audit->proposed_pembahasan)) {
            $soal->pembahasan = $audit->proposed_pembahasan;
        }
        if (Schema::hasColumn('soals', 'audit_status')) {
            $soal->audit_status = 'revised';
            $soal->audited_at = now();
            $soal->audited_by = auth()->id();
            $soal->last_audit_source = 'full_revision';
            $soal->last_audit_score = 96;
        }
        $soal->save();
        $duplicateCleanup = $this->deleteNearDuplicateQuestions($soal);

        $audit->delete();

        return back()->with('success', 'Revisi penuh (soal+opsi+kunci+pembahasan) berhasil diterapkan.'
            . (!empty($duplicateCleanup['deleted_count']) ? ' ' . $duplicateCleanup['deleted_count'] . ' soal mirip >= 80% ikut dihapus.' : ''));
    }

    public function markValid($id)
    {
        $audit = SoalAudit::findOrFail($id);
        $soal = $audit->soal;
        $duplicateCleanup = [];
        
        // Kunci DB dianggap benar, abaikan saran AI
        if ($soal && Schema::hasColumn('soals', 'audit_status')) {
            $soal->audit_status = 'audited';
            $soal->audited_at = now();
            $soal->audited_by = auth()->id();
            $soal->last_audit_source = 'manual_valid';
            $soal->last_audit_score = 100;
            $soal->save();
            $duplicateCleanup = $this->deleteNearDuplicateQuestions($soal);
        }

        // Hapus log audit
        $audit->delete();

        return back()->with('success', 'Soal ditandai sebagai benar sesuai kunci DB.'
            . (!empty($duplicateCleanup['deleted_count']) ? ' ' . $duplicateCleanup['deleted_count'] . ' soal mirip >= 80% ikut dihapus.' : ''));
    }

    public function triggerAudit(Request $request)
    {
        $kategori = strtoupper((string) $request->input('kategori', 'ALL'));
        $examModeId = (int) $request->input('exam_mode_id', 0);
        $subCategoryId = (int) $request->input('sub_category_id', 0);
        if (!in_array($kategori, ['TWK', 'TIU', 'TKP', 'ALL'], true)) {
            return back()->with('error', 'Kategori tidak valid.');
        }

        $provider = 'groq';
        $model = null;
        $onlyMissing = true;

        $q = Soal::query()->whereIn('kategori', ['TWK', 'TIU', 'TKP']);
        if ($kategori !== 'ALL') $q->where('kategori', $kategori);
        if ($examModeId > 0 && Schema::hasTable('exam_modes')) {
            $defaultSkdId = ExamMode::defaultSkd()?->id;
            $q->forExamMode($examModeId, $defaultSkdId);
        }
        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $q->forSubCategory($subCategoryId);
        }
        if ($onlyMissing) {
            $q->whereDoesntHave('audits', function ($sub) use ($provider, $model) {
                $sub->where('provider', $provider);
                $sub->whereNull('model');
            });
        }

        $total = (int) $q->count();
        if ($total === 0) {
            return back()->with('success', 'Tidak ada soal yang perlu diaudit untuk filter ini.');
        }

        $jobId = (string) Str::uuid();
        $base = 'ai_audit:' . $jobId;

        Cache::put($base . ':status', 'processing', now()->addHour());
        Cache::put($base . ':message', 'Audit sedang diproses...', now()->addHour());
        Cache::put($base . ':kategori', $kategori, now()->addHour());
        Cache::put($base . ':provider', $provider, now()->addHour());
        Cache::put($base . ':model', $model, now()->addHour());
        Cache::put($base . ':exam_mode_id', $examModeId, now()->addHour());
        Cache::put($base . ':sub_category_id', $subCategoryId, now()->addHour());
        Cache::put($base . ':runner', 'step', now()->addHour());
        Cache::put($base . ':cursor_last_id', 0, now()->addHour());
        Cache::put($base . ':batch', 2, now()->addHour());
        Cache::put($base . ':queued_at', now()->toISOString(), now()->addHour());
        Cache::put($base . ':updated_at', now()->toISOString(), now()->addHour());
        Cache::put($base . ':total', $total, now()->addHour());
        Cache::put($base . ':done', 0, now()->addHour());
        Cache::put($base . ':errors', 0, now()->addHour());

        $jobs = (array) session('ai_audit_jobs', []);
        $jobs[] = $jobId;
        session(['ai_audit_jobs' => array_values(array_unique($jobs))]);

        return back()
            ->with('audit_processing_job_id', $jobId)
            ->with('success', "Audit massal {$kategori} dimulai (mode Groq tanpa worker). Progress akan tampil di panel status.");
    }

    public function status(Request $request)
    {
        $jobId = (string) $request->query('id', '');
        if ($jobId === '') return response()->json(['status' => 'invalid'], 400);
        if (!in_array($jobId, (array) session('ai_audit_jobs', []), true)) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $base = 'ai_audit:' . $jobId;
        $total = (int) Cache::get($base . ':total', 0);
        $done = (int) Cache::get($base . ':done', 0);
        $errors = (int) Cache::get($base . ':errors', 0);
        $queuedAt = Cache::get($base . ':queued_at');
        $updatedAt = Cache::get($base . ':updated_at');
        $lastError = Cache::get($base . ':last_error');
        $lastSoalId = Cache::get($base . ':last_soal_id');
        $kategori = Cache::get($base . ':kategori');
        $provider = Cache::get($base . ':provider');
        $cachedStatus = (string) Cache::get($base . ':status', '');
        $cachedMsg = (string) Cache::get($base . ':message', '');
        $runner = (string) Cache::get($base . ':runner', 'step');
        $examModeId = (int) Cache::get($base . ':exam_mode_id', 0);
        $subCategoryId = (int) Cache::get($base . ':sub_category_id', 0);

        $status = 'processing';
        if ($cachedStatus === 'canceled') {
            $status = 'canceled';
        }
        if ($total > 0 && $done >= $total) {
            $status = $errors > 0 ? 'success_with_errors' : 'success';
            Cache::put($base . ':status', $status, now()->addHour());
            Cache::put($base . ':message', $errors > 0 ? 'Selesai dengan beberapa error.' : 'Selesai.', now()->addHour());
        }

        // Queue state heuristic (optional)
        $needle = '%' . $jobId . '%';
        $jobRow = DB::table('jobs')
            ->select(['id', 'attempts', 'reserved_at', 'available_at', 'created_at'])
            ->where('payload', 'like', $needle)
            ->orderBy('id')
            ->first();
        $queueState = $runner === 'step' ? 'step' : 'missing';
        if ($jobRow) {
            if (!empty($jobRow->reserved_at)) $queueState = 'running';
            elseif ((int) $jobRow->available_at > time()) $queueState = 'scheduled';
            else $queueState = 'queued';
        }

        return response()->json([
            'status' => $status,
            'message' => $cachedMsg !== '' ? $cachedMsg : null,
            'runner' => $runner,
            'total' => $total,
            'done' => $done,
            'errors' => $errors,
            'queued_at' => $queuedAt,
            'updated_at' => $updatedAt,
            'kategori' => $kategori,
            'provider' => $provider,
            'exam_mode_id' => $examModeId ?: null,
            'sub_category_id' => $subCategoryId ?: null,
            'last_soal_id' => $lastSoalId,
            'last_error' => $lastError,
            'queue' => [
                'state' => $queueState,
                'attempts' => $jobRow->attempts ?? null,
                'reserved_at' => $jobRow->reserved_at ?? null,
                'created_at' => $jobRow->created_at ?? null,
                'job_id' => $jobRow->id ?? null,
            ],
        ]);
    }

    public function step(Request $request, AISoalService $ai)
    {
        $validated = $request->validate([
            'job_id' => 'required|string|min:8|max:80',
        ]);
        $jobId = (string) $validated['job_id'];
        if (!in_array($jobId, (array) session('ai_audit_jobs', []), true)) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

        $base = 'ai_audit:' . $jobId;
        $status = (string) Cache::get($base . ':status', 'processing');
        if (in_array($status, ['success', 'success_with_errors', 'error', 'canceled'], true)) {
            return response()->json(['ok' => true, 'status' => $status]);
        }

        $kategori = (string) Cache::get($base . ':kategori', 'ALL');
        $provider = (string) Cache::get($base . ':provider', 'groq');
        $model = Cache::get($base . ':model');
        $runner = (string) Cache::get($base . ':runner', 'step');
        $examModeId = (int) Cache::get($base . ':exam_mode_id', 0);
        $subCategoryId = (int) Cache::get($base . ':sub_category_id', 0);
        if ($runner !== 'step' || $provider !== 'groq') {
            return response()->json(['ok' => false, 'error' => 'Step hanya untuk Groq'], 400);
        }

        $lastId = (int) Cache::get($base . ':cursor_last_id', 0);
        $batch = (int) Cache::get($base . ':batch', 3);
        if ($batch < 1) $batch = 1;
        if ($batch > 5) $batch = 5;

        $onlyMissing = true;
        $q = Soal::query()->whereIn('kategori', ['TWK', 'TIU', 'TKP']);
        if ($kategori !== 'ALL') $q->where('kategori', $kategori);
        if ($examModeId > 0 && Schema::hasTable('exam_modes')) {
            $defaultSkdId = ExamMode::defaultSkd()?->id;
            $q->forExamMode($examModeId, $defaultSkdId);
        }
        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $q->forSubCategory($subCategoryId);
        }
        if ($onlyMissing) {
            $q->whereDoesntHave('audits', function ($sub) use ($provider, $model) {
                $sub->where('provider', $provider);
                $sub->whereNull('model');
            });
        }
        $items = $q->where('id', '>', $lastId)->orderBy('id')->take($batch)->get();

        if ($items->isEmpty()) {
            // finish
            $errors = (int) Cache::get($base . ':errors', 0);
            $final = $errors > 0 ? 'success_with_errors' : 'success';
            Cache::put($base . ':status', $final, now()->addHour());
            Cache::put($base . ':message', $errors > 0 ? 'Selesai dengan beberapa error.' : 'Selesai.', now()->addHour());
            Cache::put($base . ':updated_at', now()->toISOString(), now()->addHour());
            return response()->json(['ok' => true, 'status' => $final]);
        }

        foreach ($items as $soal) {
            try {
                $result = $ai->auditExistingSoal($soal, $provider, is_string($model) && $model !== '' ? $model : null);

                $audit = SoalAudit::firstOrNew([
                    'soal_id' => $soal->id,
                    'provider' => $provider,
                    'model' => $model,
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
            } catch (\Throwable $e) {
                $audit = SoalAudit::firstOrNew([
                    'soal_id' => $soal->id,
                    'provider' => $provider,
                    'model' => $model,
                ]);
                $audit->status = 'error';
                $audit->notes = 'Exception: ' . $e->getMessage();
                $audit->audited_at = now();
                $audit->save();

                Cache::increment($base . ':errors', 1);
                Cache::put($base . ':last_error', substr((string) $e->getMessage(), 0, 220), now()->addHour());
            } finally {
                Cache::increment($base . ':done', 1);
                Cache::put($base . ':updated_at', now()->toISOString(), now()->addHour());
                Cache::put($base . ':last_soal_id', (int) $soal->id, now()->addHour());
                $lastId = max($lastId, (int) $soal->id);
            }
        }

        Cache::put($base . ':cursor_last_id', $lastId, now()->addHour());
        Cache::put($base . ':message', 'Audit sedang diproses...', now()->addHour());

        return response()->json(['ok' => true]);
    }
}

