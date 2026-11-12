<?php

namespace App\Providers;

use App\Support\ExamDashboardSupport;
use App\Support\ThemeManifest;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.apple');

        View::composer(['layouts.app', 'auth.profile'], function ($view) {
            $user = Auth::user();
            $themes = ThemeManifest::all();
            $activeThemeSlug = ThemeManifest::normalizeSlug($user?->theme ?? null);

            $view->with([
                'themeRegistry' => $themes,
                'activeThemeSlug' => $activeThemeSlug,
                'themeEngineCss' => asset('themes/engine.css'),
            ]);

            if ($user) {
                $view->with(ExamDashboardSupport::startExamData($user));
            }
        });
    }
}
