<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Supervisor;
use App\Http\Controllers\Student;
use App\Http\Controllers\ParentPortal;

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================
// ADMIN ROUTES
// ============================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::resource('students', Admin\StudentController::class);

    // Supervisors
    Route::resource('supervisors', Admin\SupervisorController::class);

    // Parents
    Route::resource('parents', Admin\ParentController::class);

    // Levels
    Route::resource('levels', Admin\LevelController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [Admin\ReportController::class, 'index'])->name('index');
        Route::get('/recitation', [Admin\ReportController::class, 'recitation'])->name('recitation');
        Route::get('/attendance', [Admin\ReportController::class, 'attendance'])->name('attendance');
    });

    // Warnings
    Route::resource('warnings', Admin\WarningController::class);

    // Settings
    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

    // Notifications
    Route::get('/notifications/mark-read', [Admin\NotificationController::class, 'markAllRead'])->name('notifications.mark-read');
});

// ============================
// SUPERVISOR ROUTES
// ============================
Route::prefix('supervisor')->name('supervisor.')->middleware(['auth', 'role:supervisor'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [Supervisor\DashboardController::class, 'index'])->name('dashboard');

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [Supervisor\AttendanceController::class, 'index'])->name('index');
        Route::post('/', [Supervisor\AttendanceController::class, 'store'])->name('store');
        Route::get('/history', [Supervisor\AttendanceController::class, 'history'])->name('history');
    });

    // Recitation
    Route::prefix('recitation')->name('recitation.')->group(function () {
        Route::get('/', [Supervisor\RecitationController::class, 'index'])->name('index');
        Route::post('/start', [Supervisor\RecitationController::class, 'startSession'])->name('start');
        Route::get('/new-memorization', [Supervisor\RecitationController::class, 'newMemorization'])->name('new-memorization');
        Route::get('/review', [Supervisor\RecitationController::class, 'review'])->name('review');
        Route::get('/unmastered', [Supervisor\RecitationController::class, 'unmastered'])->name('unmastered');
        Route::get('/recitation', [Supervisor\RecitationController::class, 'recitation'])->name('recitation');
        Route::get('/mafsil', [Supervisor\RecitationController::class, 'mafsil'])->name('mafsil');
        Route::post('/save', [Supervisor\RecitationController::class, 'saveSession'])->name('save');
        Route::post('/calculate-color', [Supervisor\RecitationController::class, 'calculateColor'])->name('calculate-color');
        Route::post('/get-pages', [Supervisor\RecitationController::class, 'getPages'])->name('get-pages');
        Route::get('/history', [Supervisor\RecitationController::class, 'history'])->name('history');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [Supervisor\ReportController::class, 'index'])->name('index');
        Route::get('/recitation', [Supervisor\ReportController::class, 'recitationReport'])->name('recitation');
        Route::get('/attendance', [Supervisor\ReportController::class, 'attendanceReport'])->name('attendance');
        Route::get('/student/{student}', [Supervisor\ReportController::class, 'studentReport'])->name('student');
    });

    // Warnings
    Route::resource('warnings', Supervisor\WarningController::class);
    Route::get('/warnings/{warning}/print', [Supervisor\WarningController::class, 'print'])->name('warnings.print');

    // Students (view only)
    Route::get('/students', [Supervisor\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [Supervisor\StudentController::class, 'show'])->name('students.show');

    // Notifications
    Route::get('/notifications/mark-read', [Supervisor\NotificationController::class, 'markAllRead'])->name('notifications.mark-read');
});

// ============================
// STUDENT ROUTES
// ============================
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');

    // Memorization methods (طرق الحفظ)
    Route::get('/memorization-methods', [Student\DashboardController::class, 'memorization'])->name('memorization');

    // View own sessions
    Route::get('/sessions', [Student\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [Student\SessionController::class, 'show'])->name('sessions.show');

    // View own attendance
    Route::get('/attendance', [Student\AttendanceController::class, 'index'])->name('attendance.index');

    // View own reports
    Route::get('/reports', [Student\ReportController::class, 'index'])->name('reports.index');

    // View own warnings
    Route::get('/warnings', [Student\WarningController::class, 'index'])->name('warnings.index');

    // Profile
    Route::get('/profile', [Student\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [Student\ProfileController::class, 'update'])->name('profile.update');
});

// ============================
// PARENT ROUTES
// ============================
Route::prefix('parent')->name('parent.')->middleware(['auth', 'role:parent'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [ParentPortal\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/student/{student}', [ParentPortal\DashboardController::class, 'studentDetail'])->name('student.detail');

    // View child's sessions
    Route::get('/sessions', [ParentPortal\SessionController::class, 'index'])->name('sessions.index');

    // View child's attendance
    Route::get('/attendance', [ParentPortal\AttendanceController::class, 'index'])->name('attendance.index');

    // View warnings
    Route::get('/warnings', [ParentPortal\WarningController::class, 'index'])->name('warnings.index');
    Route::get('/warnings/{warning}', [ParentPortal\WarningController::class, 'show'])->name('warnings.show');

    // Reports
    Route::get('/reports', [ParentPortal\ReportController::class, 'index'])->name('reports.index');

    // Profile
    Route::get('/profile', [ParentPortal\ProfileController::class, 'index'])->name('profile.index');
});
