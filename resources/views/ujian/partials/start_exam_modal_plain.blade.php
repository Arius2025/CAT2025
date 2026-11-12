@php
    $startModeRows = collect($examModes ?? collect())->values()->map(fn ($mode) => [
        'id' => (string) ($mode->id ?? ''),
        'name' => (string) ($mode->name ?? ''),
        'code' => strtolower((string) ($mode->code ?? '')),
    ])->all();
    $startDefaultSubRows = collect($defaultSubCategories ?? collect())->values()->map(fn ($sub) => [
        'id' => (string) ($sub->id ?? ''),
        'name' => (string) ($sub->name ?? ''),
    ])->all();
    $startDefaultPackageRows = collect($defaultModePackages ?? collect())->values()->map(fn ($pkg) => [
        'code' => strtoupper((string) ($pkg->code ?? '')),
        'name' => (string) ($pkg->name ?? ('Paket ' . strtoupper((string) ($pkg->code ?? '')))),
    ])->all();
    $startOldModeId = (string) old('exam_mode_id', (string) ($defaultExamMode->id ?? ''));
    $startOldSubCategoryId = (string) old('sub_category_id', '');
    $startOldPaket = strtoupper((string) old('paket', ''));
    $startOldNama = (string) old('nama', auth()->user()->name ?? '');
    $startErrorText = (string) session('error', '');
    $canForceContinue = str_contains(strtolower($startErrorText), 'soal berulang')
        || str_contains(strtolower($startErrorText), 'tetap lanjut');
@endphp

<style>
    .start-exam-modal-dialog {
        max-width: 680px;
        margin: 1rem auto;
    }
    .start-exam-modal {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 2rem);
        border-radius: 28px !important;
        border: 1px solid var(--border-glass) !important;
        background:
            radial-gradient(circle at top left, rgba(197, 155, 39, 0.12), transparent 34%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent 30%),
            linear-gradient(180deg, #0f172a 0%, #0b2545 100%);
    }
    [data-bs-theme="light"] .start-exam-modal {
        border-color: rgba(197, 155, 39, 0.28) !important;
        background:
            radial-gradient(circle at top left, rgba(197, 155, 39, 0.10), transparent 34%),
            radial-gradient(circle at top right, rgba(19, 62, 135, 0.08), transparent 30%),
            linear-gradient(180deg, #ffffff 0%, #f8f9fc 100%);
    }
    .start-exam-hero {
        flex: 0 0 auto;
        padding: 22px 24px 12px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .start-exam-modal .modal-header {
        flex: 0 0 auto;
    }
    .start-exam-modal form {
        min-height: 0;
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
    }
    .start-exam-modal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 18px !important;
    }
    .start-exam-modal .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    .start-exam-modal .modal-body::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.38);
        border-radius: 999px;
    }
    .start-exam-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        color: var(--text-secondary);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .start-exam-grid {
        display: grid;
        gap: 16px;
    }
    .start-exam-step {
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        padding: 18px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    [data-bs-theme="light"] .start-exam-step {
        background: rgba(15, 23, 42, 0.03);
        border-color: rgba(15, 23, 42, 0.08);
    }
    .start-exam-step__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .start-exam-step__label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.8rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-secondary);
    }
    .start-exam-step__index {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-grid;
        place-items: center;
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.22), rgba(59, 130, 246, 0.18));
        color: var(--text-primary);
        font-size: 0.78rem;
    }
    .start-exam-step__hint {
        margin-top: 8px;
        color: var(--text-secondary);
        font-size: 0.88rem;
        line-height: 1.55;
    }
    .start-exam-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.05);
        color: var(--text-secondary);
        font-size: 0.76rem;
        font-weight: 800;
    }
    .start-exam-pill.start-exam-pill--warn {
        border-color: rgba(245, 158, 11, 0.24);
        background: rgba(245, 158, 11, 0.12);
        color: var(--text-primary);
    }
    .start-exam-pill.start-exam-pill--ok {
        border-color: rgba(16, 185, 129, 0.24);
        background: rgba(16, 185, 129, 0.12);
        color: var(--text-primary);
    }
    .start-exam-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }
    .start-exam-summary__item {
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        padding: 12px 14px;
    }
    [data-bs-theme="light"] .start-exam-summary__item {
        background: rgba(15, 23, 42, 0.03);
        border-color: rgba(15, 23, 42, 0.08);
    }
    .start-exam-summary__label {
        color: var(--text-secondary);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .start-exam-summary__value {
        margin-top: 6px;
        color: var(--text-primary);
        font-weight: 800;
        line-height: 1.35;
    }
    .start-exam-submit {
        margin-top: 4px;
        padding-top: 8px;
        background: linear-gradient(180deg, rgba(15,23,42,0), rgba(15,23,42,0.35) 55%, rgba(15,23,42,0.55));
        backdrop-filter: blur(6px);
        border-radius: 18px;
    }
    [data-bs-theme="light"] .start-exam-submit {
        background: linear-gradient(180deg, rgba(255,255,255,0), rgba(248,250,252,0.78) 55%, rgba(248,250,252,0.92));
    }
    @media (max-width: 575.98px) {
        .start-exam-modal-dialog {
            margin: 0.5rem;
        }
        .start-exam-modal {
            max-height: calc(100vh - 1rem);
            border-radius: 22px !important;
        }
        .start-exam-modal .modal-body {
            padding-bottom: 12px !important;
        }
        .start-exam-hero {
            padding: 18px 18px 10px;
        }
        .start-exam-step {
            padding: 16px;
            border-radius: 18px;
        }
        .start-exam-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="modal fade" id="modalMulai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered start-exam-modal-dialog">
        <div class="modal-content border-0 shadow-lg apple-glass apple-glass-card start-exam-modal" style="color: var(--text-primary);">
            @if(session('error'))
                <div class="alert border-0 apple-glass apple-glass-card p-3 m-3 mb-0 d-flex align-items-start gap-3" style="background: rgba(239,68,68,0.10); border-color: rgba(239,68,68,0.22);">
                    <i class="fas fa-triangle-exclamation text-danger mt-1"></i>
                    <div>
                        <div class="fw-800 mb-1" style="color: var(--text-primary);">Mulai ujian belum berhasil</div>
                        <div class="apple-hint" style="color: var(--text-primary) !important; opacity: 0.9;">{{ session('error') }}</div>
                    </div>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert border-0 apple-glass apple-glass-card p-3 m-3 mb-0 d-flex align-items-start gap-3" style="background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.22);">
                    <i class="fas fa-circle-exclamation text-warning mt-1"></i>
                    <div>
                        <div class="fw-800 mb-1" style="color: var(--text-primary);">Form mulai ujian masih ada yang kurang</div>
                        <div class="apple-hint" style="color: var(--text-primary) !important; opacity: 0.9;">{{ $errors->first() }}</div>
                    </div>
                </div>
            @endif

            <div class="start-exam-hero">
                <div class="start-exam-kicker mb-3">
                    <i class="fas fa-play-circle opacity-75"></i>
                    Siapkan Simulasi &bull; Standar CASN 2027
                </div>
            </div>

            <div class="modal-header border-0 px-4 pt-0 pb-3">
                <div>
                    <h4 class="modal-title fw-800 mb-1">Mulai Sesi Ujian</h4>
                    <div class="apple-hint">Simulasi berstandar resmi <strong>CASN 2027 (KemenPAN-RB &amp; BKN)</strong> dengan prioritas soal aktif terverifikasi 2027.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('ujian.buat') }}" method="POST" id="plainStartExamForm">
                @csrf
                <input type="hidden" name="allow_partial" value="0">

                <div class="modal-body p-4 pt-0">
                    <div class="start-exam-summary">
                        <div class="start-exam-summary__item">
                            <div class="start-exam-summary__label">Mode Default</div>
                            <div class="start-exam-summary__value">{{ $defaultExamMode->name ?? 'Belum ada' }}</div>
                        </div>
                        <div class="start-exam-summary__item">
                            <div class="start-exam-summary__label">Sesi Tersedia</div>
                            <div class="start-exam-summary__value">{{ count($startDefaultPackageRows) > 0 ? count($startDefaultPackageRows) . ' paket' : 'Akan menyesuaikan mode' }}</div>
                        </div>
                        <div class="start-exam-summary__item">
                            <div class="start-exam-summary__label">Standar Soal</div>
                            <div class="start-exam-summary__value text-success"><i class="fas fa-certificate me-1"></i> Standar CASN 2027</div>
                        </div>
                    </div>

                    <div class="start-exam-grid mt-4">
                    <section class="start-exam-step">
                        <div class="start-exam-step__head">
                            <label class="start-exam-step__label mb-0">
                                <span class="start-exam-step__index">1</span>
                                Mode Ujian
                            </label>
                            <span class="start-exam-pill start-exam-pill--ok">Wajib</span>
                        </div>
                        <select name="exam_mode_id" id="plainExamModeSelect" class="form-select form-select-lg apple-glass-input" required>
                            <option value="">-- Pilih mode ujian --</option>
                            @foreach($startModeRows as $mode)
                                <option value="{{ $mode['id'] }}" data-code="{{ $mode['code'] }}" {{ $startOldModeId === $mode['id'] ? 'selected' : '' }}>
                                    {{ $mode['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="start-exam-step__hint">Pilih jalur ujian dulu agar formasi dan sesi latihan menyesuaikan struktur yang benar.</div>
                    </section>

                    <section class="start-exam-step" id="plainSubCategoryWrap">
                        <div class="start-exam-step__head">
                            <label class="start-exam-step__label mb-0">
                                <span class="start-exam-step__index">2</span>
                                Formasi / Sub Kategori
                            </label>
                            <span class="start-exam-pill start-exam-pill--warn" id="plainSubCategoryBadge">Menyesuaikan</span>
                        </div>
                        <select name="sub_category_id" id="plainSubCategorySelect" class="form-select form-select-lg apple-glass-input">
                            <option value="">-- Pilih formasi --</option>
                        </select>
                        <div class="start-exam-step__hint" id="plainSubCategoryHint">Untuk SKB dan PPPK, formasi wajib dipilih.</div>
                    </section>

                    <section class="start-exam-step">
                        <div class="start-exam-step__head">
                            <label class="start-exam-step__label mb-0">
                                <span class="start-exam-step__index">3</span>
                                Sesi Latihan
                            </label>
                            <span class="start-exam-pill">Pilih Paket</span>
                        </div>
                        <select name="paket" id="plainPaketSelect" class="form-select form-select-lg apple-glass-input" required>
                            <option value="">-- Pilih sesi latihan --</option>
                        </select>
                        <div class="start-exam-step__hint" id="plainPaketHint">Daftar sesi akan menyesuaikan mode ujian yang dipilih.</div>
                    </section>

                    <section class="start-exam-step">
                        <div class="start-exam-step__head">
                            <label class="start-exam-step__label mb-0">
                                <span class="start-exam-step__index">4</span>
                                Nama Peserta & Opsi
                            </label>
                            <span class="start-exam-pill">Identitas</span>
                        </div>
                        <input type="text" name="nama" class="form-control form-control-lg apple-glass-input mb-3" value="{{ $startOldNama }}" placeholder="Nama Lengkap" required>
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="isUjiCoba" name="is_uji_coba" value="1">
                            <label class="form-check-label small" for="isUjiCoba" style="color: var(--text-secondary);">
                                <strong>Mode Test/Lomba Bareng</strong> (Gunakan stok soal yang sama persis namun diacak nomernya untuk dikerjakan bersamaan).
                            </label>
                        </div>
                    </section>

                    <section class="d-grid start-exam-submit">
                        @if($canForceContinue)
                            <button type="submit" name="allow_partial" value="1" class="apple-glass-btn apple-btn-primary w-100 py-3">
                                <i class="fas fa-forward me-2"></i>TETAP LANJUT
                            </button>
                            <button type="button" class="apple-glass-btn apple-btn-outline w-100 py-3 mt-2" data-bs-dismiss="modal">
                                <i class="fas fa-xmark me-2"></i>BATAL
                            </button>
                            <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-outline w-100 py-3 mt-2 text-decoration-none text-center">
                                <i class="fas fa-wand-magic-sparkles me-2"></i>GENERATE SOAL
                            </a>
                        @else
                            <button type="submit" class="apple-glass-btn apple-btn-primary w-100 py-3" id="plainStartExamSubmit">
                                <i class="fas fa-play me-2"></i>MULAI SEKARANG
                            </button>
                        @endif
                    </section>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('plainStartExamForm');
        if (!form) return;

        const modeSelect = document.getElementById('plainExamModeSelect');
        const subCategoryWrap = document.getElementById('plainSubCategoryWrap');
        const subCategorySelect = document.getElementById('plainSubCategorySelect');
        const subCategoryBadge = document.getElementById('plainSubCategoryBadge');
        const subCategoryHint = document.getElementById('plainSubCategoryHint');
        const paketSelect = document.getElementById('plainPaketSelect');
        const paketHint = document.getElementById('plainPaketHint');
        const submitBtn = document.getElementById('plainStartExamSubmit');

        const subCategoryApiBase = @json(route('pref.sub-categories'));
        const examPackagesApiBase = @json(route('pref.exam-packages'));
        const defaultModeId = @json((string) ($defaultExamMode->id ?? ''));
        const oldModeId = @json($startOldModeId);
        const oldSubCategoryId = @json($startOldSubCategoryId);
        const oldPaket = @json($startOldPaket);
        const initialSubCategories = @json($startDefaultSubRows);
        const initialPackages = @json($startDefaultPackageRows);

        function selectedModeCode() {
            return String(modeSelect?.selectedOptions?.[0]?.dataset?.code || '').toLowerCase();
        }

        function requiresFormasi() {
            return ['skb', 'pppk'].includes(selectedModeCode());
        }

        function fillSelect(selectEl, rows, placeholder, valueKey, labelKey, wanted = '') {
            if (!selectEl) return;
            const target = String(wanted || '').trim();
            selectEl.innerHTML = '';
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            selectEl.appendChild(placeholderOption);

            (Array.isArray(rows) ? rows : []).forEach((row) => {
                const opt = document.createElement('option');
                opt.value = String(row?.[valueKey] ?? '');
                opt.textContent = String(row?.[labelKey] ?? opt.value);
                if (target !== '' && opt.value === target) {
                    opt.selected = true;
                }
                selectEl.appendChild(opt);
            });
        }

        function syncFormState() {
            const needFormasi = requiresFormasi();
            const hasMode = String(modeSelect?.value || '').trim() !== '';
            const hasSubCategory = String(subCategorySelect?.value || '').trim() !== '';
            const hasPaket = String(paketSelect?.value || '').trim() !== '';

            if (subCategoryWrap) {
                subCategoryWrap.style.display = needFormasi ? '' : 'none';
            }
            if (subCategorySelect) {
                subCategorySelect.required = needFormasi;
            }
            if (subCategoryBadge) {
                subCategoryBadge.textContent = needFormasi ? 'Wajib untuk mode ini' : 'Dilewati untuk SKD';
                subCategoryBadge.className = 'start-exam-pill ' + (needFormasi ? 'start-exam-pill--warn' : 'start-exam-pill--ok');
            }
            if (subCategoryHint) {
                subCategoryHint.textContent = needFormasi
                    ? 'Untuk SKB dan PPPK, formasi wajib dipilih.'
                    : 'Untuk SKD, langkah formasi dilewati supaya lebih cepat.';
            }
            if (paketHint) {
                paketHint.textContent = hasMode
                    ? 'Pilih sesi yang ingin dikerjakan.'
                    : 'Pilih mode dulu agar daftar sesi latihan tampil.';
            }
            if (submitBtn) {
                const canSubmit = hasMode && (!needFormasi || hasSubCategory) && hasPaket;
                submitBtn.disabled = !canSubmit;
                submitBtn.classList.toggle('opacity-50', !canSubmit);
            }
        }

        async function loadDependencies(keepSelection = true) {
            const modeId = String(modeSelect?.value || '').trim();
            const modeCode = selectedModeCode();
            const wantedSubCategory = keepSelection ? String(subCategorySelect?.dataset?.wantedValue || subCategorySelect?.value || '') : '';
            const wantedPaket = keepSelection ? String(paketSelect?.dataset?.wantedValue || paketSelect?.value || '') : '';

            if (!modeId) {
                fillSelect(subCategorySelect, [], '-- Pilih formasi --', 'id', 'name');
                fillSelect(paketSelect, [], '-- Pilih sesi latihan --', 'code', 'name');
                syncFormState();
                return;
            }

            try {
                const [subResponse, paketResponse] = await Promise.all([
                    fetch(subCategoryApiBase + '?kind=formasi&exam_mode_id=' + encodeURIComponent(modeId) + '&exam_mode_code=' + encodeURIComponent(modeCode), { credentials: 'same-origin' }),
                    fetch(examPackagesApiBase + '?exam_mode_id=' + encodeURIComponent(modeId) + '&exam_mode_code=' + encodeURIComponent(modeCode), { credentials: 'same-origin' }),
                ]);

                const subJson = await subResponse.json();
                const paketJson = await paketResponse.json();
                const subRows = Array.isArray(subJson?.data) ? subJson.data : [];
                const paketRows = Array.isArray(paketJson?.data) ? paketJson.data.map((row) => ({
                    code: String(row?.code || '').toUpperCase(),
                    name: String(row?.name || ('Paket ' + String(row?.code || '').toUpperCase())),
                })) : [];

                fillSelect(subCategorySelect, subRows, '-- Pilih formasi --', 'id', 'name', wantedSubCategory);
                fillSelect(paketSelect, paketRows, '-- Pilih sesi latihan --', 'code', 'name', wantedPaket);
            } catch (e) {
                fillSelect(subCategorySelect, [], '-- Gagal memuat formasi --', 'id', 'name');
                fillSelect(paketSelect, [], '-- Gagal memuat sesi --', 'code', 'name');
            }

            syncFormState();
        }

        subCategorySelect.dataset.wantedValue = oldSubCategoryId;
        paketSelect.dataset.wantedValue = oldPaket;

        fillSelect(subCategorySelect, initialSubCategories, '-- Pilih formasi --', 'id', 'name', oldSubCategoryId);
        fillSelect(paketSelect, initialPackages, '-- Pilih sesi latihan --', 'code', 'name', oldPaket);

        if (String(modeSelect?.value || '') === '' && defaultModeId !== '') {
            modeSelect.value = defaultModeId;
        }

        modeSelect?.addEventListener('change', () => {
            subCategorySelect.dataset.wantedValue = '';
            paketSelect.dataset.wantedValue = '';
            loadDependencies(false);
        });

        subCategorySelect?.addEventListener('change', syncFormState);
        paketSelect?.addEventListener('change', syncFormState);

        if (String(modeSelect?.value || '') === defaultModeId && defaultModeId !== '') {
            syncFormState();
        } else {
            loadDependencies(true);
        }

        form.addEventListener('submit', function () {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyiapkan Lembar Ujian...';
            }
        });

        syncFormState();
    })();
</script>
@endpush
