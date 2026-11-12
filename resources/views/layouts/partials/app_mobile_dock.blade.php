@unless($examMode)
<div class="d-lg-none mobile-dock-wrap">
    @if(View::hasSection('mobileDock'))
        <div data-dock-override>
            @yield('mobileDock')
        </div>
    @else
        @include('partials.mobile_dock_form', [
            'slots' => auth()->check()
        ? [
                    [
                        'label' => 'Analisis',
                        'icon' => 'fa-chart-pie',
                        'href' => route('analysis.index'),
                        'active' => request()->routeIs('analysis.*'),
                    ],
                    [
                        'label' => 'Mulai',
                        'icon' => 'fa-play',
                        'id' => 'dockQuickStartBtn',
                        'style' => 'cta',
                        'aria' => 'Mulai Ujian',
                    ],
                    [
                        'label' => 'Favorit',
                        'icon' => 'fa-bookmark',
                        'href' => route('bookmark.index'),
                        'active' => request()->routeIs('bookmark.*'),
                    ],
                ]
                : [
                    [
                        'label' => 'Ranking',
                        'icon' => 'fa-trophy',
                        'href' => route('login'),
                    ],
                    [
                        'label' => 'Masuk',
                        'icon' => 'fa-right-to-bracket',
                        'href' => route('login'),
                        'style' => 'cta',
                        'aria' => 'Masuk',
                    ],
                    [
                        'label' => 'Daftar',
                        'icon' => 'fa-user-plus',
                        'href' => route('register'),
                    ],
                ],
        ])
    @endif
</div>
@endunless
