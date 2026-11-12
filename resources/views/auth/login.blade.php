@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .auth-wrapper {
        min-height: calc(100vh - 120px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px 16px;
    }

    .auth-container {
        width: 100%;
        max-width: 980px;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid var(--border-glass);
        background: var(--card-bg);
        box-shadow: var(--shadow-premium);
        display: grid;
        grid-template-columns: 1.05fr 1.25fr;
    }

    [data-bs-theme="light"] .auth-container {
        border-color: rgba(197, 155, 39, 0.22);
        box-shadow: 0 20px 60px rgba(11, 37, 69, 0.08), 0 1px 3px rgba(11, 37, 69, 0.04);
    }

    /* Left Branding Banner */
    .auth-brand-side {
        position: relative;
        padding: 44px 36px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background:
            radial-gradient(circle at 15% 15%, rgba(197, 155, 39, 0.16), transparent 45%),
            radial-gradient(circle at 85% 85%, rgba(19, 62, 135, 0.35), transparent 50%),
            linear-gradient(145deg, #0b2545 0%, #081a32 100%);
        color: #ffffff;
        overflow: hidden;
    }

    [data-bs-theme="light"] .auth-brand-side {
        background:
            radial-gradient(circle at 10% 10%, rgba(197, 155, 39, 0.22), transparent 40%),
            radial-gradient(circle at 90% 90%, rgba(19, 62, 135, 0.14), transparent 50%),
            linear-gradient(145deg, #0b2545 0%, #133e87 100%);
    }

    .auth-brand-header {
        position: relative;
        z-index: 2;
    }

    .auth-brand-badge {
        width: 68px;
        height: 68px;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(197, 155, 39, 0.35);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .auth-brand-badge img {
        width: 52px;
        height: 52px;
        object-fit: contain;
    }

    .auth-brand-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #f59e0b;
        margin-bottom: 8px;
    }

    .auth-brand-title {
        font-size: 1.85rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        line-height: 1.15;
        color: #ffffff;
        margin-bottom: 12px;
    }

    .auth-brand-desc {
        font-size: 0.92rem;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.82);
    }

    .auth-feature-list {
        position: relative;
        z-index: 2;
        margin: 28px 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .auth-feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.86rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.92);
    }

    .auth-feature-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(197, 155, 39, 0.22);
        border: 1px solid rgba(197, 155, 39, 0.40);
        color: #fbbf24;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.76rem;
        flex-shrink: 0;
    }

    .auth-brand-footer {
        position: relative;
        z-index: 2;
        padding-top: 24px;
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.60);
        letter-spacing: 0.04em;
    }

    /* Right Form Side */
    .auth-form-side {
        padding: 44px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: var(--surface);
    }

    .auth-form-header {
        margin-bottom: 26px;
    }

    .auth-form-title {
        font-size: 1.65rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .auth-form-subtitle {
        font-size: 0.88rem;
        color: var(--text-secondary);
    }

    .auth-input-group {
        position: relative;
        margin-bottom: 18px;
    }

    .auth-input-label {
        font-size: 0.82rem;
        font-weight: 800;
        color: var(--text-secondary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .auth-input-field {
        min-height: 48px;
        padding: 12px 16px !important;
        border-radius: 14px !important;
        font-size: 0.95rem;
        border: 1px solid var(--input-border) !important;
        background: var(--input-bg) !important;
        color: var(--input-text) !important;
        transition: all 0.2s ease;
    }

    .auth-input-field:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent) !important;
    }

    .auth-password-wrapper {
        position: relative;
    }

    .auth-password-toggle {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 0.92rem;
        padding: 4px;
        transition: color 0.15s ease;
    }

    .auth-password-toggle:hover {
        color: var(--text-primary);
    }

    .auth-submit-btn {
        min-height: 50px;
        font-size: 0.98rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 10px 24px color-mix(in srgb, var(--accent) 26%, transparent);
        margin-top: 8px;
        margin-bottom: 20px;
    }

    .auth-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 16px 0;
        color: var(--text-muted);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--divider);
    }

    .auth-footer-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.88rem;
        color: var(--text-secondary);
    }

    .auth-footer-link {
        font-weight: 800;
        color: var(--accent);
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .auth-footer-link:hover {
        color: var(--accent-hover);
        text-decoration: underline;
    }

    /* Responsive Breakpoint */
    @media (max-width: 991px) {
        .auth-container {
            grid-template-columns: 1fr;
            max-width: 480px;
        }

        .auth-brand-side {
            padding: 32px 24px;
            text-align: center;
            align-items: center;
        }

        .auth-brand-header {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .auth-feature-list {
            display: none;
        }

        .auth-brand-footer {
            display: none;
        }

        .auth-form-side {
            padding: 32px 24px;
        }
    }
</style>
@endpush

<div class="auth-wrapper">
    <div class="auth-container">
        <!-- Sisi Kiri: Visual Branding Eksklusif CAT ASN -->
        <div class="auth-brand-side">
            <div class="auth-brand-header">
                <div class="auth-brand-badge">
                    <img src="/icons/cat-logo.png?v=20261008-4" alt="CAT ASN Official Logo">
                </div>
                <div class="auth-brand-kicker">
                    <i class="fas fa-certificate"></i> PORTAL RESMI SELEKSI MANDIRI
                </div>
                <h2 class="auth-brand-title">CAT ASN & CPNS</h2>
                <p class="auth-brand-desc">
                    Standar ujian CAT terkalibrasi BKN dengan ribuan bank soal berkualitas tinggi, pembahasan tuntas, dan perankingan real-time.
                </p>

                <ul class="auth-feature-list">
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon"><i class="fas fa-check"></i></span>
                        <span>Mode SKD Lengkap (TWK, TIU, & TKP)</span>
                    </li>
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon"><i class="fas fa-check"></i></span>
                        <span>SKB Teknis & Formasi Spesifik PPPK</span>
                    </li>
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon"><i class="fas fa-check"></i></span>
                        <span>Analisis Kelemahan & Rekomendasi Mentor AI</span>
                    </li>
                </ul>
            </div>

            <div class="auth-brand-footer">
                &copy; {{ date('Y') }} CAT ASN Simulator • KORPRI Digital Standard
            </div>
        </div>

        <!-- Sisi Kanan: Formulir Masuk -->
        <div class="auth-form-side">
            <div class="auth-form-header">
                <h3 class="auth-form-title">Selamat Datang</h3>
                <div class="auth-form-subtitle">Silakan masukkan akun Anda untuk melanjutkan simulasi.</div>
            </div>

            @if(session('success'))
                <div class="alert border-0 apple-glass-card mb-3 d-flex align-items-center gap-3 p-3" style="background: rgba(13, 148, 136, 0.12); border: 1px solid rgba(13, 148, 136, 0.28) !important; border-radius: 14px;">
                    <i class="fas fa-circle-check text-success fs-5"></i>
                    <div class="small fw-bold" style="color: var(--text-primary);">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert border-0 apple-glass-card mb-3 d-flex align-items-center gap-3 p-3" style="background: rgba(225, 29, 72, 0.10); border: 1px solid rgba(225, 29, 72, 0.24) !important; border-radius: 14px;">
                    <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                    <div class="small fw-bold" style="color: var(--text-primary);">{{ session('error') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="auth-input-group">
                    <label class="auth-input-label" for="loginEmail">Alamat Email</label>
                    <input
                        type="email"
                        id="loginEmail"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="form-control auth-input-field @error('email') is-invalid @enderror"
                        placeholder="nama@email.com"
                        autocomplete="email"
                    >
                    @error('email')
                        <div class="invalid-feedback fw-bold mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-input-group">
                    <label class="auth-input-label" for="loginPassword">
                        <span>Kata Sandi</span>
                    </label>
                    <div class="auth-password-wrapper">
                        <input
                            type="password"
                            id="loginPassword"
                            name="password"
                            required
                            class="form-control auth-input-field @error('password') is-invalid @enderror"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                        >
                        <button type="button" class="auth-password-toggle" id="togglePasswordBtn" title="Tampilkan/Sembunyikan password">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback fw-bold mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="apple-glass-btn apple-btn-primary w-100 auth-submit-btn">
                    <span>Masuk ke Akun</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-divider">atau</div>

            <div class="auth-footer-nav">
                <span>Belum memiliki akun?</span>
                <a href="{{ route('register') }}" class="auth-footer-link">Daftar Akun Baru</a>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('ujian.index') }}" class="text-decoration-none small text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passField = document.getElementById('loginPassword');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passField && toggleIcon) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = passField.type === 'password';
                passField.type = isPassword ? 'text' : 'password';
                toggleIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        }
    });
</script>
@endpush
@endsection
