    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.CATThemeRegistry = @json($themeRegistry ?? []);
        window.CATTheme = window.CATTheme || {
            themes: window.CATThemeRegistry,
            get allowedThemes() {
                return Object.keys(this.themes || {});
            },
            normalize(theme) {
                return this.allowedThemes.includes(theme) ? theme : {{ \Illuminate\Support\Js::from($activeThemeSlug ?? 'dark-pro') }};
            },
            bootstrapTheme(theme) {
                const safeTheme = this.normalize(theme);
                return this.themes?.[safeTheme]?.dark ? 'dark' : 'light';
            },
            loadAssets(theme) {
                const meta = this.themes?.[theme];
                if (!meta) return;

                const cssLink = document.getElementById('activeThemeCss');
                if (cssLink) {
                    cssLink.setAttribute('href', `${meta.assets.css}?v=${encodeURIComponent(meta.version || '1.0')}`);
                }

                const oldScript = document.getElementById('activeThemeJs');
                if (oldScript) oldScript.remove();

                const script = document.createElement('script');
                script.id = 'activeThemeJs';
                script.src = `${meta.assets.js}?v=${encodeURIComponent(meta.version || '1.0')}`;
                script.defer = true;
                script.onload = () => {
                    try {
                        if (window.CATThemeModules && typeof window.CATThemeModules[theme] === 'function') {
                            window.CATThemeModules[theme]();
                        }
                    } catch (error) {
                        console.warn('Theme hook gagal dijalankan.', error);
                    }
                };
                document.body.appendChild(script);
            },
            apply(theme) {
                const safeTheme = this.normalize(theme);
                const themeMeta = this.themes?.[safeTheme] || {};
                const body = document.body;
                if (!body) return safeTheme;
                body.setAttribute('data-theme', safeTheme);
                body.setAttribute('data-bs-theme', themeMeta.dark ? 'dark' : 'light');
                this.loadAssets(safeTheme);
                try { localStorage.setItem('cat_theme', safeTheme); } catch (e) {}
                const themeMetaTag = document.querySelector('meta[name="theme-color"]');
                if (themeMetaTag) themeMetaTag.setAttribute('content', this.themes?.[safeTheme]?.theme_color || '#0f1115');
                return safeTheme;
            }
        };
        document.addEventListener('DOMContentLoaded', function() {
            try {
                window.CATTheme.apply(localStorage.getItem('cat_theme') || document.body?.getAttribute('data-theme') || {{ \Illuminate\Support\Js::from($activeThemeSlug ?? 'dark-pro') }});
            } catch (e) {
                console.warn('Theme bootstrap fallback gagal dipakai.', e);
            }
        });
        window.catThemeApp = function() {
            return {
                theme: localStorage.getItem('cat_theme') || @json($activeThemeSlug ?? 'dark-pro'),
                allowedThemes: Object.keys(window.CATThemeRegistry || {}),
                init() {
                    if (!this.allowedThemes.includes(this.theme)) this.theme = {{ \Illuminate\Support\Js::from($activeThemeSlug ?? 'dark-pro') }};
                    if (window.CATTheme && typeof window.CATTheme.apply === 'function') {
                        this.theme = window.CATTheme.apply(this.theme);
                    } else {
                        this.applyThemeMeta();
                    }
                    window.addEventListener('cat-theme:set', (event) => this.setTheme(event.detail?.theme || {{ \Illuminate\Support\Js::from($activeThemeSlug ?? 'dark-pro') }}));
                },
                setTheme(nextTheme) {
                    this.theme = this.allowedThemes.includes(nextTheme) ? nextTheme : {{ \Illuminate\Support\Js::from($activeThemeSlug ?? 'dark-pro') }};
                    if (window.CATTheme && typeof window.CATTheme.apply === 'function') {
                        this.theme = window.CATTheme.apply(this.theme);
                    } else {
                        localStorage.setItem('cat_theme', this.theme);
                        this.applyThemeMeta();
                    }
                },
                bootstrapTheme() {
                    return window.CATTheme ? window.CATTheme.bootstrapTheme(this.theme) : 'dark';
                },
                isDarkTheme() {
                    return this.bootstrapTheme() === 'dark';
                },
                toggleTheme() {
                    const order = this.allowedThemes;
                    const currentIndex = Math.max(0, order.indexOf(this.theme));
                    const nextIndex = (currentIndex + 1) % Math.max(order.length, 1);
                    this.setTheme(order[nextIndex] || {{ \Illuminate\Support\Js::from($activeThemeSlug ?? 'dark-pro') }});
                },
                themeIcon() {
                    return this.isDarkTheme() ? 'fa-moon' : 'fa-sun';
                },
                applyThemeMeta() {
                    if (window.CATTheme && window.CATTheme.themes) {
                        const meta = document.querySelector('meta[name="theme-color"]');
                        if (meta) {
                            meta.setAttribute('content', window.CATTheme.themes?.[this.theme]?.theme_color || '#0f1115');
                        }
                        return;
                    }
                    const meta = document.querySelector('meta[name="theme-color"]');
                    if (meta) meta.setAttribute('content', '#0f1115');
                }
            };
        };
    </script>
    <script>
        (function () {
            const showLoader = () => {
                const loader = document.getElementById('globalPageLoader');
                if (!loader) return;
                const bar = loader.querySelector('.cat-progress-indicator');
                if (bar) bar.style.width = '75%';
                loader.classList.add('is-visible');
                loader.setAttribute('aria-hidden', 'false');
            };
            const hideLoader = () => {
                const loader = document.getElementById('globalPageLoader');
                if (!loader) return;
                const bar = loader.querySelector('.cat-progress-indicator');
                if (bar) bar.style.width = '100%';
                setTimeout(() => {
                    loader.classList.remove('is-visible');
                    loader.setAttribute('aria-hidden', 'true');
                    setTimeout(() => { if (bar) bar.style.width = '0%'; }, 220);
                }, 160);
            };
            window.addEventListener('load', hideLoader);
            window.addEventListener('pageshow', hideLoader);
            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.add('page-enter');

                document.querySelectorAll('a[href]').forEach((link) => {
                    link.addEventListener('click', (event) => {
                        if (event.defaultPrevented) return;
                        const href = link.getAttribute('href') || '';
                        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                        if (link.target === '_blank' || link.hasAttribute('download')) return;
                        const url = new URL(href, window.location.origin);
                        if (url.origin !== window.location.origin) return;
                        showLoader();
                    });
                });

                document.querySelectorAll('form').forEach((form) => {
                    form.addEventListener('submit', () => showLoader());
                });
            });
        })();

        (function() {
            // Stabilize bottom-fixed UI (dock/toast) on mobile browsers with dynamic toolbars.
            // Uses VisualViewport to compensate for URL bar show/hide jitter.
            const root = document.documentElement;
            function setVvBottom() {
                try {
                    const vv = window.visualViewport;
                    if (!vv) { root.style.setProperty('--vv-bottom', '0px'); return; }
                    const bottom = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
                    root.style.setProperty('--vv-bottom', bottom + 'px');
                } catch (e) {
                    root.style.setProperty('--vv-bottom', '0px');
                }
            }
            setVvBottom();
            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', setVvBottom);
                window.visualViewport.addEventListener('scroll', setVvBottom);
            }
            window.addEventListener('resize', setVvBottom);

            function getDock() { return document.querySelector('.mobile-dock'); }
            function getIndicator(dock) { return dock ? dock.querySelector('.dock-indicator') : null; }

            function applyIndicator(x, w, instant) {
                const dock = getDock();
                const indicator = getIndicator(dock);
                if (!dock || !indicator) return;

                if (instant) indicator.style.transition = 'none';
                indicator.style.width = w + 'px';
                indicator.style.transform = 'translate3d(' + x + 'px, 0, 0)';

                if (instant) {
                    // Re-enable transition next frame
                    requestAnimationFrame(() => {
                        indicator.style.transition = '';
                    });
                }

                try {
                    localStorage.setItem('dock_ind_x', String(x));
                    localStorage.setItem('dock_ind_w', String(w));
                } catch (e) {}
            }

            function measureActive() {
                const dock = getDock();
                const indicator = getIndicator(dock);
                if (!dock || !indicator) return null;

                const active = dock.querySelector('.dock-item.active');
                if (!active) return null;

                const dockRect = dock.getBoundingClientRect();
                const aRect = active.getBoundingClientRect();

                const pad = 10; // left/right padding inside indicator
                const width = Math.max(64, aRect.width - pad);
                const x = (aRect.left - dockRect.left) + (aRect.width - width) / 2;
                return { x, w: width };
            }

            function animateStretch(fromX, fromW, toX, toW) {
                const dock = getDock();
                const indicator = getIndicator(dock);
                if (!dock || !indicator) return;

                // Step 1: start state (instant)
                applyIndicator(fromX, fromW, true);

                // Step 2: stretch to cover path
                requestAnimationFrame(() => {
                    const minX = Math.min(fromX, toX);
                    const maxRight = Math.max(fromX + fromW, toX + toW);
                    const stretchW = Math.max(64, maxRight - minX);
                    indicator.style.width = stretchW + 'px';
                    indicator.style.transform = 'translate3d(' + minX + 'px, 0, 0)';

                    // Step 3: settle to final
                    setTimeout(() => {
                        indicator.style.width = toW + 'px';
                        indicator.style.transform = 'translate3d(' + toX + 'px, 0, 0)';
                        try {
                            localStorage.setItem('dock_ind_x', String(toX));
                            localStorage.setItem('dock_ind_w', String(toW));
                        } catch (e) {}
                    }, 140);
                });
            }

            function bootIndicator() {
                const dock = getDock();
                const indicator = getIndicator(dock);
                if (!dock || !indicator) return;

                // Start from previous position (smooth intro), then animate to current.
                let prevX = null, prevW = null;
                try {
                    prevX = parseFloat(localStorage.getItem('dock_ind_x'));
                    prevW = parseFloat(localStorage.getItem('dock_ind_w'));
                } catch (e) {}

                const cur = measureActive();
                if (!cur) return;

                const hasPrev = Number.isFinite(prevX) && Number.isFinite(prevW);
                if (hasPrev) animateStretch(prevX, prevW, cur.x, cur.w);
                else applyIndicator(cur.x, cur.w, true);

                window.addEventListener('resize', () => {
                    const m = measureActive();
                    if (m) applyIndicator(m.x, m.w, true);
                }, { passive: true });

                // If user taps around without navigation (rare), keep it fluid.
                dock.querySelectorAll('.dock-item').forEach(el => {
                    el.addEventListener('pointerdown', () => {
                        const from = measureActive();
                        dock.querySelectorAll('.dock-item').forEach(x => x.classList.remove('active'));
                        el.classList.add('active');
                        const to = measureActive();
                        if (from && to) animateStretch(from.x, from.w, to.x, to.w);
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootIndicator);
            } else {
                bootIndicator();
            }
        })();
    </script>

    <script>
        (function() {
            // Convert tables into touch-friendly cards on mobile for better UX.
            // Applies to `table.apple-glass-table` inside `.apple-glass-table-wrap` or `.table-responsive`.
            function isMobile() { return window.matchMedia && window.matchMedia('(max-width: 768px)').matches; }

            function getText(el) {
                return String(el ? (el.textContent || '') : '').replace(/\s+/g, ' ').trim();
            }

            function escapeHtml(str) {
                const s = String(str ?? '');
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
                return s.replace(/[&<>"']/g, (m) => map[m]);
            }

            function buildCardsForTable(table) {
                if (table && table.dataset && String(table.dataset.cardDisable || '') === '1') return;
                const wrap = table.closest('.apple-glass-table-wrap') || table.closest('.table-responsive') || table.parentElement;
                if (!wrap) return;
                if (wrap.classList.contains('table-cards-ready')) return;

                // Per-table config (0-based columns)
                const titleCol = Number.isFinite(parseInt(table.dataset.cardTitleCol, 10))
                    ? parseInt(table.dataset.cardTitleCol, 10)
                    : 0;
                const titleSelector = (table.dataset.cardTitleSelector || '').trim();
                const hideColsRaw = (table.dataset.cardHideCols || '').trim();
                const hideCols = new Set();
                if (hideColsRaw) {
                    hideColsRaw.split(',').map(s => parseInt(s.trim(), 10)).filter(n => Number.isFinite(n)).forEach(n => hideCols.add(n));
                }

                const chipsColsRaw = (table.dataset.cardChipsCols || '').trim();
                const chipsCols = [];
                if (chipsColsRaw) {
                    chipsColsRaw.split(',').map(s => parseInt(s.trim(), 10)).filter(n => Number.isFinite(n)).forEach(n => chipsCols.push(n));
                }
                const chipsLabels = (table.dataset.cardChipsLabels || '').split(',').map(s => s.trim()).filter(Boolean);

                const metaColsRaw = (table.dataset.cardMetaCols || '').trim();
                const metaCols = [];
                if (metaColsRaw) {
                    metaColsRaw.split(',').map(s => parseInt(s.trim(), 10)).filter(n => Number.isFinite(n)).forEach(n => metaCols.push(n));
                }

                const actionsCol = Number.isFinite(parseInt(table.dataset.cardActionsCol, 10))
                    ? parseInt(table.dataset.cardActionsCol, 10)
                    : null;

                const thead = table.querySelector('thead');
                const headers = [];
                if (thead) {
                    thead.querySelectorAll('th').forEach(th => headers.push(getText(th)));
                }
                if (headers.length === 0) return;

                const tbody = table.querySelector('tbody');
                const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
                if (rows.length === 0) return;

                // Skip extremely wide tables by default (usually not good as cards).
                if (headers.length > 9) return;

                const cardsWrap = document.createElement('div');
                cardsWrap.className = 'table-cards-wrap';

                rows.forEach(tr => {
                    const tds = Array.from(tr.children).filter(x => x && x.tagName && x.tagName.toLowerCase() === 'td');
                    if (tds.length === 0) return;

                    const card = document.createElement('div');
                    card.className = 'apple-glass apple-glass-card table-card mb-3';

                    // Title: configurable column (default 0)
                    const titleTd = tds[Math.max(0, Math.min(titleCol, tds.length - 1))];
                    let titleText = '';
                    if (titleSelector && titleTd) {
                        const el = titleTd.querySelector(titleSelector);
                        titleText = getText(el);
                    }
                    if (!titleText) titleText = getText(titleTd);
                    const title = document.createElement('div');
                    title.className = 'title';
                    title.textContent = titleText !== '' ? titleText : (headers[titleCol] || headers[0] || 'Item');
                    card.appendChild(title);

                    // Optional meta row (compact)
                    if (metaCols.length) {
                        const metaParts = metaCols.map(idx => {
                            const td = tds[idx];
                            const t = getText(td);
                            return t ? t : '';
                        }).filter(Boolean);
                        if (metaParts.length) {
                            const meta = document.createElement('div');
                            meta.className = 'meta mt-1';
                            meta.textContent = metaParts.join(' \u2022 ');
                            card.appendChild(meta);
                        }
                    }

                    // Optional chips row
                    if (chipsCols.length) {
                        const row = document.createElement('div');
                        row.className = 'apple-chip-row mt-3';
                        chipsCols.forEach((idx, j) => {
                            if (idx < 0 || idx >= tds.length) return;
                            const td = tds[idx];
                            const label = chipsLabels[j] || headers[idx] || ('Kolom ' + (idx + 1));
                            const val = getText(td);
                            if (!val) return;
                            const chip = document.createElement('span');
                            chip.className = 'apple-chip';
                            chip.innerHTML = '<span class="k">' + escapeHtml(label) + '</span>' + escapeHtml(val);
                            row.appendChild(chip);
                        });
                        if (row.children.length) card.appendChild(row);
                    }

                    // Remaining cells as label/value rows
                    for (let i = 0; i < tds.length && i < headers.length; i++) {
                        // Skip the title column to avoid repeating it.
                        if (i === titleCol) continue;
                        if (metaCols.includes(i)) continue;
                        if (chipsCols.includes(i)) continue;
                        if (hideCols.has(i)) continue;
                        if (actionsCol !== null && i === actionsCol) continue;
                        const label = headers[i] || ('Kolom ' + (i + 1));
                        const td = tds[i];
                        const valueHtml = td.innerHTML;

                        // Skip empty
                        if (getText(td) === '' && !td.querySelector('a,button,form')) continue;

                        const line = document.createElement('div');
                        line.className = 'rowline';

                        const l = document.createElement('div');
                        l.className = 'label';
                        l.textContent = label;

                        const v = document.createElement('div');
                        v.className = 'value';
                        // keep actions/links/forms working
                        v.innerHTML = valueHtml;

                        line.appendChild(l);
                        line.appendChild(v);
                        card.appendChild(line);
                    }

                    // Actions pinned to bottom (full width)
                    if (actionsCol !== null && actionsCol >= 0 && actionsCol < tds.length) {
                        const td = tds[actionsCol];
                        const html = td ? td.innerHTML : '';
                        if (html && html.trim() !== '') {
                            const actionWrap = document.createElement('div');
                            actionWrap.className = 'mt-3';
                            actionWrap.innerHTML = html;

                            // Make primary link/button full width when possible
                            const primary = actionWrap.querySelector('a,button[type="submit"],button');
                            if (primary && primary.classList) {
                                primary.classList.add('w-100');
                                primary.classList.add('d-flex', 'align-items-center', 'justify-content-center');
                            }
                            card.appendChild(actionWrap);
                        }
                    }

                    cardsWrap.appendChild(card);
                });

                wrap.classList.add('table-cards-ready');
                wrap.appendChild(cardsWrap);
            }

            function boot() {
                document.querySelectorAll('table.apple-glass-table').forEach(buildCardsForTable);
            }

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
            else boot();

            // Rebuild on resize once (cheap). If cards already built, we just keep toggling via CSS.
            window.addEventListener('resize', function() {
                if (!isMobile()) return;
                boot();
            }, { passive: true });
        })();
    </script>
    @auth
    <!-- Start (package picker) sheet -->
    <div class="offcanvas offcanvas-bottom border-0 mobile-sheet" tabindex="-1" id="offcanvasStart" style="height: min(62vh, 560px);">
        <div class="offcanvas-header border-0 pb-0">
            <div class="w-100 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="sheet-grabber"></div>
                    <div class="ms-2 fw-800" style="color: var(--text-primary);">Pilih Sesi</div>
                </div>
                <button type="button" class="btn-close" :class="isDarkTheme() ? 'btn-close-white' : ''" data-bs-dismiss="offcanvas"></button>
            </div>
        </div>
        <div class="offcanvas-body pt-3">
            <div class="small fw-800 mb-2" style="color: var(--text-secondary); letter-spacing: 0.16em;">MULAI UJIAN</div>
            <div class="apple-glass-soft rounded-4 p-3 mb-3">
                <div class="mb-3">
                    <label class="small fw-800 mb-2 d-block" style="color: var(--text-secondary);">Mode Ujian</label>
                    <select id="dockExamModeSelect" class="form-select apple-glass-input">
                        <option value="">Memuat mode...</option>
                    </select>
                </div>
                <div>
                    <label class="small fw-800 mb-2 d-block" style="color: var(--text-secondary);">Formasi Aktif</label>
                    <select id="dockSubCategorySelect" class="form-select apple-glass-input">
                        <option value="">-- Semua / mode default --</option>
                    </select>
                    <div class="small mt-2" style="color: var(--text-secondary);" id="dockSubCategoryStatus">Pilih formasi jika ingin sesi fokus ke satu rumpun tertentu.</div>
                </div>
            </div>
            <div class="row g-3" id="dockPaketGrid">
                <div class="col-6">
                    <button type="button" class="w-100 paket-card" data-start-paket="A">
                        <div class="paket-badge mb-2"><span class="dot"></span> <span data-paket-code="A">Paket A</span></div>
                        <div class="small fw-800" style="color: var(--text-primary);" data-paket-title="A">TWK</div>
                        <div class="small" style="color: var(--text-secondary);" data-paket-desc="A">Wawasan Kebangsaan (30 soal)</div>
                        <div class="small" style="color: var(--text-secondary);">30 menit</div>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="w-100 paket-card" data-start-paket="B">
                        <div class="paket-badge mb-2"><span class="dot"></span> <span data-paket-code="B">Paket B</span></div>
                        <div class="small fw-800" style="color: var(--text-primary);" data-paket-title="B">TIU</div>
                        <div class="small" style="color: var(--text-secondary);" data-paket-desc="B">Intelegensia Umum (35 soal)</div>
                        <div class="small" style="color: var(--text-secondary);">30 menit</div>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="w-100 paket-card" data-start-paket="C">
                        <div class="paket-badge mb-2"><span class="dot"></span> <span data-paket-code="C">Paket C</span></div>
                        <div class="small fw-800" style="color: var(--text-primary);" data-paket-title="C">TKP</div>
                        <div class="small" style="color: var(--text-secondary);" data-paket-desc="C">Karakteristik Pribadi (45 soal)</div>
                        <div class="small" style="color: var(--text-secondary);">30 menit</div>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="w-100 paket-card" data-start-paket="D">
                        <div class="paket-badge mb-2"><span class="dot"></span> <span data-paket-code="D">Paket D</span></div>
                        <div class="small fw-800" style="color: var(--text-primary);" data-paket-title="D">Full SKD</div>
                        <div class="small" style="color: var(--text-secondary);" data-paket-desc="D">Tryout Lengkap (110 soal)</div>
                        <div class="small" style="color: var(--text-secondary);">100 menit</div>
                    </button>
                </div>
            </div>

            <div class="mt-3 apple-glass-soft rounded-4 p-3" id="dockPackagePreview">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="small fw-800" style="color: var(--text-secondary); letter-spacing: 0.14em;">RINGKASAN SESI</div>
                        <div class="fw-900" id="dockPreviewTitle" style="color: var(--text-primary);">Pilih sesi dulu</div>
                    </div>
                    <span class="apple-pill muted" id="dockPreviewMeta">-</span>
                </div>
                <div class="mt-3 d-flex flex-wrap gap-2" id="dockPreviewSections">
                    <span class="apple-pill muted">Komposisi section akan muncul di sini.</span>
                </div>
                <div class="mt-3 small" style="color: var(--text-secondary);" id="dockPreviewRules">Aturan skor dan ambang sesi akan muncul di sini.</div>
                <div class="mt-2 small" style="color: var(--text-secondary);" id="dockPreviewStock">Stok soal per sesi sedang dicek...</div>
            </div>

            <!-- Optional sub-topik picker for Paket A/B/C (keeps mobile UX parity with desktop modal) -->
            <div class="mt-3 d-none" id="dockSubTopikPanel">
                <div class="apple-glass-soft rounded-4 p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="fw-900" style="color: var(--text-primary);">Fokus Sub-Topik</div>
                            <div class="small" style="color: var(--text-secondary);">Opsional, bisa acak semua.</div>
                        </div>
                        <button type="button" class="apple-glass-btn apple-btn-outline" id="dockSubTopikBack" style="padding:10px 14px;">Kembali</button>
                    </div>
                    <div class="mt-3">
                        <select id="dockSubTopikSelect" class="form-select apple-glass-input">
                            <option value="">-- Acak Semua Topik --</option>
                        </select>
                        <div class="small mt-2" style="color: var(--text-secondary);" id="dockSubTopikStatus">Daftar topik akan update otomatis.</div>
                    </div>
                    <button type="button" class="apple-glass-btn apple-btn-primary w-100 mt-3 py-3" id="dockSubTopikStart">Mulai</button>
                </div>
            </div>

            <div class="mt-3 small" style="color: var(--text-secondary);" id="dockStartHint">
                Sesi lengkap langsung mulai. Sesi fokus bisa dipersempit lagi dengan sub-topik bila tersedia.
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('dockQuickStartForm');
            const paketInput = document.getElementById('dockQuickStartPaket');
            const examModeInput = document.getElementById('dockQuickStartExamModeId');
            const subCategoryInput = document.getElementById('dockQuickStartSubCategoryId');
            const subTopikInput = document.getElementById('dockQuickStartSubTopik');
            const grid = document.getElementById('dockPaketGrid');
            const panel = document.getElementById('dockSubTopikPanel');
            const backBtn = document.getElementById('dockSubTopikBack');
            const startBtn = document.getElementById('dockSubTopikStart');
            const sel = document.getElementById('dockSubTopikSelect');
            const statusEl = document.getElementById('dockSubTopikStatus');
            const examModeSelect = document.getElementById('dockExamModeSelect');
            const subCategorySelect = document.getElementById('dockSubCategorySelect');
            const subCategoryStatus = document.getElementById('dockSubCategoryStatus');
            if (!form || !paketInput) return;

            const apiUrl = @json(route('ujian.sub_topik_options'));
            const stockApiUrl = @json(route('ujian.session_stock_options'));
            const examModesApi = @json(route('pref.exam-modes'));
            const subCategoriesApi = @json(route('pref.sub-categories'));
            const examPackagesApi = @json(route('pref.exam-packages'));
            const focusTopicPresets = @json(\App\Support\ExamStructureSupport::focusTopicPresets());
            let cached = null;
            let cachedModes = null;
            let currentPackages = [];
            let currentSessionStock = [];

            function decoratePackageForUi(modeCode, row) {
                const mode = String(modeCode || '').toLowerCase();
                const code = String(row?.code || '').toUpperCase();
                const profiles = {
                    pppk: {
                        A: { name: 'Teknis Formasi', description: 'Sesi inti kompetensi teknis sesuai formasi aktif.' },
                        B: { name: 'Manajerial ASN', description: 'Sesi manajerial untuk kerja tim, layanan, komunikasi, dan keputusan.' },
                        C: { name: 'Sosio-Kultural ASN', description: 'Sesi sosial kultural untuk layanan publik, keberagaman, dan etika sosial.' },
                        D: { name: 'Simulasi Lengkap PPPK', description: 'Paket lengkap PPPK: teknis formasi, manajerial, sosio-kultural, dan wawancara.' },
                    },
                    skb: {
                        A: { name: 'Kompetensi Bidang Inti', description: 'Sesi inti materi keahlian yang mengikuti formasi aktif.' },
                        B: { name: 'Regulasi & Standar Profesi', description: 'Sesi regulasi, SOP, standar profesi, dan kepatuhan bidang.' },
                        C: { name: 'Studi Kasus Praktik', description: 'Sesi analisis kasus lapangan dan penerapan keputusan teknis.' },
                        D: { name: 'Simulasi Lengkap SKB', description: 'Paket lengkap SKB yang menggabungkan bidang inti, regulasi, dan studi kasus.' },
                    },
                };
                const legacyNames = {
                    pppk: { A: ['Kompetensi Teknis'], B: ['Manajerial'], C: ['Sosio-Kultural'], D: ['Full PPPK'] },
                    skb: { A: ['Materi Inti Bidang'], B: ['Regulasi dan Analisis'], C: ['Studi Kasus Bidang'], D: ['Full SKB'] },
                };
                const profile = profiles?.[mode]?.[code];
                if (!profile) return row || {};
                const next = Object.assign({}, row || {});
                const currentName = String(next.name || '').trim();
                const currentDescription = String(next.description || '').trim();
                const allowed = legacyNames?.[mode]?.[code] || [];
                if (!currentName || allowed.includes(currentName)) next.name = profile.name;
                if (!currentDescription || allowed.includes(currentName)) next.description = profile.description;
                return next;
            }

            function resolveFocusPresetKey(modeCode, subCategoryName) {
                const mode = String(modeCode || '').toLowerCase();
                const sub = String(subCategoryName || '').toLowerCase();
                const detailedMap = {
                    pppk: [
                        [/farmakologi/i, 'pppk_farmakologi'],
                        [/farmakoterapi/i, 'pppk_farmakoterapi'],
                        [/farmasi klinis/i, 'pppk_farmasi_klinis'],
                        [/pelayanan kefarmasian/i, 'pppk_pelayanan_kefarmasian'],
                        [/resep|dosis/i, 'pppk_resep_dosis'],
                        [/regulasi obat/i, 'pppk_regulasi_obat'],
                    ],
                    skb: [
                        [/farmakologi klinik/i, 'skb_farmakologi_klinik'],
                        [/farmasi klinik/i, 'skb_farmasi_klinik'],
                        [/pelayanan kefarmasian/i, 'skb_pelayanan_kefarmasian'],
                        [/analisis resep/i, 'skb_analisis_resep'],
                        [/regulasi kefarmasian/i, 'skb_regulasi_kefarmasian'],
                        [/studi kasus pasien/i, 'skb_studi_kasus_pasien'],
                    ],
                };
                for (const [pattern, key] of (detailedMap[mode] || [])) {
                    if (pattern.test(sub)) return key;
                }
                if (mode === 'pppk' && /d3 farmasi/.test(sub)) return 'pppk_asisten_apoteker';
                if (mode === 'pppk' && /d3 manajemen informatika/.test(sub)) return 'pppk_it_terampil';
                if (mode === 'pppk' && /s1 teknik informatika/.test(sub)) return 'pppk_it_ahli';
                if (mode === 'pppk' && /s1 keperawatan/.test(sub)) return 'pppk_keperawatan_s1';
                if (mode === 'pppk' && /d4 anastesi/.test(sub)) return 'pppk_anastesi_d4';
                if (mode === 'skb' && /d3 farmasi/.test(sub)) return 'skb_asisten_apoteker';
                if (mode === 'skb' && /d3 manajemen informatika/.test(sub)) return 'skb_it_terampil';
                if (mode === 'skb' && /s1 teknik informatika/.test(sub)) return 'skb_it_ahli';
                if (mode === 'skb' && /s1 keperawatan/.test(sub)) return 'skb_keperawatan_s1';
                if (mode === 'skb' && /d4 anastesi/.test(sub)) return 'skb_anastesi_d4';
                return mode || 'skd';
            }

            function recommendedTopicsForDock(kategori) {
                const modeCode = String(examModeSelect?.selectedOptions?.[0]?.dataset?.code || '');
                const subCategoryName = String(subCategorySelect?.selectedOptions?.[0]?.textContent || '');
                const presetKey = resolveFocusPresetKey(modeCode, subCategoryName);
                const rows = focusTopicPresets?.[presetKey] || focusTopicPresets?.skd || {};
                return Array.isArray(rows?.[kategori]) ? rows[kategori] : [];
            }
            async function getOptions() {
                const modeId = examModeSelect?.value || '';
                const subCategoryId = subCategorySelect?.value || '';
                const cacheKey = [modeId || '0', subCategoryId || '0'].join(':');
                if (cached && cached[cacheKey]) return cached[cacheKey];
                try {
                    const url = new URL(apiUrl, window.location.origin);
                    url.searchParams.set('force', '1');
                    url.searchParams.set('ts', String(Date.now()));
                    if (modeId) url.searchParams.set('exam_mode_id', modeId);
                    if (subCategoryId) url.searchParams.set('sub_category_id', subCategoryId);
                    const res = await fetch(url.toString(), { credentials: 'same-origin' });
                    // If the server returns HTML (e.g. auth redirect / error page), json() will throw.
                    if (!res.ok) {
                        const txt = await res.text();
                        const snip = String(txt || '').replace(/\s+/g, ' ').trim().slice(0, 120);
                        return { TWK: [], TIU: [], TKP: [], __error: `HTTP ${res.status}: ${snip}` };
                    }
                    const json = await res.json();
                    if (json && json.ok && json.data) {
                        if (!cached) cached = {};
                        cached[cacheKey] = json.data;
                        cached[cacheKey].__counts = json.counts || null;
                        cached[cacheKey].__error = null;
                        return cached[cacheKey];
                    }
                } catch (e) {}
                return { TWK: [], TIU: [], TKP: [], __error: 'Gagal memuat opsi sub-topik (network/json).' };
            }

            async function getExamModes() {
                if (cachedModes) return cachedModes;
                try {
                    const res = await fetch(examModesApi, { credentials: 'same-origin' });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();
                    cachedModes = Array.isArray(json?.data) ? json.data : [];
                    return cachedModes;
                } catch (e) {
                    return [];
                }
            }

            async function refreshDockSubCategories() {
                if (!examModeSelect || !subCategorySelect) return;
                const examModeId = examModeSelect.value || '';
                subCategorySelect.value = '';
                subCategorySelect.innerHTML = '<option value="">-- Semua / mode default --</option>';
                if (subCategoryInput) subCategoryInput.value = '';
                if (!examModeId) return;

                try {
                    const res = await fetch(subCategoriesApi + '?kind=formasi&exam_mode_id=' + encodeURIComponent(examModeId), { credentials: 'same-origin' });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();
                    const rows = Array.isArray(json?.data) ? json.data : [];
                    rows.forEach((row) => {
                        const opt = document.createElement('option');
                        opt.value = row.id;
                        opt.textContent = row.name;
                        subCategorySelect.appendChild(opt);
                    });
                    if (subCategoryStatus) {
                        subCategoryStatus.textContent = rows.length
                            ? 'Formasi siap dipakai, atau kosongkan jika ingin mencampur formasi pada mode ini.'
                            : 'Mode ini belum punya formasi. Tambahkan dulu di Admin > Struktur Ujian.';
                        subCategoryStatus.style.color = 'var(--text-secondary)';
                    }
                } catch (e) {
                    if (subCategoryStatus) {
                        subCategoryStatus.textContent = 'Gagal memuat formasi. Cek API/sub kategori atau tambahkan di Struktur Ujian.';
                        subCategoryStatus.style.color = 'rgba(239,68,68,0.95)';
                    }
                }
            }

            async function refreshDockSessionStock() {
                try {
                    const modeId = examModeSelect?.value || '';
                    const subCategoryId = subCategorySelect?.value || '';
                    const url = new URL(stockApiUrl, window.location.origin);
                    if (modeId) url.searchParams.set('exam_mode_id', modeId);
                    if (subCategoryId) url.searchParams.set('sub_category_id', subCategoryId);
                    const res = await fetch(url.toString(), { credentials: 'same-origin' });
                    const json = await res.json();
                    currentSessionStock = Array.isArray(json?.data) ? json.data : [];
                } catch (e) {
                    currentSessionStock = [];
                }
            }

            async function bootExamModePicker() {
                if (!examModeSelect) return;
                const rows = await getExamModes();
                examModeSelect.innerHTML = '';
                if (!rows.length) {
                    examModeSelect.innerHTML = '<option value="">Mode default (fallback)</option>';
                    if (subCategoryStatus) subCategoryStatus.textContent = 'API mode belum siap. Sistem tetap berjalan dengan mode default.';
                    return;
                }

                rows.forEach((row, idx) => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name;
                    opt.dataset.code = row.code || '';
                    if ((row.code || '').toLowerCase() === 'skd' || idx === 0) opt.selected = true;
                    examModeSelect.appendChild(opt);
                });
                if (examModeInput) examModeInput.value = examModeSelect.value || '';
                await refreshDockSubCategories();
                await refreshDockPaketLabels();
            }

            async function refreshDockPaketLabels() {
                const modeId = examModeSelect?.value || '';
                const modeCode = String(examModeSelect?.selectedOptions?.[0]?.dataset?.code || '').toLowerCase();
                try {
                    const res = await fetch(examPackagesApi + '?exam_mode_id=' + encodeURIComponent(modeId) + '&exam_mode_code=' + encodeURIComponent(modeCode), { credentials: 'same-origin' });
                    const json = await res.json();
                    currentPackages = (Array.isArray(json?.data) ? json.data : []).map((row) => decoratePackageForUi(modeCode, row));
                } catch (e) {
                    currentPackages = [];
                }
                await refreshDockSessionStock();

                const labels = {};
                currentPackages.forEach((row) => {
                    labels[row.code] = {
                        title: row.name || row.code,
                        desc: row.description || (row.total_questions ? row.total_questions + ' soal' : 'Sesi ujian'),
                        meta: row.duration_minutes ? row.duration_minutes + ' menit' : '',
                    };
                });
                if (!Object.keys(labels).length) {
                    Object.assign(labels, modeCode === 'skd'
                        ? {
                            A: { title: 'TWK', desc: 'Wawasan Kebangsaan (30 soal)', meta: '30 menit' },
                            B: { title: 'TIU', desc: 'Intelegensia Umum (35 soal)', meta: '30 menit' },
                            C: { title: 'TKP', desc: 'Karakteristik Pribadi (45 soal)', meta: '30 menit' },
                            D: { title: 'Full SKD', desc: 'Tryout Lengkap (110 soal)', meta: '100 menit' },
                        }
                        : {
                            A: { title: 'Kompetensi Bidang Inti', desc: 'Sesi fokus pertama sesuai mode aktif', meta: '' },
                            B: { title: 'Pendalaman Regulasi', desc: 'Sesi fokus kedua sesuai mode aktif', meta: '' },
                            C: { title: 'Studi Kasus Praktik', desc: 'Sesi fokus ketiga sesuai mode aktif', meta: '' },
                            D: { title: 'Simulasi Lengkap', desc: 'Sesi lengkap sesuai mode aktif', meta: '' },
                        });
                }
                document.querySelectorAll('[data-paket-title]').forEach((node) => {
                    const key = node.getAttribute('data-paket-title');
                    if (labels[key]?.title) node.textContent = labels[key].title;
                });
                document.querySelectorAll('[data-paket-desc]').forEach((node) => {
                    const key = node.getAttribute('data-paket-desc');
                    if (labels[key]?.desc) node.textContent = labels[key].desc;
                });
                document.querySelectorAll('[data-paket-code]').forEach((node) => {
                    const key = node.getAttribute('data-paket-code');
                    node.textContent = 'Sesi ' + key;
                });
                document.querySelectorAll('[data-start-paket]').forEach((btn) => {
                    const key = btn.getAttribute('data-start-paket');
                    const metaEl = btn.querySelector('.small:last-child');
                    if (metaEl && labels[key]?.meta) {
                        metaEl.textContent = labels[key].meta;
                    }
                });
                const hint = document.getElementById('dockStartHint');
                if (hint) {
                    hint.textContent = modeCode === 'skd'
                        ? 'Label sesi mengikuti SKD. Sesi fokus bisa pakai sub-topik bila tersedia.'
                        : 'Label sesi, durasi, dan komposisi soal mengikuti pengaturan mode ujian.';
                }
                renderDockPackagePreview(paketInput?.value || 'D');
                syncDockStartAvailability(paketInput?.value || 'D');
            }

            function mapKategori(paket) {
                const current = Array.isArray(currentPackages) ? currentPackages.find((row) => String(row.code) === String(paket)) : null;
                const sections = Array.isArray(current?.sections) ? current.sections : [];
                return sections.length === 1 ? String(sections[0].kategori || '') : '';
            }

            function getDockStockRow(paket) {
                return Array.isArray(currentSessionStock) ? currentSessionStock.find((row) => String(row.code) === String(paket)) : null;
            }

            function syncDockStartAvailability(paket) {
                const row = getDockStockRow(paketInput?.value || paket || 'D');
                if (startBtn) {
                    const blocked = !!(row && row.ready === false);
                    startBtn.disabled = blocked;
                    startBtn.classList.toggle('opacity-50', blocked);
                    startBtn.innerHTML = blocked ? 'Stok Belum Cukup' : 'Mulai';
                }
                document.querySelectorAll('[data-start-paket]').forEach((btn) => {
                    const code = String(btn.getAttribute('data-start-paket') || '').toUpperCase();
                    const stockRow = getDockStockRow(code);
                    btn.classList.toggle('paket-card--ready', !!(stockRow && stockRow.ready));
                    btn.classList.toggle('paket-card--short', !!(stockRow && stockRow.ready === false));
                    btn.disabled = !!(stockRow && stockRow.ready === false);
                    btn.title = stockRow && stockRow.ready === false ? 'Stok soal sesi ini belum cukup.' : '';
                });
            }

            function renderDockPackagePreview(paket) {
                const titleEl = document.getElementById('dockPreviewTitle');
                const metaEl = document.getElementById('dockPreviewMeta');
                const sectionsEl = document.getElementById('dockPreviewSections');
                const rulesEl = document.getElementById('dockPreviewRules');
                const stockEl = document.getElementById('dockPreviewStock');
                const current = Array.isArray(currentPackages) ? currentPackages.find((row) => String(row.code) === String(paket)) : null;
                const stockRow = Array.isArray(currentSessionStock) ? currentSessionStock.find((row) => String(row.code) === String(paket)) : null;
                if (!titleEl || !metaEl || !sectionsEl || !rulesEl) return;

                titleEl.textContent = current?.name || ('Sesi ' + (paket || ''));
                metaEl.textContent = current?.duration_minutes ? `${current.duration_minutes} menit` : 'Durasi mengikuti mode';
                sectionsEl.innerHTML = '';

                const sections = Array.isArray(current?.sections) ? current.sections : [];
                if (!sections.length) {
                    sectionsEl.innerHTML = '<span class=\"apple-pill muted\">Komposisi soal mengikuti konfigurasi aktif.</span>';
                    rulesEl.textContent = 'Aturan skor mengikuti konfigurasi mode aktif.';
                    syncDockStartAvailability(paket);
                    return;
                }

                sections.forEach((section) => {
                    const chip = document.createElement('span');
                    chip.className = 'apple-pill muted';
                    const label = String(section.label || section.kategori || 'Section');
                    const count = parseInt(section.count || 0, 10) || 0;
                    chip.textContent = `${label}${count ? ' - ' + count + ' soal' : ''}`;
                    sectionsEl.appendChild(chip);
                });

                const notes = [];
                if (current?.score_rules?.summary) notes.push(String(current.score_rules.summary));
                if (current?.passing_rules?.notes) notes.push(String(current.passing_rules.notes));
                const sectionMins = current?.passing_rules?.section_min || {};
                Object.entries(sectionMins).forEach(([key, min]) => {
                    if (min !== null && min !== undefined && String(min) !== '') {
                        notes.push(`${key} minimal ${min}`);
                    }
                });
                rulesEl.textContent = notes.length ? notes.join(' - ') : 'Aturan skor mengikuti konfigurasi mode aktif.';
                if (stockEl) {
                    if (stockRow) {
                        stockEl.textContent = stockRow.ready
                            ? 'Stok soal siap untuk sesi ini.'
                            : ('Stok belum penuh: ' + (Array.isArray(stockRow.missing) ? stockRow.missing.join(' - ') : 'masih kurang.'));
                        stockEl.style.color = stockRow.ready ? 'var(--text-secondary)' : 'rgba(251,191,36,0.96)';
                    } else {
                        stockEl.textContent = 'Stok soal per sesi sedang dicek...';
                        stockEl.style.color = 'var(--text-secondary)';
                    }
                }
            }

            async function openSubTopik(paket) {
                if (!panel || !grid || !sel) {
                    form.submit();
                    return;
                }
                const kat = mapKategori(paket);

                // Show panel immediately so user isn't stuck if network fails.
                grid.classList.add('d-none');
                panel.classList.remove('d-none');
                sel.innerHTML = '<option value="">Memuat daftar topik...</option>';
                if (statusEl) statusEl.textContent = 'Memuat...';

                // default
                if (subTopikInput) subTopikInput.value = '';

                const opts = await getOptions();
                const list = Array.isArray(opts[kat]) ? opts[kat]
                    : Array.isArray(opts[(kat || '').toLowerCase()]) ? opts[(kat || '').toLowerCase()]
                    : [];
                sel.innerHTML = '<option value="">-- Acak Semua Topik --</option>';
                const recommended = recommendedTopicsForDock(kat);
                if (recommended.length) {
                    const group = document.createElement('optgroup');
                    group.label = 'Rekomendasi Fokus';
                    recommended.forEach((t) => {
                        if (!t || !String(t).trim()) return;
                        const o = document.createElement('option');
                        o.value = String(t);
                        o.textContent = String(t);
                        group.appendChild(o);
                    });
                    if (group.children.length) sel.appendChild(group);
                }
                if (list.length) {
                    const group = document.createElement('optgroup');
                    group.label = 'Topik dari Bank Soal';
                    list.forEach((t) => {
                        if (!t || !String(t).trim()) return;
                        const v = String(t);
                        const o = document.createElement('option');
                        o.value = v;
                        o.textContent = v;
                        group.appendChild(o);
                    });
                    if (group.children.length) sel.appendChild(group);
                }

                if (list.length === 0 && recommended.length === 0) {
                    sel.innerHTML = '<option value="">-- Belum ada sub-topik. Jalankan Auto-Label dulu --</option>';
                }

                const counts = opts.__counts || null;
                const err = opts.__error || '';
                if (statusEl) {
                    if (err) {
                        statusEl.textContent = 'Error: ' + err;
                        statusEl.style.color = 'rgba(239,68,68,0.95)';
                    } else if (recommended.length) {
                        statusEl.textContent = `Rekomendasi fokus bidang tersedia${list.length ? ' + topik dari bank soal' : ''}.`;
                        statusEl.style.color = 'var(--text-secondary)';
                    } else if (counts && typeof counts === 'object') {
                        statusEl.textContent = `Tersedia: ${kat} ${counts[kat] ?? list.length} topik.`;
                        statusEl.style.color = 'var(--text-secondary)';
                    } else {
                        statusEl.textContent = list.length > 0 ? `Tersedia: ${kat} ${list.length} topik.` : 'Daftar topik kosong.';
                        statusEl.style.color = 'var(--text-secondary)';
                    }
                }
            }

            function closeSubTopik() {
                if (!panel || !grid) return;
                panel.classList.add('d-none');
                grid.classList.remove('d-none');
                if (subTopikInput) subTopikInput.value = '';
            }

            document.querySelectorAll('[data-start-paket]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const paket = (btn.getAttribute('data-start-paket') || 'D').toUpperCase();
                    const stockRow = getDockStockRow(paket);
                    if (stockRow && stockRow.ready === false) {
                        paketInput.value = paket;
                        renderDockPackagePreview(paket);
                        return;
                    }
                    paketInput.value = paket;
                    renderDockPackagePreview(paket);
                    if (examModeInput) examModeInput.value = examModeSelect?.value || '';
                    if (subCategoryInput) subCategoryInput.value = subCategorySelect?.value || '';
                    if (subTopikInput) subTopikInput.value = '';
                    try { if (navigator.vibrate) navigator.vibrate(12); } catch (e) {}

                    // Paket D: start immediately. Paket A/B/C: offer sub-topik (optional) before start.
                    if (paket === 'D') {
                        try {
                            const sheetEl = document.getElementById('offcanvasStart');
                            const inst = sheetEl ? bootstrap.Offcanvas.getOrCreateInstance(sheetEl) : null;
                            if (inst) inst.hide();
                        } catch (e) {}
                        setTimeout(() => form.submit(), 120);
                        return;
                    }

                    openSubTopik(paket);
                });
            });

            backBtn?.addEventListener('click', closeSubTopik);
            examModeSelect?.addEventListener('change', async () => {
                if (examModeInput) examModeInput.value = examModeSelect.value || '';
                await refreshDockSubCategories();
                await refreshDockPaketLabels();
            });
            subCategorySelect?.addEventListener('change', () => {
                if (subCategoryInput) subCategoryInput.value = subCategorySelect.value || '';
            });
            sel?.addEventListener('change', () => {
                if (subTopikInput) subTopikInput.value = sel.value || '';
            });
            startBtn?.addEventListener('click', () => {
                try {
                    const sheetEl = document.getElementById('offcanvasStart');
                    const inst = sheetEl ? bootstrap.Offcanvas.getOrCreateInstance(sheetEl) : null;
                    if (inst) inst.hide();
                } catch (e) {}
                setTimeout(() => form.submit(), 120);
            });

            const startSheet = document.getElementById('offcanvasStart');
            if (startSheet) {
                startSheet.addEventListener('show.bs.offcanvas', () => {
                    bootExamModePicker();
                });
            }
            const dockQuickStartBtn = document.getElementById('dockQuickStartBtn');
            const sharedStartModal = document.getElementById('modalMulai');
            if (dockQuickStartBtn && sharedStartModal && window.bootstrap?.Modal) {
                dockQuickStartBtn.removeAttribute('data-bs-toggle');
                dockQuickStartBtn.removeAttribute('data-bs-target');
                dockQuickStartBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const modal = bootstrap.Modal.getOrCreateInstance(sharedStartModal);
                    modal.show();
                });
            }
            bootExamModePicker();
        })();
    </script>
    @endauth

    <!-- PWA Update Toast -->
    <div class="pwa-toast-wrap" aria-live="polite" aria-atomic="true">
        <div id="pwaUpdateToast" class="pwa-toast apple-glass" role="status" style="display:none;">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 14px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-glass);">
                    <i class="fas fa-arrow-rotate-right" style="color: var(--text-primary);"></i>
                </div>
                <div>
                    <div class="title">Update tersedia</div>
                    <div class="sub">Tap refresh untuk pakai versi terbaru.</div>
                </div>
            </div>
            <div class="btns">
                <button type="button" id="pwaDismissBtn" class="mini-btn">Nanti</button>
                <button type="button" id="pwaRefreshBtn" class="mini-btn primary">Refresh</button>
            </div>
        </div>
    </div>
