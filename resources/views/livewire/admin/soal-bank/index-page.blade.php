@push('styles')
<style>
    .bank-soal-hero {
        background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.1) 0%, transparent 40%),
                    radial-gradient(circle at bottom left, rgba(34, 197, 94, 0.05) 0%, transparent 40%);
    }
    .stat-block {
        transition: all 0.3s ease;
    }
    .stat-block:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .apple-glass-input {
        background-color: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        color: var(--bs-body-color);
        transition: all 0.2s ease;
    }
    .apple-glass-input:focus {
        background-color: rgba(255,255,255,0.06);
        border-color: rgba(255,255,255,0.3);
        box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
    }
    .dark-mode .apple-glass-input {
        background-color: rgba(0,0,0,0.2);
        border-color: rgba(255,255,255,0.08);
    }
    .dark-mode .apple-glass-input:focus {
        background-color: rgba(0,0,0,0.4);
        border-color: rgba(255,255,255,0.2);
    }
    .bank-toolbar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 12px;
    }
    .bank-filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .bank-filter-chips .apple-glass-btn {
        min-height: 40px;
        padding: 10px 14px;
    }
    .bulk-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .bulk-details summary {
        list-style: none;
        cursor: pointer;
        user-select: none;
    }
    .bulk-details summary::-webkit-details-marker { display: none; }
    .bulk-details[open] summary {
        border-bottom: 1px solid rgba(255,255,255,0.10);
    }
    .table-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
</style>
@endpush

<div class="container py-4 py-md-5">

    <!-- Premium Hero Section -->
    <div class="apple-glass apple-glass-card mb-4 position-relative overflow-hidden bank-soal-hero">
        <div class="position-absolute top-0 end-0 p-5 opacity-10 d-none d-lg-block pointer-events-none">
            <i class="bi bi-database" style="font-size: 16rem;"></i>
        </div>
        
        <div class="p-4 p-md-5 position-relative z-1">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-2">
                <div>
                    <div class="apple-kicker">Admin &middot; Database</div>
                    <div class="display-5 apple-title mb-2">Manajemen Bank Soal</div>
                    <div class="apple-subtitle" style="max-width: 600px;">
                        Pusat kontrol utama untuk semua material ujian. Filter spesifik, lakukan update massal, dan kelola kualitas soal secara menyeluruh dalam satu dashboard.
                    </div>
                </div>
                <div class="bank-toolbar">
                    <a href="{{ route('admin.soal.create') }}" wire:navigate class="apple-nav-tile">
                        <span class="apple-nav-tile__icon"><i class="bi bi-plus-lg"></i></span>
                        <span class="apple-nav-tile__body">
                            <span class="apple-nav-tile__title">Tambah Soal</span>
                            <span class="apple-nav-tile__hint">Buat soal baru secara manual.</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.api-tokens.index') }}" wire:navigate class="apple-nav-tile">
                        <span class="apple-nav-tile__icon"><i class="bi bi-key"></i></span>
                        <span class="apple-nav-tile__body">
                            <span class="apple-nav-tile__title">Akses API</span>
                            <span class="apple-nav-tile__hint">Buka helper API dan token.</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats Strip -->
        <div class="row g-0 border-top border-white border-opacity-10">
            <div class="col-6 col-md-2 p-3 border-end border-white border-opacity-10 stat-block">
                <div class="apple-hint mb-1 uppercase-tracking" style="font-size: 0.75rem;">Total Soal</div>
                <div class="h3 fw-800 mb-0">{{ number_format((int) ($modeStats['total'] ?? 0)) }}</div>
            </div>
            <div class="col-6 col-md-2 p-3 border-end border-white border-opacity-10 stat-block">
                <div class="apple-hint mb-1 text-info uppercase-tracking" style="font-size: 0.75rem;">SKD</div>
                <div class="h3 fw-800 mb-0">{{ number_format((int) ($modeStats['skd'] ?? 0)) }}</div>
            </div>
            <div class="col-6 col-md-2 p-3 border-end border-white border-opacity-10 stat-block">
                <div class="apple-hint mb-1 text-success uppercase-tracking" style="font-size: 0.75rem;">SKB</div>
                <div class="h3 fw-800 mb-0">{{ number_format((int) ($modeStats['skb'] ?? 0)) }}</div>
            </div>
            <div class="col-6 col-md-2 p-3 border-end border-white border-opacity-10 stat-block">
                <div class="apple-hint mb-1 text-warning uppercase-tracking" style="font-size: 0.75rem;">PPPK</div>
                <div class="h3 fw-800 mb-0">{{ number_format((int) ($modeStats['pppk'] ?? 0)) }}</div>
            </div>
            <div class="col-6 col-md-2 p-3 border-end border-white border-opacity-10 stat-block">
                <div class="apple-hint mb-1 text-danger uppercase-tracking" style="font-size: 0.75rem;">Figural (Visual)</div>
                <div class="h3 fw-800 mb-0">{{ number_format((int) ($modeStats['figural'] ?? 0)) }}</div>
            </div>
            <div class="col-6 col-md-2 p-3 stat-block">
                <div class="apple-hint mb-1 text-secondary uppercase-tracking" style="font-size: 0.75rem;">Unlabeled</div>
                <div class="h3 fw-800 mb-0 {{ ($modeStats['unlabeled'] ?? 0) > 0 ? 'text-danger' : '' }}">{{ number_format((int) ($modeStats['unlabeled'] ?? 0)) }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert apple-alert apple-glass apple-glass-card apple-alert-inline mb-4" style="background: rgba(16,185,129,0.10); border-color: rgba(16,185,129,0.18) !important;">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    <div class="alert d-none mb-4" id="bankPageAlert"></div>

    <!-- Searching & Filtering Module -->
    <div class="apple-glass apple-glass-card p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-white border-opacity-10 pb-3">
            <h5 class="apple-title mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-funnel text-primary"></i> Filter & Pencarian
            </h5>
            <div class="bank-filter-chips">
                <button type="button" wire:click="resetFilters" class="apple-glass-btn apple-btn-outline text-decoration-none">Reset</button>
                <button type="button" wire:click="applyQuickFilter('farmasi')" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="bi bi-capsule me-1 opacity-75"></i>Farmasi
                </button>
                <button type="button" wire:click="applyQuickFilter('pppk_farmasi')" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="bi bi-person-hearts me-1 opacity-75"></i>PPPK Farmasi
                </button>
                <button type="button" wire:click="applyQuickFilter('skb_farmasi')" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="bi bi-clipboard2-pulse me-1 opacity-75"></i>SKB Farmasi
                </button>
            </div>
        </div>
        
        <div class="row g-3 align-items-end" wire:loading.class="opacity-75" wire:target="search,kategori,examModeId,subCategoryId,subTopik,difficulty">
            <div class="col-md-12 col-lg-4">
                <label class="apple-label mb-1">Pencarian Kata Kunci</label>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control apple-glass-input ps-5" placeholder="Cari ID, isi soal, atau pembahasan...">
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="apple-label mb-1">Kategori Dasar</label>
                <select wire:model.live="kategori" class="form-select apple-glass-input">
                    <option value="">Semua Kategori</option>
                    @foreach(($categoryOptions ?? collect()) as $row)
                        @php
                            $rawVal = is_array($row) ? ($row['value'] ?? '') : ($row->value ?? '');
                            $val = strtoupper(trim((string) $rawVal));
                            $rawLabel = is_array($row) ? ($row['label'] ?? '') : ($row->label ?? '');
                            $label = (string) (($rawLabel !== '' && $rawLabel !== null) ? $rawLabel : $val);
                        @endphp
                        @if($val !== '')
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="apple-label mb-1">Mode Ujian</label>
                <select wire:model.live="examModeId" id="bankExamModeSelect" class="form-select apple-glass-input">
                    <option value="">Semua Mode & Tipe Ujian</option>
                    @foreach($examModes as $mode)
                        <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="apple-label mb-1">Sub Kategori Profesi</label>
                <select wire:model.live="subCategoryId" id="bankSubCategorySelect" class="form-select apple-glass-input">
                    <option value="">Semua Sub Kategori</option>
                    @foreach($subCategories as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-5 col-lg-4">
                <label class="apple-label mb-1">Filter Sub Topik</label>
                <input type="text" wire:model.live.debounce.300ms="subTopik" class="form-control apple-glass-input" placeholder="Contoh: Integritas">
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="apple-label mb-1">Tingkat Kesulitan</label>
                <select wire:model.live="difficulty" class="form-select apple-glass-input">
                    <option value="">Semua Level</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="col-md-3 col-lg-6 d-flex justify-content-end">
                <div class="apple-glass-btn apple-btn-outline me-2 disabled" wire:loading wire:target="search,kategori,examModeId,subCategoryId,subTopik,difficulty">Memuat...</div>
                <button type="button" wire:click="resetFilters" class="apple-glass-btn apple-btn-primary px-4 shadow-sm">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filter
                </button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.soal.bulk-update') }}" id="bulkUpdateForm" onsubmit="return confirmBulkUpdate();">
        @csrf
        @method('PATCH')

        <!-- Mass Edit Module (Secondary UI, default minimized) -->
        <details class="apple-glass apple-glass-card mb-4 position-relative overflow-hidden bulk-details" id="bulkEditorDetails">
            <summary class="p-3 bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 text-primary-emphasis fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-magic"></i> Editor Massal (Bulk Actions)
                    </h6>
                </div>
                <div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-medium shadow-sm">
                        Terpilih: <span id="selectedSummaryCount">0</span> Soal
                    </span>
                </div>
            </summary>

            <div class="p-4">
                <div class="bulk-toolbar mb-4">
                    <button type="button" class="apple-glass-btn apple-btn-outline" id="bulkPresetPppkFarmasiBtn">
                        <i class="bi bi-magic me-1"></i>Isi Auto PPPK Farmasi
                    </button>
                    <button type="button" class="apple-glass-btn apple-btn-outline" id="bulkPresetSkbFarmasiBtn">
                        <i class="bi bi-magic me-1"></i>Isi Auto SKB Farmasi
                    </button>
                    <button type="button" class="apple-glass-btn apple-btn-outline" id="bulkFixKategoriByModeBtn" title="Untuk soal PPPK/SKB yang terlanjur berkategori TWK/TIU/TKP">
                        <i class="bi bi-shield-check me-1"></i>Fix Kategori (PPPK/SKB)
                    </button>
                    <button type="button" class="apple-glass-btn apple-btn-outline" id="bulkFixKategoriAiBtn" title="Auto-fix kategori via AI (async, aman dari timeout)" @if(in_array(strtolower((string) ($selectedMode?->code ?? '')), ['pppk', 'skb'], true)) style="display:inline-flex;" @else style="display:none;" @endif>
                        <i class="bi bi-stars me-1"></i>Auto Fix (AI)
                    </button>
                </div>

                <div class="apple-glass-soft apple-glass-card p-3 mb-3 d-none" id="bulkFixKategoriAiPanel">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="fw-700">Auto Fix Kategori (AI)</div>
                            <div class="apple-hint">AI hanya memilih dari kategori valid untuk mode ujian ini.</div>
                        </div>
                        <span class="badge rounded-pill bg-secondary" id="bulkFixKategoriAiStatus">Belum jalan</span>
                    </div>
                    <div class="apple-hint mt-2" id="bulkFixKategoriAiMeta"></div>
                    <div class="progress mt-2" style="height: 7px; border-radius: 999px; display:none;" id="bulkFixKategoriAiBarWrap">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" id="bulkFixKategoriAiBar"></div>
                    </div>
                    <div class="small apple-hint mt-2" id="bulkFixKategoriAiLog"></div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-4 col-lg-2">
                        <label class="apple-label mb-1">Mode Ujian Tujuan</label>
                        <select name="bulk_exam_mode_id" id="bulkExamModeSelect" class="form-select apple-glass-input">
                            <option value="">Abaikan</option>
                            <option value="0">Lepas & Kosongkan</option>
                            @foreach($examModes as $mode)
                                <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="apple-label mb-1">Sub Kategori Tujuan</label>
                        <select name="bulk_sub_category_id" id="bulkSubCategorySelect" class="form-select apple-glass-input">
                            <option value="">Abaikan</option>
                            <option value="0">Lepas & Kosongkan</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="apple-label mb-1">Ubah Kategori</label>
                        <select name="bulk_kategori" class="form-select apple-glass-input">
                            <option value="">Abaikan</option>
                            @foreach(($categoryOptions ?? collect()) as $row)
                                @php
                                    $rawVal = is_array($row) ? ($row['value'] ?? '') : ($row->value ?? '');
                                    $val = strtoupper(trim((string) $rawVal));
                                    $rawLabel = is_array($row) ? ($row['label'] ?? '') : ($row->label ?? '');
                                    $label = (string) (($rawLabel !== '' && $rawLabel !== null) ? $rawLabel : $val);
                                @endphp
                                @if($val !== '')
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="apple-label mb-1">Ubah Difficulty</label>
                        <select name="bulk_difficulty" class="form-select apple-glass-input">
                            <option value="">Abaikan</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    <div class="col-md-8 col-lg-4">
                        <label class="apple-label mb-1">Timpa Sub Topik</label>
                        <div class="input-group">
                            <input type="text" name="bulk_sub_topik" class="form-control apple-glass-input" placeholder="Tuliskan nama topik...">
                            <div class="input-group-text apple-glass-input bg-transparent d-flex align-items-center gap-2" title="Hapus seluruh Sub Topik dari soal terpilih">
                                <input class="form-check-input mt-0" type="checkbox" name="bulk_clear_sub_topik" value="1" id="bulkClearSubTopik">
                                <label class="form-check-label apple-hint mb-0" for="bulkClearSubTopik" style="cursor: pointer;">Clear</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-4 text-end border-top border-white border-opacity-10 pt-3">
                        <button type="submit" id="bulkUpdateBtn" class="apple-glass-btn apple-btn-primary px-4 shadow-sm" disabled>
                            <i class="bi bi-rocket-takeoff position-relative" style="top: -1px; margin-right: 6px;"></i> Eksekusi Update Massal
                        </button>
                    </div>
                </div>
            </div>
        </details>
    </form>

    <form method="POST" action="{{ route('admin.soal.bulk-destroy') }}" id="bulkDeleteForm" onsubmit="return confirmBulkDelete();">
        @csrf
        @method('DELETE')

        <!-- Data Table Section -->
        <div class="apple-glass apple-glass-card mb-4 position-relative overflow-hidden">
            
            <div class="p-3 border-bottom border-white border-opacity-10 bg-dark bg-opacity-10">
                <div class="table-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-table text-secondary"></i>
                    <h6 class="mb-0 fw-bold">Daftar Material Soal</h6>
                    <span class="apple-pill muted ms-2">Menampilkan {{ $soals->count() }} soal di halaman ini</span>
                </div>
                <div>
                    <button type="submit" id="bulkDeleteBtn" class="apple-glass-btn apple-btn-danger d-flex align-items-center gap-2" disabled>
                        <i class="bi bi-trash"></i> Hapus <span id="bulkDeleteCount" class="badge bg-white text-danger px-1">0</span>
                    </button>
                </div>
                </div>
            </div>

            <div class="table-responsive apple-glass-table-wrap m-0 border-0">
                <table class="table apple-glass-table table-hover align-middle mb-0" style="min-width: 900px;">
                    <thead class="table-dark" style="background: rgba(0,0,0,0.4);">
                        <tr>
                            <th class="text-center" style="width: 50px; padding: 1rem 0;">
                                <div class="form-check d-flex justify-content-center m-0">
                                    <input class="form-check-input" type="checkbox" id="selectAllSoals">
                                </div>
                            </th>
                            <th style="width: 80px;" class="fw-semibold uppercase-tracking text-secondary">ID</th>
                            <th class="fw-semibold uppercase-tracking text-secondary">Konten Soal</th>
                            <th style="width: 120px;" class="fw-semibold uppercase-tracking text-secondary">Kategori</th>
                            <th style="width: 250px;" class="fw-semibold uppercase-tracking text-secondary">Metadata (Mode & Sub)</th>
                            <th style="width: 100px;" class="fw-semibold uppercase-tracking text-secondary">Diff</th>
                            <th style="width: 100px;" class="fw-semibold uppercase-tracking text-secondary text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($soals as $soal)
                            <tr style="transition: background-color 0.2s;">
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center m-0">
                                        <input class="form-check-input bulk-soal-checkbox" type="checkbox" name="ids[]" value="{{ $soal->id }}">
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 text-body fw-normal font-monospace">#{{ $soal->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1 pe-3">
                                        <div class="text-truncate quick-edit-text" style="max-width: 450px; font-weight: 500;">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($soal->teks_soal), 90) }}
                                        </div>
                                        @if($soal->sub_topik)
                                            <div class="apple-hint quick-edit-subtopik" style="font-size: 0.75rem;"><i class="bi bi-tag-fill text-primary opacity-75"></i> {{ $soal->sub_topik }}</div>
                                        @else
                                            <div class="text-danger quick-edit-subtopik" style="font-size: 0.7rem;"><i class="bi bi-exclamation-triangle-fill"></i> Unlabeled</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $catColor = match($soal->kategori) {
                                            'TWK' => 'danger',
                                            'TIU' => 'primary',
                                            'TKP' => 'warning',
                                            'FIGURAL' => 'info',
                                            'TEKNIS' => 'success',
                                            'MANAJERIAL' => 'warning',
                                            'SOSIAL_KULTURAL' => 'info',
                                            'WAWANCARA' => 'secondary',
                                            'SKB_TEKNIS' => 'success',
                                            'SKB_REGULASI' => 'info',
                                            'SKB_STUDI_KASUS' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $catColor }} bg-opacity-25 text-{{ $catColor }} px-2 py-1 rounded-1 quick-edit-kategori">
                                        {{ $soal->kategori }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-1 quick-edit-meta">
                                        @if($soal->examMode)
                                            <span class="apple-pill py-0 px-2 quick-edit-mode" style="font-size: 0.70rem; border-color: {{ $soal->examMode->color ?? '#aaa' }}; color: {{ $soal->examMode->color ?? '#aaa' }}">
                                                {{ $soal->examMode->name }}
                                            </span>
                                        @else
                                            <span class="apple-pill py-0 px-2 text-muted quick-edit-mode" style="font-size: 0.70rem; border-color: rgba(255,255,255,0.1);">Mode Skd / Default</span>
                                        @endif
                                        
                                        @if($soal->subCategory)
                                            <span class="text-secondary quick-edit-subcategory" style="font-size: 0.8rem;"><i class="bi bi-arrow-return-right opacity-50"></i> {{ $soal->subCategory->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $diffColor = match(strtolower((string)$soal->difficulty)) {
                                            'easy' => 'success',
                                            'hard' => 'danger',
                                            default => 'info'
                                        };
                                    @endphp
                                    <div class="d-flex align-items-center gap-1 quick-edit-difficulty-wrap">
                                        <div style="width: 8px; height: 8px; border-radius: 50%; opacity: 0.8;" class="bg-{{ $diffColor }}"></div>
                                        <span class="text-capitalize fs-sm quick-edit-difficulty">{{ $soal->difficulty ?: 'Medium' }}</span>
                                    </div>
                                </td>
                                <td class="text-end pe-3 quick-edit-row">
                                    <button
                                        type="button"
                                        class="apple-glass-btn apple-btn-outline py-1 px-2 d-inline-flex align-items-center justify-content-center quick-edit-btn"
                                        data-bs-toggle="tooltip"
                                        title="Quick Edit"
                                        data-soal-id="{{ $soal->id }}"
                                        data-update-url="{{ route('admin.soal.update', $soal->id) }}"
                                        data-kategori="{{ $soal->kategori }}"
                                        data-exam-mode-id="{{ $soal->exam_mode_id }}"
                                        data-sub-category-id="{{ $soal->sub_category_id }}"
                                        data-difficulty="{{ $soal->difficulty }}"
                                        data-jawaban-benar="{{ strtoupper((string) ($soal->jawaban_benar ?? '')) }}"
                                        data-sub-topik="{{ e((string) ($soal->sub_topik ?? '')) }}"
                                        data-teks-soal="{{ e((string) $soal->teks_soal) }}"
                                    >
                                        <i class="bi bi-lightning-charge"></i>
                                    </button>
                                    <a href="{{ route('admin.soal.edit', $soal->id) }}" wire:navigate class="apple-glass-btn apple-btn-outline py-1 px-2 d-inline-flex align-items-center justify-content-center ms-1" data-bs-toggle="tooltip" title="Edit Lengkap">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted d-flex flex-column align-items-center justify-content-center">
                                        <i class="bi bi-inboxes mb-3 opacity-50" style="font-size: 3rem;"></i>
                                        <h5 class="fw-normal">Tidak ada soal yang ditemukan</h5>
                                        <p class="apple-hint">Gunakan pencarian lain atau <button type="button" wire:click="resetFilters" class="btn btn-link p-0 align-baseline text-decoration-none">reset filter</button>.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($soals->hasPages())
            <div class="p-3 border-top border-white border-opacity-10 bg-dark bg-opacity-10 d-flex justify-content-end">
                {{ $soals->links() }}
            </div>
            @endif
        </div>
    </form>

    <div class="modal fade" id="quickEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content apple-glass apple-glass-card border-0">
                <form method="POST" id="quickEditForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom border-white border-opacity-10">
                        <div>
                            <div class="fw-800" style="color: var(--text-primary);">Quick Edit Soal</div>
                            <div class="apple-hint" id="quickEditMeta">Koreksi cepat mode, kategori, jawaban, dan metadata tanpa pindah halaman.</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="quickEditAlert" class="alert d-none mb-3"></div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="apple-label mb-1">Mode Ujian</label>
                                <select name="exam_mode_id" id="quickEditExamMode" class="form-select apple-glass-input">
                                    <option value="">Mode default / fallback</option>
                                    @foreach($examModes as $mode)
                                        <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="apple-label mb-1">Sub Kategori</label>
                                <select name="sub_category_id" id="quickEditSubCategory" class="form-select apple-glass-input">
                                    <option value="">Semua / mode default</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="apple-label mb-1">Kategori</label>
                                <select name="kategori" id="quickEditKategori" class="form-select apple-glass-input" required>
                                    <option value="">Pilih kategori</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="apple-label mb-1">Jawaban</label>
                                <select name="jawaban_benar" id="quickEditJawaban" class="form-select apple-glass-input">
                                    <option value="">(kosong)</option>
                                    @foreach(['A','B','C','D','E'] as $j)
                                        <option value="{{ $j }}">{{ $j }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="apple-label mb-1">Difficulty</label>
                                <select name="difficulty" id="quickEditDifficulty" class="form-select apple-glass-input">
                                    <option value="">(kosong)</option>
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="apple-label mb-1">Sub Topik</label>
                                <input type="text" name="sub_topik" id="quickEditSubTopik" class="form-control apple-glass-input" maxlength="120">
                            </div>
                            <div class="col-12">
                                <label class="apple-label mb-1">Preview Soal</label>
                                <textarea id="quickEditPreview" class="form-control apple-glass-input" rows="6" style="min-height: 180px;" readonly></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-white border-opacity-10">
                        <button type="button" class="apple-glass-btn apple-btn-outline" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="apple-glass-btn apple-btn-primary" id="quickEditSubmitBtn">Simpan Quick Edit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const apiBase = @json(url('/api/sub-categories'));
        const examPackagesApiBase = @json(route('pref.exam-packages'));

        function getCheckboxes() {
            return Array.from(document.querySelectorAll('.bulk-soal-checkbox'));
        }

        function initTooltips() {
            if (!window.bootstrap?.Tooltip) return;

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((node) => {
                window.bootstrap.Tooltip.getOrCreateInstance(node);
            });
        }

        function syncBulkState() {
            const checkboxes = getCheckboxes();
            const checkedCount = checkboxes.filter((item) => item.checked).length;
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const bulkDeleteCount = document.getElementById('bulkDeleteCount');
            const selectedSummaryCount = document.getElementById('selectedSummaryCount');
            const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
            const selectAll = document.getElementById('selectAllSoals');

            if (bulkDeleteCount) bulkDeleteCount.textContent = String(checkedCount);
            if (selectedSummaryCount) selectedSummaryCount.textContent = String(checkedCount);
            if (bulkDeleteBtn) bulkDeleteBtn.disabled = checkedCount === 0;
            if (bulkUpdateBtn) bulkUpdateBtn.disabled = checkedCount === 0;
            if (selectAll) {
                selectAll.checked = checkedCount > 0 && checkedCount === checkboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        }

        function syncIdsForForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;

            form.querySelectorAll('input[name="ids[]"].bulk-hidden-id').forEach((node) => node.remove());

            getCheckboxes()
                .filter((item) => item.checked)
                .forEach((item) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = item.value;
                    hidden.className = 'bulk-hidden-id';
                    form.appendChild(hidden);
                });

            return true;
        }

        async function reloadSubCategories(targetSelect, examModeId, keepZero) {
            if (!targetSelect) return;

            const oldValue = targetSelect.value || '';
            targetSelect.innerHTML = keepZero
                ? '<option value="">Abaikan</option><option value="0">Lepas & Kosongkan</option>'
                : '<option value="">Semua Sub Kategori</option>';

            if (!examModeId || examModeId === '0') return;

            try {
                const res = await fetch(apiBase + '?exam_mode_id=' + encodeURIComponent(examModeId), { credentials: 'same-origin' });
                const json = await res.json();
                const rows = Array.isArray(json?.data) ? json.data : [];

                rows.forEach((row) => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name;
                    if (String(row.id) === String(oldValue)) opt.selected = true;
                    targetSelect.appendChild(opt);
                });
            } catch (e) {}
        }

        async function reloadQuickEditCategories(targetSelect, examModeId, currentValue) {
            if (!targetSelect) return;
            targetSelect.innerHTML = '<option value="">Pilih kategori</option>';
            if (!examModeId) {
                ['TWK', 'TIU', 'TKP', 'FIGURAL'].forEach((value) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = value;
                    if (String(currentValue || '').toUpperCase() === value) opt.selected = true;
                    targetSelect.appendChild(opt);
                });
                return;
            }

            const modeSelect = document.getElementById('quickEditExamMode');
            const selectedOption = modeSelect?.options?.[modeSelect.selectedIndex];
            const examModeCode = modeCodeFromText(selectedOption?.textContent || '');

            try {
                const res = await fetch(examPackagesApiBase + '?exam_mode_id=' + encodeURIComponent(examModeId) + '&exam_mode_code=' + encodeURIComponent(examModeCode), { credentials: 'same-origin' });
                const json = await res.json();
                const rows = Array.isArray(json?.data) ? json.data : [];
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
                seen.forEach((label, value) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = label;
                    if (String(currentValue || '').toUpperCase() === value) opt.selected = true;
                    targetSelect.appendChild(opt);
                });
            } catch (e) {}
        }

        async function applyBulkPreset(type) {
            const bulkExamModeSelect = document.getElementById('bulkExamModeSelect');
            const bulkSubCategorySelect = document.getElementById('bulkSubCategorySelect');
            if (!bulkExamModeSelect || !bulkSubCategorySelect) return;

            const targetMode = type === 'skb_farmasi' ? 'skb' : 'pppk';
            const modeOption = Array.from(bulkExamModeSelect.options).find((opt) => String(opt.textContent || '').toLowerCase() === targetMode);
            if (modeOption) {
                bulkExamModeSelect.value = modeOption.value;
                await reloadSubCategories(bulkSubCategorySelect, modeOption.value, true);
            }

            const subMatch = Array.from(bulkSubCategorySelect.options).find((opt) => /farmasi|apoteker/i.test(String(opt.textContent || '')));
            if (subMatch) {
                bulkSubCategorySelect.value = subMatch.value;
            }

            const diffSelect = document.querySelector('select[name="bulk_difficulty"]');
            const subTopikInput = document.querySelector('input[name="bulk_sub_topik"]');
            if (diffSelect) diffSelect.value = 'medium';
            if (subTopikInput) {
                subTopikInput.value = type === 'skb_farmasi' ? 'Regulasi Kefarmasian' : 'Farmakologi Dasar';
            }
        }

        function modeCodeFromText(text) {
            const value = String(text || '').toLowerCase();
            if (value.includes('pppk')) return 'pppk';
            if (value.includes('skb')) return 'skb';
            if (value.includes('skd')) return 'skd';

            return '';
        }

        function categoryColor(category) {
            switch (String(category || '').toUpperCase()) {
                case 'TWK': return 'danger';
                case 'TIU': return 'primary';
                case 'TKP': return 'warning';
                case 'FIGURAL': return 'info';
                case 'TEKNIS': return 'success';
                case 'MANAJERIAL': return 'warning';
                case 'SOSIAL_KULTURAL': return 'info';
                case 'WAWANCARA': return 'secondary';
                case 'SKB_TEKNIS': return 'success';
                case 'SKB_REGULASI': return 'info';
                case 'SKB_STUDI_KASUS': return 'warning';
                default: return 'secondary';
            }
        }

        function difficultyColor(level) {
            switch (String(level || '').toLowerCase()) {
                case 'easy': return 'success';
                case 'hard': return 'danger';
                default: return 'info';
            }
        }

        function setQuickEditAlert(kind, message) {
            const el = document.getElementById('quickEditAlert');
            if (!el) return;
            if (!message) {
                el.className = 'alert d-none mb-3';
                el.textContent = '';
                return;
            }
            el.className = 'alert mb-3 ' + (kind === 'error'
                ? 'alert-danger'
                : (kind === 'success' ? 'alert-success' : 'alert-primary'));
            el.textContent = message;
        }

        function setBankAlert(kind, message) {
            const el = document.getElementById('bankPageAlert');
            if (!el) return;
            if (!message) {
                el.className = 'alert d-none mb-4';
                el.textContent = '';
                return;
            }
            el.className = 'alert mb-4 ' + (kind === 'error' ? 'alert-danger' : 'alert-success');
            el.textContent = message;
        }

        function applyQuickEditRowUpdate(form, payload) {
            const id = Number(payload?.id || 0);
            if (!id) return;
            const button = document.querySelector(`.quick-edit-btn[data-soal-id="${id}"]`);
            const row = button?.closest('tr');
            if (!row) return;

            const textEl = row.querySelector('.quick-edit-text');
            const subTopikEl = row.querySelector('.quick-edit-subtopik');
            const kategoriEl = row.querySelector('.quick-edit-kategori');
            const modeEl = row.querySelector('.quick-edit-mode');
            const subCategoryEl = row.querySelector('.quick-edit-subcategory');
            const diffWrap = row.querySelector('.quick-edit-difficulty-wrap');
            const diffText = row.querySelector('.quick-edit-difficulty');

            const text = String(payload.teks_soal || '').replace(/\s+/g, ' ').trim();
            if (textEl) textEl.textContent = text.length > 90 ? (text.slice(0, 90) + '...') : text;

            const subTopik = String(payload.sub_topik || '').trim();
            if (subTopikEl) {
                if (subTopik) {
                    subTopikEl.className = 'apple-hint quick-edit-subtopik';
                    subTopikEl.style.fontSize = '0.75rem';
                    subTopikEl.innerHTML = '<i class="bi bi-tag-fill text-primary opacity-75"></i> ' + subTopik;
                } else {
                    subTopikEl.className = 'text-danger quick-edit-subtopik';
                    subTopikEl.style.fontSize = '0.7rem';
                    subTopikEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Unlabeled';
                }
            }

            const kategori = String(payload.kategori || '').toUpperCase();
            if (kategoriEl) {
                const color = categoryColor(kategori);
                kategoriEl.className = `badge bg-${color} bg-opacity-25 text-${color} px-2 py-1 rounded-1 quick-edit-kategori`;
                kategoriEl.textContent = kategori;
            }

            if (modeEl) {
                const modeName = String(payload.exam_mode_name || '').trim();
                const modeColor = String(payload.exam_mode_color || '#aaa').trim() || '#aaa';
                if (modeName) {
                    modeEl.textContent = modeName;
                    modeEl.className = 'apple-pill py-0 px-2 quick-edit-mode';
                    modeEl.style.fontSize = '0.70rem';
                    modeEl.style.borderColor = modeColor;
                    modeEl.style.color = modeColor;
                } else {
                    modeEl.textContent = 'Mode Skd / Default';
                    modeEl.className = 'apple-pill py-0 px-2 text-muted quick-edit-mode';
                    modeEl.style.fontSize = '0.70rem';
                    modeEl.style.borderColor = 'rgba(255,255,255,0.1)';
                    modeEl.style.color = '';
                }
            }

            const subName = String(payload.sub_category_name || '').trim();
            if (subName) {
                if (subCategoryEl) {
                    subCategoryEl.innerHTML = '<i class="bi bi-arrow-return-right opacity-50"></i> ' + subName;
                } else if (modeEl?.parentElement) {
                    const node = document.createElement('span');
                    node.className = 'text-secondary quick-edit-subcategory';
                    node.style.fontSize = '0.8rem';
                    node.innerHTML = '<i class="bi bi-arrow-return-right opacity-50"></i> ' + subName;
                    modeEl.parentElement.appendChild(node);
                }
            } else if (subCategoryEl) {
                subCategoryEl.remove();
            }

            const diff = String(payload.difficulty || '').trim() || 'medium';
            if (diffWrap) {
                const dot = diffWrap.querySelector('div');
                if (dot) dot.className = 'bg-' + difficultyColor(diff);
            }
            if (diffText) diffText.textContent = diff || 'Medium';

            if (button) {
                button.dataset.kategori = kategori;
                button.dataset.examModeId = payload.exam_mode_id ?? '';
                button.dataset.subCategoryId = payload.sub_category_id ?? '';
                button.dataset.difficulty = payload.difficulty ?? '';
                button.dataset.jawabanBenar = payload.jawaban_benar ?? '';
                button.dataset.subTopik = payload.sub_topik ?? '';
                button.dataset.teksSoal = payload.teks_soal ?? '';
            }
        }

        async function pollFixKategoriAi(jobId) {
            if (!jobId) return;
            const bulkFixKategoriAiPanel = document.getElementById('bulkFixKategoriAiPanel');
            const bulkFixKategoriAiStatus = document.getElementById('bulkFixKategoriAiStatus');
            const bulkFixKategoriAiMeta = document.getElementById('bulkFixKategoriAiMeta');
            const bulkFixKategoriAiBarWrap = document.getElementById('bulkFixKategoriAiBarWrap');
            const bulkFixKategoriAiBar = document.getElementById('bulkFixKategoriAiBar');
            const bulkFixKategoriAiLog = document.getElementById('bulkFixKategoriAiLog');

            if (bulkFixKategoriAiPanel) bulkFixKategoriAiPanel.classList.remove('d-none');
            const statusUrl = @json(route('admin.soal-bank.fix-kategori-ai.status')) + '?id=' + encodeURIComponent(jobId);
            const stepUrl = @json(route('admin.soal-bank.fix-kategori-ai.step'));
            for (let i = 0; i < 360; i++) {
                try {
                    const res = await fetch(statusUrl, { credentials: 'same-origin' });
                    const data = await res.json();
                    const st = String(data.status || 'unknown');
                    const prog = data.progress || {};
                    const done = Number(prog.done || 0);
                    const total = Number(prog.total || 0);
                    const changed = Number(prog.changed || 0);
                    const errors = Number(prog.errors || 0);

                    if (bulkFixKategoriAiStatus) {
                        bulkFixKategoriAiStatus.textContent = st === 'processing' ? 'Memproses' : (st === 'success' ? 'Selesai' : (st === 'error' ? 'Gagal' : st));
                        bulkFixKategoriAiStatus.className = 'badge rounded-pill ' + (st === 'success' ? 'bg-success' : (st === 'error' ? 'bg-danger' : 'bg-primary'));
                    }
                    if (bulkFixKategoriAiMeta) {
                        bulkFixKategoriAiMeta.textContent = total
                            ? `Progress: ${done}/${total} | berubah: ${changed} | error: ${errors}`
                            : String(data.message || '');
                    }
                    if (bulkFixKategoriAiBarWrap && bulkFixKategoriAiBar) {
                        const pct = total ? Math.min(100, Math.round((done / total) * 100)) : 0;
                        bulkFixKategoriAiBarWrap.style.display = total ? '' : 'none';
                        bulkFixKategoriAiBar.style.width = pct + '%';
                    }
                    if (bulkFixKategoriAiLog) {
                        const lines = Array.isArray(data.log) ? data.log : [];
                        bulkFixKategoriAiLog.textContent = lines.slice(-6).join(' | ');
                    }

                    if (st === 'success' || st === 'error') return;

                    if (String(data.runner || '') === 'step') {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        await fetch(stepUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify({ job_id: String(jobId) }),
                            credentials: 'same-origin',
                        });
                    }
                } catch (e) {}
                await new Promise(r => setTimeout(r, 2000));
            }
        }

        function initBankSoalPage() {
            if (!document.getElementById('bankExamModeSelect')) return;
            initTooltips();
            syncBulkState();
        }

        if (!window.__bankSoalBindingsInitialized) {
            window.__bankSoalBindingsInitialized = true;

            document.addEventListener('mouseover', function (event) {
                const trigger = event.target.closest('[data-bs-toggle="tooltip"]');
                if (trigger && window.bootstrap?.Tooltip) {
                    window.bootstrap.Tooltip.getOrCreateInstance(trigger);
                }
            });

            document.addEventListener('change', async function (event) {
                const target = event.target;

                if (target?.id === 'selectAllSoals') {
                    getCheckboxes().forEach((item) => {
                        item.checked = target.checked;
                    });
                    syncBulkState();
                    return;
                }

                if (target?.classList?.contains('bulk-soal-checkbox')) {
                    syncBulkState();
                    return;
                }

                if (target?.id === 'bulkExamModeSelect') {
                    await reloadSubCategories(document.getElementById('bulkSubCategorySelect'), target.value || '', true);
                    return;
                }

                if (target?.id === 'quickEditExamMode') {
                    const subSelect = document.getElementById('quickEditSubCategory');
                    const catSelect = document.getElementById('quickEditKategori');
                    await reloadSubCategories(subSelect, target.value || '', false);
                    await reloadQuickEditCategories(catSelect, target.value || '', catSelect?.dataset.currentValue || '');
                }
            });

            document.addEventListener('click', async function (event) {
                const presetPppk = event.target.closest('#bulkPresetPppkFarmasiBtn');
                if (presetPppk) {
                    await applyBulkPreset('pppk_farmasi');
                    return;
                }

                const presetSkb = event.target.closest('#bulkPresetSkbFarmasiBtn');
                if (presetSkb) {
                    await applyBulkPreset('skb_farmasi');
                    return;
                }

                const bulkFixByMode = event.target.closest('#bulkFixKategoriByModeBtn');
                if (bulkFixByMode) {
                    const catSelect = document.querySelector('select[name="bulk_kategori"]');
                    const examModeSelect = document.getElementById('bankExamModeSelect');
                    const modeText = String(examModeSelect?.selectedOptions?.[0]?.textContent || '').toLowerCase();
                    if (!catSelect) return;

                    if (modeText.includes('pppk')) {
                        catSelect.value = 'TEKNIS';
                    } else if (modeText.includes('skb')) {
                        catSelect.value = 'SKB_TEKNIS';
                    } else {
                        alert('Set dulu filter Mode Ujian ke PPPK atau SKB, lalu pilih soal yang mau dibetulkan.');
                    }

                    return;
                }

                const bulkFixAi = event.target.closest('#bulkFixKategoriAiBtn');
                if (bulkFixAi) {
                    const examModeSelect = document.getElementById('bankExamModeSelect');
                    const bulkFixKategoriAiPanel = document.getElementById('bulkFixKategoriAiPanel');
                    const bulkFixKategoriAiStatus = document.getElementById('bulkFixKategoriAiStatus');
                    const modeId = String(examModeSelect?.value || '').trim();

                    if (!modeId) {
                        alert('Pilih dulu filter Mode Ujian (PPPK atau SKB).');
                        return;
                    }

                    const modeCode = modeCodeFromText(examModeSelect?.selectedOptions?.[0]?.textContent || '');
                    if (!['pppk', 'skb'].includes(modeCode)) {
                        alert('Auto Fix (AI) hanya dipakai untuk filter mode PPPK atau SKB.');
                        return;
                    }

                    const ok = confirm('Jalankan Auto Fix Kategori (AI) untuk soal pada mode ini yang masih berkategori TWK/TIU/TKP? Proses berjalan di background.');
                    if (!ok) return;

                    try {
                        bulkFixAi.disabled = true;
                        if (bulkFixKategoriAiPanel) bulkFixKategoriAiPanel.classList.remove('d-none');
                        if (bulkFixKategoriAiStatus) {
                            bulkFixKategoriAiStatus.textContent = 'Mengirim';
                            bulkFixKategoriAiStatus.className = 'badge rounded-pill bg-primary';
                        }

                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(@json(route('admin.soal-bank.fix-kategori-ai')), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify({ exam_mode_id: Number(modeId), provider: 'groq', limit: 400, runner: 'step' }),
                            credentials: 'same-origin',
                        });
                        const data = await res.json();
                        if (!data.ok || !data.job_id) throw new Error(data.error || 'Gagal start job');
                        pollFixKategoriAi(String(data.job_id));
                    } catch (e) {
                        alert('Gagal: ' + String(e && e.message ? e.message : e));
                    } finally {
                        bulkFixAi.disabled = false;
                    }
                    return;
                }

                const quickEditBtn = event.target.closest('.quick-edit-btn');
                if (quickEditBtn) {
                    const modalEl = document.getElementById('quickEditModal');
                    const form = document.getElementById('quickEditForm');
                    const examModeSelect = document.getElementById('quickEditExamMode');
                    const subCategorySelect = document.getElementById('quickEditSubCategory');
                    const kategoriSelect = document.getElementById('quickEditKategori');
                    const jawabanSelect = document.getElementById('quickEditJawaban');
                    const difficultySelect = document.getElementById('quickEditDifficulty');
                    const subTopikInput = document.getElementById('quickEditSubTopik');
                    const preview = document.getElementById('quickEditPreview');
                    const meta = document.getElementById('quickEditMeta');
                    if (!modalEl || !form || !examModeSelect || !subCategorySelect || !kategoriSelect) return;

                    const dataset = quickEditBtn.dataset || {};
                    form.action = dataset.updateUrl || '';
                    examModeSelect.value = dataset.examModeId || '';
                    jawabanSelect.value = dataset.jawabanBenar || '';
                    difficultySelect.value = dataset.difficulty || '';
                    subTopikInput.value = dataset.subTopik || '';
                    if (preview) preview.value = dataset.teksSoal || '';
                    if (meta) meta.textContent = `Soal #${dataset.soalId || '-'} • Pindah mode/kategori/jawaban cepat tanpa masuk halaman edit penuh.`;

                    kategoriSelect.dataset.currentValue = dataset.kategori || '';
                    await reloadSubCategories(subCategorySelect, examModeSelect.value || '', false);
                    subCategorySelect.value = dataset.subCategoryId || '';
                    await reloadQuickEditCategories(kategoriSelect, examModeSelect.value || '', dataset.kategori || '');

                    const modal = window.bootstrap?.Modal ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
                    modal?.show();
                }
            });

            document.addEventListener('submit', function (event) {
                if (event.target?.id === 'bulkUpdateForm') {
                    event.preventDefault();
                    syncIdsForForm('bulkUpdateForm');
                    const form = event.target;
                    const submitBtn = document.getElementById('bulkUpdateBtn');
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const run = async () => {
                        try {
                            setBankAlert('', '');
                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Menyimpan...';
                            }
                            const fd = new FormData(form);
                            const res = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json',
                                },
                                body: fd,
                                credentials: 'same-origin',
                            });
                            const data = await res.json();
                            if (!res.ok || !data?.ok) {
                                throw new Error(data?.message || 'Gagal update massal.');
                            }
                            setBankAlert('success', data.message || 'Update massal berhasil.');
                            setTimeout(() => window.location.reload(), 700);
                        } catch (e) {
                            setBankAlert('error', String(e?.message || e));
                        } finally {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="bi bi-rocket-takeoff position-relative" style="top: -1px; margin-right: 6px;"></i> Eksekusi Update Massal';
                            }
                        }
                    };
                    run();
                    return;
                }

                if (event.target?.id === 'bulkDeleteForm') {
                    syncIdsForForm('bulkDeleteForm');
                    return;
                }

                if (event.target?.id === 'quickEditForm') {
                    event.preventDefault();
                    const form = event.target;
                    const submitBtn = document.getElementById('quickEditSubmitBtn');
                    const modalEl = document.getElementById('quickEditModal');
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    const run = async () => {
                        try {
                            setQuickEditAlert('', '');
                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.textContent = 'Menyimpan...';
                            }

                            const fd = new FormData(form);
                            const res = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json',
                                },
                                body: fd,
                                credentials: 'same-origin',
                            });

                            const data = await res.json();
                            if (!res.ok || !data?.ok) {
                                const firstError = data?.message
                                    || (data?.errors ? Object.values(data.errors).flat().join(' ') : 'Gagal menyimpan quick edit.');
                                throw new Error(firstError);
                            }

                            applyQuickEditRowUpdate(form, data.soal || {});
                            setQuickEditAlert('success', data.message || 'Soal berhasil disimpan.');
                            setTimeout(() => {
                                const modal = window.bootstrap?.Modal ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
                                modal?.hide();
                                setQuickEditAlert('', '');
                            }, 700);
                        } catch (e) {
                            setQuickEditAlert('error', String(e?.message || e || 'Gagal menyimpan quick edit.'));
                        } finally {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Simpan Quick Edit';
                            }
                        }
                    };

                    run();
                }
            });

            document.addEventListener('livewire:navigated', initBankSoalPage);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBankSoalPage, { once: true });
        } else {
            initBankSoalPage();
        }
    })();

    function confirmBulkDelete() {
        const checked = document.querySelectorAll('.bulk-soal-checkbox:checked').length;
        if (!checked) return false;
        return confirm('PERINGATAN: Hapus ' + checked + ' soal secara permanen? Tindakan ini tidak bisa dibatalkan.');
    }

    function confirmBulkUpdate() {
        const checked = document.querySelectorAll('.bulk-soal-checkbox:checked').length;
        if (!checked) return false;
        return confirm('Anda yakin ingin meng-update ' + checked + ' soal dengan metadata baru ini?');
    }
</script>
@endpush
