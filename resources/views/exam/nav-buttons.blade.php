<div class="d-flex flex-wrap">
    @for($i = 1; $i <= $totalQuestions; $i++)
        @php
            $qId = $questions[$i-1]->id;
            $isAnswered = isset($answers[$qId]) && $answers[$qId] !== null;
            $visited = session('visited_questions', []);
            $isVisited = in_array($i, $visited);

            $class = 'bg-unseen';
            if ($i == $currentNumber) $class = 'bg-current';
            elseif ($isAnswered) $class = 'bg-answered';
            elseif ($isVisited) $class = 'bg-skipped';
        @endphp
        <a href="{{ route('exam.show', ['no' => $i]) }}" class="nav-no {{ $class }}">{{ $i }}</a>
    @endfor
</div>
<hr>
<div class="small text-muted">
    <div class="mb-1"><span class="nav-no bg-current" style="width:12px; height:12px;"></span> Aktif</div>
    <div class="mb-1"><span class="nav-no bg-answered" style="width:12px; height:12px;"></span> Terisi</div>
    <div class="mb-1"><span class="nav-no bg-skipped" style="width:12px; height:12px;"></span> Kosong</div>
    <div><span class="nav-no bg-unseen" style="width:12px; height:12px; border:1px solid #ccc;"></span> Belum</div>
</div>