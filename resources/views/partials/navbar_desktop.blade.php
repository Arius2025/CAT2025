@unless($examMode)
@php
    $primaryNavLinks = collect([
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'href' => route('ujian.index'),
            'active' => request()->routeIs('ujian.index'),
        ],
        [
            'key' => 'ranking',
            'label' => 'Ranking',
            'icon' => 'fa-ranking-star',
            'href' => route('ujian.dashboard'),
            'active' => request()->routeIs('ujian.dashboard'),
        ],
        [
            'key' => 'leaderboard',
            'label' => 'Klasemen',
            'icon' => 'fa-trophy',
            'href' => route('ujian.leaderboard'),
            'active' => request()->routeIs('ujian.leaderboard'),
        ],
        [
            'key' => 'analysis',
            'label' => 'Analisis',
            'icon' => 'fa-chart-pie',
            'href' => route('analysis.index'),
            'active' => request()->routeIs('analysis.*'),
        ],
    ]);

    $belajarMenuLinks = collect([
        [
            'key' => 'coach',
            'label' => 'Mentor Pintar',
            'icon' => 'fa-user-graduate',
            'href' => route('coach.index'),
            'active' => request()->routeIs('coach.*'),
        ],
        [
            'key' => 'bookmark',
            'label' => 'Favorit Soal',
            'icon' => 'fa-bookmark',
            'href' => route('bookmark.index'),
            'active' => request()->routeIs('bookmark.*'),
        ],
        [
            'key' => 'guide',
            'label' => 'Panduan Ujian',
            'icon' => 'fa-circle-info',
            'href' => route('guide.index'),
            'active' => request()->routeIs('guide.*'),
        ],
    ]);

    $isBelajarActive = $belajarMenuLinks->contains(fn ($item) => !empty($item['active']));
@endphp
<nav class="desktop-topbar d-none d-lg-block">
    <div class="desktop-topbar__inner container-fluid">
        <div class="desktop-topbar__brand">
            <a class="desktop-topbar__brand-link" href="{{ route('ujian.index') }}" title="CAT ASN Beranda">
                <span class="desktop-topbar__brand-mark">
                    <img src="/icons/cat-logo.png?v=20261008-3" alt="CAT ASN Logo" class="desktop-brand-img">
                </span>
            </a>
        </div>

        <div class="desktop-topbar__center">
            <div class="desktop-topbar__menu-shell">
                <ul class="desktop-topbar__menu list-unstyled mb-0">
                    @foreach($primaryNavLinks as $item)
                        <li>
                            <a class="desktop-topbar__menu-link {{ !empty($item['active']) ? 'is-active' : '' }}" href="{{ $item['href'] }}">
                                <i class="fas {{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    <li class="dropdown">
                        <button class="desktop-topbar__menu-link desktop-topbar__menu-link--button {{ $isBelajarActive ? 'is-active' : '' }}"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-brain"></i>
                            <span>Belajar</span>
                            <i class="fas fa-chevron-down desktop-topbar__caret"></i>
                        </button>
                        <ul class="dropdown-menu desktop-dropdown-menu desktop-topbar__dropdown p-2">
                            @foreach($belajarMenuLinks as $item)
                                <li>
                                    <a class="dropdown-item py-2 rounded-3 {{ !empty($item['active']) ? 'is-active' : '' }}" href="{{ $item['href'] }}">
                                        <i class="fas {{ $item['icon'] }} me-2 opacity-75"></i>{{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>

                    @if(!empty($adminMenuLinks) && $adminMenuLinks->isNotEmpty())
                        <li class="dropdown">
                            <button class="desktop-topbar__menu-link desktop-topbar__menu-link--button {{ $adminMenuLinks->contains(fn ($item) => !empty($item['active'])) ? 'is-active' : '' }}"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-shield-halved"></i>
                                <span>Admin</span>
                                @if(!empty($layoutPendingUserCount) && $layoutPendingUserCount > 0)
                                    <span class="apple-pill ms-1 warning">{{ $layoutPendingUserCount }}</span>
                                @endif
                                <i class="fas fa-chevron-down desktop-topbar__caret"></i>
                            </button>
                            <ul class="dropdown-menu desktop-dropdown-menu desktop-topbar__dropdown p-2">
                                @foreach($adminMenuLinks as $item)
                                    <li>
                                        <a class="dropdown-item py-2 rounded-3 {{ !empty($item['active']) ? 'is-active' : '' }}" href="{{ $item['href'] }}">
                                            <i class="fas {{ $item['icon'] }} me-2 opacity-75"></i>{{ $item['label'] }}
                                            @if(!empty($item['badge']))
                                                <span class="apple-pill ms-2 warning">{{ $item['badge'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="desktop-topbar__actions">
            <button type="button" class="desktop-topbar__icon-btn border-0" data-bs-toggle="modal" data-bs-target="#desktopSearchModal" title="Cari cepat (Ctrl+K)">
                <i class="fas fa-magnifying-glass"></i>
                <span>Cari</span>
            </button>

            <button type="button" class="desktop-topbar__theme-btn border-0" @click="toggleTheme()" title="Ganti tema">
                <i class="fas" :class="themeIcon()"></i>
            </button>

            @guest
                <div class="desktop-topbar__guest">
                    <a href="{{ route('login') }}" class="desktop-topbar__text-link">Login</a>
                    <a href="{{ route('register') }}" class="apple-glass-btn apple-btn-primary px-3 py-2">Daftar</a>
                </div>
            @else
                <div class="dropdown desktop-topbar__user">
                    <button class="desktop-topbar__user-btn border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="desktop-topbar__user-meta d-none d-xl-flex">
                            <span class="desktop-topbar__user-label">Masuk sebagai</span>
                            <span class="desktop-topbar__user-name">{{ Auth::user()->name }}</span>
                        </span>
                        <span class="desktop-topbar__avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(Auth::user()->name, 0, 1)) }}</span>
                        <i class="fas fa-chevron-down desktop-topbar__caret"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end desktop-topbar__dropdown p-2">
                        <li>
                            <a class="dropdown-item py-2 rounded-3" href="{{ route('profile') }}">
                                <i class="fas fa-user-gear me-2 opacity-75"></i>Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item py-2 rounded-3 text-danger" type="submit">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endguest
        </div>
    </div>
</nav>



<div class="modal fade desktop-search-modal" id="desktopSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="apple-kicker">Quick Access</div>
                    <h5 class="modal-title fw-800">Command Center</h5>
                </div>
                <span class="apple-pill muted d-none d-md-inline-flex">Ctrl + K</span>
                <button type="button" class="btn-close" :class="isDarkTheme() ? 'btn-close-white' : ''" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @auth
                    <form action="{{ $layoutUser->menuAllowed('admin_bank') ? route('admin.soal-bank.index') : route('analysis.index') }}" method="GET" class="mb-4">
                        <label class="apple-label mb-2">Pencarian cepat</label>
                        <div class="d-flex gap-2">
                            <input
                                type="text"
                                name="{{ $layoutUser->menuAllowed('admin_bank') ? 'q' : 'focus' }}"
                                class="form-control apple-glass-input"
                                id="desktopCommandSearch"
                                placeholder="{{ $layoutUser->menuAllowed('admin_bank') ? 'Cari soal, pembahasan, atau ID...' : 'Cari fokus belajar...' }}">
                            <button type="submit" class="apple-glass-btn apple-btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>Buka
                            </button>
                        </div>
                        <div class="small apple-hint mt-2">Gunakan Ctrl + K untuk membuka Command Center lebih cepat.</div>
                    </form>
                @else
                    <div class="apple-glass-soft apple-glass-card p-3 mb-4">
                        <div class="fw-700 mb-1">Masuk untuk pencarian penuh</div>
                        <div class="apple-hint">Login dulu untuk mencari soal, analisis, dan menu admin secara cepat.</div>
                    </div>
                @endauth

                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 mt-4">
                    <div>
                        <div class="fw-800">Pintasan Cepat</div>
                        <div class="small apple-hint">Langsung lompat ke halaman yang paling sering dipakai.</div>
                    </div>
                    <span class="apple-pill muted">Desktop only</span>
                </div>

                <div class="quick-link-grid" id="desktopCommandGrid">
                    <a href="{{ route('ujian.index') }}" class="quick-link-card" data-command-item data-command-text="dashboard beranda utama home ujian">
                        <i class="fas fa-house"></i>
                        <div>
                            <div class="fw-700">Dashboard</div>
                            <div class="apple-hint">Beranda utama aplikasi</div>
                        </div>
                    </a>
                    @auth
                        <a href="{{ route('analysis.index') }}" class="quick-link-card" data-command-item data-command-text="analisis belajar progress insight">
                            <i class="fas fa-chart-pie"></i>
                            <div>
                                <div class="fw-700">Analisis Belajar</div>
                                <div class="apple-hint">Lihat progres dan insight</div>
                            </div>
                        </a>
                        <a href="{{ route('coach.index') }}" class="quick-link-card" data-command-item data-command-text="mentor pintar rekomendasi belajar personal">
                            <i class="fas fa-user-graduate"></i>
                            <div>
                                <div class="fw-700">Mentor Pintar</div>
                                <div class="apple-hint">Rekomendasi belajar personal</div>
                            </div>
                        </a>
                        @if($layoutUser->menuAllowed('admin_bank'))
                            <a href="{{ route('admin.soal-bank.index') }}" class="quick-link-card" data-command-item data-command-text="bank soal kelola cari soal admin">
                                <i class="fas fa-database"></i>
                                <div>
                                    <div class="fw-700">Bank Soal</div>
                                    <div class="apple-hint">Cari dan kelola soal</div>
                                </div>
                            </a>
                        @endif
                        @if($layoutUser->menuAllowed('admin_generate'))
                            <a href="{{ route('admin.soal.index') }}" class="quick-link-card" data-command-item data-command-text="generate soal admin buat soal">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                <div>
                                    <div class="fw-700">Generate Soal</div>
                                    <div class="apple-hint">Buat soal baru dengan AI</div>
                                </div>
                            </a>
                            <a href="{{ route('admin.soal.bulk.index') }}" class="quick-link-card" data-command-item data-command-text="generate soal massal gemini bulk">
                                <i class="fas fa-layer-group"></i>
                                <div>
                                    <div class="fw-700">Generate Massal</div>
                                    <div class="apple-hint">Prompt Gemini dan import JSON</div>
                                </div>
                            </a>
                        @endif
                        @if($layoutUser->menuAllowed('admin_audit') || $layoutUser->menuAllowed('admin_qc'))
                            <a href="{{ route('admin.audit-soal.index') }}" class="quick-link-card" data-command-item data-command-text="audit soal ai review gemini">
                                <i class="fas fa-shield-halved"></i>
                                <div>
                                    <div class="fw-700">Audit Soal AI</div>
                                    <div class="apple-hint">Export, import, dan review audit</div>
                                </div>
                            </a>
                        @endif
                    @endauth
                </div>

                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 mt-4">
                    <div>
                        <div class="fw-800">Terakhir Dibuka</div>
                        <div class="small apple-hint">Disimpan di perangkat ini untuk akses yang lebih cepat.</div>
                    </div>
                    <button type="button" class="apple-glass-btn apple-btn-outline px-3 py-2" id="desktopRecentClearBtn">
                        Bersihkan
                    </button>
                </div>

                <div class="quick-link-grid" id="desktopRecentGrid">
                    <div class="apple-glass-soft apple-glass-card p-3" id="desktopRecentEmpty">
                        <div class="fw-700 mb-1">Belum ada riwayat lokal</div>
                        <div class="apple-hint">Buka beberapa halaman dulu, nanti daftar ini muncul otomatis.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endunless
