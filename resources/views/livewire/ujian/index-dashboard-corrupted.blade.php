@push('styles')
<style>
    :root {
        --glass-card: rgba(30, 41, 59, 0.4);
        --neon-green: #10b981;
        --neon-blue: #3b82f6;
        --neon-purple: #8b5cf6;
        --table-row-bg: rgba(255,255,255,0.02);
    }
    [data-bs-theme="light"] {
        --glass-card: rgba(255, 255, 255, 0.6);
        --table-row-bg: rgba(0,0,0,0.02);
    }

    /* Premium Overhauled Hero */
    .welcome-hero {
        position: relative;
        border-radius: 32px;
        border: 1px solid var(--border-glass);
        background: 
            radial-gradient(ellipse at 0% 0%, rgba(34, 211, 238, 0.22), transparent 45%),
            radial-gradient(ellipse at 100% 0%, rgba(59, 130, 246, 0.28), transparent 45%),
            linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.7));
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.08);
        overflow: hidden;
        padding: 45px;
        margin-bottom: 40px;
    }

    [data-bs-theme="light"] .welcome-hero {
        background: 
            radial-gradient(ellipse at 0% 0%, rgba(8, 145, 178, 0.15), transparent 48%),
            radial-gradient(ellipse at 100% 0%, rgba(37, 99, 235, 0.18), transparent 48%),
            linear-gradient(135deg, rgba(255,255,255,0.85), rgba(241, 245, 249, 0.85));
        box-shadow: 
            0 20px 40px -10px rgba(0, 0, 0, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.7);
    }

    .hero-label {
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-size: 0.75rem;
        color: var(--accent-cyan);
        display: block;
        margin-bottom: 15px;
    }

    .hero-stat {
        background: rgba(255,255,255,0.05);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--border-glass);
        border-radius: 20px;
        padding: 20px;
        transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .hero-stat:hover { transform: translateY(-5px); background: rgba(255,255,255,0.08); }
    .hero-stat-value { font-size: 1.5rem; font-weight: 900; color: var(--text-primary); }
    .hero-stat-label { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: var(--text-secondary); opacity: 0.6; }
    [data-bs-theme="light"] .hero-stat {
        background: rgba(255,255,255,0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    }
    [data-bs-theme="light"] .hero-stat:hover {
        background: rgba(255,255,255,0.96);
    }

    /* Activity List */
    .activity-item {
        background: var(--table-row-bg);
        border-radius: 16px; 
        padding: 16px 20px;
        margin-bottom: 12px;
        border: 1px solid transparent; transition: 0.3s;
    }
    .activity-item:hover { border-color: var(--accent-blue); }

    @keyframes pulse-btn {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
    .btn-pulse { animation: pulse-btn 2s infinite; }

    .hub-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 36px;
    }
    .hub-card {
        position: relative;
        overflow: hidden;
        min-height: 100%;
    }
    .hub-card::after {
        content: "";
        position: absolute;
        inset: auto -60px -60px auto;
        width: 140px;
        height: 140px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(59,130,246,0.20), transparent 65%);
        pointer-events: none;
    }
    .hub-card h5 {
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .hub-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }
    .hub-meta .apple-pill {
        font-size: 0.78rem;
    }
    .hub-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }
    .quick-link-card {
        display: block;
        text-decoration: none;
        color: inherit;
        height: 100%;
    }
    .quick-link-card:hover {
        transform: translateY(-2px);
    }
    .quick-link-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: rgba(255,255,255,0.06);
        border: 1px solid var(--border-glass);
        color: var(--text-primary);
        font-size: 1.15rem;
    }
    [data-bs-theme="light"] .quick-link-icon {
        background: linear-gradient(135deg, rgba(8,145,178,0.10), rgba(37,99,235,0.08));
        border-color: rgba(15,23,42,0.08);
    }
    .dashboard-command-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
        margin: 14px 0 10px;
    }
    .dashboard-mini-note {
        margin-top: 10px;
        color: var(--text-secondary);
        font-size: 0.88rem;
    }
    .dashboard-command-bar .apple-nav-tile {
        min-height: 88px;
    }
    .is-loading-preview .apple-pill,
    .is-loading-preview .fw-800,
    .is-loading-preview #paketPreviewRules,
    .is-loading-preview #paketPreviewStock {
        opacity: 0.5;
    }
    .workflow-step {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 14px 0;
        border-top: 1px solid var(--border-glass);
    }
    .workflow-step:first-child {
        border-top: 0;
        padding-top: 0;
    }
    .workflow-no {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        font-weight: 800;
        background: rgba(59,130,246,0.14);
        color: var(--text-primary);
        border: 1px solid rgba(59,130,246,0.24);
    }
    .dashboard-section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }
    .section-link-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
    }
    @media (max-width: 767px) {
        .welcome-hero {
            border-radius: 22px;
            padding: 24px 18px;
            margin-bottom: 20px;
        }
        .hero-label {
            margin-bottom: 10px;
        }
        .hero-stat {
            border-radius: 18px;
            padding: 16px;
        }
        .hub-grid {
            gap: 14px;
            margin-bottom: 20px;
        }
        .hub-card {
            border-radius: 18px;
        }
        .dashboard-mini-note {
            margin-top: 12px;
            font-size: 0.82rem;
        }
        .dashboard-command-bar,
        .section-link-row {
            grid-template-columns: 1fr;
        }
    }

    .step-card-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .step-card-grid.step-card-grid-compact {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .step-select-card {
        text-align: left;
        border-radius: 20px;
        border: 1px solid var(--border-glass);
        background: rgba(255,255,255,0.04);
        color: var(--text-primary);
        padding: 16px 16px;
        transition: transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }
    .step-select-card:hover {
        transform: translateY(-2px);
        border-color: rgba(59,130,246,0.26);
        background: rgba(255,255,255,0.06);
    }
    .step-select-card.active {
        border-color: rgba(34,211,238,0.34);
        background: linear-gradient(135deg, rgba(34,211,238,0.16), rgba(59,130,246,0.10));
        box-shadow: 0 18px 36px rgba(15,23,42,0.16);
    }
    .step-select-card.step-select-card--ready {
        border-color: rgba(16,185,129,0.24);
        box-shadow: inset 0 0 0 1px rgba(16,185,129,0.08);
    }
    .step-select-card.step-select-card--short {
        border-color: rgba(245,158,11,0.30);
        background: linear-gradient(135deg, rgba(245,158,11,0.10), rgba(239,68,68,0.06));
    }
    .stock-state {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.8rem;
        border: 1px solid var(--border-glass);
    }
    .stock-state--ready {
        background: rgba(16,185,129,0.12);
        border-color: rgba(16,185,129,0.24);
        color: #d1fae5;
    }
    .stock-state--short {
        background: rgba(245,158,11,0.12);
        border-color: rgba(245,158,11,0.24);
        color: #fde68a;
    }
    [data-bs-theme="light"] .stock-state--ready,
    [data-bs-theme="light"] .stock-state--short {
        color: var(--text-primary);
    }
    .step-select-card__title {
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }
    .step-select-card__hint {
        margin-top: 6px;
        color: var(--text-secondary);
        font-size: 0.84rem;
        line-height: 1.45;
    }
    .step-native-select {
        margin-top: 4px;
    }
    .step-native-select.step-native-select-hidden {
        display: none !important;
    }
    .step-flow-note {
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.6;
    }

    @media (max-width: 767px) {
        .step-card-grid,
        .step-card-grid.step-card-grid-compact {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991px) {
        .hub-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
    }

</style>
@endpush

@php
    $startErrorText = strtolower((string) session('error'));
    $startUniqueShortageError = str_contains($startErrorText, 'soal unik');
    $startTotalShortageError = str_contains($startErrorText, 'stok soal kategori');
@endphp

<div class="container page-shell">
    @if(auth()->user()?->isMasterAdmin() && (int) ($masterAdminAlerts['pending_users'] ?? 0) > 0)
        <div class="alert border-0 apple-glass apple-glass-card apple-alert apple-alert-inline mb-4" style="background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.20) !important;">
            <i class="fas fa-user-clock mt-1" style="color: #f59e0b;"></i>
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 w-100">
                <div>
                    <div class="fw-800" style="color: var(--text-primary);">Ada {{ (int) ($masterAdminAlerts['pending_users'] ?? 0) }} akun menunggu persetujuan</div>
                    <div class="apple-hint">Buka Akses User untuk mengaktifkan akun baru yang baru mendaftar.</div>
                </div>
                <a href="{{ route('admin.user-access.index', ['status' => 'pending']) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-user-gear me-2"></i>Buka Akses User
                </a>
            </div>
        </div>
    @endif

    <div class="welcome-hero">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="hero-label">User Dashboard</span>
                <h1 class="display-5 fw-800 mb-3 apple-title" style="font-weight: 900; letter-spacing: -1px; color: var(--text-primary);">Halo, {{ Auth::user()->name }}!</h1>
                <p class="fs-5 mb-4 pe-lg-5 apple-subtitle" style="max-width: 500px;">Lanjutkan progres persiapan seleksi Anda hari ini dengan simulasi terbaik untuk berbagai mode ujian.</p>
                
                <div class="d-lg-none mt-3">
                   <div class="p-3 rounded-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div class="small fw-bold text-success"><i class="fas fa-info-circle me-1"></i> Quick Start</div>
                        <div class="small apple-hint mt-1">Gunakan tombol <span style="color: var(--text-primary); font-weight: 800;">Mulai</span> di dock bawah.</div>
                   </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="hero-stat">
                            <div class="hero-stat-label">Soal Selesai</div>
                            <div class="hero-stat-value mt-1">{{ number_format($stats['total_soal_selesai']) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="hero-stat">
                            <div class="hero-stat-label">Skor Tertinggi</div>
                            <div class="hero-stat-value mt-1">{{ $stats['skor_tertinggi'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('bookmark.index') }}" class="hero-stat d-block text-decoration-none">
                            <div class="hero-stat-label">Favorit Soal</div>
                            <div class="hero-stat-value mt-1">{{ number_format($stats['bookmark_count'] ?? 0) }}</div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <div class="hero-stat d-flex align-items-center justify-content-between" style="border-left: 4px solid var(--neon-purple);">
                            <div>
                                <div class="hero-stat-label">Rata-rata Skor</div>
                                <div class="hero-stat-value mt-1">{{ $stats['rata_rata'] }}</div>
                            </div>
                            <i class="fas fa-chart-line fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="apple-glass apple-glass-card p-3 p-md-4 mb-4">
        <div class="dashboard-section-title mb-0">
            <div>
                <div class="apple-kicker">Akses Cepat</div>
                <div class="apple-hint">Semua jalur penting dikumpulkan di satu tempat agar dashboard tidak dobel tombol dan tidak bikin bingung.</div>
            </div>
            <span class="apple-pill muted">Terhubung</span>
        </div>
        <div class="dashboard-command-bar">
            <button class="apple-nav-tile d-none d-lg-flex" type="button" data-bs-toggle="modal" data-bs-target="#modalMulai">
                <span class="apple-nav-tile__icon"><i class="fas fa-play"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Mulai Ujian</span>
                    <span class="apple-nav-tile__hint">Buka langkah mulai ujian tanpa pindah halaman.</span>
                </span>
            </button>
            <a href="{{ route('ujian.dashboard') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-ranking-star"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Ranking</span>
                    <span class="apple-nav-tile__hint">Lihat hasil latihan dan posisi per mode.</span>
                </span>
            </a>
            <a href="{{ route('ujian.leaderboard') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-trophy"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Klasemen</span>
                    <span class="apple-nav-tile__hint">Bandingkan nilai terbaik antar peserta.</span>
                </span>
            </a>
            <a href="{{ route('analysis.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-chart-pie"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Analisis Belajar</span>
                    <span class="apple-nav-tile__hint">Baca topik terkuat, area lemah, dan tren progres pribadi.</span>
                </span>
            </a>
            <a href="{{ route('coach.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-user-graduate"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Mentor Pintar</span>
                    <span class="apple-nav-tile__hint">Dapatkan prioritas belajar, saran mentor, dan jadwal otomatis.</span>
                </span>
            </a>
            <a href="{{ route('bookmark.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-bookmark"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Favorit</span>
                    <span class="apple-nav-tile__hint">Kumpulkan soal yang ingin direview lagi.</span>
                </span>
            </a>
            <a href="{{ route('guide.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-circle-info"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Panduan</span>
                    <span class="apple-nav-tile__hint">Baca alur penggunaan dengan bahasa yang lebih sederhana.</span>
                </span>
            </a>
            @auth
            <a href="{{ route('admin.soal.generate') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-wand-magic-sparkles"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Generate</span>
                    <span class="apple-nav-tile__hint">Buat soal baru lalu lanjut audit tanpa pindah alur.</span>
                </span>
            </a>
            @endauth
        </div>
        <div class="dashboard-mini-note">Di mobile, tombol utama tetap ada di dock bawah. Di desktop, semua halaman penting cukup lewat bar ini tanpa tombol hero yang dobel.</div>
    </div>

    <div class="hub-grid">
        <div class="apple-glass apple-glass-card p-4 hub-card" style="grid-column: span 5;">
            <div class="dashboard-section-title">
                <div>
                    <div class="apple-kicker">Pusat Aktivitas</div>
                    <h5 class="mb-1">Semua alur utama sekarang mulai dari sini</h5>
                    <div class="apple-hint">Mulai latihan, cek stok soal, pindah ke ranking, sampai rapikan bank soal dan audit tanpa putus konteks.</div>
                </div>
                <span class="apple-pill {{ $dynamicExamReady ? 'success' : 'danger' }}">
                    {{ $dynamicExamReady ? 'Struktur aktif' : 'Masih fallback' }}
                </span>
            </div>
            <div class="workflow-step">
                <div class="workflow-no">1</div>
                <div>
                    <div class="fw-800" style="color: var(--text-primary);">Pilih mode dan formasi</div>
                    <div class="apple-hint">Dashboard, modal mulai, generate, dan panduan sekarang membaca struktur ujian yang sama.</div>
                </div>
            </div>
            <div class="workflow-step">
                <div class="workflow-no">2</div>
                <div>
                    <div class="fw-800" style="color: var(--text-primary);">Isi dan audit bank soal</div>
                    <div class="apple-hint">Generate, auto-label, bank soal, dan audit dirangkai jadi satu alur kerja admin.</div>
                </div>
            </div>
            <div class="workflow-step">
                <div class="workflow-no">3</div>
                <div>
                    <div class="fw-800" style="color: var(--text-primary);">Pantau hasil dan ranking</div>
                    <div class="apple-hint">Riwayat, leaderboard, bookmark, dan hasil ujian sekarang mengikuti mode/sub kategori yang sama.</div>
                </div>
            </div>
            <div class="hub-actions">
                <a href="{{ route('guide.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-book-open me-2"></i>Panduan
                </a>
                <a href="{{ route('ujian.leaderboard') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-trophy me-2"></i>Klasemen
                </a>
                <a href="{{ route('bookmark.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-bookmark me-2"></i>Favorit
                </a>
            </div>
        </div>

        <div class="apple-glass apple-glass-card p-4 hub-card" style="grid-column: span 4;">
            <div class="apple-kicker">Bank Soal Terkoneksi</div>
            <h5>Stok soal, mode, dan struktur</h5>
            <div class="apple-hint">Dashboard memakai angka yang sama dengan halaman Generate Soal dan Panduan, jadi kamu bisa baca kondisi bank soal langsung dari sini.</div>
            <div class="hub-meta">
                <span class="apple-pill muted"><i class="fas fa-database opacity-75"></i>Total {{ number_format((int) ($questionHubStats['total'] ?? 0)) }}</span>
                <span class="apple-pill mode-skd">SKD {{ number_format((int) ($questionHubStats['skd'] ?? 0)) }}</span>
                <span class="apple-pill mode-skb">SKB {{ number_format((int) ($questionHubStats['skb'] ?? 0)) }}</span>
                <span class="apple-pill mode-pppk">PPPK {{ number_format((int) ($questionHubStats['pppk'] ?? 0)) }}</span>
                <span class="apple-pill mode-farmasi">Farmasi {{ number_format((int) ($questionHubStats['farmasi'] ?? 0)) }}</span>
                <span class="apple-pill {{ ((int) ($questionHubStats['unlabeled'] ?? 0)) > 0 ? 'danger' : 'success' }}">
                    {{ ((int) ($questionHubStats['unlabeled'] ?? 0)) > 0 ? 'Perlu label ' . number_format((int) ($questionHubStats['unlabeled'] ?? 0)) : 'Label aman' }}
                </span>
            </div>
            <div class="hub-actions">
                <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-primary text-decoration-none">
                    <i class="fas fa-wand-magic-sparkles me-2"></i>Generate Soal
                </a>
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-layer-group me-2"></i>Bank Soal
                </a>
            </div>
        </div>

        <div class="apple-glass apple-glass-card p-4 hub-card" style="grid-column: span 3;">
            <div class="apple-kicker">Struktur Ujian</div>
            <h5>Mode, formasi, dan sesi</h5>
            <div class="apple-hint">Bagian ini memastikan dashboard, generate, dan mulai ujian pakai struktur yang sama.</div>
            <div class="hub-meta">
                <span class="apple-pill muted"><i class="fas fa-shapes opacity-75"></i>{{ number_format((int) ($structureStats['mode_count'] ?? 0)) }} mode</span>
                <span class="apple-pill muted"><i class="fas fa-diagram-project opacity-75"></i>{{ number_format((int) ($structureStats['sub_category_count'] ?? 0)) }} formasi</span>
                <span class="apple-pill muted"><i class="fas fa-clock opacity-75"></i>{{ number_format((int) ($structureStats['package_count'] ?? 0)) }} sesi</span>
            </div>
            <div class="hub-actions">
                <a href="{{ route('admin.exam-structure.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-sitemap me-2"></i>Struktur Ujian
                </a>
            </div>
        </div>
    </div>

    @auth
    <div class="apple-glass apple-glass-card p-4 mb-4">
        <div class="dashboard-section-title">
            <div>
                <div class="apple-kicker">Jalur Admin</div>
                <h5 class="mb-1">Semua alat admin tersambung dari dashboard</h5>
                <div class="apple-hint">Generate, Struktur Ujian, Bank Soal, Audit, dan API helper sekarang berada dalam satu jalur kerja yang sama.</div>
            </div>
            <span class="apple-pill muted"><i class="fas fa-link opacity-75"></i>Sinkron</span>
        </div>
        <div class="section-link-row">
            <a href="{{ route('admin.soal.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-wand-magic-sparkles"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Generate Soal</span>
                    <span class="apple-nav-tile__hint">Buat stok soal baru sesuai mode dan formasi.</span>
                </span>
            </a>
            <a href="{{ route('admin.exam-structure.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-sitemap"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Struktur Ujian</span>
                    <span class="apple-nav-tile__hint">Atur mode, formasi, topik, dan sesi ujian.</span>
                </span>
            </a>
            <a href="{{ route('admin.soal-bank.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-book"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Bank Soal</span>
                    <span class="apple-nav-tile__hint">Cari, rapikan, dan update metadata soal.</span>
                </span>
            </a>
            <a href="{{ route('admin.audit-soal.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-shield-halved"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">Audit</span>
                    <span class="apple-nav-tile__hint">Periksa kunci, pembahasan, dan saran AI.</span>
                </span>
            </a>
            <a href="{{ route('admin.api-tokens.index') }}" class="apple-nav-tile">
                <span class="apple-nav-tile__icon"><i class="fas fa-key"></i></span>
                <span class="apple-nav-tile__body">
                    <span class="apple-nav-tile__title">API Token</span>
                    <span class="apple-nav-tile__hint">Kelola akses API dan helper pengujian.</span>
                </span>
            </a>
        </div>
    </div>
    @endauth

    <div class="row g-3 g-lg-4 mb-5">
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('ujian.dashboard') }}" class="quick-link-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="quick-link-icon"><i class="fas fa-ranking-star"></i></div>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">Ranking Simulasi</div>
                            <div class="apple-hint">Lihat performa peserta berdasarkan mode, formasi, paket, tanggal, dan skor.</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('ujian.leaderboard') }}" class="quick-link-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="quick-link-icon"><i class="fas fa-trophy"></i></div>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">Klasemen</div>
                            <div class="apple-hint">Bandingkan hasil per mode ujian dan lihat posisi terbaik secara real-time.</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.audit-soal.index') }}" class="quick-link-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="quick-link-icon"><i class="fas fa-shield-halved"></i></div>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">Audit Soal</div>
                            <div class="apple-hint">Review kualitas soal, revisi pembahasan, dan cek antrian audit dari satu tempat.</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('analysis.index') }}" class="quick-link-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="quick-link-icon"><i class="fas fa-chart-pie"></i></div>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">Analisis Belajar</div>
                            <div class="apple-hint">Lihat kekuatan, kelemahan, saran belajar, dan perbandingan antar mode ujian.</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('coach.index') }}" class="quick-link-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="quick-link-icon"><i class="fas fa-user-graduate"></i></div>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">Mentor Pintar</div>
                            <div class="apple-hint">Lihat prioritas mingguan, saran rule-based, dan prediksi kesiapan pribadi.</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.api-tokens.index') }}" class="quick-link-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="quick-link-icon"><i class="fas fa-key"></i></div>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">API & Mobile</div>
                            <div class="apple-hint">Kelola token, helper API, dan jalur integrasi agar web dan app tetap satu sistem.</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Info Bank Soal / Warning -->
    @if(isset($bankSoalStats))
        @php
            $lowFresh = collect($bankSoalStats)->filter(fn($stat) => $stat['fresh'] < $stat['need']);
        @endphp

        <div class="row g-3 mb-4">
            @foreach($bankSoalStats as $kat => $s)
                @php
                    $total = (int) ($s['total'] ?? 0);
                    $used = (int) ($s['used'] ?? 0);
                    $fresh = (int) ($s['fresh'] ?? 0);
                    $pct = $total > 0 ? max(0, min(100, (int) round(($fresh / $total) * 100))) : 0;
                @endphp
                <div class="col-12 col-md-4">
                    <div class="apple-glass apple-glass-card p-4">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="small fw-800 opacity-75" style="color: var(--text-secondary); letter-spacing: .16em;">BANK SOAL</div>
                                <div class="h4 fw-800 mb-1">{{ $kat }}</div>
                                <div class="small" style="color: var(--text-secondary);">
                                    Fresh: <b style="color: var(--text-primary);">{{ $fresh }}</b> dari {{ $total }} unik
                                </div>
                            </div>
                            <span class="badge rounded-pill" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border-glass); color: var(--text-primary);">
                                Terpakai {{ $used }}
                            </span>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 10px; background: rgba(255,255,255,0.06); border-radius: 999px; overflow: hidden;">
                                <div class="progress-bar"
                                     role="progressbar"
                                     style="width: {{ $pct }}%; background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));"
                                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="small mt-2" style="color: var(--text-secondary);">
                                Rotasi anti-ulang aktif.
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($lowFresh->isNotEmpty())
            <div class="alert mb-5 border-0 shadow-sm d-flex align-items-center" style="background: rgba(245,158,11,0.12); color: var(--text-primary); border: 1px solid rgba(245,158,11,0.25) !important; border-radius: 16px;">
                <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning opacity-75"></i>
                <div>
                    <strong style="color: #d97706;">Perhatian: Stok soal baru (fresh) mulai menipis!</strong><br>
                    <span class="small" style="color: var(--text-secondary);">Anda mungkin akan menemukan soal yang diulang untuk kategori: </span>
                    @foreach($lowFresh as $kat => $s)
                        <span class="badge bg-warning text-dark me-1">{{ $kat }} (sisa {{ $s['fresh'] }})</span>
                    @endforeach
                    <br><span class="small" style="color: var(--text-secondary);">Silakan <a href="{{ route('admin.soal.index') }}" class="text-warning fw-bold text-decoration-none">Generate Soal AI Baru</a> untuk memperbanyak variasi soal ujian.</span>
                </div>
            </div>
        @endif
    @endif

    @if(!($dynamicExamReady ?? false))
        <div class="apple-glass apple-glass-card p-4 mb-5" style="background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.22);">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="apple-kicker">Mode Ujian Dinamis</div>
                    <div class="h4 apple-title mb-2">Struktur SKB dan PPPK sudah diarahkan ke formasi yang dipakai</div>
                    <div class="apple-hint">
                        Saat ini tampilan mode sudah muncul, tapi aktivasi penuh butuh migrasi tabel baru.
                        Jalankan <code>php artisan migrate</code> supaya Struktur Ujian, formasi PPPK D3 Farmasi, PPPK D3 Manajemen Informatika, PPPK S1 Teknik Informatika, serta SKB bidang IT aktif sempurna.
                    </div>
                </div>
                <a href="{{ route('guide.index') }}" class="apple-glass-btn apple-btn-primary text-decoration-none">
                    <i class="fas fa-book-open me-2"></i>Lihat Panduan
                </a>
            </div>
        </div>
    @endif

    <div class="row g-5">
        <div class="col-lg-7">
            <h4 class="fw-800 mb-4" style="color: var(--text-primary);">Ujian Aktif</h4>
            <div class="table-responsive d-none d-lg-block apple-glass-table-wrap">
                <table class="table apple-glass-table mb-0" data-card-disable="1">
                    <thead>
                        <tr class="text-muted small border-secondary border-opacity-25">
                            <th class="py-3 px-3">DETAIL SESI</th>
                            <th class="py-3">UPDATE</th>
                            <th class="py-3 text-end">OPSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($progresUjian as $ujian)
                        @php
                            $modeName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'exam_mode_id') && $ujian->examMode) ? $ujian->examMode->name : 'Mode default';
                            $subName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'sub_category_id') && $ujian->subCategory) ? $ujian->subCategory->name : null;
                            $modeCode = strtolower((string) ($ujian->examMode->code ?? ''));
                            $modePillClass = str_contains(strtolower($modeName . ' ' . ($subName ?? '')), 'farmasi') || str_contains(strtolower($modeName . ' ' . ($subName ?? '')), 'apoteker')
                                ? 'mode-farmasi'
                                : ($modeCode === 'skb' ? 'mode-skb' : ($modeCode === 'pppk' ? 'mode-pppk' : 'mode-skd'));
                        @endphp
                        <tr class="align-middle border-secondary border-opacity-10">
                            <td class="py-4 px-3">
                                <div class="fw-bold mb-1">{{ $ujian->nama_peserta }} <span class="badge bg-secondary bg-opacity-25 ms-2" style="color: var(--text-primary);">Paket {{ $ujian->paket }}</span></div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="apple-pill {{ $modePillClass }}">{{ $modeName }}</span>
                                    @if($subName)
                                        <span class="apple-pill muted">{{ $subName }}</span>
                                    @endif
                                </div>
                                <small class="text-muted font-monospace"><i class="far fa-clock me-1"></i> {{ floor($ujian->sisa_waktu / 60) }}m {{ $ujian->sisa_waktu % 60 }}d</small>
                            </td>
                            <td class="small text-muted">{{ $ujian->updated_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <div class="active-actions d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route('ujian.show', $ujian->id) }}" class="apple-glass-btn apple-btn-primary btn-sm px-4 py-2">Lanjut</a>
                                    <form action="{{ route('ujian.destroy', $ujian->id) }}" method="POST" class="m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="apple-glass-btn apple-btn-outline btn-sm text-danger" onclick="return confirm('Hapus progres ini?')" style="width: 44px; height: 44px; display: grid; place-items: center; border-radius: 12px; padding: 0;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-5 opacity-50" style="color: var(--text-primary);">Tidak ada ujian aktif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile/Tablet: Cards -->
            <div class="d-lg-none vstack gap-3">
                @forelse($progresUjian as $ujian)
                    @php
                        $mins = floor($ujian->sisa_waktu / 60);
                        $secs = $ujian->sisa_waktu % 60;
                        $modeName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'exam_mode_id') && $ujian->examMode) ? $ujian->examMode->name : 'Mode default';
                        $subName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'sub_category_id') && $ujian->subCategory) ? $ujian->subCategory->name : null;
                        $modeCode = strtolower((string) ($ujian->examMode->code ?? ''));
                        $modePillClass = str_contains(strtolower($modeName . ' ' . ($subName ?? '')), 'farmasi') || str_contains(strtolower($modeName . ' ' . ($subName ?? '')), 'apoteker')
                            ? 'mode-farmasi'
                            : ($modeCode === 'skb' ? 'mode-skb' : ($modeCode === 'pppk' ? 'mode-pppk' : 'mode-skd'));
                    @endphp
                    <div class="apple-glass apple-glass-card p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div style="min-width: 0;">
                                <div class="fw-800" style="color: var(--text-primary); line-height: 1.15;">
                                    {{ $ujian->nama_peserta }}
                                </div>
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="apple-pill {{ $modePillClass }}"><i class="fas fa-shield-halved opacity-75"></i>{{ $modeName }}</span>
                                    @if($subName)
                                        <span class="apple-pill muted"><i class="fas fa-diagram-project opacity-75"></i>{{ $subName }}</span>
                                    @endif
                                    <span class="apple-pill muted"><i class="fas fa-layer-group opacity-75"></i>Paket {{ $ujian->paket }}</span>
                                    <span class="apple-pill muted"><i class="far fa-clock opacity-75"></i>{{ $mins }}m {{ $secs }}d</span>
                                    <span class="apple-pill muted"><i class="fas fa-rotate opacity-75"></i>{{ $ujian->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <form action="{{ route('ujian.destroy', $ujian->id) }}" method="POST" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="apple-glass-btn apple-btn-outline text-danger" onclick="return confirm('Hapus progres ini?')" style="width: 44px; height: 44px; display: grid; place-items: center; border-radius: 14px; padding: 0;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('ujian.show', $ujian->id) }}" class="apple-glass-btn apple-btn-primary w-100 mt-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-play opacity-75"></i> Lanjut
                        </a>
                    </div>
                @empty
                    $modePillClass = str_contains(strtolower($modeName . ' ' . ($subName ?? '')), 'farmasi') || str_contains(strtolower($modeName . ' ' . ($subName ?? '')), 'apoteker')
                        ? 'mode-farmasi'
                        : ($modeCode === 'skb' ? 'mode-skb' : ($modeCode === 'pppk' ? 'mode-pppk' : 'mode-skd'));
                @endphp
                <div class="apple-glass apple-glass-card p-3 mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3" style="min-width: 0;">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary" style="flex: 0 0 auto;">
                                <i class="fas fa-file-lines"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div class="fw-800" style="color: var(--text-primary); line-height: 1.15;">
                                    Paket {{ $act->paket }}
                                </div>
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="apple-pill {{ $modePillClass }}"><i class="fas fa-shield-halved opacity-75"></i>{{ $modeName }}</span>
                                    @if($subName)
                                        <span class="apple-pill muted"><i class="fas fa-diagram-project opacity-75"></i>{{ $subName }}</span>
                                    @endif
                                    @php
                                        $paketLabel = $act->paket === 'D' ? 'Full' : ('Mini ' . $act->paket);
                                    @endphp
                                    <span class="apple-pill muted"><i class="fas fa-tag opacity-75"></i>{{ $paketLabel }}</span>
                                    <span class="apple-pill total">Skor {{ $act->total_skor }}</span>
                                    <span class="apple-pill {{ $act->is_lulus ? 'success' : 'danger' }}">
                                        {{ $act->is_lulus ? 'LULUS' : 'GAGAL' }}
                                    </span>
                                    <span class="apple-pill muted"><i class="fas fa-calendar opacity-75"></i>{{ $act->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('ujian.hasil', $act->id) }}" class="apple-glass-btn apple-btn-outline w-100 mt-3 d-flex align-items-center justify-content-center gap-2" title="Lihat Hasil">
                        <i class="fas fa-chevron-right opacity-75"></i> Lihat Detail
                    </a>
                </div>
            @empty
                <div class="text-center py-5 opacity-50" style="color: var(--text-primary);">Belum ada riwayat.</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        try {
            const url = new URL(window.location.href);
            const shouldAutoOpen = url.searchParams.get('start') === '1'
                || @json(session()->has('error'))
                || @json($errors->any());
            if (shouldAutoOpen) {
                const el = document.getElementById('modalMulai');
                if (el && window.bootstrap?.Modal) {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                }
                url.searchParams.delete('start');
                window.history.replaceState({}, document.title, url.toString());
            }
        } catch (e) {}
    })();
</script>
@endpush

















