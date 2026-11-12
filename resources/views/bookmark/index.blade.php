@extends('layouts.app')

@section('content')
<div class="container page-shell">
    <section class="page-hero apple-glass apple-glass-card mb-4">
        <div class="page-hero__row">
            <div class="page-hero__copy">
                <div class="apple-kicker">Favorit</div>
                <div class="display-6 apple-title mb-1">Bookmark Soal</div>
                <div class="apple-subtitle">Soal yang kamu tandai akan terkumpul di sini supaya gampang direview lagi.</div>
            </div>
            <div class="page-toolbar">
                <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-house me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </section>

    @if($bookmarks->isEmpty())
        <div class="page-empty apple-glass apple-glass-card">
            Belum ada soal favorit. Saat ujian berjalan, kamu bisa tandai soal yang ingin disimpan.
        </div>
    @else
        <div class="d-grid gap-3 gap-md-4">
            @foreach($bookmarks as $bookmark)
                @php($soal = $bookmark->question)
                @if($soal)
                    <div class="apple-glass apple-glass-card p-3 p-md-4">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="apple-pill">{{ $soal->kategori }}</span>
                            <span class="apple-pill muted">{{ $soal->examMode->name ?? 'Mode default' }}</span>
                            @if($soal->subCategory)
                                <span class="apple-pill muted">{{ $soal->subCategory->name }}</span>
                            @endif
                            @if(!empty($soal->difficulty))
                                <span class="apple-pill muted">{{ strtoupper($soal->difficulty) }}</span>
                            @endif
                        </div>
                        <div class="mb-3">{!! \App\Support\QuestionDisplaySupport::format($soal->teks_soal, $soal->kategori, $soal->sub_topik) !!}</div>
                        <div class="apple-hint mb-3">Disimpan: {{ optional($bookmark->created_at)->format('d/m/Y H:i') }}</div>
                        <form action="{{ route('bookmark.destroy', $soal->id) }}" method="POST" onsubmit="return confirm('Hapus soal ini dari favorit?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="apple-glass-btn apple-btn-danger w-100 w-md-auto">
                                <i class="fas fa-bookmark-slash me-2"></i>Hapus dari Favorit
                            </button>
                        </form>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $bookmarks->links() }}
        </div>
    @endif
</div>
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Ranking',
            'icon' => 'fa-ranking-star',
            'href' => route('ujian.dashboard'),
        ],
        [
            'label' => 'Favorit',
            'icon' => 'fa-bookmark',
            'href' => route('bookmark.index'),
            'style' => 'cta',
            'aria' => 'Bookmark Soal',
        ],
        [
            'label' => 'Mulai',
            'icon' => 'fa-play',
            'id' => 'dockQuickStartBtn',
            'style' => 'item',
            'aria' => 'Mulai Ujian',
        ],
    ],
])
@endsection
