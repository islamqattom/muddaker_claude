<div class="tarteeel-tab-content" id="memorization-tab">
    <div class="row g-4">
        <div class="col-lg-4">
            <!-- Pomodoro Timer Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>مؤقت الحفظ المتقن</h5>
                </div>
                <div class="card-body">
                    <div class="pomodoro-container">
                        <div class="pomodoro-timer" id="pomodoroTimer">25:00</div>
                        <div class="pomodoro-label" id="pomodoroLabel">مؤقت الحفظ</div>
                        <div class="pomodoro-controls">
                            <button class="pomodoro-btn start" id="pomodoroStart">
                                <i class="fas fa-play me-1"></i>ابدأ
                            </button>
                            <button class="pomodoro-btn reset d-none" id="pomodoroReset">
                                <i class="fas fa-redo me-1"></i>إعادة
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <small class="text-muted">جلسة: 25 دقيقة</small>
                        <small class="text-muted">استراحة: 5 دقائق</small>
                    </div>
                </div>
            </div>

            <!-- Daily Goal Card -->
            <div class="goal-progress-card">
                <div class="goal-header">
                    <span class="goal-title">هدف اليوم</span>
                    <span class="goal-value" id="dailyGoalProgress">0 / 20</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" id="goalProgressBar" style="width: 0%" role="progressbar"></div>
                </div>
                <small class="text-muted mt-2 d-block" id="goalHint">حدد هدفك اليومي من الآيات</small>
            </div>

            <!-- Last Memorized -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>آخر ما تم حفظه</h5>
                </div>
                <div class="card-body" id="lastMemorizedSection">
                    <div class="text-center text-muted">
                        <i class="fas fa-book fa-2x mb-2 opacity-25"></i>
                        <p>لم يتم الحفظ بعد</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Memorization Stats -->
            <div class="memorization-stats">
                <div class="memorization-stat">
                    <div class="stat-value" id="statCompletedParts">0</div>
                    <div class="stat-label">الأجزاء المتقنة</div>
                </div>
                <div class="memorization-stat">
                    <div class="stat-value" id="statTotalParts">0</div>
                    <div class="stat-label">إجمالي الأجزاء</div>
                </div>
                <div class="memorization-stat">
                    <div class="stat-value" id="statDueReviews">0</div>
                    <div class="stat-label">مراجعات مستحقة</div>
                </div>
                <div class="memorization-stat">
                    <div class="stat-value" id="statRecentMemorization">0</div>
                    <div class="stat-label">حفظ هذا الأسبوع</div>
                </div>
            </div>

            <!-- Smart Review Schedule -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-sync-alt me-2"></i>الجدول المراجعي الذكي</h5>
                    <a href="{{ route('student.reviews.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    <div id="reviewScheduleList">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                            <p>جارٍ تحميل المراجعات...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Memorization Plan -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-tasks me-2"></i>خطة الحفظ الشخصية</h5>
                    <button class="btn btn-sm btn-outline-primary" id="refreshMemorizationPlan">
                        <i class="fas fa-redo me-1"></i>تحديث
                    </button>
                </div>
                <div class="card-body">
                    <div id="memorizationPlanList" class="row g-3">
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                            <p>جارٍ تحميل خطة الحفظ...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>