@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .auth-card { border-radius: 22px; overflow: hidden; }
    .auth-hero {
        background:
            radial-gradient(900px 300px at 20% 0%, rgba(34, 211, 238, 0.20), transparent 60%),
            radial-gradient(700px 260px at 90% 20%, rgba(59, 130, 246, 0.22), transparent 60%),
            linear-gradient(135deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.6));
        border-bottom: 1px solid var(--border-glass);
    }
    [data-bs-theme="light"] .auth-hero {
        background:
            radial-gradient(900px 300px at 20% 0%, rgba(8, 145, 178, 0.14), transparent 60%),
            radial-gradient(700px 260px at 90% 20%, rgba(37, 99, 235, 0.18), transparent 60%),
            linear-gradient(135deg, rgba(255,255,255,0.85), rgba(241, 245, 249, 0.85));
    }
    .auth-input { padding: 14px 16px !important; }
    .preference-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }
    .preference-card {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 14px 14px;
        border-radius: 18px;
        border: 1px solid var(--border-glass);
        background: rgba(255,255,255,0.03);
        cursor: pointer;
    }
    .preference-card.is-active {
        border-color: rgba(56,189,248,0.28);
        background: linear-gradient(135deg, rgba(34,211,238,0.12), rgba(59,130,246,0.08));
        box-shadow: 0 16px 28px rgba(15,23,42,0.10);
    }
    .preference-card input {
        margin-top: 3px;
    }
    .preference-card__title {
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.15;
    }
    .preference-card__hint {
        color: var(--text-secondary);
        font-size: 0.83rem;
        margin-top: 4px;
        line-height: 1.45;
    }
    .preference-card.is-hidden {
        display: none;
    }
</style>
@endpush

<div class="page-shell py-5">
    <div class="row justify-content-center align-items-center g-4">
        <div class="col-lg-7 col-xl-6">
            <div class="auth-card apple-glass apple-glass-card">
                <div class="auth-hero p-4 p-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-4"
                             style="width: 48px; height: 48px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-glass);">
                            <i class="fas fa-user-plus" style="color: var(--accent-cyan);"></i>
                        </div>
                        <div>
                        <div class="apple-kicker" style="letter-spacing: 0.12em;">CAT ASN</div>
                        <h3 class="m-0 apple-title" style="color: var(--text-primary);">Daftar Akun</h3>
                    </div>
                </div>
                    <div class="mt-3 apple-subtitle">
                        Lengkapi biodata. Setelah daftar, akun akan menunggu persetujuan master admin sebelum bisa login.
                    </div>
                </div>

                <div class="p-4 p-md-4">
                    <form method="POST" action="{{ route('register.post') }}" class="vstack gap-3">
                        @csrf

                        <div>
                            <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">Nama Lengkap</label>
                            <input type="text" class="form-control apple-glass-input auth-input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Ahmad Sulaiman">
                            @error('name') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">Instansi Tujuan</label>
                                <input type="text" class="form-control apple-glass-input auth-input @error('instansi') is-invalid @enderror" name="instansi" value="{{ old('instansi') }}" required placeholder="Contoh: BKN">
                                @error('instansi') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">No. HP / WhatsApp</label>
                                <input type="text" class="form-control apple-glass-input auth-input @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp') }}" required placeholder="08xxxxxxxxxx">
                                @error('no_hp') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">Email</label>
                            <input type="email" class="form-control apple-glass-input auth-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="email@domain.com">
                            @error('email') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                        </div>

                        <div class="apple-glass-soft apple-glass-card p-3">
                            <div class="fw-800 mb-2" style="color: var(--text-primary);">Arah Latihan Utama</div>
                            <div class="apple-hint mb-3">Pilih jalur dan formasi yang paling sering ingin kamu latih. Nanti menu dan pilihan akan dibuat lebih ringkas.</div>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">Jalur Ujian Yang Ingin Ditampilkan</label>
                                    <div class="preference-grid" id="registerModeCards">
                                        @foreach(($examModes ?? collect()) as $mode)
                                            <label class="preference-card">
                                                <input type="checkbox" name="preferred_exam_mode_ids[]" value="{{ $mode->id }}" data-code="{{ $mode->code }}"
                                                    {{ in_array((string) $mode->id, array_map('strval', old('preferred_exam_mode_ids', old('preferred_exam_mode_id') ? [old('preferred_exam_mode_id')] : [])), true) ? 'checked' : '' }}>
                                                <span>
                                                    <span class="preference-card__title">{{ $mode->name }}</span>
                                                    <span class="preference-card__hint">Tampilkan jalur ini di menu dan latihanmu.</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">Formasi Yang Ingin Ditampilkan</label>
                                    <div class="apple-hint mb-2">Boleh pilih lebih dari satu. Kami akan menyembunyikan formasi lain agar pilihanmu lebih ringkas.</div>
                                    <div class="preference-grid" id="registerFormasiCards">
                                        @foreach(($subCategories ?? collect()) as $sub)
                                            @php($oldFormasi = old('preferred_formasi_names', []))
                                            <label class="preference-card formasi-card" data-mode-ids="{{ implode(',', $sub->exam_mode_ids ?? [$sub->exam_mode_id]) }}" data-formasi-name="{{ $sub->name }}">
                                                <input type="checkbox" name="preferred_formasi_names[]" value="{{ $sub->name }}"
                                                    {{ in_array($sub->name, $oldFormasi, true) ? 'checked' : '' }}>
                                                <span>
                                                    <span class="preference-card__title">{{ $sub->name }}</span>
                                                    <span class="preference-card__hint">Tampilkan latihan dan menu yang relevan untuk formasi ini.</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('preferred_formasi_names') <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span> @enderror
                                    @error('preferred_formasi_names.*') <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">Password</label>
                                <input type="password" class="form-control apple-glass-input auth-input @error('password') is-invalid @enderror" name="password" required placeholder="Minimal 8 karakter">
                                @error('password') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2" style="color: var(--text-secondary);">Konfirmasi Password</label>
                                <input type="password" class="form-control apple-glass-input auth-input" name="password_confirmation" required placeholder="Ulangi password">
                            </div>
                        </div>

                        <button type="submit" class="apple-glass-btn apple-btn-primary w-100">
                            BUAT AKUN
                        </button>

                        <div class="text-center small" style="color: var(--text-secondary);">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: var(--accent-cyan);">Masuk</a>
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
        const modeChecks = Array.from(document.querySelectorAll('#registerModeCards input[type="checkbox"]'));
        const formasiCards = Array.from(document.querySelectorAll('#registerFormasiCards .formasi-card'));
        if (!modeChecks.length || !formasiCards.length) return;

        function syncCardStates() {
            document.querySelectorAll('.preference-card').forEach((card) => {
                const input = card.querySelector('input[type="checkbox"]');
                card.classList.toggle('is-active', !!input?.checked);
            });
        }

        function syncFormasiCards() {
            const activeModeIds = modeChecks.filter((item) => item.checked).map((item) => String(item.value));
            formasiCards.forEach((card) => {
                const modeIds = String(card.dataset.modeIds || '').split(',').map((row) => row.trim()).filter(Boolean);
                const visible = !activeModeIds.length || modeIds.some((id) => activeModeIds.includes(id));
                card.classList.toggle('is-hidden', !visible);
                const input = card.querySelector('input[type="checkbox"]');
                if (!visible && input) input.checked = false;
            });
            syncCardStates();
        }

        document.querySelectorAll('.preference-card input[type="checkbox"]').forEach((item) => {
            item.addEventListener('change', () => {
                syncCardStates();
                syncFormasiCards();
            });
        });
        modeChecks.forEach((item) => item.addEventListener('change', syncFormasiCards));
        syncFormasiCards();
        syncCardStates();
    })();
</script>
@endpush
