<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SmartCoachService;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    public function __construct(
        private readonly SmartCoachService $coachService
    ) {
    }

    public function status(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->coachService->dashboard($request->user())['status'],
        ]);
    }

    public function priorities(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->coachService->dashboard($request->user())['priorities'],
        ]);
    }

    public function recommendations(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->coachService->dashboard($request->user())['recommendations'],
        ]);
    }

    public function studyPlan(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->coachService->dashboard($request->user())['study_plan'],
        ]);
    }

    public function prediction(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->coachService->dashboard($request->user())['prediction'],
        ]);
    }

    public function regenerateStudyPlan(Request $request)
    {
        return response()->json([
            'ok' => true,
            'message' => 'Jadwal belajar berhasil diperbarui.',
            'data' => $this->coachService->regenerateStudyPlan($request->user()),
        ]);
    }
}
