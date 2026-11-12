<div class="container page-shell">
    <section class="page-hero mb-4 position-relative overflow-hidden">
        <div class="position-absolute top-0 end-0 p-5 opacity-10 d-none d-lg-block">
            <i class="bi bi-journal-text" style="font-size: 15rem;"></i>
        </div>
        <div class="apple-kicker">Pusat Bantuan</div>
        <div class="apple-title mb-2" style="font-size: clamp(1.85rem, 4vw, 3rem);">Panduan Lengkap Platform CAT ASN</div>
        <div class="apple-subtitle col-lg-8">Selamat datang di ekosistem simulasi ujian berbasis AI. Halaman ini memberikan instruksi mendetail mengenai pengelolaan bank soal, konfigurasi ujian dinamis, hingga pemanfaatan API untuk integrasi pihak ketiga.</div>
        <div class="page-toolbar mt-4">
            <a href="{{ route('ujian.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                <i class="fas fa-house me-2"></i>Dashboard
            </a>
            <a href="{{ route('admin.soal.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                <i class="fas fa-wand-magic-sparkles me-2"></i>Generate Soal
            </a>
            <a href="{{ route('admin.exam-structure.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                <i class="fas fa-sitemap me-2"></i>Struktur Ujian
            </a>
            <a href="{{ route('admin.audit-soal.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none">
                <i class="fas fa-shield-halved me-2"></i>Audit
            </a>
        </div>
    </section>


    <div class="row g-4 mb-4 mb-md-5">
        <!-- Main Content Core Features -->
        <div class="col-lg-8">
            <div class="apple-glass apple-glass-card p-4 p-md-5 mb-4">
                <div class="h3 apple-title mb-4">Mekanisme Utama</div>
                
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3 text-primary">
                            <i class="bi bi-robot h4 mb-0"></i>
                        </div>
                        <div>
                            <h5 class="apple-title mb-0">1. AI Question Generation (Groq/Llama 3.1)</h5>
                            <span class="apple-hint text-primary fw-600">Teknologi Inti</span>
                        </div>
                    </div>
                    <p class="apple-subtitle mb-3">Sistem menggunakan <strong>LLM Llama 3.1</strong> untuk menghasilkan soal yang mirip dengan format resmi BKN. Anda dapat menentukan:</p>
                    <ul class="apple-hint list-unstyled ms-4">
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Fokus Topik:</strong> Preset seperti Nasionalisme, Pancasila, atau Farmasi.</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Komposisi Skor:</strong> Secara otomatis AI menentukan kunci jawaban dan pembahasan rasional.</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i><strong>Output Massal:</strong> Menghasilkan hingga ratusan soal dalam satu antrean background process.</li>
                    </ul>
                </div>

                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded-4 me-3 text-info">
                            <i class="bi bi-image h4 mb-0"></i>
                        </div>
                        <div>
                            <h5 class="apple-title mb-0">2. Manajemen Soal Figural (Visual)</h5>
                            <span class="apple-hint text-info fw-600">Upload & Prompt</span>
                        </div>
                    </div>
                    <p class="apple-subtitle mb-3">Soal TIU Figural memerlukan gambar. Sistem mendukung dua mode:</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="apple-glass-soft p-3 rounded-4 h-100">
                                <div class="fw-700 mb-1">Upload Manual</div>
                                <div class="apple-hint small">Unggah gambar untuk soal dan opsi A-E di menu Admin Figural.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="apple-glass-soft p-3 rounded-4 h-100">
                                <div class="fw-700 mb-1">AI Prompt Tool</div>
                                <div class="apple-hint small">Generate deskripsi gambar (Prompt) yang bisa digunakan untuk AI image generator (seperti Midjourney/DALL-E).</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded-4 me-3 text-success">
                            <i class="bi bi-shield-check h4 mb-0"></i>
                        </div>
                        <div>
                            <h5 class="apple-title mb-0">3. Audit & Validasi Massal</h5>
                            <span class="apple-hint text-success fw-600">Quality Control</span>
                        </div>
                    </div>
                    <p class="apple-subtitle">Gunakan fitur ini untuk memastikan tidak ada "halusinas AI". Sistem akan memverifikasi kesesuaian antara teks soal, opsi, jawaban benar, dan pembahasan secara otomatis.</p>
                </div>
            </div>

            <div class="apple-glass apple-glass-card p-4 p-md-5">
                <div class="h4 apple-title mb-4">Aturan Kelulusan (Passing Grade)</div>
                <div class="table-responsive">
                    <table class="table table-borderless apple-hint mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th>Kategori</th>
                                <th>Nilai Ambang</th>
                                <th>Bobot Benar</th>
                                <th>Salah/Kosong</th>
                            </tr>
                        </thead>
                        <tbody class="apple-subtitle">
                            <tr class="align-middle">
                                <td><span class="apple-pill mode-twk">TWK</span> </td>
                                <td>65</td>
                                <td>+5</td>
                                <td>0</td>
                            </tr>
                            <tr class="align-middle">
                                <td><span class="apple-pill mode-tiu">TIU</span> </td>
                                <td>80</td>
                                <td>+5</td>
                                <td>0</td>
                            </tr>
                            <tr class="align-middle">
                                <td><span class="apple-pill mode-tkp">TKP</span> </td>
                                <td>166</td>
                                <td>Bobot 1-5</td>
                                <td>0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Shortcuts -->
        <div class="col-lg-4">
            <div class="apple-glass apple-glass-card p-4 mb-4">
                <div class="h5 apple-title mb-3">Alur Pengoperasian</div>
                <div class="vertical-steps">
                    <div class="step-item mb-3">
                        <div class="fw-700 small text-primary mb-1">LANGKAH 1</div>
                        <div class="apple-hint fw-600">Pastikan Migrasi Berhasil</div>
                        <div class="small text-muted">Cek status struktur dinamis di bawah.</div>
                    </div>
                    <div class="step-item mb-3">
                        <div class="fw-700 small text-primary mb-1">LANGKAH 2</div>
                        <div class="apple-hint fw-600">Populasi Soal</div>
                        <div class="small text-muted">Gunakan AI Generate atau Import manual.</div>
                    </div>
                    <div class="step-item mb-3">
                        <div class="fw-700 small text-primary mb-1">LANGKAH 3</div>
                        <div class="apple-hint fw-600">Setting Sesi</div>
                        <div class="small text-muted">Atur durasi dan passing grade per mode.</div>
                    </div>
                    <div class="step-item">
                        <div class="fw-700 small text-primary mb-1">LANGKAH 4</div>
                        <div class="apple-hint fw-600">Distribusi</div>
                        <div class="small text-muted">Buka API atau bagikan link ujian.</div>
                    </div>
                </div>
            </div>

            <div class="apple-glass apple-glass-card p-4 mb-4">
                <div class="h5 apple-title mb-3"><i class="bi bi-cpu me-2"></i>API & Token</div>
                <p class="apple-hint mb-3">Gunakan token untuk akses data ujian via mobile app atau integrasi pihak ketiga.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.api-tokens.index') }}" class="apple-glass-btn apple-btn-outline text-decoration-none text-center">Kelola Token</a>
                </div>
            </div>

            <div class="apple-glass apple-glass-card p-4 mb-4">
                <div class="h5 apple-title mb-3">Sistem Status</div>
                @if($schemaReady)
                <div class="d-flex align-items-center text-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div class="fw-600 small">Database Terstruktur (Aktif)</div>
                </div>
                @else
                <div class="d-flex align-items-center text-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div class="fw-600 small">Butuh Migrasi Database</div>
                </div>
                @endif
            </div>

    </div>
</div>

<style>
.uppercase-tracking {
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.75rem;
    font-weight: 700;
}
.vertical-steps {
    border-left: 2px solid rgba(0,0,0,0.05);
    padding-left: 1.5rem;
}
.step-item {
    position: relative;
}
.step-item::before {
    content: "";
    position: absolute;
    left: -1.9rem;
    top: 0.2rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--apple-blue, #007aff);
    border: 3px solid white;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
}
</style>


