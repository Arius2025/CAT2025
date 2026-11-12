<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.app_head_assets')
    @include('layouts.partials.app_theme_styles')
</head>
<body x-data="catThemeApp()" x-init="init()" data-theme="{{ $activeThemeSlug ?? 'dark-pro' }}" data-bs-theme="{{ ($themeRegistry[$activeThemeSlug]['dark'] ?? true) ? 'dark' : 'light' }}" class="{{ View::hasSection('examMode') ? 'is-exam' : '' }}">
    @include('partials.splash_loader')
    <div class="cat-progress-track" id="globalPageLoader" aria-hidden="true">
        <div class="cat-progress-indicator"></div>
    </div>
    
    @php
        $examMode = View::hasSection('examMode');
        $layoutUser = auth()->user();
        $isMasterAdmin = $layoutUser && method_exists($layoutUser, 'isMasterAdmin') ? $layoutUser->isMasterAdmin() : false;
        $layoutPendingUserCount = 0;
        if ($isMasterAdmin && class_exists(\App\Models\User::class) && \Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
            $layoutPendingUserCount = \App\Models\User::query()
                ->where('is_active', false)
                ->when(\Illuminate\Support\Facades\Schema::hasColumn('users', 'activated_at'), fn ($q) => $q->whereNull('activated_at'))
                ->count();
        }

        $sharedMenuLinks = collect([
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'fa-house',
                'href' => route('ujian.index'),
                'active' => request()->routeIs('ujian.index'),
                'visible' => true,
            ],
            [
                'key' => 'analysis',
                'label' => 'Analisis Belajar',
                'icon' => 'fa-chart-pie',
                'href' => route('analysis.index'),
                'active' => request()->routeIs('analysis.*'),
                'visible' => (bool) $layoutUser,
            ],
            [
                'key' => 'coach',
                'label' => 'Mentor Pintar',
                'icon' => 'fa-user-graduate',
                'href' => route('coach.index'),
                'active' => request()->routeIs('coach.*'),
                'visible' => (bool) $layoutUser,
            ],
            [
                'key' => 'ranking',
                'label' => 'Ranking',
                'icon' => 'fa-ranking-star',
                'href' => route('ujian.dashboard'),
                'active' => request()->routeIs('ujian.dashboard'),
                'visible' => !$layoutUser || $layoutUser->menuAllowed('ranking'),
            ],
            [
                'key' => 'leaderboard',
                'label' => 'Klasemen',
                'icon' => 'fa-trophy',
                'href' => route('ujian.leaderboard'),
                'active' => request()->routeIs('ujian.leaderboard'),
                'visible' => !$layoutUser || $layoutUser->menuAllowed('leaderboard'),
                'highlight' => true,
            ],
            [
                'key' => 'guide',
                'label' => 'Panduan',
                'icon' => 'fa-circle-info',
                'href' => route('guide.index'),
                'active' => request()->routeIs('guide.*'),
                'visible' => !$layoutUser || $layoutUser->menuAllowed('guide'),
            ],
            [
                'key' => 'bookmark',
                'label' => 'Favorit Soal',
                'icon' => 'fa-bookmark',
                'href' => route('bookmark.index'),
                'active' => request()->routeIs('bookmark.*'),
                'visible' => (bool) $layoutUser && $layoutUser->menuAllowed('bookmark'),
            ],
        ])->filter(fn ($item) => !empty($item['visible']))->values();

        $desktopPrimaryKeys = ['dashboard', 'analysis', 'coach', 'ranking', 'leaderboard'];
        $desktopPrimaryMenuLinks = $sharedMenuLinks
            ->filter(fn ($item) => in_array($item['key'], $desktopPrimaryKeys, true))
            ->values();
        $desktopOverflowMenuLinks = $sharedMenuLinks
            ->reject(fn ($item) => in_array($item['key'], $desktopPrimaryKeys, true))
            ->values();

        $adminMenuLinks = collect([
            [
                'key' => 'admin_audit_hub',
                'label' => 'Audit Soal AI',
                'icon' => 'fa-shield-halved',
                'href' => route('admin.audit-soal.index'),
                'active' => request()->routeIs('admin.audit-soal.*') || request()->routeIs('admin.question-quality.*'),
                'visible' => (bool) $layoutUser && ($layoutUser->menuAllowed('admin_audit') || $layoutUser->menuAllowed('admin_qc')),
            ],
            [
                'key' => 'admin_generate',
                'label' => 'Generate Soal',
                'icon' => 'fa-wand-magic-sparkles',
                'href' => route('admin.soal.index'),
                'active' => request()->routeIs('admin.soal.index'),
                'visible' => (bool) $layoutUser && $layoutUser->menuAllowed('admin_generate'),
            ],
            [
                'key' => 'admin_generate_bulk',
                'label' => 'Generate Massal',
                'icon' => 'fa-layer-group',
                'href' => route('admin.soal.bulk.index'),
                'active' => request()->routeIs('admin.soal.bulk.*'),
                'visible' => (bool) $layoutUser && $layoutUser->menuAllowed('admin_generate'),
            ],
            [
                'key' => 'admin_bank',
                'label' => 'Bank Soal',
                'icon' => 'fa-book',
                'href' => route('admin.soal-bank.index'),
                'active' => request()->routeIs('admin.soal-bank.*'),
                'visible' => (bool) $layoutUser && $layoutUser->menuAllowed('admin_bank'),
            ],
            [
                'key' => 'admin_figural',
                'label' => 'Soal Figural',
                'icon' => 'fa-image',
                'href' => route('admin.soal-figural.index'),
                'active' => request()->routeIs('admin.soal-figural.*'),
                'visible' => (bool) $layoutUser && $layoutUser->menuAllowed('admin_figural'),
            ],
            [
                'key' => 'admin_structure',
                'label' => 'Struktur Ujian',
                'icon' => 'fa-sitemap',
                'href' => route('admin.exam-structure.index'),
                'active' => request()->routeIs('admin.exam-structure.*'),
                'visible' => (bool) $layoutUser && $layoutUser->menuAllowed('admin_structure'),
            ],
            [
                'key' => 'admin_api_tokens',
                'label' => 'API Token',
                'icon' => 'fa-key',
                'href' => route('admin.api-tokens.index'),
                'active' => request()->routeIs('admin.api-tokens.*'),
                'visible' => (bool) $layoutUser && $layoutUser->menuAllowed('admin_api_tokens'),
            ],
            [
                'key' => 'admin_user_access',
                'label' => 'Akses User',
                'icon' => 'fa-users-gear',
                'href' => route('admin.user-access.index'),
                'active' => request()->routeIs('admin.user-access.*'),
                'visible' => $isMasterAdmin,
                'badge' => $layoutPendingUserCount,
            ],
        ])->filter(fn ($item) => !empty($item['visible']))->values();
    @endphp

    @include('partials.navbar_desktop')

    @include('layouts.partials.app_mobile_offcanvas')

    <main class="{{ $examMode ? 'page-fade' : 'container page-fade pb-5 mb-5 mb-lg-0' }}">
        @yield('content')
    </main>

    @include('layouts.partials.app_mobile_dock')
    @include('layouts.partials.app_queue_center')
    @include('layouts.partials.app_quick_start')

@include('layouts.partials.app_scripts')

    @include('layouts.partials.app_bottom_scripts')
</body>
</html>

