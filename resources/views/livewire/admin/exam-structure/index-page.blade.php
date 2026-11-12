<?php $__env->startPush('styles'); ?>
<style>
    .structure-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 12px;
    }
    .structure-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }
    .structure-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .structure-stack-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
</style>
<?php $__env->stopPush(); ?>


<div class="container page-shell">
    <section class="page-hero apple-glass apple-glass-card mb-4">
        <div class="page-hero__row">
            <div class="page-hero__copy">
                <div class="apple-kicker">Admin</div>
                <div class="display-6 apple-title mb-1">Struktur Ujian</div>
                <div class="apple-subtitle">Kelola mode, sub kategori, dan sesi ujian per mode agar SKD, SKB, dan PPPK tidak lagi hardcode.</div>
            </div>
            <div class="page-toolbar">
                <a href="<?php echo e(route('guide.index')); ?>" class="apple-glass-btn apple-btn-outline text-decoration-none">
            <i class="fas fa-circle-info me-2"></i>Panduan
                </a>
            </div>
        </div>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!($schemaReady ?? false)): ?>
        <div class="alert border-0 apple-glass apple-glass-card apple-alert apple-alert-inline mb-4" style="background: rgba(245,158,11,0.12); border-color: rgba(245,158,11,0.22) !important;">
            <i class="fas fa-triangle-exclamation text-warning mt-1"></i>
            <div>
                <div class="fw-700 mb-1">Tabel mode ujian belum dibuat</div>
                <div class="apple-hint mb-2">Halaman ini sedang menampilkan fallback SKD/SKB/PPPK agar kamu tetap bisa lihat strukturnya, tapi untuk menyimpan perubahan kamu perlu jalankan migrasi dulu.</div>
                <code><?php echo e($migrationCommand ?? 'php artisan migrate'); ?></code>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert border-0 apple-glass-soft apple-alert d-flex align-items-start gap-2 mb-4">
            <i class="fas fa-check-circle text-success mt-1"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert border-0 apple-glass-soft apple-alert d-flex align-items-start gap-2 mb-4">
            <i class="fas fa-triangle-exclamation text-danger mt-1"></i>
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div><?php echo e($error); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="apple-glass apple-glass-card p-4">
                <div class="d-flex flex-column flex-xl-row align-items-start justify-content-between gap-4">
                    <div>
                        <div class="apple-kicker">Alur Kerja</div>
                        <div class="h4 apple-title mb-2">Struktur Ujian ke Generate Soal</div>
                        <div class="apple-hint">Sub kategori yang dibuat di sini otomatis muncul di dropdown <strong>Generate Soal</strong>. Preset sekarang mengikuti struktur nyata: SKB hanya IT, PPPK mencakup Farmasi dan IT.</div>
                    </div>
                    <div class="structure-action-grid">
                        <a href="<?php echo e(route('admin.soal.generate')); ?>" class="apple-nav-tile">
                            <span class="apple-nav-tile__icon"><i class="fas fa-wand-magic-sparkles"></i></span>
                            <span class="apple-nav-tile__body">
                                <span class="apple-nav-tile__title">Generate Soal</span>
                                <span class="apple-nav-tile__hint">Buka workspace pembuatan soal AI.</span>
                            </span>
                        </a>
                        <a href="<?php echo e(route('admin.soal-bank.index', ['q' => 'farmasi'])); ?>" class="apple-nav-tile">
                            <span class="apple-nav-tile__icon"><i class="fas fa-book"></i></span>
                            <span class="apple-nav-tile__body">
                                <span class="apple-nav-tile__title">Bank Soal</span>
                                <span class="apple-nav-tile__hint">Lihat stok dan metadata soal.</span>
                            </span>
                        </a>
                        <a href="<?php echo e(route('admin.audit-soal.index')); ?>" class="apple-nav-tile">
                            <span class="apple-nav-tile__icon"><i class="fas fa-microscope"></i></span>
                            <span class="apple-nav-tile__body">
                                <span class="apple-nav-tile__title">Audit Soal</span>
                                <span class="apple-nav-tile__hint">Periksa kualitas soal dan saran AI.</span>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <div class="apple-glass-soft apple-glass-card p-3 h-100">
                            <div class="fw-800 mb-1">1. Terapkan preset</div>
                            <div class="apple-hint">Klik preset PPPK Farmasi jika mau menyiapkan struktur PPPK yang sesuai data saat ini.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="apple-glass-soft apple-glass-card p-3 h-100">
                            <div class="fw-800 mb-1">2. Buka Generate Soal</div>
                            <div class="apple-hint">Pilih mode ujian lalu formasi. Dropdown sekarang mengikuti data database yang benar-benar ada.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="apple-glass-soft apple-glass-card p-3 h-100">
                            <div class="fw-800 mb-1">3. Audit dan rapikan</div>
                            <div class="apple-hint">Cek Bank Soal atau Audit Soal untuk memperbaiki pembahasan, label, dan metadata yang belum rapi.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="apple-glass apple-glass-card p-4">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 h-100">
                    <div>
                        <div class="fw-800 mb-1">Preset Cepat PPPK Farmasi</div>
                        <div class="apple-hint">Mengisi formasi PPPK ke struktur nyata: D3 Farmasi, D3 Manajemen Informatika, dan S1 Teknik Informatika.</div>
                    </div>
                    <form method="POST" action="<?php echo e(route('admin.exam-structure.presets.apply', 'pppk-apoteker')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="apple-glass-btn apple-btn-primary" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                            <i class="fas fa-flask me-2"></i>Terapkan Preset PPPK Farmasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div><div class="row g-4">
        <div class="col-lg-5">
            <div class="apple-glass apple-glass-card p-4 mb-4">
                <div class="fw-800 mb-3">Tambah Mode Ujian</div>
                <form method="POST" action="<?php echo e(route('admin.exam-structure.modes.store')); ?>" class="vstack gap-3">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="name" class="form-control apple-glass-input" placeholder="Nama mode" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                    <input type="text" name="code" class="form-control apple-glass-input" placeholder="Code unik" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                    <input type="text" name="color" class="form-control apple-glass-input" placeholder="Color optional" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                    <div class="d-flex gap-3">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_figural" value="1" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                            <span class="form-check-label">Has figural</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <button type="submit" class="apple-glass-btn apple-btn-primary" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Simpan Mode</button>
                </form>
            </div>

            <div class="apple-glass apple-glass-card p-4 mb-4">
                <div class="fw-800 mb-3">Tambah Sub Kategori</div>
                <form method="POST" action="<?php echo e(route('admin.exam-structure.sub-categories.store')); ?>" class="vstack gap-3">
                    <?php echo csrf_field(); ?>
                    <select name="exam_mode_id" class="form-select apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $examModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($mode->id); ?>"><?php echo e($mode->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <select name="kind" class="form-select apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                        <option value="formasi">Formasi</option>
                        <option value="topik">Topik Teknis</option>
                    </select>
                    <input type="text" name="name" class="form-control apple-glass-input" placeholder="Nama sub kategori" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                    <button type="submit" class="apple-glass-btn apple-btn-primary" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Simpan Sub Kategori</button>
                </form>
            </div>

            <div class="apple-glass apple-glass-card p-4">
                <div class="fw-800 mb-2">Tambah Sesi / Paket Mode</div>
                <div class="apple-hint mb-3">Gunakan ini untuk mengatur nama sesi, durasi, jumlah soal, aturan skor, dan ambang per mode.</div>
                <form method="POST" action="<?php echo e(route('admin.exam-structure.packages.store')); ?>" class="vstack gap-3">
                    <?php echo csrf_field(); ?>
                    <select name="exam_mode_id" class="form-select apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $examModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($mode->id); ?>"><?php echo e($mode->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <div class="row g-2">
                        <div class="col-4"><input type="text" name="code" class="form-control apple-glass-input" placeholder="Kode" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                        <div class="col-8"><input type="text" name="name" class="form-control apple-glass-input" placeholder="Nama sesi" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                    </div>
                    <textarea name="description" class="form-control apple-glass-input" rows="2" placeholder="Deskripsi sesi" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></textarea>
                    <div class="row g-2">
                        <div class="col-6"><input type="number" name="duration_minutes" class="form-control apple-glass-input" placeholder="Durasi (menit)" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                        <div class="col-6"><input type="number" name="total_questions" class="form-control apple-glass-input" placeholder="Jumlah soal" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                    </div>
                    <textarea name="sections_json" class="form-control apple-glass-input font-monospace" rows="6" placeholder='JSON sections, contoh: [{"kategori":"TEKNIS","count":90,"label":"Teknis","score_type":"objective"}]' <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></textarea>
                    <textarea name="passing_rules_json" class="form-control apple-glass-input font-monospace" rows="4" placeholder='JSON passing rules, contoh: {"logic":"all_sections","section_min":{"TWK":65}}' <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></textarea>
                    <textarea name="score_rules_json" class="form-control apple-glass-input font-monospace" rows="4" placeholder='JSON score rules, contoh: {"summary":"..."}' <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></textarea>
                    <div class="row g-2 align-items-center">
                        <div class="col-6"><input type="number" name="sort_order" class="form-control apple-glass-input" placeholder="Urutan" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                        <div class="col-6">
                            <label class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                                <span class="form-check-label">Active</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="apple-glass-btn apple-btn-primary" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Simpan Sesi</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="apple-glass apple-glass-card p-4 mb-4">
                <div class="fw-800 mb-3">Mode Ujian</div>
                <div class="vstack gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $examModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <form method="POST" action="<?php echo e(route('admin.exam-structure.modes.update', $mode->id)); ?>" class="apple-glass-soft rounded-4 p-3">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="row g-2">
                                <div class="col-md-4"><input type="text" name="name" value="<?php echo e($mode->name); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                <div class="col-md-3"><input type="text" name="code" value="<?php echo e($mode->code); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                <div class="col-md-2"><input type="text" name="color" value="<?php echo e($mode->color); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                <div class="col-md-3 d-flex align-items-center gap-3">
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="has_figural" value="1" <?php echo e($mode->has_figural ? 'checked' : ''); ?> <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                                        <span class="form-check-label">Figural</span>
                                    </label>
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo e($mode->is_active ? 'checked' : ''); ?> <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                                        <span class="form-check-label">Active</span>
                                    </label>
                                </div>
                            </div>
                            <div class="structure-stack-actions mt-3">
                                <div class="small apple-hint"><?php echo e($mode->sub_categories_count); ?> sub kategori</div>
                                <button type="submit" class="apple-glass-btn apple-btn-outline" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Update</button>
                            </div>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.exam-structure.modes.destroy', $mode->id)); ?>" class="mt-2">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="apple-glass-btn apple-btn-danger" onclick="return confirm('Hapus mode ini?')" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Hapus Mode</button>
                        </form>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <div class="apple-glass apple-glass-card p-4 mb-4">
                <div class="fw-800 mb-3">Sub Kategori</div>
                <div class="structure-chip-row align-items-center mb-3">
                    <span class="apple-pill mode-skd">Total <?php echo e($subCategoryStats['all'] ?? 0); ?></span>
                    <span class="apple-pill mode-skb">Formasi <?php echo e($subCategoryStats['formasi'] ?? 0); ?></span>
                    <span class="apple-pill mode-pppk">Topik <?php echo e($subCategoryStats['topik'] ?? 0); ?></span>
                    <form method="POST" action="<?php echo e(route('admin.exam-structure.sub-categories.normalize')); ?>" class="ms-auto">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="apple-glass-btn apple-btn-outline" onclick="return confirm('Rapikan isi sub kategori sekarang? Entri generik yang tidak dipakai soal akan dibersihkan, tapi soal yang sudah terhubung tidak akan dihapus.')" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                            <i class="fas fa-sparkles me-2"></i>Rapikan Database
                        </button>
                    </form>
                </div>
                <form method="GET" action="<?php echo e(route('admin.exam-structure.index')); ?>" class="structure-filter-grid mb-4">
                    <div>
                        <select name="exam_mode_id" class="form-select apple-glass-input">
                            <option value="">Semua Mode</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $examModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($mode->id); ?>" <?php echo e((string) ($selectedModeId ?? '') === (string) $mode->id ? 'selected' : ''); ?>><?php echo e($mode->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <select name="kind" class="form-select apple-glass-input">
                            <option value="">Semua Jenis</option>
                            <option value="formasi" <?php echo e(($selectedKind ?? '') === 'formasi' ? 'selected' : ''); ?>>Formasi</option>
                            <option value="topik" <?php echo e(($selectedKind ?? '') === 'topik' ? 'selected' : ''); ?>>Topik Teknis</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="q" value="<?php echo e($search ?? ''); ?>" class="form-control apple-glass-input" placeholder="Cari nama sub kategori...">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="apple-glass-btn apple-btn-outline w-100">Filter</button>
                    </div>
                </form>
                <div class="structure-chip-row mb-4">
                    <a href="<?php echo e(route('admin.exam-structure.index', ['kind' => 'formasi'])); ?>" class="apple-glass-btn apple-btn-outline text-decoration-none <?php echo e(($selectedKind ?? 'formasi') === 'formasi' && empty($selectedModeId) && ($search ?? '') === '' ? 'active' : ''); ?>">Formasi Saja</a>
                    <a href="<?php echo e(route('admin.exam-structure.index', ['kind' => 'topik'])); ?>" class="apple-glass-btn apple-btn-outline text-decoration-none <?php echo e(($selectedKind ?? '') === 'topik' && empty($selectedModeId) && ($search ?? '') === '' ? 'active' : ''); ?>">Topik Teknis</a>
                    <a href="<?php echo e(route('admin.exam-structure.index', ['kind' => ''])); ?>" class="apple-glass-btn apple-btn-outline text-decoration-none <?php echo e(($selectedKind ?? '') === '' ? 'active' : ''); ?>">Semua</a>
                    <a href="<?php echo e(route('admin.exam-structure.index')); ?>" class="apple-glass-btn apple-btn-outline text-decoration-none">Reset</a>
                </div>
                <div class="vstack gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $subCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <form method="POST" action="<?php echo e(route('admin.exam-structure.sub-categories.update', $sub->id)); ?>" class="apple-glass-soft rounded-4 p-3">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="row g-2">
                                <div class="col-md-4"><input type="text" name="name" value="<?php echo e($sub->name); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                <div class="col-md-4">
                                    <select name="exam_mode_id" class="form-select apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $examModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($mode->id); ?>" <?php echo e((string) $sub->exam_mode_id === (string) $mode->id ? 'selected' : ''); ?>><?php echo e($mode->name); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="kind" class="form-select apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                                        <option value="formasi" <?php echo e((($sub->kind ?? 'formasi') === 'formasi') ? 'selected' : ''); ?>>Formasi</option>
                                        <option value="topik" <?php echo e((($sub->kind ?? '') === 'topik') ? 'selected' : ''); ?>>Topik</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="apple-glass-btn apple-btn-outline w-100" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Update</button>
                                </div>
                            </div>
                            <div class="mt-2 small apple-hint">
                                Jenis: <span class="apple-pill <?php echo e((($sub->kind ?? 'formasi') === 'formasi') ? 'mode-skd' : 'muted'); ?>"><?php echo e(($sub->kind ?? 'formasi') === 'formasi' ? 'Formasi' : 'Topik Teknis'); ?></span>
                            </div>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.exam-structure.sub-categories.destroy', $sub->id)); ?>" class="mt-2">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="apple-glass-btn apple-btn-danger" onclick="return confirm('Hapus sub kategori ini?')" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Hapus Sub Kategori</button>
                        </form>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="apple-glass-soft rounded-4 p-4 apple-hint">
                            Belum ada data yang cocok dengan filter ini. Coba ubah mode, jenis, atau kata kunci pencarian.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="apple-glass apple-glass-card p-4">
                <div class="fw-800 mb-3">Sesi per Mode</div>
                <div class="apple-hint mb-3">Edit bagian ini jika SKB/PPPK Farmasi butuh nama sesi, durasi, komposisi, dan aturan skor yang berbeda dari preset awal.</div>
                <div class="vstack gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $examModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="apple-glass-soft rounded-4 p-3">
                            <div class="fw-800 mb-3"><?php echo e($mode->name); ?></div>
                            <div class="vstack gap-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($packages[$mode->id] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <form method="POST" action="<?php echo e(route('admin.exam-structure.packages.update', $pkg->id)); ?>" class="apple-glass rounded-4 p-3">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="exam_mode_id" value="<?php echo e($mode->id); ?>">
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-2"><input type="text" name="code" value="<?php echo e($pkg->code); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                            <div class="col-md-5"><input type="text" name="name" value="<?php echo e($pkg->name); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                            <div class="col-md-2"><input type="number" name="duration_minutes" value="<?php echo e($pkg->duration_minutes); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                            <div class="col-md-3"><input type="number" name="total_questions" value="<?php echo e($pkg->total_questions); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                        </div>
                                        <textarea name="description" class="form-control apple-glass-input mb-2" rows="2" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>><?php echo e($pkg->description); ?></textarea>
                                        <textarea name="sections_json" class="form-control apple-glass-input font-monospace mb-2" rows="5" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>><?php echo json_encode($pkg->sections ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES, 512) ?></textarea>
                                        <textarea name="passing_rules_json" class="form-control apple-glass-input font-monospace mb-2" rows="4" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>><?php echo json_encode($pkg->passing_rules ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES, 512) ?></textarea>
                                        <textarea name="score_rules_json" class="form-control apple-glass-input font-monospace mb-2" rows="3" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>><?php echo json_encode($pkg->score_rules ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES, 512) ?></textarea>
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-3"><input type="number" name="sort_order" value="<?php echo e($pkg->sort_order ?? 0); ?>" class="form-control apple-glass-input" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>></div>
                                            <div class="col-md-3">
                                                <label class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo e(!empty($pkg->is_active) ? 'checked' : ''); ?> <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>
                                                    <span class="form-check-label">Active</span>
                                                </label>
                                            </div>
                                            <div class="col-md-6 d-flex justify-content-end gap-2">
                                                <button type="submit" class="apple-glass-btn apple-btn-outline" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Update Sesi</button>
                                            </div>
                                        </div>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('admin.exam-structure.packages.destroy', $pkg->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="apple-glass-btn apple-btn-danger" onclick="return confirm('Hapus sesi ini?')" <?php echo e(!($schemaReady ?? false) ? 'disabled' : ''); ?>>Hapus Sesi</button>
                                    </form>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>




<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
