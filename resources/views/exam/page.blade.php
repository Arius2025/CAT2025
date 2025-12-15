<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAT CPNS 2024 - Simulasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #2c3e50; border-bottom: 4px solid #e67e22; position: sticky; top: 0; z-index: 1020; }
        
        /* Box Soal */
        .question-box { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); min-height: 400px; }
        .question-text { font-size: 1.1rem; line-height: 1.6; }

        /* Navigasi Nomor Soal */
        .nav-no { 
            width: 38px; height: 38px; line-height: 38px; 
            text-align: center; margin: 3px; border-radius: 6px; 
            cursor: pointer; display: inline-block; font-weight: bold; 
            text-decoration: none; font-size: 0.85rem;
        }
        
        /* Status Warna */
        .bg-current { background-color: #007bff !important; color: white !important; border: 2px solid #2c3e50; }
        .bg-answered { background-color: #27ae60 !important; color: white !important; }
        .bg-skipped { background-color: #e74c3c !important; color: white !important; }
        .bg-unseen { background-color: #e9ecef !important; color: #2c3e50 !important; border: 1px solid #dee2e6; }
        
        /* Timer & Header */
        #timer { font-size: 1.3rem; font-weight: bold; color: #e67e22; }
        .btn-nav-toggle { background: #34495e; color: white; border: none; padding: 5px 12px; border-radius: 5px; }

        /* Pilihan Jawaban */
        .options-label { display: flex; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: 0.2s; align-items: flex-start; }
        .options-label:hover { background-color: #f8f9fa; }
        .btn-check:checked + .options-label { background-color: #e7f1ff; border-color: #007bff; font-weight: bold; }
        
        /* Sticky Footer Mobile */
        @media (max-width: 768px) {
            .mobile-bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 10px; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); display: flex; gap: 10px; z-index: 1000; }
            .question-box { margin-bottom: 80px; padding: 15px; }
            .navbar-brand { font-size: 1rem; }
            #timer { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark py-2 mb-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <button class="btn-nav-toggle d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNav">
                ☰ No
            </button>
            <span class="navbar-brand mb-0 fw-bold">CAT CPNS</span>
        </div>
        <div class="text-white text-end">
            <span id="timer">00:00:00</span>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-9 col-md-8">
            <div class="question-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary">Soal {{ $currentNumber }}</span>
                    <span class="badge bg-secondary">{{ $question->category }}</span>
                </div>
                
                <div class="question-text mb-4">
                    {!! nl2br(e($question->question_text)) !!}
                </div>

                <form action="{{ route('exam.submit') }}" method="POST" id="examForm">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <input type="hidden" name="next_no" value="{{ $currentNumber + 1 }}">

                    <div class="options-container">
                        @foreach(['A', 'B', 'C', 'D', 'E'] as $opt)
                            @php $field = 'option_' . strtolower($opt); @endphp
                            <input type="radio" class="btn-check" name="answer" value="{{ $opt }}" id="opt{{ $opt }}" 
                                   {{ (isset($answers[$question->id]) && $answers[$question->id] == $opt) ? 'checked' : '' }}>
                            <label class="options-label" for="opt{{ $opt }}">
                                <strong class="me-2">{{ $opt }}.</strong> <span>{{ $question->$field }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="d-none d-md-flex justify-content-between mt-4">
                        <a href="{{ route('exam.show', ['no' => max(1, $currentNumber - 1)]) }}" 
                           class="btn btn-outline-secondary px-4 {{ $currentNumber == 1 ? 'disabled' : '' }}">Sebelumnya</a>
                        
                        @if($currentNumber < $totalQuestions)
                            <button type="submit" name="action" value="next" class="btn btn-primary px-5">Simpan & Lanjut</button>
                        @else
                            <button type="submit" name="action" value="finish" class="btn btn-success px-5" onclick="return confirm('Selesai ujian?')">Selesai</button>
                        @endif
                    </div>

                    <div class="mobile-bottom-nav d-md-none">
                        <a href="{{ route('exam.show', ['no' => max(1, $currentNumber - 1)]) }}" 
                           class="btn btn-outline-secondary flex-grow-1 {{ $currentNumber == 1 ? 'disabled' : '' }}">←</a>
                        
                        @if($currentNumber < $totalQuestions)
                            <button type="submit" name="action" value="next" class="btn btn-primary flex-grow-1">Simpan & Lanjut</button>
                        @else
                            <button type="submit" name="action" value="finish" class="btn btn-success flex-grow-1" onclick="return confirm('Selesai?')">Selesai</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 d-none d-md-block">
            <div class="card shadow-sm border-0 card-navigation">
                <div class="card-header bg-white fw-bold">Navigasi Soal</div>
                <div class="card-body overflow-auto" style="max-height: 70vh;">
                    @include('exam.nav-buttons')
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNav">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">Daftar Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        @include('exam.nav-buttons')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let timeLeft = Math.floor({{ $remainingSeconds }}); 
    const timerDisplay = document.getElementById("timer");

    function updateTimer() {
        if (timeLeft <= 0) {
            document.getElementById("examForm").submit();
            return;
        }
        let h = Math.floor(timeLeft / 3600);
        let m = Math.floor((timeLeft % 3600) / 60);
        let s = Math.floor(timeLeft % 60);
        timerDisplay.innerHTML = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        timeLeft--;
    }
    setInterval(updateTimer, 1000);
    updateTimer();
</script>
</body>
</html>