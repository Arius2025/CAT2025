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

    .admin-form-control {
        border-radius: 12px;
        border: 1px solid var(--border-glass);
        background: var(--input-bg, rgba(255, 255, 255, 0.06));
        color: var(--text-primary);
        font-family: inherit;
        padding: 8px 12px;
        font-size: 0.88rem;
    }

    .figural-upload-tile {
        border-radius: 16px;
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid var(--border-glass);
        padding: 14px;
        transition: all 0.2s ease;
    }
    [data-bs-theme="dark"] .figural-upload-tile {
        background: rgba(255, 255, 255, 0.04);
    }
    .figural-upload-tile:hover {
        border-color: rgba(197, 155, 39, 0.4);
    }
</style>
@endpush

<div class="admin-shell">
    {{-- Page Hero --}}
    <div class="admin-page-hero">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="admin-hero-kicker">
                    <i class="fas fa-shapes"></i> TIU Visual & Spasial
                </div>
                <h1 class="apple-title fs-2 fw-800 mb-1" style="color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                    Manajemen Soal Figural
                </h1>
                <div class="apple-subtitle" style="max-width: 700px;">
                    Kelola aset gambar stimulus dan opsi pilihan ganda spasial (A–E) untuk bank soal TIU figural berstandar CAT BKN.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill fs-7 fw-bold">
                    Total: {{ $totalTiu }} Soal
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill fs-7 fw-bold">
                    Siap: {{ $totalGambar }} Bergambar
                </span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 py-2 rounded-pill fs-7 fw-bold">
                    Perlu Aset: {{ $totalTanpaGambar }}
                </span>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="admin-card mb-4 p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.soal-figural.index') }}" class="apple-glass-btn {{ !request('filter') || request('filter') === 'semua' ? 'apple-btn-gold' : 'apple-btn-outline' }} px-3 py-2">
                    Semua Soal ({{ $totalTiu }})
                </a>
                <a href="{{ route('admin.soal-figural.index', ['filter' => 'ada_gambar']) }}" class="apple-glass-btn {{ request('filter') === 'ada_gambar' ? 'apple-btn-gold' : 'apple-btn-outline' }} px-3 py-2">
                    <i class="fas fa-image me-1"></i> Sudah Bergambar ({{ $totalGambar }})
                </a>
                <a href="{{ route('admin.soal-figural.index', ['filter' => 'tanpa_gambar']) }}" class="apple-glass-btn {{ request('filter') === 'tanpa_gambar' ? 'apple-btn-gold' : 'apple-btn-outline' }} px-3 py-2">
                    <i class="fas fa-triangle-exclamation me-1"></i> Belum Ada Gambar ({{ $totalTanpaGambar }})
                </a>
            </div>
            @if(($figuralModes ?? collect())->isNotEmpty())
                <div class="small text-secondary">
                    Mode Figural:
                    @foreach($figuralModes as $mode)
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill">{{ $mode->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- List Soal Figural --}}
    @forelse($soals as $soal)
        <div class="admin-card mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill font-monospace fw-bold">
                        #ID: {{ $soal->id }}
                    </span>
                    <div>
                        <div class="fw-bold fs-6" style="color: var(--text-primary);">
                            {{ $soal->sub_topik ?: 'TIU Spasial / Figural' }}
                        </div>
                        <small class="text-secondary">Kunci Resmi: <strong>Opsi {{ $soal->jawaban_benar ?: '-' }}</strong></small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.soal.edit', $soal->id) }}" class="btn btn-sm apple-glass-btn apple-btn-outline px-3 py-2">
                        <i class="fas fa-pen-to-square me-1"></i> Edit Teks Soal
                    </a>
                    <form action="{{ route('admin.soal-figural.destroy', $soal->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua berkas gambar pada butir soal ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-2 rounded-pill">
                            <i class="fas fa-trash me-1"></i> Reset Gambar
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-3 rounded-3 mb-3" style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-glass); line-height: 1.6; color: var(--text-primary);">
                {!! nl2br(e(strip_tags($soal->teks_soal))) !!}
            </div>

            <form action="{{ route('admin.soal-figural.upload', $soal->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php($fields = [
                    ['key' => 'gambar', 'db' => 'gambar_path', 'label' => 'Gambar Stimulus Utama'],
                    ['key' => 'gambar_a', 'db' => 'gambar_a', 'label' => 'Pilihan Opsi A'],
                    ['key' => 'gambar_b', 'db' => 'gambar_b', 'label' => 'Pilihan Opsi B'],
                    ['key' => 'gambar_c', 'db' => 'gambar_c', 'label' => 'Pilihan Opsi C'],
                    ['key' => 'gambar_d', 'db' => 'gambar_d', 'label' => 'Pilihan Opsi D'],
                    ['key' => 'gambar_e', 'db' => 'gambar_e', 'label' => 'Pilihan Opsi E'],
                ])
                <div class="row g-3">
                    @foreach($fields as $field)
                        <div class="col-md-4 col-sm-6">
                            <div class="figural-upload-tile h-100">
                                <div class="fw-bold small mb-2 text-uppercase" style="letter-spacing: 0.05em; color: var(--text-primary); font-size: 0.76rem;">
                                    {{ $field['label'] }}
                                </div>
                                @if(!empty($soal->{$field['db']}))
                                    <div class="text-center mb-2 p-2 rounded-3" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.1);">
                                        <img src="{{ asset('storage/' . $soal->{$field['db']}) }}" alt="{{ $field['label'] }}" class="img-fluid rounded" style="max-height: 120px; object-fit: contain;">
                                    </div>
                                @else
                                    <div class="text-center mb-2 p-3 rounded-3 text-secondary" style="background: rgba(0,0,0,0.03); border: 1px dashed var(--border-glass);">
                                        <i class="fas fa-image fa-2x opacity-25"></i>
                                        <div class="small mt-1" style="font-size: 0.72rem;">Belum ada gambar</div>
                                    </div>
                                @endif
                                <input type="file" name="{{ $field['key'] }}" class="form-control admin-form-control" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 text-end pt-3 border-top" style="border-color: rgba(197, 155, 39, 0.15) !important;">
                    <button type="submit" class="apple-glass-btn apple-btn-primary px-4 py-2 fw-bold">
                        <i class="fas fa-cloud-arrow-up me-2"></i> Simpan Gambar Soal
                    </button>
                </div>
            </form>
        </div>
    @empty
        <div class="admin-card text-center p-5">
            <i class="fas fa-shapes fa-3x text-secondary opacity-50 mb-3"></i>
            <h3 class="h5 fw-bold mb-1">Tidak Ditemukan Butir Soal Figural</h3>
            <p class="text-secondary small">Coba ubah filter atau tambahkan soal dengan kategori FIGURAL di Bank Soal.</p>
        </div>
    @endforelse

    <div class="mt-4 d-flex justify-content-center">
        {{ $soals->appends(request()->query())->links() }}
    </div>
</div>
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
            'label' => 'Figural',
            'icon' => 'fa-shapes',
            'href' => route('admin.soal-figural.index'),
            'style' => 'cta',
        ],
        [
            'label' => 'Audit',
            'icon' => 'fa-shield-halved',
            'href' => route('admin.audit-soal.index'),
        ],
    ],
])
@endsection
