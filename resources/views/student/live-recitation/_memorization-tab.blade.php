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
        <!-- ==================== التسميع الذكي والمتابعة الصوتية ==================== -->
        <div class="card mb-4" id="smartMemorizationCard">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-brain me-2"></i>التسميع الذكي والمتابعة الصوتية</h5>
                <span class="badge bg-secondary" id="smartMemoStatus">غير نشط</span>
            </div>
            <div class="card-body">
                <!-- اختيار الآيات -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-12">
                        <label class="form-label">السورة</label>
                        <select class="form-select" id="smartMemoSurah">
                            <option value="">اختر السورة</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">من آية</label>
                        <input type="number" class="form-control" id="smartMemoFrom" value="1" min="1">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">إلى آية</label>
                        <input type="number" class="form-control" id="smartMemoTo" value="3" min="1">
                        <small id="smartMemoToHint" class="text-muted"></small>
                    </div>
                    <div class="col-md-2 col-12 d-flex align-items-end">
                        <button class="btn btn-outline-primary w-100" id="smartMemoLoadBtn">
                            <i class="fas fa-eye me-1"></i>عرض
                        </button>
                    </div>
                </div>

                <!-- أزرار التحكم -->
                <div class="d-flex justify-content-center align-items-center gap-3 mb-4 flex-wrap">
                    <button class="btn btn-success btn-lg" id="smartMemoStartBtn" disabled>
                        <i class="fas fa-microphone me-2"></i>بدء التسميع الذكي
                    </button>
                    <button class="btn btn-danger btn-lg d-none" id="smartMemoStopBtn">
                        <i class="fas fa-stop me-2"></i>إيقاف
                    </button>
                    <button class="btn btn-outline-warning d-none" id="smartMemoHintBtn" title="التلقين / المساعدة (H)">
                        <i class="fas fa-lightbulb me-1"></i>مساعدة
                    </button>
                </div>

                <!-- شريط التقدم -->
                <div class="progress-wrapper mb-3">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" id="smartMemoProgressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="progress-text small text-muted mt-1 d-block text-center" id="smartMemoProgressText">0 / 0 كلمة (0%)</span>
                </div>

                <!-- منطقة عرض النص -->
                <div id="smartMemoTextArea" class="smart-memo-text-area text-center p-4 mb-3" dir="rtl">
                    <div class="text-muted">
                        <i class="fas fa-book-quran fa-3x mb-3 opacity-25"></i>
                        <p>اختر السورة والآيات ثم اضغط "عرض" لبدء التسميع الذكي</p>
                    </div>
                </div>

                <!-- حالة التعرف على الصوت -->
                <div class="text-center">
                    <div id="smartMemoListeningIndicator" class="d-none">
                        <span class="listening-pulse me-2"></span>
                        <span class="text-muted">جارٍ الاستماع... اقرأ بصوت واضح</span>
                    </div>
                    <div id="smartMemoTranscript" class="smart-memo-transcript mt-2"></div>
                </div>
            </div>
        </div>

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