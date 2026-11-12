<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()
            ->tokens()
            ->orderByDesc('id')
            ->get(['id', 'name', 'abilities', 'last_used_at', 'created_at']);

        return view('admin.api_tokens.index', [
            'tokens' => $tokens,
            'newToken' => session('new_api_token'),
            'newTokenName' => session('new_api_token_name'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'abilities' => 'nullable|string|max:1000',
        ]);

        $abilities = collect(explode(',', (string) ($validated['abilities'] ?? '')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        if (empty($abilities)) {
            $abilities = ['*'];
        }

        $token = $request->user()->createToken($validated['name'], $abilities);

        return redirect()
            ->route('admin.api-tokens.index')
            ->with('success', 'Token API berhasil dibuat. Simpan token ini sekarang karena hanya muncul sekali.')
            ->with('new_api_token', $token->plainTextToken)
            ->with('new_api_token_name', $validated['name']);
    }

    public function destroy(Request $request, int $id)
    {
        $request->user()->tokens()->where('id', $id)->delete();

        return redirect()
            ->route('admin.api-tokens.index')
            ->with('success', 'Token API berhasil dicabut.');
    }
}
