<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SoalController; // Tambahkan ini di paling atas
use App\Http\Controllers\Admin\SoalBankController;
use App\Http\Controllers\Admin\QueueCenterController;
use App\Http\Controllers\Admin\QuestionQualityController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\FiguralController;
use App\Http\Controllers\Admin\ExamStructureController;
use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Api\ExamModeController as ApiExamModeController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\SmartCoachController;
use App\Http\Controllers\UjianController;
use App\Http\Controllers\AuthController;
use App\Models\ProgresUjian;
use Illuminate\Support\Facades\Schema;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 1. Landing Page - Semua rute dashboard dilindungi middleware 'auth'
Route::get('/', function() {
    return redirect()->route('ujian.index');
});

Route::get('/panduan-penggunaan', [GuideController::class, 'index'])->name('guide.index')->middleware('auth');

// 2. Route untuk Admin & AI Soal
// Menampilkan Halaman Generate
// 1. Rute untuk MENAMPILKAN halaman (GET)
Route::get('/admin/generate-soal', [SoalController::class, 'index'])->name('admin.soal.index')->middleware('auth');
Route::get('/admin/generate-soal/alias', [SoalController::class, 'index'])->name('admin.generate_soal')->middleware('auth');
Route::get('/admin/generate-bulk', [SoalController::class, 'indexBulk'])->name('admin.soal.bulk.index')->middleware('auth');
Route::get('/admin/generate-bulk/alias', [SoalController::class, 'indexBulk'])->name('admin.soal.bulk')->middleware('auth');
Route::get('/admin/generate-soal-pdf', [SoalController::class, 'indexPdf'])->name('admin.soal.pdf')->middleware('auth');
Route::get('/admin/generate-soal-pdf/jobs', [SoalController::class, 'pdfJobs'])->name('admin.soal.pdf.jobs')->middleware('auth');

Route::post('/admin/generate-soal', [SoalController::class, 'generate'])->name('admin.soal.generate')->middleware('auth');
Route::post('/admin/generate-bulk', [SoalController::class, 'generateBulk'])->name('admin.soal.bulk.generate')->middleware('auth');
Route::match(['get', 'post'], '/admin/generate-bulk/import/preview', [SoalController::class, 'previewBulkImport'])->name('admin.soal.bulk.import.preview')->middleware('auth');
Route::match(['get', 'post'], '/admin/generate-bulk/import/store', [SoalController::class, 'storeBulkImport'])->name('admin.soal.bulk.import.store')->middleware('auth');
Route::post('/admin/generate-soal/categorize-old', [SoalController::class, 'categorizeOld'])->name('admin.soal.categorize-old')->middleware('auth');
Route::get('/admin/generate-soal/categorize-status', [SoalController::class, 'categorizeStatus'])->name('admin.soal.categorize-status')->middleware('auth');
Route::post('/admin/generate-soal/categorize-step', [SoalController::class, 'categorizeStep'])->name('admin.soal.categorize-step')->middleware('auth');
Route::post('/admin/generate-soal/step', [SoalController::class, 'step'])->name('admin.soal.step')->middleware('auth');
Route::get('/admin/generate-soal/status', [SoalController::class, 'status'])->name('admin.soal.status')->middleware('auth');
Route::post('/admin/generate-soal/retry', [SoalController::class, 'retryPersistentJob'])->name('admin.soal.retry')->middleware('auth');
Route::get('/admin/generate-soal/ping-groq', [SoalController::class, 'pingGroq'])->name('admin.soal.ping-groq')->middleware('auth');
Route::get('/admin/ai-check', [SoalController::class, 'aiCheck'])->name('admin.ai-check')->middleware('auth');
Route::post('/admin/ai-check/test', [SoalController::class, 'testAi'])->name('admin.ai-check.test')->middleware('auth');
Route::post('/admin/generate-soal/reset-queue', [SoalController::class, 'resetQueue'])->name('admin.soal.reset-queue')->middleware('auth');
Route::post('/admin/queue/cancel', [QueueCenterController::class, 'cancel'])->name('admin.queue.cancel')->middleware('auth');
Route::post('/admin/soal-bank/fix-kategori-ai', [SoalBankController::class, 'fixKategoriAi'])->name('admin.soal-bank.fix-kategori-ai')->middleware('auth');
Route::get('/admin/soal-bank/fix-kategori-ai/status', [SoalBankController::class, 'fixKategoriAiStatus'])->name('admin.soal-bank.fix-kategori-ai.status')->middleware('auth');
Route::post('/admin/soal-bank/fix-kategori-ai/step', [SoalBankController::class, 'fixKategoriAiStep'])->name('admin.soal-bank.fix-kategori-ai.step')->middleware('auth');

// Route Manajemen Soal Figural (Upload Gambar)
Route::get('/admin/soal-figural', [FiguralController::class, 'index'])->name('admin.soal-figural.index')->middleware('auth');
Route::post('/admin/soal-figural/{id}/upload', [FiguralController::class, 'upload'])->name('admin.soal-figural.upload')->middleware('auth');
Route::delete('/admin/soal-figural/{id}/hapus-gambar', [FiguralController::class, 'destroyGambar'])->name('admin.soal-figural.destroy')->middleware('auth');
Route::get('/admin/exam-structure', [ExamStructureController::class, 'index'])->name('admin.exam-structure.index')->middleware('auth');
Route::get('/admin/api-tokens', [ApiTokenController::class, 'index'])->name('admin.api-tokens.index')->middleware('auth');
Route::post('/admin/api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store')->middleware('auth');
Route::delete('/admin/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('admin.api-tokens.destroy')->middleware('auth');
Route::get('/admin/user-access', [UserAccessController::class, 'index'])->name('admin.user-access.index')->middleware('auth');
Route::post('/admin/user-access/bulk', [UserAccessController::class, 'bulkUpdate'])->name('admin.user-access.bulk-update')->middleware('auth');
Route::put('/admin/user-access/{id}', [UserAccessController::class, 'update'])->name('admin.user-access.update')->middleware('auth');
Route::post('/admin/exam-structure/modes', [ExamStructureController::class, 'storeMode'])->name('admin.exam-structure.modes.store')->middleware('auth');
Route::put('/admin/exam-structure/modes/{id}', [ExamStructureController::class, 'updateMode'])->name('admin.exam-structure.modes.update')->middleware('auth');
Route::delete('/admin/exam-structure/modes/{id}', [ExamStructureController::class, 'destroyMode'])->name('admin.exam-structure.modes.destroy')->middleware('auth');
Route::post('/admin/exam-structure/sub-categories', [ExamStructureController::class, 'storeSubCategory'])->name('admin.exam-structure.sub-categories.store')->middleware('auth');
Route::put('/admin/exam-structure/sub-categories/{id}', [ExamStructureController::class, 'updateSubCategory'])->name('admin.exam-structure.sub-categories.update')->middleware('auth');
Route::delete('/admin/exam-structure/sub-categories/{id}', [ExamStructureController::class, 'destroySubCategory'])->name('admin.exam-structure.sub-categories.destroy')->middleware('auth');
Route::post('/admin/exam-structure/sub-categories/normalize', [ExamStructureController::class, 'normalizeSubCategories'])->name('admin.exam-structure.sub-categories.normalize')->middleware('auth');
Route::post('/admin/exam-structure/packages', [ExamStructureController::class, 'storePackage'])->name('admin.exam-structure.packages.store')->middleware('auth');
Route::put('/admin/exam-structure/packages/{id}', [ExamStructureController::class, 'updatePackage'])->name('admin.exam-structure.packages.update')->middleware('auth');
Route::delete('/admin/exam-structure/packages/{id}', [ExamStructureController::class, 'destroyPackage'])->name('admin.exam-structure.packages.destroy')->middleware('auth');
Route::post('/admin/exam-structure/presets/{preset}', [ExamStructureController::class, 'applyPreset'])->name('admin.exam-structure.presets.apply')->middleware('auth');

// Route Audit Soal
Route::get('/admin/audit-soal', [\App\Http\Controllers\Admin\AuditSoalController::class, 'index'])->name('admin.audit-soal.index')->middleware('auth');
Route::post('/admin/audit-soal/export/generate', [\App\Http\Controllers\Admin\AuditSoalController::class, 'generateExport'])->name('admin.audit-soal.export.generate')->middleware('auth');
Route::get('/admin/audit-soal/export/{token}/{format}', [\App\Http\Controllers\Admin\AuditSoalController::class, 'downloadExport'])->name('admin.audit-soal.export.download')->middleware('auth');
Route::post('/admin/audit-soal/import/preview', [\App\Http\Controllers\Admin\AuditSoalController::class, 'previewImport'])->name('admin.audit-soal.import.preview')->middleware('auth');
Route::post('/admin/audit-soal/import/apply', [\App\Http\Controllers\Admin\AuditSoalController::class, 'applyImport'])->name('admin.audit-soal.import.apply')->middleware('auth');
Route::post('/admin/audit-soal/trigger', [\App\Http\Controllers\Admin\AuditSoalController::class, 'triggerAudit'])->name('admin.audit-soal.trigger')->middleware('auth');
Route::get('/admin/audit-soal/status', [\App\Http\Controllers\Admin\AuditSoalController::class, 'status'])->name('admin.audit-soal.status')->middleware('auth');
Route::post('/admin/audit-soal/step', [\App\Http\Controllers\Admin\AuditSoalController::class, 'step'])->name('admin.audit-soal.step')->middleware('auth');
Route::post('/admin/audit-soal/{id}/apply', [\App\Http\Controllers\Admin\AuditSoalController::class, 'apply'])->name('admin.audit-soal.apply')->middleware('auth');
Route::post('/admin/audit-soal/{id}/apply-revision', [\App\Http\Controllers\Admin\AuditSoalController::class, 'applyRevision'])->name('admin.audit-soal.apply-revision')->middleware('auth');
Route::post('/admin/audit-soal/{id}/mark-valid', [\App\Http\Controllers\Admin\AuditSoalController::class, 'markValid'])->name('admin.audit-soal.mark-valid')->middleware('auth');
Route::get('/admin/question-quality', [QuestionQualityController::class, 'index'])->name('admin.question-quality.index')->middleware('auth');
Route::get('/admin/question-quality/export-json', [QuestionQualityController::class, 'exportJson'])->name('admin.question-quality.export-json')->middleware('auth');
Route::post('/admin/question-quality/audit-tiu-batch', [QuestionQualityController::class, 'auditTiuBatch'])->name('admin.question-quality.audit-tiu-batch')->middleware('auth');
Route::post('/admin/question-quality/question/{id}/audit', [QuestionQualityController::class, 'auditOne'])->name('admin.question-quality.audit-one')->middleware('auth');
Route::post('/admin/question-quality/{id}/apply-fix', [QuestionQualityController::class, 'applyFix'])->name('admin.question-quality.apply-fix')->middleware('auth');
Route::post('/admin/question-quality/{id}/mark-reviewed', [QuestionQualityController::class, 'markReviewed'])->name('admin.question-quality.mark-reviewed')->middleware('auth');

// Edit soal manual (dipakai saat audit / manual review)
Route::get('/admin/soal-bank', [SoalBankController::class, 'index'])->name('admin.soal-bank.index')->middleware('auth');
Route::get('/admin/soal-bank/export', [SoalBankController::class, 'export'])->name('admin.soal-bank.export')->middleware('auth');
Route::post('/admin/soal-bank/import', [SoalBankController::class, 'import'])->name('admin.soal-bank.import')->middleware('auth');
Route::get('/admin/soal/create', [SoalBankController::class, 'create'])->name('admin.soal.create')->middleware('auth');
Route::post('/admin/soal', [SoalBankController::class, 'store'])->name('admin.soal.store')->middleware('auth');
Route::get('/admin/soal/{id}/edit', [SoalBankController::class, 'edit'])->name('admin.soal.edit')->middleware('auth');
Route::put('/admin/soal/{id}', [SoalBankController::class, 'update'])->name('admin.soal.update')->middleware('auth');
Route::delete('/admin/soal', [SoalBankController::class, 'bulkDestroy'])->name('admin.soal.bulk-destroy')->middleware('auth');
Route::patch('/admin/soal', [SoalBankController::class, 'bulkUpdate'])->name('admin.soal.bulk-update')->middleware('auth');

// 3. Route untuk Proses Ujian (Tahap 1 & 2)
Route::middleware('auth')->group(function () {
    Route::get('/profil', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profil', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profil/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/pref/exam-modes', [ApiExamModeController::class, 'index'])->name('pref.exam-modes');
    Route::get('/pref/sub-categories', [ApiExamModeController::class, 'subCategories'])->name('pref.sub-categories');
    Route::get('/pref/exam-packages', [ApiExamModeController::class, 'packages'])->name('pref.exam-packages');
    Route::get('/bookmark-soal', [BookmarkController::class, 'index'])->name('bookmark.index');
    Route::post('/bookmark-soal/{soalId}', [BookmarkController::class, 'store'])->name('bookmark.store');
    Route::delete('/bookmark-soal/{soalId}', [BookmarkController::class, 'destroy'])->name('bookmark.destroy');
    Route::get('/dashboard/analysis', [AnalysisController::class, 'index'])->name('analysis.index');
    Route::get('/dashboard/coach', [SmartCoachController::class, 'index'])->name('coach.index');
    Route::get('/smart-coach', [SmartCoachController::class, 'index'])->name('coach.alias');
    Route::post('/dashboard/coach/regenerate', [SmartCoachController::class, 'regenerateStudyPlan'])->name('coach.regenerate');
    Route::post('/mulai-ujian', [UjianController::class, 'store'])->name('ujian.store');
    Route::get('/ujian/{id}', [UjianController::class, 'show'])->name('ujian.show');


// Debug routes AI (hanya untuk environment local)
if (app()->environment('local')) {
    Route::get('/test-ai', function() {
        $apiKey = config('services.groq.api_key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'GROQ_API_KEY belum di-set'], 400);
        }

        $response = Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->withoutVerifying()->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.1-8b-instant',
            'messages' => [['role' => 'user', 'content' => 'Halo']]
        ]);

        return $response->json();
    });
}
Route::delete('/ujian/{id}', [UjianController::class, 'destroy'])->name('ujian.destroy');



// Grouping untuk fitur Ujian
Route::prefix('ujian')->name('ujian.')->group(function () {
    // 1. Halaman Awal (Daftar & Progres)
    Route::get('/', [UjianController::class, 'index'])->name('index');
    
    // Quick start (mobile dock): create an exam session immediately with defaults.
    Route::get('/quick-start', [UjianController::class, 'quickStart'])->name('quick_start');

    // Get latest sub-topik options (for start modal) without refreshing the whole page.
    // IMPORTANT: must be declared before "/{id}" to avoid being captured as {id}.
    Route::get('/sub-topik-options', [UjianController::class, 'subTopikOptions'])->name('sub_topik_options');
    Route::get('/session-stock-options', [UjianController::class, 'sessionStockOptions'])->name('session_stock_options');

    // 2. Submit Form Start Ujian
    Route::post('/mulai', [UjianController::class, 'buatUjian'])->name('buat');
    // 3. Halaman Ujian Sedang Berlangsung (Running Test)
    Route::get('/{id}', [UjianController::class, 'show'])->name('show')->whereNumber('id');
    
    // 4. API Simpan Jawaban (Auto-save via Axios)
    Route::post('/{id}/simpan', [UjianController::class, 'simpanJawaban'])->name('simpan')->whereNumber('id');
    
    // 5. Submit Selesai Ujian & Hitung Skor
    Route::get('/{id}/selesai', [UjianController::class, 'selesai'])->name('selesai')->whereNumber('id');
    
    // 6. Halaman Hasil Akhir & Pembahasan Fundamental
    Route::get('/{id}/hasil', [UjianController::class, 'hasil'])->name('hasil')->whereNumber('id');
});

Route::get('/ujian-dashboard', [UjianController::class, 'dashboard'])->name('ujian.dashboard');
Route::get('/leaderboard', [UjianController::class, 'leaderboard'])->name('ujian.leaderboard')->middleware('auth');
});






