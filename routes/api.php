<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\QuranReaderController;
use App\Http\Controllers\Student\BookmarkController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\AchievementController;

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
