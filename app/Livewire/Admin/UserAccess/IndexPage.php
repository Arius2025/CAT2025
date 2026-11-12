<?php

namespace App\Livewire\Admin\UserAccess;

use App\Models\SubCategory;
use App\Models\User;
use App\Support\ExamStructureSupport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class IndexPage extends Component
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
        'admin_bank' => 'Bank Soal',
        'admin_figural' => 'Figural',
        'admin_structure' => 'Struktur Ujian',
        'admin_api_tokens' => 'API Token',
    ];

    public function render()
    {
        abort_unless(Auth::user()?->isMasterAdmin(), 403);

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
            ->get()
            ->filter(function ($user) use ($statusFilter) {
                if ($statusFilter === 'all') {
                    return true;
                }

                return $user->activationStatus() === $statusFilter;
            })
            ->values();

        $statusStats = [
            'all' => User::query()->count(),
            'active' => User::query()->when(Schema::hasColumn('users', 'is_active'), fn ($q) => $q->where('is_active', true))->count(),
            'pending' => Schema::hasColumn('users', 'is_active')
                ? User::query()->where('is_active', false)->when(Schema::hasColumn('users', 'activated_at'), fn ($q) => $q->whereNull('activated_at'))->count()
                : 0,
            'inactive' => Schema::hasColumn('users', 'is_active')
                ? User::query()->where('is_active', false)->when(Schema::hasColumn('users', 'activated_at'), fn ($q) => $q->whereNotNull('activated_at'))->count()
                : 0,
        ];

        return view('livewire.admin.user-access.index-page', [
            'users' => $users,
            'examModes' => ExamStructureSupport::modes(),
            'subCategories' => $subCategories,
            'menuOptions' => $this->menuOptions,
            'statusFilter' => $statusFilter,
            'statusStats' => $statusStats,
            'search' => $search,
        ]);
    }
}
