<div class="tarteeel-tab-content active" id="recitation-tab">
    <div class="row g-4">
        <!-- Settings Sidebar -->
        <div class="col-lg-4">
            <!-- Settings Card -->
            <div class="card settings-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-sliders-h me-2"></i>إعدادات التسميع</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">نوع التسميع</label>
                        <select class="form-select" id="modeSelect" aria-describedby="modeHelp">
                            <option value="page">صفحة واحدة</option>
                            <option value="range">نطاق صفحات</option>
                            <option value="surah">سورة</option>
                        </select>
                        <small id="modeHelp" class="text-muted">اختر نوع التسميع</small>
                    </div>

                    <div class="mb-3" id="pageInputs">
                        <label class="form-label" for="startPage">من صفحة</label>
                        <input type="number" class="form-control" id="startPage" value="1" min="1" max="604" aria-describedby="pageHelp">
                        <small id="pageHelp" class="text-muted">رقم الصفحة (1-604)</small>
                    </div>

                    <div class="mb-3 d-none" id="endPageInput">
                        <label class="form-label" for="endPage">إلى صفحة</label>
                        <input type="number" class="form-control" id="endPage" value="1" min="1" max="604">
                    </div>

                    <div class="mb-3 d-none" id="surahInputs">
                        <label class="form-label" for="surahSelect">السورة</label>
                        <select class="form-select" id="surahSelect">
                            <option value="">اختر السورة</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="verseRangeInputs">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" for="verseStart">من آية</label>
                                <input type="number" class="form-control" id="verseStart" value="1" min="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="verseEnd">إلى آية</label>
                                <input type="number" class="form-control" id="verseEnd" value="1" min="1">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="similarityThreshold">
                            عتبة التشابه
                            <span class="threshold-value" id="thresholdValue">70%</span>
                        </label>
                        <input type="range" class="form-range" id="similarityThreshold" value="70" min="50" max="100" step="5" aria-describedby="thresholdHelp">
                        <small id="thresholdHelp" class="text-muted">إذا كان التشابه أقل من هذه القيمة يعتبر خطأ</small>
                    </div>

                    <button class="btn btn-outline-primary w-100 mb-2" id="previewVerses" aria-describedby="previewHelp">
                        <i class="fas fa-eye me-2"></i>عرض الآيات
                    </button>
                    <small id="previewHelp" class="text-muted d-block mb-3 text-center">اضغط لمعاينة الآيات قبل البدء</small>

                    <button class="btn btn-start w-100" id="startRecitation" disabled aria-describedby="startHelp">
                        <i class="fas fa-microphone me-2"></i>ابدأ التسميع
                    </button>
                    <small id="startHelp" class="text-muted d-block mt-2 text-center">اضغط للبدء أو اضغط空格</small>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card status-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-info-circle me-2"></i>حالة التسميع</h5>
                    <span class="status-indicator" id="statusIndicator"></span>
                </div>
                <div class="card-body text-center">
                    <div id="recitationStatus" class="status-text mb-3">في انتظار البدء</div>

                    <div class="progress-wrapper mb-3">
                        <div class="progress">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="progress-text" id="recitationProgress">0 / 0</span>
                    </div>

                    <div class="stats-row mb-3">
                        <div class="stat-item">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            <span>الآيات: <span id="versesCount">0</span></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-times-circle text-danger me-1"></i>
                            <span>الأخطاء: <span id="errorCount">0</span></span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary d-none" id="nextVerse">
                            <i class="fas fa-forward me-1"></i>التالي
                        </button>
                        <button class="btn btn-stop d-none" id="stopRecitation">
                            <i class="fas fa-stop me-1"></i>إنهاء التسميع
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Current Verse Display -->
            <div class="card verses-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-book-quran me-2"></i>الآيات</h5>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted hint-text">اضغط على آية للانتقال إليها</small>
                        <div class="btn-group btn-group-sm" role="group" aria-label="عرض الآيات" id="viewModeToggle">
                            <button type="button" class="btn btn-outline-secondary active" id="viewModeCards" title="عرض البطاقات" onclick="window.toggleViewMode('cards')">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="viewModeQuran" title="عرض مصحف" onclick="window.toggleViewMode('quran')">
                                <i class="fas fa-book-open"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div id="currentVerse">
                        <div class="idle-state">
                            <i class="fas fa-book-quran fa-4x mb-3 opacity-25"></i>
                            <h5>اختر الإعدادات ثم اضغط "عرض الآيات"</h5>
                            <p class="text-muted">ستظهر الآيات هنا للمعاينة قبل بدء التسميع</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transcript Display -->
            <div class="card transcript-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-comment-dots me-2"></i>ما تم نطقه</h5>
                    <button class="btn btn-sm btn-outline-secondary" id="clearTranscript" title="مسح النص">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div id="transcript" class="transcript-text" dir="rtl" role="status" aria-live="polite">
                        <span class="text-muted">جارٍ الاستماع...</span>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card instructions-card">
                <div class="card-header">
                    <h5><i class="fas fa-question-circle me-2"></i>كيفية الاستخدام</h5>
                </div>
                <div class="card-body">
                    <ol class="instructions-list mb-0">
                        <li class="mb-2">
                            <span class="step-number">١</span>
                            <span>اختر نوع التسميع (صفحة أو نطاق أو سورة)</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٢</span>
                            <span>اضغط "عرض الآيات" لمعاينة الآيات قبل البدء</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٣</span>
                            <span>اضغط "ابدأ التسميع" وامنح الإذن لاستخدام الميكروفون</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٤</span>
                            <span>ابدأ بتلاوة الآية الأولى - ستُظلل بالأخضر</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">5</span>
                            <span>سيتم الاستماع إليك ومطابقة نطقك مع الآيات تلقائياً</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">6</span>
                            <span>عند اكتشاف خطأ، ستسمع صوت تنبيه وتُضاء الآية باللون الأحمر</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">7</span>
                            <span>عند إكمال آية بنجاح، يتم الانتقال تلقائياً للآية التالية</span>
                        </li>
                        <li>
                            <span class="step-number">٨</span>
                            <span>عند الانتهاء، سيظهر تقرير مفصل بالأخطاء</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>