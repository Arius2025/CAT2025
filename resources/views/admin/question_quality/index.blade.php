@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .admin-shell { padding-top: 1rem; padding-bottom: 2.5rem; }

    /* Luxury Admin Hero */
    .admin-page-hero {
        border-radius: 26px;
        border: 1px solid var(--border-glass);
        background:
            radial-gradient(circle at 10% 10%, rgba(197, 155, 39, 0.16), transparent 45%),
            radial-gradient(circle at 90% 10%, rgba(59, 130, 246, 0.18), transparent 45%),
            linear-gradient(135deg, rgba(15, 23, 42, 0.90), rgba(11, 37, 69, 0.94));
        box-shadow: var(--shadow-premium);
        padding: 32px 28px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    [data-bs-theme="light"] .admin-page-hero {
        border-color: rgba(197, 155, 39, 0.25);
        background:
            radial-gradient(circle at 10% 10%, rgba(197, 155, 39, 0.14), transparent 45%),
            radial-gradient(circle at 90% 10%, rgba(19, 62, 135, 0.08), transparent 45%),
            linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
        box-shadow: 0 16px 40px rgba(11, 37, 69, 0.06);
    }

    .admin-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(197, 155, 39, 0.14);
        border: 1px solid rgba(197, 155, 39, 0.32);
        color: #d97706;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    [data-bs-theme="dark"] .admin-hero-kicker { color: #fbbf24; }

    .qc-stat-card {
        border-radius: 20px;
        border: 1px solid var(--card-border);
        background: var(--card-bg);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .qc-stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(197, 155, 39, 0.4);
    }
</style>
@endpush

<div class="container page-shell py-4">
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-microscope"></i> AI Quality Control & Audit
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Audit Kualitas & Integritas Soal
                </h1>
                <div class="apple-subtitle" style="max-width: 720px;">
                    Pusat penjaminan mutu bank soal mandiri: verifikasi akurasi kunci, deteksi ambiguitas stimulus, audit massal otomatis, dan ekspor data validasi ke Gemini AI.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-self-start align-self-lg-center">
                <a href="{{ route('admin.question-quality.export-json', array_filter([
                    'kategori' => $kategori ?: null,
                    'exam_mode_id' => $examModeId ?: null,
                    'sub_category_id' => $subCategoryId ?: null,
                    'limit' => 20,
                ])) }}" class="apple-glass-btn apple-btn-gold px-3 py-2 text-decoration-none">
                    <i class="fas fa-file-arrow-down me-1"></i> JSON Gemini
                </a>
                <a href="{{ route('admin.audit-soal.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2 text-decoration-none">
                    <i class="fas fa-shield-halved me-1"></i> Panel Audit
                </a>
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2 text-decoration-none">
                    <i class="fas fa-database me-1"></i> Bank Soal
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'Belum Dicek', 'value' => $stats['unchecked'] ?? 0, 'class' => 'info'],
            ['label' => 'Approved', 'value' => $stats['approved'] ?? 0, 'class' => 'success'],
            ['label' => 'Revised', 'value' => $stats['revised'] ?? 0, 'class' => 'warning'],
            ['label' => 'Flagged', 'value' => $stats['flagged'] ?? 0, 'class' => 'danger'],
            ['label' => 'Rejected', 'value' => $stats['rejected'] ?? 0, 'class' => 'muted'],
        ] as $card)
            <div class="col-6 col-lg">
                <div class="apple-glass apple-glass-card p-3 h-100">
                    <div class="apple-hint mb-2">{{ strtoupper($card['label']) }}</div>
                    <div class="fs-2 fw-800" style="color: var(--text-primary);">{{ $card['value'] }}</div>
                    <span class="apple-pill {{ $card['class'] }}">{{ $card['label'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <form method="GET" class="apple-glass apple-glass-card p-3 p-md-4 h-100">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Kategori</label>
                        <select name="kategori" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach(['TWK','TIU','TKP','FIGURAL','TEKNIS','MANAJERIAL','SOSIAL_KULTURAL','WAWANCARA','SKB_TEKNIS','SKB_REGULASI','SKB_STUDI_KASUS'] as $option)
                                <option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Sumber</label>
                        <select name="source" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            <option value="generated" @selected($source === 'generated')>Generated Review</option>
                            <option value="existing_tiu" @selected($source === 'existing_tiu')>Existing TIU Audit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Status</label>
                        <select name="status" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach(['valid','revised','flagged','rejected','wrong','suspicious','reviewed'] as $option)
                                <option value="{{ $option }}" @selected($status === $option)>{{ strtoupper($option) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="apple-label mb-1">Mode</label>
                        <select name="exam_mode_id" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach($examModes as $mode)
                                <option value="{{ $mode->id }}" @selected($examModeId === (int) $mode->id)>{{ $mode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="apple-label mb-1">Sub Kategori</label>
                        <select name="sub_category_id" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach($subCategories as $sub)
                                <option value="{{ $sub->id }}" @selected($subCategoryId === (int) $sub->id)>{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="apple-glass-btn apple-btn-primary">Terapkan Filter</button>
                        <a href="{{ route('admin.question-quality.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <form action="{{ route('admin.question-quality.audit-tiu-batch') }}" method="POST" class="apple-glass apple-glass-card p-3 p-md-4 h-100">
                @csrf
                <div class="apple-kicker mb-2">Batch Audit Soal</div>
                <div class="apple-subtitle mb-3">Utamakan semua soal yang belum pernah dicek. Soal yang sudah lolos QC tidak diikutkan lagi di batch ini.</div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="apple-label mb-1">Kategori</label>
                        <select name="kategori" class="form-select apple-glass-input">
                            <option value="">Semua Kategori</option>
                            @foreach(['TWK','TIU','TKP','FIGURAL','TEKNIS','MANAJERIAL','SOSIAL_KULTURAL','WAWANCARA','SKB_TEKNIS','SKB_REGULASI','SKB_STUDI_KASUS'] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="apple-label mb-1">Mode</label>
                        <select name="exam_mode_id" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach($examModes as $mode)
                                <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="apple-label mb-1">Limit</label>
                        <input type="number" name="limit" class="form-control apple-glass-input" value="100" min="1" max="500">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="apple-glass-btn apple-btn-primary w-100 justify-content-center">
                            <i class="fas fa-shield-virus me-2"></i>Mulai Audit Massal
                        </button>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="audit_all_unchecked" value="1" class="apple-glass-btn apple-btn-outline w-100 justify-content-center">
                            <i class="fas fa-layer-group me-2"></i>Audit Semua Belum Dicek
                        </button>
                    </div>
                    <div class="col-12">
                        <a href="{{ route('admin.question-quality.export-json', array_filter([
                            'kategori' => $kategori ?: null,
                            'exam_mode_id' => $examModeId ?: null,
                            'sub_category_id' => $subCategoryId ?: null,
                            'limit' => 50,
                        ])) }}" class="apple-glass-btn apple-btn-outline w-100 justify-content-center text-decoration-none">
                            <i class="fas fa-download me-2"></i>Download JSON untuk Gemini
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="apple-glass apple-glass-card p-3 p-md-4 mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <div class="apple-kicker">Antrian Utama</div>
                <h5 class="m-0 fw-800">Soal yang belum pernah dicek kualitasnya</h5>
            </div>
            <div class="apple-pill info">{{ $pendingQuestions->total() }} soal</div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori</th>
                        <th>Mode / Formasi</th>
                        <th>Soal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingQuestions as $question)
                        <tr>
                            <td>#{{ $question->id }}</td>
                            <td>{{ $question->kategori }}</td>
                            <td>
                                <div>{{ $question->examMode?->name ?? '-' }}</div>
                                <div class="apple-hint">{{ $question->subCategory?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-700" style="color: var(--text-primary);">{{ \Illuminate\Support\Str::limit(strip_tags($question->teks_soal), 140) }}</div>
                                <div class="apple-hint mt-1">{{ $question->sub_topik ?? 'Tanpa sub topik' }}</div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <a href="{{ route('admin.soal.edit', $question->id) }}" class="apple-glass-btn apple-btn-outline px-3 text-decoration-none">Edit</a>
                                    <form action="{{ route('admin.question-quality.audit-one', $question->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="apple-glass-btn apple-btn-primary px-3">Cek Sekarang</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 apple-hint">Tidak ada soal belum dicek untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $pendingQuestions->links() }}
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="apple-glass apple-glass-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 fw-800">Generated Question Review</h5>
                    <span class="apple-pill info">{{ $generatedAudits->count() }} terbaru</span>
                </div>
                @forelse($generatedAudits as $audit)
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <div class="fw-700">#{{ $audit->question_id ?? '-' }} · {{ $audit->question->kategori ?? '-' }}</div>
                            <span class="apple-pill {{ in_array($audit->status, ['valid','reviewed'], true) ? 'success' : (in_array($audit->status, ['revised','suspicious'], true) ? 'warning' : 'danger') }}">{{ strtoupper($audit->status) }}</span>
                        </div>
                        <div class="apple-hint">{{ $audit->notes }}</div>
                    </div>
                @empty
                    <div class="apple-hint">Belum ada hasil generate yang masuk antrean QC.</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="apple-glass apple-glass-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 fw-800">Existing TIU Audit</h5>
                    <span class="apple-pill warning">{{ $tiuAudits->count() }} terbaru</span>
                </div>
                @forelse($tiuAudits as $audit)
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <div class="fw-700">#{{ $audit->question_id ?? '-' }} · {{ $audit->question->sub_topik ?? 'TIU' }}</div>
                            <span class="apple-pill {{ $audit->status === 'valid' ? 'success' : 'danger' }}">{{ strtoupper($audit->status) }}</span>
                        </div>
                        <div class="apple-hint">{{ $audit->notes }}</div>
                    </div>
                @empty
                    <div class="apple-hint">Belum ada audit TIU yang tersimpan.</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="apple-glass apple-glass-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 fw-800">Dihapus Otomatis</h5>
                    <span class="apple-pill danger">{{ $autoDeletedAudits->count() }} terbaru</span>
                </div>
                @forelse($autoDeletedAudits as $audit)
                    @php($snapshot = is_array($audit->payload['question_snapshot'] ?? null) ? $audit->payload['question_snapshot'] : [])
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <div class="fw-700">#{{ $audit->question_id ?? ($snapshot['id'] ?? '-') }} · {{ $snapshot['kategori'] ?? 'UNKNOWN' }}</div>
                            <span class="apple-pill danger">AUTO DELETE</span>
                        </div>
                        <div class="apple-hint mb-2">{{ $audit->notes }}</div>
                        @if(!empty($snapshot['teks_soal']))
                            <div class="small" style="color: var(--text-primary);">
                                {{ \Illuminate\Support\Str::limit(strip_tags((string) $snapshot['teks_soal']), 180) }}
                            </div>
                        @endif
                        <div class="apple-hint mt-2">{{ optional($audit->created_at)->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="apple-hint">Belum ada soal yang dihapus otomatis oleh aturan QC ini.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="apple-glass apple-glass-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 fw-800">Flagged Questions</h5>
                    <span class="apple-pill danger">{{ $flaggedAudits->count() }} prioritas</span>
                </div>
                @forelse($flaggedAudits as $audit)
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3">
                        <div class="fw-700 mb-2">#{{ $audit->question_id ?? '-' }} · {{ $audit->issue_type ?? 'review' }}</div>
                        <div class="apple-hint mb-3">{{ $audit->notes }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($audit->question_id)
                                <form action="{{ route('admin.question-quality.audit-one', $audit->question_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="apple-glass-btn apple-btn-outline px-3">Audit Ulang</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.question-quality.mark-reviewed', $audit->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="apple-glass-btn apple-btn-outline px-3">Tandai Reviewed</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="apple-hint">Belum ada soal yang ditandai prioritas review.</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="apple-glass apple-glass-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 fw-800">Auto Correction Queue</h5>
                    <span class="apple-pill info">{{ $autoQueue->count() }} siap tindakan</span>
                </div>
                @forelse($autoQueue as $audit)
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3">
                        <div class="fw-700 mb-2">#{{ $audit->question_id ?? '-' }} · {{ strtoupper($audit->status) }}</div>
                        <div class="apple-hint mb-3">
                            Confidence {{ number_format((float) $audit->confidence, 0) }}%
                            @if($audit->suggested_answer)
                                · Saran kunci {{ $audit->suggested_answer }}
                            @endif
                        </div>
                        <form action="{{ route('admin.question-quality.apply-fix', $audit->id) }}" method="POST" class="d-flex flex-wrap gap-2">
                            @csrf
                            <button type="submit" class="apple-glass-btn apple-btn-primary px-3">Apply Suggested Fix</button>
                            <a href="{{ $audit->question_id ? route('admin.soal.edit', $audit->question_id) : route('admin.question-quality.index') }}" class="apple-glass-btn apple-btn-outline px-3 text-decoration-none">Buka Soal</a>
                        </form>
                    </div>
                @empty
                    <div class="apple-hint">Belum ada antrean koreksi yang bisa diterapkan.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="apple-glass apple-glass-card p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <div class="apple-kicker">Temuan Aktif</div>
                <h5 class="m-0 fw-800">Hanya soal yang masih bermasalah</h5>
            </div>
            <div class="apple-pill total">{{ $audits->total() }} baris</div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sumber</th>
                        <th>Status</th>
                        <th>Confidence</th>
                        <th>Issue</th>
                        <th>Saran</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td>#{{ $audit->question_id ?? '-' }}</td>
                            <td>{{ strtoupper($audit->source) }}</td>
                            <td><span class="apple-pill {{ in_array($audit->status, ['valid','reviewed'], true) ? 'success' : (in_array($audit->status, ['revised','suspicious'], true) ? 'warning' : 'danger') }}">{{ strtoupper($audit->status) }}</span></td>
                            <td>{{ number_format((float) $audit->confidence, 0) }}%</td>
                            <td>{{ $audit->issue_type ?? '-' }}</td>
                            <td>
                                @if($audit->suggested_answer)
                                    Kunci {{ $audit->suggested_answer }}
                                @endif
                                @if($audit->suggested_explanation)
                                    <div class="apple-hint mt-1">Pembahasan revisi tersedia</div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    @if($audit->question_id)
                                        <a href="{{ route('admin.soal.edit', $audit->question_id) }}" class="apple-glass-btn apple-btn-outline px-3 text-decoration-none">Edit</a>
                                    @endif
                                    <form action="{{ route('admin.question-quality.apply-fix', $audit->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="apple-glass-btn apple-btn-primary px-3">Apply</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 apple-hint">Belum ada temuan aktif untuk filter ini. Soal yang sudah benar tidak ditampilkan di panel bawah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $audits->links() }}
        </div>
    </div>
</div>
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
        [
            'label' => 'QC',
            'icon' => 'fa-shield-halved',
            'href' => route('admin.question-quality.index'),
            'style' => 'cta',
            'aria' => 'Audit Kualitas Soal',
        ],
        [
            'label' => 'Audit',
            'icon' => 'fa-microscope',
            'href' => route('admin.audit-soal.index'),
        ],
    ],
])
@endsection
