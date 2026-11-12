@extends('layouts.app')

@section('content')
@php
    $status = $coach['status'] ?? [];
    $priorities = $coach['priorities']['items'] ?? [];
    $recommendations = $coach['recommendations']['items'] ?? [];
    $studyPlanDays = $coach['study_plan']['days'] ?? [];
    $prediction = $coach['prediction'] ?? [];
    $analysisSnapshot = $coach['analysis_snapshot'] ?? [];
@endphp

<div class="container page-shell coach-shell">
    <section class="page-hero apple-glass apple-glass-card mb-4">
        <div class="page-hero__row">
            <div class="page-hero__copy">
                <div class="apple-kicker mb-2">Mentor Pintar</div>
                <h1 class="display-6 apple-title mb-2">Belajar lebih cerdas dengan rekomendasi personal</h1>
                <p class="apple-subtitle mb-0">Modul ini membaca riwayat ujian Anda, menentukan prioritas belajar mingguan, lalu memberi saran seperti mentor yang fokus pada area paling berdampak.</p>
            </div>
            <div class="page-toolbar d-flex flex-column flex-sm-row gap-2">
                <a href="{{ route('analysis.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-chart-pie me-2"></i>Analisis
                </a>
                <button type="button" class="apple-glass-btn apple-btn-primary" id="coachRegenerateBtn">
                    <i class="fas fa-rotate-right me-2"></i>Regenerasi Jadwal
                </button>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="page-section__head">
            <div>
                <div class="apple-kicker">Status Kesiapan</div>
                <div class="apple-hint">Mode aktif dibaca langsung dari database, jadi tetap adaptif untuk mode baru.</div>
            </div>
        </div>
        <div class="row g-3">
            @forelse(($status['modes'] ?? []) as $mode)
                <div class="col-md-6 col-xl-4">
                    <div class="coach-card h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="coach-card__kicker">{{ strtoupper($mode['mode_code'] ?? 'mode') }}</div>
                                <div class="coach-card__title">{{ $mode['mode_label'] }}</div>
                            </div>
                            <span class="coach-badge coach-badge--{{ $mode['status'] }}">{{ number_format((float) ($mode['readiness'] ?? 0), 1) }}%</span>
                        </div>
                        <div class="coach-progress">
                            <span class="coach-progress__fill is-{{ $mode['status'] }}" style="width: {{ min(100, max(4, (float) ($mode['readiness'] ?? 0))) }}%"></span>
                        </div>
                        <div class="coach-meta-list mt-3">
                            <div><span>Akurasi</span><strong>{{ number_format((float) ($mode['accuracy'] ?? 0), 1) }}%</strong></div>
                            <div><span>Sesi</span><strong>{{ (int) ($mode['attempts'] ?? 0) }}</strong></div>
                            <div><span>Tren</span><strong>{{ $mode['trend']['label'] ?? 'Stabil' }}</strong></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="page-empty">Belum ada data mode yang cukup untuk dibaca Mentor Pintar.</div>
                </div>
            @endforelse
            <div class="col-md-6 col-xl-4">
                <div class="coach-card h-100">
                    <div class="coach-card__kicker">TREN PERFORMA</div>
                    <div class="coach-card__title">{{ $status['trend']['label'] ?? 'Belum cukup data' }}</div>
                    <p class="apple-hint mb-3">Delta {{ number_format((float) ($status['trend']['delta'] ?? 0), 1) }} poin dari beberapa sesi terakhir.</p>
                    <div class="coach-inline-stat {{ ($status['trend']['direction'] ?? 'flat') === 'up' ? 'is-positive' : ((($status['trend']['direction'] ?? 'flat') === 'down') ? 'is-negative' : '') }}">
                        <i class="fas {{ ($status['trend']['direction'] ?? 'flat') === 'up' ? 'fa-arrow-trend-up' : ((($status['trend']['direction'] ?? 'flat') === 'down') ? 'fa-arrow-trend-down' : 'fa-minus') }}"></i>
                        <span>{{ $status['trend']['label'] ?? 'Stabil' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="coach-card h-100">
                    <div class="coach-card__kicker">KONSISTENSI BELAJAR</div>
                    <div class="coach-card__title">{{ $status['consistency']['label'] ?? 'Belum cukup data' }}</div>
                    <div class="coach-progress mt-3">
                        <span class="coach-progress__fill is-{{ $status['consistency']['status'] ?? 'medium' }}" style="width: {{ min(100, max(4, (float) ($status['consistency']['score'] ?? 0))) }}%"></span>
                    </div>
                    <div class="coach-meta-list mt-3">
                        <div><span>Aktif</span><strong>{{ (int) ($status['consistency']['active_days'] ?? 0) }}/{{ (int) ($status['consistency']['window_days'] ?? 14) }} hari</strong></div>
                        <div><span>Jeda terakhir</span><strong>{{ (int) ($status['last_activity_days'] ?? 0) }} hari</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="row g-4">
            <div class="col-xl-7">
                <div class="coach-panel h-100">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <div class="apple-kicker">Prioritas Belajar Minggu Ini</div>
                            <div class="apple-hint">SPK memberi ranking topik berdasarkan akurasi, kesalahan terbaru, frekuensi topik, dan jeda latihan.</div>
                        </div>
                    </div>
                    @forelse($priorities as $index => $item)
                        <div class="coach-priority">
                            <div class="coach-priority__rank">{{ $index + 1 }}</div>
                            <div class="coach-priority__body">
                                <div class="d-flex justify-content-between gap-3 flex-wrap">
                                    <div>
                                        <div class="fw-800">{{ $item['topic_label'] }}</div>
                                        <div class="apple-hint">{{ $item['mode_label'] ?: 'Mode campuran' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-800 {{ ($item['priority_score'] ?? 0) >= 70 ? 'coach-score coach-score--urgent' : 'coach-score coach-score--medium' }}">{{ number_format((float) ($item['priority_score'] ?? 0), 1) }}</div>
                                        <div class="apple-hint">priority score</div>
                                    </div>
                                </div>
                                <div class="coach-meta-list mt-3">
                                    <div><span>Akurasi</span><strong>{{ number_format((float) ($item['accuracy'] ?? 0), 1) }}%</strong></div>
                                    <div><span>Salah terbaru</span><strong>{{ (int) ($item['recent_wrong_count'] ?? 0) }}</strong></div>
                                    <div><span>Jeda latihan</span><strong>{{ (int) ($item['days_since_practice'] ?? 0) }} hari</strong></div>
                                </div>
                                <div class="coach-reason mt-2">{{ $item['reason_text'] ?: 'Topik ini tetap diprioritaskan karena berdampak besar.' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="page-empty">Belum cukup data untuk menentukan prioritas mingguan yang tajam.</div>
                    @endforelse
                </div>
            </div>
            <div class="col-xl-5">
                <div class="coach-panel h-100">
                    <div class="apple-kicker mb-2">Saran Mentor</div>
                    <div class="apple-hint mb-3">Sistem pakar membaca pola yang cocok dengan rule aktif, lalu menerjemahkannya menjadi saran belajar praktis.</div>
                    @forelse($recommendations as $row)
                        <div class="coach-note">
                            <i class="fas fa-user-graduate"></i>
                            <span>{{ $row['text'] }}</span>
                        </div>
                    @empty
                        <div class="page-empty">Belum ada saran mentor yang cukup spesifik.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="coach-panel h-100">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <div class="apple-kicker">Jadwal Belajar Otomatis</div>
                            <div class="apple-hint">Rencana 7 hari dibuat dari prioritas aktual Anda, bukan template umum.</div>
                        </div>
                    </div>
                    <div id="coachPlanWrap">
                        @forelse($studyPlanDays as $day)
                            <div class="coach-day">
                                <div class="coach-day__head">{{ $day['label'] }}</div>
                                <div class="coach-day__tasks">
                                    @foreach(($day['tasks'] ?? []) as $task)
                                        <div class="coach-task">
                                            <span class="coach-task__dot"></span>
                                            <span>{{ $task['task_text'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="page-empty">Jadwal belajar belum terbentuk.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="coach-panel h-100">
                    <div class="apple-kicker mb-2">Prediksi Kesiapan</div>
                    <div class="coach-forecast coach-forecast--{{ ($prediction['level'] ?? 'sedang') === 'tinggi' ? 'strong' : ((($prediction['level'] ?? 'sedang') === 'rendah') ? 'urgent' : 'medium') }}">
                        <div class="coach-forecast__score">{{ number_format((float) ($prediction['readiness_score'] ?? 0), 1) }}</div>
                        <div>
                            <div class="coach-card__title mb-1">{{ $prediction['estimate'] ?? 'Belum cukup data' }}</div>
                            <div class="apple-hint">{{ $prediction['confidence_note'] ?? '' }}</div>
                        </div>
                    </div>
                    <div class="coach-meta-list mt-3">
                        <div><span>Window rekomendasi</span><strong>{{ $prediction['recommended_window'] ?? '-' }}</strong></div>
                        <div><span>Fokus utama</span><strong>{{ $prediction['focus_topic'] ?? 'Belum ada' }}</strong></div>
                        <div><span>Total sesi</span><strong>{{ (int) ($analysisSnapshot['summary']['total_sessions'] ?? 0) }}</strong></div>
                    </div>
                    <div class="coach-note is-soft mt-3">
                        <i class="fas fa-lightbulb"></i>
                        <span>{{ $prediction['message'] ?? 'Tambahkan sesi latihan untuk membuka prediksi yang lebih stabil.' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Analisis',
            'icon' => 'fa-chart-pie',
            'href' => route('analysis.index'),
        ],
        [
            'label' => 'Coach',
            'icon' => 'fa-user-graduate',
            'href' => route('coach.index'),
            'style' => 'cta',
            'active' => true,
        ],
        [
            'label' => 'Mulai',
            'icon' => 'fa-play',
            'id' => 'dockQuickStartBtn',
        ],
    ],
])
@endsection

@push('styles')
<style>
    .coach-shell .coach-card,
    .coach-shell .coach-panel {
        border-radius: 26px;
        border: 1px solid var(--border-glass);
        background: var(--card-bg);
        padding: 22px;
        height: 100%;
        box-shadow: var(--shadow-soft);
    }
    .coach-card__kicker {
        font-size: .78rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }
    .coach-card__title {
        font-weight: 800;
        font-size: 1.2rem;
        line-height: 1.2;
    }
    .coach-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .coach-badge--strong,
    .coach-progress__fill.is-strong { background: linear-gradient(90deg, color-mix(in srgb, var(--success) 16%, var(--surface)), color-mix(in srgb, var(--success) 48%, white)); color: var(--text-primary); border-color: color-mix(in srgb, var(--success) 28%, transparent); }
    .coach-badge--medium,
    .coach-progress__fill.is-medium { background: linear-gradient(90deg, color-mix(in srgb, var(--warning) 16%, var(--surface)), color-mix(in srgb, var(--warning) 42%, white)); color: var(--text-primary); border-color: color-mix(in srgb, var(--warning) 28%, transparent); }
    .coach-badge--urgent,
    .coach-progress__fill.is-urgent { background: linear-gradient(90deg, color-mix(in srgb, var(--danger) 16%, var(--surface)), color-mix(in srgb, var(--danger) 42%, white)); color: var(--text-primary); border-color: color-mix(in srgb, var(--danger) 28%, transparent); }
    .coach-progress {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: var(--surface-2);
        overflow: hidden;
    }
    .coach-progress__fill {
        display: block;
        height: 100%;
        border-radius: inherit;
    }
    .coach-meta-list {
        display: grid;
        gap: 10px;
    }
    .coach-meta-list > div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-top: 10px;
        border-top: 1px solid var(--border-glass);
        font-size: .95rem;
    }
    .coach-meta-list > div:first-child {
        padding-top: 0;
        border-top: 0;
    }
    .coach-inline-stat {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 16px;
        background: var(--surface-2);
        border: 1px solid var(--border-glass);
    }
    .coach-inline-stat.is-positive { color: var(--success); }
    .coach-inline-stat.is-negative { color: var(--danger); }
    .coach-score--urgent { color: var(--danger); }
    .coach-score--medium { color: var(--warning); }
    .coach-priority {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        gap: 16px;
        padding: 18px 0;
        border-top: 1px solid var(--border-glass);
    }
    .coach-priority:first-of-type {
        border-top: 0;
        padding-top: 0;
    }
    .coach-priority__rank {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: linear-gradient(180deg, color-mix(in srgb, var(--accent) 24%, transparent), color-mix(in srgb, var(--accent) 42%, var(--surface)));
        font-weight: 800;
        font-size: 1.1rem;
    }
    .coach-reason {
        color: var(--text-secondary);
        font-size: .95rem;
    }
    .coach-note {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 15px 16px;
        border-radius: 18px;
        border: 1px solid color-mix(in srgb, var(--accent) 20%, transparent);
        background: color-mix(in srgb, var(--accent) 10%, var(--surface));
        margin-bottom: 12px;
    }
    .coach-note.is-soft {
        background: color-mix(in srgb, var(--success) 10%, var(--surface));
        border-color: color-mix(in srgb, var(--success) 20%, transparent);
    }
    .coach-day {
        border-top: 1px solid var(--border-glass);
        padding: 16px 0;
    }
    .coach-day:first-of-type {
        border-top: 0;
        padding-top: 0;
    }
    .coach-day__head {
        font-weight: 800;
        margin-bottom: 12px;
    }
    .coach-day__tasks {
        display: grid;
        gap: 10px;
    }
    .coach-task {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 16px;
        background: var(--surface-2);
        border: 1px solid var(--border-glass);
    }
    .coach-task__dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: linear-gradient(180deg, color-mix(in srgb, var(--accent) 68%, white), var(--accent));
        margin-top: 6px;
        flex: 0 0 auto;
    }
    .coach-forecast {
        display: flex;
        gap: 16px;
        align-items: center;
        padding: 18px;
        border-radius: 22px;
        border: 1px solid var(--border-glass);
    }
    .coach-forecast--strong { background: color-mix(in srgb, var(--success) 10%, var(--surface)); }
    .coach-forecast--medium { background: color-mix(in srgb, var(--warning) 10%, var(--surface)); }
    .coach-forecast--urgent { background: color-mix(in srgb, var(--danger) 10%, var(--surface)); }
    .coach-forecast__score {
        min-width: 82px;
        height: 82px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 22px;
        background: var(--surface-2);
        font-weight: 900;
        font-size: 1.45rem;
    }
    @media (max-width: 767.98px) {
        .coach-shell .coach-card,
        .coach-shell .coach-panel {
            padding: 18px;
            border-radius: 22px;
        }
        .coach-priority {
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 12px;
        }
        .coach-priority__rank {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            font-size: 1rem;
        }
        .coach-forecast {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const btn = document.getElementById('coachRegenerateBtn');
        const wrap = document.getElementById('coachPlanWrap');
        if (!btn || !wrap) return;

        btn.addEventListener('click', async () => {
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

            try {
                const response = await fetch('{{ route('coach.regenerate') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({}),
                });
                const payload = await response.json();
                const days = Array.isArray(payload?.data?.days) ? payload.data.days : [];
                if (!days.length) return;

                wrap.innerHTML = days.map(day => `
                    <div class="coach-day">
                        <div class="coach-day__head">${day.label}</div>
                        <div class="coach-day__tasks">
                            ${(day.tasks || []).map(task => `
                                <div class="coach-task">
                                    <span class="coach-task__dot"></span>
                                    <span>${task.task_text}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Gagal regenerasi jadwal coach', error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        });
    })();
</script>
@endpush
