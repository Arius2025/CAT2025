@unless($examMode)
<div class="offcanvas offcanvas-bottom border-0 mobile-sheet" tabindex="-1" id="offcanvasNavbar" style="height: min(78vh, 680px);">
    <div class="offcanvas-header border-0 pb-0">
        <div class="w-100 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="sheet-grabber"></div>
                <div class="ms-2 fw-800" style="color: var(--text-primary);">Menu</div>
            </div>
            <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm rounded-3"
                    style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-glass); color: var(--text-primary);"
                    @click="toggleTheme()">
                <i class="fas" :class="themeIcon()"></i>
            </button>
            <button type="button" class="btn-close" :class="isDarkTheme() ? 'btn-close-white' : ''" data-bs-dismiss="offcanvas"></button>
            </div>
        </div>
    </div>
    <div class="offcanvas-body d-flex flex-column pt-3">
        @auth
            <div class="p-3 rounded-4 mb-3" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-glass);">
                <div class="d-flex align-items-center gap-3">
                    <div class="user-avatar" style="margin-left: 0;">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    <div>
                        <div class="fw-800" style="color: var(--text-primary); line-height: 1.1;">{{ Auth::user()->name }}</div>
                        <div class="small" style="color: var(--text-secondary);">Akun Anda</div>
                    </div>
                </div>
            </div>
        @endauth

        <div class="small fw-800 mb-2" style="color: var(--text-secondary); letter-spacing: 0.16em;">MENU</div>
        <div class="vstack gap-2">
            @auth
                <a class="btn w-100 text-start rounded-4 py-3"
                   href="{{ route('profile') }}"
                   style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-glass); color: var(--text-primary);">
                    <i class="fas fa-user-gear me-2 opacity-75"></i> Profil
                </a>
                @foreach($sharedMenuLinks as $item)
                    <a class="btn w-100 text-start rounded-4 py-3 mt-2 {{ !empty($item['highlight']) ? 'fw-bold text-warning' : '' }}"
                       href="{{ $item['href'] }}"
                       style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-glass); {{ empty($item['highlight']) ? 'color: var(--text-primary);' : '' }}">
                        <i class="fas {{ $item['icon'] }} me-2 {{ !empty($item['highlight']) ? '' : 'opacity-75' }}"></i> {{ !empty($item['highlight']) ? 'Klasemen Global' : $item['label'] }}
                    </a>
                @endforeach

                @foreach($adminMenuLinks as $item)
                    <a class="btn w-100 text-start rounded-4 py-3 mt-2"
                       href="{{ $item['href'] }}"
                       style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-glass); color: var(--text-primary);">
                        <i class="fas {{ $item['icon'] }} me-2 opacity-75"></i> {{ $item['label'] }}
                        @if(!empty($item['badge']))
                            <span class="apple-pill ms-2" style="background: rgba(245,158,11,0.16); border-color: rgba(245,158,11,0.28); color: inherit;">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach

                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button class="btn w-100 text-start rounded-4 py-3"
                            type="submit"
                            style="background: rgba(239,68,68,0.10); border: 1px solid rgba(239,68,68,0.25); color: var(--text-primary);">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </button>
                </form>
            @endauth

            @guest
                <a class="btn w-100 text-start rounded-4 py-3"
                   href="{{ route('login') }}"
                   style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-glass); color: var(--text-primary);">
                    <i class="fas fa-right-to-bracket me-2 opacity-75"></i> Login
                </a>
                <a class="btn w-100 text-start rounded-4 py-3"
                   href="{{ route('register') }}"
                   style="border: 1px solid var(--border-glass); color: var(--text-primary); background: rgba(255,255,255,0.04);">
                    <i class="fas fa-user-plus me-2 opacity-75"></i> Daftar
                </a>
            @endguest

            <div class="mt-3 pt-3 border-top border-secondary border-opacity-10 text-center">
                <div class="small fw-800" style="color: var(--text-secondary); letter-spacing: 0.04em;">
                    CAT ASN • Versi 3.2.0
                </div>
                <div class="opacity-50" style="color: var(--text-secondary); font-size: 0.72rem;">
                    Platform Belajar Digital KORPRI • Standar BKN
                </div>
            </div>
        </div>
    </div>
</div>
@endunless
