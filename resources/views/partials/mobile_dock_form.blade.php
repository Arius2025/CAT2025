@php
    $slots = array_values($slots ?? []);
    while (count($slots) < 3) {
        $slots[] = null;
    }

    $homeHref = $homeHref ?? route('ujian.index');
    $homeLabel = $homeLabel ?? 'Utama';
    $homeIcon = $homeIcon ?? 'fa-house';
    $homeActive = $homeActive ?? request()->routeIs('ujian.index');
    $menuLabel = $menuLabel ?? 'Menu';
    $menuIcon = $menuIcon ?? 'fa-bars-staggered';

    $renderAttrs = function (array $attrs = []) {
        return collect($attrs)->map(function ($value, $key) {
            if (is_bool($value)) {
                return $value ? $key : '';
            }
            return $key . '="' . e((string) $value) . '"';
        })->filter()->implode(' ');
    };
@endphp

<div class="mobile-dock" role="navigation" aria-label="Navigasi cepat">
    <div class="dock-indicator" aria-hidden="true"></div>
    <div class="dock-row">
        <div class="dock-slot">
            <a class="dock-item {{ $homeActive ? 'active' : '' }}" data-dock="home" href="{{ $homeHref }}" aria-label="{{ $homeLabel }}" title="{{ $homeLabel }}">
                <div class="dock-stack text-center">
                    <span class="dock-icon-wrap"><i class="fas {{ $homeIcon }}"></i></span>
                    <div class="dock-label">{{ $homeLabel }}</div>
                </div>
            </a>
        </div>

        @foreach($slots as $index => $slot)
            @php
                $slot = is_array($slot) ? $slot : [];
                $slotLabel = $slot['label'] ?? '';
                $slotIcon = $slot['icon'] ?? 'fa-circle';
                $slotHref = $slot['href'] ?? null;
                $slotId = $slot['id'] ?? null;
                $slotType = $slot['type'] ?? ($slotHref ? 'link' : 'button');
                $slotStyle = $slot['style'] ?? (($index === 1) ? 'cta' : 'item');
                $slotActive = (bool) ($slot['active'] ?? false);
                $slotAria = $slot['aria'] ?? $slotLabel;
                $slotForm = $slot['form'] ?? null;
                $slotButtonType = $slotForm ? 'submit' : ($slot['button_type'] ?? 'button');
                $slotAttrs = is_array($slot['attrs'] ?? null) ? $slot['attrs'] : [];
                $slotDotId = $slot['dot_id'] ?? null;
                $slotClass = trim((string) ($slot['class'] ?? ''));
                $slotIconClass = 'fas ' . $slotIcon;
                $isCta = $slotStyle === 'cta';
                $wrapperClass = $isCta ? 'dock-cta border-0' : 'dock-item border-0 bg-transparent';
                if (!$isCta && $slotActive) {
                    $wrapperClass .= ' active';
                }
                if ($slotClass !== '') {
                    $wrapperClass .= ' ' . $slotClass;
                }
            @endphp

            <div class="dock-slot">
                @if($slotType === 'link')
                    <a class="{{ $wrapperClass }}" href="{{ $slotHref ?: '#' }}" aria-label="{{ $slotAria }}" title="{{ $slotAria }}" {!! $renderAttrs($slotAttrs) !!}>
                        <div class="dock-stack text-center position-relative">
                            @if($slotDotId)
                                <span class="dock-dot d-none" id="{{ $slotDotId }}"></span>
                            @endif
                            <span class="dock-icon-wrap"><i class="{{ $slotIconClass }}"></i></span>
                            <div class="dock-label {{ $isCta ? 'dock-label-cta' : '' }}">{{ $slotLabel }}</div>
                        </div>
                    </a>
                @else
                    <button
                        type="{{ $slotButtonType }}"
                        @if($slotForm) form="{{ $slotForm }}" @endif
                        @if($slotId) id="{{ $slotId }}" @endif
                        class="{{ $wrapperClass }}"
                        aria-label="{{ $slotAria }}"
                        title="{{ $slotAria }}"
                        style="color: inherit;"
                        {!! $renderAttrs($slotAttrs) !!}>
                        <div class="dock-stack text-center position-relative">
                            @if($slotDotId)
                                <span class="dock-dot d-none" id="{{ $slotDotId }}"></span>
                            @endif
                            <span class="dock-icon-wrap"><i class="{{ $slotIconClass }}"></i></span>
                            <div class="dock-label {{ $isCta ? 'dock-label-cta' : '' }}">{{ $slotLabel }}</div>
                        </div>
                    </button>
                @endif
            </div>
        @endforeach

        <div class="dock-slot">
            <button class="dock-item border-0 bg-transparent" type="button"
                    data-dock="menu"
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" style="color: inherit;" aria-label="{{ $menuLabel }}" title="{{ $menuLabel }}">
                <div class="dock-stack text-center">
                    <span class="dock-icon-wrap"><i class="fas {{ $menuIcon }}"></i></span>
                    <div class="dock-label">{{ $menuLabel }}</div>
                </div>
            </button>
        </div>
    </div>
</div>
