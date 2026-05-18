(function() {
    'use strict';

    const TajweedTab = {
        currentRule: null,
        quizQuestions: [],
        currentQuizIndex: 0,
        quizScore: 0,

        init: function() {
            this.loadTajweedRules();
            this.initSurahSelect();
            this.initQuiz();
        },

        loadTajweedRules: function() {
            const container = document.getElementById('tajweedRulesList');
            if (!container) return;

            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';

            fetch('/api/student/tarteel/tajweed-rules', {
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    this.renderTajweedRules(data.data);
                } else {
                    container.innerHTML = '<div class="p-3 text-center text-muted"><p>لا توجد قواعد تجويد متوفرة</p></div>';
                }
            })
            .catch(err => {
                console.error('Error loading tajweed rules:', err);
                container.innerHTML = '<div class="p-3 text-center text-danger"><p>فشل في تحميل القواعد</p></div>';
            });
        },

        renderTajweedRules: function(rules) {
            const container = document.getElementById('tajweedRulesList');
            if (!container) return;

            const categoryIcons = {
                'مخارج الحروف': 'fa-person-rays',
                'صفات الحروف': 'fa-font',
                'الوقف والابتداء': 'fa-pause-circle',
                'المد': 'fa-wave-square',
                'النون الساكنة والتنوين': 'fa-circle-half-stroke',
                'الميم الساكنة': 'fa-volume-high',
                'الغنة': 'fa-bell',
            };

            let html = '';
            let currentCategory = '';

            rules.forEach(rule => {
                if (rule.category !== currentCategory) {
                    currentCategory = rule.category;
                    html += `
                        <div class="list-group-item list-group-item-secondary fw-bold text-center">
                            ${currentCategory}
                        </div>
                    `;
                }

                const icon = categoryIcons[rule.category] || 'fa-book';
                html += `
                    <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                            onclick="TajweedTab.loadRuleDetail(${rule.id})">
                        <span>
                            <i class="fas ${icon} me-2 text-primary"></i>
                            ${rule.name}
                        </span>
                        <i class="fas fa-chevron-left text-muted"></i>
                    </button>
                `;
            });

            container.innerHTML = html;
        },

        loadRuleDetail: function(ruleId) {
            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';
            const detailCard = document.getElementById('tajweedRuleDetail');

            if (!detailCard) return;

            detailCard.style.display = 'block';

            fetch(`/api/student/tarteel/tajweed-example/${ruleId}`, {
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.renderRuleDetail(data.data);
                }
            })
            .catch(err => console.error('Error loading rule detail:', err));
        },

        renderRuleDetail: function(data) {
            this.currentRule = data.rule;

            const titleEl = document.getElementById('ruleDetailTitle');
            const categoryEl = document.getElementById('ruleDetailCategory');
            const descEl = document.getElementById('ruleDetailDescription');
            const verseEl = document.getElementById('ruleDetailVerse');
            const verseRefEl = document.getElementById('ruleDetailVerseRef');

            if (titleEl) titleEl.innerHTML = `<i class="fas fa-book me-2"></i>${data.rule.name}`;
            if (categoryEl) categoryEl.textContent = data.rule.category;
            if (descEl) descEl.textContent = data.rule.description || 'لا يوجد وصف';

            if (data.example_verse && data.example_verse.text) {
                const surahName = window.SURAH_INFO?.[data.example_verse.surah]?.name || data.example_verse.surah;
                if (verseEl) verseEl.textContent = data.example_verse.text;
                if (verseRefEl) verseRefEl.textContent = `${surahName} - الآية ${data.example_verse.verse_number}`;
                document.getElementById('tajweedExampleSection').style.display = 'block';
            } else {
                document.getElementById('tajweedExampleSection').style.display = 'none';
            }

            if (data.rule.examples) {
                const examplesList = document.getElementById('ruleExamplesList');
                if (examplesList) {
                    try {
                        const examples = typeof data.rule.examples === 'string'
                            ? JSON.parse(data.rule.examples)
                            : data.rule.examples;
                        if (Array.isArray(examples) && examples.length > 0) {
                            examplesList.innerHTML = examples.map(ex =>
                                `<li class="list-group-item">${ex}</li>`
                            ).join('');
                            document.getElementById('ruleExamplesSection').style.display = 'block';
                        } else {
                            document.getElementById('ruleExamplesSection').style.display = 'none';
                        }
                    } catch (e) {
                        document.getElementById('ruleExamplesSection').style.display = 'none';
                    }
                }
            } else {
                document.getElementById('ruleExamplesSection').style.display = 'none';
            }
        },

        initSurahSelect: function() {
            const surahSelect = document.getElementById('tajweedSurahSelect');
            const verseInput = document.getElementById('tajweedVerseNumber');
            const loadBtn = document.getElementById('loadTajweedVerse');

            if (!surahSelect) return;

            const SURAH_INFO = window.SURAH_INFO || {};
            Object.keys(SURAH_INFO).forEach(num => {
                const opt = document.createElement('option');
                opt.value = num;
                opt.textContent = `${num} - ${SURAH_INFO[num].name}`;
                surahSelect.appendChild(opt);
            });

            loadBtn?.addEventListener('click', () => this.loadTajweedVerseDisplay());
            verseInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.loadTajweedVerseDisplay();
            });
        },

        loadTajweedVerseDisplay: function() {
            const surahSelect = document.getElementById('tajweedSurahSelect');
            const verseInput = document.getElementById('tajweedVerseNumber');
            const displayContainer = document.getElementById('tajweedVerseDisplay');
            const verseText = document.getElementById('tajweedVerseText');

            if (!surahSelect?.value || !verseInput?.value) return;

            const surah = surahSelect.value;
            const verse = verseInput.value;
            const verseKey = `${surah}:${verse}`;

            if (displayContainer) displayContainer.style.display = 'block';

            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';

            fetch(`https://api.alquran.cloud/v1/verse/${verseKey}`, {
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.data && data.data.text) {
                    if (verseText) verseText.textContent = data.data.text;
                }
            })
            .catch(err => console.error('Error loading verse:', err));
        },

        initQuiz: function() {
            const startBtn = document.getElementById('startTajweedQuiz');
            const nextBtn = document.getElementById('nextQuizQuestion');
            const finishBtn = document.getElementById('finishQuiz');

            startBtn?.addEventListener('click', () => this.startQuiz());
            nextBtn?.addEventListener('click', () => this.nextQuizQuestion());
            finishBtn?.addEventListener('click', () => this.finishQuiz());
        },

        startQuiz: function() {
            document.getElementById('quizPlaceholder').style.display = 'none';
            document.getElementById('quizContent').style.display = 'block';

            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';

            fetch('/api/student/tarteel/tajweed-rules', {
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    this.quizQuestions = this.generateQuizQuestions(data.data);
                    this.currentQuizIndex = 0;
                    this.quizScore = 0;
                    this.displayQuizQuestion();
                }
            })
            .catch(err => console.error('Error starting quiz:', err));
        },

        generateQuizQuestions: function(rules) {
            const questions = [];
            const shuffled = [...rules].sort(() => Math.random() - 0.5);
            const selected = shuffled.slice(0, Math.min(5, shuffled.length));

            selected.forEach(rule => {
                questions.push({
                    question: `ما هي قاعدة "${rule.name}"؟`,
                    correct: rule.description || rule.category,
                    options: this.generateOptions(rule, rules),
                });
            });

            return questions;
        },

        generateOptions: function(correctRule, allRules) {
            const options = [correctRule.description || correctRule.category];
            const otherDescriptions = allRules
                .filter(r => r.id !== correctRule.id)
                .map(r => r.description || r.category)
                .filter(d => d);

            while (options.length < 4 && otherDescriptions.length > 0) {
                const idx = Math.floor(Math.random() * otherDescriptions.length);
                const option = otherDescriptions.splice(idx, 1)[0];
                if (!options.includes(option)) {
                    options.push(option);
                }
            }

            return options.sort(() => Math.random() - 0.5);
        },

        displayQuizQuestion: function() {
            if (this.currentQuizIndex >= this.quizQuestions.length) {
                this.finishQuiz();
                return;
            }

            const question = this.quizQuestions[this.currentQuizIndex];
            document.getElementById('quizQuestionNum').textContent =
                `السؤال ${this.currentQuizIndex + 1} من ${this.quizQuestions.length}`;
            document.getElementById('quizQuestionText').textContent = question.question;

            const optionsContainer = document.getElementById('quizOptions');
            optionsContainer.innerHTML = question.options.map((opt, idx) => `
                <button class="list-group-item list-group-item-action" onclick="TajweedTab.selectAnswer(${idx})">
                    ${opt}
                </button>
            `).join('');

            document.getElementById('quizFeedback').style.display = 'none';
            document.getElementById('nextQuizQuestion').style.display = 'none';
            document.getElementById('finishQuiz').style.display = 'none';
        },

        selectAnswer: function(index) {
            const question = this.quizQuestions[this.currentQuizIndex];
            const selectedOption = question.options[index];
            const isCorrect = selectedOption === question.correct;

            if (isCorrect) this.quizScore++;

            const feedbackEl = document.getElementById('quizFeedback');
            const feedbackText = document.getElementById('quizFeedbackText');

            if (isCorrect) {
                feedbackText.className = 'alert alert-success';
                feedbackText.innerHTML = '<i class="fas fa-check-circle me-2"></i>إجابة صحيحة!';
            } else {
                feedbackText.className = 'alert alert-danger';
                feedbackText.innerHTML = `<i class="fas fa-times-circle me-2"></i>إجابة خاطئة! الإجابة الصحيحة: ${question.correct}`;
            }

            feedbackEl.style.display = 'block';

            const options = document.querySelectorAll('#quizOptions .list-group-item');
            options.forEach((opt, idx) => {
                opt.classList.remove('active');
                if (idx === index) {
                    opt.classList.add(isCorrect ? 'list-group-item-success' : 'list-group-item-danger');
                }
                opt.style.pointerEvents = 'none';
            });

            if (this.currentQuizIndex < this.quizQuestions.length - 1) {
                document.getElementById('nextQuizQuestion').style.display = 'inline-block';
            } else {
                document.getElementById('finishQuiz').style.display = 'inline-block';
            }
        },

        nextQuizQuestion: function() {
            this.currentQuizIndex++;
            this.displayQuizQuestion();
        },

        finishQuiz: function() {
            const percentage = Math.round((this.quizScore / this.quizQuestions.length) * 100);
            let message = '';
            let alertClass = '';

            if (percentage >= 80) {
                message = `ممتاز! درجة النهائي: ${percentage}%`;
                alertClass = 'alert-success';
            } else if (percentage >= 60) {
                message = `جيد! درجة النهائي: ${percentage}%`;
                alertClass = 'alert-warning';
            } else {
                message = `يمكنك تحسين نتيجتك! درجة النهائي: ${percentage}%`;
                alertClass = 'alert-info';
            }

            document.getElementById('quizContent').innerHTML = `
                <div class="text-center py-5">
                    <i class="fas ${percentage >= 60 ? 'fa-trophy text-warning' : 'fa-book text-info'} fa-4x mb-3"></i>
                    <h4>${message}</h4>
                    <p class="text-muted">أجبت على ${this.quizScore} من ${this.quizQuestions.length} بشكل صحيح</p>
                    <button class="btn btn-outline-primary mt-3" onclick="location.reload()">
                        <i class="fas fa-redo me-2"></i>إعادة الاختبار
                    </button>
                </div>
            `;
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('tajweed-tab')) {
            TajweedTab.init();
        }
    });

    window.TajweedTab = TajweedTab;
})();