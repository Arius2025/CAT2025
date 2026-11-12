@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .admin-shell { padding-top: 1rem; padding-bottom: 2rem; }

    /* Luxury Admin Hero */
    .admin-page-hero {
        border-radius: 26px;
        border: 1px solid var(--border-glass);
        background:
            radial-gradient(circle at 10% 10%, rgba(197, 155, 39, 0.16), transparent 45%),
            radial-gradient(circle at 90% 10%, rgba(59, 130, 246, 0.18), transparent 45%),
            linear-gradient(135deg, rgba(15, 23, 42, 0.90), rgba(11, 37, 69, 0.94));
        box-shadow: var(--shadow-premium);
        padding: 36px 32px;
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
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(197, 155, 39, 0.14);
        border: 1px solid rgba(197, 155, 39, 0.32);
        color: #d97706;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    [data-bs-theme="dark"] .admin-hero-kicker { color: #fbbf24; }

    .admin-stat-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .admin-filter-card {
        border-radius: 22px;
        border: 1px solid var(--card-border);
        background: var(--card-bg);
        padding: 24px;
        margin-bottom: 24px;
    }

    [data-bs-theme="light"] .admin-filter-card {
        border-color: rgba(11, 37, 69, 0.08);
        box-shadow: 0 8px 24px rgba(11, 37, 69, 0.03);
    }

    .admin-table-card {
        border-radius: 22px;
        border: 1px solid var(--card-border);
        background: var(--card-bg);
        overflow: hidden;
    }

    .admin-table-card thead th {
        background: var(--table-header);
        color: var(--text-secondary);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 14px 16px;
        border-bottom: 1px solid var(--divider);
    }

    .admin-table-card tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid var(--divider);
        color: var(--text-primary);
    }

    .admin-table-card tbody tr:hover td {
        background: var(--table-row-hover);
    }

    /* Floating Bulk Action Bar */
    .admin-floating-bulk {
        position: fixed;
        bottom: calc(20px + env(safe-area-inset-bottom));
        left: 50%;
        transform: translateX(-50%) translateY(120px);
        width: min(92%, 780px);
        border-radius: 20px;
        padding: 12px 20px;
        background: rgba(11, 37, 69, 0.95);
        border: 1px solid rgba(197, 155, 39, 0.4);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        color: #ffffff;
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: transform 0.28s cubic-bezier(0.2, 0.9, 0.2, 1);
        backdrop-filter: blur(16px);
    }

    .admin-floating-bulk.is-visible {
        transform: translateX(-50%) translateY(0);
    }

    @media (max-width: 991px) {
        .admin-page-hero { padding: 24px 20px; border-radius: 20px; }
        .admin-filter-card { padding: 18px; border-radius: 18px; }
        .admin-desktop-table { display: none; }
        .admin-mobile-list { display: grid; gap: 14px; }
    }

    @media (min-width: 992px) {
        .admin-mobile-list { display: none; }
    }
</style>
@endpush

<div class="container page-shell admin-shell">
    <!-- Header Hero -->
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-database"></i> Bank Soal CAT ASN
                </div>
                <h1 class="apple-title mb-2 fs-2 fw-800" style="color: var(--text-primary);">Manajemen Bank Soal</h1>
                <div class="apple-subtitle" style="max-width: 650px;">
                    Kelola ribuan butir soal terstandar BKN: filter multi-kategori, pencarian cepat, ekspor-impor, dan modifikasi massal.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.soal.create') }}" class="apple-glass-btn apple-btn-primary px-4 py-2">
                    <i class="fas fa-plus me-2"></i>Tambah Soal
                </a>
                <button type="button" class="apple-glass-btn apple-btn-outline px-3 py-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-file-export me-2"></i>Export Backup
                </button>
                <button type="button" class="apple-glass-btn apple-btn-outline px-3 py-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-import me-2"></i>Import Soal
                </button>
                <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-wand-magic-sparkles me-2"></i>Generate AI
                </a>
                <a href="{{ route('admin.audit-soal.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-shield-halved me-2"></i>Audit Soal
                </a>
            </div>
        </div>

        <div class="admin-stat-pills">
            <span class="apple-pill total">Total {{ number_format($modeStats['total'] ?? 0) }} Soal</span>
            <span class="apple-pill success" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-weight: 700;"><i class="fas fa-certificate me-1"></i>Standar 2027: {{ number_format($modeStats['standar_2027'] ?? 0) }} (Aktif Ujian)</span>
            <span class="apple-pill muted" style="opacity: 0.85;"><i class="fas fa-box-archive me-1"></i>Pra-2027: {{ number_format($modeStats['pra_2027'] ?? 0) }} (Arsip)</span>
            <span class="apple-pill mode-skd">SKD: {{ number_format($modeStats['skd'] ?? 0) }}</span>
            <span class="apple-pill mode-skb">SKB: {{ number_format($modeStats['skb'] ?? 0) }}</span>
            <span class="apple-pill mode-pppk">PPPK: {{ number_format($modeStats['pppk'] ?? 0) }}</span>
        </div>
    </div>

    @if(session('import_stats'))
        @php $stats = session('import_stats'); @endphp
        <div class="alert alert-dismissible fade show p-4 mb-4" style="border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.35); background: rgba(16, 185, 129, 0.08);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Laporan Pemrosesan Impor Bank Soal</h5>
                        <small class="text-secondary">Pemeriksaan anti-duplikasi telah dijalankan secara otomatis.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="apple-pill total">Total Berkas: {{ number_format($stats['total'] ?? 0) }}</span>
                <span class="apple-pill success" style="background: rgba(16,185,129,0.2); color: #10b981; font-weight: 700;">
                    <i class="fas fa-plus-circle me-1"></i>Berhasil Diimpor: {{ number_format($stats['imported'] ?? 0) }}
                </span>
                <span class="apple-pill warning" style="background: rgba(245, 158, 11, 0.18); color: #d97706; font-weight: 700;">
                    <i class="fas fa-filter-circle-xmark me-1"></i>Duplikat Dilewati: {{ number_format($stats['skipped_duplicates'] ?? 0) }}
                </span>
                @if(($stats['errors'] ?? 0) > 0)
                    <span class="apple-pill" style="background: rgba(239, 68, 68, 0.18); color: #ef4444; font-weight: 700;">
                        <i class="fas fa-triangle-exclamation me-1"></i>Gagal / Format Salah: {{ number_format($stats['errors'] ?? 0) }}
                    </span>
                @endif
            </div>
            @if(!empty($stats['error_messages']))
                <div class="mt-2 small text-danger">
                    @foreach($stats['error_messages'] as $errMsg)
                        <div><i class="fas fa-circle-exclamation me-1"></i>{{ $errMsg }}</div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Filter & Search Bar Terpadu -->
    <form method="GET" class="admin-filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="apple-label mb-1">Pencarian Cepat</label>
                <div class="position-relative">
                    <input type="text" name="q" value="{{ $search }}" class="form-control apple-glass-input ps-4" placeholder="Cari ID, kata kunci, pembahasan...">
                </div>
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="apple-label mb-1">Kategori</label>
                <select name="kategori" class="form-select apple-glass-input">
                    <option value="">Semua Kategori</option>
                    @foreach(($categoryOptions ?? collect()) as $row)
                        @php
                            $val = strtoupper(trim((string) ($row->value ?? '')));
                            $lbl = (string) (($row->label ?? '') ?: $val);
                        @endphp
                        <option value="{{ $val }}" @selected($kategori === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="apple-label mb-1">Mode Ujian</label>
                <select name="exam_mode_id" class="form-select apple-glass-input">
                    <option value="">Semua Mode</option>
                    @foreach($examModes as $mode)
                        <option value="{{ $mode->id }}" @selected((int) $examModeId === (int) $mode->id)>{{ $mode->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="apple-label mb-1">Sub Kategori</label>
                <select name="sub_category_id" class="form-select apple-glass-input">
                    <option value="">Semua Sub Kategori</option>
                    @foreach($subCategories as $sub)
                        <option value="{{ $sub->id }}" @selected((int) $subCategoryId === (int) $sub->id)>{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-4">
                <label class="apple-label mb-1">Tingkat</label>
                <select name="difficulty" class="form-select apple-glass-input">
                    <option value="">Semua</option>
                    <option value="easy" @selected($difficulty === 'easy')>Mudah</option>
                    <option value="medium" @selected($difficulty === 'medium')>Sedang</option>
                    <option value="hard" @selected($difficulty === 'hard')>Sulit</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="apple-label mb-1">Standar Soal</label>
                <select name="standar_tahun" class="form-select apple-glass-input">
                    <option value="">Semua Standar</option>
                    <option value="2027" @selected(($standarTahun ?? '') === '2027')>⭐ Standar 2027 (Aktif)</option>
                    <option value="pra-2027" @selected(($standarTahun ?? '') === 'pra-2027')>Pra-2027 (Nonaktif Ujian)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button type="submit" class="apple-glass-btn apple-btn-primary flex-fill">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-outline" title="Reset filter">
                    <i class="fas fa-rotate"></i>
                </a>
            </div>
        </div>
    </form>

    <!-- Table Section with Bulk Action Checkboxes -->
    <form method="POST" action="{{ route('admin.soal.bulk-update') }}" id="mainSoalForm">
        @csrf
        @method('PATCH')

        <!-- Desktop View Table -->
        <div class="admin-table-card admin-desktop-table mb-4">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">
                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox" title="Pilih Semua">
                            </th>
                            <th style="width: 80px;">ID</th>
                            <th>Isi Pertanyaan & Topik</th>
                            <th style="width: 140px;">Kategori</th>
                            <th style="width: 130px;">Mode</th>
                            <th style="width: 80px; text-align: center;">Kunci</th>
                            <th style="width: 100px;">Difficulty</th>
                            <th style="width: 100px; text-align: end;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($soals as $soal)
                            @php
                                $katPill = match(strtoupper($soal->kategori)) {
                                    'TWK' => 'info',
                                    'TIU', 'FIGURAL' => 'warning',
                                    'TKP' => 'success',
                                    default => 'muted'
                                };
                            @endphp
                            <tr>
                                <td style="text-align: center;">
                                    <input class="form-check-input item-checkbox" type="checkbox" name="ids[]" value="{{ $soal->id }}">
                                </td>
                                <td>
                                    <span class="font-monospace fw-bold" style="color: var(--text-secondary);">#{{ $soal->id }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold" style="color: var(--text-primary); max-width: 460px; line-height: 1.4;">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($soal->teks_soal), 130) }}
                                    </div>
                                    @if($soal->sub_topik)
                                        <div class="small apple-hint mt-1">
                                            <i class="fas fa-tag me-1 opacity-50"></i>{{ $soal->sub_topik }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="apple-pill {{ $katPill }}">{{ $soal->kategori }}</span>
                                    <div class="mt-1">
                                        @if(($soal->standar_tahun ?? 'pra-2027') === '2027')
                                            <span class="apple-pill success small" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-size: 0.7rem; font-weight: 700;" title="Standar Resmi CASN 2027 - Aktif untuk Ujian">
                                                <i class="fas fa-check-circle me-1"></i>2027
                                            </span>
                                        @else
                                            <span class="apple-pill muted small" style="font-size: 0.7rem; opacity: 0.75;" title="Belum Standar 2027 - Tidak otomatis digunakan untuk ujian">
                                                <i class="fas fa-clock-rotate-left me-1"></i>Pra-2027
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="small fw-bold" style="color: var(--text-secondary);">
                                        {{ $soal->examMode->name ?? '-' }}
                                    </span>
                                    @if($soal->subCategory)
                                        <div class="apple-hint small">{{ $soal->subCategory->name }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge rounded-pill px-2 py-1" style="background: rgba(197, 155, 39, 0.18); color: #d97706; font-weight: 800; border: 1px solid rgba(197, 155, 39, 0.3);">
                                        {{ $soal->jawaban_benar ?: '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="apple-pill muted small">{{ ucfirst($soal->difficulty ?: 'medium') }}</span>
                                </td>
                                <td style="text-align: end;">
                                    <a href="{{ route('admin.soal.edit', $soal->id) }}" class="apple-glass-btn apple-btn-outline px-3 py-1 small" title="Edit Soal">
                                        <i class="fas fa-pencil me-1"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 opacity-50">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    Belum ada butir soal yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card List View (< 992px) -->
        <div class="admin-mobile-list mb-4">
            @forelse($soals as $soal)
                <div class="apple-glass-card p-3" style="border-left: 4px solid var(--accent) !important;">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input item-checkbox" type="checkbox" name="ids[]" value="{{ $soal->id }}">
                            <span class="font-monospace fw-bold small text-muted">#{{ $soal->id }}</span>
                            <span class="apple-pill info small">{{ $soal->kategori }}</span>
                        </div>
                        <span class="badge rounded-pill px-2 py-1" style="background: rgba(197, 155, 39, 0.18); color: #d97706; font-weight: 800;">
                            Kunci: {{ $soal->jawaban_benar ?: '-' }}
                        </span>
                    </div>

                    <div class="fw-bold mb-2" style="color: var(--text-primary); font-size: 0.95rem; line-height: 1.4;">
                        {{ \Illuminate\Support\Str::limit(strip_tags($soal->teks_soal), 140) }}
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between pt-2 border-top" style="border-color: var(--divider) !important;">
                        <span class="small text-muted">
                            {{ $soal->examMode->name ?? 'Default' }}
                            @if($soal->sub_topik) • {{ $soal->sub_topik }} @endif
                        </span>
                        <a href="{{ route('admin.soal.edit', $soal->id) }}" class="apple-glass-btn apple-btn-outline px-3 py-1 small">
                            <i class="fas fa-pencil me-1"></i> Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 opacity-50">Tidak ada soal ditemukan.</div>
            @endforelse
        </div>

        <!-- Floating Bulk Action Bar -->
        <div class="admin-floating-bulk" id="bulkFloatingBar">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-double text-warning"></i>
                <span class="fw-bold"><span id="bulkSelectedCount">0</span> soal dipilih</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="apple-glass-btn apple-btn-outline px-3 py-1 small" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                    <i class="fas fa-tags me-1"></i> Update Kategori
                </button>
                <button type="button" class="apple-glass-btn apple-btn-danger px-3 py-1 small" id="triggerBulkDeleteBtn">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            </div>
        </div>

        <!-- Modal Bulk Update -->
        <div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content apple-glass-card p-4" style="border-radius: 22px;">
                    <h5 class="fw-800 mb-3" style="color: var(--text-primary);">Update Massal Soal Terpilih</h5>
                    <div class="vstack gap-3 mb-4">
                        <div>
                            <label class="apple-label mb-1">Mode Ujian Baru</label>
                            <select name="bulk_exam_mode_id" class="form-select apple-glass-input">
                                <option value="">(Biarkan Tetap)</option>
                                <option value="0">Kosongkan Mode</option>
                                @foreach($examModes as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="apple-label mb-1">Sub Kategori Baru</label>
                            <select name="bulk_sub_category_id" class="form-select apple-glass-input">
                                <option value="">(Biarkan Tetap)</option>
                                <option value="0">Kosongkan Sub Kategori</option>
                                @foreach($subCategories as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="apple-label mb-1">Kategori Baru</label>
                            <select name="bulk_kategori" class="form-select apple-glass-input">
                                <option value="">(Biarkan Tetap)</option>
                                @foreach(($categoryOptions ?? collect()) as $row)
                                    <option value="{{ strtoupper(trim((string) ($row->value ?? ''))) }}">{{ (string) (($row->label ?? '') ?: ($row->value ?? '')) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="apple-label mb-1">Tingkat Difficulty</label>
                            <select name="bulk_difficulty" class="form-select apple-glass-input">
                                <option value="">(Biarkan Tetap)</option>
                                <option value="easy">Easy (Mudah)</option>
                                <option value="medium">Medium (Sedang)</option>
                                <option value="hard">Hard (Sulit)</option>
                            </select>
                        </div>
                        <div>
                            <label class="apple-label mb-1">Status Standar Kurikulum</label>
                            <select name="bulk_standar_tahun" class="form-select apple-glass-input">
                                <option value="">(Biarkan Tetap)</option>
                                <option value="2027">Migrasi ke Standar 2027 (Aktifkan ke Ujian)</option>
                                <option value="pra-2027">Jadikan Pra-2027 (Nonaktif dari Ujian)</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="apple-glass-btn apple-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="apple-glass-btn apple-btn-primary">Terapkan Perubahan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Form Tersembunyi Bulk Delete -->
    <form method="POST" action="{{ route('admin.soal.bulk-destroy') }}" id="bulkDeleteForm" onsubmit="return confirm('Yakin ingin menghapus seluruh soal terpilih secara permanen?')">
        @csrf
        @method('DELETE')
    </form>

    <!-- Modal Ekspor Backup -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="GET" action="{{ route('admin.soal-bank.export') }}" class="modal-content" style="border-radius: 24px; border: 1px solid var(--border-glass); background: var(--card-bg);">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="exportModalLabel" style="color: var(--text-primary);">
                            <i class="fas fa-file-export text-primary me-2"></i>Ekspor Backup Bank Soal
                        </h5>
                        <p class="small text-secondary mb-0">Unduh arsip naskah soal lengkap dengan struktur bobot dan kunci.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="apple-label mb-2">Pilih Format Berkas</label>
                        <div class="d-grid gap-2">
                            <label class="d-flex align-items-center gap-3 p-3 rounded-3 border" style="cursor: pointer; background: var(--bg-surface, rgba(255,255,255,0.03));">
                                <input type="radio" name="format" value="json" class="form-check-input mt-0" checked>
                                <div>
                                    <div class="fw-bold" style="color: var(--text-primary);"><i class="fas fa-code text-info me-1"></i> Format JSON (.json) <span class="badge bg-success ms-1">Direkomendasikan</span></div>
                                    <div class="small text-secondary">Struktur presisi tinggi: bobot TKP 1-5, format mode ujian, dan data relasional lengkap.</div>
                                </div>
                            </label>
                            <label class="d-flex align-items-center gap-3 p-3 rounded-3 border" style="cursor: pointer; background: var(--bg-surface, rgba(255,255,255,0.03));">
                                <input type="radio" name="format" value="csv" class="form-check-input mt-0">
                                <div>
                                    <div class="fw-bold" style="color: var(--text-primary);"><i class="fas fa-file-csv text-success me-1"></i> Format CSV (.csv)</div>
                                    <div class="small text-secondary">Kompatibel Microsoft Excel dan Spreadsheet (dengan UTF-8 BOM).</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="apple-label mb-2">Cakupan Data</label>
                        <div class="d-grid gap-2">
                            <label class="d-flex align-items-center gap-3 p-3 rounded-3 border" style="cursor: pointer;">
                                <input type="radio" name="export_scope" value="all" class="form-check-input mt-0" checked onchange="toggleExportScope(this.value)">
                                <div>
                                    <div class="fw-bold" style="color: var(--text-primary);">Semua Soal Bank Soal (Backup Penuh)</div>
                                    <div class="small text-secondary">Mencadangkan seluruh {{ number_format($modeStats['total'] ?? 0) }} butir soal di database.</div>
                                </div>
                            </label>
                            <label class="d-flex align-items-center gap-3 p-3 rounded-3 border" style="cursor: pointer;">
                                <input type="radio" name="export_scope" value="filtered" class="form-check-input mt-0" onchange="toggleExportScope(this.value)">
                                <div>
                                    <div class="fw-bold" style="color: var(--text-primary);">Sesuai Filter Halaman Saat Ini</div>
                                    <div class="small text-secondary">Hanya mengunduh hasil filter aktif ({{ number_format($soals->total()) }} soal).</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Hidden inputs for filtered scope -->
                    <div id="exportFilterParams">
                        <input type="hidden" name="q" value="{{ $search }}" disabled>
                        <input type="hidden" name="kategori" value="{{ $kategori }}" disabled>
                        <input type="hidden" name="exam_mode_id" value="{{ $examModeId }}" disabled>
                        <input type="hidden" name="sub_category_id" value="{{ $subCategoryId }}" disabled>
                        <input type="hidden" name="sub_topik" value="{{ $subTopik }}" disabled>
                        <input type="hidden" name="difficulty" value="{{ $difficulty }}" disabled>
                        <input type="hidden" name="standar_tahun" value="{{ $standarTahun }}" disabled>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="apple-glass-btn apple-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="apple-glass-btn apple-btn-primary px-4">
                        <i class="fas fa-download me-2"></i>Unduh Berkas Backup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Impor Backup -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.soal-bank.import') }}" enctype="multipart/form-data" class="modal-content" style="border-radius: 24px; border: 1px solid var(--border-glass); background: var(--card-bg);">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="importModalLabel" style="color: var(--text-primary);">
                            <i class="fas fa-file-import text-success me-2"></i>Impor Berkas Bank Soal
                        </h5>
                        <p class="small text-secondary mb-0">Impor naskah soal dari berkas JSON atau CSV dengan proteksi anti-duplikasi.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <!-- Info Anti-Duplikasi -->
                    <div class="p-3 mb-3 rounded-3" style="background: rgba(59, 130, 246, 0.09); border: 1px solid rgba(59, 130, 246, 0.25);">
                        <div class="d-flex gap-2">
                            <i class="fas fa-shield-halved text-primary fs-5 mt-1"></i>
                            <div class="small" style="color: var(--text-primary);">
                                <strong>Proteksi Anti-Duplikasi Otomatis Aktif:</strong>
                                <div class="text-secondary mt-1">
                                    Setiap butir soal akan diperiksa terhadap naskah soal yang ada. Soal yang terdeteksi identik/kembar akan <strong>dilewati secara otomatis</strong> sehingga tidak terjadi penggandaan bank soal.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="apple-label mb-1">Pilih Berkas Backup (.json atau .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="backup_file" class="form-control apple-glass-input" accept=".json,.csv,text/csv,application/json" required>
                        <div class="apple-hint mt-1 small">Mendukung berkas ekspor sistem atau JSON bank soal BKN (Maksimal 20 MB).</div>
                    </div>

                    <div class="mb-3">
                        <label class="apple-label mb-1">Standar Regulasi CASN Target</label>
                        <select name="standar_tahun" class="form-select apple-glass-input">
                            <option value="2027" selected>⭐ Standar 2027 (Aktif untuk Ujian CASN 2027)</option>
                            <option value="keep">Sesuai Data Berkas (Pertahankan nilai berkas jika ada)</option>
                            <option value="pra-2027">Pra-2027 (Simpan sebagai Arsip Nonaktif)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="apple-label mb-1">Fallback Mode Ujian (Bila berkas tidak mencantumkan mode)</label>
                        <select name="default_exam_mode_id" class="form-select apple-glass-input">
                            @foreach($examModes as $mode)
                                <option value="{{ $mode->id }}" @selected($mode->code === 'skd')>{{ $mode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="apple-glass-btn apple-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="apple-glass-btn apple-btn-primary px-4" id="btnSubmitImport">
                        <i class="fas fa-cloud-arrow-up me-2"></i>Mulai Impor & Validasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $soals->appends(request()->query())->links() }}
    </div>
</div>

@push('scripts')
<script>
    function toggleExportScope(val) {
        const filterBox = document.getElementById('exportFilterParams');
        if (!filterBox) return;
        if (val === 'filtered') {
            filterBox.querySelectorAll('input').forEach(inp => inp.disabled = false);
        } else {
            filterBox.querySelectorAll('input').forEach(inp => inp.disabled = true);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const floatingBar = document.getElementById('bulkFloatingBar');
        const selectedCountSpan = document.getElementById('bulkSelectedCount');
        const bulkDeleteBtn = document.getElementById('triggerBulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        // Handling status tombol submit impor
        const importForm = document.querySelector('#importModal form');
        if (importForm) {
            importForm.addEventListener('submit', function() {
                const btn = document.getElementById('btnSubmitImport');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses & Memeriksa Duplikasi...';
                }
            });
        }

        function updateFloatingState() {
            const checked = document.querySelectorAll('.item-checkbox:checked');
            const count = checked.length;
            selectedCountSpan.textContent = count;
            if (count > 0) {
                floatingBar.classList.add('is-visible');
            } else {
                floatingBar.classList.remove('is-visible');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateFloatingState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateFloatingState();
            });
        });

        if (bulkDeleteBtn && bulkDeleteForm) {
            bulkDeleteBtn.addEventListener('click', function() {
                const checked = document.querySelectorAll('.item-checkbox:checked');
                if (!checked.length) return;
                if (!confirm(`Hapus ${checked.length} butir soal terpilih? Tindakan ini tidak dapat dibatalkan.`)) return;

                bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    bulkDeleteForm.appendChild(input);
                });
                bulkDeleteForm.submit();
            });
        }
    });
</script>
@endpush
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
            'label' => 'Tambah',
            'icon' => 'fa-plus',
            'href' => route('admin.soal.create'),
            'style' => 'cta',
            'aria' => 'Tambah Soal',
        ],
        [
            'label' => 'Audit',
            'icon' => 'fa-shield-halved',
            'href' => route('admin.audit-soal.index'),
        ],
    ],
])
@endsection
