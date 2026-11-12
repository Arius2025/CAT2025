<?php

namespace App\Http\Controllers;

use App\Models\ExamMode;
use App\Models\SubCategory;
use App\Models\User;
use App\Support\ExamStructureSupport;
use App\Support\ThemeManifest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    private function preferenceFormasiOptions()
    {
        if (!Schema::hasTable('sub_categories')) {
            return collect();
        }

        $rows = SubCategory::query()
            ->when(Schema::hasColumn('sub_categories', 'kind'), fn ($q) => $q->where('kind', 'formasi'))
            ->orderBy('name')
            ->get(['id', 'name', 'exam_mode_id']);

        return $rows
            ->reject(fn ($sub) => in_array(strtoupper(trim((string) $sub->name)), ['TWK', 'TIU', 'TKP'], true))
            ->groupBy(fn ($sub) => mb_strtolower(trim((string) $sub->name)))
            ->map(function ($group) {
                $first = $group->first();
                $first->exam_mode_ids = $group->pluck('exam_mode_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
                return $first;
            })
            ->sortBy('name')
            ->values();
    }

    private function normalizePreferredModeIds(Request $request): array
    {
        $ids = $request->input('preferred_exam_mode_ids', []);
        if (!is_array($ids)) {
            $ids = [];
        }
        if ($request->filled('preferred_exam_mode_id')) {
            $ids[] = $request->input('preferred_exam_mode_id');
        }

        return array_values(array_unique(array_filter(array_map(fn ($id) => (int) $id, $ids))));
    }

    private function normalizePreferredFormasiNames(Request $request, $options): array
    {
        $names = $request->input('preferred_formasi_names', []);
        if (!is_array($names)) {
            $names = [];
        }

        $validNames = collect($options)->pluck('name')->map(fn ($name) => trim((string) $name))->all();
        return array_values(array_unique(array_filter(array_map(
            fn ($name) => in_array(trim((string) $name), $validNames, true) ? trim((string) $name) : '',
            $names
        ))));
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();
        if ($user && method_exists($user, 'isActive') && !$user->isActive()) {
            $message = method_exists($user, 'activationStatus') && $user->activationStatus() === 'pending'
                ? 'Akunmu masih menunggu persetujuan master admin.'
                : 'Akun Anda sedang dinonaktifkan oleh admin.';
            return back()->withErrors([
                'email' => $message,
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($request->user() && method_exists($request->user(), 'isActive') && !$request->user()->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = method_exists($request->user(), 'activationStatus') && $request->user()->activationStatus() === 'pending'
                    ? 'Akunmu masih menunggu persetujuan master admin.'
                    : 'Akun Anda sedang dinonaktifkan oleh admin.';

                return back()->withErrors([
                    'email' => $message,
                ])->onlyInput('email');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Informasi login yang diberikan tidak cocok dengan catatan kami.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register', [
            'examModes' => ExamStructureSupport::modes(),
            'subCategories' => $this->preferenceFormasiOptions(),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'instansi' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:20'],
            'preferred_exam_mode_id' => ['nullable', 'integer'],
            'preferred_sub_category_id' => ['nullable', 'integer'],
            'preferred_exam_mode_ids' => ['nullable', 'array'],
            'preferred_exam_mode_ids.*' => ['integer'],
            'preferred_formasi_names' => ['nullable', 'array'],
            'preferred_formasi_names.*' => ['string'],
        ]);

        $formasiOptions = $this->preferenceFormasiOptions();
        $preferredExamModeIds = $this->normalizePreferredModeIds($request);
        $preferredFormasiNames = $this->normalizePreferredFormasiNames($request, $formasiOptions);

        $preferredExamModeId = null;
        $preferredSubCategoryId = null;
        if (Schema::hasColumn('users', 'preferred_exam_mode_id')) {
            $preferredExamModeId = !empty($preferredExamModeIds) ? (int) $preferredExamModeIds[0] : null;
        }
        if (Schema::hasColumn('users', 'preferred_sub_category_id')) {
            $preferredSubCategoryId = $formasiOptions
                ->first(fn ($sub) => !empty($preferredFormasiNames) && trim((string) $sub->name) === $preferredFormasiNames[0])?->id;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'instansi' => $request->instansi,
            'no_hp' => $request->no_hp,
            'is_active' => Schema::hasColumn('users', 'is_active') ? false : null,
            'preferred_exam_mode_id' => $preferredExamModeId,
            'preferred_sub_category_id' => $preferredSubCategoryId,
            'preferred_exam_mode_ids' => Schema::hasColumn('users', 'preferred_exam_mode_ids') ? $preferredExamModeIds : null,
            'preferred_formasi_names' => Schema::hasColumn('users', 'preferred_formasi_names') ? $preferredFormasiNames : null,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran berhasil. Akunmu sedang menunggu persetujuan master admin sebelum bisa login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showProfile()
    {
        $user = Auth::user();
        return view('auth.profile', [
            'user' => $user,
            'examModes' => ExamStructureSupport::modes(),
            'subCategories' => $this->preferenceFormasiOptions(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'instansi' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:20'],
            'theme' => ['nullable', 'string', \Illuminate\Validation\Rule::in(ThemeManifest::slugs())],
            'preferred_exam_mode_id' => ['nullable', 'integer'],
            'preferred_sub_category_id' => ['nullable', 'integer'],
            'preferred_exam_mode_ids' => ['nullable', 'array'],
            'preferred_exam_mode_ids.*' => ['integer'],
            'preferred_formasi_names' => ['nullable', 'array'],
            'preferred_formasi_names.*' => ['string'],
        ]);

        $formasiOptions = $this->preferenceFormasiOptions();
        $preferredExamModeIds = $this->normalizePreferredModeIds($request);
        $preferredFormasiNames = $this->normalizePreferredFormasiNames($request, $formasiOptions);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->instansi = $request->instansi;
        $user->no_hp = $request->no_hp;
        if (Schema::hasColumn('users', 'theme')) {
            $user->theme = $request->filled('theme') ? ThemeManifest::normalizeSlug((string) $request->theme) : null;
        }
        if (Schema::hasColumn('users', 'preferred_exam_mode_id')) {
            $user->preferred_exam_mode_id = !empty($preferredExamModeIds) ? (int) $preferredExamModeIds[0] : null;
        }
        if (Schema::hasColumn('users', 'preferred_sub_category_id')) {
            $user->preferred_sub_category_id = $formasiOptions
                ->first(fn ($sub) => !empty($preferredFormasiNames) && trim((string) $sub->name) === $preferredFormasiNames[0])?->id;
        }
        if (Schema::hasColumn('users', 'preferred_exam_mode_ids')) {
            $user->preferred_exam_mode_ids = $preferredExamModeIds;
        }
        if (Schema::hasColumn('users', 'preferred_formasi_names')) {
            $user->preferred_formasi_names = $preferredFormasiNames;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->back()->with('success', 'Password berhasil diperbarui!');
    }
}
