@extends('layouts.app')

@section('content')
@php
    $jobId = session('processing_job_id');
    $isAdmin = auth()->check(); // page already behind auth
@endphp

<div class="container page-shell generate-dashboard-shell" id="generateDashboardShell">
    @if(session('success'))
        <div class="generate-toast" id="generateSuccessToast">
            <div class="generate-toast__title"><i class="fas fa-check-circle me-2"></i>Soal berhasil dibuat</div>
            <div class="apple-hint" style="color: rgba(248,250,252,0.82) !important;">{{ session('success') }}</div>
        </div>
    @endif

    <div class="admin-page-hero mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-wand-magic-sparkles"></i> AI Engine Workspace
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Generator Soal AI
                </h1>
                <div class="apple-subtitle" style="max-width: 720px;">
                    Buat butir soal terstandar resmi <strong>CASN 2027 (KemenPAN-RB &amp; BKN)</strong> secara instan menggunakan model AI mutakhir (Groq Cloud / Ollama). Mengadopsi format HOTS studi kasus untuk TWK/TIU, Situational Judgment Test (SJT) pembobotan 1–5 untuk TKP, dan komposisi 30% umum : 70% teknis khusus untuk SKB.
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="apple-pill" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-weight: 700;"><i class="fas fa-certificate me-1"></i> Standar CASN 2027 Resmi</span>
                    <span class="apple-pill mode-skd"><i class="fas fa-bolt me-1"></i> Mode Cepat Aktif</span>
                    <span class="apple-pill muted"><i class="fas fa-arrows-split-up-and-left me-1"></i> Workflow Berurutan</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                <a href="{{ route('admin.soal.pdf') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-file-pdf me-1"></i> Dari PDF
                </a>
                <a href="{{ route('admin.soal.bulk.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-microchip me-1"></i> Mode Massal
                </a>
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2">
                    <i class="fas fa-database me-1"></i> Bank Soal
                </a>
            </div>
        </div>
    </div>

    {{-- Link ke Struktur Ujian jika sub kategori kosong --}}
    @if(($subCategories ?? collect())->isEmpty())
    <div class="apple-glass apple-glass-card p-3 mb-3 d-flex align-items-start gap-3" style="background: rgba(234,179,8,0.08); border-color: rgba(234,179,8,0.2);">
        <i class="fas fa-triangle-exclamation text-warning mt-1"></i>
        <div class="flex-grow-1">
            <div class="fw-700 mb-1">Sub kategori belum ada</div>
            <div class="apple-hint">Buat formasi yang rapi di <strong>Struktur Ujian</strong>. Untuk saat ini yang dipakai: SKB = D3 Manajemen Informatika dan S1 Teknik Informatika. PPPK = D3 Farmasi, D3 Manajemen Informatika, dan S1 Teknik Informatika.</div>
        </div>
        <a href="{{ route('admin.exam-structure.index') }}" class="apple-glass-btn apple-btn-outline flex-shrink-0">
            <i class="fas fa-sitemap me-2"></i>Struktur Ujian
        </a>
    </div>
    @endif

        <div class="row g-4">
            <div class="col-lg-7 order-1 order-lg-1">
            @if(isset($dynamicExamReady) && !$dynamicExamReady)
                <div class="apple-glass apple-glass-card p-3 mb-3" style="background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.22);">
                    <div class="fw-700 mb-1">Mode ujian masih fallback</div>
                    <div class="apple-hint">Mode ujian dinamis tetap ditampilkan untuk kesiapan UI, tetapi penyimpanan mode penuh baru aktif setelah <code>php artisan migrate</code>.</div>
                </div>
            @endif
            <div class="apple-glass apple-glass-card p-4 p-md-5">
                @if(session('error'))
                    <div class="alert border-0 apple-glass-soft apple-alert d-flex align-items-start gap-2 mb-4">
                        <i class="fas fa-triangle-exclamation text-danger mt-1"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form action="{{ route('admin.soal.generate') }}" method="POST" enctype="multipart/form-data" id="formAi" class="vstack gap-4">
                    @csrf

                    <!-- Section: Target -->
                    <div class="vstack gap-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fw-800">Target & Kualifikasi</div>
                            <div class="small apple-hint">Atur alur dari umum ke spesifik</div>
                        </div>

                        <div class="apple-hint">
                            Pilih dari jalur paling umum dulu. Langkah berikutnya baru muncul setelah langkah sebelumnya dipilih supaya tidak menumpuk dropdown yang membingungkan.
                        </div>
                    </div>

                    <hr class="opacity-10 my-1">

                    <!-- Section: Mode & Kategori -->
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="step-kicker"><span class="step-kicker__dot">1</span> Langkah 1</div>
                            <label class="apple-label mb-2">Mode Ujian</label>
                            <div class="step-card-grid step-card-grid-compact mb-3" id="generateModeCards"></div>
                            <select name="exam_mode_id" class="form-select form-select-lg apple-glass-input step-native-select" id="examModeSelect">
                                <option value="" data-code="">-- Pilih mode ujian --</option>
                                @forelse(($examModes ?? collect()) as $mode)
                                    <option value="{{ $mode->id }}" data-code="{{ $mode->code }}" data-has-figural="{{ $mode->has_figural ? '1' : '0' }}"
                                        {{ (string) old('exam_mode_id', '') === (string) $mode->id ? 'selected' : '' }}>
                                        {{ $mode->name }}
                                    </option>
                                @empty
                                    <option value="">Mode default (fallback)</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-12" id="subCategoryField">
                            <div class="step-kicker"><span class="step-kicker__dot">2</span> Langkah 2</div>
                            <label class="apple-label mb-2" id="subCategoryLabel">Formasi / Sub Kategori</label>
                            <div class="step-card-grid step-card-grid-compact mb-3" id="generateSubCategoryCards"></div>
                            <select name="sub_category_id" class="form-select form-select-lg apple-glass-input step-native-select" id="subCategorySelect">
                                <option value="">-- Pilih formasi / jenis ujian --</option>
                                @foreach(($subCategories ?? collect()) as $sub)
                                    <option value="{{ $sub->id }}" {{ (string) old('sub_category_id') === (string) $sub->id ? 'selected' : '' }}>
                                        {{ $sub->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="apple-hint mt-2" id="subCategoryHint">Pilih formasi utama yang akan dipakai saat generate soal.</div>
                        </div>
                        <div class="col-12" id="kategoriField">
                            <div class="step-kicker"><span class="step-kicker__dot">3</span> Langkah 3</div>
                            <label class="apple-label mb-2" id="kategoriLabel">Kategori Soal</label>
                            <div class="step-card-grid step-card-grid-compact mb-3" id="generateKategoriCards"></div>
                            <select name="kategori" class="form-select form-select-lg apple-glass-input step-native-select" id="kategoriSelect">
                                <option value="">-- Pilih kategori soal --</option>
                            </select>
                            <div class="apple-hint mt-2" id="categoryHint">Kategori ini memakai jawaban benar/salah objektif.</div>
                        </div>
                        <div class="col-12 flow-guided-hidden" id="focusField">
                            <div class="step-kicker"><span class="step-kicker__dot">4</span> Langkah 4</div>
                            <label class="apple-label mb-2">Fokus Topik</label>
                            <select class="form-select form-select-lg apple-glass-input mb-2" id="focusTopicPresetSelect">
                                <option value="">Pilih fokus topik otomatis...</option>
                            </select>
                            <input type="text" name="fokus_topik" id="focusTopicInput" class="form-control form-control-lg apple-glass-input"
                                   placeholder="Contoh: Deret Angka, Bela Negara, Farmakologi, Analisis Resep..."
                                   value="{{ old('fokus_topik') }}">
                            <div class="apple-hint mt-2" id="focusTopicHint">Kalau dikosongkan, sistem akan pakai topik rekomendasi yang paling cocok.</div>

                            <div class="mt-3" id="topikField">
                                <label class="apple-label mb-2">Topik Opsional (Pendalaman)</label>
                                <div class="step-card-grid step-card-grid-compact mb-3" id="generateTopikCards"></div>
                                <select name="topik_sub_category_id" class="form-select form-select-lg apple-glass-input step-native-select" id="topikCategorySelect" data-old-value="{{ old('topik_sub_category_id') }}">
                                    <option value="">-- Opsional, pilih topik pendalaman --</option>
                                </select>
                                <div class="apple-hint mt-2" id="topikCategoryHint">Dipakai hanya kalau ingin soal makin spesifik. Pilihan ini juga membantu rekomendasi Fokus Topik.</div>
                            </div>
                        </div>

                        <div class="col-12 flow-guided-hidden" id="difficultyField">
                            <div class="step-kicker"><span class="step-kicker__dot">5</span> Langkah 5</div>
                            <label class="apple-label mb-2">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select form-select-lg apple-glass-input" id="difficultySelect">
                                <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                                <option value="medium" {{ old('difficulty', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                            </select>
                            <div class="apple-hint mt-2">Mengatur seberapa ringan atau menantang soal yang dihasilkan.</div>
                        </div>

                        <div class="col-12 flow-guided-hidden" id="engineField">
                            <div class="step-kicker"><span class="step-kicker__dot">6</span> Langkah 6</div>
                            <div class="runtime-panel">
                                <div class="runtime-panel__head">
                                    <div>
                                        <div class="fw-800">Mesin Generate</div>
                                        <div class="apple-hint">Provider dan mode eksekusi ditaruh terakhir supaya alurnya tidak terasa dipaksa.</div>
                                    </div>
                                    <span class="apple-pill muted">Eksekusi</span>
                                </div>
                                <div class="runtime-grid">
                                    <div class="runtime-card" id="providerField">
                                        <label class="apple-label mb-2">Provider AI</label>
                                        <div class="step-card-grid step-card-grid-compact mb-3" id="generateProviderCards"></div>
                                        <select name="provider" class="form-select form-select-lg apple-glass-input step-native-select" id="providerSelect">
                                            <option value="groq" {{ old('provider', 'groq') === 'groq' ? 'selected' : '' }}>Groq (Cloud - Fast)</option>
                                            <option value="openai" {{ old('provider') === 'openai' ? 'selected' : '' }}>OpenAI / ChatGPT (Premium)</option>
                                            <option value="ollama_qwen" {{ old('provider') === 'ollama_qwen' ? 'selected' : '' }}>Ollama Server (Private)</option>
                                        </select>
                                        <div class="apple-hint mt-2" id="providerHint">Groq: provider utama untuk generate. Pilih ini untuk alur normal.</div>
                                        <div class="mt-3" id="providerHealth">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="small apple-hint">Status Provider</div>
                                                <button type="button" class="apple-glass-btn apple-btn-outline py-1 px-3" id="btnProviderStatus">
                                                    <i class="fas fa-rotate me-2 opacity-75"></i>Cek
                                                </button>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                                <span class="badge rounded-pill bg-secondary" id="groqStatusBadge">Groq: belum dicek</span>
                                                <span class="badge rounded-pill bg-secondary" id="ollamaStatusBadge">Ollama: belum dicek</span>
                                            </div>
                                            <div class="small apple-muted mt-2" id="providerCheckNote">Auto-check terutama untuk Ollama supaya kamu tahu server lokal hidup atau tidak.</div>
                                        </div>
                                    </div>
                                    <div class="runtime-card">
                                        <label class="apple-label mb-2">Jumlah Soal</label>
                                        <input type="number" min="1" max="20" name="jumlah" id="jumlahInput" class="form-control form-control-lg apple-glass-input" value="{{ old('jumlah', 3) }}">
                                        <div class="apple-hint mt-2">Rekomendasi 1-10 untuk Groq mode cepat, atau 1-5 kalau ingin hasil lebih mudah dicek per putaran.</div>
                                    </div>
                                    <div class="runtime-card" id="ollamaModelRow">
                                        <div id="ollamaModelGroup" style="{{ old('provider') === 'ollama_qwen' ? '' : 'display:none;' }}">
                                            <label class="apple-label mb-2">Model Ollama</label>
                                            <select name="model" class="form-select form-select-lg apple-glass-input" id="modelSelect">
                                                <option value="qwen2.5-coder:7b" {{ old('model', 'qwen2.5-coder:7b') === 'qwen2.5-coder:7b' ? 'selected' : '' }}>qwen2.5-coder:7b - Default</option>
                                                <option value="llama3.2:3b" {{ old('model') === 'llama3.2:3b' ? 'selected' : '' }}>llama3.2:3b - Hemat/Cepat</option>
                                                <option value="deepseek-coder-v2:lite" {{ old('model') === 'deepseek-coder-v2:lite' ? 'selected' : '' }}>deepseek-coder-v2:lite - Reasoning Berat</option>
                                                <option value="codestral:latest" {{ old('model') === 'codestral:latest' ? 'selected' : '' }}>codestral:latest - Revisi/Audit</option>
                                                <option value="mistral:latest" {{ old('model') === 'mistral:latest' ? 'selected' : '' }}>mistral:latest - Umum</option>
                                            </select>
                                            <label class="runtime-toggle mt-3">
                                                <input type="checkbox" name="ollama_fast" value="1" id="ollamaFastToggle" class="form-check-input mt-1" {{ old('ollama_fast', '1') ? 'checked' : '' }}>
                                                <span class="runtime-toggle__body">
                                                    <span class="runtime-toggle__title">Ollama Fast</span>
                                                    <span class="runtime-toggle__hint" id="ollamaFastHint">Pakai `llama3.2:3b` otomatis untuk tes/generate ringan.</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="runtime-card" id="fastModeGroup">
                                        <label class="apple-label mb-2 d-block">Mode Eksekusi Cepat</label>
                                        <label class="runtime-toggle">
                                            <input type="checkbox" name="fast_mode" value="1" id="fastModeToggle" class="form-check-input mt-1" {{ old('fast_mode', '1') ? 'checked' : '' }}>
                                            <span class="runtime-toggle__body">
                                                <span class="runtime-toggle__title">Aktifkan mode cepat</span>
                                                <span class="runtime-toggle__hint" id="fastModeHelp">Tanpa antrian. Cocok untuk sampai 10 soal.</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="apple-glass-soft apple-glass-card p-3">
                        <div class="fw-700 mb-2">Alur Pilih Soal</div>
                        <div class="apple-hint" id="flowHint">Mulai dari mode ujian, lalu pilih formasi atau jenis ujian. Setelah itu baru kategori dan topik teknis jika memang dibutuhkan.</div>
                    </div>

                    <div class="badge-status-wrap d-flex flex-wrap gap-2">
                        <span class="apple-pill muted" id="generateModeBadge">Belum Pilih Mode</span>
                        <span class="apple-pill muted" id="generateSubCategoryBadge">Belum Pilih Formasi</span>
                        <span class="apple-pill muted" id="generateKategoriBadge">Belum Pilih Kategori</span>
                        <span class="apple-pill muted" id="generateTopikBadge">Topik Opsional</span>
                        <span class="apple-pill muted pill-hidden" id="generateDifficultyBadge">Kesulitan</span>
                        <span class="apple-pill muted pill-hidden" id="generateEngineBadge">Mesin</span>
                    </div>

                    <hr class="opacity-10 my-1">

                    <div class="position-relative" style="z-index: 5;">
                        <button type="submit" class="apple-glass-btn apple-btn-primary w-100 py-3" id="btnSubmit">
                            <span id="spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            <span id="textBtn"><i class="fas fa-wand-magic-sparkles me-2"></i>Generate</span>
                        </button>
                    </div>

                    <div class="d-lg-none d-grid gap-2">
                        <button type="button" class="apple-glass-btn apple-btn-outline w-100 py-3" id="pageStatusBtn">
                            <i class="fas fa-circle-info me-2"></i>Lihat Status Proses
                        </button>
                        <div class="apple-hint text-center">
                            Tombol aksi tetap ada di halaman agar alurnya konsisten dengan desktop.
                        </div>
                    </div>
                </form>

                {{-- IMPORTANT: separate form to avoid nested <form> which breaks desktop submit --}}
                <form id="categorizeOldForm" action="{{ route('admin.soal.categorize-old') }}" method="POST" class="d-none">
                    @csrf
                    <input type="hidden" name="limit" value="20">
                    <input type="hidden" name="provider" id="categorizeProvider" value="{{ old('provider', 'groq') }}">
                    <input type="hidden" name="model" id="categorizeModel" value="{{ old('model') }}">
                </form>
            </div>
        </div>

        <div class="col-lg-5 order-2 order-lg-2">
            <div class="apple-glass apple-glass-card p-4 p-md-4 position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div class="fw-800">Status Proses</div>
                    <div class="small apple-hint">
                        <span id="jobElapsed">0s</span>
                    </div>
                </div>

                <div id="jobAlert" class="apple-glass-soft apple-glass-card p-3 d-flex align-items-start gap-2 is-loading-panel">
                    <i class="fas fa-circle-info mt-1 opacity-75"></i>
                    <div class="flex-grow-1">
                        <div class="fw-700" id="jobMessage">Siap generate.</div>
                        <div class="small apple-hint mt-1" id="jobQueueMeta"></div>
                        <div class="mt-2" id="jobLogWrap"></div>
                    </div>
                </div>

                <details class="mt-3">
                    <summary class="small fw-700 opacity-75 apple-muted apple-summary">
                        Tips
                    </summary>
                    <div class="small mt-2 apple-hint">
                        Groq biasanya cepat untuk TWK, TIU, dan TKP. Untuk Figural, AI akan membuat prompt gambar terlebih dulu
                        dan kamu bisa upload gambar di menu "Kelola Figural".
                    </div>
                </details>
            </div>

            @if(!empty($recentPdfJobs ?? []))
            <div class="apple-glass apple-glass-card mt-4 p-4">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div class="fw-800">Job PDF Tersimpan</div>
                    <span class="apple-pill muted">Bisa retry tanpa upload ulang</span>
                </div>
                <div class="vstack gap-3">
                    @foreach($recentPdfJobs as $job)
                        @php
                            $jobIdRow = (string) ($job['job_id'] ?? '');
                            $jobStatus = (string) ($job['status'] ?? 'unknown');
                            $jobReq = is_array($job['request'] ?? null) ? $job['request'] : [];
                            $jobProgress = is_array($job['progress'] ?? null) ? $job['progress'] : [];
                            $jobDone = (int) ($jobProgress['done'] ?? 0);
                            $jobTotal = (int) ($jobProgress['total'] ?? ($jobReq['jumlah'] ?? 0));
                            $jobMode = (string) (($job['auto_detected']['exam_mode_name'] ?? '') ?: ($jobReq['exam_mode_id'] ?? 'Auto'));
                            $jobKategori = (string) (($job['auto_detected']['kategori'] ?? '') ?: ($jobReq['kategori'] ?? 'Auto'));
                            $jobSub = (string) (($job['auto_detected']['sub_category_name'] ?? '') ?: '');
                            $jobPdf = (string) ($jobReq['pdf_path'] ?? '');
                            $jobReason = (string) (($job['auto_detected']['reason'] ?? '') ?: ($job['message'] ?? ''));
                        @endphp
                        <div class="apple-glass-soft apple-glass-card p-3">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="fw-700" style="color: var(--text-primary);">{{ basename($jobPdf) ?: ('Job ' . $jobIdRow) }}</div>
                                    <div class="apple-hint mt-1">Status: {{ strtoupper($jobStatus) }} @if($jobTotal > 0) • {{ $jobDone }}/{{ $jobTotal }} soal @endif</div>
                                    <div class="apple-hint">Mode: {{ $jobMode ?: 'Auto' }} • Kategori: {{ $jobKategori ?: 'Auto' }}@if($jobSub !== '') • {{ $jobSub }}@endif</div>
                                    @if($jobReason !== '')
                                        <div class="apple-hint mt-1">{{ \Illuminate\Support\Str::limit($jobReason, 140) }}</div>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @if(in_array($jobStatus, ['error', 'failed', 'unknown'], true) || ($jobStatus === 'processing' && $jobDone < $jobTotal))
                                        <form method="POST" action="{{ route('admin.soal.retry') }}">
                                            @csrf
                                            <input type="hidden" name="job_id" value="{{ $jobIdRow }}">
                                            <button type="submit" class="apple-glass-btn apple-btn-primary">Retry / Lanjutkan</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <details class="apple-glass apple-glass-card mt-4" id="advancedSettingsPanel">
                <summary class="advanced-summary p-4 p-md-4">
                    <div class="settings-panel__head mb-0">
                        <div>
                            <div class="fw-800">Pengaturan Lanjutan</div>
                            <div class="apple-hint">Opsional. Default minimaze, dibuka saat perlu.</div>
                        </div>
                        <span class="apple-pill muted">Opsional</span>
                    </div>
                </summary>

                <div class="p-4 p-md-4 pt-0 vstack gap-3">
                    <div>
                        <label class="apple-label mb-2">Alur PDF Otomatis</label>
                        <div class="apple-glass-soft apple-glass-card p-3">
                            <div class="fw-700 mb-1">Upload PDF dipisahkan ke halaman khusus</div>
                            <div class="apple-hint mb-3">Supaya generate manual tidak bercampur dengan auto-detect PDF, proses upload, retry, log gagal, dan daftar file tersimpan sekarang punya halaman sendiri.</div>
                            <a href="{{ route('admin.soal.pdf') }}" class="apple-glass-btn apple-btn-primary">Buka Halaman PDF Otomatis</a>
                        </div>
                    </div>

                    <div class="tool-actions">
                        <a href="{{ route('admin.ai-check') }}" class="apple-glass-btn apple-btn-outline">
                            <i class="fas fa-stethoscope me-2 opacity-75"></i>Test AI
                        </a>
                        <button type="button" class="apple-glass-btn apple-btn-outline text-danger" id="btnResetQueue">
                            <i class="fas fa-broom me-2 opacity-75"></i>Reset Antrian
                        </button>
                    </div>

                    <div class="apple-glass-soft apple-glass-card p-3">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <div class="fw-800">Auto-Label</div>
                                <div class="apple-hint">Untuk soal lama yang belum punya sub-topik.</div>
                            </div>
                            <span class="badge rounded-pill {{ $uncategorizedCount > 0 ? 'bg-warning text-dark' : 'bg-success text-white' }}">
                                {{ (int) $uncategorizedCount }}
                            </span>
                        </div>
                        <div id="categorizeStatusPanel" class="mt-3 d-none">
                            <div class="progress" style="height: 6px; border-radius: 999px;">
                                <div id="categorizeBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <div class="mt-2" id="categorizeLogWrap"></div>
                        </div>
                        <button type="submit" form="categorizeOldForm" class="apple-glass-btn apple-btn-outline w-100 mt-3" id="btnCategorizeOld">
                            Labeli 20 Soal
                        </button>
                    </div>
                </div>
            </details>
        </div>
    </div>
</div>

@push('styles')
<style>
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

    .generate-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(1.25rem, 2vw, 1.8rem);
        background:
            radial-gradient(circle at top left, rgba(34, 211, 238, 0.16), transparent 34%),
            radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.18), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.015));
    }
    .generate-hero::after {
        content: "";
        position: absolute;
        inset: auto -12% -40% auto;
        width: 280px;
        height: 280px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(34, 211, 238, 0.10), transparent 68%);
        pointer-events: none;
    }
    .generate-hero__content {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(340px, 0.95fr);
        gap: 1.25rem;
        align-items: center;
    }
    .generate-hero__copy {
        max-width: 760px;
    }
    .generate-hero__subtitle {
        max-width: 58ch;
        font-size: 1.02rem;
        line-height: 1.7;
    }
    .generate-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 1rem;
    }
    .admin-command-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }
    .generate-hero__actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }
    .generate-action-card {
        display: flex;
        align-items: center;
        gap: 0.95rem;
        min-height: 92px;
        padding: 1rem 1.05rem;
        text-decoration: none;
        color: var(--text-primary);
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,0.08);
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.025));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }
    .generate-action-card:hover {
        transform: translateY(-2px);
        color: var(--text-primary);
        border-color: rgba(56, 189, 248, 0.24);
        background: linear-gradient(180deg, rgba(34,211,238,0.10), rgba(59,130,246,0.08));
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }
    .generate-action-card--wide {
        grid-column: span 2;
    }
    .generate-action-card__icon {
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        color: #dbeafe;
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.18), rgba(59, 130, 246, 0.22));
        border: 1px solid rgba(125, 211, 252, 0.24);
        font-size: 1.05rem;
    }
    .generate-action-card__body {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }
    .generate-action-card__title {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .generate-action-card__hint {
        color: var(--text-secondary);
        font-size: 0.83rem;
        line-height: 1.45;
    }

    .step-card-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .step-card-grid.step-card-grid-compact {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .runtime-panel {
        border-radius: 24px;
        padding: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
    }
    .runtime-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .runtime-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .runtime-card {
        min-width: 0;
        padding: 16px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
    }
    .runtime-toggle {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        width: 100%;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
    }
    .runtime-toggle__body {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }
    .runtime-toggle__title {
        font-weight: 800;
        color: var(--text-primary);
    }
    .runtime-toggle__hint {
        color: var(--text-secondary);
        font-size: 0.84rem;
        line-height: 1.45;
    }
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .settings-panel {
        padding: 18px;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,0.08);
        background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
    }
    .settings-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .tool-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .step-choice-card {
        text-align: left;
        min-height: 78px;
        padding: 14px 14px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        color: var(--text-primary);
        transition: transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }
    .step-choice-card:hover {
        transform: translateY(-2px);
        border-color: rgba(56, 189, 248, 0.24);
        background: rgba(255,255,255,0.06);
    }
    .step-choice-card.active {
        border-color: rgba(56, 189, 248, 0.34);
        background: linear-gradient(135deg, rgba(34,211,238,0.14), rgba(59,130,246,0.10));
        box-shadow: 0 18px 36px rgba(15,23,42,0.14);
    }
    .step-choice-card__title {
        font-weight: 800;
        line-height: 1.2;
        color: var(--text-primary);
    }
    .step-choice-card__hint {
        margin-top: 5px;
        color: var(--text-secondary);
        font-size: 0.8rem;
        line-height: 1.45;
    }
    .step-native-select {
        margin-top: 4px;
    }
    .generate-dashboard-shell.generate-dashboard-ready .step-native-select.step-native-select-hidden {
        display: none !important;
    }
    .step-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 0.76rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--text-secondary);
    }
    .step-kicker__dot {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        display: inline-grid;
        place-items: center;
        font-weight: 800;
        background: rgba(56, 189, 248, 0.14);
        border: 1px solid rgba(56, 189, 248, 0.28);
        color: #7dd3fc;
    }
    [data-bs-theme="light"] .step-choice-card {
        border-color: rgba(15,23,42,0.08);
        background: rgba(255,255,255,0.76);
    }

    [data-bs-theme="light"] .generate-hero {
        background:
            radial-gradient(circle at top left, rgba(14, 165, 233, 0.10), transparent 34%),
            radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.10), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.78), rgba(255,255,255,0.58));
    }
    [data-bs-theme="light"] .generate-action-card {
        border-color: rgba(15,23,42,0.08);
        background: linear-gradient(180deg, rgba(255,255,255,0.8), rgba(248,250,252,0.7));
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
    }
    [data-bs-theme="light"] .generate-action-card:hover {
        border-color: rgba(14, 165, 233, 0.22);
        background: linear-gradient(180deg, rgba(240,249,255,0.96), rgba(239,246,255,0.84));
        box-shadow: 0 18px 34px rgba(59, 130, 246, 0.10);
    }
    [data-bs-theme="light"] .generate-action-card__icon {
        color: #0f172a;
        background: linear-gradient(135deg, rgba(34,211,238,0.18), rgba(59,130,246,0.16));
        border-color: rgba(14, 165, 233, 0.14);
    }

    /* Page polish: keep status panel readable in light theme too */
    #jobAlert .fw-700 { color: var(--text-primary); }
    #jobLogWrap pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 220px;
        overflow: auto;
        border-radius: 14px;
        padding: 10px 12px;
        border: 1px solid var(--border-glass);
        background: rgba(0,0,0,0.18);
        color: var(--text-primary);
    }
    [data-bs-theme="light"] #jobLogWrap pre {
        background: rgba(2,6,23,0.04);
    }

    #categorizeLogWrap pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 220px;
        overflow: auto;
        border-radius: 14px;
        padding: 10px 12px;
        border: 1px solid var(--border-glass);
        background: rgba(0,0,0,0.18);
        color: var(--text-primary);
    }
    [data-bs-theme="light"] #categorizeLogWrap pre {
        background: rgba(2,6,23,0.04);
    }

    .apple-pill {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        padding: 4px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(8px);
        min-width: 60px;
        line-height: 1.2;
    }

    .badge-status-wrap .apple-pill {
        font-size: 0.85rem;
        padding: 8px 16px !important;
        border-radius: 999px !important;
    }

    .generate-dashboard-shell.generate-dashboard-ready .flow-guided-hidden {
        display: none !important;
    }
    .pill-hidden {
        display: none !important;
    }

    .advanced-summary {
        list-style: none;
        cursor: pointer;
        user-select: none;
    }
    .advanced-summary::-webkit-details-marker { display: none; }
    details#advancedSettingsPanel {
        overflow: hidden;
    }
    details#advancedSettingsPanel[open] .advanced-summary {
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .generate-toast {
        position: fixed;
        top: 88px;
        right: 18px;
        z-index: 4500;
        min-width: min(92vw, 360px);
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(16,185,129,0.22);
        background: linear-gradient(135deg, rgba(16,185,129,0.18), rgba(34,197,94,0.10));
        box-shadow: 0 22px 40px rgba(15,23,42,0.24);
        color: var(--text-primary);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }
    .generate-toast__title {
        font-weight: 800;
        margin-bottom: 3px;
    }

    .mode-skd { background: rgba(59,130,246,0.1); color: #60a5fa; border-color: rgba(59,130,246,0.2); }
    .mode-skb { background: rgba(139,92,246,0.1); color: #a78bfa; border-color: rgba(139,92,246,0.2); }
    .mode-pppk { background: rgba(234,179,8,0.1); color: #facc15; border-color: rgba(234,179,8,0.2); }
    [class*="mode-bidang"] { background: rgba(16,185,129,0.1) !important; color: #34d399 !important; border-color: rgba(16,185,129,0.2) !important; }
    .apple-pill.muted { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5); }
    [data-bs-theme="light"] .apple-pill.muted {
        background: rgba(2,6,23,0.05);
        color: #475569;
        border-color: rgba(15,23,42,0.08);
    }
    
    .apple-summary { cursor: pointer; user-select: none; }
    #btnSubmit { position: relative; z-index: 6; pointer-events: auto; }

    @media (max-width: 991.98px) {
        .generate-hero__content {
            grid-template-columns: 1fr;
        }
        .generate-hero__copy {
            max-width: none;
        }
        .generate-hero__actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .step-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .runtime-grid {
            grid-template-columns: 1fr;
        }
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .generate-dashboard-shell {
            padding-top: 1rem;
            padding-bottom: 1.5rem;
        }
        .generate-hero {
            padding: 1.15rem;
            border-radius: 22px;
        }
        .generate-hero__subtitle {
            font-size: 0.96rem;
            line-height: 1.65;
        }
        .generate-hero__meta {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .generate-hero__meta > * {
            width: 100%;
            justify-content: center;
        }
        .generate-hero__actions {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
            padding-bottom: 0.15rem;
            scrollbar-width: none;
        }
        .generate-hero__actions::-webkit-scrollbar {
            display: none;
        }
        .generate-action-card,
        .generate-action-card--wide {
            grid-column: auto;
            min-width: 220px;
            min-height: 88px;
        }
        .admin-command-bar {
            grid-template-columns: 1fr;
        }
        .generate-action-card__title {
            font-size: 0.95rem;
        }
        .generate-action-card__hint {
            font-size: 0.78rem;
        }
        .step-card-grid,
        .step-card-grid.step-card-grid-compact {
            grid-template-columns: 1fr;
        }
        .runtime-panel {
            padding: 14px;
            border-radius: 18px;
        }
        .settings-panel {
            padding: 14px;
            border-radius: 18px;
        }
        .runtime-card,
        .apple-glass-card,
        .apple-glass-soft.apple-glass-card {
            border-radius: 18px;
        }
        .badge-status-wrap {
            gap: 8px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const generateDashboardShell = document.getElementById('generateDashboardShell');
    generateDashboardShell?.classList.add('generate-dashboard-ready');

    const form = document.getElementById('formAi');
    const providerSelect = document.getElementById('providerSelect');
    const examModeSelect = document.getElementById('examModeSelect');
    const subCategorySelect = document.getElementById('subCategorySelect');
    const topikCategorySelect = document.getElementById('topikCategorySelect');
    const kategoriSelect = document.getElementById('kategoriSelect');
    const difficultySelect = document.getElementById('difficultySelect');
    const modelSelect = document.getElementById('modelSelect');
    const ollamaFastToggle = document.getElementById('ollamaFastToggle');
    const ollamaModelRow = document.getElementById('ollamaModelRow');
    const ollamaModelGroup = document.getElementById('ollamaModelGroup');
    const fastModeGroup = document.getElementById('fastModeGroup');
    const jumlahInput = document.getElementById('jumlahInput');
    const pdfInput = document.getElementById('pdfInput');
    const autoDetectPdfToggle = document.getElementById('autoDetectPdfToggle');
    const autoDetectPdfHelp = document.getElementById('autoDetectPdfHelp');
    const fastModeToggle = document.getElementById('fastModeToggle');
    const fastModeHelp = document.getElementById('fastModeHelp');
    const providerHint = document.getElementById('providerHint');
    const subCategoryHint = document.getElementById('subCategoryHint');
    const subCategoryLabel = document.getElementById('subCategoryLabel');
    const topikCategoryHint = document.getElementById('topikCategoryHint');
    const kategoriLabel = document.getElementById('kategoriLabel');
    const flowHint = document.getElementById('flowHint');
    const subCategoryField = document.getElementById('subCategoryField');
    const topikField = document.getElementById('topikField');
    const kategoriField = document.getElementById('kategoriField');
    const providerField = document.getElementById('providerField');
    const focusField = document.getElementById('focusField');
    const difficultyField = document.getElementById('difficultyField');
    const engineField = document.getElementById('engineField');
    const generateModeCards = document.getElementById('generateModeCards');
    const generateSubCategoryCards = document.getElementById('generateSubCategoryCards');
    const generateTopikCards = document.getElementById('generateTopikCards');
    const generateProviderCards = document.getElementById('generateProviderCards');
    const generateKategoriCards = document.getElementById('generateKategoriCards');
    const focusTopicPresetSelect = document.getElementById('focusTopicPresetSelect');
    const focusTopicInput = document.getElementById('focusTopicInput');
    const focusTopicHint = document.getElementById('focusTopicHint');
    const generateModeBadge = document.getElementById('generateModeBadge');
    const generateSubCategoryBadge = document.getElementById('generateSubCategoryBadge');
    const generateKategoriBadge = document.getElementById('generateKategoriBadge');
    const generateTopikBadge = document.getElementById('generateTopikBadge');
    const generateDifficultyBadge = document.getElementById('generateDifficultyBadge');
    const generateEngineBadge = document.getElementById('generateEngineBadge');

    const btnSubmit = document.getElementById('btnSubmit');
    const spinner = document.getElementById('spinner');
    const textBtn = document.getElementById('textBtn');

    const btnResetQueue = document.getElementById('btnResetQueue');
    const btnProviderStatus = document.getElementById('btnProviderStatus');
    const groqStatusBadge = document.getElementById('groqStatusBadge');
    const ollamaStatusBadge = document.getElementById('ollamaStatusBadge');
    const providerCheckNote = document.getElementById('providerCheckNote');
    const btnCategorizeOld = document.getElementById('btnCategorizeOld');
    const categorizeProvider = document.getElementById('categorizeProvider');
    const categorizeModel = document.getElementById('categorizeModel');

    const categorizeStatusPanel = document.getElementById('categorizeStatusPanel');
    const categorizePct = document.getElementById('categorizePct');
    const categorizeCount = document.getElementById('categorizeCount');
    const categorizeBar = document.getElementById('categorizeBar');
    const categorizeMeta = document.getElementById('categorizeMeta');
    const categorizeLastError = document.getElementById('categorizeLastError');
    const categorizeLogWrap = document.getElementById('categorizeLogWrap');
    const categorizeElapsed = document.getElementById('categorizeElapsed');

    const jobMessage = document.getElementById('jobMessage');
    const jobQueueMeta = document.getElementById('jobQueueMeta');
    const jobLogWrap = document.getElementById('jobLogWrap');
    const jobElapsed = document.getElementById('jobElapsed');

    const pagePrimaryBtn = document.getElementById('btnSubmit');
    const mobileDockDot = document.getElementById('dockStatusDot');
    const pageStatusBtn = document.getElementById('pageStatusBtn');
    const mobileFallbackWrap = document.getElementById('mobileGenerateFallbackWrap');
    const subCategoryApiBase = @json(route('pref.sub-categories'));
    const examPackagesApiBase = @json(route('pref.exam-packages'));
    const initialPackages = @json(($modePackages ?? collect())->values());
    const focusTopicPresets = @json($focusTopicPresets ?? []);
    let cachedTechnicalTopicRows = [];

    const jobId = @json($jobId);
    let fastModeTouched = false;
    let lastAutoTechnicalTopic = '';
    let lastAutoFocusTopic = '';
    fastModeToggle?.addEventListener('change', () => { fastModeTouched = true; });

    function syncOllamaFastMode(force = false) {
        const isOllama = providerSelect?.value === 'ollama_qwen';
        if (!modelSelect) return;

        if (!isOllama) {
            modelSelect.disabled = true;
            return;
        }

        if (ollamaFastToggle?.checked) {
            if (force || modelSelect.value !== 'llama3.2:3b') {
                modelSelect.value = 'llama3.2:3b';
            }
            modelSelect.disabled = true;
        } else {
            modelSelect.disabled = false;
        }
    }

    function escapeHtml(str) {
        const s = String(str ?? '');
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return s.replace(/[&<>"']/g, (m) => map[m]);
    }

    function renderLog(lines) {
        if (!Array.isArray(lines) || lines.length === 0) return '';
        const text = lines.map(l => String(l ?? '')).join('\n');
        return '<pre>' + escapeHtml(text) + '</pre>';
    }

    function setCategorizePanelVisible(visible) {
        if (!categorizeStatusPanel) return;
        categorizeStatusPanel.classList.toggle('d-none', !visible);
    }

    function setCategorizeUI({ pct, done, total, errors, meta, lastError, logLines }) {
        if (categorizePct) categorizePct.textContent = (pct ?? 0) + '%';
        if (categorizeCount) {
            const errText = (errors && errors > 0) ? ` (error: ${errors})` : '';
            categorizeCount.textContent = `${done ?? 0}/${total ?? 0}${errText}`;
        }
        if (categorizeBar) categorizeBar.style.width = (pct ?? 0) + '%';
        if (categorizeMeta) categorizeMeta.textContent = meta ?? '';
        if (categorizeLogWrap) categorizeLogWrap.innerHTML = renderLog(logLines) || '';

        if (categorizeLastError) {
            const show = !!(lastError && String(lastError).trim() !== '');
            categorizeLastError.classList.toggle('d-none', !show);
            if (show) categorizeLastError.textContent = 'Error terakhir: ' + String(lastError).slice(0, 240);
        }
    }

    function setElapsedText(el, queuedAtIso) {
        if (!el || !queuedAtIso) return;
        const t0 = new Date(queuedAtIso).getTime();
        if (!isFinite(t0)) return;
        const s = Math.max(0, Math.floor((Date.now() - t0) / 1000));
        el.textContent = s + 's';
    }

    function setStatus(kind, messageHtml, queueMetaHtml, logLines) {
        const icon = (kind === 'success') ? 'fa-check-circle text-success'
            : (kind === 'error') ? 'fa-triangle-exclamation text-danger'
            : (kind === 'processing') ? 'fa-spinner fa-spin opacity-75'
            : 'fa-circle-info opacity-75';
        const iconEl = document.querySelector('#jobAlert > i');
        const alertEl = document.getElementById('jobAlert');
        if (iconEl) iconEl.className = 'fas ' + icon;
        if (alertEl) {
            const shouldShimmer = kind === 'processing' && (!logLines || !logLines.length);
            alertEl.classList.toggle('is-loading-panel', shouldShimmer);
        }
        if (jobMessage) jobMessage.innerHTML = messageHtml;
        if (jobQueueMeta) jobQueueMeta.innerHTML = queueMetaHtml || '';
        if (jobLogWrap) jobLogWrap.innerHTML = renderLog(logLines) || '';
    }

    function fillSelectOptions(selectEl, rows, placeholder, oldValue = '') {
        if (!selectEl) return;
        const wanted = String(oldValue || selectEl.dataset.oldValue || selectEl.value || '').trim();
        selectEl.innerHTML = `<option value="">${placeholder}</option>`;
        rows.forEach((row) => {
            const opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = row.name;
            if (wanted !== '' && String(row.id) === wanted) {
                opt.selected = true;
            }
            selectEl.appendChild(opt);
        });
        selectEl.value = wanted && Array.from(selectEl.options).some((opt) => String(opt.value) === wanted) ? wanted : '';
        selectEl.dataset.oldValue = '';
    }

    function syncTechnicalTopicInput(force = false) {
        if (!topikCategorySelect || !focusTopicInput) return;
        const selectedText = String(topikCategorySelect?.selectedOptions?.[0]?.textContent || '').trim();
        if (!topikCategorySelect.value || selectedText === '') return;

        const current = String(focusTopicInput.value || '').trim();
        if (force || current === '' || current === lastAutoTechnicalTopic) {
            focusTopicInput.value = selectedText;
            lastAutoTechnicalTopic = selectedText;
        }
    }

    function getSelectedModeCode() {
        return String(examModeSelect?.selectedOptions?.[0]?.dataset?.code || '').toLowerCase();
    }

    function triggerNativeChange(selectEl) {
        if (!selectEl) return;
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function isSkdFiguralTrackSelected() {
        return getSelectedModeCode() === 'skd' && String(subCategorySelect?.dataset?.selectedSkdTrack || '') === 'FIGURAL';
    }

    function renderGenerateModeCards() {
        if (!generateModeCards || !examModeSelect) return;
        generateModeCards.innerHTML = '';
        Array.from(examModeSelect.options || []).forEach((opt) => {
            if (!String(opt.value || '').trim()) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'step-choice-card' + (String(examModeSelect.value) === String(opt.value) ? ' active' : '');
            btn.innerHTML = `<div class="step-choice-card__title">${opt.textContent}</div><div class="step-choice-card__hint">Tentukan jalur ujian dulu, nanti formasi dan kategori ikut menyesuaikan.</div>`;
            btn.addEventListener('click', async () => {
                examModeSelect.value = opt.value;
                renderGenerateModeCards();
                triggerNativeChange(examModeSelect);
                if (isMobileUi()) scrollToField(subCategoryField);
            });
            generateModeCards.appendChild(btn);
        });
    }

    function renderGenerateProviderCards() {
        if (!generateProviderCards || !providerSelect) return;
        const hints = {
            groq: 'Primary untuk request cepat, interaktif, dan generate utama.',
            ollama_qwen: 'Secondary untuk tugas privat, batch, audit, dan klasifikasi internal.',
        };
        generateProviderCards.innerHTML = '';
        Array.from(providerSelect.options || []).forEach((opt) => {
            if (!String(opt.value || '').trim()) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'step-choice-card' + (String(providerSelect.value) === String(opt.value) ? ' active' : '');
            btn.innerHTML = `<div class="step-choice-card__title">${opt.textContent}</div><div class="step-choice-card__hint">${hints[String(opt.value)] || 'Pilih provider ini untuk generate.'}</div>`;
            btn.addEventListener('click', () => {
                providerSelect.value = opt.value;
                renderGenerateProviderCards();
                triggerNativeChange(providerSelect);
                scheduleAutoProviderPing(true);
                if (isMobileUi()) scrollToField(engineField);
            });
            generateProviderCards.appendChild(btn);
        });
    }

    function renderGenerateSubCategoryCards() {
        if (!generateSubCategoryCards || !subCategorySelect) return;
        generateSubCategoryCards.innerHTML = '';
        Array.from(subCategorySelect.options || []).forEach((opt) => {
            if (!String(opt.value || '').trim()) return;
            const label = String(opt.textContent || '').trim();
            const hint = getSelectedModeCode() === 'skd'
                ? 'Pilih jenis ujian ini supaya generate langsung fokus ke materi utama.'
                : 'Pilih formasi utama agar arah soal lebih spesifik.';
            const btn = document.createElement('button');
            btn.type = 'button';
            const isActive = !isSkdFiguralTrackSelected() && String(subCategorySelect.value) === String(opt.value);
            btn.className = 'step-choice-card' + (isActive ? ' active' : '');
            btn.innerHTML = `<div class="step-choice-card__title">${label}</div><div class="step-choice-card__hint">${hint}</div>`;
            btn.addEventListener('click', () => {
                delete subCategorySelect.dataset.selectedSkdTrack;
                subCategorySelect.value = opt.value;
                renderGenerateSubCategoryCards();
                triggerNativeChange(subCategorySelect);
                if (isMobileUi()) scrollToField(kategoriField);
            });
            generateSubCategoryCards.appendChild(btn);
        });

        if (getSelectedModeCode() === 'skd') {
            const tiuOption = Array.from(subCategorySelect.options || []).find((opt) => String(opt.textContent || '').trim().toUpperCase() === 'TIU');
            const hasFiguralCategory = Array.from(kategoriSelect?.options || []).some((opt) => String(opt.value || '').trim().toUpperCase() === 'FIGURAL');
            if (tiuOption && hasFiguralCategory) {
                const figuralBtn = document.createElement('button');
                figuralBtn.type = 'button';
                figuralBtn.className = 'step-choice-card' + (isSkdFiguralTrackSelected() ? ' active' : '');
                figuralBtn.innerHTML = '<div class="step-choice-card__title">Figural / TIU</div><div class="step-choice-card__hint">Pilih jalur figural yang tetap masuk rumpun TIU.</div>';
                figuralBtn.addEventListener('click', () => {
                    subCategorySelect.dataset.selectedSkdTrack = 'FIGURAL';
                    subCategorySelect.value = tiuOption.value;
                    renderGenerateSubCategoryCards();
                    triggerNativeChange(subCategorySelect);
                    if (isMobileUi()) scrollToField(kategoriField);
                });
                generateSubCategoryCards.appendChild(figuralBtn);
            }
        }
    }

    function renderGenerateTopikCards() {
        if (!generateTopikCards || !topikCategorySelect) return;
        generateTopikCards.innerHTML = '';
        Array.from(topikCategorySelect.options || []).forEach((opt) => {
            if (!String(opt.value || '').trim()) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'step-choice-card' + (String(topikCategorySelect.value) === String(opt.value) ? ' active' : '');
            btn.innerHTML = `<div class="step-choice-card__title">${String(opt.textContent || '').trim()}</div><div class="step-choice-card__hint">Tambahkan fokus teknis ini agar soal lebih mengerucut.</div>`;
            btn.addEventListener('click', () => {
                topikCategorySelect.value = opt.value;
                renderGenerateTopikCards();
                triggerNativeChange(topikCategorySelect);
            });
            generateTopikCards.appendChild(btn);
        });
    }

    function renderGenerateKategoriCards() {
        if (!generateKategoriCards || !kategoriSelect) return;
        const modeCode = getSelectedModeCode();
        generateKategoriCards.innerHTML = '';
        Array.from(kategoriSelect.options || []).forEach((opt) => {
            if (!String(opt.value || '').trim()) return;
            const title = String(opt.textContent || opt.value).trim();
            const hint = modeCode === 'skd'
                ? 'Untuk SKD, kategori ini mengikuti jenis latihan yang dipilih.'
                : 'Pilih karakter soal yang ingin dibuat pada formasi aktif.';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'step-choice-card' + (String(kategoriSelect.value) === String(opt.value) ? ' active' : '');
            btn.innerHTML = `<div class="step-choice-card__title">${title}</div><div class="step-choice-card__hint">${hint}</div>`;
            btn.addEventListener('click', () => {
                kategoriSelect.value = opt.value;
                renderGenerateKategoriCards();
                triggerNativeChange(kategoriSelect);
                if (isMobileUi()) scrollToField(providerField);
            });
            generateKategoriCards.appendChild(btn);
        });
    }

    function syncSkdCategoryBridge(source = 'sub') {
        if (getSelectedModeCode() !== 'skd') return;
        const mapLabelToKategori = {
            'TWK': 'TWK',
            'TIU': 'TIU',
            'TKP': 'TKP',
            'FIGURAL': 'FIGURAL',
            'FIGURAL / TIU': 'FIGURAL',
        };

        if (source === 'sub') {
            if (isSkdFiguralTrackSelected()) {
                const figuralOption = Array.from(kategoriSelect?.options || []).find((opt) => String(opt.value).toUpperCase() === 'FIGURAL');
                if (figuralOption) {
                    kategoriSelect.value = figuralOption.value;
                    return;
                }
            }
            const label = String(subCategorySelect?.selectedOptions?.[0]?.textContent || '').trim().toUpperCase();
            const target = mapLabelToKategori[label] || mapLabelToKategori[label.replace(/\s+/g, ' ')] || '';
            const option = Array.from(kategoriSelect?.options || []).find((opt) => String(opt.value).toUpperCase() === target);
            if (option) {
                kategoriSelect.value = option.value;
            }
        } else {
            const currentKategori = String(kategoriSelect?.value || '').toUpperCase();
            if (currentKategori === 'FIGURAL') {
                subCategorySelect.dataset.selectedSkdTrack = 'FIGURAL';
            } else {
                delete subCategorySelect.dataset.selectedSkdTrack;
            }
            const subOption = Array.from(subCategorySelect?.options || []).find((opt) => mapLabelToKategori[String(opt.textContent || '').trim().toUpperCase()] === currentKategori);
            if (subOption) {
                subCategorySelect.value = subOption.value;
            }
        }
    }

    function syncFlowLayout() {
        const modeCode = getSelectedModeCode();
        const isSkd = modeCode === 'skd';
        const hasModeSelection = String(examModeSelect?.value || '').trim() !== '';
        const hasTopikRows = Array.from(topikCategorySelect?.options || []).some((opt) => String(opt.value || '').trim() !== '');
        const hasSubSelection = String(subCategorySelect?.value || '').trim() !== '';
        const hasKategoriSelection = String(kategoriSelect?.value || '').trim() !== '';
        const hasTargetSelection = hasModeSelection && hasSubSelection;
        const hasCoreSelection = hasTargetSelection && hasKategoriSelection;

        if (subCategoryLabel) {
            subCategoryLabel.textContent = isSkd ? 'Jenis Ujian SKD' : 'Formasi / Sub Kategori';
        }
        if (kategoriLabel) {
            kategoriLabel.textContent = isSkd ? 'Kategori Otomatis' : 'Kategori Soal';
        }
        if (subCategoryHint) {
            subCategoryHint.textContent = !hasModeSelection
                ? 'Pilih mode ujian dulu agar formasi atau jenis ujian muncul.'
                : isSkd
                ? 'Untuk SKD, cukup pilih jenis ujian. Kategori akan mengikuti otomatis.'
                : (subCategorySelect?.value
                    ? 'Formasi ini jadi konteks utama pembuatan soal.'
                    : 'Pilih formasi utama dulu, baru lanjut ke kategori dan topik.');
        }
        if (flowHint) {
            flowHint.textContent = !hasModeSelection
                ? 'Langkah 1 wajib dipilih dulu. Setelah mode dipilih, sistem baru membuka langkah berikutnya.'
                : isSkd
                ? 'Urutan SKD: Mode Ujian -> Jenis Ujian -> Kategori -> Fokus Topik -> Kesulitan -> Mesin Generate. Topik pendalaman bersifat opsional.'
                : 'Urutan: Mode Ujian -> Formasi -> Kategori -> Fokus Topik -> Kesulitan -> Mesin Generate. Topik pendalaman bersifat opsional.';
        }

        if (kategoriField) {
            kategoriField.classList.toggle('flow-guided-hidden', !hasModeSelection || (!isSkd && !hasSubSelection));
        }
        if (subCategoryField) {
            subCategoryField.classList.toggle('flow-guided-hidden', !hasModeSelection);
        }
        if (topikField) {
            topikField.classList.toggle('flow-guided-hidden', !hasModeSelection || isSkd || !hasTopikRows || !hasSubSelection || !hasKategoriSelection);
        }
        if (providerField) {
            providerField.classList.toggle('flow-guided-hidden', !hasModeSelection || !hasKategoriSelection);
        }
        if (focusField) focusField.classList.toggle('flow-guided-hidden', !hasCoreSelection);
        if (difficultyField) difficultyField.classList.toggle('flow-guided-hidden', !hasCoreSelection);
        if (engineField) engineField.classList.toggle('flow-guided-hidden', !hasCoreSelection);
        examModeSelect?.classList.add('step-native-select-hidden');
        providerSelect?.classList.add('step-native-select-hidden');
        kategoriSelect?.classList.toggle('step-native-select-hidden', true);
        subCategorySelect?.classList.toggle('step-native-select-hidden', true);
        topikCategorySelect?.classList.toggle('step-native-select-hidden', true);
        if (generateSubCategoryCards) {
            generateSubCategoryCards.style.display = '';
        }
        if (generateTopikCards) {
            generateTopikCards.style.display = (!isSkd && hasTopikRows && hasSubSelection && hasKategoriSelection) ? '' : 'none';
        }

        if (isSkd) {
            syncSkdCategoryBridge('sub');
        }
        renderGenerateSubCategoryCards();
        renderGenerateTopikCards();
        renderGenerateKategoriCards();
    }

    async function refreshSubCategories() {
        if (!examModeSelect || !subCategorySelect || !topikCategorySelect) return;
        const examModeId = examModeSelect.value || '';
        const selected = examModeSelect.options[examModeSelect.selectedIndex];
        const examModeCode = String(selected?.dataset?.code || '');
        subCategorySelect.value = '';
        kategoriSelect.value = '';
        topikCategorySelect.value = '';
        fillSelectOptions(subCategorySelect, [], '-- Pilih formasi / jenis ujian --');
        fillSelectOptions(topikCategorySelect, [], '-- Opsional, pilih topik pendalaman --');
        if (!examModeId) {
            if (kategoriSelect) {
                kategoriSelect.innerHTML = '<option value="">-- Pilih kategori soal --</option>';
            }
            cachedTechnicalTopicRows = [];
            syncFlowLayout();
            updateGenerateBadges();
            syncFocusTopicOptions();
            return;
        }

        try {
            const [formasiRes, topikRes] = await Promise.all([
                fetch(subCategoryApiBase + '?kind=formasi&exam_mode_id=' + encodeURIComponent(examModeId) + '&exam_mode_code=' + encodeURIComponent(examModeCode), { credentials: 'same-origin' }),
                fetch(subCategoryApiBase + '?kind=topik&exam_mode_id=' + encodeURIComponent(examModeId) + '&exam_mode_code=' + encodeURIComponent(examModeCode), { credentials: 'same-origin' })
            ]);
            const formasiJson = await formasiRes.json();
            const topikJson = await topikRes.json();
            const formasiRows = Array.isArray(formasiJson?.data) ? formasiJson.data : [];
            cachedTechnicalTopicRows = Array.isArray(topikJson?.data) ? topikJson.data : [];
            const topikRows = filterTechnicalTopicRows(cachedTechnicalTopicRows);

            fillSelectOptions(subCategorySelect, formasiRows, '-- Pilih formasi / jenis ujian --');
            fillSelectOptions(topikCategorySelect, topikRows, '-- Opsional, pilih topik pendalaman --');

            if (subCategoryHint) {
                subCategoryHint.textContent = formasiRows.length
                    ? (getSelectedModeCode() === 'skd'
                        ? 'Untuk SKD, pilih jenis ujian yang ingin dibuat.'
                        : 'Pilih formasi utama yang akan dipakai saat generate soal.')
                    : 'Belum ada formasi untuk mode ini. Tambahkan dulu di Struktur Ujian.';
            }
            if (topikCategoryHint) {
                topikCategoryHint.textContent = topikRows.length
                    ? 'Topik teknis siap dipakai. Kamu juga tetap bisa isi Fokus Topik manual.'
                    : 'Belum ada topik teknis yang cocok untuk formasi ini. Pakai Fokus Topik di bawah atau tambahkan topik yang relevan di Struktur Ujian.';
            }
            if (getSelectedModeCode() === 'skd') {
                syncSkdCategoryBridge('sub');
            }
            renderGenerateSubCategoryCards();
            renderGenerateTopikCards();
            syncTechnicalTopicInput();
            syncFlowLayout();
            updateGenerateBadges();
            syncFocusTopicOptions();
        } catch (e) {
            if (subCategoryHint) subCategoryHint.textContent = 'Gagal memuat formasi. Coba refresh halaman.';
            if (topikCategoryHint) topikCategoryHint.textContent = 'Gagal memuat topik teknis. Coba refresh halaman.';
            syncFlowLayout();
            updateGenerateBadges();
            syncFocusTopicOptions();
        }
    }

    function updateGenerateBadges() {
        const modeCode = String(examModeSelect?.selectedOptions?.[0]?.dataset?.code || '').toLowerCase();
        const hasMode = !!String(examModeSelect?.value || '').trim();
        const modeText = hasMode ? String(examModeSelect?.selectedOptions?.[0]?.textContent || 'Pilih Mode') : 'Belum Pilih Mode';
        const subText = isSkdFiguralTrackSelected()
            ? 'Figural / TIU'
            : String(subCategorySelect?.selectedOptions?.[0]?.textContent || 'Pilih Formasi');
        const topikText = String(topikCategorySelect?.selectedOptions?.[0]?.textContent || 'Topik Opsional');
        const isBidangTeknis = /bidang teknis|apoteker|farmasi|it|komputer|informatika|terampil|ahli/i.test(subText);
        if (generateModeBadge) {
            generateModeBadge.textContent = modeText;
            generateModeBadge.className = 'apple-pill ' + (!hasMode ? 'muted' : (isBidangTeknis ? 'mode-bidang teknis' : (modeCode === 'skb' ? 'mode-skb' : (modeCode === 'pppk' ? 'mode-pppk' : 'mode-skd'))));
        }
        if (generateSubCategoryBadge) {
            generateSubCategoryBadge.textContent = subCategorySelect?.value ? subText : 'Belum Pilih Formasi';
            generateSubCategoryBadge.className = 'apple-pill ' + (isBidangTeknis ? 'mode-bidang teknis' : 'muted');
        }
        if (generateTopikBadge) {
            const fokus = String(focusTopicInput?.value || '').trim();
            const fokusShort = fokus.length > 26 ? (fokus.slice(0, 26).trimEnd() + 'ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦') : fokus;
            const fallback = fokus ? `Fokus: ${fokusShort}` : 'Topik Opsional';
            generateTopikBadge.textContent = topikCategorySelect?.value ? topikText : fallback;
            generateTopikBadge.className = 'apple-pill ' + ((topikCategorySelect?.value || fokus) ? 'mode-bidang teknis' : 'muted');
        }
    }

    // Chips summary (step order):
    // Mode Ujian -> Formasi/Sub Kategori -> Kategori Soal -> Fokus Topik -> Tingkat Kesulitan -> Mesin Generate
    function updateGenerateBadges() {
        const modeCode = String(examModeSelect?.selectedOptions?.[0]?.dataset?.code || '').toLowerCase();
        const hasMode = !!String(examModeSelect?.value || '').trim();
        const modeText = String(examModeSelect?.selectedOptions?.[0]?.textContent || '').trim();

        const subText = isSkdFiguralTrackSelected()
            ? 'Figural / TIU'
            : String(subCategorySelect?.selectedOptions?.[0]?.textContent || '').trim();
        const hasSub = !!String(subCategorySelect?.value || '').trim();

        const katText = String(kategoriSelect?.selectedOptions?.[0]?.textContent || '').trim();
        const hasKat = !!String(kategoriSelect?.value || '').trim();

        const hasCore = hasMode && hasSub && hasKat;
        const isBidangTeknis = /bidang teknis|apoteker|farmasi|it|komputer|informatika|terampil|ahli/i.test(subText);

        if (generateModeBadge) {
            generateModeBadge.textContent = modeText;
            generateModeBadge.className = 'apple-pill ' + (!hasMode ? 'muted pill-hidden' : (isBidangTeknis ? 'mode-bidang teknis' : (modeCode === 'skb' ? 'mode-skb' : (modeCode === 'pppk' ? 'mode-pppk' : 'mode-skd'))));
        }
        if (generateSubCategoryBadge) {
            generateSubCategoryBadge.textContent = subText;
            generateSubCategoryBadge.className = 'apple-pill ' + (!hasSub ? 'muted pill-hidden' : (isBidangTeknis ? 'mode-bidang teknis' : 'mode-skd'));
        }
        if (generateKategoriBadge) {
            generateKategoriBadge.textContent = katText;
            generateKategoriBadge.className = 'apple-pill ' + (!hasKat ? 'muted pill-hidden' : (isBidangTeknis ? 'mode-bidang teknis' : 'mode-skd'));
        }
        if (generateTopikBadge) {
            const fokus = String(focusTopicInput?.value || '').trim();
            const topikText = String(topikCategorySelect?.selectedOptions?.[0]?.textContent || '').trim();
            const fokusShort = fokus.length > 28 ? (fokus.slice(0, 28).trimEnd() + '...') : fokus;
            const label = topikCategorySelect?.value
                ? (topikText !== '' ? `Topik: ${topikText}` : 'Topik')
                : (fokus !== '' ? `Topik: ${fokusShort}` : 'Tanpa fokus topik');
            generateTopikBadge.textContent = label;
            generateTopikBadge.className = 'apple-pill ' + (!hasCore ? 'muted pill-hidden' : ((topikCategorySelect?.value || fokus) ? 'mode-bidang teknis' : 'muted'));
        }
        if (generateDifficultyBadge) {
            const diff = String(difficultySelect?.value || '').toLowerCase();
            const label = diff ? diff.toUpperCase() : 'KESULITAN';
            generateDifficultyBadge.textContent = label;
            generateDifficultyBadge.className = 'apple-pill ' + (!hasCore ? 'muted pill-hidden' : 'mode-bidang teknis');
        }
        if (generateEngineBadge) {
            const provider = String(providerSelect?.value || '').trim();
            generateEngineBadge.textContent = provider === 'ollama_qwen' ? 'Ollama' : 'Groq';
            generateEngineBadge.className = 'apple-pill ' + (!hasCore || provider === '' ? 'muted pill-hidden' : 'mode-bidang teknis');
        }
    }

    function resolveFormasiPresetKey() {
        const examModeCode = String(examModeSelect?.selectedOptions?.[0]?.dataset?.code || '').toLowerCase();
        const subCategoryName = String(subCategorySelect?.selectedOptions?.[0]?.textContent || '').toLowerCase();

        if (examModeCode === 'pppk' && /d3 farmasi/i.test(subCategoryName)) return 'pppk_asisten_apoteker';
        if (examModeCode === 'pppk' && /d3 manajemen informatika/i.test(subCategoryName)) return 'pppk_it_terampil';
        if (examModeCode === 'pppk' && /s1 teknik informatika/i.test(subCategoryName)) return 'pppk_it_ahli';
        if (examModeCode === 'pppk' && /s1 keperawatan/i.test(subCategoryName)) return 'pppk_keperawatan_s1';
        if (examModeCode === 'pppk' && /d4 anastesi/i.test(subCategoryName)) return 'pppk_anastesi_d4';

        if (examModeCode === 'skb' && /d3 farmasi/i.test(subCategoryName)) return 'skb_asisten_apoteker';
        if (examModeCode === 'skb' && /d3 manajemen informatika/i.test(subCategoryName)) return 'skb_it_terampil';
        if (examModeCode === 'skb' && /s1 teknik informatika/i.test(subCategoryName)) return 'skb_it_ahli';
        if (examModeCode === 'skb' && /s1 keperawatan/i.test(subCategoryName)) return 'skb_keperawatan_s1';
        if (examModeCode === 'skb' && /d4 anastesi/i.test(subCategoryName)) return 'skb_anastesi_d4';

        return examModeCode || 'skd';
    }

    function resolveFocusTopicPresetKey() {
        const baseKey = resolveFormasiPresetKey();
        const topikName = String(topikCategorySelect?.selectedOptions?.[0]?.textContent || '').toLowerCase();
        if (!topikCategorySelect?.value || topikName === '') return baseKey;

        if (/farmakologi klinik/i.test(topikName)) return 'skb_farmakologi_klinik';
        if (/farmasi klinik/i.test(topikName)) return 'skb_farmasi_klinik';
        if (/pelayanan kefarmasian/i.test(topikName)) return 'skb_pelayanan_kefarmasian';
        if (/analisis resep/i.test(topikName)) return 'skb_analisis_resep';
        if (/regulasi kefarmasian/i.test(topikName)) return 'skb_regulasi_kefarmasian';
        if (/studi kasus pasien/i.test(topikName)) return 'skb_studi_kasus_pasien';
        if (/farmakologi/i.test(topikName)) return 'pppk_farmakologi';
        if (/farmakoterapi/i.test(topikName)) return 'pppk_farmakoterapi';
        if (/farmasi klinis/i.test(topikName)) return 'pppk_farmasi_klinis';
        if (/pelayanan kefarmasian/i.test(topikName)) return 'pppk_pelayanan_kefarmasian';
        if (/resep dan dosis/i.test(topikName)) return 'pppk_resep_dosis';
        if (/regulasi obat/i.test(topikName)) return 'pppk_regulasi_obat';

        return baseKey;
    }

    function recommendedTechnicalTopicsForCurrentSubCategory() {
        const presetKey = resolveFormasiPresetKey();
        const presetRows = focusTopicPresets?.[presetKey] || {};
        const merged = [];
        Object.values(presetRows || {}).forEach((topics) => {
            if (!Array.isArray(topics)) return;
            topics.forEach((topic) => {
                const value = String(topic || '').trim();
                if (value && !merged.includes(value)) merged.push(value);
            });
        });
        return merged;
    }

    function filterTechnicalTopicRows(rows) {
        const modeCode = getSelectedModeCode();
        if (modeCode === 'skd' || !modeCode) return [];

        const selectedFormasi = String(subCategorySelect?.selectedOptions?.[0]?.textContent || '').trim();
        if (selectedFormasi === '') return [];

        const recommended = recommendedTechnicalTopicsForCurrentSubCategory();
        if (!recommended.length) return [];

        const recommendedLower = recommended.map((topic) => topic.toLowerCase().replace(/\s+/g, ' ').trim());
        return (Array.isArray(rows) ? rows : []).filter((row) => {
            const name = String(row?.name || '').trim().toLowerCase().replace(/\s+/g, ' ').trim();
            return recommendedLower.includes(name);
        });
    }

    function refreshFilteredTechnicalTopics(resetSelection = false) {
        if (!topikCategorySelect) return;
        if (resetSelection) {
            topikCategorySelect.dataset.oldValue = '';
            topikCategorySelect.value = '';
            lastAutoTechnicalTopic = '';
        }
        const topikRows = filterTechnicalTopicRows(cachedTechnicalTopicRows);
        fillSelectOptions(topikCategorySelect, topikRows, '-- Opsional, pilih topik pendalaman --');
        if (topikCategoryHint) {
            topikCategoryHint.textContent = topikRows.length
                ? 'Topik teknis sudah difilter sesuai formasi aktif. Pilih jika ingin soal makin spesifik.'
                : (subCategorySelect?.value
                    ? 'Belum ada topik teknis yang cocok untuk formasi ini. Isi Fokus Topik manual atau tambahkan topik yang relevan di Struktur Ujian.'
                    : 'Pilih formasi dulu supaya topik teknis yang relevan muncul.');
        }
    }

    function syncFocusTopicOptions() {
        if (!focusTopicPresetSelect) return;

        const category = String(kategoriSelect?.value || '').toUpperCase();
        const presetKey = resolveFocusTopicPresetKey();
        const presetRows = focusTopicPresets?.[presetKey] || focusTopicPresets?.skd || {};
        const options = Array.isArray(presetRows?.[category]) ? presetRows[category] : [];
        const previous = String(focusTopicInput?.value || '').trim();
        const technicalName = String(topikCategorySelect?.selectedOptions?.[0]?.textContent || '').trim();
        const mergedOptions = [...options];
        if (topikCategorySelect?.value && technicalName !== '' && !mergedOptions.includes(technicalName)) {
            mergedOptions.unshift(technicalName);
        }

        focusTopicPresetSelect.innerHTML = '<option value="">Pilih fokus topik otomatis...</option>';
        mergedOptions.forEach((topic) => {
            const opt = document.createElement('option');
            opt.value = topic;
            opt.textContent = topic;
            if (previous !== '' && previous === String(topic)) {
                opt.selected = true;
            }
            focusTopicPresetSelect.appendChild(opt);
        });

        const shouldAutoFill = mergedOptions.length
            && focusTopicInput
            && (
                previous === ''
                || previous === lastAutoFocusTopic
                || previous === lastAutoTechnicalTopic
            );

        if (shouldAutoFill) {
            const nextTopic = String(mergedOptions[0] || '').trim();
            if (nextTopic !== '') {
                focusTopicPresetSelect.value = nextTopic;
                focusTopicInput.value = nextTopic;
                lastAutoFocusTopic = nextTopic;
            }
        } else if (previous !== '') {
            lastAutoFocusTopic = previous;
        }

        if (focusTopicHint) {
            if (mergedOptions.length) {
                const modeLabel = String(examModeSelect?.selectedOptions?.[0]?.textContent || 'mode ini');
                focusTopicHint.textContent = `Topik rekomendasi untuk ${modeLabel} - ${category || 'kategori aktif'} sudah disiapkan. Bisa pilih dari dropdown atau ketik manual.`;
            } else {
                focusTopicHint.textContent = 'Belum ada preset topik khusus untuk kombinasi ini. Kamu tetap bisa ketik fokus topik manual.';
            }
        }
    }

    async function syncExamModeUi() {
        if (!examModeSelect) return;
        const selected = examModeSelect.options[examModeSelect.selectedIndex];
        const hasFigural = String(selected?.dataset?.hasFigural || '0') === '1';
        const currentValue = kategoriSelect?.value || '';
        let categories = [];

        try {
            const examModeId = examModeSelect.value || '';
            const examModeCode = String(selected?.dataset?.code || '');
            if (examModeId) {
                const res = await fetch(examPackagesApiBase + '?exam_mode_id=' + encodeURIComponent(examModeId) + '&exam_mode_code=' + encodeURIComponent(examModeCode), { credentials: 'same-origin' });
                const json = await res.json();
                categories = Array.isArray(json?.categories) ? json.categories : [];
            }
        } catch (e) {}

        if (!examModeSelect?.value) {
            if (kategoriSelect) {
                kategoriSelect.innerHTML = '<option value="">-- Pilih kategori soal --</option>';
            }
            syncFlowLayout();
            updateGenerateBadges();
            syncFocusTopicOptions();
            return;
        }

        if (!categories.length) {
            const code = String(selected?.dataset?.code || '').toLowerCase();
            if (code === 'pppk') {
                categories = [
                    { value: 'TEKNIS', label: 'Kompetensi Teknis', score_type: 'objective' },
                    { value: 'MANAJERIAL', label: 'Manajerial', score_type: 'objective' },
                    { value: 'SOSIAL_KULTURAL', label: 'Sosio-Kultural', score_type: 'objective' },
                    { value: 'WAWANCARA', label: 'Wawancara', score_type: 'objective' },
                ];
            } else if (code === 'skb') {
                categories = [
                    { value: 'SKB_TEKNIS', label: 'Materi Inti Bidang Jabatan', score_type: 'objective' },
                    { value: 'SKB_REGULASI', label: 'Regulasi & Undang-Undang', score_type: 'objective' },
                    { value: 'SKB_STUDI_KASUS', label: 'Studi Kasus Jabatan', score_type: 'objective' },
                ];
            } else {
                categories = [
                    { value: 'TWK', label: 'TWK', score_type: 'objective' },
                    { value: 'TIU', label: 'TIU', score_type: 'objective' },
                    { value: 'TKP', label: 'TKP', score_type: 'weighted_option' },
                ];
                if (hasFigural) categories.splice(2, 0, { value: 'FIGURAL', label: 'Figural', score_type: 'objective' });
            }
        }

        if (kategoriSelect) {
            kategoriSelect.innerHTML = '';
            categories.forEach((row) => {
                if (row.value === 'FIGURAL' && !hasFigural) return;
                const opt = document.createElement('option');
                opt.value = row.value;
                opt.textContent = row.label;
                opt.dataset.scoreType = row.score_type || 'objective';
                if (String(row.value) === String(currentValue)) opt.selected = true;
                kategoriSelect.appendChild(opt);
            });
            if (!currentValue) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '-- Pilih kategori soal --';
                kategoriSelect.insertBefore(placeholder, kategoriSelect.firstChild);
                kategoriSelect.value = '';
            } else if (!kategoriSelect.value) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '-- Pilih kategori soal --';
                kategoriSelect.insertBefore(placeholder, kategoriSelect.firstChild);
                kategoriSelect.value = '';
            }
        }

        if (getSelectedModeCode() === 'skd' && String(subCategorySelect?.value || '').trim() !== '') {
            syncSkdCategoryBridge('sub');
        }

        const selectedCategory = kategoriSelect?.selectedOptions?.[0];
        const scoreType = selectedCategory?.dataset?.scoreType || 'objective';
        const categoryHint = document.getElementById('categoryHint');
        if (categoryHint) {
            categoryHint.textContent = scoreType === 'objective'
                ? 'Kategori ini memakai jawaban benar/salah objektif.'
                : 'Kategori ini cocok untuk pola skoring bertingkat seperti TKP/PPPK.';
        }
        syncFlowLayout();
        updateGenerateBadges();
        syncFocusTopicOptions();
    }

    function syncProviderUi() {
        const isOllama = providerSelect.value === 'ollama_qwen';
        const isGroq = providerSelect.value === 'groq';

        if (ollamaModelRow) {
            // row includes both Model + fast toggle; we keep it but toggle pieces below
        }
        if (ollamaModelGroup) ollamaModelGroup.style.display = isOllama ? '' : 'none';
        syncOllamaFastMode();

        if (fastModeToggle) {
            fastModeToggle.disabled = !isGroq;
            if (fastModeGroup) fastModeGroup.style.display = isGroq ? '' : 'none';
        }

        if (providerHint) {
            providerHint.textContent = isGroq
                ? 'Groq: provider utama untuk generate. Pilih ini untuk alur normal.'
                : 'Ollama: provider secondary untuk batch/internal. Prosesnya lewat job (tidak menunggu browser) supaya tidak kena timeout. Jika gagal, sistem fallback ke Groq.';
        }

        // Auto turn off fast mode when not eligible.
        const jumlah = parseInt(jumlahInput?.value || '0', 10) || 0;
        const hasPdf = !!(pdfInput && pdfInput.files && pdfInput.files.length > 0);
        const eligibleFast = isGroq && !hasPdf && jumlah > 0 && jumlah <= 10;
        if (fastModeToggle) {
            if (!eligibleFast) {
                // Always force off when not eligible.
                fastModeToggle.checked = false;
            } else if (!fastModeTouched) {
                // Auto-on by default when eligible and user hasn't manually toggled.
                fastModeToggle.checked = true;
            }
            if (fastModeHelp) {
                fastModeHelp.textContent = eligibleFast
                    ? 'Tanpa antrian. Cocok untuk sampai 10 soal.'
                    : 'Nonaktif otomatis jika jumlah > 10 atau pakai PDF.';
            }
        }

        // Make the toggle feel balanced: when Groq, expand fast toggle to full width.
        // Advanced-only buttons should still exist, but make their label accurate
        if (textBtn) {
            textBtn.innerHTML = isGroq
                ? '<i class="fas fa-bolt me-2"></i>Generate'
                : '<i class="fas fa-leaf me-2"></i>Generate';
        }

        // Keep Auto-Label config aligned with current selection (provider + model).
        if (categorizeProvider) categorizeProvider.value = providerSelect.value;
        if (categorizeModel) categorizeModel.value = (isOllama ? (modelSelect?.value || '') : '');
        syncPdfAutoDetectUi();
        syncGenerateSubmitState();
    }

    function isPdfAutoDetectActive() {
        const hasPdf = !!(pdfInput && pdfInput.files && pdfInput.files.length > 0);
        return hasPdf && !!autoDetectPdfToggle?.checked;
    }

    function syncPdfAutoDetectUi() {
        const hasPdf = !!(pdfInput && pdfInput.files && pdfInput.files.length > 0);
        if (autoDetectPdfToggle) {
            autoDetectPdfToggle.disabled = !hasPdf;
            if (!hasPdf) autoDetectPdfToggle.checked = true;
        }
        if (autoDetectPdfHelp) {
            autoDetectPdfHelp.textContent = hasPdf
                ? 'AI akan membaca PDF, menebak mode, formasi, kategori, dan fokus topik, lalu memprosesnya di background.'
                : 'Aktif saat PDF diunggah. Cocok jika Anda ingin sistem memilih profil generate otomatis.';
        }
    }

    function syncGenerateSubmitState() {
        const hasMode = !!String(examModeSelect?.value || '').trim();
        const hasSub = !!String(subCategorySelect?.value || '').trim();
        const hasKategori = !!String(kategoriSelect?.value || '').trim();
        const pdfAutoDetect = isPdfAutoDetectActive();
        const canSubmit = pdfAutoDetect || (hasMode && hasSub && hasKategori);

        let label = '';
        if (pdfAutoDetect) {
            label = '<i class="fas fa-file-pdf me-2"></i>Proses PDF Otomatis';
        } else if (!hasMode) {
            label = '<i class="fas fa-layer-group me-2"></i>Pilih Mode Dulu';
        } else if (!hasSub) {
            label = '<i class="fas fa-diagram-project me-2"></i>Pilih Formasi / Jenis';
        } else if (!hasKategori) {
            label = '<i class="fas fa-list-check me-2"></i>Pilih Kategori Dulu';
        } else {
            label = providerSelect?.value === 'groq'
                ? '<i class="fas fa-bolt me-2"></i>Generate'
                : '<i class="fas fa-leaf me-2"></i>Generate';
        }

        if (btnSubmit) {
            btnSubmit.disabled = !canSubmit;
            btnSubmit.classList.toggle('opacity-50', !canSubmit);
            btnSubmit.title = canSubmit ? '' : 'Lengkapi dulu langkah wajib sebelum generate soal.';
        }
        if (textBtn) {
            textBtn.innerHTML = label;
        }
        if (mobileDockGenerateBtn) {
            mobileDockGenerateBtn.disabled = !canSubmit;
            mobileDockGenerateBtn.classList.toggle('opacity-50', !canSubmit);
        }
    }

    function isMobileUi() {
        return !!window.matchMedia && window.matchMedia('(max-width: 991.98px)').matches;
    }

    function scrollToField(el) {
        if (!el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    providerSelect?.addEventListener('change', (e) => {
        syncProviderUi();
        if (e && e.isTrusted) scheduleAutoProviderPing(true);
    });
    ollamaFastToggle?.addEventListener('change', () => syncProviderUi());
    modelSelect?.addEventListener('change', () => syncProviderUi());
    examModeSelect?.addEventListener('change', async (e) => {
        syncExamModeUi();
        await refreshSubCategories();
        syncGenerateSubmitState();
        if (e && e.isTrusted) {
            scrollToField(subCategoryField);
            setTimeout(() => subCategorySelect?.focus?.(), 150);
        }
    });
    subCategorySelect?.addEventListener('change', (e) => {
        if (String(subCategorySelect?.selectedOptions?.[0]?.textContent || '').trim().toUpperCase() !== 'TIU') {
            delete subCategorySelect.dataset.selectedSkdTrack;
        }
        refreshFilteredTechnicalTopics(true);
        syncSkdCategoryBridge('sub');
        syncFlowLayout();
        syncFocusTopicOptions();
        updateGenerateBadges();
        syncGenerateSubmitState();
        if (e && e.isTrusted) {
            scrollToField(kategoriField);
            setTimeout(() => kategoriSelect?.focus?.(), 150);
        }
    });
    topikCategorySelect?.addEventListener('change', () => {
        syncTechnicalTopicInput();
        syncFlowLayout();
        syncFocusTopicOptions();
        updateGenerateBadges();
        syncGenerateSubmitState();
    });
    kategoriSelect?.addEventListener('change', (e) => {
        syncSkdCategoryBridge('kategori');
        syncFlowLayout();
        syncFocusTopicOptions();
        updateGenerateBadges();
        syncGenerateSubmitState();
        if (e && e.isTrusted) {
            scrollToField(focusField);
            setTimeout(() => focusTopicInput?.focus?.(), 150);
        }
    });
    focusTopicPresetSelect?.addEventListener('change', () => {
        if (focusTopicInput && focusTopicPresetSelect?.value) {
            focusTopicInput.value = focusTopicPresetSelect.value;
            lastAutoFocusTopic = focusTopicPresetSelect.value;
            updateGenerateBadges();
        }
    });
    focusTopicInput?.addEventListener('input', () => {
        if (String(focusTopicInput.value || '').trim() !== lastAutoFocusTopic) {
            lastAutoFocusTopic = '';
        }
        updateGenerateBadges();
    });
    difficultySelect?.addEventListener('change', (e) => {
        updateGenerateBadges();
        if (e && e.isTrusted) {
            scrollToField(engineField);
            setTimeout(() => providerSelect?.focus?.(), 150);
        }
    });
    jumlahInput?.addEventListener('input', syncProviderUi);
    pdfInput?.addEventListener('change', () => {
        syncPdfAutoDetectUi();
        syncProviderUi();
    });
    autoDetectPdfToggle?.addEventListener('change', syncGenerateSubmitState);
    renderGenerateModeCards();
    renderGenerateProviderCards();
    syncPdfAutoDetectUi();
    syncProviderUi();
    syncFlowLayout();
    updateGenerateBadges();
    syncFocusTopicOptions();
    syncGenerateSubmitState();
    scheduleAutoProviderPing(true);

    if (String(examModeSelect?.value || '').trim() !== '') {
        syncExamModeUi();
        refreshSubCategories();
    }

    async function startCategorizeOld(e) {
        if (e) e.preventDefault();
        if (!btnCategorizeOld) return;

        setCategorizePanelVisible(true);
        setCategorizeUI({ pct: 0, done: 0, total: 0, errors: 0, meta: 'Mengirim permintaan...', lastError: '', logLines: [] });

        btnCategorizeOld.disabled = true;
        const oldHtml = btnCategorizeOld.innerHTML;
        btnCategorizeOld.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memulai...';

        try {
            const formEl = document.getElementById('categorizeOldForm');
            if (!formEl) throw new Error('Form Auto-Label tidak ditemukan.');

            const token = document.querySelector('input[name=\"_token\"]')?.value || '';
            const fd = new FormData(formEl);
            if (categorizeProvider) fd.set('provider', categorizeProvider.value || providerSelect.value);
            if (categorizeModel) fd.set('model', categorizeModel.value || (modelSelect?.value || ''));

            const res = await fetch(formEl.action, {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (!data || !data.ok || !data.job_id) {
                throw new Error((data && (data.error || data.message)) ? (data.error || data.message) : 'Gagal memulai auto-label.');
            }

            try { window.QueueCenter?.addJob?.('categorize', data.job_id); } catch (e) {}
            await pollCategorizeStatus(data.job_id);
        } catch (err) {
            setCategorizeUI({
                pct: 0, done: 0, total: 0, errors: 1,
                meta: 'Gagal memulai.', lastError: err && err.message ? err.message : String(err), logLines: []
            });
        } finally {
            btnCategorizeOld.disabled = false;
            btnCategorizeOld.innerHTML = oldHtml;
        }
    }

    async function pollCategorizeStatus(id) {
        if (!id) return;
        const urlBase = @json(route('admin.soal.categorize-status'));
        const stepUrl = @json(route('admin.soal.categorize-step'));
        const token = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || (document.querySelector('input[name=\"_token\"]')?.value || '');

        for (let i = 0; i < 360; i++) {
            try {
                const res = await fetch(urlBase + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' });
                const data = await res.json();

                if (data && data.queued_at) setElapsedText(categorizeElapsed, data.queued_at);

                const prog = (data && data.progress && typeof data.progress === 'object') ? data.progress : {};
                const total = parseInt(prog.total || 0, 10) || 0;
                const done = parseInt(prog.done || 0, 10) || 0;
                const errors = parseInt(prog.errors || 0, 10) || 0;
                const pct = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;

                const q = data.queue && typeof data.queue === 'object' ? data.queue : null;
                const stateLabel = q && q.state
                    ? ((q.state === 'running') ? 'Sedang dikerjakan worker'
                        : (q.state === 'queued') ? 'Dalam antrian'
                        : (q.state === 'scheduled') ? 'Terjadwal'
                        : 'Tidak terdeteksi di antrian')
                    : '';
                const metaQ = stateLabel ? ('Queue: ' + stateLabel) : '';

                setCategorizeUI({
                    pct,
                    done,
                    total,
                    errors,
                    meta: (data.status === 'processing')
                        ? ((data.message || 'Sedang diproses...') + (metaQ ? (' ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â· ' + metaQ) : ''))
                        : (data.message || metaQ),
                    lastError: data.last_error || (data.status === 'error' ? data.message : ''),
                    logLines: data.log,
                });

                // Groq "no-worker" mode: run small steps from the browser.
                if (data.runner === 'step' && (data.status === 'processing')) {
                    const stepRes = await fetch(stepUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify({ job_id: id }),
                        credentials: 'same-origin',
                    });
                    try {
                        const stepData = await stepRes.json();
                        if (stepData && stepData.cooldown) {
                            const cd = parseInt(stepData.cooldown, 10) || 0;
                            if (cd > 0 && categorizeMeta) {
                                categorizeMeta.textContent = (categorizeMeta.textContent || '') + ` ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â· Cooldown ${cd}s`;
                            }
                        }
                    } catch (e) {}
                }

                if (data.status === 'success' || data.status === 'error' || data.status === 'canceled') {
                    try { window.QueueCenter?.removeJob?.('categorize', id); } catch (e) {}
                    return;
                }

                await new Promise(r => setTimeout(r, 2000));
            } catch (e) {
                await new Promise(r => setTimeout(r, 2000));
            }
        }

        setCategorizeUI({ pct: 0, done: 0, total: 0, errors: 1, meta: 'Timeout menunggu auto-label. Coba refresh.', lastError: '', logLines: [] });
    }

    btnCategorizeOld?.addEventListener('click', startCategorizeOld);

    function setProviderBadge(el, label, ok, detail = '') {
        if (!el) return;
        el.textContent = detail ? `${label}: ${detail}` : label;
        el.className = 'badge rounded-pill ' + (ok === null ? 'bg-secondary' : (ok ? 'bg-success' : 'bg-danger'));
    }

    let lastProviderPingAt = 0;
    let providerPingTimer = null;

    function scheduleAutoProviderPing(force = false) {
        if (providerPingTimer) clearTimeout(providerPingTimer);
        providerPingTimer = setTimeout(() => {
            const now = Date.now();
            if (!force && now - lastProviderPingAt < 60000) return;
            lastProviderPingAt = now;
            checkProviderStatus(true);
        }, force ? 0 : 450);
    }

    async function checkProviderStatus(silent = false) {
        setProviderBadge(groqStatusBadge, 'Groq', null, 'mengecek');
        setProviderBadge(ollamaStatusBadge, 'Ollama', null, 'mengecek');
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const [groqRes, ollamaRes] = await Promise.all([
                fetch(@json(route('admin.ai-check.test')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ provider: 'groq', mode: 'ping', kategori: 'TIU' }),
                }),
                fetch(@json(route('admin.ai-check.test')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({
                        provider: 'ollama_qwen',
                        mode: 'ping',
                        kategori: 'TIU',
                        model: modelSelect?.value || 'qwen2.5-coder:7b',
                        ollama_fast: ollamaFastToggle?.checked ? 1 : 0,
                    }),
                })
            ]);
            const groqData = await groqRes.json();
            const ollamaData = await ollamaRes.json();
            setProviderBadge(
                groqStatusBadge,
                'Groq',
                !!groqData.ok,
                groqData.ok ? `siap (${groqData.latency_ms}ms)` : `gagal (${groqData.http || 0})`
            );

            let ollamaDetail = '';
            if (!ollamaData.ok) {
                ollamaDetail = `gagal (${ollamaData.http || 0})`;
            } else if (ollamaData.model_available === false) {
                ollamaDetail = `hidup, model tidak ada (${ollamaData.latency_ms}ms)`;
            } else if (ollamaData.model_loaded === false) {
                ollamaDetail = `hidup, model ada (belum loaded) (${ollamaData.latency_ms}ms)`;
            } else if (ollamaData.model_loaded === true) {
                ollamaDetail = `siap (${ollamaData.latency_ms}ms)`;
            } else {
                ollamaDetail = `hidup, model ada (${ollamaData.latency_ms}ms)`;
            }
            setProviderBadge(ollamaStatusBadge, 'Ollama', !!ollamaData.ok, ollamaDetail);
            if (providerCheckNote) {
                const prefix = silent ? 'Auto-cek' : 'Terakhir dicek';
                providerCheckNote.textContent = `${prefix}: ${new Date().toLocaleTimeString()} (Ollama ${ollamaData.ok ? 'siap' : 'gagal'}).`;
            }
        } catch (e) {
            setProviderBadge(groqStatusBadge, 'Groq', false, 'gagal cek');
            setProviderBadge(ollamaStatusBadge, 'Ollama', false, 'gagal cek');
            if (providerCheckNote) providerCheckNote.textContent = silent ? 'Auto-cek gagal. Klik Cek untuk detail.' : 'Gagal cek provider. Coba ulangi.';
        }
    }
    btnProviderStatus?.addEventListener('click', () => checkProviderStatus(false));

    async function resetQueue() {
        if (!btnResetQueue) return;
        const ok = confirm('Reset antrian? Ini akan menghapus semua job (jobs + failed_jobs).');
        if (!ok) return;

        btnResetQueue.disabled = true;
        btnResetQueue.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            const token = document.querySelector('input[name="_token"]')?.value || '';
            const res = await fetch(@json(route('admin.soal.reset-queue')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({}),
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (data.ok) {
                if (providerCheckNote) providerCheckNote.textContent = `Reset OK (jobs: ${data.deleted?.jobs ?? 0}, failed: ${data.deleted?.failed_jobs ?? 0})`;
            } else {
                if (providerCheckNote) providerCheckNote.textContent = 'Reset gagal';
            }
        } catch (e) {
            if (providerCheckNote) providerCheckNote.textContent = 'Reset gagal: ' + String(e && e.message ? e.message : e).slice(0, 110);
        } finally {
            btnResetQueue.disabled = false;
            btnResetQueue.innerHTML = '<i class="fas fa-broom me-2 opacity-75"></i>Reset Antrian';
        }
    }
    btnResetQueue?.addEventListener('click', resetQueue);

    let elapsedTimer = null;
    function startElapsed(queuedAtIso) {
        if (!queuedAtIso) return;
        const t0 = new Date(queuedAtIso).getTime();
        if (!isFinite(t0)) return;
        if (elapsedTimer) clearInterval(elapsedTimer);
        elapsedTimer = setInterval(() => {
            const s = Math.max(0, Math.floor((Date.now() - t0) / 1000));
            if (jobElapsed) jobElapsed.textContent = s + 's';
        }, 300);
    }

    async function pollJobStatus(id) {
        if (!id) return;
        if (mobileDockDot) mobileDockDot.classList.remove('d-none');
        const stepUrl = @json(route('admin.soal.step'));
        const token = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || (document.querySelector('input[name=\"_token\"]')?.value || '');
        for (let i = 0; i < 360; i++) { // up to ~15 minutes
            try {
                const res = await fetch(@json(route('admin.soal.status')) + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' });
                const data = await res.json();
                if (data && data.queued_at) startElapsed(data.queued_at);

                if (data && data.status === 'processing') {
                    const q = data.queue && typeof data.queue === 'object' ? data.queue : null;
                    let qText = '';
                    if (q && q.state) {
                        const stateLabel = (q.state === 'running') ? 'Sedang dikerjakan worker'
                            : (q.state === 'queued') ? 'Dalam antrian'
                            : (q.state === 'scheduled') ? 'Terjadwal'
                            : 'Tidak terdeteksi di antrian';
                        const att = (q.attempts != null) ? `, attempts: ${q.attempts}` : '';
                        const qName = q.name ? ` (${q.name})` : '';
                        qText = `Queue${qName}: ${stateLabel}${att}`;
                    }
                    const p = (data.progress && typeof data.progress === 'object')
                        ? ` (${data.progress.done || 0}/${data.progress.total || 0})`
                        : '';
                    const msg = escapeHtml(data.message || 'Sedang diproses...') + p;
                    setStatus('processing', msg, escapeHtml(qText), data.log);

                    // Groq no-worker mode: perform a small generation step each poll.
                    if (data.runner === 'step') {
                        await fetch(stepUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify({ job_id: id }),
                            credentials: 'same-origin',
                        });
                    }
                    await new Promise(r => setTimeout(r, 2500));
                    continue;
                }

                if (data && data.status === 'success') {
                    setStatus('success', escapeHtml(data.message || 'Selesai.'), '', data.log);
                    if (mobileDockDot) mobileDockDot.classList.add('d-none');
                    if (elapsedTimer) clearInterval(elapsedTimer);
                    try { window.QueueCenter?.removeJob?.('generate', id); } catch (e) {}
                    return;
                }

                if (data && data.status === 'error') {
                    setStatus('error', escapeHtml(data.message || 'Gagal.'), '', data.log);
                    if (mobileDockDot) mobileDockDot.classList.add('d-none');
                    if (elapsedTimer) clearInterval(elapsedTimer);
                    try { window.QueueCenter?.removeJob?.('generate', id); } catch (e) {}
                    return;
                }

                if (data && data.status === 'canceled') {
                    setStatus('error', 'Dibatalkan.', '', data.log);
                    if (mobileDockDot) mobileDockDot.classList.add('d-none');
                    if (elapsedTimer) clearInterval(elapsedTimer);
                    try { window.QueueCenter?.removeJob?.('generate', id); } catch (e) {}
                    return;
                }

                await new Promise(r => setTimeout(r, 2500));
            } catch (e) {
                await new Promise(r => setTimeout(r, 2500));
            }
        }
        if (elapsedTimer) clearInterval(elapsedTimer);
        setStatus('error', 'Timeout menunggu proses generate. Coba refresh halaman.', '', []);
        if (mobileDockDot) mobileDockDot.classList.add('d-none');
    }

    // init status box
    setStatus('idle', 'Siap generate.', '', []);
    if (jobId) {
        try { window.QueueCenter?.addJob?.('generate', jobId); } catch (e) {}
        setStatus('processing', 'Permintaan generate diterima. Menunggu update status...', '', []);
        pollJobStatus(jobId);
    }

    // If dock override isn't present for some reason, show an inline fallback button on mobile.
    if (!pagePrimaryBtn && mobileFallbackWrap) {
        mobileFallbackWrap.classList.remove('d-none');
    }

    form?.addEventListener('submit', (e) => {
        const hasMode = !!String(examModeSelect?.value || '').trim();
        const hasSub = !!String(subCategorySelect?.value || '').trim();
        const hasKategori = !!String(kategoriSelect?.value || '').trim();
        if (!(isPdfAutoDetectActive() || (hasMode && hasSub && hasKategori))) {
            e.preventDefault();
            syncGenerateSubmitState();
            return;
        }
        if (btnSubmit && spinner) {
            btnSubmit.disabled = true;
            spinner.classList.remove('d-none');
        }
        if (pagePrimaryBtn) {
            pagePrimaryBtn.disabled = true;
        }
    });

    if (pageStatusBtn) {
        pageStatusBtn.addEventListener('click', () => {
            const el = document.getElementById('jobAlert');
            if (!el) return;
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    const successToast = document.getElementById('generateSuccessToast');
    if (successToast) {
        setTimeout(() => {
            successToast.style.transition = 'opacity .25s ease, transform .25s ease';
            successToast.style.opacity = '0';
            successToast.style.transform = 'translateY(-6px)';
            setTimeout(() => successToast.remove(), 260);
        }, 3800);
    }

</script>
@endpush
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Bank',
            'icon' => 'fa-database',
            'href' => route('admin.soal-bank.index'),
        ],
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'form' => 'formAi',
            'style' => 'cta',
            'aria' => 'Generate Soal',
            'id' => 'dockGenerateSubmitBtn',
        ],
        [
            'label' => 'Audit',
            'icon' => 'fa-shield-halved',
            'href' => route('admin.audit-soal.index'),
            'dot_id' => 'dockStatusDot',
        ],
    ],
])
@endsection

