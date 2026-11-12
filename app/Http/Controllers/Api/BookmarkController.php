<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Soal;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $rows = Bookmark::query()
            ->with(['question.examMode', 'question.subCategory'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|integer|exists:soals,id',
        ]);

        $bookmark = Bookmark::firstOrCreate([
            'user_id' => $request->user()->id,
            'question_id' => (int) $validated['question_id'],
        ]);

        return response()->json([
            'ok' => true,
            'data' => $bookmark->loadMissing(['question.examMode', 'question.subCategory']),
        ], $bookmark->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, int $questionId)
    {
        Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('question_id', $questionId)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
