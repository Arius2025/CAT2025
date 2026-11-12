@extends('layouts.app')

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

    .dropzone-box {
        border: 2px dashed rgba(197, 155, 39, 0.35);
        background: rgba(197, 155, 39, 0.03);
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        transition: all 0.2s ease;
    }
    .dropzone-box:hover {
        border-color: #C59B27;
        background: rgba(197, 155, 39, 0.06);
    }
</style>
@endpush

<div class="admin-shell" id="pdfJobsPage">
    {{-- Page Hero --}}
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-file-pdf"></i> Otomasi Bank Soal PDF &bull; Standar CASN 2027
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Ekstraksi & Generate Soal dari PDF
                </h1>
                <div class="apple-subtitle" style="max-width: 720px;">
                    Unggah modul kisi-kisi atau naskah soal PDF. Sistem otomatis mendeteksi format, merestrukturisasi butir soal ke standar resmi <strong>CASN 2027 (KemenPAN-RB &amp; BKN)</strong> dengan penalaran HOTS dan SJT TKP berbobot 1-5, lalu memproses via background worker.
                </div>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <span class="badge px-2.5 py-1.5 rounded-pill" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-weight: 700; font-size: 0.75rem;"><i class="fas fa-certificate me-1"></i> Output Standar CASN 2027</span>
                    <span class="badge px-2.5 py-1.5 rounded-pill" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); font-weight: 700; font-size: 0.75rem;"><i class="fas fa-brain me-1"></i> HOTS &amp; SJT Auto-Format</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-wand-magic-sparkles me-1"></i> Mode Cepat
                </a>
                <a href="{{ route('admin.soal.bulk.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-microchip me-1"></i> Mode Massal AI
                </a>
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2">
                    <i class="fas fa-database me-1"></i> Bank Soal
                </a>
            </div>
        </div>
    </div>

    <div id="pdfJobPageAlert" class="alert d-none mb-4"></div>

    <div class="row g-4">
        {{-- Sisi Kiri: Form Upload PDF --}}
        <div class="col-lg-5">
            <div class="admin-card">
                <div class="admin-section-header">
                    <div class="admin-step-num"><i class="fas fa-cloud-arrow-up"></i></div>
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Unggah Dokumen PDF</h2>
                        <small class="text-secondary">Ekstraksi otomatis materi & kisi-kisi BKN</small>
                    </div>
                </div>

                <form action="{{ route('admin.soal.generate') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3" id="pdfGenerateForm">
                    @csrf
                    <input type="hidden" name="auto_detect_pdf" value="1">

                    <div>
                        <label class="admin-input-label">Berkas PDF Target</label>
                        <div class="dropzone-box mb-2">
                            <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                            <input type="file" name="referensi_pdf" accept="application/pdf" class="form-control admin-form-control" required>
                        </div>
                        <div class="small text-secondary" style="font-size: 0.78rem;">
                            Berkas aman disimpan di server untuk proses retry tanpa perlu upload ulang bila terputus.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="admin-input-label">Jumlah Soal</label>
                            <input type="number" name="jumlah" min="1" max="20" value="{{ old('jumlah', 10) }}" class="form-control admin-form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="admin-input-label">Provider AI</label>
                            <select name="provider" class="form-select admin-form-control">
                                <option value="groq" {{ old('provider', 'groq') === 'groq' ? 'selected' : '' }}>Groq Cloud (Cepat)</option>
                                <option value="ollama_qwen" {{ old('provider') === 'ollama_qwen' ? 'selected' : '' }}>Ollama Lokal (Private)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="admin-input-label">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select admin-form-control">
                                <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Mudah (Easy)</option>
                                <option value="medium" {{ old('difficulty', 'medium') === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                                <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>HOTS / Sukar (Hard)</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 mt-2" style="background: rgba(19, 62, 135, 0.08); border: 1px solid rgba(19, 62, 135, 0.20);">
                        <div class="fw-bold small text-primary"><i class="fas fa-network-wired me-1"></i> Pemrosesan Antrian Terisolasi</div>
                        <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                            Tugas PDF dialihkan ke antrian khusus <code>pdf-generate</code>. Anda dapat berpindah halaman dengan aman saat sistem memproses di latar belakang.
                        </div>
                    </div>

                    <button type="submit" class="apple-glass-btn apple-btn-primary w-100 py-3 mt-2 fw-bold" id="pdfGenerateSubmitBtn">
                        <i class="fas fa-file-pdf me-2"></i> Eksekusi Pembuatan Soal PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Sisi Kanan: Status & Riwayat Job --}}
        <div class="col-lg-7">
            <div class="admin-card">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                    <div>
                        <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Riwayat Tugas PDF Real-Time</h2>
                        <small class="text-secondary">Pembaruan berkala otomatis setiap 8 detik</small>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-xs apple-glass-btn apple-btn-outline pdf-status-filter active py-1 px-2" data-status="">Semua</button>
                        <button type="button" class="btn btn-xs apple-glass-btn apple-btn-outline pdf-status-filter py-1 px-2" data-status="processing">Memproses</button>
                        <button type="button" class="btn btn-xs apple-glass-btn apple-btn-outline pdf-status-filter py-1 px-2" data-status="success">Selesai</button>
                        <button type="button" class="btn btn-xs apple-glass-btn apple-btn-outline pdf-status-filter py-1 px-2" data-status="error">Gagal</button>
                        <button type="button" class="btn btn-xs apple-glass-btn apple-btn-outline py-1 px-2" id="refreshPdfJobsBtn">
                            <i class="fas fa-arrows-rotate"></i>
                        </button>
                    </div>
                </div>

                <div id="pdfJobsList" class="vstack gap-3" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Job --}}
<div class="modal fade" id="pdfJobDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content admin-card border-0">
            <div class="modal-header border-bottom" style="border-color: rgba(197, 155, 39, 0.20) !important;">
                <div>
                    <h3 class="h5 fw-800 mb-0" style="color: var(--text-primary);"><i class="fas fa-file-waveform me-2 text-warning"></i> Detail Tugas Ekstraksi PDF</h3>
                    <div class="small text-secondary" id="pdfJobDetailMeta">Informasi status pemrosesan dan log eksekusi worker</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase">Nama Berkas PDF</div>
                            <div class="fw-bold mt-1" id="pdfDetailFile" style="color: var(--text-primary);">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase">Status Tugas</div>
                            <div class="fw-bold mt-1" id="pdfDetailStatus">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase">Antrian (Queue)</div>
                            <div class="fw-bold mt-1" id="pdfDetailQueue">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase">Mode Terdeteksi</div>
                            <div class="fw-bold mt-1" id="pdfDetailMode">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase">Kategori Soal</div>
                            <div class="fw-bold mt-1" id="pdfDetailKategori">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase">Progres Selesai</div>
                            <div class="fw-bold mt-1" id="pdfDetailProgress">-</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase">Catatan / Keterangan</div>
                            <div class="mt-1" id="pdfDetailReason">-</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.04); border: 1px solid var(--border-glass);">
                            <div class="small text-secondary fw-bold text-uppercase mb-2">Log Output Pekerja (Worker)</div>
                            <pre class="small mb-0 font-monospace p-2 rounded" id="pdfDetailLog" style="white-space: pre-wrap; max-height: 250px; overflow-y: auto; background: rgba(0,0,0,0.06);"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top" style="border-color: rgba(197, 155, 39, 0.20) !important;">
                <button type="button" class="apple-glass-btn apple-btn-outline px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="apple-glass-btn apple-btn-primary d-none px-4" id="pdfDetailRetryBtn">
                    <i class="fas fa-rotate-right me-1"></i> Retry / Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const jobsBase = @json(route('admin.soal.pdf.jobs'));
    const statusBase = @json(route('admin.soal.status'));
    const retryUrl = @json(route('admin.soal.retry'));
    const initialJobs = @json($recentPdfJobs ?? []);
    const jobsList = document.getElementById('pdfJobsList');
    const alertBox = document.getElementById('pdfJobPageAlert');
    const refreshBtn = document.getElementById('refreshPdfJobsBtn');
    const form = document.getElementById('pdfGenerateForm');
    const submitBtn = document.getElementById('pdfGenerateSubmitBtn');
    const detailModalEl = document.getElementById('pdfJobDetailModal');
    const detailRetryBtn = document.getElementById('pdfDetailRetryBtn');
    let activeStatus = '';
    let currentJobs = Array.isArray(initialJobs) ? initialJobs : [];
    let refreshTimer = null;
    let detailJobId = '';

    function setAlert(kind, message) {
        if (!alertBox) return;
        if (!message) {
            alertBox.className = 'alert d-none mb-4';
            alertBox.textContent = '';
            return;
        }
        alertBox.className = 'alert mb-4 ' + (kind === 'error' ? 'alert-danger' : 'alert-success');
        alertBox.textContent = message;
    }

    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function statusBadge(status) {
        const st = String(status || '').toLowerCase();
        if (st === 'success') return '<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">SUKSES</span>';
        if (st === 'processing') return '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1"><i class="fas fa-spinner fa-spin me-1"></i>MEMPROSES</span>';
        if (['error', 'failed', 'unknown'].includes(st)) return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">GAGAL</span>';
        return '<span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">' + esc(st.toUpperCase()) + '</span>';
    }

    function normalizeErrorStatus(status) {
        const st = String(status || '').toLowerCase();
        return ['error', 'failed', 'unknown'].includes(st) ? 'error' : st;
    }

    function renderJobs() {
        if (!jobsList) return;
        let rows = currentJobs.slice();
        if (activeStatus) {
            rows = rows.filter((job) => normalizeErrorStatus(job.status) === activeStatus);
        }
        if (!rows.length) {
            jobsList.innerHTML = '<div class="p-4 text-center text-secondary rounded-3" style="background: rgba(0,0,0,0.02); border: 1px dashed var(--border-glass);"><i class="fas fa-inbox fa-2x mb-2 opacity-50"></i><div class="small fw-bold">Belum ada tugas PDF pada status ini.</div></div>';
            return;
        }
        jobsList.innerHTML = rows.map((job) => {
            const req = job.request || {};
            const progress = job.progress || {};
            const auto = job.auto_detected || {};
            const status = String(job.status || 'unknown').toLowerCase();
            const file = String(req.pdf_path || '');
            const mode = String(auto.exam_mode_name || 'Auto');
            const kategori = String(auto.kategori || req.kategori || 'Auto');
            const sub = String(auto.sub_category_name || '');
            const reason = String(auto.reason || job.message || '');
            const done = Number(progress.done || 0);
            const total = Number(progress.total || req.jumlah || 0);
            const queueName = String(job.queue_name || 'pdf-generate');
            const canRetry = ['error', 'failed', 'unknown'].includes(status) || (status === 'processing' && done < total);
            return `
                <div class="p-3 rounded-3" style="background: var(--card-bg); border: 1px solid var(--border-glass);" data-job-id="${esc(job.job_id)}">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-start justify-content-between gap-3">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <div class="fw-bold" style="color: var(--text-primary);"><i class="fas fa-file-pdf text-danger me-1"></i> ${esc(file.split('\\').pop() || file || ('Job #' + job.job_id))}</div>
                                ${statusBadge(status)}
                            </div>
                            <div class="small text-secondary">Mode: <strong class="text-body">${esc(mode)}</strong> | Kategori: <strong class="text-body">${esc(kategori)}</strong>${sub ? ' | Formasi: ' + esc(sub) : ''}</div>
                            <div class="small text-secondary">Progres: <strong class="text-warning">${done}/${total} Butir</strong> | Antrian: <code class="small">${esc(queueName)}</code></div>
                            ${reason ? `<div class="small text-danger mt-1"><i class="fas fa-circle-info me-1"></i> ${esc(reason)}</div>` : ''}
                        </div>
                        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                            <button type="button" class="btn btn-sm apple-glass-btn apple-btn-outline pdf-detail-btn" data-job-id="${esc(job.job_id)}">
                                <i class="fas fa-eye me-1"></i> Detail
                            </button>
                            ${canRetry ? `<button type="button" class="btn btn-sm apple-glass-btn apple-btn-primary pdf-retry-btn" data-job-id="${esc(job.job_id)}"><i class="fas fa-rotate-right me-1"></i> Ulangi</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function fetchJobs() {
        const url = jobsBase + (activeStatus ? ('?status=' + encodeURIComponent(activeStatus)) : '');
        const res = await fetch(url, { credentials: 'same-origin' });
        const data = await res.json();
        if (!res.ok || !data?.ok) throw new Error(data?.message || 'Gagal memuat job PDF.');
        currentJobs = Array.isArray(data.jobs) ? data.jobs : [];
        renderJobs();
    }

    async function fetchJobDetail(jobId) {
        const res = await fetch(statusBase + '?id=' + encodeURIComponent(jobId), { credentials: 'same-origin' });
        const data = await res.json();
        if (!res.ok) throw new Error(data?.message || 'Gagal memuat detail job.');
        return data;
    }

    async function retryJob(jobId) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const fd = new FormData();
        fd.set('job_id', jobId);
        const res = await fetch(retryUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: fd,
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok || !data?.ok) throw new Error(data?.message || 'Gagal retry job PDF.');
        return data;
    }

    function fillDetailModal(data) {
        const req = data.request || {};
        const auto = data.auto_detected || {};
        const progress = data.progress || {};
        document.getElementById('pdfDetailFile').textContent = String(req.pdf_path || '-').split('\\').pop() || String(req.pdf_path || '-');
        document.getElementById('pdfDetailStatus').innerHTML = statusBadge(data.status);
        document.getElementById('pdfDetailQueue').textContent = String(data.queue?.name || data.queue_name || '-');
        document.getElementById('pdfDetailMode').textContent = String(auto.exam_mode_name || req.exam_mode_id || 'Auto');
        document.getElementById('pdfDetailKategori').textContent = String(auto.kategori || req.kategori || 'Auto');
        document.getElementById('pdfDetailProgress').textContent = `${Number(progress.done || 0)} / ${Number(progress.total || req.jumlah || 0)} Butir`;
        document.getElementById('pdfDetailReason').textContent = String(auto.reason || data.message || '-');
        document.getElementById('pdfDetailLog').textContent = Array.isArray(data.log) ? data.log.join('\n') : '';
        detailJobId = String(data.job_id || '');
        const canRetry = ['error', 'failed', 'unknown'].includes(String(data.status || '').toLowerCase())
            || (String(data.status || '').toLowerCase() === 'processing' && Number(progress.done || 0) < Number(progress.total || req.jumlah || 0));
        detailRetryBtn.classList.toggle('d-none', !canRetry);
    }

    function setActiveFilter(status) {
        activeStatus = status;
        document.querySelectorAll('.pdf-status-filter').forEach((btn) => {
            const isMatch = String(btn.dataset.status || '') === status;
            btn.classList.toggle('active', isMatch);
            btn.classList.toggle('apple-btn-gold', isMatch);
        });
    }

    async function handleRefresh() {
        try {
            await fetchJobs();
        } catch (e) {
            setAlert('error', String(e?.message || e));
        }
    }

    document.addEventListener('click', async (event) => {
        const filterBtn = event.target.closest('.pdf-status-filter');
        if (filterBtn) {
            setActiveFilter(String(filterBtn.dataset.status || ''));
            await handleRefresh();
            return;
        }

        const detailBtn = event.target.closest('.pdf-detail-btn');
        if (detailBtn) {
            try {
                const data = await fetchJobDetail(String(detailBtn.dataset.jobId || ''));
                fillDetailModal(data);
                const modal = window.bootstrap?.Modal ? window.bootstrap.Modal.getOrCreateInstance(detailModalEl) : null;
                modal?.show();
            } catch (e) {
                setAlert('error', String(e?.message || e));
            }
            return;
        }

        const retryBtn = event.target.closest('.pdf-retry-btn');
        if (retryBtn) {
            try {
                retryBtn.disabled = true;
                await retryJob(String(retryBtn.dataset.jobId || ''));
                setAlert('success', 'Tugas PDF telah dijadwalkan ulang ke antrian pekerja.');
                await handleRefresh();
            } catch (e) {
                setAlert('error', String(e?.message || e));
            } finally {
                retryBtn.disabled = false;
            }
        }
    });

    detailRetryBtn?.addEventListener('click', async () => {
        if (!detailJobId) return;
        try {
            detailRetryBtn.disabled = true;
            await retryJob(detailJobId);
            setAlert('success', 'Tugas PDF telah dijadwalkan ulang ke antrian pekerja.');
            const modal = window.bootstrap?.Modal ? window.bootstrap.Modal.getOrCreateInstance(detailModalEl) : null;
            modal?.hide();
            await handleRefresh();
        } catch (e) {
            setAlert('error', String(e?.message || e));
        } finally {
            detailRetryBtn.disabled = false;
        }
    });

    refreshBtn?.addEventListener('click', handleRefresh);

    form?.addEventListener('submit', () => {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim ke Server...';
        }
    });

    setActiveFilter('');
    renderJobs();
    refreshTimer = setInterval(handleRefresh, 8000);
})();
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
            'label' => 'PDF',
            'icon' => 'fa-file-pdf',
            'href' => route('admin.soal.pdf'),
            'style' => 'cta',
            'aria' => 'Generate Soal PDF',
        ],
        [
            'label' => 'Bank',
            'icon' => 'fa-database',
            'href' => route('admin.soal-bank.index'),
        ],
    ],
])
@endsection
