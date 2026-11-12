@extends('layouts.app')

@section('title', 'Cek AI - Kesiapan Mesin')

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

    .metric-card {
        border-radius: 16px;
        padding: 16px;
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid var(--border-glass);
        transition: transform 0.2s ease;
    }
    [data-bs-theme="dark"] .metric-card {
        background: rgba(255, 255, 255, 0.04);
    }
</style>
@endpush

<div class="admin-shell">
    {{-- Page Hero --}}
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-microchip"></i> Diagnostik & Monitoring AI
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Cek Kesiapan Engine AI
                </h1>
                <div class="apple-subtitle" style="max-width: 700px;">
                    Uji konektivitas latency, validitas token provider, dan kemampuan model menghasilkan butir soal sebelum proses generate massal dijalankan.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-wand-magic-sparkles me-1"></i> Ke Generator Soal
                </a>
                <a href="{{ route('admin.api-tokens.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2">
                    <i class="fas fa-key me-1"></i> Kelola Token API
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Sisi Kiri: Panel Kontrol Pengujian --}}
        <div class="col-lg-5">
            <div class="admin-card h-100">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-sliders"></i></div>
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Konfigurasi Pengujian</h2>
                        <small class="text-secondary">Pilih provider dan model untuk diteliti</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="admin-input-label">Provider AI</label>
                        <select id="providerSelect" class="form-select admin-form-control">
                            <option value="groq">Groq Cloud (Fast LLM)</option>
                            <option value="ollama_qwen">Ollama (Server Mandiri)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="admin-input-label">Model Ollama</label>
                        <select id="modelSelect" class="form-select admin-form-control">
                            @foreach($ollamaModels as $model)
                                <option value="{{ $model }}" {{ $defaultOllamaModel === $model ? 'selected' : '' }}>{{ $model }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-3" style="background: rgba(19, 62, 135, 0.06); border: 1px solid rgba(19, 62, 135, 0.15);" id="ollamaFastWrap">
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" id="ollamaFastToggle" class="form-check-input" checked>
                                <label class="form-check-label fw-bold small ms-2" for="ollamaFastToggle" style="color: var(--text-primary);">
                                    Ollama Fast Mode
                                </label>
                            </div>
                            <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                                Otomatis alihkan ke <code>llama3.2:3b</code> untuk pengujian ringan berlatensi rendah.
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="admin-input-label">Materi Soal Uji Coba</label>
                        <select id="kategoriSelect" class="form-select admin-form-control">
                            <option value="TIU">TIU (Tes Inteligensia Umum)</option>
                            <option value="TWK">TWK (Tes Wawasan Kebangsaan)</option>
                            <option value="TKP">TKP (Karakteristik Pribadi)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 mt-4 pt-3 border-top" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                    <button class="apple-glass-btn apple-btn-outline py-2 fw-bold" id="btnPing">
                        <i class="fas fa-network-wired me-2"></i> 1. Uji Ping Server & Latensi
                    </button>
                    <button class="apple-glass-btn apple-btn-primary py-3 fw-bold" id="btnGenerate">
                        <i class="fas fa-bolt me-2"></i> 2. Uji Generate Sampel (1 Soal)
                    </button>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Hasil & Diagnostik Metrik --}}
        <div class="col-lg-7">
            <div class="admin-card h-100">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-chart-line"></i></div>
                    <div class="flex-grow-1">
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Status Kesiapan Engine</h2>
                        <small class="text-secondary">4 pilar verifikasi respon model</small>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 rounded-pill font-monospace" id="summaryBadge">
                        BELUM DIUJI
                    </span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="metric-card">
                            <div class="small text-secondary fw-bold text-uppercase" style="font-size: 0.72rem;">Konektivitas Server</div>
                            <div class="fw-bold fs-6 mt-1" id="serverStatus" style="color: var(--text-primary);">-</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="metric-card">
                            <div class="small text-secondary fw-bold text-uppercase" style="font-size: 0.72rem;">Ketersediaan Model</div>
                            <div class="fw-bold fs-6 mt-1" id="modelStatus" style="color: var(--text-primary);">-</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="metric-card">
                            <div class="small text-secondary fw-bold text-uppercase" style="font-size: 0.72rem;">Waktu Latensi (RTT)</div>
                            <div class="fw-bold fs-6 mt-1 font-monospace" id="latencyStatus" style="color: var(--text-primary);">-</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="metric-card">
                            <div class="small text-secondary fw-bold text-uppercase" style="font-size: 0.72rem;">Kelayakan Generate</div>
                            <div class="fw-bold fs-6 mt-1" id="readyStatus" style="color: var(--text-primary);">-</div>
                        </div>
                    </div>
                </div>

                <details class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                    <summary class="fw-bold small text-secondary" style="cursor: pointer;">
                        <i class="fas fa-code me-1"></i> Rincian Teknis Raw Output JSON
                    </summary>
                    <pre id="resultBox" class="p-3 rounded mt-2 font-monospace small mb-0" style="max-height: 250px; overflow-y: auto; background: rgba(0,0,0,0.06); color: var(--text-primary); white-space: pre-wrap;">Belum ada hasil pengujian. Klik salah satu tombol uji coba di sebelah kiri.</pre>
                </details>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const providerSelect = document.getElementById('providerSelect');
    const modelSelect = document.getElementById('modelSelect');
    const kategoriSelect = document.getElementById('kategoriSelect');
    const btnPing = document.getElementById('btnPing');
    const btnGenerate = document.getElementById('btnGenerate');
    const resultBox = document.getElementById('resultBox');
    const ollamaFastToggle = document.getElementById('ollamaFastToggle');
    const ollamaFastWrap = document.getElementById('ollamaFastWrap');
    const summaryBadge = document.getElementById('summaryBadge');
    const serverStatus = document.getElementById('serverStatus');
    const modelStatus = document.getElementById('modelStatus');
    const latencyStatus = document.getElementById('latencyStatus');
    const readyStatus = document.getElementById('readyStatus');

    function syncModelState() {
        const isOllama = providerSelect.value === 'ollama_qwen';
        ollamaFastWrap.style.display = isOllama ? '' : 'none';
        if (!isOllama) {
            modelSelect.disabled = true;
            return;
        }
        if (ollamaFastToggle.checked) {
            modelSelect.value = 'llama3.2:3b';
            modelSelect.disabled = true;
        } else {
            modelSelect.disabled = false;
        }
    }

    function setSummary(label, cls) {
        summaryBadge.textContent = label.toUpperCase();
        summaryBadge.className = 'badge px-3 py-2 rounded-pill font-monospace ' + cls;
    }

    function renderStatus(data) {
        const ok = !!data.ok;
        const latency = data.latency_ms ?? 0;
        const latencyLabel = latency ? `${latency} ms` : 'Tidak ada data';
        const modelAvailable = (typeof data.model_available === 'boolean') ? data.model_available : ok;
        const ready = data.mode === 'generate' ? (ok && !!data.parsed_ok) : (ok && modelAvailable);

        serverStatus.innerHTML = ok
            ? '<span class="text-success"><i class="fas fa-check-circle me-1"></i> Server Aktif</span>'
            : '<span class="text-danger"><i class="fas fa-times-circle me-1"></i> Server Bermasalah</span>';

        modelStatus.innerHTML = modelAvailable
            ? '<span class="text-success"><i class="fas fa-check-circle me-1"></i> Model Siap Pakai</span>'
            : '<span class="text-danger"><i class="fas fa-times-circle me-1"></i> Model Belum Siap</span>';

        latencyStatus.innerHTML = latency ? `<span class="text-info">${latency} ms</span>` : '-';

        readyStatus.innerHTML = ready
            ? '<span class="text-success fw-bold"><i class="fas fa-thumbs-up me-1"></i> Layak Eksekusi</span>'
            : '<span class="text-danger fw-bold"><i class="fas fa-triangle-exclamation me-1"></i> Tidak Disarankan</span>';

        if (ready) {
            setSummary('Siap Dipakai', 'bg-success bg-opacity-10 text-success border border-success');
        } else if (ok) {
            setSummary('Perlu Penyesuaian', 'bg-warning bg-opacity-10 text-warning border border-warning');
        } else {
            setSummary('Gagal Terhubung', 'bg-danger bg-opacity-10 text-danger border border-danger');
        }
    }

    async function runTest(mode) {
        resultBox.textContent = 'Menjalankan diagnostik... mohon tunggu beberapa detik.';
        setSummary('Sedang Menguji...', 'bg-primary bg-opacity-10 text-primary border border-primary');
        try {
            const res = await fetch(@json(route('admin.ai-check.test')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    provider: providerSelect.value,
                    model: modelSelect.value,
                    ollama_fast: ollamaFastToggle.checked ? 1 : 0,
                    kategori: kategoriSelect.value,
                    mode,
                }),
            });
            const data = await res.json();
            renderStatus(data);
            resultBox.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            const data = { ok: false, error: String(e && e.message ? e.message : e) };
            renderStatus(data);
            resultBox.textContent = JSON.stringify(data, null, 2);
        }
    }

    providerSelect.addEventListener('change', syncModelState);
    ollamaFastToggle.addEventListener('change', syncModelState);
    btnPing.addEventListener('click', () => runTest('ping'));
    btnGenerate.addEventListener('click', () => runTest('generate'));
    syncModelState();
});
</script>
@endpush
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
        [
            'label' => 'Cek AI',
            'icon' => 'fa-robot',
            'href' => route('admin.ai-check'),
            'style' => 'cta',
            'aria' => 'Cek Kesiapan AI',
        ],
        [
            'label' => 'API',
            'icon' => 'fa-key',
            'href' => route('admin.api-tokens.index'),
        ],
    ],
])
@endsection
