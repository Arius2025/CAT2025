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

    .user-access-card {
        border-radius: 18px;
        background: var(--card-bg);
        border: 1px solid var(--border-glass);
        padding: 20px;
        transition: all 0.2s ease;
    }
    .user-access-card:hover {
        border-color: rgba(197, 155, 39, 0.35);
    }
</style>
@endpush

<div class="admin-shell">
    {{-- Page Hero --}}
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-users-gear"></i> Hak Akses & Otorisasi Pengguna
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Manajemen Akses & Persetujuan Akun
                </h1>
                <div class="apple-subtitle" style="max-width: 720px;">
                    Kelola otorisasi akun peserta ujian, persetujuan pendaftaran (approval flow), hak akses role master admin, serta visibilitas modul navigasi.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.api-tokens.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-key me-1"></i> Token API
                </a>
                <a href="{{ route('admin.exam-structure.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2">
                    <i class="fas fa-sitemap me-1"></i> Struktur Ujian
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Status Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.user-access.index', ['status' => 'all']) }}" class="apple-glass-btn {{ ($statusFilter ?? 'all') === 'all' ? 'apple-btn-gold' : 'apple-btn-outline' }} px-3 py-2">
            Semua Pengguna ({{ $statusStats['all'] ?? 0 }})
        </a>
        <a href="{{ route('admin.user-access.index', ['status' => 'pending']) }}" class="apple-glass-btn {{ ($statusFilter ?? 'all') === 'pending' ? 'apple-btn-gold' : 'apple-btn-outline' }} px-3 py-2">
            <i class="fas fa-clock me-1 text-warning"></i> Menunggu Persetujuan ({{ $statusStats['pending'] ?? 0 }})
        </a>
        <a href="{{ route('admin.user-access.index', ['status' => 'active']) }}" class="apple-glass-btn {{ ($statusFilter ?? 'all') === 'active' ? 'apple-btn-gold' : 'apple-btn-outline' }} px-3 py-2">
            <i class="fas fa-circle-check me-1 text-success"></i> Akun Aktif ({{ $statusStats['active'] ?? 0 }})
        </a>
        <a href="{{ route('admin.user-access.index', ['status' => 'inactive']) }}" class="apple-glass-btn {{ ($statusFilter ?? 'all') === 'inactive' ? 'apple-btn-gold' : 'apple-btn-outline' }} px-3 py-2">
            <i class="fas fa-ban me-1 text-danger"></i> Nonaktif ({{ $statusStats['inactive'] ?? 0 }})
        </a>
    </div>

    {{-- Pencarian --}}
    <form method="GET" class="admin-card p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="admin-input-label">Cari Nama atau Alamat Email</label>
                <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control admin-form-control" placeholder="Ketik kata kunci...">
            </div>
            <div class="col-lg-3">
                <label class="admin-input-label">Filter Status</label>
                <select name="status" class="form-select admin-form-control">
                    <option value="all" @selected(($statusFilter ?? 'all') === 'all')>Semua Status</option>
                    <option value="pending" @selected(($statusFilter ?? 'all') === 'pending')>Menunggu Approval</option>
                    <option value="active" @selected(($statusFilter ?? 'all') === 'active')>Aktif</option>
                    <option value="inactive" @selected(($statusFilter ?? 'all') === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button type="submit" class="apple-glass-btn apple-btn-primary px-4 py-2 flex-grow-1">
                    <i class="fas fa-magnifying-glass me-1"></i> Cari Pengguna
                </button>
                <a href="{{ route('admin.user-access.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2 text-decoration-none">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Bulk Action Bar --}}
    <form method="POST" action="{{ route('admin.user-access.bulk-update') }}" class="admin-card p-3 mb-4" id="bulkUserAccessForm">
        @csrf
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div class="small text-secondary">
                <i class="fas fa-check-double me-1 text-warning"></i> Tindakan Massal untuk akun yang dicentang di bawah ini.
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="bulk_action" value="approve" class="apple-glass-btn apple-btn-primary px-3 py-1 fs-7">
                    <i class="fas fa-check-circle me-1"></i> Setujui Massal
                </button>
                <button type="submit" name="bulk_action" value="deactivate" class="btn btn-sm btn-outline-danger px-3 py-1 rounded-pill">
                    <i class="fas fa-user-xmark me-1"></i> Nonaktifkan Massal
                </button>
            </div>
        </div>
    </form>

    {{-- Daftar Pengguna --}}
    <div class="vstack gap-3 mt-3">
        @forelse($users as $managedUser)
            <div class="user-access-card">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-3 pb-3 border-bottom" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input mt-0" type="checkbox" name="selected_user_ids[]" value="{{ $managedUser->id }}" form="bulkUserAccessForm" style="width: 18px; height: 18px;">
                        <div>
                            <div class="fw-bold fs-6" style="color: var(--text-primary);">
                                {{ $managedUser->name }}
                                @if($managedUser->isMasterAdmin())
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning ms-2" style="font-size: 0.7rem;">MASTER ADMIN</span>
                                @endif
                            </div>
                            <div class="small text-secondary font-monospace">{{ $managedUser->email }}</div>
                        </div>
                    </div>
                    <div>
                        @php($status = $managedUser->activationStatus())
                        @if($status === 'active')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill font-monospace">
                                <i class="fas fa-circle-check me-1"></i> AKTIF
                            </span>
                        @elseif($status === 'pending')
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 py-2 rounded-pill font-monospace">
                                <i class="fas fa-clock me-1"></i> MENUNGGU
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill font-monospace">
                                <i class="fas fa-ban me-1"></i> NONAKTIF
                            </span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.user-access.update', $managedUser->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 align-items-start">
                        <div class="col-sm-3 col-md-2">
                            <label class="admin-input-label">Status Akun</label>
                            <label class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($managedUser->isActive())>
                                <span class="form-check-label small fw-bold" style="color: var(--text-primary);">Aktif</span>
                            </label>
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_master_admin" value="1" @checked($managedUser->isMasterAdmin())>
                                <span class="form-check-label small fw-bold text-warning">Master Admin</span>
                            </label>
                        </div>
                        <div class="col-md-5">
                            <label class="admin-input-label">Mode Ujian Diizinkan</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($examModes as $mode)
                                    <label class="form-check p-2 rounded-3 border d-flex align-items-center gap-2 mb-0" style="background: rgba(0,0,0,0.02); font-size: 0.82rem; cursor: pointer;">
                                        <input class="form-check-input mt-0" type="checkbox" name="preferred_exam_mode_ids[]" value="{{ $mode->id }}" @checked(in_array((int) $mode->id, array_map('intval', $managedUser->preferredExamModeIds()), true))>
                                        <span class="form-check-label">{{ $mode->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="admin-input-label">Menu Navigasi Ditampilkan</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($menuOptions as $key => $label)
                                    <label class="form-check p-2 rounded-3 border d-flex align-items-center gap-2 mb-0" style="background: rgba(0,0,0,0.02); font-size: 0.82rem; cursor: pointer;">
                                        <input class="form-check-input mt-0" type="checkbox" name="visible_menu_keys[]" value="{{ $key }}" @checked(empty($managedUser->visible_menu_keys) || in_array($key, $managedUser->visible_menu_keys ?? [], true))>
                                        <span class="form-check-label">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-end pt-2 border-top" style="border-color: rgba(197, 155, 39, 0.1) !important;">
                        <button type="submit" class="apple-glass-btn apple-btn-primary px-4 py-2 fw-bold">
                            <i class="fas fa-floppy-disk me-1"></i> Simpan Izin Pengguna
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="admin-card text-center p-5">
                <i class="fas fa-users-slash fa-3x text-secondary opacity-50 mb-3"></i>
                <h3 class="h5 fw-bold mb-1">Tidak Ditemukan Pengguna</h3>
                <p class="text-secondary small">Tidak ada akun yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
            </div>
        @endforelse
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
            'style' => 'cta',
        ],
        [
            'label' => 'Struktur',
            'icon' => 'fa-sitemap',
            'href' => route('admin.exam-structure.index'),
        ],
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
    ],
])
@endsection
