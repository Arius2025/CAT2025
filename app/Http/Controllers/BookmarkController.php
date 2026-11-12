<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Soal;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = Bookmark::query()
            ->with(['question.examMode', 'question.subCategory'])
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(20);

        return view('bookmark.index', compact('bookmarks'));
    }

    public function store(Request $request, int $soalId)
    {
        Soal::findOrFail($soalId);

        Bookmark::firstOrCreate([
            'user_id' => $request->user()->id,
            'question_id' => $soalId,
        ]);

        return response()->json(['ok' => true, 'bookmarked' => true]);
    }

    public function destroy(Request $request, int $soalId)
    {
        Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('question_id', $soalId)
            ->delete();

        return response()->json(['ok' => true, 'bookmarked' => false]);
    }
}
