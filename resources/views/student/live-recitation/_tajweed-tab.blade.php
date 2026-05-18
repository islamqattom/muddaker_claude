<div class="tarteeel-tab-content" id="tajweed-tab">
    <div class="row g-4">
        <div class="col-lg-4">
            <!-- Tajweed Rules List -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book-open me-2"></i>قواعد التجويد</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="tajweedRulesList">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="text-muted mt-2">جارٍ تحميل القواعد...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Tajweed Rule Detail -->
            <div class="card mb-4" id="tajweedRuleDetail" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 id="ruleDetailTitle"><i class="fas fa-book me-2"></i>تفاصيل القاعدة</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#tajweedRuleDetail').hide();">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span class="badge bg-primary mb-2" id="ruleDetailCategory"></span>
                        <p class="rule-description" id="ruleDetailDescription"></p>
                    </div>

                    <div class="tajweed-example" id="tajweedExampleSection">
                        <h6 class="mb-3"><i class="fas fa-quote-right me-2"></i>مثال تطبيقي</h6>
                        <div class="verse-text" id="ruleDetailVerse"></div>
                        <small class="text-muted d-block mt-2 text-center" id="ruleDetailVerseRef"></small>
                    </div>

                    <div class="mt-4" id="ruleExamplesSection">
                        <h6 class="mb-3"><i class="fas fa-list me-2"></i>أمثلة إضافية</h6>
                        <ul class="list-group" id="ruleExamplesList"></ul>
                    </div>
                </div>
            </div>

            <!-- Interactive Shadda Display -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-eye me-2"></i>التشكيل التفاعلي</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">اختر آية لعرضها بالتشكيل الكامل:</p>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="tajweedSurahSelect">
                                <option value="">اختر السورة</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="tajweedVerseNumber" placeholder="رقم الآية" min="1">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" id="loadTajweedVerse">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="tajweedVerseDisplay" class="text-center" style="display: none;">
                        <div class="verse-text" id="tajweedVerseText" style="font-size: 1.8rem; line-height: 2.2;"></div>
                        <div class="mt-3">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-secondary" id="toggleTashkeel">إظهار/إخفاء التشكيل</button>
                                <button class="btn btn-sm btn-outline-secondary" id="playTajweedAudio">
                                    <i class="fas fa-volume-up me-1"></i>تشغيل الصوت
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tajweed Quiz -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-question-circle me-2"></i>اختبار قصير على التجويد</h5>
                </div>
                <div class="card-body" id="tajweedQuizSection">
                    <div class="text-center text-muted" id="quizPlaceholder">
                        <i class="fas fa-graduation-cap fa-3x mb-3 opacity-25"></i>
                        <h5>اختبر معلوماتك</h5>
                        <p class="text-muted">اضغط على الزر لبدء اختبار عشوائي</p>
                        <button class="btn btn-primary" id="startTajweedQuiz">
                            <i class="fas fa-play me-2"></i>ابدأ الاختبار
                        </button>
                    </div>
                    <div id="quizContent" style="display: none;">
                        <div class="mb-4">
                            <span class="badge bg-info mb-2" id="quizQuestionNum">السؤال 1</span>
                            <h5 id="quizQuestionText"></h5>
                        </div>
                        <div class="list-group" id="quizOptions"></div>
                        <div class="mt-3" id="quizFeedback" style="display: none;">
                            <div class="alert" role="alert" id="quizFeedbackText"></div>
                        </div>
                        <div class="mt-3 text-center" id="quizNavigation">
                            <button class="btn btn-primary" id="nextQuizQuestion" style="display: none;">
                                السؤال التالي <i class="fas fa-arrow-right me-1"></i>
                            </button>
                            <button class="btn btn-success" id="finishQuiz" style="display: none;">
                                <i class="fas fa-check me-1"></i>إنهاء الاختبار
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>