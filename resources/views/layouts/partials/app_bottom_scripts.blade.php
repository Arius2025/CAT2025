    @auth
    <script>
        (function() {
            const LS_KEY = 'catcpns_active_jobs_v1';
            const fab = document.getElementById('queueCenterFab');
            const dot = document.getElementById('queueCenterDot');
            const countEl = document.getElementById('queueCenterCount');
            const listEl = document.getElementById('queueCenterList');
            const emptyEl = document.getElementById('queueCenterEmpty');
            const hintEl = document.getElementById('queueCenterHint');

            const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
            const cancelUrl = @json(route('admin.queue.cancel'));
            const statusUrls = {
                generate: @json(route('admin.soal.status')),
                categorize: @json(route('admin.soal.categorize-status')),
                audit: @json(route('admin.audit-soal.status')),
            };

            function loadJobs() {
                try {
                    const raw = localStorage.getItem(LS_KEY);
                    const arr = raw ? JSON.parse(raw) : [];
                    return Array.isArray(arr) ? arr : [];
                } catch (e) {
                    return [];
                }
            }
            function saveJobs(jobs) {
                try { localStorage.setItem(LS_KEY, JSON.stringify(jobs || [])); } catch (e) {}
            }
            function addJob(type, id) {
                if (!type || !id) return;
                const jobs = loadJobs();
                const exists = jobs.some(j => j && j.type === type && j.id === id);
                if (!exists) {
                    jobs.push({ type, id, started_at: new Date().toISOString() });
                    saveJobs(jobs);
                }
                refreshFab();
            }
            function removeJob(type, id) {
                const jobs = loadJobs().filter(j => !(j && j.type === type && j.id === id));
                saveJobs(jobs);
                refreshFab();
            }

            window.QueueCenter = window.QueueCenter || {};
            window.QueueCenter.addJob = addJob;
            window.QueueCenter.removeJob = removeJob;
            window.QueueCenter.open = () => {
                const modalEl = document.getElementById('queueCenterModal');
                if (!modalEl) return;
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                renderAndPoll(true);
            };

            function shortId(id) {
                const s = String(id || '');
                return s.length > 10 ? (s.slice(0, 8) + '...' + s.slice(-4)) : s;
            }
            function escapeHtml(str) {
                const s = String(str ?? '');
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
                return s.replace(/[&<>\"']/g, (m) => map[m]);
            }
            function elapsed(iso) {
                const t0 = new Date(String(iso || '')).getTime();
                if (!isFinite(t0)) return null;
                return Math.max(0, Math.floor((Date.now() - t0) / 1000));
            }

            async function fetchStatus(job) {
                const base = statusUrls[job.type];
                if (!base) return { status: 'unknown', message: 'Unknown job type.' };
                const res = await fetch(base + '?id=' + encodeURIComponent(job.id), { credentials: 'same-origin' });
                const data = await res.json();
                return data || { status: 'unknown' };
            }

            function refreshFab() {
                if (!fab) return;
                const jobs = loadJobs();
                if (jobs.length === 0) {
                    fab.classList.add('d-none');
                    return;
                }
                fab.classList.remove('d-none');
                if (countEl) countEl.textContent = String(jobs.length);
            }

            function jobLabel(type) {
                if (type === 'generate') return 'Generate Soal';
                if (type === 'categorize') return 'Auto-Label Sub Topik';
                if (type === 'audit') return 'Audit Soal';
                return type;
            }

            function buildCard(job, data) {
                const q = data.queue || {};
                const state = q.state || '-';
                const s = elapsed(data.queued_at || job.started_at);
                const prog = data.progress || {};
                const done = (prog.done != null) ? parseInt(prog.done, 10) : (data.done != null ? parseInt(data.done, 10) : null);
                const total = (prog.total != null) ? parseInt(prog.total, 10) : (data.total != null ? parseInt(data.total, 10) : null);
                const errors = (prog.errors != null) ? parseInt(prog.errors, 10) : (data.errors != null ? parseInt(data.errors, 10) : 0);
                const pct = (total && total > 0 && done != null) ? Math.min(100, Math.round((done / total) * 100)) : null;

                const isBlockingQueue = (data.status === 'processing') && (state === 'queued' || state === 'missing' || state === 'scheduled');
                const showDot = isBlockingQueue && (s != null && s >= 15);

                const statusText = escapeHtml(String(data.message || data.status || 'processing'));
                const metaParts = [];
                if (s != null) metaParts.push('Waktu: ' + s + 's');
                if (state && state !== '-') metaParts.push('Queue: ' + state);
                if (pct != null) metaParts.push('Progres: ' + pct + '%');
                if (errors) metaParts.push('Error: ' + errors);

                const lastErr = data.last_error || '';
                const canCancel = (data.status === 'processing') || (state === 'queued') || (state === 'missing') || (state === 'scheduled');

                return `
                    <div class="apple-glass-soft rounded-4 p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="fw-900" style="color: var(--text-primary);">${escapeHtml(jobLabel(job.type))}</div>
                                <div class="small apple-hint">ID: ${escapeHtml(shortId(job.id))}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                ${canCancel ? `<button class="apple-glass-btn apple-btn-danger btnCancelJob" data-type="${escapeHtml(job.type)}" data-id="${escapeHtml(job.id)}" style="padding: 10px 14px;">Batalkan</button>` : ``}
                            </div>
                        </div>
                        <div class="small mt-2" style="color: var(--text-primary);">${statusText}</div>
                        <div class="small apple-hint mt-1">${escapeHtml(metaParts.join(' - '))}</div>
                        ${lastErr ? `<div class="small mt-2 text-danger">Error terakhir: ${escapeHtml(String(lastErr).slice(0, 240))}</div>` : ``}
                    </div>
                `;
            }

            async function cancelJob(type, id) {
                const ok = confirm('Batalkan job ini?');
                if (!ok) return;
                const res = await fetch(cancelUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ type, job_id: id }),
                });
                const data = await res.json();
                if (data && data.ok) {
                    removeJob(type, id);
                    await renderAndPoll(false);
                } else {
                    alert('Gagal membatalkan job.');
                }
            }

            async function renderAndPoll(loop) {
                if (!listEl || !emptyEl) return;
                const jobs = loadJobs();
                if (jobs.length === 0) {
                    emptyEl.classList.remove('d-none');
                    listEl.innerHTML = '';
                    if (dot) dot.classList.add('d-none');
                    if (hintEl) hintEl.textContent = 'Tidak ada job aktif.';
                    refreshFab();
                    return;
                }

                emptyEl.classList.add('d-none');

                let anyStuck = false;
                const cards = [];
                const stillActive = [];

                for (const j of jobs) {
                    try {
                        const data = await fetchStatus(j);
                        const q = data.queue || {};
                        const state = q.state || '';
                        const s = elapsed(data.queued_at || j.started_at);
                        const stuck = (data.status === 'processing') && (state === 'queued' || state === 'missing' || state === 'scheduled') && (s != null && s >= 15);
                        if (stuck) anyStuck = true;

                        if (!(data.status === 'success' || data.status === 'error' || data.status === 'canceled' || data.status === 'success_with_errors')) {
                            stillActive.push(j);
                        }

                        cards.push(buildCard(j, data));
                    } catch (e) {
                        stillActive.push(j);
                        cards.push(buildCard(j, {
                            status: 'processing',
                            message: 'Gagal mengambil status (network).',
                            queue: { state: 'unknown' },
                            queued_at: j.started_at,
                            last_error: String(e && e.message ? e.message : e),
                        }));
                    }
                }

                saveJobs(stillActive);
                refreshFab();
                listEl.innerHTML = cards.join('');
                if (dot) dot.classList.toggle('d-none', !anyStuck);
                if (hintEl) hintEl.textContent = anyStuck ? 'Ada job yang tertahan di antrian. Worker queue kemungkinan belum jalan.' : 'Job berjalan normal.';

                // Auto-open once per tab/session when a job is stuck in queue (to reduce confusion).
                try {
                    const flagKey = 'queue_center_auto_opened_v1';
                    if (!loop && anyStuck && !sessionStorage.getItem(flagKey)) {
                        sessionStorage.setItem(flagKey, '1');
                        window.QueueCenter?.open?.();
                    }
                } catch (e) {}

                listEl.querySelectorAll('.btnCancelJob').forEach(btn => {
                    btn.addEventListener('click', () => cancelJob(btn.getAttribute('data-type'), btn.getAttribute('data-id')));
                });

                if (loop) {
                    setTimeout(() => {
                        const modalEl = document.getElementById('queueCenterModal');
                        const isOpen = modalEl && modalEl.classList.contains('show');
                        if (isOpen) renderAndPoll(true);
                    }, 3000);
                }
            }

            fab?.addEventListener('click', () => window.QueueCenter.open());

            refreshFab();
            if (loadJobs().length > 0) renderAndPoll(false);
        })();
    </script>
    @endauth

    <script>
        (function() {
            const modalEl = document.getElementById('desktopSearchModal');
            const searchInput = document.getElementById('desktopCommandSearch');
            const commandItems = Array.from(document.querySelectorAll('[data-command-item]'));
            const recentGrid = document.getElementById('desktopRecentGrid');
            const recentEmpty = document.getElementById('desktopRecentEmpty');
            const clearBtn = document.getElementById('desktopRecentClearBtn');
            const storageKey = 'catcpns_recent_pages_v2';
            const maxItems = 6;

            if (!modalEl) return;

            function loadItems() {
                try {
                    const raw = localStorage.getItem(storageKey);
                    const items = raw ? JSON.parse(raw) : [];
                    return Array.isArray(items) ? items : [];
                } catch (e) {
                    return [];
                }
            }

            function saveItems(items) {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(items || []));
                } catch (e) {}
            }

            function collectCurrentPage() {
                const titleEl = document.querySelector('main h1, main .apple-title, main .display-5');
                const subtitleEl = document.querySelector('main .apple-subtitle');
                const title = String(titleEl?.textContent || document.title || 'Halaman').trim();
                const subtitle = String(subtitleEl?.textContent || document.body?.dataset?.pageDescription || '').trim();
                const path = String(window.location.pathname + window.location.search + window.location.hash);
                if (!path || path === '/' || path === '/login' || path === '/register') return;

                const items = loadItems().filter((item) => item && item.path !== path);
                items.unshift({
                    title,
                    subtitle,
                    path,
                    visited_at: new Date().toISOString(),
                });
                saveItems(items.slice(0, maxItems));
            }

            function openModal() {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            function renderRecent() {
                if (!recentGrid || !recentEmpty) return;
                const items = loadItems();

                recentGrid.querySelectorAll('[data-recent-item]').forEach((el) => el.remove());
                recentEmpty.classList.toggle('d-none', items.length > 0);

                items.slice(0, maxItems).forEach((item) => {
                    const a = document.createElement('a');
                    a.href = item.path || '#';
                    a.className = 'quick-link-card';
                    a.dataset.recentItem = '1';
                    a.innerHTML = `
                        <i class="fas fa-clock-rotate-left"></i>
                        <div>
                            <div class="fw-700">${escapeHtml(item.title || 'Halaman')}</div>
                            <div class="apple-hint">${escapeHtml(item.subtitle || item.path || '')}</div>
                        </div>
                    `;
                    recentGrid.appendChild(a);
                });
            }

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, (m) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                }[m]));
            }

            function filterCommands(query) {
                const q = String(query || '').trim().toLowerCase();
                let visible = 0;
                commandItems.forEach((item) => {
                    const text = String(item.dataset.commandText || item.textContent || '').toLowerCase();
                    const match = q === '' || text.includes(q);
                    item.classList.toggle('d-none', !match);
                    if (match) visible++;
                });

                const emptyId = 'desktopCommandEmpty';
                let empty = document.getElementById(emptyId);
                if (!empty) {
                    empty = document.createElement('div');
                    empty.id = emptyId;
                    empty.className = 'apple-glass-soft apple-glass-card p-3 mt-3 d-none';
                    empty.innerHTML = '<div class="fw-700 mb-1">Tidak ada hasil</div><div class="apple-hint">Coba kata kunci lain atau buka pintasan yang tersedia.</div>';
                    recentGrid?.parentElement?.appendChild(empty);
                }
                empty.classList.toggle('d-none', visible > 0);
            }

            modalEl.addEventListener('shown.bs.modal', () => {
                collectCurrentPage();
                renderRecent();
                filterCommands(searchInput?.value || '');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select?.();
                }
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                if (searchInput) searchInput.value = '';
                filterCommands('');
            });

            searchInput?.addEventListener('input', () => filterCommands(searchInput.value));
            clearBtn?.addEventListener('click', () => {
                try { localStorage.removeItem(storageKey); } catch (e) {}
                renderRecent();
                filterCommands(searchInput?.value || '');
            });

            document.addEventListener('keydown', (event) => {
                const key = String(event.key || '').toLowerCase();
                if ((event.ctrlKey || event.metaKey) && key === 'k') {
                    event.preventDefault();
                    openModal();
                }
            });

            window.DesktopCommandCenter = {
                open: openModal,
                refresh: function() {
                    collectCurrentPage();
                    renderRecent();
                    filterCommands(searchInput?.value || '');
                },
            };

            collectCurrentPage();
        })();
    </script>

    <script>
        (function() {
            const seen = new Set();
            const prefetched = new WeakSet();

            function isSameOrigin(href) {
                try {
                    const url = new URL(href, window.location.href);
                    return url.origin === window.location.origin && !url.href.startsWith('javascript:');
                } catch (e) {
                    return false;
                }
            }

            function prefetch(href) {
                if (!href || !isSameOrigin(href)) return;
                const url = new URL(href, window.location.href);
                const key = url.pathname + url.search + url.hash;
                if (seen.has(key)) return;
                seen.add(key);
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url.href;
                link.as = 'document';
                document.head.appendChild(link);
            }

            function bind(root) {
                if (!root || prefetched.has(root)) return;
                prefetched.add(root);
                root.querySelectorAll('a[href]').forEach((el) => {
                    const href = el.getAttribute('href') || '';
                    if (!isSameOrigin(href)) return;
                    const warm = () => prefetch(href);
                    el.addEventListener('mouseenter', warm, { passive: true });
                    el.addEventListener('focus', warm, { passive: true });
                    el.addEventListener('touchstart', warm, { passive: true });
                });
            }

            function idlePrefetch() {
                const quickLinks = [
                    @json(route('ujian.index')),
                    @json(route('analysis.index')),
                    @json(route('coach.index')),
                    @json(route('ujian.dashboard')),
                    @json(route('ujian.leaderboard')),
                    @json(route('bookmark.index')),
                ];
                quickLinks.forEach(prefetch);
            }

            function boot() {
                bind(document.querySelector('nav.desktop-topbar'));
                bind(document.querySelector('.desktop-search-modal'));
                bind(document.querySelector('.mobile-dock'));
                bind(document.querySelector('.offcanvas.mobile-sheet'));
                const idle = window.requestIdleCallback || function(cb) { return window.setTimeout(cb, 1200); };
                idle(idlePrefetch);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                boot();
            }
        })();
    </script>

    @stack('scripts')
    @livewireScripts
    <script>
        (function() {
            if (!('serviceWorker' in navigator)) return;

            const SW_VERSION = '20261008-4';
            const SW_URL = '/sw.js?v=' + encodeURIComponent(SW_VERSION);
            const toastEl = document.getElementById('pwaUpdateToast');
            const dismissBtn = document.getElementById('pwaDismissBtn');
            const refreshBtn = document.getElementById('pwaRefreshBtn');

            let lastReg = null;
            let reloading = false;

            function showToast(reg, message) {
                if (!toastEl || !refreshBtn || !dismissBtn) return;
                lastReg = reg || lastReg;
                const titleEl = toastEl.querySelector('.title');
                const subEl = toastEl.querySelector('.sub');
                if (titleEl) titleEl.textContent = 'Update tersedia';
                if (subEl && message) subEl.textContent = message;
                toastEl.style.display = '';
                requestAnimationFrame(() => toastEl.classList.add('show'));
            }

            function hideToast() {
                if (!toastEl) return;
                toastEl.classList.remove('show');
                window.setTimeout(() => {
                    toastEl.style.display = 'none';
                }, 260);
            }

            async function cleanupLegacyRegistrations() {
                try {
                    const regs = await navigator.serviceWorker.getRegistrations();
                    await Promise.all(regs.map(async (reg) => {
                        const scriptUrl = String(reg.active?.scriptURL || reg.waiting?.scriptURL || reg.installing?.scriptURL || '');
                        if (scriptUrl && !scriptUrl.includes('v=' + SW_VERSION)) {
                            await reg.unregister();
                        }
                    }));
                } catch (e) {}
            }

            function triggerActivation(reg, announce = false) {
                if (!reg) return;
                lastReg = reg;

                if (announce) {
                    showToast(reg, 'Versi baru sedang diterapkan. Halaman akan dimuat ulang otomatis.');
                }

                try {
                    if (reg.waiting) {
                        reloading = true;
                        if (refreshBtn) {
                            refreshBtn.disabled = true;
                            refreshBtn.textContent = 'Memuat...';
                        }
                        reg.waiting.postMessage({ type: 'SKIP_WAITING' });
                        window.setTimeout(() => {
                            if (reloading) window.location.reload();
                        }, 1800);
                    }
                } catch (e) {}
            }

            if (dismissBtn) dismissBtn.addEventListener('click', hideToast);

            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    triggerActivation(lastReg, true);
                });
            }

            navigator.serviceWorker.addEventListener('controllerchange', function() {
                if (!reloading) return;
                reloading = false;
                window.location.reload();
            });

            navigator.serviceWorker.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'SW_ACTIVATED') {
                    reloading = false;
                }
            });

            window.addEventListener('load', async function() {
                await cleanupLegacyRegistrations();

                navigator.serviceWorker.register(SW_URL, { scope: '/' })
                    .then(function(reg) {
                        lastReg = reg;
                        try { reg.update(); } catch (e) {}

                        if (reg.waiting) {
                            triggerActivation(reg, true);
                        }

                        reg.addEventListener('updatefound', function() {
                            const nw = reg.installing;
                            if (!nw) return;
                            nw.addEventListener('statechange', function() {
                                if (nw.state === 'installed' && navigator.serviceWorker.controller) {
                                    triggerActivation(reg, true);
                                }
                            });
                        });
                    })
                    .catch(function() { /* ignore */ });
            });
        })();
    </script>
    <script>
        (function() {
            // Extra guard for iOS Safari: prevent pinch-zoom "bounce" gestures in app-like mode.
            // (Android Chrome primarily respects the viewport meta already.)
            document.addEventListener('gesturestart', function(e) { e.preventDefault(); }, { passive: false });
            document.addEventListener('gesturechange', function(e) { e.preventDefault(); }, { passive: false });
            document.addEventListener('gestureend', function(e) { e.preventDefault(); }, { passive: false });
        })();
    </script>
