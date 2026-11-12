<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamMode;
use App\Models\SubCategory;
use App\Support\ExamStructureSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ExamModeController extends Controller
{
    public function index()
    {
        $preferredModeIds = auth()->check() && method_exists(auth()->user(), 'preferredExamModeIds')
            ? auth()->user()->preferredExamModeIds()
            : [];

        if (!Schema::hasTable('exam_modes')) {
            $rows = ExamStructureSupport::modes()->values();
            if (!empty($preferredModeIds)) {
                $rows = collect($rows)->filter(fn ($mode) => in_array((int) ($mode->id ?? 0), $preferredModeIds, true))->values();
            }
            return response()->json(['ok' => true, 'data' => $rows]);
        }

        $modes = ExamMode::query()
            ->active()
            ->when(!empty($preferredModeIds), fn ($q) => $q->whereIn('id', $preferredModeIds))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color', 'has_figural', 'is_active']);

        return response()->json([
            'ok' => true,
            'data' => $modes,
        ]);
    }

    public function subCategories(Request $request)
    {
        $examModeId = (int) $request->query('exam_mode_id', 0);
        $examModeCode = (string) $request->query('exam_mode_code', '');
        $kind = trim((string) $request->query('kind', 'formasi'));
        $rows = ExamStructureSupport::subCategories($examModeId ?: null, $examModeCode, $kind !== '' ? $kind : null)->values();

        if ($kind === 'formasi' && strtolower($examModeCode) !== 'skd' && auth()->check() && method_exists(auth()->user(), 'preferredFormasiNames')) {
            $preferredNames = auth()->user()->preferredFormasiNames();
            if (!empty($preferredNames)) {
                $rows = collect($rows)
                    ->filter(fn ($row) => in_array(trim((string) ($row->name ?? $row['name'] ?? '')), $preferredNames, true))
                    ->values();
            }
        }

        $rows = collect($rows)
            ->groupBy(fn ($row) => mb_strtolower(trim((string) ($row->name ?? $row['name'] ?? ''))))
            ->map(fn ($group) => $group->first())
            ->values();

        return response()->json([
            'ok' => true,
            'data' => $rows,
        ]);
    }

    public function packages(Request $request)
    {
        $examModeId = (int) $request->query('exam_mode_id', 0);
        $examModeCode = (string) $request->query('exam_mode_code', '');
        $rows = ExamStructureSupport::packages($examModeId ?: null, $examModeCode)->values();

        return response()->json([
            'ok' => true,
            'data' => $rows,
            'categories' => ExamStructureSupport::categoryOptions($examModeId ?: null, $examModeCode)->values(),
        ]);
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('exam_modes')) {
            return response()->json(['ok' => false, 'message' => 'Tabel exam_modes belum tersedia.'], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:exam_modes,code',
            'color' => 'nullable|string|max:32',
            'has_figural' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $row = ExamMode::create([
            'name' => $validated['name'],
            'code' => strtolower(trim($validated['code'])),
            'color' => $validated['color'] ?? null,
            'has_figural' => (bool) ($validated['has_figural'] ?? false),
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
        ]);

        return response()->json(['ok' => true, 'data' => $row], 201);
    }

    public function update(Request $request, int $id)
    {
        $row = ExamMode::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:100', Rule::unique('exam_modes', 'code')->ignore($row->id)],
            'color' => 'nullable|string|max:32',
            'has_figural' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $row->update([
            'name' => $validated['name'],
            'code' => strtolower(trim($validated['code'])),
            'color' => $validated['color'] ?? null,
            'has_figural' => (bool) ($validated['has_figural'] ?? false),
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : $row->is_active,
        ]);

        return response()->json(['ok' => true, 'data' => $row]);
    }

    public function destroy(int $id)
    {
        $row = ExamMode::findOrFail($id);
        $row->delete();

        return response()->json(['ok' => true]);
    }
}


