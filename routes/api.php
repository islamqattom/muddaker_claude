<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\QuranReaderController;
use App\Http\Controllers\Student\BookmarkController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\AchievementController;
use App\Http\Controllers\Student\LiveRecitationController;
use App\Http\Controllers\Api\ExamController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student Quran Reader API
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student/quran')->group(function () {
    Route::get('/page/{page}', [QuranReaderController::class, 'apiGetPage']);
    Route::get('/audio/{page}', [QuranReaderController::class, 'apiGetAudio']);
    Route::post('/search', [QuranReaderController::class, 'apiSearch']);
    Route::get('/juz', [QuranReaderController::class, 'apiGetJuzOptions']);
});

// Student Bookmarks API
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student/bookmarks')->group(function () {
    Route::get('/', [BookmarkController::class, 'apiIndex']);
    Route::post('/', [BookmarkController::class, 'store']);
    Route::put('/{id}', [BookmarkController::class, 'update']);
    Route::delete('/{id}', [BookmarkController::class, 'destroy']);
});

// Student Progress & Achievements API
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student/progress')->group(function () {
    Route::get('/stats', [ProgressController::class, 'apiStats']);
    Route::get('/weekly', [ProgressController::class, 'apiWeekly']);
    Route::get('/goals', [ProgressController::class, 'apiGoals']);
    Route::put('/goals', [ProgressController::class, 'updateGoal']);
    Route::post('/record', [ProgressController::class, 'apiRecordProgress']);
});

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student/achievements')->group(function () {
    Route::get('/earned', [AchievementController::class, 'apiEarned']);
    Route::get('/next', [AchievementController::class, 'apiNext']);
    Route::get('/progress/{code}', [AchievementController::class, 'apiProgress']);
});

// Live Recitation API
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student/live-recitation')->group(function () {
    Route::post('/start', [LiveRecitationController::class, 'start']);
    Route::get('/session/{id}', [LiveRecitationController::class, 'getSession']);
    Route::post('/session/{id}/end', [LiveRecitationController::class, 'endSession']);
    Route::delete('/session/{id}', [LiveRecitationController::class, 'cancelSession']);
    Route::post('/error', [LiveRecitationController::class, 'recordError']);
    Route::post('/progress', [LiveRecitationController::class, 'updateProgress']);
    Route::get('/session/{id}/report', [LiveRecitationController::class, 'getReport']);
});

// Exam API
Route::middleware('auth')->group(function () {
    Route::post('/exam/start', [ExamController::class, 'start']);
    Route::post('/exam/add-question', [ExamController::class, 'addQuestion']);
    Route::put('/exam/questions/{id}', [ExamController::class, 'updateQuestion']);
    Route::post('/exam/{id}/finish', [ExamController::class, 'finish']);
    Route::delete('/exam/{id}', [ExamController::class, 'destroy']);
    Route::get('/exam/{examId}/questions', [ExamController::class, 'getQuestions']);
    Route::get('/exam/in-progress', [ExamController::class, 'getInProgress']);
});
