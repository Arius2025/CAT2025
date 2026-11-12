<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthApiController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'instansi' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'instansi' => $validated['instansi'] ?? '',
            'no_hp' => $validated['no_hp'] ?? '',
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();
        if ($user && method_exists($user, 'isActive') && !$user->isActive()) {
            $message = method_exists($user, 'activationStatus') && $user->activationStatus() === 'pending'
                ? 'Akun ini masih menunggu persetujuan master admin.'
                : 'Akun ini sedang dinonaktifkan oleh admin.';
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 403);
        }

        if (!Auth::attempt($validated)) {
            return response()->json(['ok' => false, 'message' => 'Login gagal.'], 422);
        }

        $user = $request->user();
        if ($user && method_exists($user, 'isActive') && !$user->isActive()) {
            Auth::logout();
            $message = method_exists($user, 'activationStatus') && $user->activationStatus() === 'pending'
                ? 'Akun ini masih menunggu persetujuan master admin.'
                : 'Akun ini sedang dinonaktifkan oleh admin.';

            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 403);
        }
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'ok' => true,
            'user' => $request->user(),
        ]);
    }
}
