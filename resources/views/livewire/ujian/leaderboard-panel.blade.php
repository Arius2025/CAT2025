<div>
    <div class="leader-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
            <div>
                <div class="apple-kicker">Klasemen</div>
                <h1 class="apple-title mb-2">Papan Peringkat Tryout</h1>
                <div class="apple-subtitle">Klasemen ini sekarang sudah selaras dengan mode ujian baru. Filter paket, mode, sub kategori, dan pencarian nama berjalan langsung di komponen.</div>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <div class="apple-pill total">{{ $this->rankings->count() }} peserta</div>
                <div class="apple-pill muted">{{ $this->selectedMode?->name ?? 'Semua Mode' }}</div>
            </div>
        </div>
        @if($this->modeStats->isNotEmpty())
            <div class="d-flex flex-wrap gap-3 mt-4 pt-2">
                @foreach($this->modeStats as $modeStat)
                    <div class="leader-mode-stat">
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
            <div class="col-lg-4">
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['D' => 'Full Tryout', 'A' => 'Paket A', 'B' => 'Paket B', 'C' => 'Paket C'] as $code => $label)
                        <button
                            type="button"
                            wire:click="$set('paket', '{{ $code }}')"
                            class="apple-glass-btn {{ $paket === $code ? 'apple-btn-primary' : 'apple-btn-outline' }} text-decoration-none"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-2">
                <select wire:model.live="examModeId" class="form-select apple-glass-input">
                    <option value="">Semua Mode</option>
                    @foreach($this->examModes as $mode)
                        <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <select wire:model.live="subCategoryId" class="form-select apple-glass-input">
                    <option value="">Semua Sub Kategori</option>
                    @foreach($this->subCategories as $subCategory)
                        <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <input
                    wire:model.live.debounce.300ms="search"
                    class="form-control apple-glass-input"
                    placeholder="Cari nama peserta..."
                >
            </div>
            <div class="col-lg-1 text-lg-end">
                <button type="button" wire:click="resetFilters" class="apple-glass-btn apple-btn-outline">Reset</button>
            </div>
        </div>
    </div>

    <div class="leader-results-shell" wire:loading.class="leader-results-shell--loading" wire:target="search,paket,examModeId,subCategoryId">
        <div class="apple-skeleton-table mb-3" wire:loading wire:target="search,paket,examModeId,subCategoryId">
            <div class="apple-skeleton-table__row"></div>
            <div class="apple-skeleton-table__row"></div>
            <div class="apple-skeleton-table__row"></div>
        </div>

        <div wire:loading.remove wire:target="search,paket,examModeId,subCategoryId">
            <div class="table-responsive d-none d-lg-block apple-glass-table-wrap">
                <table class="table apple-glass-table mb-0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Peserta</th>
                            <th>Mode</th>
                            <th>Paket</th>
                            <th>Ringkasan Skor</th>
                            <th>Total</th>
                            <th>Update</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->rankings as $index => $r)
                            @php
                                $scoreMeta = $this->scoreMeta($r);
                                $display = $this->displayMeta($r);
                            @endphp
                            <tr>
                                <td>
                                    <div class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">{{ $index + 1 }}</div>
                                </td>
                                <td>
                                    <div class="fw-700">{{ strtoupper($r->nama_peserta) }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="mode-badge-strong {{ $display['is_farmasi'] ? 'farmasi-mode-badge' : '' }}">{{ $display['mode_name'] }}</span>
                                        @if($display['sub_name'])
                                            <span class="sub-badge-soft {{ $display['is_farmasi'] ? 'farmasi-sub-badge' : '' }}">{{ $display['sub_name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="mode-badge-strong {{ $display['is_farmasi'] ? 'farmasi-mode-badge' : '' }}">{{ $display['mode_name'] }}</span></td>
                                <td><span class="apple-pill muted">Paket {{ $r->paket }}</span></td>
                                <td>
                                    <div class="apple-chip-row">
                                        @foreach($scoreMeta['chips'] as $chip)
                                            <span class="apple-chip {{ $chip['state'] }}">
                                                <span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td><span class="apple-pill total">{{ $r->total_skor }}</span></td>
                                <td class="small apple-muted">{{ optional($r->updated_at)->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('ujian.hasil', $r->id) }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 apple-muted">Belum ada peserta yang cocok dengan filter klasemen ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-lg-none vstack gap-3">
                @forelse($this->rankings as $index => $r)
                    @php
                        $scoreMeta = $this->scoreMeta($r);
                        $display = $this->displayMeta($r);
                    @endphp
                    <div class="leader-card apple-glass apple-glass-card p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="d-flex gap-3" style="min-width: 0;">
                                <div class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">{{ $index + 1 }}</div>
                                <div style="min-width: 0;">
                                    <div class="fw-800">{{ strtoupper($r->nama_peserta) }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="mode-badge-strong {{ $display['is_farmasi'] ? 'farmasi-mode-badge' : '' }}">{{ $display['mode_name'] }}</span>
                                        <span class="apple-pill muted">Paket {{ $r->paket }}</span>
                                        @if($display['sub_name'])
                                            <span class="sub-badge-soft {{ $display['is_farmasi'] ? 'farmasi-sub-badge' : '' }}">{{ $display['sub_name'] }}</span>
                                        @endif
                                    </div>
                                    <div class="apple-hint mt-2">{{ optional($r->updated_at)->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="apple-pill total">{{ $r->total_skor }}</span>
                            </div>
                        </div>
                        <div class="mt-3 apple-chip-row">
                            @foreach($scoreMeta['chips'] as $chip)
                                <span class="apple-chip {{ $chip['state'] }}"><span class="k">{{ \Illuminate\Support\Str::limit($chip['label'], 18, '') }}</span>{{ $chip['score'] }}</span>
                            @endforeach
                        </div>
                        <a href="{{ route('ujian.hasil', $r->id) }}" class="apple-glass-btn apple-btn-outline w-100 mt-3 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-eye opacity-75"></i>Detail
                        </a>
                    </div>
                @empty
                    <div class="text-center py-5 apple-muted">Belum ada peserta yang cocok dengan filter klasemen ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
