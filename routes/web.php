<?php

use App\Http\Controllers\Supervisor\StudentPartRecordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\StudentPartRecordController as AdminStudentPartRecordController;
use App\Http\Controllers\Supervisor;
use App\Http\Controllers\Student;
use App\Http\Controllers\Student\StudentPartRecordController as StudentStudentPartRecordController;
use App\Http\Controllers\ParentPortal;
use App\Http\Controllers\ParentPortal\StudentPartRecordController as ParentStudentPartRecordController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;

// Root redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::middleware('throttle:5,1')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ============================
// GUEST: Yawm Al-Himma (يوم الهمة)
// ============================
Route::prefix('guest/yawm-al-himma')->name('guest.yawm-al-himma.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Guest\YawmAlHimmaController::class, 'showForm'])->name('index');
    Route::post('/parts', [\App\Http\Controllers\Guest\YawmAlHimmaController::class, 'showParts'])->name('parts');
    Route::post('/evaluate', [\App\Http\Controllers\Guest\YawmAlHimmaController::class, 'showEvaluation'])->name('evaluate');
    Route::post('/save', [\App\Http\Controllers\Guest\YawmAlHimmaController::class, 'save'])->name('save');
    Route::get('/result/{session}', [\App\Http\Controllers\Guest\YawmAlHimmaController::class, 'result'])->name('result');
});

    // Push subscription routes (authenticated)
    Route::middleware('auth')->group(function () {
        Route::post('/push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
        Route::delete('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');
        Route::get('/push/status', [PushController::class, 'status'])->name('push.status');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');

    // Notification preferences (settings page, test, update)
    Route::get('/notifications/settings', [NotificationPreferenceController::class, 'index'])->name('notifications.settings');
    Route::put('/notifications/settings', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
    Route::post('/notifications/test', [NotificationPreferenceController::class, 'test'])->name('notifications.test');

    // Chat routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::post('/chat/{conversation}/message', [ChatController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::post('/chat/{conversation}/read', [ChatController::class, 'markAsRead'])->name('chat.markAsRead');
    Route::get('/chat/users/search', [ChatController::class, 'getUsers'])->name('chat.users');
    Route::post('/chat/{conversation}/participants', [ChatController::class, 'addParticipant'])->name('chat.addParticipant');
    Route::delete('/chat/{conversation}/participants/{user}', [ChatController::class, 'removeParticipant'])->name('chat.removeParticipant');
    Route::get('/chat/unread/count', [ChatController::class, 'unreadCount'])->name('chat.unreadCount');
});

// ============================
// ADMIN ROUTES
// ============================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::resource('students', Admin\StudentController::class);
    Route::get('/students/list', [Admin\StudentController::class, 'listForBilling'])->name('students.list');

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

    // Student Parts Record
    Route::get('/student-parts', [AdminStudentPartRecordController::class, 'index'])->name('student-parts.index');
    Route::get('/student-parts/{studentId}', [AdminStudentPartRecordController::class, 'show'])->name('student-parts.show');
    Route::put('/student-parts/{studentId}/{partNumber}', [AdminStudentPartRecordController::class, 'update'])->name('student-parts.update');
    Route::post('/student-parts/{studentId}/{partNumber}/reset', [AdminStudentPartRecordController::class, 'resetToAuto'])->name('student-parts.reset');

    // Sessions
    Route::get('/sessions', [Admin\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [Admin\SessionController::class, 'show'])->name('sessions.show');

    // Meeting Minutes
    Route::prefix('meeting-minutes')->name('meeting-minutes.')->group(function () {
        Route::get('/', [Admin\MeetingMinuteController::class, 'index'])->name('index');
        Route::get('/create', [Admin\MeetingMinuteController::class, 'create'])->name('create');
        Route::post('/', [Admin\MeetingMinuteController::class, 'store'])->name('store');
        Route::get('/{meetingMinute}', [Admin\MeetingMinuteController::class, 'show'])->name('show');
        Route::get('/{meetingMinute}/edit', [Admin\MeetingMinuteController::class, 'edit'])->name('edit');
        Route::put('/{meetingMinute}', [Admin\MeetingMinuteController::class, 'update'])->name('update');
        Route::delete('/{meetingMinute}', [Admin\MeetingMinuteController::class, 'destroy'])->name('destroy');
        Route::post('/{meetingMinute}/approve', [Admin\MeetingMinuteController::class, 'approve'])->name('approve');
        Route::post('/{meetingMinute}/archive', [Admin\MeetingMinuteController::class, 'archive'])->name('archive');
    });

    // Tasks
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [Admin\TaskController::class, 'index'])->name('index');
        Route::get('/calendar', [Admin\TaskController::class, 'calendar'])->name('calendar');
        Route::post('/', [Admin\TaskController::class, 'store'])->name('store');
        Route::put('/{task}/status', [Admin\TaskController::class, 'updateStatus'])->name('updateStatus');
        Route::put('/{task}/title', [Admin\TaskController::class, 'updateTitle'])->name('updateTitle');
    });

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
        Route::post('/create-bulk-unpaid', [Admin\BillingController::class, 'createBulkUnpaid'])->name('createBulkUnpaid');
        Route::delete('/delete', [Admin\BillingController::class, 'destroy'])->name('deleteFee');
        Route::get('/students-for-parent/{parentId}', [Admin\BillingController::class, 'getStudentsForParent'])->name('studentsForParent');
        Route::get('/history', [Admin\BillingController::class, 'history'])->name('history');
        Route::get('/fee-categories', [Admin\FeeCategoryController::class, 'index'])->name('feeCategories');
        Route::post('/fee-categories', [Admin\FeeCategoryController::class, 'store'])->name('feeCategories.store');
        Route::put('/fee-categories/{feeCategory}', [Admin\FeeCategoryController::class, 'update'])->name('feeCategories.update');
        Route::post('/fee-categories/{feeCategory}/toggle-active', [Admin\FeeCategoryController::class, 'toggleActive'])->name('feeCategories.toggleActive');
        Route::delete('/fee-categories/{feeCategory}', [Admin\FeeCategoryController::class, 'destroy'])->name('feeCategories.destroy');
    });

    // Ijazah
    Route::get('/ijazah', [Admin\IjazahController::class, 'index'])->name('ijazah.index');
    Route::post('/ijazah', [Admin\IjazahController::class, 'store'])->name('ijazah.store');
    Route::delete('/ijazah', [Admin\IjazahController::class, 'destroy'])->name('ijazah.destroy');
    Route::get('/ijazah/get-students', [Admin\IjazahController::class, 'getStudentsForSupervisor'])->name('ijazah.getStudents');
    Route::get('/ijazah/get-ijazah', [Admin\IjazahController::class, 'getIjazahForStudent'])->name('ijazah.getIjazah');
    Route::post('/ijazah/assign-student', [Admin\IjazahController::class, 'assignStudentToSupervisor'])->name('ijazah.assignStudent');
    Route::post('/ijazah/remove-student', [Admin\IjazahController::class, 'removeStudentFromIjazah'])->name('ijazah.removeStudent');

    // Yawm Al-Himma (يوم الهمة)
    Route::get('/yawm-al-himma', [Admin\YawmAlHimmaController::class, 'index'])->name('yawm-al-himma.index');
    Route::get('/yawm-al-himma/day/{date}', [Admin\YawmAlHimmaController::class, 'show'])->name('yawm-al-himma.show');
    Route::delete('/yawm-al-himma/{session}', [Admin\YawmAlHimmaController::class, 'destroy'])->name('yawm-al-himma.destroy');
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
        Route::get('/mafsil-position/{mafsilPosition}/ayahs', [Supervisor\RecitationController::class, 'getMafsilPositionAyahs'])->name('mafsil-position.ayahs');
        Route::get('/tajweed-rules', [Supervisor\RecitationController::class, 'tajweedRules'])->name('tajweed-rules');
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
    Route::get('/students/search', [Supervisor\StudentController::class, 'search'])->name('students.search');
    Route::get('/students/{student}/info', [Supervisor\StudentController::class, 'apiShow'])->name('students.apiShow');

    // Notifications
    Route::get('/notifications/mark-read', [Supervisor\NotificationController::class, 'markAllRead'])->name('notifications.mark-read');

    // Profile
    Route::get('/profile', [Supervisor\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [Supervisor\ProfileController::class, 'update'])->name('profile.update');

    // Sessions
    Route::get('/sessions', [Supervisor\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [Supervisor\SessionController::class, 'show'])->name('sessions.show');

    // Student Parts Record
    Route::get('/student-parts', [StudentPartRecordController::class, 'index'])->name('student-parts.index');
    Route::get('/student-parts/{studentId}', [StudentPartRecordController::class, 'show'])->name('student-parts.show');
    Route::put('/student-parts/{studentId}/{partNumber}', [StudentPartRecordController::class, 'update'])->name('student-parts.update');
    Route::post('/student-parts/{studentId}/{partNumber}/reset', [StudentPartRecordController::class, 'resetToAuto'])->name('student-parts.reset');

    // Ijazah
    Route::get('/ijazah', [Supervisor\IjazahController::class, 'index'])->name('ijazah.index');
    Route::post('/ijazah', [Supervisor\IjazahController::class, 'store'])->name('ijazah.store');
    Route::delete('/ijazah', [Supervisor\IjazahController::class, 'destroy'])->name('ijazah.destroy');

    // Tasks (تكليفات)
    Route::get('/tasks', [Supervisor\TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/calendar', [Supervisor\TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::post('/tasks', [Supervisor\TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}/status', [Supervisor\TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

    // Yawm Al-Himma (يوم الهمة)
    Route::get('/yawm-al-himma', [Supervisor\YawmAlHimmaController::class, 'index'])->name('yawm-al-himma.index');
    Route::get('/yawm-al-himma/{session}', [Supervisor\YawmAlHimmaController::class, 'show'])->name('yawm-al-himma.show');
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

    // Student Parts Record (own record only)
    Route::get('/student-parts', [StudentStudentPartRecordController::class, 'show'])->name('student-parts');

    // Yawm Al-Himma (يوم الهمة)
    Route::get('/yawm-al-himma', [Student\YawmAlHimmaController::class, 'index'])->name('yawm-al-himma.index');
    Route::get('/yawm-al-himma/{session}', [Student\YawmAlHimmaController::class, 'show'])->name('yawm-al-himma.show');

    // Quran Reader
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

    // Azkar (أذكار المسلم)
    Route::get('/azkar', [Student\AzkarController::class, 'index'])->name('azkar');
    Route::get('/azkar/{id}', [Student\AzkarController::class, 'category'])->name('azkar.category');
    Route::post('/azkar/track', [Student\AzkarController::class, 'track'])->name('azkar.track');
    Route::post('/azkar/reset', [Student\AzkarController::class, 'resetStats'])->name('azkar.reset');

    // Achievements
    Route::get('/achievements', [Student\AchievementController::class, 'index'])->name('achievements');

    // Smart Review Scheduler (SM-2)
    Route::get('/reviews', [Student\ReviewController::class, 'index'])->name('reviews.index');

    // Tasks Calendar (مهام الطالب)
    Route::get('/tasks/calendar', [Student\TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::post('/tasks', [Student\TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}/status', [Student\TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::delete('/tasks/{task}', [Student\TaskController::class, 'destroy'])->name('tasks.destroy');
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

    // Tarteel Center Pages
    Route::get('/voice-search', [Student\VoiceSearchController::class, 'index'])->name('voice-search');
    Route::get('/tafseer', [Student\TafseerController::class, 'index'])->name('tafseer');
    Route::get('/tarteel-challenges', [Student\TarteelCenterController::class, 'challengesPage'])->name('tarteel-challenges');
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

    // Student Parts Record
    Route::get('/student-parts', [ParentStudentPartRecordController::class, 'index'])->name('student-parts.index');
    Route::get('/student-parts/{studentId}', [ParentStudentPartRecordController::class, 'show'])->name('student-parts.show');
    Route::put('/student-parts/{studentId}/{partNumber}', [ParentStudentPartRecordController::class, 'update'])->name('student-parts.update');

    // Yawm Al-Himma (يوم الهمة)
    Route::get('/yawm-al-himma', [ParentPortal\YawmAlHimmaController::class, 'index'])->name('yawm-al-himma.index');
    Route::get('/yawm-al-himma/{session}', [ParentPortal\YawmAlHimmaController::class, 'show'])->name('yawm-al-himma.show');

    // Billing
    Route::get('/billing', [ParentPortal\BillingController::class, 'index'])->name('billing');
});
