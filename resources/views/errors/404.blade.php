@extends('layouts.app')

@section('content')
<div class="container page-shell d-flex align-items-center" style="min-height: 78vh;">
    <div class="w-100">
        <section class="page-hero text-center">
            <div class="apple-kicker mb-2">404 Error</div>
            <div class="display-1 fw-800 mb-3" style="background: linear-gradient(135deg, var(--accent-blue), #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -5px;">404</div>
            <h1 class="apple-title mb-3">Halaman Tidak Ditemukan</h1>
            <p class="apple-subtitle fs-5 mx-auto mb-4" style="max-width: 640px;">
                Halaman yang kamu tuju kemungkinan sudah dipindah, belum tersedia, atau tautannya sudah tidak aktif.
            </p>
            <div class="page-empty mx-auto mb-4" style="max-width: 460px;">
                Coba kembali ke dashboard atau buka panduan agar alur penggunaan tetap jelas.
            </div>
            <div class="page-toolbar justify-content-center">
                <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-primary text-decoration-none">Kembali ke Dashboard</a>
                <a href="{{ route('guide.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Buka Panduan</a>
            </div>
        </section>
    </div>
</div>
@endsection
