@php
    $focusOptions = $this->focusTopicOptions();
    $stockRow = $this->selectedStockRow;
    $selectedPackage = $this->selectedPackage;
    $sections = (array) ($selectedPackage->sections ?? []);
    $rules = [];
    if (!empty($selectedPackage->score_rules['summary'] ?? null)) {
        $rules[] = $selectedPackage->score_rules['summary'];
    }
    if (!empty($selectedPackage->passing_rules['notes'] ?? null)) {
        $rules[] = $selectedPackage->passing_rules['notes'];
    }
    foreach ((array) ($selectedPackage->passing_rules['section_min'] ?? []) as $key => $min) {
        if ($min !== null && $min !== '') {
            $rules[] = $key . ' minimal ' . $min;
        }
    }
@endphp

<div>
    <div class="modal fade" id="modalMulai" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg apple-glass apple-glass-card" style="color: var(--text-primary); border-radius: 24px;">
                @if(session('error'))
                    <div class="alert border-0 apple-glass apple-glass-card p-3 m-3 mb-0 d-flex align-items-start gap-3" style="background: rgba(239,68,68,0.10); border-color: rgba(239,68,68,0.22);">
                        <i class="fas fa-triangle-exclamation text-danger mt-1"></i>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">Mulai ujian belum berhasil</div>
                            <div class="apple-hint" style="color: var(--text-primary) !important; opacity: 0.9;">{{ session('error') }}</div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert border-0 apple-glass apple-glass-card p-3 m-3 mb-0 d-flex align-items-start gap-3" style="background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.22);">
                        <i class="fas fa-circle-exclamation text-warning mt-1"></i>
                        <div>
                            <div class="fw-800 mb-1" style="color: var(--text-primary);">Form mulai ujian masih ada yang kurang</div>
                            <div class="apple-hint" style="color: var(--text-primary) !important; opacity: 0.9;">{{ $errors->first() }}</div>
                        </div>
                    </div>
                @endif

                <div class="modal-header border-0 p-4 pb-3">
                    <div>
                        <div class="d-inline-flex align-items-center gap-2 mb-1 px-2.5 py-1 rounded-pill" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.72rem; font-weight: 700;">
                            <i class="fas fa-certificate"></i> Standar CASN 2027 Resmi
                        </div>
                        <h4 class="modal-title fw-800 mb-1">Mulai Sesi Ujian</h4>
                        <div class="apple-hint">Menggunakan bank soal terstandar kisi-kisi resmi <strong>CASN 2027</strong> (HOTS TWK/TIU &amp; SJT TKP).</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('ujian.buat') }}" method="POST">
                    @csrf
                    <input type="hidden" name="exam_mode_id" value="{{ $examModeId }}">
                    <input type="hidden" name="sub_category_id" value="{{ $subCategoryId }}">
                    <input type="hidden" name="paket" value="{{ $paket }}">
                    <input type="hidden" name="allow_partial" value="0">

                    <div class="modal-body p-4 pt-0">
                        <section class="mb-4" id="ujian-step-mode">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <label class="small fw-bold text-muted mb-0">1. MODE UJIAN</label>
                                <span class="apple-pill muted">Wajib</span>
                            </div>
                            <div class="step-card-grid mb-2">
                                @foreach($this->examModes as $mode)
                                    <button type="button" wire:click="selectMode('{{ $mode->id }}')" wire:key="exam-mode-{{ $mode->id }}" class="step-select-card {{ (string) $examModeId === (string) $mode->id ? 'active' : '' }}">
                                        <div class="step-select-card__title">{{ $mode->name }}</div>
                                        <div class="step-select-card__hint">Pilih jalur ujian dulu supaya formasi, sesi, dan fokus topik ikut menyesuaikan.</div>
                                    </button>
                                @endforeach
                            </div>
                            <div class="step-flow-note">{{ $examModeId !== '' ? 'Mode aktif akan menjadi acuan seluruh langkah berikutnya.' : 'Pilih mode dulu untuk membuka langkah berikutnya.' }}</div>
                        </section>

                        <section class="mb-4" id="ujian-step-sub-category">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <label class="small fw-bold text-muted mb-0">2. FORMASI / SUB KATEGORI</label>
                                <span class="apple-pill {{ $this->requiresFormasi() ? 'warning' : 'muted' }}">{{ $this->requiresFormasi() ? 'Menyesuaikan mode' : 'Dilewati untuk SKD' }}</span>
                            </div>

                            @if($examModeId === '')
                                <div class="apple-glass-soft apple-glass-card p-3 apple-hint">Langkah ini akan muncul setelah mode ujian dipilih.</div>
                            @elseif(!$this->requiresFormasi())
                                <div class="apple-glass-soft apple-glass-card p-3">
                                    <div class="fw-700 mb-1">SKD tidak butuh formasi tambahan</div>
                                    <div class="apple-hint">Supaya alur lebih cepat, kamu bisa langsung lanjut memilih sesi latihan.</div>
                                </div>
                            @elseif($this->subCategories->isEmpty())
                                <div class="apple-glass-soft apple-glass-card p-3">
                                    <div class="fw-700 mb-1">Belum ada formasi untuk mode ini</div>
                                    <div class="apple-hint">Tambahkan dulu di Struktur Ujian agar sesi dan stok soal bisa lebih presisi.</div>
                                </div>
                            @else
                                <div class="step-card-grid step-card-grid-compact mb-2">
                                    @foreach($this->subCategories as $sub)
                                        <button type="button" wire:click="selectSubCategory('{{ $sub->id }}')" wire:key="sub-category-{{ $sub->id }}" class="step-select-card {{ (string) $subCategoryId === (string) $sub->id ? 'active' : '' }}">
                                            <div class="step-select-card__title">{{ $sub->name }}</div>
                                            <div class="step-select-card__hint">Pilih formasi agar kategori dan stok mengikuti rumpun yang benar.</div>
                                        </button>
                                    @endforeach
                                </div>
                                <div class="step-flow-note">{{ $subCategoryId !== '' ? 'Formasi aktif dipakai untuk menajamkan sesi dan topik.' : 'Untuk PPPK dan SKB, formasi perlu dipilih sebelum mulai.' }}</div>
                            @endif
                        </section>

                        <section class="mb-4" id="ujian-step-paket">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <label class="small fw-bold text-muted mb-0">3. SESI LATIHAN</label>
                                <span class="apple-pill muted">Utama</span>
                            </div>

                            @if($examModeId === '')
                                <div class="apple-glass-soft apple-glass-card p-3 apple-hint">Pilih mode dulu agar daftar sesi latihan tampil sesuai struktur ujian.</div>
                            @else
                                <div class="step-card-grid step-card-grid-compact mb-3">
                                    @foreach($this->packages as $pkg)
                                        @php
                                            $pkgCode = strtoupper((string) ($pkg->code ?? ''));
                                            $pkgStock = $this->sessionStockData->firstWhere('code', $pkgCode);
                                            $hints = [];
                                            if (!empty($pkg->total_questions)) $hints[] = $pkg->total_questions . ' soal';
                                            if (!empty($pkg->duration_minutes)) $hints[] = $pkg->duration_minutes . ' menit';
                                            if (!empty($pkgStock['ready'])) $hints[] = 'stok siap';
                                            elseif ($pkgStock) $hints[] = 'stok kurang';
                                            if (!empty($pkg->description)) $hints[] = $pkg->description;
                                        @endphp
                                        <button type="button" wire:click="selectPaket('{{ $pkgCode }}')" wire:key="paket-{{ $pkgCode }}" class="step-select-card {{ (string) $paket === $pkgCode ? 'active' : '' }}">
                                            <div class="step-select-card__title">{{ $pkg->name }}</div>
                                            <div class="step-select-card__hint">{{ implode('  ', $hints) }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="apple-glass-soft apple-glass-card p-3 {{ $paket === '' ? 'is-loading-preview' : '' }}" id="ujian-step-preview" wire:loading.class="is-loading-preview" wire:target="selectMode,selectSubCategory,selectPaket,examModeId,subCategoryId,paket">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div>
                                        <div class="small fw-800 opacity-75" style="color: var(--text-secondary); letter-spacing: .14em;">RINGKASAN SESI</div>
                                        <div class="fw-800" style="color: var(--text-primary);">{{ $selectedPackage->name ?? 'Pilih sesi latihan' }}</div>
                                    </div>
                                    <div class="apple-pill muted">{{ $selectedPackage?->duration_minutes ? $selectedPackage->duration_minutes . ' menit' : 'Belum dipilih' }}</div>
                                </div>

                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    @if(empty($sections))
                                        <span class="apple-pill muted">Pilih mode lalu sesi untuk melihat komposisi soal.</span>
                                    @else
                                        @foreach($sections as $section)
                                            <span class="apple-pill muted">{{ ($section['label'] ?? $section['kategori'] ?? 'Bagian') . (!empty($section['count']) ? '  ' . $section['count'] . ' soal' : '') }}</span>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="mt-3 small" style="color: var(--text-secondary);">
                                    {{ !empty($rules) ? implode('  ', $rules) : 'Aturan skor dan kelulusan akan mengikuti konfigurasi mode yang aktif.' }}
                                </div>

                                <div class="mt-3 small" style="color: var(--text-secondary);">
                                    @if($stockRow)
                                        @if(!empty($stockRow['ready']))
                                            <span class="stock-state stock-state--ready"><i class="fas fa-circle-check"></i>Siap: stok soal sesi ini sudah cukup.</span>
                                        @else
                                            <span class="stock-state stock-state--short"><i class="fas fa-triangle-exclamation"></i>Perlu perhatian: {{ implode('  ', $stockRow['missing'] ?? []) }}</span>
                                            <div class="apple-hint mt-2">Kategori kurang: {{ $stockRow['missing_count'] ?? 0 }}. Estimasi pengulangan: {{ $stockRow['repeat_estimate'] ?? 0 }} soal.</div>
                                        @endif
                                    @else
                                        Stok soal akan dihitung setelah sesi dipilih.
                                    @endif
                                </div>
                            </div>
                        </section>

                        <section class="mb-4" id="ujian-step-focus-topic">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <label class="small fw-bold text-muted mb-0">4. FOKUS TOPIK</label>
                                <span class="apple-pill muted">Opsional</span>
                            </div>

                            @if(!$this->focusTopicVisible())
                                <div class="apple-glass-soft apple-glass-card p-3 apple-hint">Topik fokus baru aktif kalau sesi yang dipilih memang hanya punya satu rumpun materi utama.</div>
                            @else
                                <label class="small fw-bold text-muted mb-2">{{ $this->focusTopicLabel() }}</label>
                                <select name="sub_topik" wire:model.live="subTopik" class="form-select form-select-lg apple-glass-input">
                                    <option value="">-- Acak Semua Topik --</option>
                                    @if(!empty($focusOptions['recommended']))
                                        <optgroup label="Rekomendasi Fokus">
                                            @foreach($focusOptions['recommended'] as $topic)
                                                <option value="{{ $topic }}">{{ $topic }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if(!empty($focusOptions['existing']))
                                        <optgroup label="Topik dari Bank Soal">
                                            @foreach($focusOptions['existing'] as $topic)
                                                <option value="{{ $topic }}">{{ $topic }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                <div class="form-text small opacity-75 mt-2" style="color: var(--text-secondary);">
                                    {{ !empty($focusOptions['recommended']) ? 'Topik rekomendasi bidang sudah disiapkan. Kamu juga bisa memakai topik dari bank soal yang sudah ada.' : 'Biarkan kosong jika ingin campuran soal yang lebih natural.' }}
                                </div>
                            @endif
                        </section>

                        <section class="mb-4" id="ujian-step-name">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <label class="small fw-bold text-muted mb-0">5. NAMA PESERTA</label>
                                <span class="apple-pill muted">Identitas</span>
                            </div>
                            <input type="text" name="nama" class="form-control form-control-lg apple-glass-input" value="{{ $nama }}" placeholder="Nama Lengkap" required>
                        </section>

                        <section class="d-grid gap-2" id="ujian-step-submit">
                            <button type="submit" class="apple-glass-btn apple-btn-primary w-100 py-3 {{ $this->showForceButton() ? 'd-none' : '' }} {{ $this->startButtonDisabled() ? 'opacity-50' : '' }}" {{ $this->startButtonDisabled() ? 'disabled' : '' }}>
                                @if($this->selectedStockBlocked())
                                    <i class="fas fa-lock me-2"></i>
                                @elseif($this->requiresFormasi() && $subCategoryId === '')
                                    <i class="fas fa-diagram-project me-2"></i>
                                @elseif($paket === '')
                                    <i class="fas fa-layer-group me-2"></i>
                                @endif
                                {{ $this->submitLabel() }}
                            </button>

                            @if($this->showForceButton())
                                <button type="submit" name="allow_partial" value="1" class="apple-glass-btn apple-btn-outline w-100 py-3">
                                    <i class="fas fa-forward me-2"></i>TETAP LANJUT
                                </button>
                            @endif
                        </section>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @once
        @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('ujian-modal-step-focus', ({ step }) => {
                    const map = {
                        'sub-category': 'ujian-step-sub-category',
                        'paket': 'ujian-step-paket',
                        'focus-topic': 'ujian-step-focus-topic',
                        'submit': 'ujian-step-submit'
                    };
                    const el = document.getElementById(map[step] || '');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
        </script>
        @endpush
    @endonce
</div>
