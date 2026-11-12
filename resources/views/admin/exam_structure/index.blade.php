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

    .admin-card {
        border-radius: 22px;
        border: 1px solid var(--card-border);
        background: var(--card-bg);
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(16px);
        padding: 24px;
        margin-bottom: 20px;
    }

    .admin-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(197, 155, 39, 0.15);
    }

    .admin-step-num {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: rgba(197, 155, 39, 0.15);
        border: 1px solid rgba(197, 155, 39, 0.35);
        color: #d97706;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }
    [data-bs-theme="dark"] .admin-step-num { color: #fbbf24; }

    .admin-input-label {
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 6px;
        display: block;
    }

    .admin-form-control {
        border-radius: 12px;
        border: 1px solid var(--border-glass);
        background: var(--input-bg, rgba(255, 255, 255, 0.06));
        color: var(--text-primary);
        font-family: inherit;
        padding: 10px 14px;
        transition: all 0.2s ease;
    }
    .admin-form-control:focus {
        border-color: #C59B27;
        box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.20);
        outline: none;
    }
</style>
@endpush

<div class="admin-shell">
    {{-- Page Hero --}}
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-sitemap"></i> Arsitektur & Taksonomi Sistem
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Struktur Ujian & Formasi
                </h1>
                <div class="apple-subtitle" style="max-width: 720px;">
                    Konfigurasi mode seleksi (SKD, SKB, PPPK), integrasi modul spasial figural, serta pemetaan rumpun formasi jabatan teknis.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-database me-1"></i> Bank Soal
                </a>
                <a href="{{ route('guide.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2">
                    <i class="fas fa-circle-info me-1"></i> Panduan Kisi-kisi
                </a>
            </div>
        </div>
    </div>

    @if(!($schemaReady ?? false))
        <div class="alert apple-alert border-0 mb-4" style="background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.25) !important;">
            <i class="fas fa-triangle-exclamation text-warning me-2"></i> Mode dinamis belum aktif penuh. Jalankan <code>{{ $migrationCommand ?? 'php artisan migrate' }}</code> untuk penyimpanan permanen.
        </div>
    @endif

    <div class="row g-4">
        {{-- Sisi Kiri: Tambah Mode & Tambah Sub Kategori --}}
        <div class="col-lg-5">
            {{-- Tambah Mode Ujian --}}
            <div class="admin-card">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-plus"></i></div>
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Tambah Mode Ujian</h2>
                        <small class="text-secondary">Definisikan mode seleksi baru</small>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.exam-structure.modes.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="admin-input-label">Nama Mode</label>
                        <input type="text" name="name" class="form-control admin-form-control" placeholder="Contoh: SKD CPNS 2024" @disabled(!($schemaReady ?? false)) required>
                    </div>
                    <div>
                        <label class="admin-input-label">Kode Unik Sistem</label>
                        <input type="text" name="code" class="form-control admin-form-control font-monospace" placeholder="skd / skb / pppk" @disabled(!($schemaReady ?? false)) required>
                    </div>
                    <div>
                        <label class="admin-input-label">Warna Identitas (Hex/CSS)</label>
                        <input type="text" name="color" class="form-control admin-form-control" placeholder="#133E87 atau #C59B27" @disabled(!($schemaReady ?? false))>
                    </div>
                    <div class="d-flex gap-3 pt-1">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_figural" value="1" @disabled(!($schemaReady ?? false))>
                            <span class="form-check-label small fw-bold" style="color: var(--text-primary);">Dukung Soal Figural</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked @disabled(!($schemaReady ?? false))>
                            <span class="form-check-label small fw-bold text-success">Aktif</span>
                        </label>
                    </div>
                    <button type="submit" class="apple-glass-btn apple-btn-primary w-100 py-2 mt-2 fw-bold" @disabled(!($schemaReady ?? false))>
                        <i class="fas fa-plus-circle me-1"></i> Simpan Mode Baru
                    </button>
                </form>
            </div>

            {{-- Tambah Sub Kategori / Formasi --}}
            <div class="admin-card">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-folder-plus"></i></div>
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Tambah Sub Kategori / Formasi</h2>
                        <small class="text-secondary">Petakan ke mode ujian induk</small>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.exam-structure.sub-categories.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="admin-input-label">Mode Ujian Induk</label>
                        <select name="exam_mode_id" class="form-select admin-form-control" @disabled(!($schemaReady ?? false)) required>
                            @foreach($examModes as $mode)
                                <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-input-label">Jenis Taksonomi</label>
                        <select name="kind" class="form-select admin-form-control" @disabled(!($schemaReady ?? false))>
                            <option value="formasi">Formasi Jabatan Teknis</option>
                            <option value="topik">Topik / Rumpun Materi Khusus</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-input-label">Nama Sub Kategori / Formasi</label>
                        <input type="text" name="name" class="form-control admin-form-control" placeholder="Contoh: S1 Teknik Informatika" @disabled(!($schemaReady ?? false)) required>
                    </div>
                    <button type="submit" class="apple-glass-btn apple-btn-primary w-100 py-2 mt-2 fw-bold" @disabled(!($schemaReady ?? false))>
                        <i class="fas fa-plus-circle me-1"></i> Simpan Sub Kategori
                    </button>
                </form>
            </div>
        </div>

        {{-- Sisi Kanan: Daftar Mode Ujian & Sub Kategori --}}
        <div class="col-lg-7">
            {{-- Daftar Mode Ujian --}}
            <div class="admin-card">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Mode Ujian Terdaftar</h2>
                        <small class="text-secondary">Konfigurasi aktifasi dan atribut figural</small>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill font-monospace">
                            {{ count($examModes) }} Mode
                        </span>
                    </div>
                </div>

                <div class="vstack gap-3">
                    @foreach($examModes as $mode)
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-glass);">
                            <form method="POST" action="{{ route('admin.exam-structure.modes.update', $mode->id) }}" id="updateMode{{ $mode->id }}">
                                @csrf
                                @method('PUT')
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label class="admin-input-label mb-1">Nama</label>
                                        <input type="text" name="name" value="{{ $mode->name }}" class="form-control admin-form-control" @disabled(!($schemaReady ?? false))>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="admin-input-label mb-1">Kode</label>
                                        <input type="text" name="code" value="{{ $mode->code }}" class="form-control admin-form-control font-monospace" @disabled(!($schemaReady ?? false))>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="admin-input-label mb-1">Warna</label>
                                        <input type="text" name="color" value="{{ $mode->color }}" class="form-control admin-form-control" @disabled(!($schemaReady ?? false))>
                                    </div>
                                    <div class="col-md-3 d-flex gap-2 align-items-center pt-md-3">
                                        <label class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="has_figural" value="1" @checked($mode->has_figural) @disabled(!($schemaReady ?? false))>
                                            <span class="form-check-label small">Figural</span>
                                        </label>
                                        <label class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($mode->is_active) @disabled(!($schemaReady ?? false))>
                                            <span class="form-check-label small text-success fw-bold">Aktif</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2 mt-3 pt-2 border-top" style="border-color: rgba(197, 155, 39, 0.1) !important;">
                                    <div class="small text-secondary">
                                        <i class="fas fa-layer-group me-1"></i> {{ $mode->sub_categories_count ?? 0 }} sub kategori terhubung
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-sm apple-glass-btn apple-btn-outline px-3" @disabled(!($schemaReady ?? false))>
                                            <i class="fas fa-check me-1"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('admin.exam-structure.modes.destroy', $mode->id) }}" class="mt-2 text-end" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mode ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-link text-danger text-decoration-none" @disabled(!($schemaReady ?? false))>
                                    <i class="fas fa-trash me-1"></i> Hapus Mode
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Daftar Sub Kategori --}}
            <div class="admin-card">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Sub Kategori & Formasi</h2>
                        <small class="text-secondary">Daftar formasi yang sudah terpetakan</small>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 rounded-pill font-monospace">
                        {{ $subCategoryStats['all'] ?? count($subCategories) }} Formasi
                    </span>
                </div>

                <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                    <table class="table apple-glass-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Formasi / Sub</th>
                                <th>Mode Induk</th>
                                <th>Jenis</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subCategories as $subCategory)
                                <tr>
                                    <td class="fw-bold" style="color: var(--text-primary);">{{ $subCategory->name }}</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                            {{ $subCategory->examMode->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 text-uppercase font-monospace" style="font-size: 0.72rem;">
                                            {{ $subCategory->kind ?? 'formasi' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.exam-structure.sub-categories.destroy', $subCategory->id) }}" onsubmit="return confirm('Hapus sub kategori ini?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none p-0" @disabled(!($schemaReady ?? false))>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary py-4 text-center">Belum ada sub kategori / formasi terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'User',
            'icon' => 'fa-users-gear',
            'href' => route('admin.user-access.index'),
        ],
        [
            'label' => 'Struktur',
            'icon' => 'fa-sitemap',
            'href' => route('admin.exam-structure.index'),
            'style' => 'cta',
            'aria' => 'Struktur Ujian',
        ],
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
    ],
])
@endsection
