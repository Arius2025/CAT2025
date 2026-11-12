<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ExamModeController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\Admin\ExpertRuleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

Route::get('/exam-modes', [ExamModeController::class, 'index']);
Route::get('/sub-categories', [ExamModeController::class, 'subCategories']);
Route::get('/exam-packages', [ExamModeController::class, 'packages']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::post('/exam-modes', [ExamModeController::class, 'store']);
    Route::put('/exam-modes/{id}', [ExamModeController::class, 'update']);
    Route::delete('/exam-modes/{id}', [ExamModeController::class, 'destroy']);

    Route::post('/sub-categories', [SubCategoryController::class, 'store']);
    Route::put('/sub-categories/{id}', [SubCategoryController::class, 'update']);
    Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'destroy']);

    Route::get('/questions', [QuestionController::class, 'index']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
    Route::post('/questions/generate', [QuestionController::class, 'generate']);
    Route::post('/questions/generate-with-qc', [QuestionController::class, 'generateWithQc']);
    Route::post('/questions/audit-tiu-batch', [QuestionController::class, 'auditTiuBatch']);
    Route::get('/questions/flagged', [QuestionController::class, 'flagged']);
    Route::post('/questions/{id}/audit', [QuestionController::class, 'audit']);
    Route::post('/questions/{id}/apply-fix', [QuestionController::class, 'applyFix']);
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::delete('/bookmarks/{questionId}', [BookmarkController::class, 'destroy']);

    Route::post('/exams/start', [ExamController::class, 'start']);
    Route::get('/exams/history', [ExamController::class, 'history']);
    Route::get('/exams/stats', [ExamController::class, 'stats']);
    Route::get('/exams/{id}', [ExamController::class, 'show']);
    Route::post('/exams/{id}/answer', [ExamController::class, 'answer']);
    Route::post('/exams/{id}/submit', [ExamController::class, 'submit']);
    Route::get('/analysis/summary', [AnalysisController::class, 'summary']);
    Route::get('/analysis/categories', [AnalysisController::class, 'categories']);
    Route::get('/analysis/recommendations', [AnalysisController::class, 'recommendations']);
    Route::get('/analysis/history', [AnalysisController::class, 'history']);
    Route::get('/analysis/modes', [AnalysisController::class, 'modes']);
    Route::get('/coach/status', [CoachController::class, 'status']);
    Route::get('/coach/priorities', [CoachController::class, 'priorities']);
    Route::get('/coach/recommendations', [CoachController::class, 'recommendations']);
    Route::get('/coach/study-plan', [CoachController::class, 'studyPlan']);
    Route::get('/coach/prediction', [CoachController::class, 'prediction']);
    Route::post('/coach/study-plan/regenerate', [CoachController::class, 'regenerateStudyPlan']);
    Route::get('/admin/expert-rules', [ExpertRuleController::class, 'index']);
    Route::post('/admin/expert-rules', [ExpertRuleController::class, 'store']);
});

