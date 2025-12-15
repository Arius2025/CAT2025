<!DOCTYPE html>
<html>
<head>
    <title>Pembahasan CAT CPNS - {{ $exam->participant_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; }
        .question-item { margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .category-badge { background: #2c3e50; color: white; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 10px; }
        .answer-box { margin-top: 8px; padding: 10px; border-radius: 5px; }
        .correct { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .wrong { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .tkp { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .page-break { page-break-after: always; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;">PEMBAHASAN HASIL CAT CPNS</h1>
        <p style="margin:5px 0;">Standar Kelulusan Permenpan RB Tahun 2024</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="150">Nama Peserta</td><td>: <strong>{{ $exam->participant_name }}</strong></td>
            <td width="100">Skor TWK</td><td>: {{ $exam->score_twk }}</td>
        </tr>
        <tr>
            <td>Tanggal Ujian</td><td>: {{ $exam->created_at->format('d F Y H:i') }}</td>
            <td>Skor TIU</td><td>: {{ $exam->score_tiu }}</td>
        </tr>
        <tr>
            <td>Total Skor</td><td>: <strong>{{ $exam->score_twk + $exam->score_tiu + $exam->score_tkp }}</strong></td>
            <td>Skor TKP</td><td>: {{ $exam->score_tkp }}</td>
        </tr>
    </table>

    <hr>

    <h3>Daftar Pembahasan Soal</h3>

    @foreach($questions as $index => $q)
        @php 
            $userAns = $userAnswers[$q->id] ?? '-';
            $isCorrect = ($q->category != 'TKP' && $userAns == $q->correct_answer);
            $tkpPoints = $q->category == 'TKP' ? json_decode($q->tkp_points, true) : null;
        @endphp

        <div class="question-item">
            <div>
                <span class="category-badge">{{ $q->category }}</span>
                <strong>Soal No. {{ $index + 1 }}</strong>
            </div>
            <p>{{ $q->question_text }}</p>
            
            <div class="answer-box {{ $q->category == 'TKP' ? 'tkp' : ($isCorrect ? 'correct' : 'wrong') }}">
                <table width="100%">
                    <tr>
                        <td width="50%">Jawaban Anda: <strong>{{ $userAns }}</strong></td>
                        <td>
                            @if($q->category != 'TKP')
                                Kunci Jawaban: <strong>{{ $q->correct_answer }}</strong>
                            @else
                                Poin Jawaban Anda: <strong>{{ $tkpPoints[$userAns] ?? 0 }}</strong>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if(($index + 1) % 4 == 0)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        Dicetak secara otomatis oleh Sistem Simulasi CAT CPNS - {{ date('Y') }}
    </div>
</body>
</html>