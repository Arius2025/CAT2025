@extends('layouts.app')

@section('content')
@php
    $summary = $analysis['summary'] ?? [];
    $categories = $analysis['categories'] ?? [];
    $history = $analysis['history'] ?? [];
    $modes = $analysis['modes'] ?? [];
    $recommendations = $analysis['recommendations'] ?? [];
@endphp

<div class="container page-shell analysis-shell">
    <section class="page-hero apple-glass apple-glass-card mb-4">
        <div class="page-hero__row">
            <div class="page-hero__copy">
                <div class="apple-kicker mb-2">Analisis Belajar</div>
                <h1 class="display-6 apple-title mb-2">Peta kekuatan, kelemahan, dan tren belajar Anda</h1>
                <p class="apple-subtitle mb-0">Halaman ini membaca riwayat jawaban Anda sendiri, lalu merangkum area yang sudah kuat, bagian yang perlu diulang, dan mode ujian yang masih tertinggal.</p>
            </div>
            <div class="page-toolbar">
                <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-house me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="page-section__head">
            <div>
                <div class="apple-kicker">Ringkasan Performa</div>
                <div class="apple-hint">Gambaran cepat dari semua sesi yang sudah selesai.</div>
            </div>
        </div>
        <div class="page-stats-grid">
            <div class="page-stat-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-hint">Total Soal Dikerjakan</div>
                    <div class="display-6 fw-800">{{ number_format((int) ($summary['total_questions'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="page-stat-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-hint">Total Benar</div>
                    <div class="display-6 fw-800 text-success">{{ number_format((int) ($summary['total_correct'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="page-stat-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-hint">Total Salah</div>
                    <div class="display-6 fw-800 text-danger">{{ number_format((int) ($summary['total_wrong'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="page-stat-card">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-hint">Accuracy</div>
                    <div class="display-6 fw-800">{{ number_format((float) ($summary['accuracy'] ?? 0), 1) }}%</div>
                </div>
            </div>
            <div class="page-stat-card page-stat-card--wide">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <div>
                            <div class="apple-hint">Average Score</div>
                            <div class="display-6 fw-800">{{ number_format((float) ($summary['average_score'] ?? 0), 1) }}</div>
                        </div>
                        <div>
                            <div class="apple-hint">Total Sesi Ujian</div>
                            <div class="display-6 fw-800">{{ number_format((int) ($summary['total_sessions'] ?? 0)) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-stat-card page-stat-card--wide">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-hint mb-2">Komposisi Jawaban</div>
                    <canvas id="analysisDonut" height="150"></canvas>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <div class="apple-kicker">Kelebihan Saya</div>
                            <div class="apple-hint">Topik dengan akurasi tertinggi.</div>
                        </div>
                        <span class="apple-pill success">Strong</span>
                    </div>
                    @forelse(($categories['strongest'] ?? []) as $row)
                        <div class="analysis-topic-row">
                            <div>
                                <div class="fw-700">{{ $row['label'] }}</div>
                                <div class="apple-hint">{{ $row['correct'] }}/{{ $row['total'] }} benar</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-800 text-success">{{ number_format((float) $row['accuracy'], 1) }}%</div>
                                <div class="analysis-bar"><span class="analysis-bar__fill is-strong" style="width: {{ min(100, max(4, (float) $row['accuracy'])) }}%"></span></div>
                            </div>
                        </div>
                    @empty
                        <div class="page-empty">Data belum cukup untuk menentukan topik terkuat.</div>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-6">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <div class="apple-kicker">Yang Harus Diperbaiki</div>
                            <div class="apple-hint">Topik yang paling sering menghambat.</div>
                        </div>
                        <span class="apple-pill danger">Weak</span>
                    </div>
                    @forelse(($categories['weakest'] ?? []) as $row)
                        <div class="analysis-topic-row">
                            <div>
                                <div class="fw-700">{{ $row['label'] }}</div>
                                <div class="apple-hint">{{ $row['wrong'] }}/{{ $row['total'] }} masih salah</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-800 text-danger">{{ number_format((float) $row['accuracy'], 1) }}%</div>
                                <div class="analysis-bar"><span class="analysis-bar__fill is-weak" style="width: {{ min(100, max(4, (float) $row['accuracy'])) }}%"></span></div>
                            </div>
                        </div>
                    @empty
                        <div class="page-empty">Belum ada area lemah yang cukup kuat secara data.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-kicker mb-2">Rekomendasi Belajar Otomatis</div>
                    <div class="apple-hint mb-3">Saran ini dibentuk dari pola jawaban dan tren sesi terbaru.</div>
                    @forelse(($recommendations['suggestions'] ?? []) as $text)
                        <div class="analysis-note">
                            <i class="fas fa-bullseye"></i>
                            <span>{{ $text }}</span>
                        </div>
                    @empty
                        <div class="page-empty">Belum ada rekomendasi yang cukup spesifik.</div>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-6">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-kicker mb-2">Insight Pintar</div>
                    <div class="apple-hint mb-3">Pola performa yang sistem temukan dari riwayat Anda.</div>
                    @forelse(($recommendations['insights'] ?? []) as $text)
                        <div class="analysis-note is-info">
                            <i class="fas fa-chart-line"></i>
                            <span>{{ $text }}</span>
                        </div>
                    @empty
                        <div class="page-empty">Tambahkan beberapa sesi lagi agar pola belajar lebih mudah terbaca.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-kicker mb-2">Grafik Progress</div>
                    <div class="apple-hint mb-3">Akurasi dan skor Anda per sesi.</div>
                    <canvas id="analysisTrend" height="120"></canvas>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="apple-glass apple-glass-card p-4 h-100">
                    <div class="apple-kicker mb-2">Weekly Progress</div>
                    <div class="apple-hint mb-3">Rerata mingguan dari sesi yang sudah selesai.</div>
                    <canvas id="analysisWeekly" height="120"></canvas>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="apple-glass apple-glass-card p-4">
            <div class="apple-kicker mb-2">Breakdown Per Mode Ujian</div>
            <div class="apple-hint mb-3">Modul ini otomatis mengikuti mode ujian yang ada di database.</div>
            <div class="row g-3">
                @forelse($modes as $mode)
                    <div class="col-md-6 col-xl-4">
                        <div class="analysis-mode-card">
                            <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                                <div>
                                    <div class="fw-800">{{ $mode['mode_label'] }}</div>
                                    <div class="apple-hint">{{ $mode['total_attempts'] }} sesi</div>
                                </div>
                                <span class="analysis-status analysis-status--{{ ($mode['accuracy'] ?? 0) >= 80 ? 'strong' : (($mode['accuracy'] ?? 0) >= 60 ? 'medium' : 'weak') }}">
                                    {{ number_format((float) ($mode['accuracy'] ?? 0), 1) }}%
                                </span>
                            </div>
                            <div class="analysis-mini-stat">
                                <span>Average score</span>
                                <strong>{{ number_format((float) ($mode['average_score'] ?? 0), 1) }}</strong>
                            </div>
                            <div class="analysis-mini-stat">
                                <span>Strongest topic</span>
                                <strong>{{ $mode['strongest_topic']['label'] ?? 'Data belum cukup' }}</strong>
                            </div>
                            <div class="analysis-mini-stat">
                                <span>Weakest topic</span>
                                <strong>{{ $mode['weakest_topic']['label'] ?? 'Data belum cukup' }}</strong>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="page-empty">Belum ada data mode ujian yang cukup untuk dianalisis.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="apple-glass apple-glass-card p-4">
            <div class="apple-kicker mb-2">Last 10 Exams Performance</div>
            <div class="apple-hint mb-3">10 sesi terakhir untuk membaca ritme belajar paling dekat.</div>
            <div class="table-responsive">
                <table class="table apple-glass-table mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Mode</th>
                            <th>Paket</th>
                            <th>Score</th>
                            <th>Accuracy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($history['last_ten'] ?? []) as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['mode_label'] }}</td>
                                <td>{{ $row['paket'] }}</td>
                                <td>{{ $row['score'] }}</td>
                                <td>{{ number_format((float) $row['accuracy'], 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center apple-muted py-4">Belum ada riwayat ujian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .analysis-shell .analysis-topic-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        padding: 14px 0;
        border-top: 1px solid var(--border-glass);
    }
    .analysis-shell .analysis-topic-row:first-of-type {
        border-top: 0;
        padding-top: 0;
    }
    .analysis-bar {
        width: 120px;
        height: 8px;
        border-radius: 999px;
        background: var(--surface-2);
        overflow: hidden;
        margin-top: 8px;
        margin-left: auto;
    }
    .analysis-bar__fill {
        display: block;
        height: 100%;
        border-radius: inherit;
    }
    .analysis-bar__fill.is-strong { background: linear-gradient(90deg, var(--success), color-mix(in srgb, var(--success) 60%, white)); }
    .analysis-bar__fill.is-weak { background: linear-gradient(90deg, var(--warning), var(--danger)); }
    .analysis-note {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid color-mix(in srgb, var(--success) 24%, transparent);
        background: color-mix(in srgb, var(--success) 12%, var(--surface));
        margin-bottom: 12px;
    }
    .analysis-note.is-info {
        border-color: color-mix(in srgb, var(--accent) 22%, transparent);
        background: color-mix(in srgb, var(--accent) 12%, var(--surface));
    }
    .analysis-mode-card {
        height: 100%;
        border-radius: 22px;
        border: 1px solid var(--border-glass);
        background: var(--surface-2);
        padding: 18px;
        box-shadow: var(--shadow-soft);
    }
    .analysis-mini-stat {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-top: 1px solid var(--border-glass);
        font-size: 0.95rem;
    }
    .analysis-mini-stat:first-of-type {
        border-top: 0;
        padding-top: 0;
    }
    .analysis-status {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.82rem;
        border: 1px solid transparent;
    }
    .analysis-status--strong {
        color: var(--text-primary);
        background: color-mix(in srgb, var(--success) 14%, var(--surface));
        border-color: color-mix(in srgb, var(--success) 28%, transparent);
    }
    .analysis-status--medium {
        color: var(--text-primary);
        background: color-mix(in srgb, var(--warning) 14%, var(--surface));
        border-color: color-mix(in srgb, var(--warning) 28%, transparent);
    }
    .analysis-status--weak {
        color: var(--text-primary);
        background: color-mix(in srgb, var(--danger) 14%, var(--surface));
        border-color: color-mix(in srgb, var(--danger) 28%, transparent);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const analysis = @json($analysis);
        const history = analysis.history || {};
        const summary = analysis.summary || {};

        const styles = getComputedStyle(document.body);
        const color = (name, fallback = '') => (styles.getPropertyValue(name) || fallback).trim() || fallback;
        const theme = {
            text: color('--text-primary', '#e5eefc'),
            textMuted: color('--text-secondary', '#9fb5d1'),
            success: color('--success', '#10b981'),
            danger: color('--danger', '#ef4444'),
            warning: color('--warning', '#f59e0b'),
            accent: color('--accent', '#3b82f6'),
            accentSoft: color('--accent-soft', 'rgba(59,130,246,0.18)'),
            border: color('--border', 'rgba(148,163,184,0.18)'),
        };
        const timeline = Array.isArray(history.timeline) ? history.timeline : [];
        const weekly = Array.isArray(history.weekly) ? history.weekly : [];

        const donutEl = document.getElementById('analysisDonut');
        if (donutEl) {
            new Chart(donutEl, {
                type: 'doughnut',
                data: {
                    labels: ['Benar', 'Salah'],
                    datasets: [{
                        data: [summary.total_correct || 0, summary.total_wrong || 0],
                        backgroundColor: [theme.success, theme.danger],
                        borderWidth: 0,
                    }],
                },
                options: {
                    plugins: { legend: { labels: { color: theme.text } } },
                },
            });
        }

        const trendEl = document.getElementById('analysisTrend');
        if (trendEl) {
            new Chart(trendEl, {
                data: {
                    labels: timeline.map(row => row.label || row.date),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Accuracy %',
                            data: timeline.map(row => row.accuracy || 0),
                            borderColor: theme.success,
                            backgroundColor: theme.accentSoft,
                            tension: 0.35,
                            yAxisID: 'y',
                        },
                        {
                            type: 'bar',
                            label: 'Score',
                            data: timeline.map(row => row.score || 0),
                            backgroundColor: theme.accentSoft,
                            borderColor: theme.accent,
                            yAxisID: 'y1',
                        }
                    ],
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { labels: { color: theme.text } } },
                    scales: {
                        x: { ticks: { color: theme.textMuted }, grid: { color: theme.border } },
                        y: { beginAtZero: true, max: 100, position: 'left', ticks: { color: theme.textMuted }, grid: { color: theme.border } },
                        y1: { beginAtZero: true, position: 'right', ticks: { color: theme.textMuted }, grid: { drawOnChartArea: false, color: theme.border } },
                    },
                },
            });
        }

        const weeklyEl = document.getElementById('analysisWeekly');
        if (weeklyEl) {
            new Chart(weeklyEl, {
                type: 'line',
                data: {
                    labels: weekly.map(row => row.label),
                    datasets: [{
                        label: 'Weekly Accuracy %',
                        data: weekly.map(row => row.accuracy || 0),
                        borderColor: theme.warning,
                        backgroundColor: theme.accentSoft,
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: {
                    plugins: { legend: { labels: { color: theme.text } } },
                    scales: {
                        x: { ticks: { color: theme.textMuted }, grid: { color: theme.border } },
                        y: { beginAtZero: true, max: 100, ticks: { color: theme.textMuted }, grid: { color: theme.border } },
                    },
                },
            });
        }
    })();
</script>
@endpush
