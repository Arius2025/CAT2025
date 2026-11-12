@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .profile-card {
        border-radius: 24px;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        padding: 40px 20px 60px 20px;
        position: relative;
        text-align: center;
    }

    @media (min-width: 768px) {
        .profile-header {
            padding: 50px 50px 70px 50px;
            text-align: left;
        }
    }
    
    .profile-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 24px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 800;
        border: 4px solid var(--card-surface);
        position: absolute;
        bottom: -50px;
        left: 50%;
        transform: translateX(-50%);
        box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        z-index: 5;
    }

    @media (min-width: 768px) {
        .profile-avatar-large {
            left: 50px;
            transform: none;
        }
    }
    
    .form-label-custom {
        color: var(--text-secondary);
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 1px;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .section-title-custom {
        position: relative;
        padding-left: 15px;
        margin-bottom: 25px;
    }
    .section-title-custom::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0; width: 4px;
        background: var(--accent-blue);
        border-radius: 2px;
    }
    .preference-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }
    .preference-card {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid var(--border-glass);
        background: rgba(255,255,255,0.03);
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .preference-card:hover {
        transform: translateY(-1px);
        border-color: rgba(56,189,248,0.22);
    }
    .preference-card.is-active {
        border-color: rgba(56,189,248,0.30);
        background: linear-gradient(135deg, rgba(34,211,238,0.12), rgba(59,130,246,0.10));
        box-shadow: 0 16px 28px rgba(15,23,42,0.12);
    }
    .preference-card input {
        margin-top: 3px;
    }
    .preference-card__title {
        font-weight: 800;
        color: var(--text-primary);
    }
    .preference-card__hint {
        display: block;
        margin-top: 4px;
        color: var(--text-secondary);
        font-size: 0.83rem;
        line-height: 1.45;
    }
    .preference-card.is-hidden {
        display: none;
    }
    .theme-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }
    .theme-preview-card {
        border-radius: 20px;
        border: 1px solid var(--border-glass);
        padding: 16px;
        background: rgba(255,255,255,0.04);
        cursor: pointer;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .theme-preview-card:hover {
        transform: translateY(-2px);
        border-color: rgba(56,189,248,0.24);
    }
    .theme-preview-card.is-active {
        border-color: rgba(56,189,248,0.35);
        box-shadow: 0 16px 28px rgba(15,23,42,0.14);
    }
    .theme-preview-thumb {
        height: 88px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.22);
        padding: 10px;
        display: grid;
        align-content: space-between;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .theme-preview-thumb__bar {
        width: 55%;
        height: 10px;
        border-radius: 999px;
        background: rgba(255,255,255,0.72);
    }
    .theme-preview-thumb__row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .theme-preview-thumb__card {
        height: 38px;
        border-radius: 12px;
        background: rgba(255,255,255,0.40);
        border: 1px solid rgba(255,255,255,0.28);
    }
    .theme-preview-card small {
        display: block;
        color: var(--text-secondary);
        line-height: 1.45;
    }
    .theme-preview-card[data-theme-key="light"] .theme-preview-thumb { background: linear-gradient(135deg, #f8fafc, #e2e8f0); }
    .theme-preview-card[data-theme-key="dark"] .theme-preview-thumb { background: linear-gradient(135deg, #0f172a, #1e293b); }
    .theme-preview-card[data-theme-key="soft-blue"] .theme-preview-thumb { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
    .theme-preview-card[data-theme-key="soft-green"] .theme-preview-thumb { background: linear-gradient(135deg, #ecfdf5, #dcfce7); }
    .theme-preview-card[data-theme-key="soft-purple"] .theme-preview-thumb { background: linear-gradient(135deg, #faf5ff, #ede9fe); }
    .theme-preview-card[data-theme-key="apple-glass-light"] .theme-preview-thumb { background: linear-gradient(135deg, rgba(255,255,255,.95), rgba(226,232,240,.90)); backdrop-filter: blur(10px); }
    .theme-preview-card[data-theme-key="apple-glass-dark"] .theme-preview-thumb { background: linear-gradient(135deg, rgba(15,23,42,.96), rgba(30,41,59,.82)); }
    .theme-preview-card[data-theme-key="one-ui-light"] .theme-preview-thumb { background: linear-gradient(135deg, #f6f7fb, #eef1f6); }
    .theme-preview-card[data-theme-key="one-ui-dark"] .theme-preview-thumb { background: linear-gradient(135deg, #121316, #1b1d22); }
    .theme-preview-card[data-theme-key="nothing-os"] .theme-preview-thumb { background: linear-gradient(135deg, #111111, #232323); }
    .theme-preview-card[data-theme-key="pixel-ui"] .theme-preview-thumb { background: linear-gradient(135deg, #faf5ff, #ddd6fe); }
</style>
@endpush

<div class="py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            
            @if(session('success'))
                <div class="apple-glass apple-glass-card apple-alert-inline mb-4" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.24);">
                    <i class="fas fa-check-circle text-success"></i>
                    <div><strong>Berhasil.</strong> {{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="apple-glass apple-glass-card apple-alert-inline mb-4" style="background: rgba(239, 68, 68, 0.10); border: 1px solid rgba(239, 68, 68, 0.20);">
                    <i class="fas fa-triangle-exclamation text-danger"></i>
                    <div>
                        <strong>Masih ada yang perlu dibenahi.</strong>
                        <div class="apple-hint mt-1" style="color: var(--text-primary) !important; opacity: .85;">{{ $errors->first() }}</div>
                    </div>
                </div>
            @endif

            <div class="profile-card apple-glass apple-glass-card">
                <div class="profile-header">
                    <h2 class="fw-800 mb-2" style="color: var(--text-primary);">Pengaturan Profil</h2>
                    <p class="mb-0 apple-subtitle">Kelola informasi identitas dan keamanan akun Anda</p>
                    <div class="profile-avatar-large">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                
                <div class="p-4 p-md-5 pt-5">
                    <form id="profileForm" action="{{ route('profile.update') }}" method="POST" class="mt-4 mt-md-0">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="theme" id="profileThemeInput" value="{{ old('theme', $activeThemeSlug ?? $user->theme ?? '') }}">
                        
                        <div class="section-title-custom">
                            <h5 class="fw-800 m-0" style="color: var(--text-primary);">Informasi Dasar</h5>
                        </div>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label-custom">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control apple-glass-input" value="{{ old('name', $user->name) }}" required placeholder="Masukkan nama lengkap">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Alamat Email</label>
                                <input type="email" name="email" class="form-control apple-glass-input" value="{{ old('email', $user->email) }}" required placeholder="email@contoh.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Nomor WhatsApp</label>
                                <input type="text" name="no_hp" class="form-control apple-glass-input" value="{{ old('no_hp', $user->no_hp) }}" required placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Asal Instansi</label>
                                <input type="text" name="instansi" class="form-control apple-glass-input" value="{{ old('instansi', $user->instansi) }}" required placeholder="Nama instansi/sekolah">
                            </div>
                        </div>

                        <div class="section-title-custom">
                            <h5 class="fw-800 m-0" style="color: var(--text-primary);">Preferensi Latihan</h5>
                        </div>

                        <div class="apple-glass-soft rounded-4 p-3 p-md-4 mb-5">
                            <div class="apple-hint mb-3">Pilih jalur dan formasi yang paling relevan buatmu. Nanti pilihan menu dan latihan bisa dibuat lebih ringkas.</div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="apple-pill muted" id="profileModeCount">Belum ada jalur dipilih</span>
                                <span class="apple-pill muted" id="profileFormasiCount">Belum ada formasi dipilih</span>
                            </div>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label-custom">Jalur Ujian Yang Tampil</label>
                                    @php
                                        $selectedModeIds = old('preferred_exam_mode_ids', $user->preferredExamModeIds());
                                    @endphp
                                    <div class="preference-grid" id="profileModeCards">
                                        @foreach(($examModes ?? collect()) as $mode)
                                            <label class="preference-card">
                                                <input type="checkbox" name="preferred_exam_mode_ids[]" value="{{ $mode->id }}" data-code="{{ $mode->code }}"
                                                    {{ in_array((int) $mode->id, array_map('intval', $selectedModeIds), true) ? 'checked' : '' }}>
                                                <span>
                                                    <span class="preference-card__title">{{ $mode->name }}</span>
                                                    <span class="preference-card__hint">Aktifkan jalur ini untuk dashboard dan latihanmu.</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Formasi Yang Tampil</label>
                                    @php
                                        $selectedFormasi = old('preferred_formasi_names', $user->preferredFormasiNames());
                                    @endphp
                                    <div class="apple-hint mb-2">Pilih beberapa jika perlu. Formasi yang tidak dipilih akan disembunyikan dari modal ujian dan menu terkait.</div>
                                    <div class="preference-grid" id="profileFormasiCards">
                                        @foreach(($subCategories ?? collect()) as $sub)
                                            <label class="preference-card formasi-card" data-mode-ids="{{ implode(',', $sub->exam_mode_ids ?? [$sub->exam_mode_id]) }}" data-formasi-name="{{ $sub->name }}">
                                                <input type="checkbox" name="preferred_formasi_names[]" value="{{ $sub->name }}"
                                                    {{ in_array($sub->name, $selectedFormasi, true) ? 'checked' : '' }}>
                                                <span>
                                                    <span class="preference-card__title">{{ $sub->name }}</span>
                                                    <span class="preference-card__hint">Tampilkan hanya latihan yang relevan untuk formasi ini.</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-md-row justify-content-md-end align-items-center gap-3 mt-4">
                                <button type="submit" class="apple-glass-btn apple-btn-primary px-4 py-3 w-100 w-md-auto justify-content-center" id="profilePreferenceSubmitBtn">
                                    <i class="fas fa-sliders me-2"></i> Simpan Preferensi Ini
                                </button>
                            </div>
                        </div>

                        <div class="section-title-custom">
                            <h5 class="fw-800 m-0" style="color: var(--text-primary);">Tema Premium</h5>
                        </div>

                        <div class="apple-glass-soft rounded-4 p-3 p-md-4 mb-5">
                            <div class="apple-hint mb-3">Tema disimpan instan di perangkat ini, tanpa reload. Mode Apple Glass fokus ke rasa premium, sedangkan One UI dibuat lebih nyaman untuk sesi belajar panjang di mobile.</div>
                            <div class="theme-preview-grid" id="themePreviewGrid">
                                @foreach(($themeRegistry ?? []) as $theme)
                                    @php
                                        $preview = is_array($theme['preview'] ?? null) ? $theme['preview'] : [];
                                        $previewBg = $preview['background'] ?? 'linear-gradient(135deg, #111827, #1f2937)';
                                        $previewBar = $preview['bar'] ?? 'rgba(255,255,255,0.72)';
                                        $previewCard = $preview['card'] ?? 'rgba(255,255,255,0.40)';
                                        $previewBorder = $preview['card_border'] ?? 'rgba(255,255,255,0.28)';
                                    @endphp
                                    <button type="button" class="theme-preview-card text-start" data-theme-key="{{ $theme['slug'] }}">
                                        <div class="theme-preview-thumb" style="background: {{ $previewBg }};">
                                            <span class="theme-preview-thumb__bar"></span>
                                            <div class="theme-preview-thumb__row">
                                                <span class="theme-preview-thumb__card" style="background: {{ $previewCard }}; border-color: {{ $previewBorder }};"></span>
                                                <span class="theme-preview-thumb__card" style="background: {{ $previewCard }}; border-color: {{ $previewBorder }};"></span>
                                            </div>
                                        </div>
                                        <div class="fw-800 mb-1">{{ $theme['name'] }}</div>
                                        <small>{{ $theme['description'] }}</small>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-md-end align-items-center gap-3 mt-4">
                            <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-outline order-2 order-md-1 justify-content-center">
                                Kembali ke Dashboard
                            </a>
                            <button type="submit" class="apple-glass-btn apple-btn-primary px-4 py-3 w-100 w-md-auto order-1 order-md-2 justify-content-center" id="profileSubmitBtn">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>

                    <form id="passwordForm" action="{{ route('profile.password.update') }}" method="POST" class="mt-5">
                        @csrf
                        @method('PUT')

                        <div class="section-title-custom">
                            <h5 class="fw-800 m-0" style="color: var(--text-primary);">Keamanan</h5>
                        </div>

                        <div class="p-3 p-md-4 rounded-4 mb-4 apple-glass-soft">
                            <div class="d-flex align-items-center mb-4 text-muted small">
                                <i class="fas fa-shield-alt me-2 text-primary"></i>
                                <span>Form ini khusus untuk ubah password. Profil dan preferensi tidak ikut berubah.</span>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-custom">Password Baru</label>
                                    <input type="password" name="password" class="form-control apple-glass-input" placeholder="Minimal 8 karakter">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" class="form-control apple-glass-input" placeholder="Ulangi password baru">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-md-end align-items-center gap-3">
                            <button type="submit" class="apple-glass-btn apple-btn-outline px-4 py-3 w-100 w-md-auto justify-content-center" id="passwordSubmitBtn">
                                <i class="fas fa-key me-2"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const modeChecks = Array.from(document.querySelectorAll('#profileModeCards input[type="checkbox"]'));
        const formasiCards = Array.from(document.querySelectorAll('#profileFormasiCards .formasi-card'));
        const allCards = Array.from(document.querySelectorAll('.preference-card'));
        const modeCountPill = document.getElementById('profileModeCount');
        const formasiCountPill = document.getElementById('profileFormasiCount');
        const themeCards = Array.from(document.querySelectorAll('#themePreviewGrid .theme-preview-card'));
        const themeInput = document.getElementById('profileThemeInput');
        const fallbackTheme = @json($activeThemeSlug ?? 'dark-pro');

        const syncThemeCards = () => {
            const activeTheme = localStorage.getItem('cat_theme') || fallbackTheme;
            themeCards.forEach((card) => card.classList.toggle('is-active', card.dataset.themeKey === activeTheme));
            if (themeInput) themeInput.value = activeTheme;
        };

        const syncCardStates = () => {
            allCards.forEach((card) => {
                const input = card.querySelector('input[type="checkbox"]');
                card.classList.toggle('is-active', !!input?.checked);
            });
            if (modeCountPill) {
                const count = modeChecks.filter((item) => item.checked).length;
                modeCountPill.textContent = count > 0 ? `${count} jalur dipilih` : 'Belum ada jalur dipilih';
            }
            if (formasiCountPill) {
                const count = formasiCards
                    .map((card) => card.querySelector('input[type="checkbox"]'))
                    .filter((input) => !!input?.checked).length;
                formasiCountPill.textContent = count > 0 ? `${count} formasi dipilih` : 'Belum ada formasi dipilih';
            }
        };

        if (modeChecks.length && formasiCards.length) {
            const syncFormasiCards = () => {
                const activeModeIds = modeChecks.filter((item) => item.checked).map((item) => String(item.value));
                formasiCards.forEach((card) => {
                    const modeIds = String(card.dataset.modeIds || '').split(',').map((row) => row.trim()).filter(Boolean);
                    const visible = !activeModeIds.length || modeIds.some((id) => activeModeIds.includes(id));
                    card.classList.toggle('is-hidden', !visible);
                    const input = card.querySelector('input[type="checkbox"]');
                    if (!visible && input) input.checked = false;
                });
                syncCardStates();
            };

            allCards.forEach((card) => {
                const input = card.querySelector('input[type="checkbox"]');
                if (!input) return;
                input.addEventListener('change', () => {
                    syncCardStates();
                    syncFormasiCards();
                });
            });

            modeChecks.forEach((item) => item.addEventListener('change', syncFormasiCards));
            syncFormasiCards();
        }
        syncCardStates();

        themeCards.forEach((card) => {
            card.addEventListener('click', () => {
                const nextTheme = card.dataset.themeKey || fallbackTheme;
                if (window.CATTheme && typeof window.CATTheme.apply === 'function') {
                    window.CATTheme.apply(nextTheme);
                } else {
                    localStorage.setItem('cat_theme', nextTheme);
                    document.body?.setAttribute('data-theme', nextTheme);
                    document.body?.setAttribute('data-bs-theme', window.CATTheme?.bootstrapTheme?.(nextTheme) || 'dark');
                }
                window.dispatchEvent(new CustomEvent('cat-theme:set', { detail: { theme: nextTheme } }));
                syncThemeCards();
            });
        });
        window.addEventListener('cat-theme:set', syncThemeCards);
        syncThemeCards();

    })();
</script>
@endpush

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Preferensi',
            'icon' => 'fa-sliders',
            'href' => '#profileForm',
        ],
        [
            'label' => 'Simpan',
            'icon' => 'fa-floppy-disk',
            'form' => 'profileForm',
            'style' => 'cta',
            'aria' => 'Simpan Profil',
        ],
        [
            'label' => 'Password',
            'icon' => 'fa-key',
            'href' => '#passwordForm',
        ],
    ],
])
@endsection
