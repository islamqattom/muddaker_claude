(function() {
    'use strict';

    const MemorizationTab = {
        pomodoroTimer: null,
        pomodoroMinutes: 25,
        pomodoroSeconds: 0,
        pomodoroRunning: false,
        pomodoroInterval: null,

        init: function() {
            this.loadMemorizationPlan();
            this.loadMemorizationStats();
            this.initPomodoro();
            this.initDailyGoal();
            this.loadDueReviews();
            this.loadLastMemorized();
        },

        loadMemorizationPlan: function() {
            const container = document.getElementById('memorizationPlanList');
            if (!container) return;

            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

            fetch('/api/student/tarteel/memorization-plan', {
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                    'X-CSRF-TOKEN': csrfToken,
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.parts.length > 0) {
                    this.renderMemorizationPlan(data.data);
                } else {
                    container.innerHTML = '<div class="col-12 text-center text-muted py-4"><p>لا توجد خطة حفظ محددة</p></div>';
                }
            })
            .catch(err => {
                console.error('Error loading memorization plan:', err);
                container.innerHTML = '<div class="col-12 text-center text-danger py-4"><p>فشل في تحميل خطة الحفظ</p></div>';
            });
        },

        renderMemorizationPlan: function(data) {
            const container = document.getElementById('memorizationPlanList');
            const SURAH_INFO = window.SURAH_INFO || {};

            let html = '';
            data.parts.forEach(part => {
                const statusClass = part.is_completed ? 'bg-success' : 'bg-warning';
                const statusText = part.is_completed ? 'مكتمل' : 'قيد الحفظ';
                html += `
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge ${statusClass}">${statusText}</span>
                                    <small class="text-muted">الجزء ${part.part_number}</small>
                                </div>
                                <h6 class="mb-1">${SURAH_INFO[part.surah]?.name || 'الجزء ' + part.part_number}</h6>
                                <small class="text-muted">
                                    ${part.is_completed ? 'تم تقنينه' : 'آخر مراجعة: ' + (part.last_recitation_date || 'لم يسمع بعد')}
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            });

            if (data.due_reviews && data.due_reviews.length > 0) {
                html += '<div class="col-12 mt-3"><h6>مراجعات مستحقة:</h6></div>';
                data.due_reviews.forEach(review => {
                    html += `
                        <div class="col-md-4">
                            <div class="alert alert-warning py-2 mb-1">
                                <small>الجزء ${review.part_number} - ${review.next_review_date}</small>
                            </div>
                        </div>
                    `;
                });
            }

            container.innerHTML = html;
        },

        loadMemorizationStats: function() {
            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';

            fetch('/api/student/tarteel/memorization-stats', {
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                }
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
                statCompletedParts: data.completed_parts,
                statTotalParts: data.total_parts,
                statDueReviews: data.due_reviews,
                statRecentMemorization: data.recently_memorized,
            };

            Object.entries(elements).forEach(([id, value]) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            });
        },

        initPomodoro: function() {
            const startBtn = document.getElementById('pomodoroStart');
            const resetBtn = document.getElementById('pomodoroReset');
            const timerDisplay = document.getElementById('pomodoroTimer');

            if (!startBtn || !timerDisplay) return;

            startBtn.addEventListener('click', () => {
                if (this.pomodoroRunning) {
                    this.pausePomodoro();
                } else {
                    this.startPomodoro();
                }
            });

            resetBtn?.addEventListener('click', () => {
                this.resetPomodoro();
            });
        },

        startPomodoro: function() {
            this.pomodoroRunning = true;
            const startBtn = document.getElementById('pomodoroStart');
            const resetBtn = document.getElementById('pomodoroReset');
            const timerDisplay = document.getElementById('pomodoroTimer');
            const label = document.getElementById('pomodoroLabel');

            if (startBtn) {
                startBtn.innerHTML = '<i class="fas fa-pause me-1"></i>إيقاف';
                startBtn.classList.remove('start');
                startBtn.classList.add('pause');
            }
            if (resetBtn) resetBtn.classList.remove('d-none');
            if (label) label.textContent = 'جلسة الحفظ المتقن';

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
                startBtn.classList.remove('pause');
                startBtn.classList.add('start');
            }
            if (label) label.textContent = 'ميقاف مؤقت';
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
                startBtn.classList.remove('pause');
                startBtn.classList.add('start');
            }
            if (resetBtn) resetBtn.classList.add('d-none');
            if (label) label.textContent = 'مؤقت الحفظ';
        },

        updatePomodoroDisplay: function() {
            const timerDisplay = document.getElementById('pomodoroTimer');
            if (timerDisplay) {
                const mins = String(this.pomodoroMinutes).padStart(2, '0');
                const secs = String(this.pomodoroSeconds).padStart(2, '0');
                timerDisplay.textContent = `${mins}:${secs}`;
            }
        },

        completePomodoro: function() {
            this.pomodoroRunning = false;
            clearInterval(this.pomodoroInterval);
            this.showToast('تم إكمال جلسة الحفظ!', 'success');
            this.resetPomodoro();
        },

        initDailyGoal: function() {
            const goalHint = document.getElementById('goalHint');
            if (goalHint) {
                goalHint.style.cursor = 'pointer';
                goalHint.addEventListener('click', () => {
                    const newGoal = prompt('أدخل هدفك اليومي من الآيات:', '20');
                    if (newGoal && !isNaN(newGoal)) {
                        this.setDailyGoal(parseInt(newGoal));
                    }
                });
            }
            this.loadDailyProgress();
        },

        setDailyGoal: function(targetVerses) {
            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

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
                }
            })
            .catch(err => console.error('Error setting goal:', err));
        },

        loadDailyProgress: function() {
            this.updateGoalDisplay(0, 20);
        },

        updateGoalDisplay: function(current, target) {
            const progressEl = document.getElementById('dailyGoalProgress');
            const progressBar = document.getElementById('goalProgressBar');

            if (progressEl) progressEl.textContent = `${current} / ${target}`;
            if (progressBar) {
                const percentage = target > 0 ? (current / target) * 100 : 0;
                progressBar.style.width = percentage + '%';
            }
        },

        loadDueReviews: function() {
            const container = document.getElementById('reviewScheduleList');
            if (!container) return;

            container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p>جارٍ تحميل المراجعات...</p></div>';
        },

        loadLastMemorized: function() {
            const container = document.getElementById('lastMemorizedSection');
            if (!container) return;

            const apiToken = document.querySelector('meta[name=api-token]')?.content || '';

            fetch('/api/student/tarteel/last-memorized', {
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    this.renderLastMemorized(data.data);
                }
            })
            .catch(err => console.error('Error loading last memorized:', err));
        },

        renderLastMemorized: function(data) {
            const container = document.getElementById('lastMemorizedSection');
            if (!container || !data.part) return;

            container.innerHTML = `
                <div class="text-center">
                    <h6>الجزء ${data.part.part_number}</h6>
                    <small class="text-muted">${data.part.last_recitation_date || 'تاريخ غير معروف'}</small>
                </div>
            `;
        },

        showToast: function(message, type) {
            const toastContainer = document.getElementById('toastContainer') || document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container';
            toastContainer.style.cssText = 'position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);z-index:9999;';

            const toast = document.createElement('div');
            toast.className = type === 'success' ? 'toast-success' : 'toast-error';
            toast.style.cssText = 'background:linear-gradient(135deg,#1a7a4a,#2d9d63);color:white;padding:1rem 1.5rem;border-radius:10px;box-shadow:0 4px 20px rgba(26,122,74,0.4);animation:slideUp 0.3s ease;margin-top:0.5rem;';
            toast.textContent = message;

            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);

            setTimeout(() => {
                toast.remove();
                if (toastContainer.children.length === 0) {
                    toastContainer.remove();
                }
            }, 3000);
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('memorization-tab')) {
            MemorizationTab.init();
        }
    });

    window.MemorizationTab = MemorizationTab;
})();