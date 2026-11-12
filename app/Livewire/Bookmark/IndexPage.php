<?php

namespace App\Livewire\Bookmark;

use App\Models\Bookmark;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use WithPagination;

    public function removeBookmark(int $soalId): void
    {
        Bookmark::query()
            ->where('user_id', auth()->id())
            ->where('question_id', $soalId)
            ->delete();

        $count = Bookmark::query()->where('user_id', auth()->id())->count();
        $lastPage = max(1, (int) ceil($count / 20));
        if ($this->getPage() > $lastPage) {
            $this->setPage($lastPage);
        }
    }

    public function render()
    {
        $bookmarks = Bookmark::query()
            ->with(['question.examMode', 'question.subCategory'])
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.bookmark.index-page', compact('bookmarks'));
    }
}
