<?php

use App\Http\Controllers\ExamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExamController::class, 'index'])->name('exam.index');
Route::post('/start', [ExamController::class, 'start'])->name('exam.start');
Route::get('/exam', [ExamController::class, 'show'])->name('exam.show');
Route::post('/submit', [ExamController::class, 'submit'])->name('exam.submit');
Route::get('/result', [ExamController::class, 'result'])->name('exam.result');
Route::get('/pdf-pembahasan', [ExamController::class, 'downloadPdf'])->name('exam.pdf');