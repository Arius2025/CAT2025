<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\User;
use App\Support\ExamStructureSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class UserAccessController extends Controller
{
    private array $menuOptions = [
        'dashboard' => 'Dashboard',
        'analysis' => 'Analisis Belajar',
        'guide' => 'Panduan',
        'ranking' => 'Ranking',
        'leaderboard' => 'Klasemen',
        'bookmark' => 'Favorit',
        'admin_generate' => 'Generate Soal',
        'admin_audit' => 'Audit Soal',
        'admin_qc' => 'Audit Kualitas Soal',
        'admin_bank' => 'Bank Soal',
        'admin_figural' => 'Figural',
        'admin_structure' => 'Struktur Ujian',
        'admin_api_tokens' => 'API Token',
    ];

    private function authorizeMaster(): void
    {
        abort_unless(Auth::user()?->isMasterAdmin(), 403);
    }

    public function index()
    {
        $this->authorizeMaster();

        $statusFilter = strtolower(trim((string) request()->query('status', 'all')));
        if (!in_array($statusFilter, ['all', 'pending', 'active', 'inactive'], true)) {
            $statusFilter = 'all';
        }
        $search = trim((string) request()->query('q', ''));

        $subCategories = Schema::hasTable('sub_categories')
            ? SubCategory::query()
                ->when(Schema::hasColumn('sub_categories', 'kind'), fn ($q) => $q->where('kind', 'formasi'))
                ->orderBy('name')
                ->get(['id', 'name', 'exam_mode_id'])
                ->reject(fn ($sub) => in_array(strtoupper(trim((string) $sub->name)), ['TWK', 'TIU', 'TKP'], true))
                ->groupBy(fn ($sub) => mb_strtolower(trim((string) $sub->name)))
                ->map(function ($group) {
                    $first = $group->first();
                    $first->exam_mode_ids = $group->pluck('exam_mode_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
                    return $first;
                })
                ->sortBy('name')
                ->values()
            : collect();

        $users = User::query()
            ->with(['preferredExamMode', 'preferredSubCategory'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->get();

        $users = $users->filter(function ($user) use ($statusFilter) {
            if ($statusFilter === 'all') {
                return true;
            }

            return $user->activationStatus() === $statusFilter;
        })->values();

        $statusStats = [
            'all' => User::query()->count(),
            'active' => User::query()
                ->when(Schema::hasColumn('users', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->count(),
            'pending' => Schema::hasColumn('users', 'is_active')
                ? User::query()
                    ->where('is_active', false)
                    ->when(Schema::hasColumn('users', 'activated_at'), fn ($q) => $q->whereNull('activated_at'))
                    ->count()
                : 0,
            'inactive' => Schema::hasColumn('users', 'is_active')
                ? User::query()
                    ->where('is_active', false)
                    ->when(Schema::hasColumn('users', 'activated_at'), fn ($q) => $q->whereNotNull('activated_at'))
                    ->count()
                : 0,
        ];

        return view('admin.user_access.index', [
            'users' => $users,
            'examModes' => ExamStructureSupport::modes(),
            'subCategories' => $subCategories,
            'menuOptions' => $this->menuOptions,
            'statusFilter' => $statusFilter,
            'statusStats' => $statusStats,
            'search' => $search,
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $this->authorizeMaster();

        $validated = $request->validate([
            'selected_user_ids' => 'required|array|min:1',
            'selected_user_ids.*' => 'integer',
            'bulk_action' => 'required|in:approve,deactivate',
        ]);

        $ids = collect($validated['selected_user_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->withErrors([
                'user_access' => 'Pilih minimal satu user dulu untuk menjalankan aksi massal.',
            ]);
        }

        $users = User::query()->whereIn('id', $ids)->get();
        $affected = 0;

        foreach ($users as $user) {
            if ($validated['bulk_action'] === 'approve') {
                if (Schema::hasColumn('users', 'is_active')) {
                    $user->is_active = true;
                }
                if (Schema::hasColumn('users', 'activated_at') && empty($user->activated_at)) {
                    $user->activated_at = now();
                }
                $user->save();
                $affected++;
                continue;
            }

            if ((int) $user->id === (int) Auth::id()) {
                continue;
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $user->is_active = false;
                if (Schema::hasColumn('users', 'activated_at') && empty($user->activated_at)) {
                    $user->activated_at = now();
                }
                $user->save();
                $affected++;
            }
        }

        if ($validated['bulk_action'] === 'deactivate' && $affected === 0) {
            return back()->withErrors([
                'user_access' => 'Tidak ada user yang bisa dinonaktifkan. Master admin yang sedang login dilewati demi keamanan.',
            ]);
        }

        $message = $validated['bulk_action'] === 'approve'
            ? "Berhasil menyetujui {$affected} akun."
            : "Berhasil menonaktifkan {$affected} akun.";

        return back()->with('success', $message);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeMaster();

        $user = User::query()->findOrFail($id);

        $validated = $request->validate([
            'is_master_admin' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'preferred_exam_mode_id' => 'nullable|integer',
            'preferred_sub_category_id' => 'nullable|integer',
            'preferred_exam_mode_ids' => 'nullable|array',
            'preferred_exam_mode_ids.*' => 'integer',
            'preferred_formasi_names' => 'nullable|array',
            'preferred_formasi_names.*' => 'string',
            'visible_menu_keys' => 'nullable|array',
            'visible_menu_keys.*' => 'string',
        ]);

        $preferredExamModeIds = array_values(array_unique(array_filter(array_map(
            fn ($id) => (int) $id,
            array_merge($validated['preferred_exam_mode_ids'] ?? [], !empty($validated['preferred_exam_mode_id']) ? [$validated['preferred_exam_mode_id']] : [])
        ))));
        $preferredFormasiNames = array_values(array_unique(array_filter(array_map(
            fn ($name) => trim((string) $name),
            $validated['preferred_formasi_names'] ?? []
        ))));

        if ($request->boolean('quick_approve')) {
            if (Schema::hasColumn('users', 'is_active')) {
                $user->is_active = true;
            }
            if (Schema::hasColumn('users', 'activated_at') && empty($user->activated_at)) {
                $user->activated_at = now();
            }
            $user->save();

            return back()->with('success', 'Akun berhasil disetujui dan diaktifkan.');
        }

        if (Schema::hasColumn('users', 'is_master_admin')) {
            $user->is_master_admin = (bool) ($validated['is_master_admin'] ?? false);
        }
        if (Schema::hasColumn('users', 'is_active')) {
            $isActive = (bool) ($validated['is_active'] ?? false);

            if ((int) $user->id === (int) Auth::id() && !$isActive) {
                return back()->withErrors([
                    'user_access' => 'Master admin yang sedang login tidak bisa menonaktifkan dirinya sendiri.',
                ]);
            }

            $user->is_active = $isActive;
            if ($isActive && Schema::hasColumn('users', 'activated_at') && empty($user->activated_at)) {
                $user->activated_at = now();
            }
        }
        if (Schema::hasColumn('users', 'preferred_exam_mode_id')) {
            $user->preferred_exam_mode_id = !empty($preferredExamModeIds) ? (int) $preferredExamModeIds[0] : null;
        }
        if (Schema::hasColumn('users', 'preferred_sub_category_id')) {
            $matchedSub = !empty($preferredFormasiNames)
                ? SubCategory::query()
                    ->when(Schema::hasColumn('sub_categories', 'kind'), fn ($q) => $q->where('kind', 'formasi'))
                    ->where('name', $preferredFormasiNames[0])
                    ->first()
                : null;
            $user->preferred_sub_category_id = $matchedSub?->id;
        }
        if (Schema::hasColumn('users', 'preferred_exam_mode_ids')) {
            $user->preferred_exam_mode_ids = $preferredExamModeIds;
        }
        if (Schema::hasColumn('users', 'preferred_formasi_names')) {
            $user->preferred_formasi_names = $preferredFormasiNames;
        }
        if (Schema::hasColumn('users', 'visible_menu_keys')) {
            $user->visible_menu_keys = array_values(array_intersect(
                array_keys($this->menuOptions),
                $validated['visible_menu_keys'] ?? []
            ));
        }

        $user->save();

        return back()->with('success', 'Akses user berhasil diperbarui.');
    }
}
