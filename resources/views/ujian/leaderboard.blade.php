@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .leader-hero{border-radius:28px;border:1px solid var(--border-glass);background:radial-gradient(1100px 420px at 15% 0%, rgba(245,158,11,.18), transparent 55%),radial-gradient(900px 420px at 90% 15%, rgba(59,130,246,.20), transparent 55%),linear-gradient(135deg, rgba(30,41,59,.55), rgba(15,23,42,.55));box-shadow:var(--shadow-premium);overflow:hidden}
    .leader-card,.leader-mode-stat{border-radius:20px}
    .leader-mode-stat{min-width:120px;padding:16px 18px;border:1px solid var(--border-glass);background:rgba(255,255,255,.05)}
    .rank-badge{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;font-weight:900;background:rgba(255,255,255,.05);color:var(--text-secondary);border:1px solid var(--border-glass)}
    .rank-1{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;box-shadow:0 5px 15px rgba(245,158,11,.4)} .rank-2{background:linear-gradient(135deg,#94a3b8,#64748b);color:#fff} .rank-3{background:linear-gradient(135deg,#b45309,#78350f);color:#fff}
    .mode-badge-strong,.sub-badge-soft{display:inline-flex;align-items:center;border-radius:999px}
    .mode-badge-strong{padding:9px 14px;border:1px solid rgba(245,158,11,.22);background:linear-gradient(135deg, rgba(245,158,11,.18), rgba(59,130,246,.10));color:var(--text-primary);font-weight:800}
    .sub-badge-soft{padding:8px 13px;border:1px solid var(--border-glass);background:rgba(255,255,255,.04);color:var(--text-secondary);font-weight:700}
    .mode-badge-strong.farmasi-mode-badge{border-color:rgba(16,185,129,.26);background:linear-gradient(135deg, rgba(16,185,129,.22), rgba(245,158,11,.10))}
    .sub-badge-soft.farmasi-sub-badge{border-color:rgba(16,185,129,.22);background:rgba(16,185,129,.08);color:var(--text-primary)}
    .leader-filter-panel{border-radius:22px}
    @media (max-width: 767px){
        .leader-hero{border-radius:22px;padding:20px 16px !important}
        .leader-card,.leader-mode-stat{border-radius:18px}
        .leader-mode-stat{width:100%;padding:16px}
        .leader-filter-panel{padding:16px !important;border-radius:18px}
        .leader-filter-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .leader-filter-actions .apple-glass-btn{width:100%;justify-content:center}
    }
</style>
@endpush

<div class="container page-shell">
    <div class="leader-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
            <div>
                <div class="apple-kicker">Klasemen</div>
                <h1 class="apple-title mb-2">Papan Peringkat Tryout</h1>
                <div class="apple-subtitle">Klasemen kembali ke filter GET biasa supaya stabil di semua perangkat.</div>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <div class="apple-pill total">{{ $rankings->count() }} peserta</div>
                <div class="apple-pill muted">{{ $selectedMode?->name ?? 'Semua Mode' }}</div>
            </div>
        </div>
        @if($modeStats->isNotEmpty())
            <div class="d-flex flex-wrap gap-3 mt-4 pt-2">
                @foreach($modeStats as $modeStat)
                    <div class="leader-mode-stat">
                        <div class="apple-label">{{ $modeStat->name }}</div>
                        <div class="d-flex align-items-end justify-content-between gap-3"><div class="h4 fw-800 mb-0">{{ $modeStat->count }}</div><div class="apple-hint">peserta</div></div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <form method="GET" class="apple-glass apple-glass-card leader-filter-panel p-3 p-md-4 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-4">
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['D' => 'Full Tryout', 'A' => 'Paket A', 'B' => 'Paket B', 'C' => 'Paket C'] as $code => $label)
                        <button type="submit" name="paket" value="{{ $code }}" class="apple-glass-btn {{ $paket === $code ? 'apple-btn-primary' : 'apple-btn-outline' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-2">
                <select name="exam_mode_id" class="form-select apple-glass-input">
                    <option value="">Semua Mode</option>
                    @foreach($examModes as $mode)
                        <option value="{{ $mode->id }}" @selected((int) $examModeId === (int) $mode->id)>{{ $mode->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <select name="sub_category_id" class="form-select apple-glass-input">
                    <option value="">Semua Sub Kategori</option>
                    @foreach($subCategories as $subCategory)
                        <option value="{{ $subCategory->id }}" @selected((int) $subCategoryId === (int) $subCategory->id)>{{ $subCategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2"><input name="q" value="{{ $search }}" class="form-control apple-glass-input" placeholder="Cari nama peserta..."></div>
            <div class="col-lg-1 d-flex gap-2 justify-content-lg-end leader-filter-actions">
                <button type="submit" class="apple-glass-btn apple-btn-primary">Cari</button>
                <a href="{{ route('ujian.leaderboard') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-responsive d-none d-lg-block apple-glass-table-wrap">
        <table class="table apple-glass-table mb-0">
            <thead><tr><th>Rank</th><th>Peserta</th><th>Mode</th><th>Paket</th><th>Ringkasan Skor</th><th>Total</th><th>Update</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($rankings as $index => $r)
                    @php($scoreMeta = \App\Support\ExamRankingSupport::buildScoreMeta($r))
                    @php($modeName = \App\Support\ExamRankingSupport::modeName($r))
                    @php($subName = \App\Support\ExamRankingSupport::subCategoryName($r))
                    @php($isFarmasi = \App\Support\ExamRankingSupport::isFarmasiContext($modeName, $subName))
                    <tr>
                        <td><div class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">{{ $index + 1 }}</div></td>
                        <td><div class="fw-700">{{ strtoupper($r->nama_peserta) }}</div><div class="d-flex flex-wrap gap-2 mt-2"><span class="mode-badge-strong {{ $isFarmasi ? 'farmasi-mode-badge' : '' }}">{{ $modeName }}</span>@if($subName)<span class="sub-badge-soft {{ $isFarmasi ? 'farmasi-sub-badge' : '' }}">{{ $subName }}</span>@endif</div></td>
                        <td><span class="mode-badge-strong {{ $isFarmasi ? 'farmasi-mode-badge' : '' }}">{{ $modeName }}</span></td>
                        <td><span class="apple-pill muted">Paket {{ $r->paket }}</span></td>
                        <td><div class="apple-chip-row">@foreach($scoreMeta['chips'] as $chip)<span class="apple-chip {{ $chip['state'] }}"><span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}</span>@endforeach</div></td>
                        <td><span class="apple-pill total">{{ $r->total_skor }}</span></td>
                        <td class="small apple-muted">{{ optional($r->updated_at)->diffForHumans() }}</td>
                        <td><a href="{{ route('ujian.hasil', $r->id) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 apple-muted">Belum ada peserta yang cocok dengan filter klasemen ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-lg-none vstack gap-3">
        @forelse($rankings as $index => $r)
            @php($scoreMeta = \App\Support\ExamRankingSupport::buildScoreMeta($r))
            @php($modeName = \App\Support\ExamRankingSupport::modeName($r))
            @php($subName = \App\Support\ExamRankingSupport::subCategoryName($r))
            @php($isFarmasi = \App\Support\ExamRankingSupport::isFarmasiContext($modeName, $subName))
            <div class="leader-card apple-glass apple-glass-card p-3">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="d-flex gap-3" style="min-width:0;">
                        <div class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">{{ $index + 1 }}</div>
                        <div style="min-width:0;">
                            <div class="fw-800">{{ strtoupper($r->nama_peserta) }}</div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="mode-badge-strong {{ $isFarmasi ? 'farmasi-mode-badge' : '' }}">{{ $modeName }}</span>
                                <span class="apple-pill muted">Paket {{ $r->paket }}</span>
                                @if($subName)<span class="sub-badge-soft {{ $isFarmasi ? 'farmasi-sub-badge' : '' }}">{{ $subName }}</span>@endif
                            </div>
                            <div class="apple-hint mt-2">{{ optional($r->updated_at)->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="text-end"><span class="apple-pill total">{{ $r->total_skor }}</span></div>
                </div>
                <div class="mt-3 apple-chip-row">@foreach($scoreMeta['chips'] as $chip)<span class="apple-chip {{ $chip['state'] }}"><span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}</span>@endforeach</div>
                <a href="{{ route('ujian.hasil', $r->id) }}" class="apple-glass-btn apple-btn-outline w-100 mt-3 text-decoration-none d-flex align-items-center justify-content-center gap-2"><i class="fas fa-eye opacity-75"></i>Detail</a>
            </div>
        @empty
            <div class="text-center py-5 apple-muted">Belum ada peserta yang cocok dengan filter klasemen ini.</div>
        @endforelse
    </div>
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
            'label' => 'Klasemen',
            'icon' => 'fa-trophy',
            'href' => route('ujian.leaderboard'),
            'style' => 'cta',
            'aria' => 'Klasemen',
        ],
        [
            'label' => 'Favorit',
            'icon' => 'fa-bookmark',
            'href' => route('bookmark.index'),
        ],
    ],
])
@endsection
