<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AuditTiuQuestionsJob;
use App\Models\ExamMode;
use App\Models\QuestionAudit;
use App\Models\Soal;
use App\Models\SubCategory;
use App\Services\AISoalService;
use App\Services\QuestionQualityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Soal::query()->with(['examMode', 'subCategory', 'creator']);

        if ($request->filled('exam_mode_id')) {
            $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;
            $query->forExamMode((int) $request->query('exam_mode_id'), $defaultSkdId);
        }
        if ($request->filled('sub_category_id')) {
            $query->forSubCategory((int) $request->query('sub_category_id'));
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', strtoupper((string) $request->query('kategori')));
        }
        if ($request->filled('difficulty') && Schema::hasColumn('soals', 'difficulty')) {
            $query->where('difficulty', (string) $request->query('difficulty'));
        }

        $rows = $query->orderByDesc('id')->paginate(20);

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|in:TWK,TIU,TKP,FIGURAL,TEKNIS,MANAJERIAL,SOSIAL_KULTURAL,WAWANCARA,SKB_TEKNIS,SKB_REGULASI,SKB_STUDI_KASUS',
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
        ]);

        $payload = $validated;
        if (Schema::hasColumn('soals', 'created_by')) {
            $payload['created_by'] = $request->user()?->id;
        }

        $row = Soal::create($payload);

        return response()->json(['ok' => true, 'data' => $row], 201);
    }

    public function update(Request $request, int $id)
    {
        $row = Soal::findOrFail($id);

        $validated = $request->validate([
            'kategori' => 'required|in:TWK,TIU,TKP,FIGURAL,TEKNIS,MANAJERIAL,SOSIAL_KULTURAL,WAWANCARA,SKB_TEKNIS,SKB_REGULASI,SKB_STUDI_KASUS',
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
        ]);

        $row->update($validated);

        return response()->json(['ok' => true, 'data' => $row]);
    }

    public function destroy(int $id)
    {
        $row = Soal::findOrFail($id);
        $row->delete();

        return response()->json(['ok' => true]);
    }

    public function generate(Request $request, AISoalService $ai)
    {
        $validated = $request->validate([
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'total' => 'required|integer|min:1|max:20',
            'provider' => 'nullable|in:groq,ollama_qwen',
        ]);

        $examMode = null;
        $subCategory = null;
        if (Schema::hasTable('exam_modes') && !empty($validated['exam_mode_id'])) {
            $examMode = ExamMode::find($validated['exam_mode_id']);
        }
        if (Schema::hasTable('sub_categories') && !empty($validated['sub_category_id'])) {
            $subCategory = SubCategory::find($validated['sub_category_id']);
        }

        $kategori = 'TEKNIS';
        $subName = strtoupper((string) ($subCategory->name ?? ''));
        $modeCode = strtolower((string) ($examMode->code ?? 'skd'));
        if ($modeCode === 'skd') {
            $kategori = 'TIU';
            if ($subName === 'TWK') $kategori = 'TWK';
            elseif ($subName === 'TIU') $kategori = 'TIU';
            elseif ($subName === 'TKP') $kategori = 'TKP';
            else $kategori = 'TWK';
        } elseif ($modeCode === 'skb') {
            $kategori = 'SKB_TEKNIS';
        } elseif ($modeCode === 'pppk') {
            $kategori = 'TEKNIS';
        }

        $result = $ai->generateSoal(
            $kategori,
            (int) $validated['total'],
            (string) ($validated['provider'] ?? 'groq'),
            null,
            null,
            $subCategory?->name,
            null,
            $examMode?->id,
            $subCategory?->id,
            (string) ($validated['difficulty'] ?? 'medium'),
            $request->user()?->id
        );

        if ($result !== true) {
            return response()->json(['ok' => false, 'message' => (string) $result], 422);
        }

        return response()->json(['ok' => true, 'message' => 'Generate selesai.']);
    }

    public function generateWithQc(Request $request, QuestionQualityService $qualityService)
    {
        $validated = $request->validate([
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'total' => 'required|integer|min:1|max:20',
            'provider' => 'nullable|in:groq,ollama_qwen',
        ]);

        $examMode = null;
        $subCategory = null;
        if (Schema::hasTable('exam_modes') && !empty($validated['exam_mode_id'])) {
            $examMode = ExamMode::find($validated['exam_mode_id']);
        }
        if (Schema::hasTable('sub_categories') && !empty($validated['sub_category_id'])) {
            $subCategory = SubCategory::find($validated['sub_category_id']);
        }

        $kategori = 'TEKNIS';
        $subName = strtoupper((string) ($subCategory->name ?? ''));
        $modeCode = strtolower((string) ($examMode->code ?? 'skd'));
        if ($modeCode === 'skd') {
            $kategori = match ($subName) {
                'TWK' => 'TWK',
                'TIU' => 'TIU',
                'TKP' => 'TKP',
                default => 'TWK',
            };
        } elseif ($modeCode === 'skb') {
            $kategori = 'SKB_TEKNIS';
        } elseif ($modeCode === 'pppk') {
            $kategori = 'TEKNIS';
        }

        $result = $qualityService->generateWithQc([
            'kategori' => $kategori,
            'total' => (int) $validated['total'],
            'provider' => (string) ($validated['provider'] ?? 'groq'),
            'difficulty' => (string) ($validated['difficulty'] ?? 'medium'),
            'exam_mode_id' => $examMode?->id,
            'sub_category_id' => $subCategory?->id,
            'fokus_topik' => $subCategory?->name,
        ], $request->user());

        if (!$result['ok']) {
            return response()->json(['ok' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => $result['message'],
            'data' => $result['summary'],
        ]);
    }

    public function audit(Request $request, int $id, QuestionQualityService $qualityService)
    {
        $row = Soal::findOrFail($id);
        $audit = $qualityService->runAudit(
            $row,
            'existing_tiu',
            (string) $request->input('provider', 'groq'),
            $request->filled('model') ? (string) $request->input('model') : null
        );

        return response()->json([
            'ok' => true,
            'data' => $audit->loadMissing('question'),
        ]);
    }

    public function auditTiuBatch(Request $request)
    {
        $validated = $request->validate([
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'provider' => 'nullable|in:groq,ollama_qwen',
            'model' => 'nullable|string|max:120',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        $limit = (int) ($validated['limit'] ?? 100);
        $provider = (string) ($validated['provider'] ?? 'groq');
        $model = $request->filled('model') ? (string) $validated['model'] : null;
        $defaultSkdId = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd()?->id : null;

        $query = Soal::query()
            ->where('kategori', 'TIU')
            ->when(!empty($validated['exam_mode_id']), fn ($q) => $q->forExamMode((int) $validated['exam_mode_id'], $defaultSkdId))
            ->when(!empty($validated['sub_category_id']), fn ($q) => $q->forSubCategory((int) $validated['sub_category_id']))
            ->orderBy('id')
            ->limit($limit);

        $ids = $query->pluck('id')->map(fn ($id) => (int) $id)->all();
        foreach (array_chunk($ids, 25) as $chunk) {
            AuditTiuQuestionsJob::dispatch($chunk, $provider, $model)->onQueue('default');
        }

        return response()->json([
            'ok' => true,
            'message' => 'Batch audit TIU sudah dimasukkan ke antrean.',
            'data' => [
                'queued' => count($ids),
                'chunks' => count(array_chunk($ids, 25)),
            ],
        ]);
    }

    public function flagged(Request $request)
    {
        $rows = QuestionAudit::query()
            ->with('question')
            ->whereIn('status', ['flagged', 'rejected', 'wrong', 'suspicious'])
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function applyFix(Request $request, int $id, QuestionQualityService $qualityService)
    {
        $audit = QuestionAudit::with('question')->findOrFail($id);
        $validated = $request->validate([
            'apply_answer' => 'nullable|boolean',
            'apply_explanation' => 'nullable|boolean',
        ]);

        $question = $qualityService->applySuggestedFix(
            $audit,
            (bool) ($validated['apply_answer'] ?? true),
            (bool) ($validated['apply_explanation'] ?? true)
        );

        return response()->json([
            'ok' => true,
            'message' => 'Saran perbaikan berhasil diterapkan.',
            'data' => $question,
        ]);
    }
}
