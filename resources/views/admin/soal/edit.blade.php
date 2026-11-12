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

    .option-badge {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(11, 37, 69, 0.08);
        border: 1px solid rgba(11, 37, 69, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
        flex-shrink: 0;
        color: var(--text-primary);
    }
    [data-bs-theme="dark"] .option-badge {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.15);
    }
</style>
@endpush

<div class="admin-shell">
    {{-- Page Hero --}}
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-pen-to-square"></i> Bank Soal Editor
                </div>
                <div class="d-flex align-items-center gap-3">
                    <h1 class="apple-title fs-2 fw-800 mb-0" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                        Edit Butir Soal
                    </h1>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fs-7 fw-bold font-monospace">
                        #ID: {{ $soal->id }}
                    </span>
                </div>
                <div class="apple-subtitle mt-1" style="max-width: 650px;">
                    Perbarui redaksional soal, pilihan ganda, kunci jawaban, dan taksonomi materi secara terintegrasi.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.audit-soal.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-shield-halved me-1"></i> Ke Panel Audit
                </a>
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2">
                    <i class="fas fa-database me-1"></i> Daftar Bank Soal
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert apple-alert border-0 mb-3" style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.25) !important;">
            <i class="fas fa-circle-check text-success me-2"></i> <strong>Berhasil:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert apple-alert border-0 mb-3" style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25) !important;">
            <i class="fas fa-circle-exclamation text-danger me-2"></i> <strong>Gagal:</strong> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert apple-alert border-0 mb-3" style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25) !important;">
            <div class="fw-800 mb-1 text-danger"><i class="fas fa-triangle-exclamation me-1"></i> Terdapat kendala validasi:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)
                    <li class="small">{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info Card Banner --}}
    <div class="admin-card mb-4" style="border-left: 4px solid #C59B27;">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="admin-step-num bg-warning bg-opacity-10 text-warning border-warning">
                    <i class="fas fa-info"></i>
                </div>
                <div>
                    <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Koreksi Soal & Taksonomi</h2>
                    <small class="text-secondary">Anda dapat memindahkan penugasan mode, formasi, kunci, atau ulasan pembahasan.</small>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 rounded-pill font-monospace">Mode: {{ $soal->examMode->name ?? ($selectedMode->name ?? 'Default') }}</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 rounded-pill font-monospace">Sub: {{ $soal->subCategory->name ?? 'Umum' }}</span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-bold">Kategori: {{ $soal->kategori }}</span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.soal.update', $soal->id) }}" method="POST" class="needs-validation" novalidate id="formEditSoal">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Bagian Kiri: Konfigurasi & Teks Soal --}}
            <div class="col-lg-7">
                {{-- Section 1: Taksonomi & Klasifikasi --}}
                <div class="admin-card">
                    <div class="admin-section-header">
                        <div class="admin-step-num">01</div>
                        <div>
                            <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Klasifikasi & Rumpun Materi</h2>
                            <small class="text-secondary">Atur target kategori dan formasi soal</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="admin-input-label">Mode Ujian</label>
                            <select name="exam_mode_id" id="editExamModeSelect" class="form-select admin-form-control">
                                <option value="">Mode default / fallback</option>
                                @foreach(($examModes ?? collect()) as $mode)
                                    <option value="{{ $mode->id }}" @selected((string) old('exam_mode_id', $soal->exam_mode_id) === (string) $mode->id)>{{ $mode->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="admin-input-label">Sub Kategori / Formasi</label>
                            <select name="sub_category_id" id="editSubCategorySelect" class="form-select admin-form-control">
                                <option value="">Semua / mode default</option>
                                @foreach(($subCategories ?? collect()) as $sub)
                                    <option value="{{ $sub->id }}" @selected((string) old('sub_category_id', $soal->sub_category_id) === (string) $sub->id)>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="admin-input-label">Kategori Soal</label>
                            <select name="kategori" id="editKategoriSelect" class="form-select admin-form-control" required>
                                @foreach(($categoryOptions ?? collect()) as $row)
                                    @php
                                        $value = strtoupper(trim((string) (is_array($row) ? ($row['value'] ?? '') : ($row->value ?? ''))));
                                        $label = (string) (is_array($row) ? ($row['label'] ?? $value) : ($row->label ?? $value));
                                    @endphp
                                    @if($value !== '')
                                        <option value="{{ $value }}" @selected(old('kategori', strtoupper((string) $soal->kategori)) === $value)>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="admin-input-label">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select admin-form-control">
                                <option value="">(kosong)</option>
                                <option value="easy" @selected(old('difficulty', $soal->difficulty) === 'easy')>Mudah (Easy)</option>
                                <option value="medium" @selected(old('difficulty', $soal->difficulty) === 'medium')>Sedang (Medium)</option>
                                <option value="hard" @selected(old('difficulty', $soal->difficulty) === 'hard')>HOTS / Sukar (Hard)</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="admin-input-label">Sub Topik / Kisi-kisi</label>
                            <input name="sub_topik" value="{{ old('sub_topik', $soal->sub_topik) }}" class="form-control admin-form-control" placeholder="Cth: Deret Angka / Wawasan Kebangsaan..." />
                        </div>
                        <div class="col-12">
                            <label class="admin-input-label">Standar Regulasi CASN</label>
                            <select name="standar_tahun" class="form-select admin-form-control">
                                <option value="2027" @selected(old('standar_tahun', $soal->standar_tahun ?? 'pra-2027') === '2027')>⭐ Standar CASN 2027 (Aktif Otomatis Masuk Sesi Ujian)</option>
                                <option value="pra-2027" @selected(old('standar_tahun', $soal->standar_tahun ?? 'pra-2027') === 'pra-2027')>Pra-2027 (Legacy / Arsip - Tidak Otomatis Digunakan)</option>
                            </select>
                            <small class="text-secondary mt-1 d-block">Ubah ke <strong>Standar CASN 2027</strong> untuk mengaktifkan soal ini agar otomatis digunakan pada paket ujian.</small>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Konten Pertanyaan --}}
                <div class="admin-card">
                    <div class="admin-section-header">
                        <div class="admin-step-num">02</div>
                        <div>
                            <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Teks Pertanyaan (Stem)</h2>
                            <small class="text-secondary">Ubah narasi pertanyaan secara lengkap</small>
                        </div>
                    </div>

                    <div>
                        <textarea name="teks_soal" rows="8" class="form-control admin-form-control" required style="min-height: 200px; font-size: 0.95rem; line-height: 1.6;">{{ old('teks_soal', $soal->teks_soal) }}</textarea>
                    </div>
                </div>

                {{-- Section 4: Pembahasan & Skema Bobot --}}
                <div class="admin-card">
                    <div class="admin-section-header">
                        <div class="admin-step-num">04</div>
                        <div>
                            <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Pembahasan & Kunci Analisis</h2>
                            <small class="text-secondary">Rasionalisasi konsep untuk bahan belajar peserta</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <textarea name="pembahasan" rows="6" class="form-control admin-form-control" placeholder="Penjelasan jawaban..." style="min-height: 150px; font-size: 0.92rem;">{{ old('pembahasan', $soal->pembahasan) }}</textarea>
                    </div>

                    <details class="rounded-3 p-3" style="background: rgba(197, 155, 39, 0.06); border: 1px solid rgba(197, 155, 39, 0.20);">
                        <summary class="fw-800" style="color: var(--text-primary); cursor: pointer; font-size: 0.88rem;">
                            <i class="fas fa-sliders me-1 text-warning"></i> Pengaturan Bobot Khusus (TKP 1–5 Poin)
                        </summary>
                        <div class="small text-secondary mt-2 mb-2">
                            Jika kategori TKP dan menggunakan pembobotan 1-5 poin, isi JSON: Contoh <code>{"A":5,"B":4,"C":3,"D":2,"E":1}</code>
                        </div>
                        <textarea name="bobot_tkp_json" rows="3" class="form-control admin-form-control" placeholder='{"A":5,"B":4,"C":3,"D":2,"E":1}'>{{ old('bobot_tkp_json', $soal->bobot_tkp ? json_encode($soal->bobot_tkp, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                    </details>
                </div>
            </div>

            {{-- Bagian Kanan: Opsi Pilihan A–E & Kunci Jawaban --}}
            <div class="col-lg-5">
                <div class="admin-card position-sticky" style="top: 85px;">
                    <div class="admin-section-header">
                        <div class="admin-step-num">03</div>
                        <div>
                            <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Pilihan Opsi Jawaban (A–E)</h2>
                            <small class="text-secondary">Pilihan distractor dan kunci resmi</small>
                        </div>
                    </div>

                    {{-- Kunci Jawaban Utama --}}
                    <div class="p-3 rounded-3 mb-3" style="background: rgba(19, 62, 135, 0.08); border: 1px solid rgba(19, 62, 135, 0.20);">
                        <label class="admin-input-label text-primary">Kunci Jawaban Benar</label>
                        <select name="jawaban_benar" class="form-select admin-form-control fw-bold fs-6">
                            <option value="" @selected(old('jawaban_benar', $soal->jawaban_benar) === null || old('jawaban_benar', $soal->jawaban_benar) === '')>-- Belum Ditentukan / TKP --</option>
                            @foreach(['A','B','C','D','E'] as $j)
                                <option value="{{ $j }}" @selected(old('jawaban_benar', strtoupper((string) $soal->jawaban_benar)) === $j)>Kunci Resmi: Opsi {{ $j }}</option>
                            @endforeach
                        </select>
                        <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                            *Untuk TKP, kunci jawaban dapat dikosongkan.
                        </div>
                    </div>

                    {{-- Form Opsi A s/d E --}}
                    <div class="d-flex flex-column gap-3">
                        @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $key => $letter)
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="option-badge">{{ $letter }}</div>
                                    <label class="admin-input-label mb-0">Pilihan {{ $letter }}</label>
                                </div>
                                <textarea name="opsi_{{ $key }}" rows="2" class="form-control admin-form-control" placeholder="Teks pilihan {{ $letter }}..." required style="min-height: 60px; font-size: 0.9rem;">{{ old('opsi_'.$key, $soal->{'opsi_'.$key}) }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tombol Submit Form --}}
                    <div class="d-grid gap-2 mt-4 pt-3 border-top" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                        <button type="submit" class="apple-glass-btn apple-btn-primary py-3 fs-6 fw-bold">
                            <i class="fas fa-check-double me-2"></i> Perbarui Butir Soal
                        </button>
                        <a href="{{ route('admin.soal-bank.index') }}" class="btn btn-sm btn-link text-secondary text-decoration-none text-center">
                            Batalkan Perubahan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    (function() {
        const examModeSelect = document.getElementById('editExamModeSelect');
        const subCategorySelect = document.getElementById('editSubCategorySelect');
        const kategoriSelect = document.getElementById('editKategoriSelect');
        const apiBase = @json(url('/api/sub-categories'));
        const examPackagesApiBase = @json(route('pref.exam-packages'));
        if (!examModeSelect || !subCategorySelect) return;

        examModeSelect.addEventListener('change', async function() {
            const examModeId = examModeSelect.value || '';
            const oldValue = subCategorySelect.value || '';
            subCategorySelect.innerHTML = '<option value="">Semua / mode default</option>';
            if (!examModeId) return;

            try {
                const res = await fetch(apiBase + '?exam_mode_id=' + encodeURIComponent(examModeId), { credentials: 'same-origin' });
                const json = await res.json();
                const rows = Array.isArray(json?.data) ? json.data : [];
                rows.forEach((row) => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name;
                    if (String(row.id) === String(oldValue)) opt.selected = true;
                    subCategorySelect.appendChild(opt);
                });
            } catch (e) {}

            if (!kategoriSelect) return;
            try {
                const selectedOption = examModeSelect.options[examModeSelect.selectedIndex];
                const examModeCode = selectedOption ? String(selectedOption.textContent || '').trim().toLowerCase() : '';
                const res = await fetch(examPackagesApiBase + '?exam_mode_id=' + encodeURIComponent(examModeId) + '&exam_mode_code=' + encodeURIComponent(examModeCode), { credentials: 'same-origin' });
                const packages = await res.json();
                const rows = Array.isArray(packages?.data) ? packages.data : [];
                const seen = new Map();
                rows.forEach((pkg) => {
                    const sections = Array.isArray(pkg?.sections) ? pkg.sections : [];
                    sections.forEach((section) => {
                        const value = String(section?.kategori || '').trim().toUpperCase();
                        if (!value || seen.has(value)) return;
                        seen.set(value, String(section?.label || value));
                    });
                });
                if (examModeCode === 'skd' && !seen.has('FIGURAL')) seen.set('FIGURAL', 'Figural');
                const current = String(kategoriSelect.value || '').trim().toUpperCase();
                kategoriSelect.innerHTML = '';
                seen.forEach((label, value) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = label;
                    if (value === current) opt.selected = true;
                    kategoriSelect.appendChild(opt);
                });
            } catch (e) {}
        });
    })();
</script>
@endpush
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Audit',
            'icon' => 'fa-shield-halved',
            'href' => route('admin.audit-soal.index'),
        ],
        [
            'label' => 'Simpan',
            'icon' => 'fa-floppy-disk',
            'style' => 'cta',
            'aria' => 'Simpan Perubahan Soal',
            'attrs' => ['onclick' => "document.querySelector('#formEditSoal')?.requestSubmit()"],
        ],
        [
            'label' => 'Bank',
            'icon' => 'fa-database',
            'href' => route('admin.soal-bank.index'),
        ],
    ],
])
@endsection
