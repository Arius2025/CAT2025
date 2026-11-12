@extends('layouts.app')

@section('content')
@push('styles')
<style>
    /* Desain Dasar */
    .result-card {
        border-top: 10px solid var(--accent-blue);
        border-radius: 24px;
        background: var(--card-surface);
        border-bottom: 1px solid var(--border-glass);
    }
    .score-circle {
        width: 100px;
        height: 100px;
        border-radius: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        border: 2px solid var(--border-glass);
        background: var(--surface-2);
        box-shadow: var(--shadow-premium);
        transition: transform 0.3s ease;
    }
    .score-circle:hover { transform: translateY(-5px); }

    .status-banner {
        font-size: 1.4rem;
        font-weight: 800;
        padding: 20px;
        border-radius: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .pembahasan-card {
        background: var(--card-surface);
        border-left: 5px solid var(--accent-blue);
        border-radius: 16px;
        border-top: 1px solid var(--border-glass);
        border-right: 1px solid var(--border-glass);
        border-bottom: 1px solid var(--border-glass);
    }
    .result-badge-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-top: 18px;
    }
    .result-mode-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 16px;
        border-radius: 999px;
        border: 1px solid color-mix(in srgb, var(--accent) 24%, transparent);
        background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 16%, var(--surface)), color-mix(in srgb, var(--accent) 8%, var(--surface)));
        color: var(--text-primary);
        font-weight: 800;
        box-shadow: var(--shadow-soft);
    }
    .result-sub-badge {
        display: inline-flex;
        align-items: center;
        padding: 11px 16px;
        border-radius: 999px;
        border: 1px solid var(--border-glass);
        background: var(--surface-2);
        color: var(--text-secondary);
        font-weight: 700;
    }
    .result-mode-badge.farmasi-mode-badge {
        border-color: color-mix(in srgb, var(--success) 28%, transparent);
        background: linear-gradient(135deg, color-mix(in srgb, var(--success) 16%, var(--surface)), color-mix(in srgb, var(--accent) 8%, var(--surface)));
    }
    .result-sub-badge.farmasi-sub-badge {
        border-color: color-mix(in srgb, var(--success) 24%, transparent);
        background: color-mix(in srgb, var(--success) 10%, var(--surface));
        color: var(--text-primary);
    }
    .result-meta-band {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 18px;
        border: 1px solid var(--border-glass);
        background: var(--surface-2);
        margin-top: 16px;
    }
    .result-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: var(--text-secondary);
    }
    .breakdown-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
        margin-top: 26px;
    }
    .breakdown-item {
        border-radius: 18px;
        border: 1px solid var(--border-glass);
        background: var(--surface-2);
        padding: 16px;
    }
    .result-logo-panel {
        background: var(--surface);
        box-shadow: var(--shadow-soft);
        border: 1px solid var(--border-glass);
    }
    .result-divider {
        width: 60px;
        height: 4px;
        background: var(--accent-blue);
        border-radius: 2px;
    }
    .result-name {
        color: var(--accent-blue);
        letter-spacing: -0.4px;
    }
    .result-score-pass { color: var(--success); }
    .result-score-fail { color: var(--danger); }
    .result-score-mid { color: var(--warning); }
    .result-filter-btn {
        background: var(--surface-2);
        border: 1px solid var(--border-glass);
        color: var(--text-primary);
    }
    .result-filter-btn.is-danger {
        background: color-mix(in srgb, var(--danger) 10%, var(--surface));
        border-color: color-mix(in srgb, var(--danger) 24%, transparent);
    }
    .result-filter-btn.is-success {
        background: color-mix(in srgb, var(--success) 10%, var(--surface));
        border-color: color-mix(in srgb, var(--success) 24%, transparent);
    }
    .result-question-badge {
        background: color-mix(in srgb, var(--accent) 10%, var(--surface));
        color: var(--accent-blue);
        padding: 8px 12px;
        border-radius: 8px;
    }
    .result-favorite-badge {
        background: color-mix(in srgb, var(--warning) 12%, var(--surface));
        color: var(--text-primary);
        border: 1px solid color-mix(in srgb, var(--warning) 24%, transparent);
    }
    .result-answer-panel,
    .result-image-panel,
    .result-explain-panel {
        background: var(--surface-2);
        border: 1px solid var(--border-glass);
    }
    .result-key-panel {
        background: color-mix(in srgb, var(--accent) 8%, var(--surface));
        border: 1px solid color-mix(in srgb, var(--accent) 24%, transparent);
    }
    .result-key-badge {
        background: var(--accent-cyan);
        color: #07111f;
    }
    .result-score-chip {
        font-size: 0.9rem;
    }
    .breakdown-item__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .breakdown-item__title {
        font-weight: 800;
        color: var(--text-primary);
    }
    .breakdown-item__meta {
        color: var(--text-secondary);
        font-size: 0.82rem;
        margin-top: 6px;
        line-height: 1.5;
    }
    .breakdown-item__score {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    /* Perbaikan Mobile */
    @media (max-width: 768px) {
        .score-circle { width: 85px; height: 85px; border-radius: 18px; }
        .score-circle h2 { font-size: 1.5rem !important; }
        .display-2 { font-size: 3rem !important; }
        .status-banner { font-size: 1.1rem; }
    }

    @media print {
        .collapse { display: block !important; height: auto !important; }
    }
</style>
@endpush

<div class="container page-shell">
    @php
        $resultSummary = is_array($resultSummary ?? null) ? $resultSummary : [];
        $scoreBreakdown = is_array($ujian->score_breakdown ?? null) ? $ujian->score_breakdown : [];
        $packageSnapshot = is_array($ujian->package_snapshot ?? null) ? $ujian->package_snapshot : [];
        $resultMessages = array_values(array_filter((array) ($resultSummary['messages'] ?? [])));
        $resultLogic = (string) ($resultSummary['logic'] ?? 'rank_only');
        $resultNotes = trim((string) ($resultSummary['notes'] ?? ''));
        $packageLabel = (string) ($resultSummary['package_name'] ?? ($packageSnapshot['name'] ?? ('Paket ' . $ujian->paket)));
        $sectionRuleMap = [];
        foreach ((array) ($packageSnapshot['sections'] ?? []) as $section) {
            $sectionKey = strtoupper(trim((string) ($section['kategori'] ?? '')));
            if ($sectionKey !== '') {
                $sectionRuleMap[$sectionKey] = $section;
            }
        }
        $modeName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'exam_mode_id') && $ujian->examMode) ? $ujian->examMode->name : 'Mode default';
        $subCategoryName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'sub_category_id') && $ujian->subCategory) ? $ujian->subCategory->name : null;
        $isFarmasiMode = str_contains(strtolower($modeName . ' ' . ($subCategoryName ?? '')), 'farmasi') || str_contains(strtolower($modeName . ' ' . ($subCategoryName ?? '')), 'apoteker');
        $resultHeading = $isFarmasiMode ? 'HASIL SIMULASI FARMASI' : 'HASIL SIMULASI UJIAN';
        $resultSubheading = $isFarmasiMode ? 'RINGKASAN HASIL, NILAI SECTION, DAN PEMBAHASAN BIDANG FARMASI' : 'RINGKASAN HASIL, SKOR, DAN PEMBAHASAN';
    @endphp
        <div class="page-hero p-3 p-md-4 mb-4 d-print-none">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="apple-kicker mb-1">Hasil Ujian</div>
                <div class="apple-title h3 mb-1">{{ $resultHeading }}</div>
                <div class="apple-subtitle small">Ringkasan nilai, breakdown materi, dan pembahasan soal.</div>
            </div>
            <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
                <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none d-inline-flex align-items-center justify-content-center flex-grow-1 flex-md-grow-0">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
                <button id="btnDownloadPdf" onclick="downloadHasilPDF(this)" class="apple-glass-btn apple-btn-danger px-4 py-2 shadow-sm d-inline-flex align-items-center justify-content-center flex-grow-1 flex-md-grow-0">
                    <i class="fas fa-file-pdf me-2"></i>Download PDF
                </button>
            </div>
        </div>
    </div>

    <div id="print-area">
        
        <div class="card result-card apple-glass apple-glass-card shadow-lg mb-5">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-5">
                    <div class="mb-4 d-inline-block p-3 rounded-4 result-logo-panel">
                        <img src="https://static.promediateknologi.id/crop/0x0:0x0/0x0/webp/photo/p2/108/2024/09/25/BKN-Background-3152179497.jpg" 
                             style="width: 80px; height: 80px; object-fit: contain;">
                    </div>
                    <h2 class="fw-800 mb-1" style="color: var(--text-primary); letter-spacing: -0.5px;">{{ $resultHeading }}</h2>
                    <p class="text-secondary small mb-0 opacity-75">{{ $resultSubheading }}</p>
                    <div class="result-badge-row">
                        <span class="result-mode-badge {{ $isFarmasiMode ? 'farmasi-mode-badge' : '' }}">
                            <i class="fas fa-layer-group opacity-75"></i>
                            {{ $isFarmasiMode ? ($modeName . ' Farmasi') : $modeName }}
                        </span>
                        @if($subCategoryName)
                            <span class="result-sub-badge {{ $isFarmasiMode ? 'farmasi-sub-badge' : '' }}">
                                {{ $subCategoryName }}
                            </span>
                        @endif
                    </div>
                    <div class="result-meta-band">
                        <span class="result-meta-pill text-success fw-bold"><i class="fas fa-certificate text-success"></i> Standar CASN 2027</span>
                        <span class="result-meta-pill"><i class="fas fa-layer-group opacity-75"></i>{{ $packageLabel }}</span>
                        <span class="result-meta-pill"><i class="fas fa-clock opacity-75"></i>{{ optional($ujian->updated_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="mx-auto my-4 result-divider"></div>
                    <h3 class="text-uppercase fw-800 result-name">{{ $ujian->nama_peserta }}</h3>
                </div>

                <div class="row g-3 mb-5 text-center">
                    @if(!empty($scoreBreakdown))
                        @foreach($scoreBreakdown as $sectionKey => $section)
                            @php
                                $sectionScore = (int) ($section['score'] ?? 0);
                                $sectionMin = isset($section['pass_min']) && $section['pass_min'] !== null ? (int) $section['pass_min'] : null;
                                $sectionPassed = is_null($sectionMin) ? null : $sectionScore >= $sectionMin;
                            @endphp
                            <div class="col-6 col-md-{{ count($scoreBreakdown) >= 4 ? '3' : '4' }}">
                                <div class="score-circle">
                                    <small class="text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">{{ $section['label'] ?? $sectionKey }}</small>
                                    <h2 class="fw-800 m-0 {{ is_null($sectionPassed) ? '' : ($sectionPassed ? 'result-score-pass' : 'result-score-fail') }}" style="color: {{ is_null($sectionPassed) ? 'var(--text-primary)' : '' }}">{{ $sectionScore }}</h2>
                                </div>
                                <div class="small opacity-50 fw-bold" style="font-size: 0.7rem;">
                                    {{ is_null($sectionMin) ? 'AMBANG: DINILAI BERDASARKAN PERINGKAT' : 'AMBANG: ' . $sectionMin }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-4">
                            <div class="score-circle">
                                <small class="text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">TWK</small>
                                <h2 class="fw-800 m-0 {{ $ujian->skor_twk >= 65 ? 'result-score-pass' : 'result-score-fail' }}">{{ $ujian->skor_twk }}</h2>
                            </div>
                            <div class="small opacity-50 fw-bold" style="font-size: 0.7rem;">AMBANG: 65</div>
                        </div>
                        <div class="col-4">
                            <div class="score-circle">
                                <small class="text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">TIU</small>
                                <h2 class="fw-800 m-0 {{ $ujian->skor_tiu >= 80 ? 'result-score-pass' : 'result-score-fail' }}">{{ $ujian->skor_tiu }}</h2>
                            </div>
                            <div class="small opacity-50 fw-bold" style="font-size: 0.7rem;">AMBANG: 80</div>
                        </div>
                        <div class="col-4">
                            <div class="score-circle">
                                <small class="text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">TKP</small>
                                <h2 class="fw-800 m-0 {{ $ujian->skor_tkp >= 166 ? 'result-score-pass' : 'result-score-fail' }}">{{ $ujian->skor_tkp }}</h2>
                            </div>
                            <div class="small opacity-50 fw-bold" style="font-size: 0.7rem;">AMBANG: 166</div>
                        </div>
                    @endif
                </div>

                @if(!empty($scoreBreakdown))
                    <div class="breakdown-grid mb-5">
                        @foreach($scoreBreakdown as $sectionKey => $section)
                            @php
                                $sectionScore = (int) ($section['score'] ?? 0);
                                $sectionCount = (int) ($section['count'] ?? 0);
                                $sectionRule = $sectionRuleMap[$sectionKey] ?? [];
                                $sectionMin = isset($section['pass_min']) && $section['pass_min'] !== null ? (int) $section['pass_min'] : null;
                                $sectionPassed = is_null($sectionMin) ? null : $sectionScore >= $sectionMin;
                                $scoreType = (string) ($sectionRule['score_type'] ?? 'objective');
                            @endphp
                            <div class="breakdown-item">
                                <div class="breakdown-item__head">
                                    <div>
                                        <div class="breakdown-item__title">{{ $section['label'] ?? $sectionKey }}</div>
                                        <div class="breakdown-item__meta">
                                            {{ $sectionCount }} soal
                                            @if($scoreType !== '')
                                                • {{ $scoreType === 'objective' ? 'Skor objektif' : 'Skor bertingkat' }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="apple-pill {{ is_null($sectionPassed) ? 'muted' : ($sectionPassed ? 'success' : 'danger') }}">
                                        {{ is_null($sectionPassed) ? 'Dinilai total' : ($sectionPassed ? 'Siap' : 'Kurang') }}
                                    </span>
                                </div>
                                <div class="breakdown-item__score mt-3">{{ $sectionScore }}</div>
                                <div class="breakdown-item__meta">
                                    @if(is_null($sectionMin))
                                        Ambang per section tidak dipakai. Hasil mengikuti total/ranking mode ini.
                                    @else
                                        Target minimal {{ $sectionMin }}.
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="text-center pt-5 border-top border-secondary border-opacity-10">
                    <p class="mb-2 fw-800 small opacity-50" style="letter-spacing: 2px;">TOTAL SKOR AKHIR</p>
                    <h1 class="display-1 fw-800 mb-4" style="color: var(--text-primary); letter-spacing: -2px;">{{ $ujian->total_skor }}</h1>
                    
                    <div class="status-banner shadow-lg {{ $isLulus ? 'bg-success bg-opacity-75 text-white' : (($resultLogic === 'rank_only' && empty($resultMessages)) ? 'bg-info bg-opacity-75 text-white' : 'bg-danger bg-opacity-75 text-white') }}">
                        @if($resultLogic === 'rank_only' && empty($resultMessages))
                            <i class="fas fa-ranking-star me-2"></i> HASIL MENGIKUTI PERINGKAT / NILAI AKHIR
                        @elseif($isLulus)
                            <i class="fas fa-medal me-2"></i> MEMENUHI ATURAN KELULUSAN MODE INI
                        @else
                            <i class="fas fa-exclamation-triangle me-2"></i> BELUM MEMENUHI ATURAN MODE INI
                        @endif
                    </div>
                    @if(!empty($resultMessages) || $resultNotes !== '')
                        <div class="mt-3 apple-glass-soft apple-glass-card p-3 text-start">
                            @foreach($resultMessages as $message)
                                <div class="small mb-1" style="color: var(--text-secondary);">
                                    <i class="fas fa-circle-exclamation me-2 opacity-75"></i>{{ $message }}
                                </div>
                            @endforeach
                            @if($resultNotes !== '')
                                <div class="small {{ !empty($resultMessages) ? 'mt-2' : '' }}" style="color: var(--text-secondary);">
                                    <i class="fas fa-circle-info me-2 opacity-75"></i>{{ $resultNotes }}
                                </div>
                            @endif
                        </div>
                    @endif
                    @if($ujian->waktu_pengerjaan)
                    <div class="mt-4 mb-2 fw-bold" style="color: var(--text-secondary); font-size: 0.9rem;">
                        <i class="fas fa-clock me-1"></i> Waktu Pengerjaan: {{ floor($ujian->waktu_pengerjaan / 60) }} menit {{ $ujian->waktu_pengerjaan % 60 }} detik
                    </div>
                    @endif
                </div>

                <!-- RADAR CHART ANALYTICS -->
                @if($ujian->analitik_topik)
                <div class="mt-5 pt-4 border-top border-secondary border-opacity-10 text-center">
                    <h5 class="fw-bold mb-4" style="color: var(--text-primary);">Analisis Kekuatan Sub-Topik</h5>
                    <div class="d-flex justify-content-center">
                        <div style="width: 100%; max-width: 500px; height: 350px;">
                            <canvas id="radarChart"></canvas>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="pdf-break">
            <div class="d-flex align-items-center mb-4 px-2">
                <div class="me-3 d-flex align-items-center justify-content-center result-answer-panel" style="width: 45px; height: 45px; border-radius: 12px;">
                    <i class="fas fa-brain" style="color: var(--accent-blue);"></i>
                </div>
                <h4 class="fw-800 m-0" style="color: var(--text-primary);">Analisis & Pembahasan Soal</h4>
            </div>

            <div class="d-print-none mb-3 mx-2 d-flex flex-column flex-md-row gap-2 align-items-start align-items-md-center justify-content-between">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm rounded-pill px-3 result-filter-btn" id="filterAll">
                        Semua
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 result-filter-btn is-danger" id="filterWrong">
                        Salah
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 result-filter-btn is-success" id="filterRight">
                        Benar
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 result-filter-btn" id="expandAll">
                        Buka Semua
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 result-filter-btn" id="collapseAll">
                        Tutup Semua
                    </button>
                </div>
                <div class="small" style="color: var(--text-secondary);">
                    Klik tombol "Pembahasan" untuk buka/tutup detail.
                </div>
            </div>
             
            @foreach($details as $index => $s)
            @php 
                $jawabanUser = $ujian->jawaban_user[$s->id] ?? '-';
                $sectionRule = $sectionRuleMap[strtoupper((string) $s->kategori)] ?? null;
                $scoreType = (string) ($sectionRule['score_type'] ?? ($s->kategori === 'TKP' ? 'weighted_option' : 'objective'));
                $poin = 0;
                if (in_array($scoreType, ['weighted_option', 'weighted_scale'], true)) {
                    if (is_array($s->bobot_tkp) && isset($s->bobot_tkp[$jawabanUser])) {
                        $poin = (int) $s->bobot_tkp[$jawabanUser];
                    } else {
                        $minScore = (int) ($sectionRule['min_option_score'] ?? 1);
                        $maxScore = (int) ($sectionRule['max_option_score'] ?? ($scoreType === 'weighted_option' ? 5 : 4));
                        $fallbackMap = ['A' => $maxScore, 'B' => max($minScore, $maxScore - 1), 'C' => max($minScore, $maxScore - 2), 'D' => min($maxScore, $minScore + 1), 'E' => $minScore];
                        $poin = (int) ($fallbackMap[strtoupper((string) $jawabanUser)] ?? 0);
                    }
                    $isBetul = $poin >= max(1, ((int) ($sectionRule['max_option_score'] ?? 4) - 1));
                } else {
                    $isBetul = (strtoupper($jawabanUser) == strtoupper($s->jawaban_benar));
                    $poin = $isBetul ? (int) ($sectionRule['correct_score'] ?? 5) : (int) ($sectionRule['wrong_score'] ?? 0);
                }
                $isScoreBased = in_array($scoreType, ['weighted_option', 'weighted_scale'], true);
            @endphp
            <div class="card pembahasan-card apple-glass apple-glass-card shadow-sm mb-4 border-0 mx-2 pemb-item" style="page-break-inside: avoid;" data-correct="{{ $isBetul ? '1' : '0' }}">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge result-question-badge">#{{ $index + 1 }} - {{ $s->kategori }}</span>
                            @if(in_array($s->id, $bookmarkedSoalIds ?? [], true))
                                <span class="badge rounded-pill px-3 py-2 result-favorite-badge">
                                    <i class="fas fa-bookmark me-1"></i>Favorit
                                </span>
                            @endif
                        </div>

                        <span class="fw-800 result-score-chip {{ $isScoreBased ? 'result-score-mid' : ($isBetul ? 'result-score-pass' : ($poin > 0 ? 'result-score-mid' : 'result-score-fail')) }}">
                            {{ $isScoreBased ? "Skor pilihan: $poin" : ($isBetul ? 'BENAR (+' . $poin . ')' : 'SALAH (' . $poin . ')') }}
                        </span>
                    </div>

                    <div class="mb-4">{!! \App\Support\QuestionDisplaySupport::format($s->teks_soal, $s->kategori, $s->sub_topik) !!}</div>
                    
                    @if($s->gambar_path)
                        <div class="mb-4 text-center p-3 rounded-4 result-image-panel">
                            <img src="{{ asset('storage/' . $s->gambar_path) }}" class="img-fluid rounded" style="max-height: 250px;">
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 result-answer-panel" style="font-size: 0.9rem; border-color: var(--border-glass) !important;">
                                <span class="text-secondary small fw-bold" style="letter-spacing: 1px;">JAWABAN ANDA</span><br>
                                @if($jawabanUser && $jawabanUser !== '-')
                                    <div class="mt-2 fw-bold" style="color: var(--text-primary);">
                                        <span class="badge bg-secondary me-2">{{ $jawabanUser }}</span> 
                                        {!! $s->{'opsi_'.strtolower($jawabanUser)} === '-' ? '' : $s->{'opsi_'.strtolower($jawabanUser)} !!}
                                    </div>
                                    @if(isset($s->{'gambar_'.strtolower($jawabanUser)}) && $s->{'gambar_'.strtolower($jawabanUser)})
                                        <div class="mt-3 text-start">
                                            <img src="{{ asset('storage/' . $s->{'gambar_'.strtolower($jawabanUser)}) }}" class="img-fluid rounded border border-secondary" style="max-height: 120px;">
                                        </div>
                                    @endif
                                @else
                                    <div class="mt-2 fw-bold opacity-50 result-score-fail">Tidak Menjawab</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($isScoreBased)
                                <div class="p-3 border rounded-4 result-key-panel" style="font-size: 0.9rem; border-color: var(--warning) !important;">
                                    <span class="fw-bold small" style="color: var(--warning); letter-spacing: 1px;">SISTEM PENILAIAN TKP</span><br>
                                    <div class="mt-2" style="color: var(--text-primary);">
                                        Nilai soal ini dihitung berdasarkan bobot tiap opsi. Tidak ada kunci jawaban tunggal.
                                    </div>
                                    @if(is_array($s->bobot_tkp))
                                        <div class="mt-3 d-flex flex-wrap gap-2">
                                            @foreach($s->bobot_tkp as $opt => $val)
                                                <span class="apple-pill muted">{{ $opt }}: {{ $val }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="p-3 border rounded-4 result-key-panel" style="font-size: 0.9rem; border-color: var(--accent-cyan) !important;">
                                    <span class="fw-bold small" style="color: var(--accent-cyan); letter-spacing: 1px;">KUNCI JAWABAN</span><br>
                                    <div class="mt-2 fw-bold" style="color: var(--text-primary);">
                                        <span class="badge me-2 result-key-badge">{{ $s->jawaban_benar }}</span> 
                                        {!! $s->{'opsi_'.strtolower($s->jawaban_benar)} === '-' ? '' : $s->{'opsi_'.strtolower($s->jawaban_benar)} !!}
                                    </div>
                                    @if(isset($s->{'gambar_'.strtolower($s->jawaban_benar)}) && $s->{'gambar_'.strtolower($s->jawaban_benar)})
                                        <div class="mt-3 text-start">
                                            <img src="{{ asset('storage/' . $s->{'gambar_'.strtolower($s->jawaban_benar)}) }}" class="img-fluid rounded border border-info" style="max-height: 120px;">
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm rounded-pill px-3 result-filter-btn"
                                data-bs-toggle="collapse" data-bs-target="#exp-{{ $s->id }}">
                            <i class="fas fa-chevron-down me-2 opacity-75"></i> Pembahasan
                        </button>
                    </div>

                    <div class="collapse" id="exp-{{ $s->id }}">
                        <div class="p-3 rounded-4 result-explain-panel" style="border-left: 4px solid var(--accent-blue);">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-lightbulb me-2" style="color: var(--accent-blue);"></i>
                            <strong style="color: var(--text-primary); font-size: 0.9rem; letter-spacing: 0.5px;">PEMBAHASAN</strong>
                        </div>
                        <div class="mt-2" style="color: var(--text-primary); font-size: 0.95rem; line-height: 1.6;">
                            @if($s->kategori == 'TKP' && is_array($s->bobot_tkp))
                                <div class="mb-3 d-flex flex-wrap gap-2">
                                    @foreach($s->bobot_tkp as $opt => $val)
                                        <div class="px-2 py-1 rounded border small" style="background: var(--bg-midnight); border-color: var(--border-glass) !important; color: var(--text-primary);">
                                            <strong>{{ $opt }}:</strong> {{ $val }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            {!! nl2br(e($s->pembahasan)) !!}
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    @if($ujian->analitik_topik)
    const rawAnalitik = @json(is_array($ujian->analitik_topik) ? $ujian->analitik_topik : json_decode($ujian->analitik_topik, true));
    if (rawAnalitik) {
        const styles = getComputedStyle(document.body);
        const color = (name, fallback = '') => (styles.getPropertyValue(name) || fallback).trim() || fallback;
        let labels = [];
        let dataScores = [];
        
        ['TWK', 'TIU', 'TKP'].forEach(kat => {
            if (rawAnalitik[kat]) {
                for (let topik in rawAnalitik[kat]) {
                    labels.push(topik.length > 15 ? topik.substring(0, 15) + '...' : topik);
                    dataScores.push(rawAnalitik[kat][topik]);
                }
            }
        });

        if (labels.length > 0) {
            const ctx = document.getElementById('radarChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Akurasi (%)',
                        data: dataScores,
                        backgroundColor: color('--accent-soft', 'rgba(59, 130, 246, 0.2)'),
                        borderColor: color('--accent', 'rgba(59, 130, 246, 1)'),
                        pointBackgroundColor: color('--success', 'rgba(16, 185, 129, 1)'),
                        pointBorderColor: color('--surface', '#fff'),
                        pointHoverBackgroundColor: color('--surface', '#fff'),
                        pointHoverBorderColor: color('--success', 'rgba(16, 185, 129, 1)'),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { color: color('--border', 'rgba(255, 255, 255, 0.1)') },
                            grid: { color: color('--border', 'rgba(255, 255, 255, 0.1)') },
                            pointLabels: { color: color('--text-secondary', 'rgba(248, 250, 252, 0.7)'), font: { size: 11, family: 'Inter' } },
                            ticks: { display: false, min: 0, max: 100 }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: color('--surface', 'rgba(15, 23, 42, 0.9)'),
                            titleColor: color('--text-primary', '#fff'),
                            bodyColor: color('--text-primary', '#fff'),
                            titleFont: { size: 13, family: 'Inter' },
                            bodyFont: { size: 14, weight: 'bold', family: 'Inter' },
                            callbacks: {
                                label: function(context) { return ' Akurasi: ' + context.parsed.r + '%'; }
                            }
                        }
                    }
                }
            });
        }
    }
    @endif
    function setBtnActive(activeId) {
        ['filterAll', 'filterWrong', 'filterRight'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            const isActive = id === activeId;
            el.style.filter = isActive ? 'brightness(1.08)' : '';
            el.style.boxShadow = isActive ? '0 0 0 0.2rem rgba(59,130,246,0.15)' : '';
        });
    }

    function applyFilter(mode) {
        document.querySelectorAll('.pemb-item').forEach(el => {
            const ok = mode === 'all' ? true : (el.getAttribute('data-correct') === (mode === 'right' ? '1' : '0'));
            el.style.display = ok ? '' : 'none';
        });
    }

    function setAllExplain(open) {
        document.querySelectorAll('#print-area .collapse').forEach(el => {
            try {
                const inst = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                open ? inst.show() : inst.hide();
            } catch (e) {
                // Fallback if bootstrap isn't available for some reason.
                open ? el.classList.add('show') : el.classList.remove('show');
            }
        });
    }

    document.getElementById('filterAll')?.addEventListener('click', () => { setBtnActive('filterAll'); applyFilter('all'); });
    document.getElementById('filterWrong')?.addEventListener('click', () => { setBtnActive('filterWrong'); applyFilter('wrong'); });
    document.getElementById('filterRight')?.addEventListener('click', () => { setBtnActive('filterRight'); applyFilter('right'); });
    document.getElementById('expandAll')?.addEventListener('click', () => setAllExplain(true));
    document.getElementById('collapseAll')?.addEventListener('click', () => setAllExplain(false));

    setBtnActive('filterAll');
    applyFilter('all');

    function downloadHasilPDF(triggerBtn = null) {
        const element = document.getElementById('print-area');
        const btn = triggerBtn || document.getElementById('btnDownloadPdf') || document.getElementById('btnDownloadPdfMobile');
        const isDock = btn && btn.id === 'dockPdfBtn';
        const prevHtml = btn ? btn.innerHTML : '';

        const collapseEls = Array.from(document.querySelectorAll('#print-area .collapse'));
        const prevOpen = collapseEls.map(el => el.classList.contains('show'));

        // Expand all collapses for PDF rendering.
        setAllExplain(true);
        
        // Visual feedback
        if (btn) {
            btn.innerHTML = isDock
                ? '<i class="fas fa-spinner fa-spin"></i>'
                : '<i class="fas fa-spinner fa-spin me-2"></i> Mengolah PDF...';
            btn.disabled = true;
        }

        const options = {
            margin:       [0.3, 0.3],
            filename:     'Hasil_Simulasi_Ujian_{{ Str::slug($ujian->nama_peserta) }}.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { 
                scale: 2, 
                useCORS: true, 
                letterRendering: true,
                scrollX: 0,
                scrollY: 0
            },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
        };

        // Jalankan html2pdf
        html2pdf().set(options).from(element).save().then(() => {
            if (btn) {
                btn.innerHTML = isDock ? '<i class="fas fa-file-pdf"></i>' : '<i class="fas fa-file-pdf me-2"></i> Download Hasil (PDF)';
                btn.disabled = false;
            }
            // Restore collapse state.
            collapseEls.forEach((el, i) => prevOpen[i] ? el.classList.add('show') : el.classList.remove('show'));
        }).catch(err => {
            console.error(err);
            alert('Gagal mendownload PDF. Pastikan koneksi internet stabil.');
            if (btn) {
                btn.innerHTML = prevHtml || (isDock ? '<i class="fas fa-file-pdf"></i>' : '<i class="fas fa-file-pdf me-2"></i> Download Hasil (PDF)');
                btn.disabled = false;
            }
            // Restore collapse state.
            collapseEls.forEach((el, i) => prevOpen[i] ? el.classList.add('show') : el.classList.remove('show'));
        });
    }

</script>
@endpush
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
            'label' => 'PDF',
            'icon' => 'fa-file-pdf',
            'id' => 'dockPdfBtn',
            'style' => 'cta',
            'aria' => 'Download PDF',
            'attrs' => ['onclick' => 'downloadHasilPDF(this)'],
        ],
        [
            'label' => 'Ulangi',
            'icon' => 'fa-play',
            'id' => 'dockQuickStartBtn',
            'style' => 'item',
            'aria' => 'Mulai Ujian Lagi',
        ],
    ],
])
@endsection


