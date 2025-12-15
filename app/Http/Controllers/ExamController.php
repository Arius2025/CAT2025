<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\ExamSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExamController extends Controller
{
    public function index()
    {
        return view('exam.index');
    }

    public function start(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required' // Menangkap paket_1, paket_2, dst
        ]);
    
        // Ambil soal berdasarkan paket yang dipilih
        $questions = Question::where('type_id', $request->type)->get();
    
        if ($questions->isEmpty()) {
            return back()->with('error', 'Maaf, soal untuk paket ini belum tersedia.');
        }
    
        session([
            'participant_name' => $request->name,
            'questions' => $questions,
            'answers' => [],
            'end_time' => now()->addMinutes(100),
        ]);
    
        return redirect()->route('exam.show');
    }
    public function show(Request $request)
    {
        if (!session()->has('participant_name')) return redirect()->route('exam.index');

        $questions = session('questions');
        $answers = session('answers');
        
        // Ambil nomor dari URL, default ke 1
        $currentNumber = (int) $request->query('no', 1);
        
        // Proteksi jika nomor di luar jangkauan
        if ($currentNumber < 1 || $currentNumber > $questions->count()) {
            return redirect()->route('exam.show', ['no' => 1]);
        }

        $question = $questions[$currentNumber - 1];

        $endTime = session('end_time');
        $remainingSeconds = max(0, (int) floor(now()->diffInSeconds($endTime, false)));

        if ($remainingSeconds <= 0) {
            return $this->processResult();
        }

        // --- LOGIKA TRACKING VISITED (UNTUK WARNA MERAH) ---
        $visited = session()->get('visited_questions', []);
        
        // Gunakan $currentNumber agar sinkron dengan navigasi
        if (!in_array($currentNumber, $visited)) {
            $visited[] = $currentNumber;
            session()->put('visited_questions', $visited);
        }

        return view('exam.page', [
            'question' => $question,
            'currentNumber' => $currentNumber,
            'totalQuestions' => $questions->count(),
            'questions' => $questions,
            'answers' => $answers,
            'remainingSeconds' => $remainingSeconds
        ]);
    }

    public function submit(Request $request)
    {
        $answers = session('answers');
        
        // Simpan jawaban jika ada (agar tidak tertimpa null jika hanya klik navigasi)
        if ($request->has('answer')) {
            $answers[$request->question_id] = $request->answer;
            session(['answers' => $answers]);
        }

        if ($request->action == 'finish') {
            return $this->processResult();
        }

        // Tentukan nomor tujuan (Next atau No spesifik dari navigasi)
        $nextNo = $request->next_no;
        return redirect()->route('exam.show', ['no' => $nextNo]);
    }

    private function processResult()
    {
        $questions = session('questions');
        $userAnswers = session('answers');
        
        $score = ['TWK' => 0, 'TIU' => 0, 'TKP' => 0];

        foreach ($questions as $q) {
            $userAns = $userAnswers[$q->id] ?? null;

            if ($q->category == 'TKP') {
                if ($userAns) {
                    $points = json_decode($q->tkp_points, true);
                    $score['TKP'] += $points[strtoupper($userAns)] ?? 0;
                }
            } else {
                if ($userAns && strtoupper($userAns) == strtoupper($q->correct_answer)) {
                    $score[$q->category] += 5;
                }
            }
        }

        $exam = ExamSession::create([
            'participant_name' => session('participant_name'),
            'score_twk' => $score['TWK'],
            'score_tiu' => $score['TIU'],
            'score_tkp' => $score['TKP'],
            'is_finished' => true,
            'answers_data' => json_encode($userAnswers)
        ]);

        // Bersihkan session tracking setelah selesai
        session()->forget(['visited_questions', 'questions', 'answers']);
        
        session(['last_exam_id' => $exam->id]);
        return redirect()->route('exam.result');
    }

    public function result()
    {
        $exam = ExamSession::findOrFail(session('last_exam_id'));
        $pg = ['TWK' => 65, 'TIU' => 80, 'TKP' => 166];
        return view('exam.result', compact('exam', 'pg'));
    }

    public function downloadPdf()
    {
        $exam = ExamSession::findOrFail(session('last_exam_id'));
        $questions = Question::all();
        $userAnswers = json_decode($exam->answers_data, true);

        $pdf = Pdf::loadView('exam.pdf', compact('exam', 'questions', 'userAnswers'));
        return $pdf->download('Pembahasan_CAT_CPNS.pdf');
    }
}