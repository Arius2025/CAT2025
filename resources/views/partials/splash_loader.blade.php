@unless($examMode ?? false)
<div id="catAsnSplashLoader" class="cat-splash-overlay" style="display: none;">
    <div class="cat-splash-card">
        <!-- Top Official Kicker -->
        <div class="cat-splash-kicker">
            <span class="cat-splash-kicker-dot"></span>
            <span>PORTAL RESMI SIMULASI ASN</span>
        </div>

        <!-- Center Brand Logo -->
        <div class="cat-splash-badge-wrap">
            <img src="/icons/cat-logo.png?v=20261008-4" alt="CAT ASN Logo" class="cat-splash-logo">
            <div class="cat-splash-glow"></div>
        </div>

        <h1 class="cat-splash-title">CAT ASN</h1>
        
        <div class="cat-splash-version-tag">
            <span>Versi 3.2.0 • Pro Edition</span>
        </div>

        <!-- Progress Bar -->
        <div class="cat-splash-progress-track">
            <div class="cat-splash-progress-fill" id="catSplashProgress"></div>
        </div>

        <!-- Rotating Status Messages -->
        <div class="cat-splash-status" id="catSplashStatus">
            Memulai sistem CAT ASN...
        </div>

        <!-- Official & Tech Ecosystem Badges -->
        <div class="cat-splash-ecosystem">
            <div class="cat-splash-eco-title">DIDUKUNG OLEH EKOSISTEM DIGITAL</div>
            <div class="cat-splash-eco-grid">
                <!-- KORPRI -->
                <div class="cat-splash-eco-item" title="Korps Pegawai Republik Indonesia">
                    <svg class="cat-splash-eco-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L4 6V11C4 16.5 7.5 21.3 12 22.5C16.5 21.3 20 16.5 20 11V6L12 2Z" fill="#f59e0b" fill-opacity="0.2" stroke="#f59e0b" stroke-width="1.8"/>
                        <path d="M12 6L14 10H18L15 13L16 17L12 14.5L8 17L9 13L6 10H10L12 6Z" fill="#f59e0b"/>
                    </svg>
                    <span>KORPRI</span>
                </div>

                <!-- BKN -->
                <div class="cat-splash-eco-item" title="Badan Kepegawaian Negara">
                    <svg class="cat-splash-eco-icon" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="4" width="18" height="16" rx="4" fill="#0284c7" fill-opacity="0.2" stroke="#0284c7" stroke-width="1.8"/>
                        <path d="M7 12L10.5 15.5L17 8.5" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>BKN CAT</span>
                </div>

                <!-- Android -->
                <div class="cat-splash-eco-item" title="Android PWA Ready">
                    <svg class="cat-splash-eco-icon" viewBox="0 0 24 24" fill="#3DDC84">
                        <path d="M6 18c0 .55.45 1 1 1h1v3.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5V19h2v3.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5V19h1c.55 0 1-.45 1-1V8H6v10zM3.5 8C2.67 8 2 8.67 2 9.5v6c0 .83.67 1.5 1.5 1.5S5 16.33 5 15.5v-6C5 8.67 4.33 8 3.5 8zm17 0c-.83 0-1.5.67-1.5 1.5v6c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-6c0-.83-.67-1.5-1.5-1.5zm-4.97-4.84l1.3-1.3c.2-.2.2-.51 0-.71-.2-.2-.51-.2-.71 0l-1.48 1.48C13.85 2.23 12.95 2 12 2c-.96 0-1.86.23-2.66.63L7.85 1.15c-.2-.2-.51-.2-.71 0-.2.2-.2.51 0 .71l1.31 1.3C6.71 4.22 5.5 5.98 5.5 8h13c0-2.02-1.21-3.78-2.97-4.84zM9 6c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm6 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
                    </svg>
                    <span>Android</span>
                </div>

                <!-- Laravel -->
                <div class="cat-splash-eco-item" title="Powered by Laravel Framework">
                    <svg class="cat-splash-eco-icon" viewBox="0 0 24 24" fill="#FF2D20">
                        <path d="M12.001 2.22l8.775 5.066v10.134L12.001 22.486 3.225 17.42V7.286l8.776-5.066zm0 2.31L5.225 9.176l6.776 3.912 6.776-3.912-6.776-4.646zm-7.276 6.347v6.643l6.276 3.623v-6.643L4.725 10.877zm14.552 0l-6.276 3.623v6.643l6.276-3.623v-6.643z"/>
                    </svg>
                    <span>Laravel</span>
                </div>
            </div>
        </div>

        <button type="button" class="cat-splash-skip-btn" id="catSplashSkip">
            Lewati <i class="fas fa-angles-right ms-1"></i>
        </button>
    </div>
</div>

<style>
.cat-splash-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 50% 15%, #1e293b 0%, #070a0f 100%);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    transition: opacity 0.42s cubic-bezier(0.4, 0, 0.2, 1), transform 0.42s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 16px;
    touch-action: manipulation;
}

[data-bs-theme="light"] .cat-splash-overlay {
    background: radial-gradient(circle at 50% 15%, #f8fafc 0%, #e2e8f0 100%);
}

.cat-splash-card {
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 30px 24px 20px;
    border-radius: 28px;
    background: rgba(15, 23, 42, 0.82);
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.05);
    animation: splashPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

[data-bs-theme="light"] .cat-splash-card {
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
}

@keyframes splashPop {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}

.cat-splash-kicker {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 4px 12px;
    border-radius: 999px;
    background: rgba(56, 189, 248, 0.10);
    border: 1px solid rgba(56, 189, 248, 0.25);
    color: #38bdf8;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 18px;
}

.cat-splash-kicker-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #38bdf8;
    box-shadow: 0 0 8px #38bdf8;
}

.cat-splash-badge-wrap {
    position: relative;
    width: 104px;
    height: 104px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.cat-splash-logo {
    width: 94px;
    height: 94px;
    object-fit: contain;
    position: relative;
    z-index: 2;
    filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.4));
    animation: logoPulse 2.4s ease-in-out infinite;
}

@keyframes logoPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.cat-splash-glow {
    position: absolute;
    inset: -12px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.28) 0%, rgba(37, 99, 235, 0.15) 50%, transparent 70%);
    border-radius: 50%;
    z-index: 1;
    animation: glowPulse 2s ease-in-out infinite alternate;
}

@keyframes glowPulse {
    from { opacity: 0.45; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1.18); }
}

.cat-splash-title {
    font-size: 1.7rem;
    font-weight: 800;
    color: #f8fafc;
    letter-spacing: -0.02em;
    margin-bottom: 4px;
}

[data-bs-theme="light"] .cat-splash-title {
    color: #0f172a;
}

.cat-splash-version-tag {
    display: inline-flex;
    align-items: center;
    padding: 3px 12px;
    border-radius: 999px;
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: #fbbf24;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 22px;
}

[data-bs-theme="light"] .cat-splash-version-tag {
    background: rgba(217, 119, 6, 0.1);
    border-color: rgba(217, 119, 6, 0.25);
    color: #b45309;
}

.cat-splash-progress-track {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 12px;
    position: relative;
}

[data-bs-theme="light"] .cat-splash-progress-track {
    background: rgba(15, 23, 42, 0.08);
}

.cat-splash-progress-fill {
    height: 100%;
    width: 0%;
    border-radius: 999px;
    background: linear-gradient(90deg, #0284c7 0%, #38bdf8 50%, #f59e0b 100%);
    box-shadow: 0 0 12px rgba(56, 189, 248, 0.6);
    transition: width 0.32s cubic-bezier(0.4, 0, 0.2, 1);
}

.cat-splash-status {
    font-size: 0.84rem;
    font-weight: 600;
    color: #94a3b8;
    min-height: 22px;
    margin-bottom: 20px;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

[data-bs-theme="light"] .cat-splash-status {
    color: #475569;
}

/* Ecosystem Bar */
.cat-splash-ecosystem {
    width: 100%;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    margin-bottom: 14px;
}

[data-bs-theme="light"] .cat-splash-ecosystem {
    border-top-color: rgba(15, 23, 42, 0.08);
}

.cat-splash-eco-title {
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: #64748b;
    margin-bottom: 10px;
}

.cat-splash-eco-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.cat-splash-eco-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    padding: 8px 4px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    transition: transform 0.18s ease;
}

[data-bs-theme="light"] .cat-splash-eco-item {
    background: rgba(15, 23, 42, 0.03);
    border-color: rgba(15, 23, 42, 0.06);
}

.cat-splash-eco-icon {
    width: 20px;
    height: 20px;
}

.cat-splash-eco-item span {
    font-size: 0.64rem;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.01em;
}

[data-bs-theme="light"] .cat-splash-eco-item span {
    color: #475569;
}

.cat-splash-skip-btn {
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: color 0.15s ease;
}

@media (min-width: 992px) {
    .cat-splash-overlay {
        display: none !important;
    }
}
</style>

<script>
(function() {
    // 1. Pastikan hanya berjalan di tampilan perangkat mobile / tablet (lebar layar < 992px)
    const isMobileViewport = window.innerWidth < 992 || (window.matchMedia && window.matchMedia('(max-width: 991.98px)').matches);
    if (!isMobileViewport) {
        return;
    }

    // 2. Pastikan HANYA muncul saat pertama kali aplikasi dibuka (tidak muncul lagi saat pindah halaman)
    // Menggunakan localStorage agar persist saat navigasi & sesi berlangsung
    const SPLASH_STORAGE_KEY = 'cat_asn_splash_first_launch_v32';
    if (localStorage.getItem(SPLASH_STORAGE_KEY) === '1' || sessionStorage.getItem(SPLASH_STORAGE_KEY) === '1') {
        return;
    }

    const overlay = document.getElementById('catAsnSplashLoader');
    const progressBar = document.getElementById('catSplashProgress');
    const statusText = document.getElementById('catSplashStatus');
    const skipBtn = document.getElementById('catSplashSkip');

    if (!overlay || !progressBar || !statusText) return;

    // Tampilkan loader hanya untuk peluncuran pertama kali di mobile
    overlay.style.display = 'flex';

    const steps = [
        { pct: 28, msg: 'Memeriksa integritas sistem & database...' },
        { pct: 58, msg: 'Menyiapkan modul bank soal SKD, SKB, & PPPK...' },
        { pct: 86, msg: 'Sinkronisasi standar CAT BKN & panduan KORPRI...' },
        { pct: 100, msg: 'Sistem siap digunakan. Selamat belajar!' }
    ];

    let currentStep = 0;
    let isDismissed = false;

    function dismissSplash() {
        if (isDismissed) return;
        isDismissed = true;
        
        // Tandai sudah pernah dilihat sehingga navigasi / pindah halaman berikutnya tidak pernah menampilkan loading ini
        try {
            localStorage.setItem(SPLASH_STORAGE_KEY, '1');
            sessionStorage.setItem(SPLASH_STORAGE_KEY, '1');
        } catch (e) {}

        overlay.style.opacity = '0';
        overlay.style.transform = 'scale(1.02)';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 400);
    }

    function runNextStep() {
        if (isDismissed) return;
        if (currentStep < steps.length) {
            const step = steps[currentStep];
            progressBar.style.width = step.pct + '%';
            
            statusText.style.opacity = '0';
            statusText.style.transform = 'translateY(4px)';
            setTimeout(() => {
                statusText.textContent = step.msg;
                statusText.style.opacity = '1';
                statusText.style.transform = 'translateY(0)';
            }, 140);

            currentStep++;
            const delay = currentStep === steps.length ? 440 : 480;
            setTimeout(runNextStep, delay);
        } else {
            setTimeout(dismissSplash, 320);
        }
    }

    if (skipBtn) {
        skipBtn.addEventListener('click', dismissSplash);
    }
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) dismissSplash();
    });

    setTimeout(runNextStep, 180);
})();
</script>
@endunless
