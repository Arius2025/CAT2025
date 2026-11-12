@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .start-page-hero {
        border-radius: 28px;
        border: 1px solid var(--border-glass);
        background:
            radial-gradient(circle at top left, rgba(34, 211, 238, 0.14), transparent 34%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 30%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.88));
        box-shadow: var(--shadow-premium);
        overflow: hidden;
    }
    [data-bs-theme="light"] .start-page-hero {
        background:
            radial-gradient(circle at top left, rgba(34, 211, 238, 0.08), transparent 34%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
    }
    .start-page-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.06);
        color: var(--text-secondary);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .start-page-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
        gap: 18px;
    }
    .start-page-card {
        border-radius: 24px;
        border: 1px solid var(--border-glass);
        background: rgba(255,255,255,0.04);
        padding: 22px;
    }
    [data-bs-theme="light"] .start-page-card {
        background: rgba(15, 23, 42, 0.03);
    }
    .start-page-step {
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        padding: 18px;
    }
    [data-bs-theme="light"] .start-page-step {
        background: rgba(15, 23, 42, 0.03);
        border-color: rgba(15, 23, 42, 0.08);
    }
    .start-page-step + .start-page-step {
        margin-top: 14px;
    }
    .start-page-step__label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.8rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 12px;
    }
    .start-page-step__index {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-grid;
        place-items: center;
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.22), rgba(59, 130, 246, 0.18));
        color: var(--text-primary);
        font-size: 0.78rem;
    }
    .start-page-list {
        display: grid;
        gap: 12px;
    }
    .start-page-list__item {
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        padding: 14px 16px;
    }
    [data-bs-theme="light"] .start-page-list__item {
        background: rgba(15, 23, 42, 0.03);
        border-color: rgba(15, 23, 42, 0.08);
    }
    @media (max-width: 991.98px) {
        .start-page-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<div class="container page-shell">
    <div class="start-page-hero p-4 p-md-5 mb-4">
        <div class="start-page-kicker mb-3">
            <i class="fas fa-play-circle opacity-75"></i>
            Siapkan Simulasi
        </div>
        <div class="start-page-grid">
            <div>
                <h1 class="apple-title mb-2">Mulai Sesi Ujian</h1>
                <div class="apple-subtitle">Halaman ini saya samakan arahnya dengan modal mulai ujian: modern, singkat, dan tetap mudah dipakai tanpa ketergantungan Livewire.</div>
            </div>
            <div class="start-page-card">
                <div class="apple-label">Saran</div>
                <div class="fw-800 mt-2" style="color: var(--text-primary);">Gunakan dashboard utama untuk alur paling lengkap.</div>
                <div class="apple-hint mt-2">Di sana mode, formasi, dan sesi latihan sudah otomatis menyesuaikan struktur terbaru.</div>
                <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-primary text-decoration-none mt-3 d-inline-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="start-page-grid">
        <div class="start-page-card">
            <div class="start-page-step">
                <div class="start-page-step__label">
                    <span class="start-page-step__index">1</span>
                    Mulai dari Dashboard
                </div>
                <div class="apple-hint">Untuk pengalaman terbaik, mulai sesi baru dari dashboard utama karena semua mode ujian dinamis ditangani di sana.</div>
            </div>

            <div class="start-page-step">
                <div class="start-page-step__label">
                    <span class="start-page-step__index">2</span>
                    Pilih Alur yang Stabil
                </div>
                <div class="apple-hint">Flow terbaru sekarang fokus ke request biasa agar lebih aman di HP dan tidak gampang macet seperti sebelumnya.</div>
            </div>

            <div class="start-page-step">
                <div class="start-page-step__label">
                    <span class="start-page-step__index">3</span>
                    Lanjutkan Ujian Aktif
                </div>
                <div class="apple-hint">Kalau masih punya sesi yang belum selesai, Anda bisa langsung melanjutkannya dari daftar aktif di bawah.</div>
            </div>
        </div>

        <div class="start-page-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-800" style="color: var(--text-primary);">Lanjutkan Ujian</div>
                <span class="start-exam-pill">{{ $progres->count() }} aktif</span>
            </div>

            @if($progres->count() > 0)
                <div class="start-page-list">
                    @foreach($progres as $p)
                        <div class="start-page-list__item d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-700" style="color: var(--text-primary);">{{ $p->nama_peserta }}</div>
                                <div class="apple-hint">Paket {{ $p->paket }}</div>
                            </div>
                            <a href="{{ route('ujian.show', $p->id) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                                Lanjut
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="apple-hint">Belum ada ujian aktif. Mulai sesi baru dari dashboard utama.</div>
            @endif
        </div>
    </div>
</div>
@endsection
