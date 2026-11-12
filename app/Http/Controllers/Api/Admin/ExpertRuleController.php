<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\SmartCoachService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpertRuleController extends Controller
{
    public function __construct(
        private readonly SmartCoachService $coachService
    ) {
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'ok' => true,
            'data' => $this->coachService->expertRules(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'code' => 'required|string|max:80|unique:expert_rules,code',
            'condition_json' => 'required|array',
            'result_text' => 'required|string|max:5000',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:1|max:9999',
        ]);

        $rule = $this->coachService->createExpertRule([
            'code' => $validated['code'],
            'condition_json' => $validated['condition_json'],
            'result_text' => $validated['result_text'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'priority' => (int) ($validated['priority'] ?? 100),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Aturan pakar berhasil ditambahkan.',
            'data' => $rule,
        ], Response::HTTP_CREATED);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user() && $request->user()->isMasterAdmin(), Response::HTTP_FORBIDDEN, 'Hanya admin utama yang boleh mengelola aturan pakar.');
    }
}
