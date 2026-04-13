<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Supervisor;
use App\Http\Controllers\Student;
use App\Http\Controllers\ParentPortal;
use App\Http\Controllers\PushController;
use App\Http\Controllers\NotificationController;

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Push subscription routes (authenticated)
Route::middleware('auth')->group(function () {
    Route::post('/push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::delete('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
});

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
    Route::get('/warnings/attendance/{student}', [Admin\WarningController::class, 'getAttendance'])->name('warnings.attendance');
    Route::get('/warnings/recitations/{student}', [Admin\WarningController::class, 'getRecitations'])->name('warnings.recitations');

    // Warning Rules
    Route::resource('warning-rules', Admin\WarningRuleController::class);
    Route::post('warning-rules/{id}/toggle', [Admin\WarningRuleController::class, 'toggle'])->name('warning-rules.toggle');
    Route::post('warning-rules/test', [Admin\WarningRuleController::class, 'test'])->name('warning-rules.test');

    // Settings
    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

    // Notifications
    Route::get('/notifications/mark-read', [Admin\NotificationController::class, 'markAllRead'])->name('notifications.mark-read');

    // Profile
    Route::get('/profile', [Admin\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

    // Sessions
    Route::get('/sessions', [Admin\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [Admin\SessionController::class, 'show'])->name('sessions.show');

    // Audit Logs
    Route::resource('audit-logs', Admin\AuditLogController::class)->only(['index', 'show']);
    Route::get('/audit-logs/export', [Admin\AuditLogController::class, 'export'])->name('audit-logs.export');

    // Billing
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [Admin\BillingController::class, 'index'])->name('index');
        Route::get('/settings', [Admin\BillingController::class, 'settings'])->name('settings');
        Route::put('/settings', [Admin\BillingController::class, 'updateSettings'])->name('settings.update');
        Route::post('/mark-paid', [Admin\BillingController::class, 'markPaid'])->name('markPaid');
        Route::post('/mark-paid-bulk', [Admin\BillingController::class, 'markPaidBulk'])->name('markPaidBulk');
        Route::post('/record-payment', [Admin\BillingController::class, 'recordPayment'])->name('recordPayment');
        Route::get('/students-for-parent/{parentId}', [Admin\BillingController::class, 'getStudentsForParent'])->name('studentsForParent');
        Route::get('/history', [Admin\BillingController::class, 'history'])->name('history');
    });
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
        Route::post('/holiday', [Supervisor\AttendanceController::class, 'holiday'])->name('holiday');
        Route::get('/history', [Supervisor\AttendanceController::class, 'history'])->name('history');
        Route::delete('/{attendance}', [Supervisor\AttendanceController::class, 'destroy'])->name('destroy');
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
        Route::post('/mafsil', [Supervisor\RecitationController::class, 'mafsil'])->name('mafsil.post');
        Route::post('/save', [Supervisor\RecitationController::class, 'saveSession'])->name('save');
        Route::post('/save-incomplete', [Supervisor\RecitationController::class, 'saveIncomplete'])->name('saveIncomplete');
        Route::post('/save-failed-continue', [Supervisor\RecitationController::class, 'saveFailedContinue'])->name('saveFailedContinue');
        Route::get('/not-recitate', [Supervisor\RecitationController::class, 'notRecitate'])->name('not-recitate');
        Route::post('/save-not-recitate', [Supervisor\RecitationController::class, 'saveNotRecitate'])->name('save-not-recitate');
        Route::post('/calculate-color', [Supervisor\RecitationController::class, 'calculateColor'])->name('calculate-color');
        Route::post('/get-pages', [Supervisor\RecitationController::class, 'getPages'])->name('get-pages');
        Route::get('/history', [Supervisor\RecitationController::class, 'history'])->name('history');
        Route::delete('/{recitation}', [Supervisor\RecitationController::class, 'destroy'])->name('destroy');
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
    Route::get('/warnings/attendance/{student}', [Supervisor\WarningController::class, 'getAttendance'])->name('warnings.attendance');
    Route::get('/warnings/recitations/{student}', [Supervisor\WarningController::class, 'getRecitations'])->name('warnings.recitations');
    Route::get('/warnings/student-stats/{student}', [Supervisor\WarningController::class, 'getStudentStats'])->name('warnings.studentStats');

    // Warning Drafts
    Route::get('warning-drafts', [Supervisor\WarningDraftController::class, 'index'])->name('warning-drafts.index');
    Route::get('warning-drafts/{id}', [Supervisor\WarningDraftController::class, 'show'])->name('warning-drafts.show');
    Route::post('warning-drafts/{id}/approve', [Supervisor\WarningDraftController::class, 'approve'])->name('warning-drafts.approve');
    Route::post('warning-drafts/{id}/dismiss', [Supervisor\WarningDraftController::class, 'dismiss'])->name('warning-drafts.dismiss');

    // Students (view only)
    Route::get('/students', [Supervisor\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [Supervisor\StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [Supervisor\StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [Supervisor\StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [Supervisor\StudentController::class, 'update'])->name('students.update');
    Route::get('/students/{student}', [Supervisor\StudentController::class, 'show'])->name('students.show');
    Route::delete('/students/{student}', [Supervisor\StudentController::class, 'destroy'])->name('students.destroy');

    // Notifications
    Route::get('/notifications/mark-read', [Supervisor\NotificationController::class, 'markAllRead'])->name('notifications.mark-read');

    // Profile
    Route::get('/profile', [Supervisor\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [Supervisor\ProfileController::class, 'update'])->name('profile.update');

    // Sessions
    Route::get('/sessions', [Supervisor\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [Supervisor\SessionController::class, 'show'])->name('sessions.show');
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
    Route::get('/warnings/{warning}', [Student\WarningController::class, 'show'])->name('warnings.show');

    // Profile
    Route::get('/profile', [Student\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [Student\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Student\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Quran Reader
    Route::get('/quran', [Student\QuranReaderController::class, 'index'])->name('quran');
    Route::get('/quran/page/{page}', [Student\QuranReaderController::class, 'page'])->name('quran.page');
    Route::get('/quran/part/{part}', [Student\QuranReaderController::class, 'part'])->name('quran.part');

    // Live Recitation (real-time evaluation)
    Route::get('/live-recitation', [Student\LiveRecitationController::class, 'index'])->name('live-recitation');
    Route::get('/live-recitation/token', [Student\LiveRecitationController::class, 'getToken'])->name('live-recitation.token');

    // Progress & Goals
    Route::get('/progress', [Student\ProgressController::class, 'index'])->name('progress');
    Route::get('/progress/goals', [Student\ProgressController::class, 'goals'])->name('progress.goals');
    Route::put('/progress/goals', [Student\ProgressController::class, 'updateGoal'])->name('progress.goals.update');

    // Bookmarks
    Route::get('/bookmarks', [Student\BookmarkController::class, 'index'])->name('bookmarks');
    Route::post('/bookmarks', [Student\BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::put('/bookmarks/{id}', [Student\BookmarkController::class, 'update'])->name('bookmarks.update');
    Route::delete('/bookmarks/{id}', [Student\BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
    Route::get('/bookmarks/export', [Student\BookmarkController::class, 'export'])->name('bookmarks.export');

    // Achievements
    Route::get('/achievements', [Student\AchievementController::class, 'index'])->name('achievements');

    // Smart Review Scheduler (SM-2)
    Route::get('/reviews', [Student\ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/session', [Student\ReviewController::class, 'session'])->name('reviews.session');
    Route::post('/reviews/{reviewItem}/submit', [Student\ReviewController::class, 'submitReview'])->name('reviews.submit');
    Route::post('/reviews/add', [Student\ReviewController::class, 'addToReviews'])->name('reviews.add');
    Route::delete('/reviews/{reviewItem}', [Student\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Weak Areas Detection
    Route::get('/weak-areas', [Student\WeakAreaController::class, 'index'])->name('weak-areas.index');
    Route::post('/weak-areas/{weakArea}/mark', [Student\WeakAreaController::class, 'markRemediated'])->name('weak-areas.mark');
    Route::get('/weak-areas/refresh', [Student\WeakAreaController::class, 'refresh'])->name('weak-areas.refresh');

    // Voice Pronunciation Analysis
    Route::get('/pronunciation', [Student\PronunciationController::class, 'index'])->name('pronunciation.index');
    Route::get('/pronunciation/practice', [Student\PronunciationController::class, 'practice'])->name('pronunciation.practice');
    Route::get('/pronunciation/verse', [Student\PronunciationController::class, 'getVerse'])->name('pronunciation.verse');
    Route::post('/pronunciation', [Student\PronunciationController::class, 'store'])->name('pronunciation.store');
    Route::get('/pronunciation/{attempt}/results', [Student\PronunciationController::class, 'results'])->name('pronunciation.results');
    Route::delete('/pronunciation/{attempt}', [Student\PronunciationController::class, 'destroy'])->name('pronunciation.destroy');

    // Leaderboards
    Route::get('/leaderboard', [Student\LeaderboardController::class, 'index'])->name('leaderboard.index');

    // Peer Challenges
    Route::get('/challenges', [Student\ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/challenges/create', [Student\ChallengeController::class, 'create'])->name('challenges.create');
    Route::post('/challenges', [Student\ChallengeController::class, 'store'])->name('challenges.store');
    Route::get('/challenges/{challenge}', [Student\ChallengeController::class, 'show'])->name('challenges.show');
    Route::post('/challenges/{challenge}/accept', [Student\ChallengeController::class, 'accept'])->name('challenges.accept');
    Route::post('/challenges/{challenge}/decline', [Student\ChallengeController::class, 'decline'])->name('challenges.decline');

    // Group Events
    Route::get('/events', [Student\GroupEventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [Student\GroupEventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/join', [Student\GroupEventController::class, 'join'])->name('events.join');
    Route::post('/events/{event}/leave', [Student\GroupEventController::class, 'leave'])->name('events.leave');
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
    Route::get('/sessions/{session}', [ParentPortal\SessionController::class, 'show'])->name('sessions.show');

    // View child's attendance
    Route::get('/attendance', [ParentPortal\AttendanceController::class, 'index'])->name('attendance.index');

    // View warnings
    Route::get('/warnings', [ParentPortal\WarningController::class, 'index'])->name('warnings.index');
    Route::get('/warnings/{warning}', [ParentPortal\WarningController::class, 'show'])->name('warnings.show');

    // Reports
    Route::get('/reports', [ParentPortal\ReportController::class, 'index'])->name('reports.index');

    // Profile
    Route::get('/profile', [ParentPortal\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ParentPortal\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ParentPortal\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Billing
    Route::get('/billing', [ParentPortal\BillingController::class, 'index'])->name('billing');
});
