<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-clock me-2"></i>مؤقت الحفظ المتقن</h5>
            </div>
            <div class="card-body text-center">
                <div class="pomodoro-timer" id="pomodoroTimer" style="font-size: 3.5rem; font-weight: 700; color: #1a7a4a; font-family: 'Segoe UI', sans-serif;">25:00</div>
                <div class="pomodoro-label text-muted mb-4" id="pomodoroLabel">جلسة حفظ متقنة</div>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-success" id="pomodoroStart">
                        <i class="fas fa-play me-1"></i>ابدأ
                    </button>
                    <button class="btn btn-outline-secondary d-none" id="pomodoroReset">
                        <i class="fas fa-redo me-1"></i>إعادة
                    </button>
                </div>
                <div class="d-flex justify-content-between mt-3 small text-muted">
                    <span>جلسة: 25 دقيقة</span>
                    <span>استراحة: 5 دقائق</span>
                </div>
            </div>
        </div>

        <div class="card border-success mb-4" style="background: linear-gradient(135deg, #e8f5ec 0%, #d4edd9 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="fas fa-bullseye me-2"></i>هدف اليوم</h6>
                    <span class="badge bg-success" id="dailyGoalBadge">20 آية</span>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-success" id="goalProgressBar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted" id="currentProgress">0 / 20</small>
                    <small class="text-success" style="cursor: pointer;" id="goalHint">تعديل الهدف</small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>آخر ما تم حفظه</h5>
            </div>
            <div class="card-body" id="lastMemorizedSection">
                <div class="text-center text-muted">
                    <i class="fas fa-book fa-2x mb-2 opacity-25"></i>
                    <p class="mb-0">لم يتم الحفظ بعد</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="stat-value text-success" id="statCompletedParts" style="font-size: 2rem; font-weight: 700;">0</div>
                        <div class="stat-label small text-muted">الأجزاء المتقنة</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="stat-value text-primary" id="statTotalParts" style="font-size: 2rem; font-weight: 700;">0</div>
                        <div class="stat-label small text-muted">إجمالي الأجزاء</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="stat-value text-warning" id="statDueReviews" style="font-size: 2rem; font-weight: 700;">0</div>
                        <div class="stat-label small text-muted">مراجعات مستحقة</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="stat-value text-info" id="statRecentMemorization" style="font-size: 2rem; font-weight: 700;">0</div>
                        <div class="stat-label small text-muted">حفظ هذا الأسبوع</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-sync-alt me-2"></i>الجدول المراجعي الذكي</h5>
                <a href="{{ route('student.reviews.index') }}" class="btn btn-sm btn-outline-primary">
                    عرض الكل <i class="fas fa-external-link-alt me-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الجزء</th>
                                <th>السورة</th>
                                <th>تاريخ المراجعة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody id="reviewScheduleList">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin me-2"></i>جارٍ تحميل المراجعات...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>خطة الحفظ الشخصية</h5>
                <button class="btn btn-sm btn-outline-primary" id="refreshMemorizationPlan">
                    <i class="fas fa-redo me-1"></i>تحديث
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3" id="memorizationPlanList">
                    <div class="col-12 text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin me-2"></i>جارٍ تحميل خطة الحفظ...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>