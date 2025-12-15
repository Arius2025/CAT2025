<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian - CAT CPNS 2024</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .score-card { border-radius: 15px; overflow: hidden; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center mb-4">
                <h1 class="fw-bold">Hasil Simulasi CAT</h1>
                <p class="fs-5 text-muted">Peserta: {{ $exam->participant_name }}</p>
            </div>
            
            <div class="col-md-10">
                <div class="row g-4 mb-4">
                    @foreach(['TWK' => $exam->score_twk, 'TIU' => $exam->score_tiu, 'TKP' => $exam->score_tkp] as $cat => $score)
                    <div class="col-md-4">
                        <div class="card score-card text-center shadow-sm">
                            <div class="card-header bg-dark text-white fw-bold">{{ $cat }}</div>
                            <div class="card-body py-4">
                                <h2 class="display-4 fw-bold {{ $score >= $pg[$cat] ? 'text-success' : 'text-danger' }}">{{ $score }}</h2>
                                <p class="mb-0">Passing Grade: {{ $pg[$cat] }}</p>
                                <span class="badge {{ $score >= $pg[$cat] ? 'bg-success' : 'bg-danger' }}">
                                    {{ $score >= $pg[$cat] ? 'MEMENUHI' : 'TIDAK MEMENUHI' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="card p-4 shadow-sm border-0 mb-4 text-center">
                    @php 
                        $isLolos = ($exam->score_twk >= $pg['TWK'] && $exam->score_tiu >= $pg['TIU'] && $exam->score_tkp >= $pg['TKP']);
                    @endphp
                    <h3 class="fw-bold {{ $isLolos ? 'text-success' : 'text-danger' }}">
                        {{ $isLolos ? 'SELAMAT! ANDA LOLOS AMBANG BATAS' : 'MAAF! ANDA BELUM LOLOS AMBANG BATAS' }}
                    </h3>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="{{ route('exam.pdf') }}" class="btn btn-dark">Cetak Pembahasan (PDF)</a>
                        <a href="{{ route('exam.index') }}" class="btn btn-outline-primary">Coba Lagi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>