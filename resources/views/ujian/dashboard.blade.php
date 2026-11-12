@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .rank-hero{border-radius:28px;border:1px solid var(--border-glass);background:radial-gradient(1100px 420px at 15% 0%, rgba(34,211,238,.18), transparent 55%),radial-gradient(900px 420px at 90% 15%, rgba(59,130,246,.22), transparent 55%),linear-gradient(135deg, rgba(30,41,59,.55), rgba(15,23,42,.55));box-shadow:var(--shadow-premium);overflow:hidden}
    [data-bs-theme="light"] .rank-hero{background:radial-gradient(1100px 420px at 15% 0%, rgba(8,145,178,.14), transparent 55%),radial-gradient(900px 420px at 90% 15%, rgba(37,99,235,.16), transparent 55%),linear-gradient(135deg, rgba(255,255,255,.85), rgba(241,245,249,.85))}
    .rank-summary,.rank-mode-stat,.rank-card{border-radius:20px}
    .rank-summary,.rank-mode-stat{padding:18px;border:1px solid var(--border-glass);background:rgba(255,255,255,.04)}
    .mode-badge-strong,.sub-badge-soft{display:inline-flex;align-items:center;border-radius:999px;font-weight:800}
    .mode-badge-strong{padding:9px 14px;border:1px solid rgba(34,211,238,.22);background:linear-gradient(135deg, rgba(34,211,238,.18), rgba(59,130,246,.12));color:var(--text-primary)}
    .sub-badge-soft{padding:8px 13px;border:1px solid var(--border-glass);background:rgba(255,255,255,.04);color:var(--text-secondary);font-weight:700}
    .mode-badge-strong.farmasi-mode-badge{border-color:rgba(16,185,129,.26);background:linear-gradient(135deg, rgba(16,185,129,.22), rgba(14,165,233,.10))}
    .sub-badge-soft.farmasi-sub-badge{border-color:rgba(16,185,129,.22);background:rgba(16,185,129,.08);color:var(--text-primary)}
    .rank-filter-panel{border-radius:22px}
    @media (max-width: 767px){
        .rank-hero{border-radius:22px;padding:20px 16px !important}
        .rank-summary,.rank-mode-stat,.rank-card{border-radius:18px}
        .rank-summary,.rank-mode-stat{width:100%;padding:16px}
        .rank-filter-panel{padding:16px !important;border-radius:18px}
        .rank-filter-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .rank-filter-actions .apple-glass-btn{width:100%;justify-content:center}
    }
</style>
@endpush

<div class="container page-shell">
    <div class="rank-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
            <div>
                <div class="apple-kicker">Ranking</div>
                <h2 class="apple-title mb-2">Ranking Simulasi Ujian</h2>
                <div class="apple-subtitle">Filter kembali ke mode biasa, jadi lebih stabil tanpa Livewire.</div>
            </div>
            <div class="d-flex flex-wrap gap-3 w-100 w-lg-auto">
                <div class="rank-summary">
                    <div class="apple-label">Total Peserta</div>
                    <div class="h3 fw-800 mb-0">{{ $rankings->count() }}</div>
                </div>
                <div class="rank-summary">
                    <div class="apple-label">Mode Aktif</div>
                    <div class="h5 fw-800 mb-0">{{ $selectedMode?->name ?? 'Semua Mode' }}</div>
                </div>
            </div>
        </div>
        @if($modeStats->isNotEmpty())
            <div class="d-flex flex-wrap gap-3 mt-4 pt-2">
                @foreach($modeStats as $modeStat)
                    <div class="rank-mode-stat">
                        <div class="apple-label">{{ $modeStat->name }}</div>
                        <div class="d-flex align-items-end justify-content-between gap-3">
                            <div class="h4 fw-800 mb-0">{{ $modeStat->count }}</div>
                            <div class="apple-hint">peserta</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <form method="GET" class="apple-glass apple-glass-card rank-filter-panel p-3 p-md-4 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-4"><input name="q" value="{{ $search }}" class="form-control apple-glass-input" placeholder="Cari nama peserta..."></div>
            <div class="col-md-2">
                <select name="paket" class="form-select apple-glass-input">
                    <option value="">Semua Paket</option>
                    @foreach(['A'=>'Paket A','B'=>'Paket B','C'=>'Paket C','D'=>'Paket D'] as $code => $label)
                        <option value="{{ $code }}" @selected($paket === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="exam_mode_id" class="form-select apple-glass-input">
                    <option value="">Semua Mode</option>
                    @foreach($examModes as $mode)
                        <option value="{{ $mode->id }}" @selected((int) $examModeId === (int) $mode->id)>{{ $mode->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="sub_category_id" class="form-select apple-glass-input">
                    <option value="">Semua Sub Kategori</option>
                    @foreach($subCategories as $subCategory)
                        <option value="{{ $subCategory->id }}" @selected((int) $subCategoryId === (int) $subCategory->id)>{{ $subCategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2 justify-content-md-end rank-filter-actions">
                <button type="submit" class="apple-glass-btn apple-btn-primary">Cari</button>
                <a href="{{ route('ujian.dashboard') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Reset</a>
            </div>
        </div>
    </form>

    <div class="table-responsive d-none d-lg-block apple-glass-table-wrap">
        <table class="table apple-glass-table mb-0">
            <thead class="text-center small">
                <tr><th>Rank</th><th class="text-start">Peserta</th><th>Mode</th><th>Paket</th><th>Tanggal & Jam</th><th>Ringkasan Skor</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody class="text-center">
                @forelse($rankings as $key => $p)
                    @php($scoreMeta = \App\Support\ExamRankingSupport::buildScoreMeta($p))
                    @php($modeName = \App\Support\ExamRankingSupport::modeName($p))
                    @php($subName = \App\Support\ExamRankingSupport::subCategoryName($p))
                    @php($isFarmasi = \App\Support\ExamRankingSupport::isFarmasiContext($modeName, $subName))
                    <tr>
                        <td class="fw-800">{{ $key + 1 }}</td>
                        <td class="text-start">
                            <div class="fw-700">{{ $p->nama_peserta }}</div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="mode-badge-strong {{ $isFarmasi ? 'farmasi-mode-badge' : '' }}">{{ $modeName }}</span>
                                @if($subName)<span class="sub-badge-soft {{ $isFarmasi ? 'farmasi-sub-badge' : '' }}">{{ $subName }}</span>@endif
                            </div>
                        </td>
                        <td><span class="mode-badge-strong {{ $isFarmasi ? 'farmasi-mode-badge' : '' }}">{{ $modeName }}</span></td>
                        <td><span class="apple-pill muted">Paket {{ $p->paket }}</span></td>
                        <td class="small apple-muted">{{ optional($p->updated_at)->format('d/m/Y H:i') }}</td>
                        <td><div class="apple-chip-row justify-content-center">@foreach($scoreMeta['chips'] as $chip)<span class="apple-chip {{ $chip['state'] }}"><span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}</span>@endforeach</div></td>
                        <td><span class="apple-pill total">{{ $p->total_skor }}</span></td>
                        <td><span class="apple-pill {{ $scoreMeta['passed'] ? 'success' : 'danger' }}">{{ $scoreMeta['status'] }}</span></td>
                        <td><a href="{{ route('ujian.hasil', $p->id) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-5 apple-muted">Belum ada data peserta yang cocok dengan filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-lg-none vstack gap-3">
        @forelse($rankings as $key => $p)
            @php($scoreMeta = \App\Support\ExamRankingSupport::buildScoreMeta($p))
            @php($modeName = \App\Support\ExamRankingSupport::modeName($p))
            @php($subName = \App\Support\ExamRankingSupport::subCategoryName($p))
            @php($isFarmasi = \App\Support\ExamRankingSupport::isFarmasiContext($modeName, $subName))
            <div class="rank-card apple-glass apple-glass-card p-3">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div style="min-width:0;">
                        <div class="fw-800" style="font-size:1.04rem;">{{ $p->nama_peserta }}</div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="mode-badge-strong {{ $isFarmasi ? 'farmasi-mode-badge' : '' }}">{{ $modeName }}</span>
                            <span class="apple-pill muted">Paket {{ $p->paket }}</span>
                            @if($subName)<span class="sub-badge-soft {{ $isFarmasi ? 'farmasi-sub-badge' : '' }}">{{ $subName }}</span>@endif
                        </div>
                        <div class="apple-hint mt-2">{{ optional($p->updated_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="text-end d-flex flex-column gap-2 align-items-end">
                        <span class="apple-pill total">#{{ $key + 1 }} • {{ $p->total_skor }}</span>
                        <span class="apple-pill {{ $scoreMeta['passed'] ? 'success' : 'danger' }}">{{ $scoreMeta['status'] }}</span>
                    </div>
                </div>
                <div class="mt-3 apple-chip-row">@foreach($scoreMeta['chips'] as $chip)<span class="apple-chip {{ $chip['state'] }}"><span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}</span>@endforeach</div>
                <a href="{{ route('ujian.hasil', $p->id) }}" class="apple-glass-btn apple-btn-outline w-100 mt-3 text-decoration-none d-flex align-items-center justify-content-center gap-2"><i class="fas fa-eye opacity-75"></i>Detail</a>
            </div>
        @empty
            <div class="py-5 text-center apple-muted">Belum ada data peserta yang cocok dengan filter ini.</div>
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
            'active' => true,
        ],
        [
            'label' => 'Mulai',
            'icon' => 'fa-play',
            'id' => 'dockQuickStartBtn',
            'style' => 'cta',
            'aria' => 'Mulai Ujian',
        ],
        [
            'label' => 'Klasemen',
            'icon' => 'fa-trophy',
            'href' => route('ujian.leaderboard'),
        ],
    ],
])
@endsection
