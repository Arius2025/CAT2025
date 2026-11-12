<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LearningAnalysisService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function __construct(
        private readonly LearningAnalysisService $analysisService
    ) {
    }

    public function summary(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->analysisService->dashboard($request->user())['summary'],
        ]);
    }

    public function categories(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->analysisService->dashboard($request->user())['categories'],
        ]);
    }

    public function recommendations(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->analysisService->dashboard($request->user())['recommendations'],
        ]);
    }

    public function history(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->analysisService->dashboard($request->user())['history'],
        ]);
    }

    public function modes(Request $request)
    {
        return response()->json([
            'ok' => true,
            'data' => $this->analysisService->dashboard($request->user())['modes'],
        ]);
    }
}
