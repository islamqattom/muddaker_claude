(function() {
    'use strict';

    const TajweedTab = {
        currentRule: null,
        quizQuestions: [],
        currentQuizIndex: 0,
        quizScore: 0,
        surahData: null,

        init: function() {
            this.loadTajweedRules();
            this.initSurahSelect();
            this.initQuizControls();
        },

        getApiToken: function() {
            return document.querySelector('meta[name=api-token]')?.content || '';
        },

        loadTajweedRules: function() {
            const container = document.getElementById('tajweedRulesList');
            if (!container) return;

            const apiToken = this.getApiToken();

            fetch('/api/student/tarteel/tajweed-rules', {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    this.renderTajweedRules(data.data);
                } else {
                    container.innerHTML = `
                        <div class="text-center py-4 px-3">
                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">لا توجد قواعد تجويد متوفرة</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
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
            const apiToken = this.getApiToken();
            const detailCard = document.getElementById('tajweedRuleDetail');

            if (!detailCard) return;

            detailCard.style.display = 'block';

            fetch(`/api/student/tarteel/tajweed-example/${ruleId}`, {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.renderRuleDetail(data.data);
                } else {
                    this.showToast('فشل في تحميل تفاصيل القاعدة', 'error');
                }
            })
            .catch(err => {
                console.error('Error loading rule detail:', err);
                this.showToast('خطأ في الاتصال', 'error');
            });
        },

        renderRuleDetail: function(data) {
            this.currentRule = data.rule;

            const titleEl = document.getElementById('ruleDetailTitle');
            const categoryEl = document.getElementById('ruleDetailCategory');
            const typeEl = document.getElementById('ruleDetailType');
            const descEl = document.getElementById('ruleDetailDescription');

            if (titleEl) titleEl.innerHTML = `<i class="fas fa-info-circle me-2"></i>${data.rule.name}`;
            if (categoryEl) categoryEl.textContent = data.rule.category;
            if (typeEl) typeEl.textContent = data.rule.order ? `#${data.rule.order}` : '';
            if (descEl) descEl.textContent = data.rule.description || 'لا يوجد وصف تفصيلي لهذه القاعدة';

            const exampleSection = document.getElementById('tajweedExampleSection');
            const verseEl = document.getElementById('ruleDetailVerse');
            const verseRefEl = document.getElementById('ruleDetailVerseRef');

            if (data.example_verse && data.example_verse.text) {
                if (verseEl) verseEl.textContent = data.example_verse.text;
                if (verseRefEl) {
                    const surahName = window.SURAH_INFO?.[data.example_verse.surah]?.name || `سورة ${data.example_verse.surah}`;
                    verseRefEl.textContent = `${surahName} - الآية ${data.example_verse.verse_number}`;
                }
                if (exampleSection) exampleSection.style.display = 'block';
            } else {
                if (exampleSection) exampleSection.style.display = 'none';
            }

            const examplesSection = document.getElementById('ruleExamplesSection');
            const examplesList = document.getElementById('ruleExamplesList');

            if (data.rule.examples && examplesList) {
                try {
                    const examples = typeof data.rule.examples === 'string'
                        ? JSON.parse(data.rule.examples)
                        : data.rule.examples;

                    if (Array.isArray(examples) && examples.length > 0) {
                        examplesList.innerHTML = examples.map(ex =>
                            `<li class="list-group-item">${ex}</li>`
                        ).join('');
                        if (examplesSection) examplesSection.style.display = 'block';
                    } else {
                        if (examplesSection) examplesSection.style.display = 'none';
                    }
                } catch (e) {
                    if (examplesSection) examplesSection.style.display = 'none';
                }
            } else {
                if (examplesSection) examplesSection.style.display = 'none';
            }
        },

        initSurahSelect: function() {
            const surahSelect = document.getElementById('tajweedSurahSelect');
            const loadBtn = document.getElementById('loadTajweedVerse');

            if (!surahSelect) return;

            const SURAH_INFO = window.SURAH_INFO || {};
            Object.keys(SURAH_INFO).forEach(num => {
                const opt = document.createElement('option');
                opt.value = num;
                opt.textContent = `${num} - ${SURAH_INFO[num].name}`;
                surahSelect.appendChild(opt);
            });

            if (loadBtn) {
                loadBtn.addEventListener('click', () => this.loadTajweedVerseDisplay());
            }

            document.getElementById('playTajweedAudio')?.addEventListener('click', () => this.playVerseAudio());
            document.getElementById('copyTajweedVerse')?.addEventListener('click', () => this.copyVerse());
        },

        loadTajweedVerseDisplay: function() {
            const surahSelect = document.getElementById('tajweedSurahSelect');
            const verseInput = document.getElementById('tajweedVerseNumber');
            const displayContainer = document.getElementById('tajweedVerseDisplay');
            const verseText = document.getElementById('tajweedVerseText');

            if (!surahSelect?.value) {
                this.showToast('الرجاء اختيار سورة', 'error');
                return;
            }

            const surah = surahSelect.value;
            const verse = verseInput?.value || 1;
            const verseKey = `${surah}:${verse}`;

            if (displayContainer) displayContainer.style.display = 'block';
            if (verseText) verseText.textContent = 'جارٍ التحميل...';

            fetch(`https://api.alquran.cloud/v1/verse/${verseKey}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.data && data.data.text) {
                    if (verseText) verseText.textContent = data.data.text;
                } else {
                    if (verseText) verseText.textContent = 'تعذر تحميل الآية';
                }
            })
            .catch(err => {
                console.error('Error loading verse:', err);
                if (verseText) verseText.textContent = 'خطأ في التحميل';
            });
        },

        playVerseAudio: function() {
            const surahSelect = document.getElementById('tajweedSurahSelect');
            const verseInput = document.getElementById('tajweedVerseNumber');

            if (!surahSelect?.value) return;

            const surah = surahSelect.value;
            const verse = verseInput?.value || 1;
            const audioUrl = `https://cdn.islamic.network/quran/audio/128/ar.alafasy/${surah}:${verse}.mp3`;

            const audio = new Audio(audioUrl);
            audio.play().catch(err => {
                console.error('Audio play error:', err);
                this.showToast('فشل في تشغيل الصوت', 'error');
            });
        },

        copyVerse: function() {
            const verseText = document.getElementById('tajweedVerseText');
            if (verseText && verseText.textContent) {
                navigator.clipboard.writeText(verseText.textContent).then(() => {
                    this.showToast('تم نسخ الآية', 'success');
                }).catch(() => {
                    this.showToast('فشل في النسخ', 'error');
                });
            }
        },

        initQuizControls: function() {
            const startBtn = document.getElementById('startTajweedQuiz');
            const nextBtn = document.getElementById('nextQuizQuestion');
            const finishBtn = document.getElementById('finishQuiz');

            if (startBtn) {
                startBtn.addEventListener('click', () => this.startQuiz());
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => this.nextQuizQuestion());
            }
            if (finishBtn) {
                finishBtn.addEventListener('click', () => this.finishQuiz());
            }
        },

        startQuiz: function() {
            const placeholder = document.getElementById('quizPlaceholder');
            const content = document.getElementById('quizContent');

            if (placeholder) placeholder.style.display = 'none';
            if (content) content.style.display = 'block';

            const apiToken = this.getApiToken();

            fetch('/api/student/tarteel/tajweed-rules', {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    this.quizQuestions = this.generateQuizQuestions(data.data);
                    this.currentQuizIndex = 0;
                    this.quizScore = 0;
                    this.displayQuizQuestion();
                } else {
                    this.showToast('لا توجد قواعد للاختبار', 'error');
                    this.resetQuiz();
                }
            })
            .catch(err => {
                console.error('Error starting quiz:', err);
                this.showToast('فشل في بدء الاختبار', 'error');
                this.resetQuiz();
            });
        },

        generateQuizQuestions: function(rules) {
            const questions = [];
            const shuffled = [...rules].sort(() => Math.random() - 0.5);
            const selected = shuffled.slice(0, Math.min(5, shuffled.length));

            selected.forEach(rule => {
                const options = this.generateOptions(rule, rules);
                questions.push({
                    question: `ما هي قاعدة "${rule.name}"؟`,
                    correct: rule.description || rule.category,
                    options: options,
                });
            });

            return questions;
        },

        generateOptions: function(correctRule, allRules) {
            const options = [correctRule.description || correctRule.category];
            const otherDescriptions = allRules
                .filter(r => r.id !== correctRule.id)
                .map(r => r.description || r.category)
                .filter(d => d && d !== options[0]);

            while (options.length < 4 && otherDescriptions.length > 0) {
                const idx = Math.floor(Math.random() * otherDescriptions.length);
                const option = otherDescriptions.splice(idx, 1)[0];
                if (!options.includes(option)) {
                    options.push(option);
                }
            }

            while (options.length < 4) {
                options.push('إجابة أخرى');
            }

            return options.sort(() => Math.random() - 0.5);
        },

        displayQuizQuestion: function() {
            if (this.currentQuizIndex >= this.quizQuestions.length) {
                this.finishQuiz();
                return;
            }

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
            const question = this.quizQuestions[this.currentQuizIndex];
            const selectedOption = question.options[index];
            const isCorrect = selectedOption === question.correct;

            if (isCorrect) this.quizScore++;

            const feedbackEl = document.getElementById('quizFeedback');
            const feedbackText = document.getElementById('quizFeedbackText');
            const nextBtn = document.getElementById('nextQuizQuestion');
            const finishBtn = document.getElementById('finishQuiz');

            if (feedbackEl && feedbackText) {
                if (isCorrect) {
                    feedbackEl.className = 'alert alert-success';
                    feedbackText.innerHTML = '<i class="fas fa-check-circle me-2"></i>إجابة صحيحة!';
                } else {
                    feedbackEl.className = 'alert alert-danger';
                    feedbackText.innerHTML = `<i class="fas fa-times-circle me-2"></i>إجابة خاطئة! الإجابة الصحيحة: ${question.correct}`;
                }
                feedbackEl.style.display = 'block';
            }

            const optionBtns = document.querySelectorAll('#quizOptions .list-group-item');
            optionBtns.forEach((opt, idx) => {
                opt.style.pointerEvents = 'none';
                if (idx === index) {
                    opt.classList.add(isCorrect ? 'list-group-item-success' : 'list-group-item-danger');
                }
                if (question.options[idx] === question.correct) {
                    opt.classList.add('list-group-item-success');
                }
            });

            if (this.currentQuizIndex < this.quizQuestions.length - 1) {
                if (nextBtn) nextBtn.style.display = 'inline-block';
            } else {
                if (finishBtn) finishBtn.style.display = 'inline-block';
            }
        },

        nextQuizQuestion: function() {
            this.currentQuizIndex++;
            this.displayQuizQuestion();
        },

        finishQuiz: function() {
            const percentage = this.quizQuestions.length > 0
                ? Math.round((this.quizScore / this.quizQuestions.length) * 100)
                : 0;

            let message = '';
            let icon = '';

            if (percentage >= 80) {
                message = `ممتاز! أحسنت!`;
                icon = 'fa-trophy text-warning';
            } else if (percentage >= 60) {
                message = `جيد جداً!`;
                icon = 'fa-star text-success';
            } else if (percentage >= 40) {
                message = `يمكنك التحسن أكثر`;
                icon = 'fa-thumbs-up text-info';
            } else {
                message = ` تحتاج لمزيد من المراجعة`;
                icon = 'fa-book text-secondary';
            }

            const resultsEl = document.getElementById('quizResults');
            const contentEl = document.getElementById('quizContent');

            if (contentEl) contentEl.style.display = 'none';
            if (resultsEl) {
                resultsEl.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas ${icon} fa-4x mb-4"></i>
                        <h4 class="mb-2">${message}</h4>
                        <h2 class="text-success mb-3">${percentage}%</h2>
                        <p class="text-muted mb-4">أجبت على ${this.quizScore} من ${this.quizQuestions.length} بشكل صحيح</p>
                        <button class="btn btn-outline-success" onclick="TajweedTab.resetQuiz()">
                            <i class="fas fa-redo me-2"></i>إعادة الاختبار
                        </button>
                    </div>
                `;
                resultsEl.style.display = 'block';
            }
        },

        resetQuiz: function() {
            const placeholder = document.getElementById('quizPlaceholder');
            const content = document.getElementById('quizContent');
            const results = document.getElementById('quizResults');

            if (results) results.style.display = 'none';
            if (content) content.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';

            this.quizQuestions = [];
            this.currentQuizIndex = 0;
            this.quizScore = 0;
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