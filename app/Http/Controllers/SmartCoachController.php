<?php

namespace App\Http\Controllers;

use App\Services\SmartCoachService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SmartCoachController extends Controller
{
    public function __construct(
        private readonly SmartCoachService $coachService
    ) {
    }

    public function index(Request $request)
    {
        return view('coach.index', [
            'coach' => $this->coachService->dashboard($request->user()),
        ]);
    }

    public function regenerateStudyPlan(Request $request)
    {
        return response()->json([
            'ok' => true,
            'message' => 'Jadwal belajar berhasil diperbarui.',
            'data' => $this->coachService->regenerateStudyPlan($request->user()),
        ], Response::HTTP_OK);
    }
}
