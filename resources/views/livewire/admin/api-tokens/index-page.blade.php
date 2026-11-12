<div class="container py-4 py-md-5">
    <?php
        $apiBaseUrl = url('/api');
        $meUrl = url('/api/me');
        $questionUrl = url('/api/questions');
        $appUrl = url('/');
    ?>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="apple-kicker">Admin</div>
            <div class="display-6 apple-title mb-1">API Token Helper</div>
            <div class="apple-subtitle">Buat token Sanctum untuk testing API dari HP, Postman, atau aplikasi mobile tanpa repot login ulang.</div>
        </div>
        <a href="<?php echo e(url('/api/me')); ?>" target="_blank" class="apple-glass-btn apple-btn-outline text-decoration-none">
            <i class="fas fa-plug me-2"></i>Cek Endpoint `/api/me`
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert apple-alert apple-glass apple-glass-card mb-4" style="background: rgba(16,185,129,0.10); border-color: rgba(16,185,129,0.18) !important;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($newToken): ?>
        <div class="apple-glass apple-glass-card p-4 mb-4" style="border-color: rgba(34,211,238,0.22);">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <div class="apple-kicker">Token Baru</div>
                    <div class="h4 apple-title mb-1"><?php echo e($newTokenName ?: 'API Token'); ?></div>
                    <div class="apple-hint">Token plain text hanya muncul sekali. Simpan sekarang.</div>
                </div>
                <button type="button" class="apple-glass-btn apple-btn-primary" onclick="navigator.clipboard.writeText(document.getElementById('newApiTokenValue').value)">
                    <i class="fas fa-copy me-2"></i>Copy Token
                </button>
            </div>
            <textarea id="newApiTokenValue" rows="3" class="form-control apple-glass-input" readonly><?php echo e($newToken); ?></textarea>
            <div class="apple-hint mt-3">Header yang dipakai di mobile tester: <code>Authorization: Bearer TOKEN_KAMU</code></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="apple-glass apple-glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="apple-kicker">Buat Token</div>
                        <div class="h4 apple-title mb-0">Token baru</div>
                    </div>
                    <span class="apple-pill muted">Sanctum</span>
                </div>

                <form method="POST" action="<?php echo e(route('admin.api-tokens.store')); ?>" class="d-grid gap-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="apple-label mb-2">Nama token</label>
                        <input type="text" name="name" class="form-control apple-glass-input" value="<?php echo e(old('name', 'mobile-tester')); ?>" placeholder="Contoh: mobile-tester" required>
                    </div>
                    <div>
                        <label class="apple-label mb-2">Abilities (opsional)</label>
                        <input type="text" name="abilities" class="form-control apple-glass-input" value="<?php echo e(old('abilities', '*')); ?>" placeholder="Contoh: questions.read,exams.write atau *">
                        <div class="apple-hint mt-2">Pisahkan dengan koma. Kosong atau <code>*</code> berarti full access untuk testing.</div>
                    </div>
                    <button type="submit" class="apple-glass-btn apple-btn-primary">
                        <i class="fas fa-key me-2"></i>Buat Token
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="apple-glass apple-glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="apple-kicker">Token Aktif</div>
                        <div class="h4 apple-title mb-0">Daftar token</div>
                    </div>
                    <span class="apple-pill muted"><?php echo e($tokens->count()); ?> token</span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tokens->isEmpty()): ?>
                    <div class="apple-glass-soft rounded-4 p-4 apple-muted">
                        Belum ada token. Bikin satu token dulu supaya testing API dari mobile lebih cepat.
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $token): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="apple-glass-soft rounded-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-700"><?php echo e($token->name); ?></div>
                                    <div class="apple-hint">Abilities: <?php echo e($token->abilities === ['*'] ? '*' : implode(', ', $token->abilities ?? [])); ?></div>
                                    <div class="apple-hint">Dibuat: <?php echo e(optional($token->created_at)->format('d/m/Y H:i')); ?> Ãƒâ€šÃ‚Â· Terakhir dipakai: <?php echo e(optional($token->last_used_at)->format('d/m/Y H:i') ?: 'Belum pernah'); ?></div>
                                </div>
                                <form method="POST" action="<?php echo e(route('admin.api-tokens.destroy', $token->id)); ?>" onsubmit="return confirm('Cabut token ini?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="apple-glass-btn apple-btn-danger">
                                        <i class="fas fa-trash-can me-2"></i>Cabut
                                    </button>
                                </form>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="apple-glass apple-glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="apple-kicker">Helper Mobile</div>
                        <div class="h4 apple-title mb-0">Base URL & header</div>
                    </div>
                    <span class="apple-pill muted">Siap copy</span>
                </div>

                <div class="d-grid gap-3">
                    <div>
                        <label class="apple-label mb-2">Base URL API</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="apiBaseUrlField" class="form-control apple-glass-input" value="<?php echo e($apiBaseUrl); ?>" readonly>
                            <button type="button" class="apple-glass-btn apple-btn-outline" onclick="navigator.clipboard.writeText(document.getElementById('apiBaseUrlField').value)">Copy</button>
                        </div>
                    </div>
                    <div>
                        <label class="apple-label mb-2">Endpoint test `/api/me`</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="apiMeUrlField" class="form-control apple-glass-input" value="<?php echo e($meUrl); ?>" readonly>
                            <button type="button" class="apple-glass-btn apple-btn-outline" onclick="navigator.clipboard.writeText(document.getElementById('apiMeUrlField').value)">Copy</button>
                        </div>
                    </div>
                    <div>
                        <label class="apple-label mb-2">Contoh header Authorization</label>
                        <textarea id="apiHeaderField" rows="3" class="form-control apple-glass-input" readonly>Authorization: Bearer TOKEN_KAMU
Accept: application/json
Content-Type: application/json</textarea>
                        <button type="button" class="apple-glass-btn apple-btn-outline mt-2" onclick="navigator.clipboard.writeText(document.getElementById('apiHeaderField').value)">
                            <i class="fas fa-copy me-2"></i>Copy Header
                        </button>
                    </div>
                    <div>
                        <label class="apple-label mb-2">Contoh request</label>
                        <textarea id="apiCurlField" rows="4" class="form-control apple-glass-input" readonly>curl -H "Authorization: Bearer TOKEN_KAMU" -H "Accept: application/json" "<?php echo e($questionUrl); ?>?difficulty=medium"</textarea>
                        <button type="button" class="apple-glass-btn apple-btn-outline mt-2" onclick="navigator.clipboard.writeText(document.getElementById('apiCurlField').value)">
                            <i class="fas fa-copy me-2"></i>Copy Contoh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="apple-glass apple-glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="apple-kicker">QR Helper</div>
                        <div class="h4 apple-title mb-0">Scan dari HP</div>
                    </div>
                    <span class="apple-pill muted">Cepat buka</span>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="apple-glass-soft rounded-4 p-3 text-center h-100">
                            <div class="fw-700 mb-2">Buka Aplikasi</div>
                            <img
                                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo e(urlencode($appUrl)); ?>"
                                alt="QR App"
                                class="img-fluid rounded-4 mb-2"
                                style="max-width: 180px;"
                            >
                            <div class="apple-hint"><?php echo e($appUrl); ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="apple-glass-soft rounded-4 p-3 text-center h-100">
                            <div class="fw-700 mb-2">Test Endpoint</div>
                            <img
                                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo e(urlencode($meUrl)); ?>"
                                alt="QR API"
                                class="img-fluid rounded-4 mb-2"
                                style="max-width: 180px;"
                            >
                            <div class="apple-hint">Scan lalu pakai token di app tester</div>
                        </div>
                    </div>
                </div>
                <div class="apple-hint mt-3">Kalau QR tidak tampil karena koneksi eksternal diblokir, helper copy di kiri tetap bisa dipakai.</div>
            </div>
        </div>
    </div>

    <div class="apple-glass apple-glass-card p-4 mt-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <div class="apple-kicker">Preview API</div>
                <div class="h4 apple-title mb-0">Cek response langsung</div>
            </div>
            <span class="apple-pill muted">Browser tester</span>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <label class="apple-label mb-2">Token untuk preview</label>
                <textarea id="previewTokenField" rows="5" class="form-control apple-glass-input" placeholder="Tempel Bearer token di sini..."><?php echo e($newToken ?? ''); ?></textarea>
            </div>
            <div class="col-lg-4">
                <label class="apple-label mb-2">Endpoint</label>
                <select id="previewEndpointSelect" class="form-select apple-glass-input">
                    <option value="<?php echo e($meUrl); ?>">GET /api/me</option>
                    <option value="<?php echo e(url('/api/questions?difficulty=medium')); ?>">GET /api/questions?difficulty=medium</option>
                    <option value="<?php echo e(url('/api/bookmarks')); ?>">GET /api/bookmarks</option>
                    <option value="<?php echo e(url('/api/exams/history')); ?>">GET /api/exams/history</option>
                </select>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" id="previewRunBtn" class="apple-glass-btn apple-btn-primary flex-fill">
                        <i class="fas fa-play me-2"></i>Test Sekarang
                    </button>
                    <button type="button" id="previewCopyBtn" class="apple-glass-btn apple-btn-outline">
                        <i class="fas fa-copy me-2"></i>Copy URL
                    </button>
                </div>
            </div>
            <div class="col-lg-4">
                <label class="apple-label mb-2">Status</label>
                <div id="previewStatusBox" class="apple-glass-soft rounded-4 p-3 h-100 apple-muted">
                    Siap dites. Pilih endpoint lalu klik <b>Test Sekarang</b>.
                </div>
            </div>
            <div class="col-12">
                <label class="apple-label mb-2">Response JSON</label>
                <pre id="previewResponseBox" class="form-control apple-glass-input" style="min-height: 240px; white-space: pre-wrap;">Belum ada response.</pre>
            </div>
        </div>
    </div>
</div>
<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        const tokenField = document.getElementById('previewTokenField');
        const endpointSelect = document.getElementById('previewEndpointSelect');
        const runBtn = document.getElementById('previewRunBtn');
        const copyBtn = document.getElementById('previewCopyBtn');
        const statusBox = document.getElementById('previewStatusBox');
        const responseBox = document.getElementById('previewResponseBox');
        if (!tokenField || !endpointSelect || !runBtn || !statusBox || !responseBox) return;

        copyBtn?.addEventListener('click', () => {
            navigator.clipboard.writeText(endpointSelect.value || '');
        });

        runBtn.addEventListener('click', async () => {
            const token = (tokenField.value || '').trim();
            const url = endpointSelect.value || '';
            if (!token || !url) {
                statusBox.textContent = 'Token atau endpoint belum diisi.';
                responseBox.textContent = 'Lengkapi token dan endpoint dulu.';
                return;
            }

            statusBox.textContent = 'Sedang mengambil response...';
            responseBox.textContent = 'Loading...';

            try {
                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
                const text = await res.text();
                statusBox.textContent = 'HTTP ' + res.status + ' Ãƒâ€šÃ‚Â· ' + (res.ok ? 'Berhasil' : 'Gagal');
                try {
                    responseBox.textContent = JSON.stringify(JSON.parse(text), null, 2);
                } catch (e) {
                    responseBox.textContent = text;
                }
            } catch (e) {
                statusBox.textContent = 'Request gagal: ' + (e?.message || 'unknown error');
                responseBox.textContent = 'Tidak bisa terhubung ke endpoint.';
            }
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
