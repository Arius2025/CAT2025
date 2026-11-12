<?php

namespace App\Http\Controllers;

use App\Services\LearningAnalysisService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function __construct(
        private readonly LearningAnalysisService $analysisService
    ) {
    }

    public function index(Request $request)
    {
        $analysis = $this->analysisService->dashboard($request->user());

        return view('analysis.index', [
            'analysis' => $analysis,
        ]);
    }
}
