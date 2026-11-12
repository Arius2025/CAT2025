@extends('layouts.app')

@section('content')
@php
    $apiBaseUrl = url('/api');
    $meUrl = url('/api/me');
    $questionUrl = url('/api/questions');
    $appUrl = url('/');
@endphp

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
                    <i class="fas fa-key"></i> Integrasi Developer & Mobile App
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Manajemen API Token Sanctum
                </h1>
                <div class="apple-subtitle" style="max-width: 720px;">
                    Kelola token otentikasi API untuk pengujian antarmuka seluler, integrasi Postman, dan automasi endpoint eksternal.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/api/me') }}" target="_blank" class="apple-glass-btn apple-btn-outline px-3 py-2 text-decoration-none">
                    <i class="fas fa-plug me-1"></i> Uji `/api/me`
                </a>
                <a href="{{ route('admin.user-access.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2 text-decoration-none">
                    <i class="fas fa-users-gear me-1"></i> Izin Pengguna
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert apple-alert border-0 mb-4" style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.25) !important;">
            <i class="fas fa-circle-check text-success me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Banner Token Baru (Jika Baru Saja Dibuat) --}}
    @if($newToken)
        <div class="admin-card mb-4" style="border: 2px solid #C59B27; background: rgba(197, 155, 39, 0.05);">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-3 py-1 rounded-pill mb-1 fw-bold">
                        TOKEN BERHASIL DIBUAT
                    </span>
                    <h2 class="h5 fw-bold mb-0" style="color: var(--text-primary);">{{ $newTokenName ?: 'API Token Baru' }}</h2>
                    <small class="text-secondary">Token plain-text ini hanya ditampilkan satu kali saja. Segera simpan di tempat aman.</small>
                </div>
                <button type="button" class="apple-glass-btn apple-btn-primary px-4 py-2" onclick="navigator.clipboard.writeText(document.getElementById('newApiTokenValue').value); alert('Token berhasil disalin ke clipboard!');">
                    <i class="fas fa-copy me-2"></i> Salin Token
                </button>
            </div>
            <textarea id="newApiTokenValue" rows="2" class="form-control admin-form-control font-monospace" readonly style="font-size: 0.9rem; background: rgba(0,0,0,0.05);">{{ $newToken }}</textarea>
            <div class="small text-secondary mt-2">
                Header Authorization: <code class="d-inline-block px-2 py-1 rounded">Authorization: Bearer {{ $newToken }}</code>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Sisi Kiri: Buat Token Baru --}}
        <div class="col-lg-5">
            <div class="admin-card h-100">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-plus"></i></div>
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Buat Token Baru</h2>
                        <small class="text-secondary">Terbitkan kredensial API untuk client</small>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.api-tokens.store') }}" class="d-grid gap-3">
                    @csrf
                    <div>
                        <label class="admin-input-label">Nama / Identitas Token</label>
                        <input type="text" name="name" class="form-control admin-form-control" value="{{ old('name', 'mobile-tester') }}" placeholder="Contoh: mobile-tester-android" required>
                    </div>
                    <div>
                        <label class="admin-input-label">Hak Akses (Abilities)</label>
                        <input type="text" name="abilities" class="form-control admin-form-control font-monospace" value="{{ old('abilities', '*') }}" placeholder="Contoh: questions.read,exams.write atau *">
                        <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                            Gunakan tanda <code>*</code> untuk akses penuh ke seluruh rute pengujian API.
                        </div>
                    </div>
                    <button type="submit" class="apple-glass-btn apple-btn-primary py-3 fw-bold mt-2">
                        <i class="fas fa-key me-2"></i> Terbitkan Token API
                    </button>
                </form>
            </div>
        </div>

        {{-- Sisi Kanan: Daftar Token Aktif --}}
        <div class="col-lg-7">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Daftar Token Aktif</h2>
                        <small class="text-secondary">Token yang memiliki izin akses saat ini</small>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 rounded-pill font-monospace">
                        {{ $tokens->count() }} Token
                    </span>
                </div>

                @if($tokens->isEmpty())
                    <div class="p-5 text-center text-secondary rounded-3" style="background: rgba(0,0,0,0.02); border: 1px dashed var(--border-glass);">
                        <i class="fas fa-key fa-2x mb-2 opacity-50"></i>
                        <div class="small fw-bold">Belum ada token aktif.</div>
                        <div class="small mt-1">Buat token pertama di form sebelah kiri untuk keperluan pengujian.</div>
                    </div>
                @else
                    <div class="d-grid gap-3" style="max-height: 380px; overflow-y: auto;">
                        @foreach($tokens as $token)
                            <div class="p-3 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-glass);">
                                <div>
                                    <div class="fw-bold" style="color: var(--text-primary);"><i class="fas fa-shield-alt text-warning me-1"></i> {{ $token->name }}</div>
                                    <div class="small text-secondary mt-1">Abilities: <code>{{ $token->abilities === ['*'] ? '*' : implode(', ', $token->abilities ?? []) }}</code></div>
                                    <div class="small text-secondary" style="font-size: 0.76rem;">
                                        Dibuat: {{ optional($token->created_at)->format('d/m/Y H:i') }} | Terakhir digunakan: <strong>{{ optional($token->last_used_at)->format('d/m/Y H:i') ?: 'Belum pernah' }}</strong>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.api-tokens.destroy', $token->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin mencabut token ini? Client tidak akan dapat mengakses API lagi.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-1 rounded-pill">
                                        <i class="fas fa-trash-can me-1"></i> Cabut
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Panduan Integrasi Cepat & QR Helper --}}
    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-code"></i></div>
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Contoh Header & cURL Request</h2>
                        <small class="text-secondary">Salin konfigurasi siap pakai ke aplikasi mobile/Postman</small>
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <div>
                        <label class="admin-input-label">Base URL API</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="apiBaseUrlField" class="form-control admin-form-control font-monospace" value="{{ $apiBaseUrl }}" readonly>
                            <button type="button" class="apple-glass-btn apple-btn-outline px-3" onclick="navigator.clipboard.writeText(document.getElementById('apiBaseUrlField').value)">Copy</button>
                        </div>
                    </div>
                    <div>
                        <label class="admin-input-label">Endpoint Uji `/api/me`</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="apiMeUrlField" class="form-control admin-form-control font-monospace" value="{{ $meUrl }}" readonly>
                            <button type="button" class="apple-glass-btn apple-btn-outline px-3" onclick="navigator.clipboard.writeText(document.getElementById('apiMeUrlField').value)">Copy</button>
                        </div>
                    </div>
                    <div>
                        <label class="admin-input-label">Contoh Perintah cURL</label>
                        <textarea id="apiCurlField" rows="3" class="form-control admin-form-control font-monospace" readonly style="font-size: 0.85rem;">curl -H "Authorization: Bearer TOKEN_KAMU" -H "Accept: application/json" "{{ $questionUrl }}?difficulty=medium"</textarea>
                        <button type="button" class="apple-glass-btn apple-btn-outline mt-2 btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('apiCurlField').value)">
                            <i class="fas fa-copy me-1"></i> Salin Perintah cURL
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-qrcode"></i></div>
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">QR Code Akses Cepat Smartphone</h2>
                        <small class="text-secondary">Pindai kamera untuk membuka web & endpoint</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 text-center rounded-3 h-100" style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-glass);">
                            <div class="fw-bold small mb-2" style="color: var(--text-primary);">Buka Web Aplikasi</div>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($appUrl) }}" alt="QR App" class="img-fluid rounded-3 mb-2 p-2 bg-white" style="max-width: 150px;">
                            <div class="small text-secondary font-monospace" style="font-size: 0.72rem;">{{ $appUrl }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 text-center rounded-3 h-100" style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-glass);">
                            <div class="fw-bold small mb-2" style="color: var(--text-primary);">Endpoint API Me</div>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($meUrl) }}" alt="QR API" class="img-fluid rounded-3 mb-2 p-2 bg-white" style="max-width: 150px;">
                            <div class="small text-secondary font-monospace" style="font-size: 0.72rem;">{{ $meUrl }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Browser API Tester Interactive --}}
    <div class="admin-card mt-4">
        <div class="admin-section-header">
            <div class="admin-step-num"><i class="fas fa-play"></i></div>
            <div>
                <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Interactive API Playground</h2>
                <small class="text-secondary">Uji respon rute API langsung melalui browser</small>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <label class="admin-input-label">Bearer Token untuk Testing</label>
                <textarea id="previewTokenField" rows="4" class="form-control admin-form-control font-monospace" placeholder="Tempelkan Bearer token di sini..." style="font-size: 0.85rem;">{{ $newToken ?? '' }}</textarea>
            </div>
            <div class="col-lg-4">
                <label class="admin-input-label">Target Endpoint API</label>
                <select id="previewEndpointSelect" class="form-select admin-form-control">
                    <option value="{{ $meUrl }}">GET /api/me (Profil Autentikasi)</option>
                    <option value="{{ url('/api/questions?difficulty=medium') }}">GET /api/questions (Koleksi Bank Soal)</option>
                    <option value="{{ url('/api/bookmarks') }}">GET /api/bookmarks (Soal Favorit)</option>
                    <option value="{{ url('/api/exams/history') }}">GET /api/exams/history (Riwayat Ujian)</option>
                </select>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" id="previewRunBtn" class="apple-glass-btn apple-btn-primary flex-fill py-2 fw-bold">
                        <i class="fas fa-bolt me-1"></i> Jalankan Request
                    </button>
                    <button type="button" id="previewCopyBtn" class="apple-glass-btn apple-btn-outline px-3 py-2">
                        <i class="fas fa-copy me-1"></i> Salin URL
                    </button>
                </div>
            </div>
            <div class="col-lg-4">
                <label class="admin-input-label">Status & Latensi Respon</label>
                <div id="previewStatusBox" class="p-3 rounded-3 h-100 font-monospace small" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass); color: var(--text-secondary); min-height: 110px;">
                    Siap diuji. Masukkan token lalu klik <strong>Jalankan Request</strong>.
                </div>
            </div>
            <div class="col-12 mt-3">
                <label class="admin-input-label">Payload Response (JSON Output)</label>
                <pre id="previewResponseBox" class="p-3 rounded-3 font-monospace small mb-0" style="max-height: 300px; overflow-y: auto; background: rgba(0,0,0,0.06); color: var(--text-primary); white-space: pre-wrap;">Respon payload API akan ditampilkan di sini.</pre>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const previewTokenField = document.getElementById('previewTokenField');
    const previewEndpointSelect = document.getElementById('previewEndpointSelect');
    const previewRunBtn = document.getElementById('previewRunBtn');
    const previewCopyBtn = document.getElementById('previewCopyBtn');
    const previewStatusBox = document.getElementById('previewStatusBox');
    const previewResponseBox = document.getElementById('previewResponseBox');

    previewCopyBtn?.addEventListener('click', () => {
        navigator.clipboard.writeText(previewEndpointSelect.value).then(() => {
            alert('URL endpoint disalin!');
        });
    });

    previewRunBtn?.addEventListener('click', async () => {
        const token = (previewTokenField.value || '').trim();
        const url = previewEndpointSelect.value;
        if (!token) {
            alert('Silakan isi Bearer token terlebih dahulu.');
            return;
        }

        previewStatusBox.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengirim HTTP Request...';
        previewResponseBox.textContent = 'Memuat response...';
        const startTime = performance.now();

        try {
            const res = await fetch(url, {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const duration = Math.round(performance.now() - startTime);
            const data = await res.json();
            
            const statusClass = res.ok ? 'text-success' : 'text-danger';
            previewStatusBox.innerHTML = `<div>Status: <strong class="${statusClass}">${res.status} ${res.statusText}</strong></div><div>Waktu: <strong>${duration} ms</strong></div>`;
            previewResponseBox.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            previewStatusBox.innerHTML = `<div class="text-danger">Request Gagal: ${e.message}</div>`;
            previewResponseBox.textContent = String(e);
        }
    });
});
</script>
@endpush
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
            'label' => 'API',
            'icon' => 'fa-key',
            'href' => route('admin.api-tokens.index'),
            'style' => 'cta',
        ],
        [
            'label' => 'Bank',
            'icon' => 'fa-database',
            'href' => route('admin.soal-bank.index'),
        ],
    ],
])
@endsection
