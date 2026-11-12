<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    public function store(Request $request)
    {
        if (!Schema::hasTable('sub_categories')) {
            return response()->json(['ok' => false, 'message' => 'Tabel sub_categories belum tersedia.'], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exam_mode_id' => 'required|integer|exists:exam_modes,id',
        ]);

        $row = SubCategory::create($validated);

        return response()->json(['ok' => true, 'data' => $row], 201);
    }

    public function update(Request $request, int $id)
    {
        $row = SubCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'exam_mode_id' => 'required|integer|exists:exam_modes,id',
        ]);

        $row->update($validated);

        return response()->json(['ok' => true, 'data' => $row]);
    }

    public function destroy(int $id)
    {
        $row = SubCategory::findOrFail($id);
        $row->delete();

        return response()->json(['ok' => true]);
    }
}
