<?php $__env->startPush('styles'); ?>
<style>
    .status-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }
    .status-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        border: 1px solid var(--border-glass);
        text-decoration: none;
        color: var(--text-primary);
        background: rgba(255,255,255,0.03);
        font-weight: 700;
    }
    .status-filter-pill.is-active {
        border-color: rgba(56,189,248,0.28);
        background: linear-gradient(135deg, rgba(34,211,238,0.14), rgba(59,130,246,0.10));
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .status-badge--active {
        background: rgba(16,185,129,0.12);
        border-color: rgba(16,185,129,0.22);
        color: #d1fae5;
    }
    .status-badge--pending {
        background: rgba(245,158,11,0.12);
        border-color: rgba(245,158,11,0.22);
        color: #fde68a;
    }
    .status-badge--inactive {
        background: rgba(239,68,68,0.12);
        border-color: rgba(239,68,68,0.22);
        color: #fecaca;
    }
    [data-bs-theme="light"] .status-badge--active,
    [data-bs-theme="light"] .status-badge--pending,
    [data-bs-theme="light"] .status-badge--inactive {
        color: var(--text-primary);
    }
    .access-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }
    .bulk-details summary {
        list-style: none;
        cursor: pointer;
        user-select: none;
    }
    .bulk-details summary::-webkit-details-marker { display: none; }
    .bulk-details[open] summary {
        border-bottom: 1px solid rgba(255,255,255,0.10);
    }
    .bulk-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }
    .bulk-toolbar__left,
    .bulk-toolbar__right {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }
    .access-card {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid var(--border-glass);
        background: rgba(255,255,255,0.03);
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .access-card:hover {
        transform: translateY(-1px);
        border-color: rgba(56,189,248,0.22);
    }
    .access-card.is-active {
        border-color: rgba(56,189,248,0.30);
        background: linear-gradient(135deg, rgba(34,211,238,0.12), rgba(59,130,246,0.10));
        box-shadow: 0 16px 28px rgba(15,23,42,0.12);
    }
    .access-card.is-hidden {
        display: none;
    }
    .access-card__title {
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.15;
    }
    .access-card__hint {
        display: block;
        margin-top: 4px;
        color: var(--text-secondary);
        font-size: 0.83rem;
        line-height: 1.45;
    }
    .access-card input {
        margin-top: 3px;
    }
</style>
<?php $__env->stopPush(); ?>


<div class="container page-shell">
    <section class="page-hero apple-glass apple-glass-card mb-4">
        <div class="page-hero__row">
            <div class="page-hero__copy">
                <div class="apple-kicker">Master Admin</div>
                <div class="display-6 apple-title mb-1">Akses User</div>
                <div class="apple-subtitle">Atur jalur ujian utama dan menu yang tampil untuk user lain agar tampilan mereka lebih sederhana dan sesuai formasi.</div>
            </div>
            <div class="page-toolbar">
                <a href="<?php echo e(route('admin.exam-structure.index')); ?>" class="apple-glass-btn apple-btn-outline text-decoration-none">
                    <i class="fas fa-sitemap me-2"></i>Struktur Ujian
                </a>
            </div>
        </div>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert border-0 apple-glass apple-glass-card apple-alert apple-alert-inline mb-4" style="background: rgba(16,185,129,0.10); border-color: rgba(16,185,129,0.18) !important;">
            <i class="fas fa-check-circle text-success mt-1"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->has('user_access')): ?>
        <div class="alert border-0 apple-glass apple-glass-card apple-alert apple-alert-inline mb-4" style="background: rgba(239,68,68,0.10); border-color: rgba(239,68,68,0.18) !important;">
            <i class="fas fa-exclamation-triangle text-danger mt-1"></i>
            <div><?php echo e($errors->first('user_access')); ?></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="status-filter-bar">
        <a href="<?php echo e(route('admin.user-access.index', ['status' => 'all'])); ?>" class="status-filter-pill <?php echo e(($statusFilter ?? 'all') === 'all' ? 'is-active' : ''); ?>">
            Semua <span class="apple-pill muted"><?php echo e((int) ($statusStats['all'] ?? 0)); ?></span>
        </a>
        <a href="<?php echo e(route('admin.user-access.index', ['status' => 'pending'])); ?>" class="status-filter-pill <?php echo e(($statusFilter ?? 'all') === 'pending' ? 'is-active' : ''); ?>">
            Menunggu Persetujuan <span class="apple-pill muted"><?php echo e((int) ($statusStats['pending'] ?? 0)); ?></span>
        </a>
        <a href="<?php echo e(route('admin.user-access.index', ['status' => 'active'])); ?>" class="status-filter-pill <?php echo e(($statusFilter ?? 'all') === 'active' ? 'is-active' : ''); ?>">
            Aktif <span class="apple-pill muted"><?php echo e((int) ($statusStats['active'] ?? 0)); ?></span>
        </a>
        <a href="<?php echo e(route('admin.user-access.index', ['status' => 'inactive'])); ?>" class="status-filter-pill <?php echo e(($statusFilter ?? 'all') === 'inactive' ? 'is-active' : ''); ?>">
            Nonaktif <span class="apple-pill muted"><?php echo e((int) ($statusStats['inactive'] ?? 0)); ?></span>
        </a>
    </div>

    <form method="GET" action="<?php echo e(route('admin.user-access.index')); ?>" class="apple-glass apple-glass-card p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="apple-label mb-2">Cari Nama / Email</label>
                <input type="text" name="q" value="<?php echo e($search ?? ''); ?>" class="form-control apple-glass-input" placeholder="Contoh: Risang atau gmail.com">
            </div>
            <div class="col-lg-3">
                <label class="apple-label mb-2">Status</label>
                <select name="status" class="form-select apple-glass-input">
                    <option value="all" <?php echo e(($statusFilter ?? 'all') === 'all' ? 'selected' : ''); ?>>Semua</option>
                    <option value="pending" <?php echo e(($statusFilter ?? 'all') === 'pending' ? 'selected' : ''); ?>>Menunggu Persetujuan</option>
                    <option value="active" <?php echo e(($statusFilter ?? 'all') === 'active' ? 'selected' : ''); ?>>Aktif</option>
                    <option value="inactive" <?php echo e(($statusFilter ?? 'all') === 'inactive' ? 'selected' : ''); ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-lg-4">
                <div class="apple-action-stack">
                    <button type="submit" class="apple-glass-btn apple-btn-primary">
                        <i class="fas fa-magnifying-glass me-1"></i>Cari
                    </button>
                    <a href="<?php echo e(route('admin.user-access.index')); ?>" class="apple-glass-btn apple-btn-outline text-decoration-none">
                        Reset
                    </a>
                </div>
            </div>
        </div>
    </form>

    <details class="apple-glass apple-glass-card mb-4 bulk-details" id="bulkUserAccessDetails">
        <summary class="p-3 d-flex align-items-center justify-content-between gap-3">
            <div class="fw-800">Editor Massal</div>
            <span class="apple-pill muted" id="selectedUsersCount">0 dipilih</span>
        </summary>
        <form method="POST" action="<?php echo e(route('admin.user-access.bulk-update')); ?>" id="bulkUserAccessForm" class="p-3 pt-0">
            <?php echo csrf_field(); ?>
            <div class="bulk-toolbar">
                <div class="bulk-toolbar__left">
                    <label class="form-check m-0">
                        <input class="form-check-input" type="checkbox" id="selectAllUsers">
                        <span class="form-check-label">Pilih semua yang terlihat</span>
                    </label>
                </div>
                <div class="bulk-toolbar__right">
                    <button type="submit" name="bulk_action" value="approve" class="apple-glass-btn apple-btn-primary" id="bulkApproveBtn" disabled>
                        <i class="fas fa-user-check me-2"></i>Setujui Massal
                    </button>
                    <button type="submit" name="bulk_action" value="deactivate" class="apple-glass-btn apple-btn-danger" id="bulkDeactivateBtn" disabled>
                        <i class="fas fa-user-slash me-2"></i>Nonaktifkan Massal
                    </button>
                </div>
            </div>
        </form>
    </details>

    <div class="vstack gap-3" id="userAccessCardsWrap">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $managedUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <form method="POST" action="<?php echo e(route('admin.user-access.update', $managedUser->id)); ?>" class="apple-glass apple-glass-card p-4 user-access-item" data-user-id="<?php echo e($managedUser->id); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <label class="form-check mt-1">
                            <input class="form-check-input bulk-user-checkbox" type="checkbox" form="bulkUserAccessForm" name="selected_user_ids[]" value="<?php echo e($managedUser->id); ?>">
                        </label>
                        <div>
                        <div class="fw-800"><?php echo e($managedUser->name); ?></div>
                        <div class="apple-hint"><?php echo e($managedUser->email); ?></div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php ($status = $managedUser->activationStatus()); ?>
                        <span class="status-badge status-badge--<?php echo e($status); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'active'): ?>
                                <i class="fas fa-circle-check"></i>
                            <?php elseif($status === 'pending'): ?>
                                <i class="fas fa-hourglass-half"></i>
                            <?php else: ?>
                                <i class="fas fa-ban"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php echo e($managedUser->activationStatusLabel()); ?>

                        </span>
                        <label class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo e($managedUser->isActive() ? 'checked' : ''); ?>>
                            <span class="form-check-label"><?php echo e($managedUser->isActive() ? 'Aktif' : 'Nonaktif'); ?></span>
                        </label>
                        <label class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="is_master_admin" value="1" <?php echo e($managedUser->isMasterAdmin() ? 'checked' : ''); ?>>
                            <span class="form-check-label">Master admin</span>
                        </label>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($managedUser->activationStatus() === 'pending'): ?>
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3" style="background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.18);">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-700 mb-1" style="color: var(--text-primary);">Menunggu persetujuan</div>
                                <div class="apple-hint">Akun ini belum bisa login sampai master admin mengaktifkannya.</div>
                            </div>
                            <button type="submit" name="quick_approve" value="1" class="apple-glass-btn apple-btn-primary">
                                <i class="fas fa-user-check me-2"></i>Setujui
                            </button>
                        </div>
                    </div>
                <?php elseif($managedUser->activationStatus() === 'inactive'): ?>
                    <div class="apple-glass-soft apple-glass-card p-3 mb-3" style="background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.18);">
                        <div class="fw-700 mb-1" style="color: var(--text-primary);">Akun dinonaktifkan</div>
                        <div class="apple-hint">User ini tidak bisa login sampai diaktifkan lagi.</div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="apple-label mb-2">Jalur Yang Ditampilkan</label>
                        <?php ($selectedModeIds = $managedUser->preferredExamModeIds()); ?>
                        <div class="access-grid user-mode-cards">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $examModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <label class="access-card">
                                    <input type="checkbox" name="preferred_exam_mode_ids[]" value="<?php echo e($mode->id); ?>" data-mode-code="<?php echo e($mode->code); ?>"
                                        <?php echo e(in_array((int) $mode->id, array_map('intval', $selectedModeIds), true) ? 'checked' : ''); ?>>
                                    <span>
                                        <span class="access-card__title"><?php echo e($mode->name); ?></span>
                                        <span class="access-card__hint">Tampilkan jalur ini untuk user ini.</span>
                                    </span>
                                </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="apple-label mb-2">Formasi Yang Ditampilkan</label>
                        <?php ($selectedFormasi = $managedUser->preferredFormasiNames()); ?>
                        <div class="apple-hint mb-2">Formasi akan otomatis menyesuaikan jalur yang kamu centang di atas.</div>
                        <div class="access-grid user-formasi-cards">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <label class="access-card user-formasi-card" data-mode-ids="<?php echo e(implode(',', $sub->exam_mode_ids ?? [$sub->exam_mode_id])); ?>">
                                    <input type="checkbox" name="preferred_formasi_names[]" value="<?php echo e($sub->name); ?>"
                                        <?php echo e(in_array($sub->name, $selectedFormasi, true) ? 'checked' : ''); ?>>
                                    <span>
                                        <span class="access-card__title"><?php echo e($sub->name); ?></span>
                                        <span class="access-card__hint">Tampilkan hanya menu dan latihan untuk formasi ini.</span>
                                    </span>
                                </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="apple-glass-btn apple-btn-primary w-100">Simpan Pengaturan</button>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="apple-label mb-2">Menu Yang Ditampilkan</div>
                    <div class="access-grid">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $menuOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <label class="access-card">
                                <input type="checkbox" name="visible_menu_keys[]" value="<?php echo e($key); ?>"
                                    <?php echo e(empty($managedUser->visible_menu_keys) || in_array($key, $managedUser->visible_menu_keys ?? [], true) ? 'checked' : ''); ?>>
                                <span>
                                    <span class="access-card__title"><?php echo e($label); ?></span>
                                    <span class="access-card__hint">Atur apakah menu ini terlihat untuk user ini.</span>
                                </span>
                            </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            </form>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="apple-glass apple-glass-card p-4">
                <div class="fw-800 mb-1" style="color: var(--text-primary);">Tidak ada user yang cocok</div>
                <div class="apple-hint">Coba ganti kata kunci pencarian atau ubah filter status.</div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>


<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        const forms = Array.from(document.querySelectorAll('form[action*="/admin/user-access/"]'));
        forms.forEach((form) => {
            const allCards = Array.from(form.querySelectorAll('.access-card'));
            const modeChecks = Array.from(form.querySelectorAll('.user-mode-cards input[type="checkbox"]'));
            const formasiCards = Array.from(form.querySelectorAll('.user-formasi-card'));

            const syncCardStates = () => {
                allCards.forEach((card) => {
                    const input = card.querySelector('input[type="checkbox"]');
                    card.classList.toggle('is-active', !!input?.checked);
                });
            };

            const syncFormasiCards = () => {
                const activeModeIds = modeChecks.filter((item) => item.checked).map((item) => String(item.value));
                formasiCards.forEach((card) => {
                    const modeIds = String(card.dataset.modeIds || '').split(',').map((row) => row.trim()).filter(Boolean);
                    const visible = !activeModeIds.length || modeIds.some((id) => activeModeIds.includes(id));
                    card.classList.toggle('is-hidden', !visible);
                    const input = card.querySelector('input[type="checkbox"]');
                    if (!visible && input) input.checked = false;
                });
                syncCardStates();
            };

            allCards.forEach((card) => {
                const input = card.querySelector('input[type="checkbox"]');
                if (!input) return;
                input.addEventListener('change', () => {
                    syncCardStates();
                    syncFormasiCards();
                });
            });

            syncFormasiCards();
            syncCardStates();
        });

        const bulkForm = document.getElementById('bulkUserAccessForm');
        const selectAll = document.getElementById('selectAllUsers');
        const selectedCount = document.getElementById('selectedUsersCount');
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');
        const bulkDeactivateBtn = document.getElementById('bulkDeactivateBtn');
        const bulkChecks = Array.from(document.querySelectorAll('.bulk-user-checkbox'));

        function syncBulkToolbar() {
            const checked = bulkChecks.filter((item) => item.checked);
            if (selectedCount) {
                selectedCount.textContent = `${checked.length} dipilih`;
            }
            const disabled = checked.length === 0;
            if (bulkApproveBtn) bulkApproveBtn.disabled = disabled;
            if (bulkDeactivateBtn) bulkDeactivateBtn.disabled = disabled;
            if (selectAll) {
                selectAll.checked = bulkChecks.length > 0 && checked.length === bulkChecks.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < bulkChecks.length;
            }
        }

        bulkChecks.forEach((item) => item.addEventListener('change', syncBulkToolbar));
        selectAll?.addEventListener('change', () => {
            bulkChecks.forEach((item) => {
                item.checked = !!selectAll.checked;
            });
            syncBulkToolbar();
        });
        bulkForm?.addEventListener('submit', (event) => {
            const checked = bulkChecks.filter((item) => item.checked);
            if (!checked.length) {
                event.preventDefault();
                return;
            }
        });
        syncBulkToolbar();
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
