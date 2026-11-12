<div>
    <div class="rank-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
            <div>
                <div class="apple-kicker">Ranking</div>
                <h2 class="apple-title mb-2">Ranking Simulasi Ujian</h2>
                <div class="apple-subtitle">Sekarang ranking sudah siap mengikuti mode ujian baru. Filter mode, paket, dan pencarian peserta bergerak langsung tanpa refresh halaman.</div>
            </div>
            <div class="d-flex flex-wrap gap-3 w-100 w-lg-auto">
                <div class="rank-summary">
                    <div class="apple-label">Total Peserta</div>
                    <div class="h3 fw-800 mb-0">{{ $this->rankings->count() }}</div>
                </div>
                <div class="rank-summary">
                    <div class="apple-label">Mode Aktif</div>
                    <div class="h5 fw-800 mb-0">{{ $this->selectedMode?->name ?? 'Semua Mode' }}</div>
                </div>
            </div>
        </div>
        @if($this->modeStats->isNotEmpty())
            <div class="d-flex flex-wrap gap-3 mt-4 pt-2">
                @foreach($this->modeStats as $modeStat)
                    <div class="rank-mode-stat">
                        <div class="apple-label">{{ $modeStat->name }}</div>
                        <div class="d-flex align-items-end justify-content-between gap-3">
                            <div class="h4 fw-800 mb-0">{{ $modeStat->count }}</div>
                            <div class="apple-hint">peserta</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="apple-glass apple-glass-card p-3 p-md-4 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <input
                    wire:model.live.debounce.300ms="search"
                    class="form-control apple-glass-input"
                    placeholder="Cari nama peserta..."
                >
            </div>
            <div class="col-md-2">
                <select wire:model.live="paket" class="form-select apple-glass-input">
                    <option value="">Semua Paket</option>
                    @foreach(['A' => 'Paket A', 'B' => 'Paket B', 'C' => 'Paket C', 'D' => 'Paket D'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select wire:model.live="examModeId" class="form-select apple-glass-input">
                    <option value="">Semua Mode</option>
                    @foreach($this->examModes as $mode)
                        <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select wire:model.live="subCategoryId" class="form-select apple-glass-input">
                    <option value="">Semua Sub Kategori</option>
                    @foreach($this->subCategories as $subCategory)
                        <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 text-md-end">
                <button type="button" wire:click="resetFilters" class="apple-glass-btn apple-btn-outline d-inline-flex align-items-center justify-content-center">Reset</button>
            </div>
        </div>
    </div>

    <div class="rank-results-shell" wire:loading.class="rank-results-shell--loading" wire:target="search,paket,examModeId,subCategoryId">
        <div class="apple-skeleton-table mb-3" wire:loading wire:target="search,paket,examModeId,subCategoryId">
            <div class="apple-skeleton-table__row"></div>
            <div class="apple-skeleton-table__row"></div>
            <div class="apple-skeleton-table__row"></div>
        </div>

        <div wire:loading.remove wire:target="search,paket,examModeId,subCategoryId">
            <div class="table-responsive d-none d-lg-block apple-glass-table-wrap">
                <table class="table apple-glass-table mb-0">
                    <thead class="text-center small">
                        <tr>
                            <th>Rank</th>
                            <th class="text-start">Peserta</th>
                            <th>Mode</th>
                            <th>Paket</th>
                            <th>Tanggal & Jam</th>
                            <th>Ringkasan Skor</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($this->rankings as $key => $p)
                            @php
                                $scoreMeta = $this->scoreMeta($p);
                                $display = $this->displayMeta($p);
                            @endphp
                            <tr>
                                <td class="fw-800">{{ $key + 1 }}</td>
                                <td class="text-start">
                                    <div class="fw-700">{{ $p->nama_peserta }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="mode-badge-strong {{ $display['is_farmasi'] ? 'farmasi-mode-badge' : '' }}">{{ $display['mode_name'] }}</span>
                                        @if($display['sub_name'])
                                            <span class="sub-badge-soft {{ $display['is_farmasi'] ? 'farmasi-sub-badge' : '' }}">{{ $display['sub_name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="mode-badge-strong {{ $display['is_farmasi'] ? 'farmasi-mode-badge' : '' }}">{{ $display['mode_name'] }}</span></td>
                                <td><span class="apple-pill muted">Paket {{ $p->paket }}</span></td>
                                <td class="small apple-muted">{{ optional($p->updated_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="apple-chip-row justify-content-center">
                                        @foreach($scoreMeta['chips'] as $chip)
                                            <span class="apple-chip {{ $chip['state'] }}">
                                                <span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td><span class="apple-pill total">{{ $p->total_skor }}</span></td>
                                <td><span class="apple-pill {{ $scoreMeta['passed'] ? 'success' : 'danger' }}">{{ $scoreMeta['status'] }}</span></td>
                                <td>
                                    <a href="{{ route('ujian.hasil', $p->id) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-5 apple-muted">Belum ada data peserta yang cocok dengan filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-lg-none vstack gap-3">
                @forelse($this->rankings as $key => $p)
                    @php
                        $scoreMeta = $this->scoreMeta($p);
                        $display = $this->displayMeta($p);
                    @endphp
                    <div class="rank-card apple-glass apple-glass-card p-3 rank-card-item">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div style="min-width: 0;">
                                <div class="fw-800" style="font-size: 1.04rem;">{{ $p->nama_peserta }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="mode-badge-strong {{ $display['is_farmasi'] ? 'farmasi-mode-badge' : '' }}">{{ $display['mode_name'] }}</span>
                                    <span class="apple-pill muted">Paket {{ $p->paket }}</span>
                                    @if($display['sub_name'])
                                        <span class="sub-badge-soft {{ $display['is_farmasi'] ? 'farmasi-sub-badge' : '' }}">{{ $display['sub_name'] }}</span>
                                    @endif
                                </div>
                                <div class="apple-hint mt-2">{{ optional($p->updated_at)->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="text-end d-flex flex-column gap-2 align-items-end">
                                <span class="apple-pill total">#{{ $key + 1 }} • {{ $p->total_skor }}</span>
                                <span class="apple-pill {{ $scoreMeta['passed'] ? 'success' : 'danger' }}">{{ $scoreMeta['status'] }}</span>
                            </div>
                        </div>
                        <div class="mt-3 apple-chip-row">
                            @foreach($scoreMeta['chips'] as $chip)
                                <span class="apple-chip {{ $chip['state'] }}"><span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}</span>
                            @endforeach
                        </div>
                        <a href="{{ route('ujian.hasil', $p->id) }}" class="apple-glass-btn apple-btn-outline w-100 mt-3 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-eye opacity-75"></i>Detail
                        </a>
                    </div>
                @empty
                    <div class="py-5 text-center apple-muted">Belum ada data peserta yang cocok dengan filter ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
