<div class="row g-4">
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 1rem;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>قواعد التجويد</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="tajweedRulesList" style="max-height: 60vh; overflow-y: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">جارٍ التحميل...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">جارٍ تحميل القواعد...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4" id="tajweedRuleDetail">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="ruleDetailTitle"><i class="fas fa-info-circle me-2"></i>تفاصيل القاعدة</h5>
                <button class="btn btn-sm btn-outline-secondary" id="closeRuleDetail">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-success me-2" id="ruleDetailCategory"></span>
                    <span class="badge bg-outline-secondary" id="ruleDetailType"></span>
                </div>
                <p class="lead" id="ruleDetailDescription"></p>

                <div class="tajweed-example bg-light rounded p-4 mt-4" id="tajweedExampleSection" style="display: none;">
                    <h6 class="text-success mb-3"><i class="fas fa-quote-right me-2"></i>مثال تطبيقي</h6>
                    <div class="verse-text mb-2" id="ruleDetailVerse" style="font-family: 'Amiri', serif; font-size: 1.6rem; line-height: 2;"></div>
                    <small class="text-muted" id="ruleDetailVerseRef"></small>
                </div>

                <div class="mt-4" id="ruleExamplesSection" style="display: none;">
                    <h6 class="mb-3"><i class="fas fa-list me-2"></i>أمثلة إضافية</h6>
                    <ul class="list-group" id="ruleExamplesList"></ul>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-language me-2"></i>التشكيل التفاعلي</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">اختر آية لعرضها بالتشكيل الكامل مع إمكانية التشغيل الصوتي</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label">السورة</label>
                        <select class="form-select" id="tajweedSurahSelect">
                            <option value="">اختر السورة</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">رقم الآية</label>
                        <input type="number" class="form-control" id="tajweedVerseNumber" value="1" min="1">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-success w-100" id="loadTajweedVerse">
                            <i class="fas fa-search me-2"></i>عرض الآية
                        </button>
                    </div>
                </div>
                <div class="text-center p-4 bg-light rounded" id="tajweedVerseDisplay" style="display: none;">
                    <div class="mb-3" dir="rtl">
                        <span class="verse-text" id="tajweedVerseText" style="font-family: 'Amiri', serif; font-size: 1.8rem; line-height: 2.2;"></span>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" id="playTajweedAudio">
                            <i class="fas fa-volume-up me-1"></i>تشغيل
                        </button>
                        <button class="btn btn-sm btn-outline-primary" id="copyTajweedVerse">
                            <i class="fas fa-copy me-1"></i>نسخ
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>اختبر معلوماتك</h5>
            </div>
            <div class="card-body text-center" id="tajweedQuizSection">
                <div id="quizPlaceholder">
                    <i class="fas fa-question-circle fa-4x mb-3 text-success opacity-25"></i>
                    <h5>اختبر معلوماتك في التجويد</h5>
                    <p class="text-muted mb-3">اضغط على الزر لبدء اختبار عشوائي من القواعد</p>
                    <button class="btn btn-success" id="startTajweedQuiz">
                        <i class="fas fa-play me-2"></i>ابدأ الاختبار
                    </button>
                </div>
                <div id="quizContent" style="display: none;">
                    <div class="mb-4">
                        <span class="badge bg-success mb-2" id="quizQuestionNum">السؤال 1</span>
                        <h5 id="quizQuestionText" class="mb-0"></h5>
                    </div>
                    <div class="list-group mb-3" id="quizOptions"></div>
                    <div class="alert mb-3" id="quizFeedback" style="display: none;"></div>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-primary" id="nextQuizQuestion" style="display: none;">
                            التالي <i class="fas fa-arrow-right me-1"></i>
                        </button>
                        <button class="btn btn-success" id="finishQuiz" style="display: none;">
                            <i class="fas fa-check me-1"></i>إنهاء الاختبار
                        </button>
                    </div>
                </div>
                <div id="quizResults" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('closeRuleDetail');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            document.getElementById('tajweedRuleDetail').style.display = 'none';
        });
    }
});
</script>