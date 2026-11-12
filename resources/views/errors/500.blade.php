@extends('layouts.app')

@section('content')
<div class="container page-shell d-flex align-items-center" style="min-height: 78vh;">
    <div class="w-100">
        <section class="page-hero text-center">
            <div class="apple-kicker mb-2">500 Error</div>
            <div class="display-1 fw-800 mb-3" style="background: linear-gradient(135deg, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -5px;">500</div>
            <h1 class="apple-title mb-3">Terjadi Kendala di Server</h1>
            <p class="apple-subtitle fs-5 mx-auto mb-4" style="max-width: 640px;">
                Sistem sedang mencoba pulih. Halaman ini tidak gagal total, tapi server butuh sedikit waktu untuk merespons dengan stabil.
            </p>
            <div class="page-empty mx-auto mb-4" style="max-width: 460px;">
                Kamu bisa kembali ke halaman sebelumnya, atau kembali ke dashboard utama untuk melanjutkan alur yang lain.
            </div>
            <div class="page-toolbar justify-content-center">
                <a href="{{ url()->previous() }}" class="apple-glass-btn apple-btn-outline text-decoration-none">Kembali</a>
                <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-primary text-decoration-none">Dashboard Utama</a>
            </div>
        </section>
    </div>
</div>
@endsection
