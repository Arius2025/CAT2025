@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .audit-shell { padding-top: 1rem; padding-bottom: 2.5rem; }

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

    .audit-panel, .audit-card {
        border-radius: 22px;
        border: 1px solid var(--card-border);
        background: var(--card-bg);
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(16px);
    }

    .audit-preview-box {
        min-height: 260px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .86rem;
        white-space: pre-wrap;
    }

    .audit-mini-stat {
        border-radius: 18px;
        border: 1px solid var(--card-border);
        background: var(--card-bg);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .audit-mini-stat:hover {
        transform: translateY(-2px);
        border-color: rgba(197, 155, 39, 0.4);
    }

    .audit-table td, .audit-table th { vertical-align: top; }
    .audit-table thead th {
        background: var(--table-header);
        color: var(--text-secondary);
        border-color: var(--divider);
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .audit-table tbody td {
        color: var(--text-primary);
        border-color: var(--divider);
    }
    .audit-import-old, .audit-import-new { max-width: 340px; }
    .audit-tone-danger { color: var(--danger); }
    .audit-tone-success { color: var(--success); }
    .audit-note-error { color: var(--danger); font-size: .875rem; }

    @media (max-width: 767px) {
        .audit-shell { padding-top: 1rem; }
        .admin-page-hero, .audit-panel, .audit-card { border-radius: 18px; }
        .admin-page-hero { padding: 22px 18px !important; }
        .audit-panel, .audit-card { padding: 18px !important; }
    }
</style>
@endpush

<div class="container page-shell audit-shell">
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-shield-halved"></i> Audit & Quality Control BKN
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Audit Kualitas & Verifikasi Soal AI
                </h1>
                <div class="apple-subtitle" style="max-width: 760px;">
                    Pusat penelaahan kualitas butir soal: ekspor naskah ke Gemini/ChatGPT, salin instruksi prompt terstruktur, impor data revisi perbaikan, serta penyelesaian temuan kunci ambigu.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-self-start align-self-xl-center">
                <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2 text-decoration-none">
                    <i class="fas fa-wand-magic-sparkles me-1"></i> Generate Soal
                </a>
                <a href="{{ route('admin.question-quality.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2 text-decoration-none">
                    <i class="fas fa-check-double me-1"></i> QC Kualitas
                </a>
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2 text-decoration-none">
                    <i class="fas fa-database me-1"></i> Bank Soal
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="apple-glass apple-glass-card p-3 audit-mini-stat">
                <div class="apple-hint">Pending</div>
                <div class="fs-2 fw-800">{{ $qcPendingCount }}</div>
                <span class="apple-pill warning">Belum dicek</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="apple-glass apple-glass-card p-3 audit-mini-stat">
                <div class="apple-hint">Revised</div>
                <div class="fs-2 fw-800">{{ $revisedCount }}</div>
                <span class="apple-pill info">Sudah revisi</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="apple-glass apple-glass-card p-3 audit-mini-stat">
                <div class="apple-hint">Need Re-Audit</div>
                <div class="fs-2 fw-800">{{ $needsReauditCount }}</div>
                <span class="apple-pill danger">Masuk ulang</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="apple-glass apple-glass-card p-3 audit-mini-stat">
                <div class="apple-hint">Audited</div>
                <div class="fs-2 fw-800">{{ $auditedCount }}</div>
                <span class="apple-pill success">Sudah final</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="apple-glass apple-glass-card p-3 audit-mini-stat">
                <div class="apple-hint">Flagged</div>
                <div class="fs-2 fw-800">{{ $flaggedCount }}</div>
                <span class="apple-pill warning">QC aktif</span>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="apple-glass apple-glass-card p-3 audit-mini-stat">
                <div class="apple-hint">Export Terbaru</div>
                <div class="fs-2 fw-800">{{ method_exists($recentExports, 'total') ? $recentExports->total() : $recentExports->count() }}</div>
                <span class="apple-pill muted">Riwayat</span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <form method="POST" action="{{ route('admin.audit-soal.export.generate') }}" class="apple-glass apple-glass-card audit-panel p-3 p-md-4 h-100">
                @csrf
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="apple-kicker">A. Filter Questions</div>
                        <h5 class="m-0 fw-800">Generate Export Audit</h5>
                    </div>
                    <span class="apple-pill info">Step 1-5</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="apple-label mb-1">Sumber Soal</label>
                        <select name="source_scope" class="form-select apple-glass-input">
                            <option value="latest_generated">Latest Generated</option>
                            <option value="selected">Selected Questions</option>
                            <option value="unchecked">Belum Dicek QC</option>
                            <option value="flagged">Flagged Questions</option>
                            <option value="TIU" @selected($kategori === 'TIU')>TIU Only</option>
                            <option value="TWK" @selected($kategori === 'TWK')>TWK Only</option>
                            <option value="TKP" @selected($kategori === 'TKP')>TKP Only</option>
                            <option value="SKB">SKB</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Jumlah</label>
                        <select name="jumlah" class="form-select apple-glass-input">
                            @foreach([10,20,50] as $qty)
                                <option value="{{ $qty }}">{{ $qty }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Format</label>
                        <select name="export_format" class="form-select apple-glass-input">
                            <option value="json">Download JSON</option>
                            <option value="txt">Download TXT</option>
                            <option value="prompt">Copy Prompt + JSON</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="apple-label mb-1">Filter Audit</label>
                        <select name="audit_filter" class="form-select apple-glass-input">
                            <option value="active" @selected($auditFilter === 'active')>Pending + Revised + Need Re-Audit</option>
                            <option value="pending_only" @selected($auditFilter === 'pending_only')>Pending Only</option>
                            <option value="needs_reaudit" @selected($auditFilter === 'needs_reaudit')>Need Re-Audit</option>
                            <option value="revised" @selected($auditFilter === 'revised')>Revised</option>
                            <option value="already_audited" @selected($auditFilter === 'already_audited')>Already Audited</option>
                            <option value="all_questions" @selected($auditFilter === 'all_questions')>All Questions</option>
                        </select>
                        <div class="apple-hint mt-1">Default aman: soal yang sudah <strong>audited</strong> tidak ikut muncul lagi.</div>
                    </div>
                    <div class="col-12">
                        <label class="apple-label mb-1">Selected Question IDs</label>
                        <textarea name="selected_ids" rows="3" class="form-control apple-glass-input" placeholder="Contoh: 101, 102, 205"></textarea>
                        <div class="apple-hint mt-1">Dipakai jika sumber memilih <strong>Selected Questions</strong>.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Kategori</label>
                        <select name="kategori" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach(['TWK','TIU','TKP','FIGURAL','TEKNIS','MANAJERIAL','SOSIAL_KULTURAL','WAWANCARA','SKB_TEKNIS','SKB_REGULASI','SKB_STUDI_KASUS'] as $row)
                                <option value="{{ $row }}" @selected($kategori === $row)>{{ $row }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Mode</label>
                        <select name="exam_mode_id" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach($examModes as $mode)
                                <option value="{{ $mode->id }}" @selected((int) $examModeId === (int) $mode->id)>{{ $mode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Sub Kategori</label>
                        <select name="sub_category_id" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach($subCategories as $sub)
                                <option value="{{ $sub->id }}" @selected((int) $subCategoryId === (int) $sub->id)>{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="apple-label mb-1">Difficulty</label>
                        <select name="difficulty" class="form-select apple-glass-input">
                            <option value="">Semua</option>
                            @foreach(['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'] as $key => $label)
                                <option value="{{ $key }}" @selected($difficulty === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="apple-glass-btn apple-btn-primary">
                            <i class="fas fa-file-export me-2"></i>Generate Export
                        </button>
                        <a href="{{ route('admin.audit-soal.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-xl-7">
            <div class="apple-glass apple-glass-card audit-panel p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="apple-kicker">B. Preview Output</div>
                        <h5 class="m-0 fw-800">Prompt + JSON Siap Kirim</h5>
                    </div>
                    @if($exportPreview)
                        <span class="apple-pill success">{{ $exportPreview['total_questions'] ?? 0 }} soal</span>
                    @else
                        <span class="apple-pill muted">Belum dibuat</span>
                    @endif
                </div>

                @if($exportPreview)
                    <textarea id="auditExportPreview" class="form-control apple-glass-input audit-preview-box" readonly>{{ $exportPreview['prompt_text'] ?? '' }}</textarea>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="apple-glass-btn apple-btn-primary" onclick="copyAuditPreview()">
                            <i class="fas fa-copy me-2"></i>Copy to Clipboard
                        </button>
                        <a href="{{ route('admin.audit-soal.export.download', ['token' => $exportToken, 'format' => 'txt']) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                            <i class="fas fa-file-lines me-2"></i>Download TXT
                        </a>
                        <a href="{{ route('admin.audit-soal.export.download', ['token' => $exportToken, 'format' => 'json']) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                            <i class="fas fa-file-code me-2"></i>Download JSON
                        </a>
                    </div>
                    <div class="apple-hint mt-3">Kalau soal yang sama pernah diexport sebelumnya, sistem tetap mengizinkan export agar workflow audit tidak terhambat.</div>
                @else
                    <div class="apple-glass-soft apple-glass-card p-4 text-center apple-hint">
                        Pilih sumber soal, jumlah, lalu klik <strong>Generate Export</strong>. Preview prompt akan muncul di sini dan tombol download otomatis aktif.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="apple-glass apple-glass-card audit-panel p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="apple-kicker">C. Import Gemini Result</div>
                        <h5 class="m-0 fw-800">Paste JSON Hasil Audit</h5>
                    </div>
                    <span class="apple-pill warning">Safe Update</span>
                </div>
                <form method="POST" action="{{ route('admin.audit-soal.import.preview') }}">
                    @csrf
                    <textarea name="import_payload" rows="10" class="form-control apple-glass-input audit-preview-box" placeholder='[{"id":101,"status":"revisi","jawaban_baru":"D","pembahasan_baru":"...","soal_baru":"...","catatan":"..."}]'>{{ old('import_payload') }}</textarea>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="submit" class="apple-glass-btn apple-btn-primary">
                            <i class="fas fa-magnifying-glass-arrow-right me-2"></i>Preview Changes
                        </button>
                    </div>
                </form>

                @if(is_array($importPreview) && !empty($importPreview['rows']))
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h6 class="m-0 fw-800">Preview Perubahan Gemini</h6>
                            <span class="apple-pill info">{{ count($importPreview['rows']) }} baris</span>
                        </div>
                        <form method="POST" action="{{ route('admin.audit-soal.import.apply') }}">
                            @csrf
                            <input type="hidden" name="import_token" value="{{ $importToken }}">
                            <div class="table-responsive">
                        <table class="table audit-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Apply</th>
                                            <th>ID</th>
                                            <th>Status</th>
                                            <th>Jawaban</th>
                                            <th>Pembahasan</th>
                                            <th>Soal</th>
                                            <th>Catatan / Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($importPreview['rows'] as $row)
                                            <tr>
                                                <td>
                                                    @if(!empty($row['valid']))
                                                        <input type="checkbox" name="apply_rows[{{ $row['id'] }}]" value="1" checked>
                                                    @else
                                                        <span class="apple-pill danger">Error</span>
                                                    @endif
                                                </td>
                                                <td>#{{ $row['id'] }}</td>
                                                <td><span class="apple-pill {{ !empty($row['valid']) ? 'success' : 'danger' }}">{{ strtoupper($row['status'] ?? '-') }}</span></td>
                                                <td class="audit-import-old">
                                                    <div><strong>Lama:</strong> {{ $row['answer_old'] ?: '-' }}</div>
                                                    <div><strong>Baru:</strong> {{ $row['answer_new'] ?: '-' }}</div>
                                                </td>
                                                <td class="audit-import-old">
                                                    <div class="apple-hint">Lama</div>
                                                    <div>{{ \Illuminate\Support\Str::limit(strip_tags((string) ($row['explanation_old'] ?? '')), 120) ?: '-' }}</div>
                                                    <div class="apple-hint mt-2">Baru</div>
                                                    <div>{{ \Illuminate\Support\Str::limit(strip_tags((string) ($row['explanation_new'] ?? '')), 120) ?: '-' }}</div>
                                                </td>
                                                <td class="audit-import-new">
                                                    <div class="apple-hint">Lama</div>
                                                    <div>{{ \Illuminate\Support\Str::limit(strip_tags((string) ($row['question_old'] ?? '')), 120) ?: '-' }}</div>
                                                    <div class="apple-hint mt-2">Baru</div>
                                                    <div>{{ \Illuminate\Support\Str::limit(strip_tags((string) ($row['question_new'] ?? '')), 120) ?: '-' }}</div>
                                                </td>
                                                <td>
                                                    @if(!empty($row['errors']))
                                                        <div class="audit-note-error">{{ implode(' ', $row['errors']) }}</div>
                                                    @else
                                                        <div class="apple-hint">{{ $row['notes'] ?: 'Siap diterapkan.' }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="submit" class="apple-glass-btn apple-btn-primary">
                                    <i class="fas fa-check-double me-2"></i>Apply Selected
                                </button>
                                <a href="{{ route('admin.audit-soal.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Cancel</a>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4">
            <div class="apple-glass apple-glass-card audit-panel p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="apple-kicker">D. Export History</div>
                        <h5 class="m-0 fw-800">Riwayat Export Terbaru</h5>
                    </div>
                    <span class="apple-pill muted">{{ method_exists($recentExports, 'total') ? $recentExports->total() : $recentExports->count() }} item</span>
                </div>
                @forelse($recentExports as $export)
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3">
                        <div class="d-flex justify-content-between gap-2">
                            <div class="fw-700">{{ strtoupper((string) $export->source_scope) }}</div>
                            <span class="apple-pill info">{{ $export->total_questions }} soal</span>
                        </div>
                        <div class="apple-hint mt-2">
                            Format {{ strtoupper((string) $export->format) }} - {{ optional($export->created_at)->diffForHumans() }}
                        </div>
                        @if(!empty($export->question_ids))
                            <div class="apple-hint mt-1">ID: {{ implode(', ', array_slice($export->question_ids, 0, 6)) }}@if(count($export->question_ids) > 6), ...@endif</div>
                        @endif
                    </div>
                @empty
                    <div class="apple-hint">Belum ada riwayat export audit yang tersimpan.</div>
                @endforelse
                @if(method_exists($recentExports, 'links'))
                    <div class="mt-3">
                        {{ $recentExports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="apple-glass apple-glass-card audit-card p-3 p-md-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="apple-kicker">E. Existing Findings</div>
                <h5 class="m-0 fw-800">Temuan Audit Soal Lama</h5>
            </div>
            <span class="apple-pill danger">{{ $audits->total() }} temuan</span>
        </div>

        <form method="GET" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="apple-label mb-1">Kategori</label>
                    <select name="kategori" class="form-select apple-glass-input">
                        <option value="">Semua</option>
                        @foreach(['TWK','TIU','TKP'] as $row)
                            <option value="{{ $row }}" @selected($kategori === $row)>{{ $row }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="apple-label mb-1">Mode</label>
                    <select name="exam_mode_id" class="form-select apple-glass-input">
                        <option value="">Semua Mode</option>
                        @foreach($examModes as $mode)
                            <option value="{{ $mode->id }}" @selected((int) $examModeId === (int) $mode->id)>{{ $mode->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="apple-label mb-1">Sub Kategori</label>
                    <select name="sub_category_id" class="form-select apple-glass-input">
                        <option value="">Semua Sub Kategori</option>
                        @foreach($subCategories as $sub)
                            <option value="{{ $sub->id }}" @selected((int) $subCategoryId === (int) $sub->id)>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="apple-label mb-1">Difficulty</label>
                    <select name="difficulty" class="form-select apple-glass-input">
                        <option value="">Semua</option>
                        @foreach(['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'] as $key => $label)
                            <option value="{{ $key }}" @selected($difficulty === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="apple-glass-btn apple-btn-primary">Cari</button>
                    <a href="{{ route('admin.audit-soal.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Reset</a>
                </div>
            </div>
        </form>

        @forelse($audits as $audit)
            <div class="apple-glass-soft apple-glass-card p-4 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="apple-pill {{ $audit->status === 'mismatch' ? 'danger' : 'warning' }}">{{ $audit->status === 'mismatch' ? 'KUNCI BERBEDA' : 'PEMBAHASAN KURANG' }}</span>
                        <span class="apple-pill muted">{{ $audit->soal->kategori ?? 'Unknown' }}</span>
                        @if(!empty($audit->soal->difficulty))
                            <span class="apple-pill muted">{{ strtoupper($audit->soal->difficulty) }}</span>
                        @endif
                    </div>
                    <div class="apple-hint">{{ optional($audit->created_at)->diffForHumans() }}</div>
                </div>
                <div class="mb-3">{!! nl2br(e($audit->soal->teks_soal ?? 'Soal asli sudah dihapus.')) !!}</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="apple-glass apple-glass-card p-3 h-100">
                            <div class="fw-700 mb-2 audit-tone-danger">Data DB</div>
                            <div class="mb-2"><strong>{{ $audit->db_key ?? '-' }}</strong></div>
                            @if(!$audit->pembahasan_ok)
                                <div class="apple-hint">{!! nl2br(e($audit->soal->pembahasan ?? '-')) !!}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="apple-glass apple-glass-card p-3 h-100">
                            <div class="fw-700 mb-2 audit-tone-success">Saran AI</div>
                            <div class="mb-2"><strong>{{ $audit->ai_key ?? '-' }}</strong></div>
                            @if($audit->ai_pembahasan)
                                <div class="apple-hint">{!! nl2br(e($audit->ai_pembahasan)) !!}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="{{ route('admin.soal.edit', $audit->soal_id) }}" class="apple-glass-btn apple-btn-outline">Edit Soal</a>
                    <form action="{{ route('admin.audit-soal.mark-valid', $audit->id) }}" method="POST">@csrf<button type="submit" class="apple-glass-btn apple-btn-outline" onclick="return confirm('Abaikan saran AI ini?')">Abaikan</button></form>
                    <form action="{{ route('admin.audit-soal.apply', $audit->id) }}" method="POST">@csrf<button type="submit" class="apple-glass-btn apple-btn-primary" onclick="return confirm('Terapkan saran AI ini?')">Terapkan Saran AI</button></form>
                    @if($audit->needs_revision)<form action="{{ route('admin.audit-soal.apply-revision', $audit->id) }}" method="POST">@csrf<button type="submit" class="apple-glass-btn apple-btn-primary" onclick="return confirm('Terapkan revisi penuh ini?')">Terapkan Revisi Penuh</button></form>@endif
                </div>
            </div>
        @empty
            <div class="apple-glass apple-glass-card p-5 text-center apple-hint">Belum ada audit lama yang cocok dengan filter ini.</div>
        @endforelse

        <div class="mt-4 d-flex justify-content-center">{{ $audits->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function copyAuditPreview() {
        const textarea = document.getElementById('auditExportPreview');
        if (!textarea) return;
        try {
            await navigator.clipboard.writeText(textarea.value);
            alert('Prompt audit berhasil disalin.');
        } catch (e) {
            textarea.select();
            document.execCommand('copy');
            alert('Prompt audit berhasil disalin.');
        }
    }
</script>
@endpush

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Bank',
            'icon' => 'fa-database',
            'href' => route('admin.soal-bank.index'),
        ],
        [
            'label' => 'Audit',
            'icon' => 'fa-shield-halved',
            'href' => route('admin.audit-soal.index'),
            'style' => 'cta',
            'aria' => 'Audit Soal AI',
        ],
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
    ],
])
@endsection
