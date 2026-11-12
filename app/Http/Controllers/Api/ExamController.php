<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamMode;
use App\Models\SubCategory;
use App\Models\Ujian;
use App\Services\ExamSessionService;
use App\Services\LearningAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamSessionService $examService,
        private readonly LearningAnalysisService $analysisService
    )
    {
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'exam_mode_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'paket' => 'nullable|in:A,B,C,D',
            'sub_topik' => 'nullable|string|max:120',
            'difficulty' => 'nullable|in:easy,medium,hard',
        ]);

        $examMode = null;
        $subCategory = null;
        if (Schema::hasTable('exam_modes') && !empty($validated['exam_mode_id'])) {
            $examMode = ExamMode::find($validated['exam_mode_id']);
        } else {
            $examMode = Schema::hasTable('exam_modes') ? ExamMode::defaultSkd() : null;
        }
        if (Schema::hasTable('sub_categories') && !empty($validated['sub_category_id'])) {
            $subCategory = SubCategory::find($validated['sub_category_id']);
        }

        $paket = $this->examService->inferPaket($subCategory, $examMode, $validated['paket'] ?? null);
        [$ujian, $soals] = $this->examService->createExamForUser(
            $request->user(),
            $paket,
            $request->user()->name,
            (string) ($validated['sub_topik'] ?? ''),
            $examMode,
            $subCategory,
            $validated['difficulty'] ?? null
        );

        return response()->json([
            'ok' => true,
            'data' => [
                'exam' => $ujian->loadMissing(['examMode', 'subCategory']),
                'questions' => $soals,
            ],
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $ujian = Ujian::with(['examMode', 'subCategory'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $soalIds = array_keys($ujian->jawaban_user ?? []);
        $questions = \App\Models\Soal::whereIn('id', $soalIds)->get();

        return response()->json(['ok' => true, 'data' => ['exam' => $ujian, 'questions' => $questions]]);
    }

    public function answer(Request $request, int $id)
    {
        $validated = $request->validate([
            'question_id' => 'required|integer|exists:soals,id',
            'selected_answer' => 'nullable|in:A,B,C,D,E',
            'sisa_waktu' => 'nullable|integer|min:0',
        ]);

        $ujian = Ujian::where('user_id', $request->user()->id)->findOrFail($id);
        $jawaban = $ujian->jawaban_user ?? [];
        $jawaban[$validated['question_id']] = $validated['selected_answer'] ?? null;

        $ujian->update([
            'jawaban_user' => $jawaban,
            'sisa_waktu' => $validated['sisa_waktu'] ?? $ujian->sisa_waktu,
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'question_id' => (int) $validated['question_id'],
                'selected_answer' => $validated['selected_answer'] ?? null,
                'is_correct' => null,
            ],
        ]);
    }

    public function submit(Request $request, int $id)
    {
        $ujian = Ujian::where('user_id', $request->user()->id)->findOrFail($id);
        if ($ujian->is_selesai) {
            return response()->json(['ok' => true, 'data' => $ujian]);
        }

        $scored = $this->examService->scoreExam($ujian);
        $waktuTotalDetik = (int) ($scored['waktu_total_detik'] ?? ($ujian->paket !== 'D' ? 1800 : 6000));
        $payload = [
            'skor_twk' => $scored['skor_twk'],
            'skor_tiu' => $scored['skor_tiu'],
            'skor_tkp' => $scored['skor_tkp'],
            'is_selesai' => true,
            'analitik_topik' => $scored['analitik_topik'],
            'waktu_pengerjaan' => max(0, $waktuTotalDetik - $ujian->sisa_waktu),
        ];
        if (Schema::hasColumn('ujians', 'total_score')) $payload['total_score'] = $scored['total_skor'] ?? 0;
        if (Schema::hasColumn('ujians', 'score_breakdown')) $payload['score_breakdown'] = $scored['score_breakdown'] ?? [];
        if (Schema::hasColumn('ujians', 'result_summary')) $payload['result_summary'] = $scored['result_summary'] ?? [];
        if (Schema::hasColumn('ujians', 'package_snapshot')) $payload['package_snapshot'] = $scored['package_snapshot'] ?? [];
        if (Schema::hasColumn('ujians', 'duration_minutes')) $payload['duration_minutes'] = intdiv($waktuTotalDetik, 60);
        $ujian->update($payload);
        $this->analysisService->persistExamAnswers($ujian->fresh(), (array) ($scored['answers'] ?? []));

        return response()->json([
            'ok' => true,
            'data' => [
                'exam' => $ujian->fresh()->loadMissing(['examMode', 'subCategory']),
                'answers' => $scored['answers'],
                'total_score' => $scored['total_skor'] ?? 0,
                'result_summary' => $scored['result_summary'] ?? [],
                'score_breakdown' => $scored['score_breakdown'] ?? [],
            ],
        ]);
    }

    public function history(Request $request)
    {
        $rows = Ujian::with(['examMode', 'subCategory'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function stats(Request $request)
    {
        $query = Ujian::query()
            ->where('user_id', $request->user()->id)
            ->where('is_selesai', true);

        if ($request->filled('exam_mode_id') && Schema::hasColumn('ujians', 'exam_mode_id')) {
            $query->where('exam_mode_id', (int) $request->query('exam_mode_id'));
        }

        $rows = $query->get();

        return response()->json([
            'ok' => true,
            'data' => [
                'total_exams' => $rows->count(),
                'best_score' => $rows->max(fn ($u) => (int) ($u->total_score ?? ($u->skor_twk + $u->skor_tiu + $u->skor_tkp))) ?? 0,
                'average_score' => $rows->isEmpty() ? 0 : round($rows->avg(fn ($u) => (int) ($u->total_score ?? ($u->skor_twk + $u->skor_tiu + $u->skor_tkp)))),
            ],
        ]);
    }
}


