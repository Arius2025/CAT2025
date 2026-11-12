@push('styles')
<style>
    :root {
        --ai-card: rgba(30, 41, 59, 0.45);
        --ai-soft: rgba(255,255,255,0.06);
        --ai-soft-2: rgba(255,255,255,0.10);
    }
    [data-bs-theme="light"] {
        --ai-card: rgba(255, 255, 255, 0.65);
        --ai-soft: rgba(0,0,0,0.04);
        --ai-soft-2: rgba(0,0,0,0.06);
    }

    .ai-hero {
        border-radius: 24px;
        border: 1px solid var(--border-glass);
        background:
            radial-gradient(1200px 500px at 10% 0%, rgba(245, 158, 11, 0.18), transparent 55%),
            radial-gradient(900px 450px at 90% 10%, rgba(239, 68, 68, 0.22), transparent 55%),
            linear-gradient(135deg, rgba(30, 41, 59, 0.55), rgba(15, 23, 42, 0.55));
        box-shadow: var(--shadow-premium);
        overflow: hidden;
    }

    [data-bs-theme="light"] .ai-hero {
        background:
            radial-gradient(1200px 500px at 10% 0%, rgba(245, 158, 11, 0.14), transparent 55%),
            radial-gradient(900px 450px at 90% 10%, rgba(239, 68, 68, 0.16), transparent 55%),
            linear-gradient(135deg, rgba(255,255,255,0.8), rgba(241, 245, 249, 0.8));
    }

    .ai-panel {
        background: var(--ai-card);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--border-glass);
        border-radius: 20px;
        box-shadow: var(--shadow-premium);
    }

    .ai-label {
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid var(--border-glass);
        background: var(--ai-soft-2);
        color: var(--text-secondary);
        font-weight: 700;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .audit-card {
        transition: transform 0.2s;
    }
    
    .audit-card:hover {
        transform: translateY(-2px);
    }

    .diff-box {
        background: var(--ai-soft);
        border-radius: 12px;
        padding: 12px;
        border: 1px solid var(--border-glass);
    }
</style>
@endpush

<div class="py-4">
    <div class="ai-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="ai-label mb-2">Admin Tools</div>
                <h1 class="fw-bold mb-2" style="color: var(--text-primary); font-weight: 800;">Audit Soal AI</h1>
                <div class="text-muted" style="color: var(--text-secondary) !important;">
                    Review soal yang kunci jawabannya berbeda dengan saran AI, atau pembahasannya kurang baik.
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="chip"><i class="fas fa-microscope opacity-75"></i> {{ $audits->total() }} Mismatch Validations</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert border-0 shadow-sm apple-alert apple-alert-inline mb-4" style="background: rgba(16,185,129,0.12); color: var(--text-primary); border: 1px solid rgba(16,185,129,0.25) !important;">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert border-0 shadow-sm apple-alert apple-alert-inline mb-4" style="background: rgba(239,68,68,0.10); color: var(--text-primary); border: 1px solid rgba(239,68,68,0.25) !important;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @php
        $auditJobId = session('audit_processing_job_id');
    @endphp
    <div id="auditStatusPanel" class="ai-panel apple-glass apple-glass-card p-4 mb-4 d-none">
        <div class="d-flex align-items-start justify-content-between gap-3">
            <div>
                <div class="ai-label mb-1">Status Audit</div>
                <div class="fw-800" style="color: var(--text-primary);" id="auditStatusText">Menyiapkan...</div>
                <div class="small mt-1" style="color: var(--text-secondary);" id="auditStatusMeta"></div>
                <div class="small mt-1" style="color: var(--text-secondary);" id="auditQueueMeta"></div>
            </div>
            <div class="text-end">
                <div class="ai-label mb-1">Progress</div>
                <div class="fw-800" style="color: var(--text-primary);" id="auditPct">0%</div>
                <div class="small" style="color: var(--text-secondary);" id="auditCount">0/0</div>
            </div>
        </div>
        <div class="progress mt-3" style="height: 10px; background: rgba(255,255,255,0.06); border-radius: 999px; overflow: hidden;">
            <div class="progress-bar" id="auditBar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));"></div>
        </div>
        <div class="small mt-3 d-none" id="auditLastError" style="color: rgba(239,68,68,0.95);"></div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <!-- Filter Kategori -->
        <div class="w-100">
            <div class="d-flex gap-2 overflow-auto pb-2 pb-md-0 m-0 mb-3">
                <button type="button" wire:click="selectKategori('')" class="apple-glass-btn {{ $kategori == '' ? 'apple-btn-primary' : 'apple-btn-outline' }}">Semua</button>
                <button type="button" wire:click="selectKategori('TWK')" class="apple-glass-btn {{ $kategori == 'TWK' ? 'apple-btn-primary' : 'apple-btn-outline' }}">TWK</button>
                <button type="button" wire:click="selectKategori('TIU')" class="apple-glass-btn {{ $kategori == 'TIU' ? 'apple-btn-primary' : 'apple-btn-outline' }}">TIU</button>
                <button type="button" wire:click="selectKategori('TKP')" class="apple-glass-btn {{ $kategori == 'TKP' ? 'apple-btn-primary' : 'apple-btn-outline' }}">TKP</button>
                <button type="button" wire:click="applyQuickMode('pppk')" class="apple-glass-btn apple-btn-outline">PPPK Farmasi</button>
                <button type="button" wire:click="applyQuickMode('skb')" class="apple-glass-btn apple-btn-outline">SKB Farmasi</button>
            </div>

            <div class="row g-2" wire:loading.class="opacity-75" wire:target="kategori,examModeId,subCategoryId,difficulty">
                <div class="col-md-4">
                    <select wire:model.live="examModeId" id="auditExamModeSelect" class="form-select apple-glass-input">
                        <option value="">Semua Mode</option>
                        @foreach(($examModes ?? collect()) as $mode)
                            <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="subCategoryId" id="auditSubCategorySelect" class="form-select apple-glass-input">
                        <option value="">Semua Sub Kategori</option>
                        @foreach(($subCategories ?? collect()) as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="difficulty" class="form-select apple-glass-input">
                        <option value="">Semua Difficulty</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <div class="apple-glass-btn apple-btn-outline disabled" wire:loading wire:target="kategori,examModeId,subCategoryId,difficulty">Memuat...</div>
                    <button type="button" wire:click="resetFilters" class="apple-glass-btn apple-btn-outline">Reset</button>
                </div>
            </div>
        </div>

        <!-- Tombol Trigger Audit -->
        <form action="{{ route('admin.audit-soal.trigger') }}" method="POST">
            @csrf
            <input type="hidden" name="kategori" value="{{ $kategori ?: 'ALL' }}">
            <input type="hidden" name="exam_mode_id" value="{{ $examModeId ?: '' }}">
            <input type="hidden" name="sub_category_id" value="{{ $subCategoryId ?: '' }}">
            <button type="submit" class="apple-glass-btn apple-btn-primary" onclick="return confirm('Mulai proses audit AI (Groq) secara massal untuk kategori {{ $kategori ?: 'Semua' }}? Ini akan memakan waktu dan masuk ke background Queue.')">
                <i class="fas fa-play me-2"></i> Jalankan Audit ({{ $kategori ?: 'Semua' }})
            </button>
        </form>
    </div>

    @if($audits->isEmpty())
        <div class="ai-panel p-5 text-center">
            <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-75"></i>
            <h4 class="fw-bold" style="color: var(--text-primary);">Semua Aman!</h4>
            <p style="color: var(--text-secondary);">Tidak ada soal yang mismatch atau membutuhkan perbaikan pembahasan saat ini.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($audits as $audit)
                <div class="col-12">
                    <div class="ai-panel apple-glass apple-glass-card p-4 audit-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge {{ $audit->status == 'mismatch' ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill mb-2">
                                    {{ $audit->status == 'mismatch' ? 'KUNCI BERBEDA' : 'PEMBAHASAN KURANG' }}
                                </span>
                                <span class="badge bg-secondary rounded-pill mb-2 ms-1">{{ $audit->soal->kategori ?? 'Unknown' }}</span>
                                @if(!empty($audit->soal->difficulty))
                                    <span class="badge bg-info text-dark rounded-pill mb-2 ms-1">{{ strtoupper($audit->soal->difficulty) }}</span>
                                @endif
                            </div>
                            <small class="text-muted">Diaudit: {{ $audit->created_at->diffForHumans() }}</small>
                        </div>

                        <div class="mb-3" style="color: var(--text-primary);">
                            @if($audit->soal)
                                {!! nl2br(e($audit->soal->teks_soal)) !!}
                                
                                <div class="mt-3 ps-3 border-start border-2 border-secondary small" style="color: var(--text-secondary);">
                                    @if(in_array($audit->soal->kategori, ['TWK', 'TIU']) && isset($audit->soal->opsi_a))
                                        <div class="{{ $audit->db_key == 'A' ? 'text-danger fw-bold' : ($audit->ai_key == 'A' ? 'text-success fw-bold' : '') }}">A. {{ $audit->soal->opsi_a }}</div>
                                        <div class="{{ $audit->db_key == 'B' ? 'text-danger fw-bold' : ($audit->ai_key == 'B' ? 'text-success fw-bold' : '') }}">B. {{ $audit->soal->opsi_b }}</div>
                                        <div class="{{ $audit->db_key == 'C' ? 'text-danger fw-bold' : ($audit->ai_key == 'C' ? 'text-success fw-bold' : '') }}">C. {{ $audit->soal->opsi_c }}</div>
                                        <div class="{{ $audit->db_key == 'D' ? 'text-danger fw-bold' : ($audit->ai_key == 'D' ? 'text-success fw-bold' : '') }}">D. {{ $audit->soal->opsi_d }}</div>
                                        <div class="{{ $audit->db_key == 'E' ? 'text-danger fw-bold' : ($audit->ai_key == 'E' ? 'text-success fw-bold' : '') }}">E. {{ $audit->soal->opsi_e }}</div>
                                    @else
                                        <em>Opsi TKP tidak ditampilkan secara detal di sini, fokus evaluasi bobot.</em><br>
                                        <small>{{ json_encode($audit->soal->bobot_tkp ?? []) }}</small>
                                    @endif
                                </div>
                            @else
                                <em class="text-danger">Soal asli sudah dihapus dari database.</em>
                            @endif
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="diff-box h-100">
                                    <div class="ai-label mb-2 text-danger"><i class="fas fa-database me-1"></i> Data DB Saat Ini</div>
                                    <div class="mb-2"><strong class="text-danger fs-5">{{ $audit->db_key ?? '-' }}</strong></div>
                                    @if(!$audit->pembahasan_ok)
                                        <div class="small" style="color: var(--text-secondary);">
                                            <strong>Pembahasan DB:</strong><br>
                                            {!! nl2br(e($audit->soal->pembahasan ?? '-')) !!}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="diff-box h-100" style="border-color: rgba(16,185,129,0.3);">
                                    <div class="ai-label mb-2 text-success"><i class="fas fa-robot me-1"></i> Saran AI ({{ $audit->provider }})</div>
                                    <div class="mb-2"><strong class="text-success fs-5">{{ $audit->ai_key ?? '-' }}</strong></div>
                                    @if($audit->ai_pembahasan)
                                        <div class="small" style="color: var(--text-secondary);">
                                            <strong>Saran Pembahasan:</strong><br>
                                            {!! nl2br(e($audit->ai_pembahasan)) !!}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($audit->notes)
                            <div class="alert alert-secondary small mb-4 border-0 d-flex align-items-start" style="background: var(--ai-soft-2); color: var(--text-primary);">
                                <i class="fas fa-info-circle mt-1 me-2 opacity-75"></i>
                                <div><strong>Catatan AI:</strong> {{ $audit->notes }}</div>
                            </div>
                        @endif

                        @if($audit->needs_revision)
                            <div class="alert border-0 mb-4 d-flex align-items-start" style="background: rgba(245,158,11,0.10); color: var(--text-primary); border: 1px solid rgba(245,158,11,0.22) !important; border-radius: 16px;">
                                <i class="fas fa-wrench mt-1 me-2 text-warning opacity-75"></i>
                                <div>
                                    <div class="fw-800">Perlu Revisi Penuh</div>
                                    <div class="small" style="color: var(--text-secondary);">
                                        {{ $audit->revision_reason ?: 'AI mendeteksi jawaban tidak ada di opsi / opsi tidak konsisten.' }}
                                    </div>
                                </div>
                            </div>

                            <div class="diff-box mb-4">
                                <div class="ai-label mb-2"><i class="fas fa-pen-to-square me-1 opacity-75"></i> Usulan Revisi (Preview)</div>
                                <div class="mb-3"><strong>Soal:</strong><br>{!! nl2br(e($audit->proposed_teks_soal ?? '-')) !!}</div>
                                <div class="small" style="color: var(--text-secondary);">
                                    <div><strong>Kunci:</strong> <span class="fw-800" style="color: var(--text-primary);">{{ $audit->proposed_jawaban_benar ?? '-' }}</span></div>
                                    <div class="mt-2"><strong>Opsi:</strong></div>
                                    <div>A. {{ $audit->proposed_opsi_a }}</div>
                                    <div>B. {{ $audit->proposed_opsi_b }}</div>
                                    <div>C. {{ $audit->proposed_opsi_c }}</div>
                                    <div>D. {{ $audit->proposed_opsi_d }}</div>
                                    <div>E. {{ $audit->proposed_opsi_e }}</div>
                                    @if($audit->proposed_pembahasan)
                                        <div class="mt-2"><strong>Pembahasan:</strong><br>{!! nl2br(e($audit->proposed_pembahasan)) !!}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="d-flex gap-2 justify-content-end border-top pt-3 border-secondary" style="border-opacity: 0.2">
                            <a href="{{ route('admin.soal.edit', $audit->soal_id) }}" wire:navigate class="apple-glass-btn apple-btn-outline px-4">
                                <i class="fas fa-pen me-2 opacity-75"></i>Edit Soal
                            </a>
                            <form action="{{ route('admin.audit-soal.mark-valid', $audit->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="apple-glass-btn apple-btn-outline px-4" onclick="return confirm('Yakin kunci DB yang benar dan abaikan saran AI ini?')">
                                    <i class="fas fa-times me-2"></i>Abaikan (DB Benar)
                                </button>
                            </form>
                            <form action="{{ route('admin.audit-soal.apply', $audit->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="apple-glass-btn apple-btn-primary px-4" onclick="return confirm('Terapkan kunci dan pembahasan dari AI ke soal ini?')">
                                    <i class="fas fa-check me-2"></i>Terapkan Saran AI
                                </button>
                            </form>
                            @if($audit->needs_revision)
                                <form action="{{ route('admin.audit-soal.apply-revision', $audit->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="apple-glass-btn apple-btn-primary px-4"
                                            onclick="return confirm('Terapkan revisi penuh (soal+opsi+kunci+pembahasan) dari AI? Pastikan preview sudah benar.')">
                                        <i class="fas fa-wrench me-2 opacity-75"></i>Terapkan Revisi Penuh
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $audits->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    (function() {
        const jobId = @json($auditJobId);
        const examModeSelect = document.getElementById('auditExamModeSelect');
        const subCategorySelect = document.getElementById('auditSubCategorySelect');
        const subCategoryApiBase = @json(url('/api/sub-categories'));
        if (!jobId) return;
        try { window.QueueCenter?.addJob?.('audit', jobId); } catch (e) {}

        const panel = document.getElementById('auditStatusPanel');
        const txt = document.getElementById('auditStatusText');
        const meta = document.getElementById('auditStatusMeta');
        const qmeta = document.getElementById('auditQueueMeta');
        const pct = document.getElementById('auditPct');
        const count = document.getElementById('auditCount');
        const bar = document.getElementById('auditBar');
        const lastErr = document.getElementById('auditLastError');

        if (panel) panel.classList.remove('d-none');

        let t0 = null;
        function setElapsed(iso) {
            const t = new Date(iso).getTime();
            if (!isFinite(t)) return;
            if (!t0) t0 = t;
            const s = Math.max(0, Math.floor((Date.now() - t0) / 1000));
            return s;
        }

        async function poll() {
            for (let i = 0; i < 360; i++) {
                try {
                    const res = await fetch(@json(route('admin.audit-soal.status')) + '?id=' + encodeURIComponent(jobId), { credentials: 'same-origin' });
                    const data = await res.json();

                    const total = parseInt(data.total || 0, 10) || 0;
                    const done = parseInt(data.done || 0, 10) || 0;
                    const errors = parseInt(data.errors || 0, 10) || 0;
                    const p = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;

                    if (pct) pct.textContent = p + '%';
                    if (count) count.textContent = done + '/' + total + (errors ? (' (error: ' + errors + ')') : '');
                    if (bar) bar.style.width = p + '%';

                    const elapsed = data.queued_at ? setElapsed(data.queued_at) : null;
                    const k = data.kategori ? String(data.kategori) : '';
                    const prov = data.provider ? String(data.provider) : '';
                    if (meta) meta.textContent = (k ? (k + ' â€¢ ') : '') + (prov ? (prov + ' â€¢ ') : '') + (elapsed != null ? ('Waktu: ' + elapsed + 's') : '');

                    if (data.queue && data.queue.state) {
                        const st = data.queue.state === 'running' ? 'Sedang dikerjakan worker'
                            : data.queue.state === 'queued' ? 'Dalam antrian'
                            : data.queue.state === 'scheduled' ? 'Terjadwal'
                            : data.queue.state === 'step' ? 'Mode cepat (tanpa worker)'
                            : 'Tidak terdeteksi di antrian';
                        qmeta.textContent = 'Queue: ' + st + (data.queue.attempts != null ? (', attempts: ' + data.queue.attempts) : '');
                    }

                    if (data.last_error) {
                        if (lastErr) {
                            lastErr.classList.remove('d-none');
                            lastErr.textContent = 'Last error: ' + String(data.last_error);
                        }
                    }

                    if (data.status === 'success' || data.status === 'success_with_errors') {
                        if (txt) txt.textContent = data.status === 'success' ? 'Selesai.' : 'Selesai (ada error).';
                        try { window.QueueCenter?.removeJob?.('audit', jobId); } catch (e) {}
                        return;
                    }
                    if (data.status === 'canceled') {
                        if (txt) txt.textContent = 'Dibatalkan.';
                        try { window.QueueCenter?.removeJob?.('audit', jobId); } catch (e) {}
                        return;
                    }

                    if (txt) txt.textContent = 'Sedang diaudit...';

                    // Groq no-worker mode: perform a small step each poll.
                    if (data.runner === 'step') {
                        const token = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || (document.querySelector('input[name=\"_token\"]')?.value || '');
                        await fetch(@json(route('admin.audit-soal.step')), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify({ job_id: jobId }),
                            credentials: 'same-origin',
                        });
                    }
                } catch (e) {}

                await new Promise(r => setTimeout(r, 2500));
            }
            if (txt) txt.textContent = 'Timeout menunggu audit. Coba refresh.';
        }

        poll();
    })();

</script>
@endpush
