(function() {
    'use strict';

    const TajweedTab = {
        currentRule: null,
        quizAttemptId: null,
        quizQuestions: [],
        currentQuizIndex: 0,
        quizSelectedAnswers: [],
        questionStartedAt: null,
        categories: [],

        init: function() {
            this.loadTajweedRules();
            this.loadCategories();
            this.initQuizControls();
        },

        getApiToken: function() {
            return document.querySelector('meta[name=api-token]')?.content || '';
        },

        getFetchOptions: function(method, body) {
            const apiToken = this.getApiToken();
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };
            if (apiToken) {
                headers['Authorization'] = 'Bearer ' + apiToken;
            }
            const opts = { method: method || 'GET', headers: headers, credentials: 'include' };
            if (body) {
                headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(body);
            }
            return opts;
        },

        apiRequest: function(url, method, body) {
            return fetch(url, this.getFetchOptions(method, body)).then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        const error = new Error('HTTP ' + res.status + ': ' + (text || res.statusText));
                        error.status = res.status;
                        error.body = text;
                        throw error;
                    });
                }
                return res.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        const error = new Error('Invalid JSON response: ' + (text || '').substring(0, 200));
                        error.status = res.status;
                        error.body = text;
                        throw error;
                    }
                });
            });
        },

        loadCategories: function() {
            this.apiRequest('/api/student/tarteel/tajweed-rules').then(data => {
                if (data && data.success && data.data && data.data.categories) {
                    this.categories = data.data.categories;
                    const select = document.getElementById('quizCategorySelect');
                    if (select) {
                        select.innerHTML = '<option value="">جميع الفئات</option>' +
                            this.categories.map(c => `<option value="${c}">${c}</option>`).join('');
                    }
                }
            }).catch(() => {});
        },

        loadTajweedRules: function() {
            const container = document.getElementById('tajweedRulesList');
            if (!container) return;

            this.apiRequest('/api/student/tarteel/tajweed-rules').then(data => {
                const rules = data && data.data ? (Array.isArray(data.data) ? data.data : (data.data.rules || [])) : [];
                if (data && data.success && rules.length > 0) {
                    this.renderTajweedRules(rules);
                } else {
                    container.innerHTML = `
                        <div class="text-center py-4 px-3">
                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">لا توجد قواعد تجويد متوفرة</p>
                        </div>
                    `;
                }
            }).catch(err => {
                console.error('Error loading tajweed rules:', err);
                container.innerHTML = `
                    <div class="text-center py-4 px-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                        <p class="text-danger mb-0">فشل في تحميل القواعد</p>
                    </div>
                `;
            });
        },

        renderTajweedRules: function(rules) {
            const container = document.getElementById('tajweedRulesList');
            if (!container) return;

            const categories = {};
            rules.forEach(rule => {
                if (!categories[rule.category]) {
                    categories[rule.category] = [];
                }
                categories[rule.category].push(rule);
            });

            let html = '';
            const categoryIcons = {
                'مخارج الحروف': 'fa-person-rays',
                'صفات الحروف': 'fa-font',
                'الوقف والابتداء': 'fa-pause-circle',
                'المد': 'fa-wave-square',
                'النون الساكنة والتنوين': 'fa-circle-half-stroke',
                'الميم الساكنة': 'fa-volume-high',
                'الغنة': 'fa-bell',
                'default': 'fa-book'
            };

            Object.entries(categories).forEach(([category, categoryRules]) => {
                const icon = categoryIcons[category] || categoryIcons.default;
                html += `
                    <div class="list-group-item bg-light fw-bold text-success d-flex align-items-center">
                        <i class="fas ${icon} me-2"></i>${category}
                    </div>
                `;

                categoryRules.forEach(rule => {
                    html += `
                        <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                onclick="TajweedTab.loadRuleDetail(${rule.id})">
                            <span class="text-truncate me-2">${rule.name}</span>
                            <i class="fas fa-chevron-left text-muted small"></i>
                        </button>
                    `;
                });
            });

            container.innerHTML = html;
        },

        loadRuleDetail: function(ruleId) {
            const detailCard = document.getElementById('tajweedRuleDetail');

            if (!detailCard) return;

            detailCard.style.display = 'block';

            this.apiRequest(`/api/student/tarteel/tajweed-example/${ruleId}`).then(data => {
                if (data && data.success) {
                    this.renderRuleDetail(data.data);
                }
            }).catch(err => console.error('Error loading rule detail:', err));
        },

        renderRuleDetail: function(data) {
            const rule = data.rule || {};
            const example = data.example_verse || {};

            document.getElementById('ruleDetailTitle').innerHTML =
                `<i class="fas fa-info-circle me-2"></i>${rule.name || ''}`;
            document.getElementById('ruleDetailCategory').textContent = rule.category || '';
            document.getElementById('ruleDetailDescription').textContent = rule.description || '';

            const exampleSection = document.getElementById('tajweedExampleSection');
            if (example && example.text) {
                exampleSection.style.display = 'block';
                document.getElementById('ruleDetailVerse').textContent = example.text;
                document.getElementById('ruleDetailVerseRef').textContent =
                    `${example.surah && example.surah.name ? example.surah.name : ''} - آية ${example.verse_number || ''}`;
            } else {
                exampleSection.style.display = 'none';
            }

            const examplesSection = document.getElementById('ruleExamplesSection');
            if (Array.isArray(rule.examples) && rule.examples.length > 0) {
                examplesSection.style.display = 'block';
                document.getElementById('ruleExamplesList').innerHTML =
                    rule.examples.map(ex => `<li class="list-group-item">${ex}</li>`).join('');
            } else {
                examplesSection.style.display = 'none';
            }
        },

        initQuizControls: function() {
            const startBtn = document.getElementById('startTajweedQuiz');
            const nextBtn = document.getElementById('nextQuizQuestion');
            const finishBtn = document.getElementById('finishQuiz');
            const historyBtn = document.getElementById('toggleQuizHistory');
            const closeHistoryBtn = document.getElementById('closeQuizHistory');
            const filterContainer = document.getElementById('quizCategoryFilter');

            if (filterContainer) filterContainer.style.display = 'block';

            if (startBtn) startBtn.addEventListener('click', () => this.startQuiz());
            if (nextBtn) nextBtn.addEventListener('click', () => this.recordAnswerAndNext());
            if (finishBtn) finishBtn.addEventListener('click', () => this.submitAndFinish());
            if (historyBtn) historyBtn.addEventListener('click', () => this.showHistory());
            if (closeHistoryBtn) closeHistoryBtn.addEventListener('click', () => this.hideHistory());
        },

        startQuiz: function() {
            const categorySelect = document.getElementById('quizCategorySelect');
            const category = categorySelect ? categorySelect.value : '';

            const placeholder = document.getElementById('quizPlaceholder');
            const content = document.getElementById('quizContent');
            if (placeholder) placeholder.style.display = 'none';
            if (content) content.style.display = 'block';

            this.apiRequest('/api/student/tarteel/tajweed-quiz/start', 'POST', {
                category: category || null,
            }).then(data => {
                if (data && data.success && data.data && data.data.questions) {
                    this.quizAttemptId = data.data.attempt_id;
                    this.quizQuestions = data.data.questions;
                    this.currentQuizIndex = 0;
                    this.quizSelectedAnswers = new Array(this.quizQuestions.length).fill(null);
                    this.displayQuizQuestion();
                } else {
                    this.showToast((data && data.error) || 'فشل في بدء الاختبار', 'error');
                    this.resetQuiz();
                }
            }).catch(err => {
                console.error('Error starting quiz:', err);
                this.showToast('فشل في بدء الاختبار: ' + (err.message || 'خطأ غير معروف'), 'error');
                this.resetQuiz();
            });
        },

        displayQuizQuestion: function() {
            if (this.currentQuizIndex >= this.quizQuestions.length) {
                this.submitAndFinish();
                return;
            }

            this.questionStartedAt = Date.now();
            const question = this.quizQuestions[this.currentQuizIndex];
            const questionNumEl = document.getElementById('quizQuestionNum');
            const questionTextEl = document.getElementById('quizQuestionText');
            const optionsContainer = document.getElementById('quizOptions');
            const feedbackEl = document.getElementById('quizFeedback');
            const nextBtn = document.getElementById('nextQuizQuestion');
            const finishBtn = document.getElementById('finishQuiz');

            if (questionNumEl) {
                questionNumEl.textContent = `السؤال ${this.currentQuizIndex + 1} من ${this.quizQuestions.length}`;
            }
            if (questionTextEl) questionTextEl.textContent = question.question;

            if (optionsContainer) {
                optionsContainer.innerHTML = question.options.map((opt, idx) => `
                    <button class="list-group-item list-group-item-action text-end"
                            style="cursor:pointer;"
                            onclick="TajweedTab.selectAnswer(${idx})">
                        <span class="badge bg-secondary me-2">${String.fromCharCode(65 + idx)}</span>
                        ${opt}
                    </button>
                `).join('');
            }

            if (feedbackEl) feedbackEl.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            if (finishBtn) finishBtn.style.display = 'none';
        },

        selectAnswer: function(index) {
            this.quizSelectedAnswers[this.currentQuizIndex] = index;

            const question = this.quizQuestions[this.currentQuizIndex];
            const feedbackEl = document.getElementById('quizFeedback');
            const feedbackText = document.getElementById('quizFeedbackText');
            const nextBtn = document.getElementById('nextQuizQuestion');
            const finishBtn = document.getElementById('finishQuiz');

            if (feedbackEl && feedbackText) {
                feedbackEl.className = 'alert alert-info';
                feedbackText.innerHTML = '<i class="fas fa-check me-2"></i>تم اختيار الإجابة';
                feedbackEl.style.display = 'block';
            }

            const optionBtns = document.querySelectorAll('#quizOptions .list-group-item');
            optionBtns.forEach((opt, idx) => {
                opt.style.pointerEvents = 'none';
                opt.classList.add('list-group-item-secondary');
                if (idx === index) {
                    opt.classList.remove('list-group-item-secondary');
                    opt.classList.add('list-group-item-primary');
                }
            });

            if (this.currentQuizIndex < this.quizQuestions.length - 1) {
                if (nextBtn) nextBtn.style.display = 'inline-block';
            } else {
                if (finishBtn) finishBtn.style.display = 'inline-block';
            }
        },

        recordAnswerAndNext: function() {
            this.currentQuizIndex++;
            this.displayQuizQuestion();
        },

        submitAndFinish: function() {
            if (!this.quizAttemptId) {
                this.resetQuiz();
                return;
            }

            const answers = this.quizQuestions.map((q, idx) => {
                const timeTaken = this.questionStartedAt
                    ? Math.round((Date.now() - this.questionStartedAt) / 1000)
                    : 0;
                return {
                    rule_id: q.rule_id,
                    selected_index: this.quizSelectedAnswers[idx] !== null ? this.quizSelectedAnswers[idx] : 0,
                    time_taken_seconds: timeTaken,
                };
            });

            this.apiRequest(`/api/student/tarteel/tajweed-quiz/${this.quizAttemptId}/submit`, 'POST', {
                answers: answers,
            }).then(data => {
                if (data && data.success && data.data) {
                    this.renderResults(data.data);
                } else {
                    this.showToast((data && data.error) || 'فشل في حفظ النتيجة', 'error');
                }
            }).catch(err => {
                console.error('Error submitting quiz:', err);
                this.showToast('فشل في حفظ النتيجة', 'error');
            });
        },

        renderResults: function(result) {
            const score = result.score_percentage || 0;
            const correct = result.correct_answers || 0;
            const total = result.total_questions || 0;

            let message = '';
            let icon = '';
            if (score >= 80) {
                message = 'ممتاز! أحسنت!';
                icon = 'fa-trophy text-warning';
            } else if (score >= 60) {
                message = 'جيد جداً!';
                icon = 'fa-star text-success';
            } else if (score >= 40) {
                message = 'يمكنك التحسن أكثر';
                icon = 'fa-thumbs-up text-info';
            } else {
                message = 'تحتاج لمزيد من المراجعة';
                icon = 'fa-book text-secondary';
            }

            const answersList = (result.answers || []).map(a => {
                const statusIcon = a.is_correct
                    ? '<i class="fas fa-check-circle text-success"></i>'
                    : '<i class="fas fa-times-circle text-danger"></i>';
                return `
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="text-end flex-grow-1">
                            <div class="fw-bold">${a.rule_name || ''}</div>
                            <small class="text-muted">${a.question_text || ''}</small>
                            ${a.student_answer ? `<div class="small mt-1">إجابتك: <span class="${a.is_correct ? 'text-success' : 'text-danger'}">${a.student_answer}</span></div>` : ''}
                            ${!a.is_correct ? `<div class="small text-success">الصواب: ${a.correct_answer}</div>` : ''}
                        </div>
                        <span class="ms-2">${statusIcon}</span>
                    </li>
                `;
            }).join('');

            const resultsEl = document.getElementById('quizResults');
            const contentEl = document.getElementById('quizContent');
            if (contentEl) contentEl.style.display = 'none';
            if (resultsEl) {
                resultsEl.innerHTML = `
                    <div class="py-4">
                        <i class="fas ${icon} fa-4x mb-3"></i>
                        <h4 class="mb-2">${message}</h4>
                        <h2 class="text-success mb-2">${score}%</h2>
                        <p class="text-muted mb-3">أجبت على ${correct} من ${total} بشكل صحيح</p>
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button class="btn btn-outline-success" onclick="TajweedTab.resetQuiz()">
                                <i class="fas fa-redo me-2"></i>إعادة الاختبار
                            </button>
                            <button class="btn btn-outline-primary" onclick="TajweedTab.showHistory()">
                                <i class="fas fa-history me-2"></i>عرض السجل
                            </button>
                        </div>
                        <div class="text-end mt-4">
                            <h6 class="mb-2">تفاصيل الإجابات</h6>
                            <ul class="list-group text-end">${answersList}</ul>
                        </div>
                    </div>
                `;
                resultsEl.style.display = 'block';
            }
        },

        showHistory: function() {
            const card = document.getElementById('quizHistoryCard');
            if (card) card.style.display = 'block';
            this.loadHistory();
        },

        hideHistory: function() {
            const card = document.getElementById('quizHistoryCard');
            if (card) card.style.display = 'none';
        },

        loadHistory: function() {
            const container = document.getElementById('quizHistoryList');
            if (!container) return;

            this.apiRequest('/api/student/tarteel/tajweed-quiz/history').then(data => {
                if (data && data.success) {
                    const list = data.data || [];
                    if (list.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-4 px-3">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">لا توجد اختبارات سابقة</p>
                            </div>
                        `;
                        return;
                    }

                    container.innerHTML = list.map(a => {
                        const scoreClass = a.score_percentage >= 80 ? 'success' :
                            a.score_percentage >= 60 ? 'info' :
                            a.score_percentage >= 40 ? 'warning' : 'danger';
                        const date = a.finished_at ? new Date(a.finished_at).toLocaleString('ar-EG') : '';
                        return `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="text-end">
                                    <div class="fw-bold">${a.correct_answers} / ${a.total_questions}</div>
                                    <small class="text-muted">${date}</small>
                                    ${a.category_filter ? `<div class="small text-muted">${a.category_filter}</div>` : ''}
                                </div>
                                <span class="badge bg-${scoreClass} fs-6">${a.score_percentage}%</span>
                            </div>
                        `;
                    }).join('');
                }
            }).catch(err => {
                console.error('Error loading quiz history:', err);
                container.innerHTML = `
                    <div class="text-center py-4 px-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                        <p class="text-danger mb-0">فشل في تحميل السجل</p>
                    </div>
                `;
            });
        },

        resetQuiz: function() {
            const placeholder = document.getElementById('quizPlaceholder');
            const content = document.getElementById('quizContent');
            const results = document.getElementById('quizResults');

            if (results) results.style.display = 'none';
            if (content) content.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';

            this.quizAttemptId = null;
            this.quizQuestions = [];
            this.currentQuizIndex = 0;
            this.quizSelectedAnswers = [];
        },

        showToast: function(message, type) {
            let container = document.getElementById('toastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastContainer';
                container.className = 'toast-container';
                container.style.cssText = 'position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:0.5rem;';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const bgColor = type === 'success'
                ? 'linear-gradient(135deg,#1a7a4a,#2d9d63)'
                : 'linear-gradient(135deg,#dc3545,#c82333)';
            toast.style.cssText = `${bgColor};color:white;padding:1rem 1.5rem;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:slideUp 0.3s ease;`;
            toast.textContent = message;

            container.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    };

    if (document.getElementById('panel-tajweed')) {
        TajweedTab.init();
    }

    window.TajweedTab = TajweedTab;
})();
