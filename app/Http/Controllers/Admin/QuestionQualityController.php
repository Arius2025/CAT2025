<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AuditTiuQuestionsJob;
use App\Models\ExamMode;
use App\Models\QuestionAudit;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Services\QuestionQualityService;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class QuestionQualityController extends Controller
{
    protected function buildPendingQuestionsQuery(Request $request)
    {
        $kategori = strtoupper(trim((string) $request->query('kategori', '')));
        $examModeId = (int) $request->query('exam_mode_id', 0);
        $subCategoryId = (int) $request->query('sub_category_id', 0);
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;

        return Soal::query()
            ->with(['examMode', 'subCategory'])
            ->when($kategori !== '', fn ($q) => $q->where('kategori', $kategori))
            ->when($examModeId > 0, fn ($q) => $q->forExamMode($examModeId, $defaultSkdId))
            ->when($subCategoryId > 0, fn ($q) => $q->forSubCategory($subCategoryId))
            ->when(
                Schema::hasColumn('soals', 'qc_status'),
                fn ($q) => $q->where(function ($sub) {
                    $sub->whereNull('qc_status')
                        ->orWhere('qc_status', '');
                }),
                fn ($q) => $q
            );
    }

    public function index(Request $request)
    {
        if (!$request->boolean('legacy')) {
            return redirect()->route('admin.audit-soal.index', array_filter([
                'kategori' => $request->query('kategori'),
                'exam_mode_id' => $request->query('exam_mode_id'),
                'sub_category_id' => $request->query('sub_category_id'),
            ]));
        }

        $source = trim((string) $request->query('source', ''));
        $status = trim((string) $request->query('status', ''));
        $kategori = strtoupper(trim((string) $request->query('kategori', '')));
        $examModeId = (int) $request->query('exam_mode_id', 0);
        $subCategoryId = (int) $request->query('sub_category_id', 0);
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;

        $pendingQuestions = $this->buildPendingQuestionsQuery($request)
            ->orderBy('id')
            ->paginate(20, ['*'], 'pending_page')
            ->appends($request->query());

        $query = QuestionAudit::query()->with('question');

        if ($source !== '') {
            $query->where('source', $source);
        }
        if ($status !== '') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['flagged', 'rejected', 'wrong', 'suspicious', 'revised']);
        }
        if ($kategori !== '') {
            $query->whereHas('question', fn ($q) => $q->where('kategori', $kategori));
        }
        if ($examModeId > 0 && Schema::hasTable('exam_modes')) {
            $query->whereHas('question', fn ($q) => $q->forExamMode($examModeId, $defaultSkdId));
        }
        if ($subCategoryId > 0 && Schema::hasTable('sub_categories')) {
            $query->whereHas('question', fn ($q) => $q->forSubCategory($subCategoryId));
        }

        $audits = $query->orderByDesc('created_at')->paginate(20, ['*'], 'audit_page')->appends($request->query());

        $stats = [
            'total_questions' => Soal::query()->count(),
            'unchecked' => Schema::hasColumn('soals', 'qc_status') ? Soal::query()->where(function ($q) {
                $q->whereNull('qc_status')->orWhere('qc_status', '');
            })->count() : Soal::query()->count(),
            'approved' => Schema::hasColumn('soals', 'qc_status') ? Soal::query()->where('qc_status', 'approved')->count() : 0,
            'revised' => Schema::hasColumn('soals', 'qc_status') ? Soal::query()->where('qc_status', 'revised')->count() : 0,
            'flagged' => Schema::hasColumn('soals', 'qc_status') ? Soal::query()->where('qc_status', 'flagged')->count() : 0,
            'rejected' => Schema::hasColumn('soals', 'qc_status') ? Soal::query()->where('qc_status', 'rejected')->count() : 0,
            'reviewed' => Schema::hasColumn('soals', 'qc_status') ? Soal::query()->where('qc_status', 'reviewed')->count() : 0,
        ];

        $generatedAudits = QuestionAudit::query()->with('question')->where('source', 'generated')->latest()->take(8)->get();
        $tiuAudits = QuestionAudit::query()->with('question')->where('source', 'existing_tiu')->latest()->take(8)->get();
        $flaggedAudits = QuestionAudit::query()->with('question')->whereIn('status', ['flagged', 'rejected', 'wrong', 'suspicious'])->latest()->take(8)->get();
        $autoQueue = QuestionAudit::query()
            ->with('question')
            ->whereNull('applied_at')
            ->where(function ($q) {
                $q->whereNotNull('suggested_answer')
                    ->orWhereNotNull('suggested_explanation');
            })
            ->latest()
            ->take(8)
            ->get();
        $autoDeletedAudits = QuestionAudit::query()
            ->where('status', 'rejected')
            ->where('issue_type', 'nearest_option_invalid')
            ->latest()
            ->take(8)
            ->get();

        $examModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->orderBy('name')->get(['id', 'name'])
            : collect();
        $subCategories = ($examModeId > 0 && Schema::hasTable('sub_categories'))
            ? SubCategory::query()->where('exam_mode_id', $examModeId)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('admin.question_quality.index', compact(
            'pendingQuestions',
            'audits',
            'stats',
            'generatedAudits',
            'tiuAudits',
            'flaggedAudits',
            'autoQueue',
            'autoDeletedAudits',
            'source',
            'status',
            'kategori',
            'examModeId',
            'subCategoryId',
            'examModes',
            'subCategories'
        ));
    }

    public function exportJson(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        $limit = (int) ($validated['limit'] ?? 20);
        $rows = $this->buildPendingQuestionsQuery($request)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $payload = $rows->map(function (Soal $soal) {
            return [
                'id' => $soal->id,
                'kategori' => (string) $soal->kategori,
                'sub_topik' => (string) ($soal->sub_topik ?? ''),
                'exam_mode' => (string) ($soal->examMode?->name ?? ''),
                'sub_category' => (string) ($soal->subCategory?->name ?? ''),
                'question_text' => (string) $soal->teks_soal,
                'options' => [
                    'A' => (string) $soal->opsi_a,
                    'B' => (string) $soal->opsi_b,
                    'C' => (string) $soal->opsi_c,
                    'D' => (string) $soal->opsi_d,
                    'E' => (string) $soal->opsi_e,
                ],
                'current_answer' => (string) ($soal->jawaban_benar ?? ''),
                'current_explanation' => (string) ($soal->pembahasan ?? ''),
                'gemini_audit_format' => [
                    'id' => $soal->id,
                    'status' => 'valid/revisi',
                    'jawaban_baru' => '',
                    'pembahasan_baru' => '',
                    'soal_baru' => '',
                    'catatan' => '',
                ],
            ];
        })->values();

        $meta = [
            'exported_at' => now()->toIso8601String(),
            'provider_target' => 'gemini',
            'limit' => $limit,
            'total_exported' => $payload->count(),
            'filters' => [
                'kategori' => strtoupper(trim((string) $request->query('kategori', ''))),
                'exam_mode_id' => (int) $request->query('exam_mode_id', 0),
                'sub_category_id' => (int) $request->query('sub_category_id', 0),
            ],
            'instructions' => [
                'Audit kejelasan soal, kunci jawaban, pembahasan, typo, dan ambiguitas.',
                'Balas hanya JSON array dengan field id, status, jawaban_baru, pembahasan_baru, soal_baru, catatan.',
                'Gunakan status "valid" jika tidak perlu revisi, atau "revisi" jika perlu pembetulan.',
            ],
        ];

        $filename = 'gemini-question-audit-' . now()->format('Ymd-His') . '.json';

        return response()->streamDownload(function () use ($meta, $payload) {
            echo json_encode([
                'meta' => $meta,
                'questions' => $payload,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function auditOne(int $id, QuestionQualityService $qualityService)
    {
        try {
            $soal = Soal::findOrFail($id);
            $qualityService->runAudit($soal, 'existing_tiu');

            return back()->with('success', 'Audit AI untuk soal terpilih selesai dijalankan.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Audit soal gagal dijalankan: ' . $e->getMessage());
        }
    }

    public function auditTiuBatch(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'nullable|string|max:32',
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1',
            'audit_all_unchecked' => 'nullable|boolean',
        ]);

        $limit = (int) ($validated['limit'] ?? 100);
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
        $kategori = strtoupper(trim((string) ($validated['kategori'] ?? '')));

        $baseQuery = Soal::query()
            ->when($kategori !== '', fn ($q) => $q->where('kategori', $kategori), fn ($q) => $q->whereIn('kategori', ['TWK', 'TIU', 'TKP', 'FIGURAL', 'TEKNIS', 'MANAJERIAL', 'SOSIAL_KULTURAL', 'WAWANCARA', 'SKB_TEKNIS', 'SKB_REGULASI', 'SKB_STUDI_KASUS']))
            ->when(!empty($validated['exam_mode_id']), fn ($q) => $q->forExamMode((int) $validated['exam_mode_id'], $defaultSkdId))
            ->when(!empty($validated['sub_category_id']), fn ($q) => $q->forSubCategory((int) $validated['sub_category_id']))
            ->when(Schema::hasColumn('soals', 'qc_status'), fn ($q) => $q->where(function ($sub) {
                $sub->whereNull('qc_status')->orWhere('qc_status', '');
            }));

        if ($request->boolean('audit_all_unchecked')) {
            $limit = max(1, (clone $baseQuery)->count());
        }

        $ids = $baseQuery
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($ids)) {
            return back()->with('success', 'Tidak ada soal belum dicek yang cocok dengan filter ini.');
        }

        foreach (array_chunk($ids, 25) as $chunk) {
            AuditTiuQuestionsJob::dispatch($chunk, 'groq', null)->onQueue('default');
        }

        return back()->with('success', 'Batch audit kualitas dimasukkan ke antrean untuk ' . count($ids) . ' soal yang belum dicek.');
    }

    public function applyFix(Request $request, int $id, QuestionQualityService $qualityService)
    {
        try {
            $audit = QuestionAudit::with('question')->findOrFail($id);
            $qualityService->applySuggestedFix(
                $audit,
                $request->boolean('apply_answer', true),
                $request->boolean('apply_explanation', true)
            );

            return back()->with('success', 'Saran QC berhasil diterapkan ke soal.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Saran QC gagal diterapkan: ' . $e->getMessage());
        }
    }

    public function markReviewed(int $id, QuestionQualityService $qualityService)
    {
        try {
            $audit = QuestionAudit::with('question')->findOrFail($id);
            $qualityService->markReviewed($audit);

            return back()->with('success', 'Audit ditandai sudah direview manual.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Audit gagal ditandai reviewed: ' . $e->getMessage());
        }
    }
}
