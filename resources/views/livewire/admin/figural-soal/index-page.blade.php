<?php $__env->startPush('styles'); ?>
<style>
    :root {
        --ai-card: rgba(30, 41, 59, 0.45);
        --ai-soft: rgba(255,255,255,0.06);
        --ai-soft-2: rgba(255,255,255,0.10);
    }
    [data-bs-theme="light"] {
        --ai-card: rgba(255, 255, 255, 0.65);
        --ai-soft: rgba(0,0,0,0.04);
        --ai-soft-2: rgba(0,0,0,0.06);
    }

    .ai-hero {
        position: relative;
        border-radius: 32px;
        border: 1px solid var(--border-glass);
        background: 
            radial-gradient(ellipse at 0% 0%, rgba(34, 211, 238, 0.22), transparent 45%),
            radial-gradient(ellipse at 100% 0%, rgba(59, 130, 246, 0.28), transparent 45%),
            linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.7));
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.08);
        overflow: hidden;
    }

    [data-bs-theme="light"] .ai-hero {
        background: 
            radial-gradient(ellipse at 0% 0%, rgba(8, 145, 178, 0.15), transparent 48%),
            radial-gradient(ellipse at 100% 0%, rgba(37, 99, 235, 0.18), transparent 48%),
            linear-gradient(135deg, rgba(255,255,255,0.85), rgba(241, 245, 249, 0.85));
        box-shadow: 
            0 20px 40px -10px rgba(0, 0, 0, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.7);
    }

    .ai-label {
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-size: 0.7rem;
        color: var(--accent-cyan);
        opacity: 0.85;
    }

    .stat-pill {
        background: var(--ai-soft);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid var(--border-glass);
        border-radius: 18px;
        padding: 12px 20px;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .stat-pill:hover { transform: translateY(-3px); background: var(--ai-soft-2); }

    .stat-value { font-size: 1.25rem; font-weight: 800; color: var(--text-primary); }
    .stat-label { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: var(--text-secondary); opacity: 0.7; letter-spacing: 0.05em; }

    .ai-panel {
        background: var(--ai-card);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--border-glass);
        border-radius: 28px;
        box-shadow: var(--shadow-premium);
    }

    .gambar-thumbnail {
        max-width: 150px;
        max-height: 120px;
        border-radius: 12px;
        border: 1px solid var(--border-glass);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
</style>
<?php $__env->stopPush(); ?>

<div class="py-4">
    <!-- Premium Overhauled Hero -->
    <div class="ai-hero p-4 p-md-5 mb-5 px-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="ai-label mb-3">
                    <i class="fas fa-microscope me-1"></i> Admin Quality Control
                </div>
                <h1 class="display-6 fw-bold mb-3" style="color: var(--text-primary); font-weight: 900; letter-spacing: -1px;">
                    Manajemen Visual <br class="d-none d-md-block"> TIU Figural
                </h1>
                <p class="text-muted fs-6 mb-0 opacity-75" style="max-width: 480px; color: var(--text-secondary) !important;">
                    Konsistensi visual menentukan akurasi tes. Gunakan tools ini untuk melengkapi prompt AI dengan gambar HD pendukung.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="row g-2 g-md-3">
                    <div class="col-6 col-md-4">
                        <div class="stat-pill text-center">
                            <div class="stat-value"><?php echo e($totalTiu); ?></div>
                            <div class="stat-label">Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-pill text-center" style="border-bottom: 3px solid #10b981;">
                            <div class="stat-value" style="color: #10b981;"><?php echo e($totalGambar); ?></div>
                            <div class="stat-label">Ready</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="stat-pill text-center" style="border-bottom: 3px solid #f59e0b;">
                            <div class="stat-value" style="color: #f59e0b;"><?php echo e($totalTanpaGambar); ?></div>
                            <div class="stat-label">Empty</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="ai-panel apple-glass apple-glass-card p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">
                <i class="fas fa-list-ul me-2 opacity-50"></i>Daftar Soal
            </h5>
            <!-- Filter buttons - Desktop only -->
            <div class="d-none d-md-flex gap-2">
                <a href="?filter=semua" class="apple-glass-btn btn-sm px-3 <?php echo e(request('filter') == 'semua' || !request('filter') ? 'apple-btn-primary' : ''); ?>">Semua</a>
                <a href="?filter=ada_gambar" class="apple-glass-btn btn-sm px-3 <?php echo e(request('filter') == 'ada_gambar' ? 'bg-success text-white' : ''); ?>">Bergambar</a>
                <a href="?filter=tanpa_gambar" class="apple-glass-btn btn-sm px-3 <?php echo e(request('filter') == 'tanpa_gambar' ? 'bg-warning text-dark' : ''); ?>">Tanpa Gambar</a>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($figuralModes ?? collect())->isNotEmpty()): ?>
            <div class="small mb-4" style="color: var(--text-secondary);">
                Mode yang mendukung figural:
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $figuralModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <span class="badge rounded-pill me-1" style="background: rgba(255,255,255,0.06); color: var(--text-primary); border: 1px solid var(--border-glass);">
                        <?php echo e($mode->name); ?>

                    </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" style="background: rgba(16,185,129,0.12); color: var(--text-primary); border: 1px solid rgba(16,185,129,0.25) !important;">
                <i class="fas fa-check-circle me-2 text-success"></i>
                <div><?php echo e(session('success')); ?></div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4" style="background: rgba(239,68,68,0.10); color: var(--text-primary); border: 1px solid rgba(239,68,68,0.25) !important;">
                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                <div><?php echo e(session('error')); ?></div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="background: rgba(239,68,68,0.10); color: var(--text-primary); border: 1px solid rgba(239,68,68,0.25) !important;">
                <ul class="mb-0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li><?php echo e($error); ?></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="table-responsive apple-glass-table-wrap">
            <table class="table table-borderless align-middle apple-glass-table"
                   data-card-disable="1"
                   style="color: var(--text-primary);">
                <thead style="background: var(--ai-soft-2);">
                    <tr>
                        <th class="py-3 px-3">MANAJEMEN GAMBAR (SOAL + OPSI)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $soals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $soal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td class="p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="fw-bold fs-5" style="color: var(--accent-blue);">#<?php echo e($soal->id); ?></div>
                                    <div class="badge bg-secondary opacity-75 mt-1"><?php echo e($soal->sub_topik ?: 'TIU - Umum'); ?></div>
                                </div>
                                <form action="<?php echo e(route('admin.soal-figural.destroy', $soal->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" onclick="return confirm('Hapus semua 6 gambar untuk soal ini?')">
                                        <i class="fas fa-trash me-1"></i> Hapus Semua Gambar
                                    </button>
                                </form>
                            </div>
                            
                            <div class="p-3 mb-4 rounded-3" style="background: var(--ai-soft); border: 1px solid var(--border-glass);">
                                <?php echo strip_tags($soal->teks_soal); ?>

                            </div>
                            
                            <form action="<?php echo e(route('admin.soal-figural.upload', $soal->id)); ?>" method="POST" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="row g-3">
                                    <?php
                                        $fields = [
                                            ['key' => 'gambar', 'db' => 'gambar_path', 'label' => 'Gambar Soal Utama', 'prompt' => 'prompt_gambar_soal'],
                                            ['key' => 'gambar_a', 'db' => 'gambar_a', 'label' => 'Gambar Opsi A', 'prompt' => 'prompt_gambar_a'],
                                            ['key' => 'gambar_b', 'db' => 'gambar_b', 'label' => 'Gambar Opsi B', 'prompt' => 'prompt_gambar_b'],
                                            ['key' => 'gambar_c', 'db' => 'gambar_c', 'label' => 'Gambar Opsi C', 'prompt' => 'prompt_gambar_c'],
                                            ['key' => 'gambar_d', 'db' => 'gambar_d', 'label' => 'Gambar Opsi D', 'prompt' => 'prompt_gambar_d'],
                                            ['key' => 'gambar_e', 'db' => 'gambar_e', 'label' => 'Gambar Opsi E', 'prompt' => 'prompt_gambar_e'],
                                        ];
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded-4 h-100 d-flex flex-column" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass);">
                                            <div class="fw-bold mb-2"><?php echo e($f['label']); ?></div>
                                            
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($soal->{$f['prompt']}): ?>
                                                <div class="small p-2 rounded-3 mb-3" style="background: rgba(14, 165, 233, 0.1); border-left: 3px solid #0ea5e9;">
                                                    <i class="fas fa-robot me-1" style="color: #0ea5e9;"></i> 
                                                    <span class="opacity-75"><?php echo e($soal->{$f['prompt']}); ?></span>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            
                                            <div class="mt-auto">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($soal->{$f['db']}): ?>
                                                    <div class="text-center mb-2">
                                                        <img src="<?php echo e(asset('storage/' . $soal->{$f['db']})); ?>" class="gambar-thumbnail" style="object-fit: cover; width: 100%; height: 120px;" alt="Gambar">
                                                    </div>
                                                    <span class="badge bg-success w-100 mb-2 py-2"><i class="fas fa-check-circle me-1"></i> Terisi</span>
                                                <?php else: ?>
                                                    <span class="badge w-100 mb-2 py-2 text-dark" style="background: #f59e0b;"><i class="fas fa-exclamation-circle me-1"></i> Kosong</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <input type="file" name="<?php echo e($f['key']); ?>" class="form-control form-control-sm" accept=".jpg,.jpeg,.png" style="background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--border-glass);">
                                            </div>
                                        </div>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="submit" class="apple-glass-btn apple-btn-primary px-4">
                                        <i class="fas fa-cloud-arrow-up me-2"></i> Upload Gambar (+ Simpan)
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td class="text-center py-5 opacity-50">Tidak ada soal TIU figural yang sesuai kriteria pencarian.</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            <?php echo e($soals->appends(request()->query())->links()); ?>

        </div>
    </div>
</div>

<?php $__env->startSection('mobileDock'); ?>
<div class="mobile-dock">
    <div class="dock-row" style="grid-template-columns: 1fr 1fr 1.25fr 1fr 1fr;">
        <div class="dock-slot">
            <a class="dock-item" href="<?php echo e(route('ujian.index')); ?>" aria-label="Home">
                <div class="text-center">
                    <i class="fas fa-house"></i>
                    <div class="dock-label">Utama</div>
                </div>
            </a>
        </div>
        <div class="dock-slot">
            <a class="dock-item <?php echo e(request('filter') == 'ada_gambar' ? 'active' : ''); ?>" href="?filter=ada_gambar" aria-label="Bergambar">
                <div class="text-center">
                    <i class="fas fa-image text-success"></i>
                    <div class="dock-label">Ada</div>
                </div>
            </a>
        </div>

        <div class="dock-slot">
            <a href="?filter=semua" class="dock-cta border-0 d-flex align-items-center justify-content-center" style="<?php echo e(request('filter') == 'semua' || !request('filter') ? '' : 'filter: grayscale(1) opacity(0.8);'); ?>" aria-label="Semua">
                <i class="fas fa-layer-group"></i>
            </a>
        </div>

        <div class="dock-slot">
            <a class="dock-item <?php echo e(request('filter') == 'tanpa_gambar' ? 'active' : ''); ?>" href="?filter=tanpa_gambar" aria-label="Kosong">
                <div class="text-center">
                    <i class="fas fa-image-slash text-warning"></i>
                    <div class="dock-label">Kosong</div>
                </div>
            </a>
        </div>

        <div class="dock-slot">
            <button class="dock-item border-0 bg-transparent" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" style="color: inherit;" aria-label="Menu">
                <div class="text-center">
                    <i class="fas fa-bars-staggered"></i>
                    <div class="dock-label">Menu</div>
                </div>
            </button>
        </div>
    </div>
</div>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
