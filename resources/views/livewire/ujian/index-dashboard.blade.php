@push('styles')
<style>
    /* Human-Crafted Institutional Palette (CAT ASN & KORPRI Standard) */
    :root {
        --dash-navy: #0B2545;
        --dash-navy-light: #133E87;
        --dash-gold: #C59B27;
        --dash-gold-hover: #b0881e;
        --dash-amber: #D97706;
        --dash-surface: #FFFFFF;
        --dash-surface-soft: #F8FAFC;
        --dash-border: rgba(11, 37, 69, 0.08);
        --dash-border-gold: rgba(197, 155, 39, 0.28);
        --dash-shadow: 0 4px 20px -2px rgba(11, 37, 69, 0.06), 0 2px 6px -1px rgba(11, 37, 69, 0.03);
        --dash-shadow-lg: 0 12px 32px -4px rgba(11, 37, 69, 0.08), 0 4px 12px -2px rgba(11, 37, 69, 0.04);
    }

    [data-bs-theme="dark"] {
        --dash-navy: #0f172a;
        --dash-navy-light: #1e293b;
        --dash-surface: #0f172a;
        --dash-surface-soft: #1e293b;
        --dash-border: rgba(255, 255, 255, 0.10);
        --dash-border-gold: rgba(197, 155, 39, 0.35);
        --dash-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        --dash-shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.5);
    }

    /* Page Layout */
    .dashboard-shell {
        padding-top: 1.25rem;
        padding-bottom: 3.5rem;
    }

    /* Executive Hero Card */
    .exec-hero {
        border-radius: 24px;
        background: linear-gradient(135deg, #0B2545 0%, #133E87 100%);
        color: #ffffff;
        padding: 36px 32px;
        margin-bottom: 28px;
        box-shadow: 0 16px 36px -6px rgba(11, 37, 69, 0.25);
        border: 1px solid rgba(197, 155, 39, 0.35);
        position: relative;
        overflow: hidden;
    }

    .exec-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(197, 155, 39, 0.20);
        border: 1px solid rgba(197, 155, 39, 0.45);
        color: #FBBF24;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 14px;
    }

    .exec-hero-title {
        font-size: clamp(1.45rem, 3.2vw, 2.15rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 10px;
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .exec-hero-subtitle {
        font-size: 0.98rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.6;
        max-width: 580px;
        margin-bottom: 24px;
    }

    /* Primary CTA Button */
    .btn-exec-primary {
        background: linear-gradient(135deg, #C59B27 0%, #D97706 100%);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 14px;
        font-weight: 800;
        font-size: 0.95rem;
        padding: 12px 24px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 6px 18px rgba(197, 155, 39, 0.35);
        text-decoration: none;
    }
    .btn-exec-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(197, 155, 39, 0.45);
        color: #ffffff !important;
    }

    .btn-exec-secondary {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 12px 22px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
        text-decoration: none;
        backdrop-filter: blur(8px);
    }
    .btn-exec-secondary:hover {
        background: rgba(255, 255, 255, 0.20);
        color: #ffffff !important;
        transform: translateY(-2px);
    }

    /* KPI Mini Cards inside Hero */
    .exec-stat-card {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 16px 18px;
        transition: transform 0.2s ease, background 0.2s ease;
        backdrop-filter: blur(10px);
        height: 100%;
    }
    .exec-stat-card:hover {
        background: rgba(255, 255, 255, 0.14);
        transform: translateY(-2px);
    }
    .exec-stat-label {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 4px;
    }
    .exec-stat-value {
        font-size: clamp(1.25rem, 2.5vw, 1.65rem);
        font-weight: 900;
        letter-spacing: -0.02em;
        color: #ffffff;
        line-height: 1.1;
    }

    /* General Card Component */
    .human-card {
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--dash-border);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--dash-shadow);
    }
    [data-bs-theme="light"] .human-card {
        border-color: rgba(11, 37, 69, 0.08);
    }
    [data-bs-theme="dark"] .human-card {
        background: #1e293b;
        border-color: rgba(255, 255, 255, 0.08);
    }

    .human-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(197, 155, 39, 0.12);
    }

    .human-card-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Active Exam Priority Card */
    .active-session-banner {
        background: linear-gradient(135deg, rgba(11, 37, 69, 0.03) 0%, rgba(197, 155, 39, 0.06) 100%);
        border: 1px solid var(--dash-border-gold);
        border-left: 5px solid #C59B27;
        border-radius: 18px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    @media (min-width: 768px) {
        .active-session-banner {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    /* Navigation Grid Tiles (Fluid Auto-fill) */
    .quick-tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 170px), 1fr));
        gap: 14px;
    }

    .tool-tile {
        background: var(--dash-surface-soft);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        text-decoration: none;
        color: var(--text-primary);
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                    border-color 0.2s ease,
                    color 0.2s ease;
    }
    .tool-tile:hover {
        transform: translateY(-3px);
        border-color: rgba(197, 155, 39, 0.45);
        box-shadow: 0 10px 24px -4px rgba(11, 37, 69, 0.08);
        color: var(--dash-navy-light);
    }
    .tool-tile-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(19, 62, 135, 0.08);
        color: var(--dash-navy-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    [data-bs-theme="dark"] .tool-tile-icon {
        background: rgba(255, 255, 255, 0.08);
        color: #FBBF24;
    }

    /* Bank Soal Tracker */
    .tracker-card {
        border-radius: 16px;
        padding: 16px 18px;
        background: var(--dash-surface-soft);
        border: 1px solid var(--dash-border);
        height: 100%;
    }
    .tracker-bar {
        height: 8px;
        border-radius: 999px;
        background: rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin: 10px 0 6px;
    }
    [data-bs-theme="dark"] .tracker-bar {
        background: rgba(255, 255, 255, 0.08);
    }
    .tracker-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #133E87 0%, #C59B27 100%);
    }

    /* Data Table & Mobile List */
    .data-table-container {
        border-radius: 16px;
        border: 1px solid var(--dash-border);
        overflow: hidden;
    }
    .human-table th {
        background: rgba(11, 37, 69, 0.04);
        color: var(--text-secondary);
        font-weight: 800;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 14px 18px;
        border: none;
    }
    [data-bs-theme="dark"] .human-table th {
        background: rgba(255, 255, 255, 0.04);
    }
    .human-table td {
        padding: 16px 18px;
        vertical-align: middle;
        border-color: var(--dash-border);
        color: var(--text-primary);
    }

    .badge-mode {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.76rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .badge-mode-skd {
        background: rgba(19, 62, 135, 0.10);
        color: #133E87;
        border: 1px solid rgba(19, 62, 135, 0.20);
    }
    .badge-mode-skb {
        background: rgba(197, 155, 39, 0.12);
        color: #C59B27;
        border: 1px solid rgba(197, 155, 39, 0.25);
    }
    .badge-mode-pppk {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    [data-bs-theme="dark"] .badge-mode-skd { color: #60A5FA; }
    [data-bs-theme="dark"] .badge-mode-skb { color: #FBBF24; }
    [data-bs-theme="dark"] .badge-mode-pppk { color: #34D399; }

    @media (max-width: 767px) {
        .exec-hero {
            padding: 24px 20px;
            border-radius: 20px;
        }
        .exec-hero-title {
            font-size: 1.6rem;
        }
        .human-card {
            padding: 18px;
            border-radius: 18px;
        }
    }
</style>
@endpush

<div class="container dashboard-shell">
    {{-- Alert Master Admin (Jika ada persetujuan user pending) --}}
    @if(auth()->user()?->isMasterAdmin() && (int) ($masterAdminAlerts['pending_users'] ?? 0) > 0)
        <div class="alert border-0 mb-4 d-flex align-items-center justify-content-between p-3 rounded-4" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25) !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 rounded-3 bg-warning text-dark"><i class="fas fa-user-clock"></i></div>
                <div>
                    <strong style="color: var(--text-primary);">Terdapat {{ (int) ($masterAdminAlerts['pending_users'] ?? 0) }} pendaftaran akun menunggu verifikasi</strong>
                    <div class="small text-secondary">Buka modul Akses Pengguna untuk mengonfirmasi akun baru.</div>
                </div>
            </div>
            <a href="{{ route('admin.user-access.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning fw-bold px-3 py-2 rounded-pill">
                Tinjau Akun
            </a>
        </div>
    @endif

    {{-- Executive Welcome Hero --}}
    <div class="exec-hero">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="exec-hero-badge">
                    <i class="fas fa-shield-halved"></i> Standar Kisi-Kisi CASN 2027 (KemenPAN-RB & BKN)
                </div>
                <h1 class="exec-hero-title">
                    Selamat Datang, {{ Auth::user()->name }}!
                </h1>
                <p class="exec-hero-subtitle">
                    Tingkatkan kesiapan seleksi CASN 2027 Anda melalui bank soal terstandar kisi-kisi resmi BKN. Mengadopsi penalaran HOTS studi kasus untuk TWK &amp; TIU serta Situational Judgment Test (SJT) pembobotan 1-5 untuk TKP.
                </p>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge px-3 py-2 rounded-pill font-monospace" style="background: rgba(16, 185, 129, 0.20); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 0.78rem;">
                        <i class="fas fa-circle-check me-1"></i> TWK: HOTS (PG 65)
                    </span>
                    <span class="badge px-3 py-2 rounded-pill font-monospace" style="background: rgba(59, 130, 246, 0.20); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 0.78rem;">
                        <i class="fas fa-circle-check me-1"></i> TIU: PG 80
                    </span>
                    <span class="badge px-3 py-2 rounded-pill font-monospace" style="background: rgba(245, 158, 11, 0.20); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 0.78rem;">
                        <i class="fas fa-circle-check me-1"></i> TKP: SJT Bobot 1-5 (PG 166)
                    </span>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <button class="btn-exec-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalMulai">
                        <i class="fas fa-play"></i> Mulai Ujian Baru
                    </button>
                    <a href="{{ route('analysis.index') }}" class="btn-exec-secondary">
                        <i class="fas fa-chart-pie"></i> Evaluasi Belajar
                    </a>
                </div>
            </div>

            {{-- 4 Kolom KPI Ringkasan Peserta --}}
            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="exec-stat-card">
                            <div class="exec-stat-label">Soal Diselesaikan</div>
                            <div class="exec-stat-value">{{ number_format($stats['total_soal_selesai']) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="exec-stat-card">
                            <div class="exec-stat-label">Skor Tertinggi</div>
                            <div class="exec-stat-value">{{ $stats['skor_tertinggi'] }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('bookmark.index') }}" class="exec-stat-card d-block text-decoration-none">
                            <div class="exec-stat-label">Soal Disimpan</div>
                            <div class="exec-stat-value">{{ number_format($stats['bookmark_count'] ?? 0) }}</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <div class="exec-stat-card">
                            <div class="exec-stat-label">Rata-rata Skor</div>
                            <div class="exec-stat-value">{{ $stats['rata_rata'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Banner Sesi Ujian Aktif (Prioritas Tinggi jika ada sesi berjalan) --}}
    @php
        $activeExam = $progresUjian->first();
    @endphp
    @if($activeExam)
        @php
            $mins = floor($activeExam->sisa_waktu / 60);
            $secs = $activeExam->sisa_waktu % 60;
            $modeName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'exam_mode_id') && $activeExam->examMode) ? $activeExam->examMode->name : 'Mode default';
            $subName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'sub_category_id') && $activeExam->subCategory) ? $activeExam->subCategory->name : null;
        @endphp
        <div class="active-session-banner">
            <div class="d-flex align-items-start gap-3">
                <div class="p-3 rounded-4 bg-warning bg-opacity-20 text-warning d-none d-sm-block">
                    <i class="fas fa-hourglass-half fa-2x"></i>
                </div>
                <div>
                    <span class="badge bg-warning text-dark fw-bold mb-1">SESI SEDANG BERLANGSUNG</span>
                    <h3 class="h5 fw-bold mb-1" style="color: var(--text-primary);">
                        Paket {{ $activeExam->paket }} ({{ $modeName }}{{ $subName ? ' - ' . $subName : '' }})
                    </h3>
                    <div class="small text-secondary font-monospace">
                        <i class="far fa-clock me-1"></i> Sisa Waktu Pengerjaan: <strong class="text-danger">{{ $mins }} Menit {{ $secs }} Detik</strong>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <a href="{{ route('ujian.show', $activeExam->id) }}" class="btn-exec-primary py-2 px-4 fs-6">
                    <i class="fas fa-arrow-right"></i> Lanjutkan Sesi
                </a>
                <form action="{{ route('ujian.destroy', $activeExam->id) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus sesi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger p-2 rounded-3" title="Hapus sesi aktif" style="width: 44px; height: 44px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Akses Cepat Fitur Peserta --}}
    <div class="human-card">
        <div class="human-card-header">
            <h2 class="human-card-title">
                <i class="fas fa-compass text-warning"></i> Menu & Fasilitas Belajar
            </h2>
            <span class="small text-secondary">Navigasi Terpadu</span>
        </div>
        <div class="quick-tools-grid">
            <a href="{{ route('ujian.dashboard') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-ranking-star"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Ranking Mandiri</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Statistik skor & perkembangan latihan pribadi.</div>
            </a>
            <a href="{{ route('ujian.leaderboard') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-trophy"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Klasemen Nasional</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Peringkat perolehan skor terbaik antar peserta.</div>
            </a>
            <a href="{{ route('analysis.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Analisis Kemampuan</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Diagnosis materi kuat dan area perlu perbaikan.</div>
            </a>
            <a href="{{ route('coach.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Mentor Belajar</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Rekomendasi prioritas dan jadwal simulasi harian.</div>
            </a>
            <a href="{{ route('bookmark.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-bookmark"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Koleksi Soal Favorit</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Butir soal bertanda untuk dipelajari ulang.</div>
            </a>
            <a href="{{ route('guide.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-circle-info"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Panduan & Regulasi</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Passing grade BKN dan mekanisme CAT ASN.</div>
            </a>
        </div>
    </div>

    {{-- Akses Khusus Pengelola / Admin (Hanya tampil jika user punya hak) --}}
    @if(auth()->user()?->isMasterAdmin() || auth()->user()?->role === 'admin' || auth()->user()?->menuAllowed('admin_bank'))
    <div class="human-card" style="border-left: 4px solid #133E87;">
        <div class="human-card-header">
            <h2 class="human-card-title">
                <i class="fas fa-screwdriver-wrench text-primary"></i> Modul Manajemen Administrator
            </h2>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1 rounded-pill font-monospace">
                PANEL ADMIN
            </span>
        </div>
        <div class="quick-tools-grid">
            <a href="{{ route('admin.soal.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Generator Soal AI</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Produksi butir soal terstandar kisi-kisi BKN.</div>
            </a>
            <a href="{{ route('admin.soal-bank.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-database"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Bank Soal Induk</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Katalog soal, penyuntingan kunci, dan taksonomi.</div>
            </a>
            <a href="{{ route('admin.audit-soal.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-shield-halved"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Audit Kualitas</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Verifikasi akurasi naskah dan telaah AI.</div>
            </a>
            <a href="{{ route('admin.exam-structure.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-sitemap"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Struktur Ujian</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Konfigurasi mode seleksi dan formasi teknis.</div>
            </a>
            <a href="{{ route('admin.user-access.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-users-gear"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Akses Pengguna</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Persetujuan registrasi dan pengaturan perizinan.</div>
            </a>
            <a href="{{ route('admin.api-tokens.index') }}" class="tool-tile">
                <div class="tool-tile-icon"><i class="fas fa-key"></i></div>
                <div class="fw-bold" style="color: var(--text-primary);">Token API</div>
                <div class="small text-secondary" style="font-size: 0.78rem;">Integrasi autentikasi pengujian aplikasi seluler.</div>
            </a>
        </div>
    </div>
    @endif

    {{-- Status Rotasi Bank Soal Mandiri --}}
    @if(isset($bankSoalStats))
        <div class="human-card">
            <div class="human-card-header">
                <div>
                    <h2 class="human-card-title">
                        <i class="fas fa-rotate text-info"></i> Ketersediaan Bank Soal Standar CASN 2027 (Anti-Pengulangan)
                    </h2>
                    <small class="text-secondary">Sistem memprioritaskan butir soal terverifikasi standar resmi 2027 pada setiap sesi simulasi mandiri.</small>
                </div>
            </div>
            <div class="row g-3">
                @foreach($bankSoalStats as $kat => $s)
                    @php
                        $total = (int) ($s['total'] ?? 0);
                        $used = (int) ($s['used'] ?? 0);
                        $fresh = (int) ($s['fresh'] ?? 0);
                        $pct = $total > 0 ? max(0, min(100, (int) round(($fresh / $total) * 100))) : 0;
                    @endphp
                    <div class="col-md-4">
                        <div class="tracker-card">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold" style="color: var(--text-primary);">Kategori {{ $kat }}</span>
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">{{ $fresh }} Belum Dikerjakan</span>
                            </div>
                            <div class="tracker-bar">
                                <div class="tracker-fill" style="width: {{ $pct }}%;"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-secondary mt-1" style="font-size: 0.78rem;">
                                <span>Total Unik: {{ $total }}</span>
                                <span>Pernah Diuji: {{ $used }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bagian Ujian Aktif & Riwayat Ujian --}}
    <div class="row g-4">
        {{-- Sesi Berlangsung --}}
        <div class="col-lg-6">
            <div class="human-card h-100">
                <div class="human-card-header">
                    <h2 class="human-card-title">
                        <i class="fas fa-list-check text-warning"></i> Sesi Ujian Aktif
                    </h2>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-1 rounded-pill font-monospace">
                        {{ $progresUjian->count() }} Sesi
                    </span>
                </div>

                @if($progresUjian->isEmpty())
                    <div class="p-4 text-center text-secondary rounded-3" style="background: rgba(0,0,0,0.02); border: 1px dashed var(--dash-border);">
                        <i class="fas fa-circle-check fa-2x mb-2 opacity-50 text-success"></i>
                        <div class="fw-bold small" style="color: var(--text-primary);">Tidak Ada Sesi yang Tertunda</div>
                        <div class="small mt-1">Tekan tombol "Mulai Ujian Baru" untuk mengawali simulasi.</div>
                    </div>
                @else
                    <div class="vstack gap-3">
                        @foreach($progresUjian as $ujian)
                            @php
                                $mins = floor($ujian->sisa_waktu / 60);
                                $secs = $ujian->sisa_waktu % 60;
                                $modeName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'exam_mode_id') && $ujian->examMode) ? $ujian->examMode->name : 'Mode default';
                                $subName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'sub_category_id') && $ujian->subCategory) ? $ujian->subCategory->name : null;
                                $modeCode = strtolower((string) ($ujian->examMode->code ?? ''));
                                $badgeClass = $modeCode === 'skb' ? 'badge-mode-skb' : ($modeCode === 'pppk' ? 'badge-mode-pppk' : 'badge-mode-skd');
                            @endphp
                            <div class="p-3 rounded-3" style="background: var(--dash-surface-soft); border: 1px solid var(--dash-border);">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge {{ $badgeClass }}">{{ $modeName }}</span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">Paket {{ $ujian->paket }}</span>
                                        </div>
                                        <div class="fw-bold" style="color: var(--text-primary);">
                                            {{ $ujian->nama_peserta }}
                                        </div>
                                        @if($subName)
                                            <div class="small text-secondary">{{ $subName }}</div>
                                        @endif
                                        <div class="small text-secondary mt-1 font-monospace">
                                            <i class="far fa-clock me-1 text-danger"></i> Sisa Waktu: <strong>{{ $mins }}m {{ $secs }}d</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('ujian.show', $ujian->id) }}" class="btn btn-sm btn-primary px-3 py-2 fw-bold rounded-pill">
                                            Lanjut
                                        </a>
                                        <form action="{{ route('ujian.destroy', $ujian->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus progres ujian ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-2 rounded-circle" style="width: 34px; height: 34px;" title="Batalkan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Riwayat Ujian Terakhir --}}
        <div class="col-lg-6" id="riwayat-section">
            <div class="human-card h-100">
                <div class="human-card-header">
                    <h2 class="human-card-title">
                        <i class="fas fa-clock-rotate-left text-primary"></i> Riwayat Simulasi Terakhir
                    </h2>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-1 rounded-pill font-monospace">
                        Selesai
                    </span>
                </div>

                @if($recentActivity->isEmpty())
                    <div class="p-4 text-center text-secondary rounded-3" style="background: rgba(0,0,0,0.02); border: 1px dashed var(--dash-border);">
                        <i class="fas fa-history fa-2x mb-2 opacity-50"></i>
                        <div class="fw-bold small" style="color: var(--text-primary);">Belum Ada Riwayat Ujian</div>
                        <div class="small mt-1">Hasil perolehan nilai ujian yang telah selesai akan dicatat di sini.</div>
                    </div>
                @else
                    <div class="vstack gap-3">
                        @foreach($recentActivity as $act)
                            @php
                                $modeName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'exam_mode_id') && $act->examMode) ? $act->examMode->name : 'Mode default';
                                $subName = (\Illuminate\Support\Facades\Schema::hasColumn('ujians', 'sub_category_id') && $act->subCategory) ? $act->subCategory->name : null;
                                $modeCode = strtolower((string) ($act->examMode->code ?? ''));
                                $badgeClass = $modeCode === 'skb' ? 'badge-mode-skb' : ($modeCode === 'pppk' ? 'badge-mode-pppk' : 'badge-mode-skd');
                                $isLulus = (bool) $act->is_lulus;
                            @endphp
                            <div class="p-3 rounded-3" style="background: var(--dash-surface-soft); border: 1px solid var(--dash-border);">
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge {{ $badgeClass }}">{{ $modeName }}</span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">Paket {{ $act->paket }}</span>
                                            <span class="badge {{ $isLulus ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-warning bg-opacity-10 text-warning border border-warning' }}">
                                                {{ $isLulus ? 'MEMENUHI NILAI AMBANG BATAS' : 'BELUM MEMENUHI' }}
                                            </span>
                                        </div>
                                        <div class="fw-bold fs-6" style="color: var(--text-primary);">
                                            Perolehan Skor Total: <strong class="text-primary">{{ $act->total_skor }}</strong>
                                        </div>
                                        <div class="small text-secondary mt-1">
                                            <i class="far fa-calendar-check me-1"></i> Diselesaikan pada: {{ $act->updated_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('ujian.hasil', $act->id) }}" class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-pill fw-bold">
                                            Rincian <i class="fas fa-chevron-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
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
