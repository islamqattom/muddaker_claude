(function() {
    'use strict';

    const MemorizationTab = {
        pomodoroTimer: null,
        pomodoroMinutes: 25,
        pomodoroSeconds: 0,
        pomodoroRunning: false,
        pomodoroInterval: null,

        init: function() {
            this.initPomodoro();
            this.initGoalSetting();
            this.loadMemorizationStats();
            this.loadDueReviews();
            this.loadMemorizationPlan();
            this.loadLastMemorized();
        },

        getApiToken: function() {
            return document.querySelector('meta[name=api-token]')?.content || '';
        },

        getCsrfToken: function() {
            return document.querySelector('meta[name=csrf-token]')?.content || '';
        },

        initPomodoro: function() {
            const startBtn = document.getElementById('pomodoroStart');
            const resetBtn = document.getElementById('pomodoroReset');

            if (!startBtn) return;

            startBtn.addEventListener('click', () => {
                if (this.pomodoroRunning) {
                    this.pausePomodoro();
                } else {
                    this.startPomodoro();
                }
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', () => this.resetPomodoro());
            }
        },

        startPomodoro: function() {
            this.pomodoroRunning = true;
            const startBtn = document.getElementById('pomodoroStart');
            const resetBtn = document.getElementById('pomodoroReset');
            const label = document.getElementById('pomodoroLabel');

            if (startBtn) {
                startBtn.innerHTML = '<i class="fas fa-pause me-1"></i>إيقاف';
                startBtn.classList.remove('btn-success');
                startBtn.classList.add('btn-warning');
            }
            if (resetBtn) resetBtn.classList.remove('d-none');
            if (label) label.textContent = 'جلسة حفظ متقنة';

            this.pomodoroInterval = setInterval(() => {
                if (this.pomodoroSeconds === 0) {
                    if (this.pomodoroMinutes === 0) {
                        this.completePomodoro();
                        return;
                    }
                    this.pomodoroMinutes--;
                    this.pomodoroSeconds = 59;
                } else {
                    this.pomodoroSeconds--;
                }
                this.updatePomodoroDisplay();
            }, 1000);
        },

        pausePomodoro: function() {
            this.pomodoroRunning = false;
            clearInterval(this.pomodoroInterval);
            const startBtn = document.getElementById('pomodoroStart');
            const label = document.getElementById('pomodoroLabel');

            if (startBtn) {
                startBtn.innerHTML = '<i class="fas fa-play me-1"></i>استمرار';
                startBtn.classList.remove('btn-warning');
                startBtn.classList.add('btn-success');
            }
            if (label) label.textContent = 'موقوف مؤقتاً';
        },

        resetPomodoro: function() {
            this.pomodoroRunning = false;
            clearInterval(this.pomodoroInterval);
            this.pomodoroMinutes = 25;
            this.pomodoroSeconds = 0;
            this.updatePomodoroDisplay();

            const startBtn = document.getElementById('pomodoroStart');
            const resetBtn = document.getElementById('pomodoroReset');
            const label = document.getElementById('pomodoroLabel');

            if (startBtn) {
                startBtn.innerHTML = '<i class="fas fa-play me-1"></i>ابدأ';
                startBtn.classList.remove('btn-warning');
                startBtn.classList.add('btn-success');
            }
            if (resetBtn) resetBtn.classList.add('d-none');
            if (label) label.textContent = 'جلسة حفظ متقنة';
        },

        updatePomodoroDisplay: function() {
            const timer = document.getElementById('pomodoroTimer');
            if (timer) {
                const mins = String(this.pomodoroMinutes).padStart(2, '0');
                const secs = String(this.pomodoroSeconds).padStart(2, '0');
                timer.textContent = `${mins}:${secs}`;
            }
        },

        completePomodoro: function() {
            this.pomodoroRunning = false;
            clearInterval(this.pomodoroInterval);
            this.showToast('تم إكمال جلسة الحفظ بنجاح!', 'success');
            this.resetPomodoro();
        },

        initGoalSetting: function() {
            const goalHint = document.getElementById('goalHint');
            if (goalHint) {
                goalHint.addEventListener('click', () => {
                    const newGoal = prompt('أدخل هدفك اليومي من الآيات:', '20');
                    if (newGoal && !isNaN(newGoal) && newGoal > 0) {
                        this.setDailyGoal(parseInt(newGoal));
                    }
                });
            }
        },

        setDailyGoal: function(targetVerses) {
            const apiToken = this.getApiToken();
            const csrfToken = this.getCsrfToken();

            fetch('/api/student/tarteel/daily-goal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + apiToken,
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ target_verses: targetVerses })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.updateGoalDisplay(0, data.data.target_verses);
                    this.showToast('تم تحديث الهدف اليومي', 'success');
                }
            })
            .catch(err => {
                console.error('Error setting goal:', err);
                this.showToast('فشل في تحديث الهدف', 'error');
            });
        },

        loadMemorizationStats: function() {
            const apiToken = this.getApiToken();
            const statsUrl = document.querySelector('meta[name=memorization-stats-url]')?.content;

            if (!statsUrl) {
                this.loadMemorizationStatsDirectly(apiToken);
                return;
            }

            fetch(statsUrl, {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.updateStatsDisplay(data.data);
                }
            })
            .catch(() => this.loadMemorizationStatsDirectly(apiToken));
        },

        loadMemorizationStatsDirectly: function(apiToken) {
            fetch('/api/student/tarteel/memorization-stats', {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.updateStatsDisplay(data.data);
                }
            })
            .catch(err => console.error('Error loading stats:', err));
        },

        updateStatsDisplay: function(data) {
            const elements = {
                statCompletedParts: data.completed_parts || 0,
                statTotalParts: data.total_parts || 0,
                statDueReviews: data.due_reviews || 0,
                statRecentMemorization: data.recently_memorized || 0,
            };

            Object.entries(elements).forEach(([id, value]) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            });

            if (data.total_parts > 0) {
                const dailyGoal = 20;
                this.updateGoalDisplay(data.recently_memorized || 0, dailyGoal);
            }
        },

        updateGoalDisplay: function(current, target) {
            const progressEl = document.getElementById('currentProgress');
            const progressBar = document.getElementById('goalProgressBar');
            const badgeEl = document.getElementById('dailyGoalBadge');

            if (progressEl) progressEl.textContent = `${current} / ${target}`;
            if (progressBar) {
                const percentage = target > 0 ? Math.min((current / target) * 100, 100) : 0;
                progressBar.style.width = percentage + '%';
            }
            if (badgeEl) badgeEl.textContent = `${target} آية`;
        },

        loadDueReviews: function() {
            const container = document.getElementById('reviewScheduleList');
            if (!container) return;

            const apiToken = this.getApiToken();

            fetch('/api/student/tarteel/memorization-plan', {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.due_reviews && data.data.due_reviews.length > 0) {
                    this.renderReviews(data.data.due_reviews);
                } else {
                    container.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">لا توجد مراجعات مستحقة</td></tr>';
                }
            })
            .catch(() => {
                container.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-danger">فشل في تحميل المراجعات</td></tr>';
            });
        },

        renderReviews: function(reviews) {
            const container = document.getElementById('reviewScheduleList');
            if (!container) return;

            const SURAH_INFO = window.SURAH_INFO || {};

            container.innerHTML = reviews.map(review => {
                const surahName = SURAH_INFO[review.surah]?.name || `السورة ${review.surah}`;
                const isDue = new Date(review.next_review_date) <= new Date();
                return `
                    <tr>
                        <td><span class="badge bg-primary">الجزء ${review.part_number}</span></td>
                        <td>${surahName}</td>
                        <td>${review.next_review_date}</td>
                        <td>
                            ${isDue
                                ? '<span class="badge bg-danger">مستحقة</span>'
                                : '<span class="badge bg-success">مجدولة</span>'
                            }
                        </td>
                    </tr>
                `;
            }).join('');
        },

        loadMemorizationPlan: function() {
            const container = document.getElementById('memorizationPlanList');
            if (!container) return;

            const apiToken = this.getApiToken();

            fetch('/api/student/tarteel/memorization-plan', {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.parts && data.data.parts.length > 0) {
                    this.renderMemorizationPlan(data.data);
                } else {
                    container.innerHTML = '<div class="col-12 text-center py-4 text-muted">لا توجد خطة حفظ محددة</div>';
                }
            })
            .catch(() => {
                container.innerHTML = '<div class="col-12 text-center py-4 text-danger">فشل في تحميل خطة الحفظ</div>';
            });
        },

        renderMemorizationPlan: function(data) {
            const container = document.getElementById('memorizationPlanList');
            if (!container) return;

            const SURAH_INFO = window.SURAH_INFO || {};

            container.innerHTML = data.parts.map(part => {
                const statusClass = part.is_completed ? 'bg-success' : 'bg-warning';
                const statusText = part.is_completed ? 'مكتمل' : 'قيد الحفظ';
                const surahName = SURAH_INFO[part.surah]?.name || `الجزء ${part.part_number}`;

                return `
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge ${statusClass}">${statusText}</span>
                                    <small class="text-muted">الجزء ${part.part_number}</small>
                                </div>
                                <h6 class="mb-1">${surahName}</h6>
                                <small class="text-muted">
                                    ${part.is_completed
                                        ? '<i class="fas fa-check-circle text-success me-1"></i>تم تقنينه'
                                        : `<i class="fas fa-clock text-warning me-1"></i>آخر حفظ: ${part.last_recitation_date || 'لم يسمع بعد'}`
                                    }
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        },

        loadLastMemorized: function() {
            const container = document.getElementById('lastMemorizedSection');
            if (!container) return;

            const apiToken = this.getApiToken();

            fetch('/api/student/tarteel/memorization-plan', {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.parts && data.data.parts.length > 0) {
                    const lastPart = data.data.parts.find(p => p.last_recitation_date);
                    if (lastPart) {
                        this.renderLastMemorized(lastPart);
                    }
                }
            })
            .catch(err => console.error('Error loading last memorized:', err));
        },

        renderLastMemorized: function(part) {
            const container = document.getElementById('lastMemorizedSection');
            if (!container) return;

            const SURAH_INFO = window.SURAH_INFO || {};
            const surahName = SURAH_INFO[part.surah]?.name || `الجزء ${part.part_number}`;

            container.innerHTML = `
                <div class="text-center">
                    <div class="mb-2">
                        <i class="fas fa-book fa-2x text-success"></i>
                    </div>
                    <h6>${surahName}</h6>
                    <small class="text-muted">الجزء ${part.part_number}</small>
                    <div class="mt-2">
                        <small class="text-success">
                            <i class="fas fa-check me-1"></i>${part.last_recitation_date || 'تم الحفظ'}
                        </small>
                    </div>
                </div>
            `;
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
            const bgColor = type === 'success' ? 'linear-gradient(135deg,#1a7a4a,#2d9d63)' : 'linear-gradient(135deg,#dc3545,#c82333)';
            toast.style.cssText = `${bgColor};color:white;padding:1rem 1.5rem;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:slideUp 0.3s ease;`;
            toast.textContent = message;

            container.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    };

    if (document.getElementById('panel-memorization')) {
        MemorizationTab.init();
    }

    window.MemorizationTab = MemorizationTab;
})();