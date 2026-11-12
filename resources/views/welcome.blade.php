@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .home-hero {
        position: relative;
        border-radius: 32px;
        padding: 56px 44px;
        margin-bottom: 36px;
        overflow: hidden;
        border: 1px solid var(--border-glass);
        background:
            radial-gradient(circle at 85% 15%, rgba(197, 155, 39, 0.16), transparent 45%),
            radial-gradient(circle at 10% 85%, rgba(59, 130, 246, 0.20), transparent 45%),
            linear-gradient(135deg, rgba(15, 23, 42, 0.88), rgba(11, 37, 69, 0.94));
        box-shadow: var(--shadow-premium);
    }

    [data-bs-theme="light"] .home-hero {
        border-color: rgba(197, 155, 39, 0.25);
        background:
            radial-gradient(circle at 90% 10%, rgba(197, 155, 39, 0.14), transparent 42%),
            radial-gradient(circle at 10% 90%, rgba(19, 62, 135, 0.08), transparent 45%),
            linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
        box-shadow: 0 20px 50px rgba(11, 37, 69, 0.07), 0 1px 3px rgba(11, 37, 69, 0.03);
    }

    .home-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(197, 155, 39, 0.15);
        border: 1px solid rgba(197, 155, 39, 0.35);
        color: #d97706;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    [data-bs-theme="dark"] .home-hero-kicker {
        color: #fbbf24;
    }

    .home-hero-title {
        font-size: clamp(2.1rem, 4.5vw, 3.4rem);
        font-weight: 900;
        letter-spacing: -0.03em;
        line-height: 1.12;
        color: var(--text-primary);
        margin-bottom: 18px;
    }

    .home-hero-desc {
        font-size: clamp(1rem, 1.8vw, 1.2rem);
        line-height: 1.6;
        color: var(--text-secondary);
        max-width: 680px;
        margin-bottom: 32px;
    }

    .home-badge-box {
        width: 140px;
        height: 140px;
        border-radius: 36px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid var(--border-glass);
        box-shadow: var(--shadow-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    [data-bs-theme="light"] .home-badge-box {
        background: #ffffff;
        border-color: rgba(197, 155, 39, 0.25);
        box-shadow: 0 16px 36px rgba(11, 37, 69, 0.08);
    }

    .home-badge-box img {
        width: 108px;
        height: 108px;
        object-fit: contain;
    }

    /* Bento Feature Cards */
    .bento-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 36px;
    }

    .bento-card {
        border-radius: 24px;
        padding: 28px;
        border: 1px solid var(--card-border);
        background: var(--card-bg);
        box-shadow: var(--card-shadow);
        transition: transform 0.24s ease, border-color 0.24s ease, box-shadow 0.24s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .bento-card:hover {
        transform: translateY(-3px);
        border-color: rgba(197, 155, 39, 0.32);
        box-shadow: 0 16px 36px rgba(11, 37, 69, 0.08);
    }

    .bento-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 18px;
    }

    .bento-icon-navy {
        background: rgba(19, 62, 135, 0.12);
        color: #133e87;
        border: 1px solid rgba(19, 62, 135, 0.25);
    }

    .bento-icon-gold {
        background: rgba(197, 155, 39, 0.14);
        color: #b45309;
        border: 1px solid rgba(197, 155, 39, 0.30);
    }

    [data-bs-theme="dark"] .bento-icon-navy {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
        border-color: rgba(59, 130, 246, 0.3);
    }

    [data-bs-theme="dark"] .bento-icon-gold {
        background: rgba(245, 158, 11, 0.15);
        color: #facc15;
        border-color: rgba(245, 158, 11, 0.3);
    }

    .bento-icon-emerald {
        background: rgba(13, 148, 136, 0.12);
        color: #0d9488;
        border: 1px solid rgba(13, 148, 136, 0.25);
    }

    .bento-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .bento-desc {
        font-size: 0.88rem;
        line-height: 1.5;
        color: var(--text-secondary);
        margin-bottom: 0;
    }

    @media (max-width: 991px) {
        .home-hero {
            padding: 32px 20px;
            border-radius: 24px;
        }

        .home-badge-box {
            display: none;
        }

        .bento-grid {
            gap: 14px;
        }
    }
</style>
@endpush

<div class="container page-shell">
    <!-- Hero Section -->
    <section class="home-hero">
        <div class="row align-items-center g-4">
            <div class="col-lg-9">
                <div class="home-hero-kicker">
                    <i class="fas fa-certificate"></i> Portal Resmi Seleksi Mandiri
                </div>
                <h1 class="home-hero-title">
                    Simulasi Mandiri CAT ASN & CPNS
                </h1>
                <p class="home-hero-desc">
                    Platform latihan terstandar BKN dengan kisi-kisi resmi MenPAN-RB, ribuan soal berkualitas tinggi, sistem scoring persis ujian asli, dan analisis kelemahan cerdas.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    @auth
                        <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-primary px-4 py-3">
                            <i class="fas fa-play me-2"></i> Masuk ke Dashboard
                        </a>
                        <a href="{{ route('ujian.dashboard') }}" class="apple-glass-btn apple-btn-outline px-4 py-3">
                            <i class="fas fa-ranking-star me-2"></i> Papan Skor & Ranking
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="apple-glass-btn apple-btn-primary px-4 py-3">
                            <i class="fas fa-right-to-bracket me-2"></i> Masuk Sekarang
                        </a>
                        <a href="{{ route('register') }}" class="apple-glass-btn apple-btn-outline px-4 py-3">
                            <i class="fas fa-user-plus me-2"></i> Daftar Akun
                        </a>
                    @endauth
                    <a href="{{ route('guide.index') }}" class="apple-glass-btn apple-btn-ghost px-4 py-3">
                        <i class="fas fa-circle-info me-2"></i> Panduan
                    </a>
                </div>
            </div>

            <div class="col-lg-3 d-none d-lg-flex justify-content-end">
                <div class="home-badge-box">
                    <img src="/icons/cat-logo.png?v=20261008-4" alt="CAT ASN Logo">
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Fitur Unggulan -->
    <div class="bento-grid">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="bento-card h-100">
                <div>
                    <div class="bento-card-icon bento-icon-navy">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="bento-title">SKD Terstandar</h3>
                    <p class="bento-desc">
                        Simulasi lengkap TWK, TIU, dan TKP sesuai passing grade BKN dengan timer resmi 100 menit.
                    </p>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: var(--divider) !important;">
                    <span class="apple-pill muted small">110 Butir Soal</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="bento-card h-100">
                <div>
                    <div class="bento-card-icon bento-icon-gold">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="bento-title">SKB & PPPK Teknis</h3>
                    <p class="bento-desc">
                        Bank soal berbasis kompetensi formasi: Tenaga Pendidik, Kesehatan, Teknis, dan Pranata Komputer.
                    </p>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: var(--divider) !important;">
                    <span class="apple-pill warning small">Formasi Spesifik</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="bento-card h-100">
                <div>
                    <div class="bento-card-icon bento-icon-emerald">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 class="bento-title">Analisis Belajar</h3>
                    <p class="bento-desc">
                        Grafik riwayat skor, statistik sub-topik terlemah, dan panduan target kelulusan per kategori.
                    </p>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: var(--divider) !important;">
                    <span class="apple-pill success small">Real-time Insight</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="bento-card h-100">
                <div>
                    <div class="bento-card-icon bento-icon-navy">
                        <i class="fas fa-mobile-screen"></i>
                    </div>
                    <h3 class="bento-title">PWA & Offline Ready</h3>
                    <p class="bento-desc">
                        Bisa diinstall ke Android atau desktop seperti aplikasi native dengan responsivitas cepat dan ringan.
                    </p>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: var(--divider) !important;">
                    <span class="apple-pill muted small">Versi 3.2.0</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
