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
                    <i class="fas fa-plus-circle"></i> Manajemen Bank Soal
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Input Soal Baru
                </h1>
                <div class="apple-subtitle" style="max-width: 650px;">
                    Tambahkan butir soal terstandar BKN dengan klasifikasi mode ujian, formasi teknis, dan skema penskoran resmi.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.soal-bank.index') }}" class="apple-glass-btn apple-btn-outline px-3 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Bank Soal
                </a>
                <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-gold px-3 py-2">
                    <i class="fas fa-wand-magic-sparkles me-1"></i> Generate AI
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
            <div class="fw-800 mb-1 text-danger"><i class="fas fa-triangle-exclamation me-1"></i> Mohon lengkapi data berikut:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)
                    <li class="small">{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.soal.store') }}" method="POST" class="needs-validation" novalidate id="formCreateSoal">
        @csrf

        <div class="row g-4">
            {{-- Bagian Kiri: Konfigurasi & Teks Soal --}}
            <div class="col-lg-7">
                {{-- Section 1: Taksonomi & Klasifikasi --}}
                <div class="admin-card">
                    <div class="admin-section-header">
                        <div class="admin-step-num">01</div>
                        <div>
                            <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Klasifikasi & Taksonomi</h2>
                            <small class="text-secondary">Pilih rumpun materi dan target formasi</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="admin-input-label">Mode Ujian</label>
                            <select name="exam_mode_id" id="createExamModeSelect" class="form-select admin-form-control">
                                <option value="">Pilih Mode (Default / SKD)</option>
                                @foreach(($examModes ?? collect()) as $mode)
                                    <option value="{{ $mode->id }}" @selected((string) old('exam_mode_id', $defaultMode?->id) === (string) $mode->id)>{{ $mode->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="admin-input-label">Sub Kategori / Formasi</label>
                            <select name="sub_category_id" id="createSubCategorySelect" class="form-select admin-form-control">
                                <option value="">Semua / mode default</option>
                                @foreach(($subCategories ?? collect()) as $sub)
                                    <option value="{{ $sub->id }}" @selected((string) old('sub_category_id') === (string) $sub->id)>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="admin-input-label">Kategori Soal</label>
                            <select name="kategori" class="form-select admin-form-control" required>
                                @foreach(['TWK','TIU','TKP','FIGURAL'] as $k)
                                    <option value="{{ $k }}" @selected(old('kategori', 'TWK') === $k)>{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="admin-input-label">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select admin-form-control">
                                <option value="">Standar</option>
                                <option value="easy" @selected(old('difficulty') === 'easy')>Mudah (Easy)</option>
                                <option value="medium" @selected(old('difficulty', 'medium') === 'medium')>Sedang (Medium)</option>
                                <option value="hard" @selected(old('difficulty') === 'hard')>HOTS / Sukar (Hard)</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="admin-input-label">Sub Topik / Kisi-kisi</label>
                            <input name="sub_topik" value="{{ old('sub_topik') }}" class="form-control admin-form-control" placeholder="Cth: Nasionalisme, Deret, Integritas" />
                        </div>
                        <div class="col-12">
                            <label class="admin-input-label">Standar Regulasi CASN</label>
                            <select name="standar_tahun" class="form-select admin-form-control">
                                <option value="2027" @selected(old('standar_tahun', '2027') === '2027')>⭐ Standar CASN 2027 (Aktif Otomatis Masuk Sesi Ujian)</option>
                                <option value="pra-2027" @selected(old('standar_tahun') === 'pra-2027')>Pra-2027 (Legacy / Arsip - Tidak Otomatis Digunakan)</option>
                            </select>
                            <small class="text-secondary mt-1 d-block">Sistem ujian hanya menggunakan soal berstandar 2027. Soal bertanda pra-2027 diarsipkan.</small>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Konten Pertanyaan --}}
                <div class="admin-card">
                    <div class="admin-section-header">
                        <div class="admin-step-num">02</div>
                        <div>
                            <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Teks Pertanyaan (Stem)</h2>
                            <small class="text-secondary">Tuliskan narasi soal secara lugas dan komprehensif</small>
                        </div>
                    </div>

                    <div>
                        <textarea name="teks_soal" rows="7" class="form-control admin-form-control" placeholder="Tuliskan stimulus atau teks pertanyaan di sini..." required style="min-height: 180px; font-size: 0.95rem; line-height: 1.6;">{{ old('teks_soal') }}</textarea>
                    </div>
                </div>

                {{-- Section 4: Pembahasan & Skema Bobot --}}
                <div class="admin-card">
                    <div class="admin-section-header">
                        <div class="admin-step-num">04</div>
                        <div>
                            <h2 class="h6 fw-800 mb-0" style="color: var(--text-primary);">Pembahasan & Rasionalisasi</h2>
                            <small class="text-secondary">Ulasan ilmiah untuk umpan balik belajar peserta</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <textarea name="pembahasan" rows="5" class="form-control admin-form-control" placeholder="Tuliskan ulasan konsep, dasar hukum, atau cara cepat penyelesaian di sini..." style="min-height: 130px; font-size: 0.92rem;">{{ old('pembahasan') }}</textarea>
                    </div>

                    <details class="rounded-3 p-3" style="background: rgba(197, 155, 39, 0.06); border: 1px solid rgba(197, 155, 39, 0.20);">
                        <summary class="fw-800" style="color: var(--text-primary); cursor: pointer; font-size: 0.88rem;">
                            <i class="fas fa-sliders me-1 text-warning"></i> Pengaturan Bobot Khusus (TKP 1–5 Poin)
                        </summary>
                        <div class="small text-secondary mt-2 mb-2">
                            Isi format JSON jika kategori soal adalah TKP dengan rentang poin 1–5. Contoh:
                            <code class="d-inline-block px-2 py-1 rounded" style="background: rgba(0,0,0,0.1);">{"A":5,"B":4,"C":3,"D":2,"E":1}</code>
                        </div>
                        <textarea name="bobot_tkp_json" rows="3" class="form-control admin-form-control" placeholder='{"A":5,"B":4,"C":3,"D":2,"E":1}'>{{ old('bobot_tkp_json') }}</textarea>
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
                            <small class="text-secondary">Tentukan opsi pengecoh dan kunci benar</small>
                        </div>
                    </div>

                    {{-- Kunci Jawaban Utama --}}
                    <div class="p-3 rounded-3 mb-3" style="background: rgba(19, 62, 135, 0.08); border: 1px solid rgba(19, 62, 135, 0.20);">
                        <label class="admin-input-label text-primary">Kunci Jawaban Benar</label>
                        <select name="jawaban_benar" class="form-select admin-form-control fw-bold fs-6">
                            <option value="" @selected(old('jawaban_benar') === null || old('jawaban_benar') === '')>-- Belum Ditentukan / TKP --</option>
                            @foreach(['A','B','C','D','E'] as $j)
                                <option value="{{ $j }}" @selected(old('jawaban_benar') === $j)>Kunci Resmi: Opsi {{ $j }}</option>
                            @endforeach
                        </select>
                        <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                            *Khusus kategori TKP, opsi kunci dapat dikosongkan dan digantikan dengan bobot poin.
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
                                <textarea name="opsi_{{ $key }}" rows="2" class="form-control admin-form-control" placeholder="Teks pilihan {{ $letter }}..." required style="min-height: 60px; font-size: 0.9rem;">{{ old('opsi_'.$key) }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tombol Submit Form --}}
                    <div class="d-grid gap-2 mt-4 pt-3 border-top" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                        <button type="submit" class="apple-glass-btn apple-btn-primary py-3 fs-6 fw-bold">
                            <i class="fas fa-floppy-disk me-2"></i> Simpan Butir Soal
                        </button>
                        <a href="{{ route('admin.soal-bank.index') }}" class="btn btn-sm btn-link text-secondary text-decoration-none text-center">
                            Batalkan & Kembali
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
        const examModeSelect = document.getElementById('createExamModeSelect');
        const subCategorySelect = document.getElementById('createSubCategorySelect');
        const apiBase = @json(url('/api/sub-categories'));
        if (!examModeSelect || !subCategorySelect) return;

        examModeSelect.addEventListener('change', async function() {
            const examModeId = examModeSelect.value || '';
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
                    if (String(row.id) === String(@json(old('sub_category_id')))) opt.selected = true;
                    subCategorySelect.appendChild(opt);
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
            'label' => 'Bank',
            'icon' => 'fa-database',
            'href' => route('admin.soal-bank.index'),
        ],
        [
            'label' => 'Simpan',
            'icon' => 'fa-floppy-disk',
            'style' => 'cta',
            'aria' => 'Simpan Soal',
            'form' => null,
            'attrs' => ['onclick' => "document.querySelector('#formCreateSoal')?.requestSubmit()"],
        ],
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
    ],
])
@endsection
