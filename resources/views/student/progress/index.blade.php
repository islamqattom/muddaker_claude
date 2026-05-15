@extends('layouts.app')

@section('title', 'تقدمي')
@section('page-title', 'تقدمي في الحفظ')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.progress-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #9b59b6;
}

.progress-page .page-hero {
    background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.progress-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.progress-page .hero-icon {
    width: 56px; height: 56px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    backdrop-filter: blur(5px);
}

.progress-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.progress-page .hero-sub { color: rgba(255,255,255,0.8); font-size: 13px; margin: 0; }
.progress-page .hero-streak { color: white; font-size: 48px; font-weight: 800; line-height: 1; }
.progress-page .hero-streak-label { color: rgba(255,255,255,0.8); font-size: 13px; }

.progress-page .stat-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    transition: all 0.3s ease;
}

.progress-page .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }

.progress-page .stat-card-body {
    padding: 20px;
    text-align: center;
}

.progress-page .stat-icon {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: 0 auto 12px;
}

.progress-page .stat-icon.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: var(--gold); }
.progress-page .stat-icon.success { background: linear-gradient(135deg, var(--success), #2ecc71); color: white; }
.progress-page .stat-icon.warning { background: linear-gradient(135deg, var(--warning), #e67e22); color: white; }
.progress-page .stat-icon.info { background: linear-gradient(135deg, var(--info), #2980b9); color: white; }

.progress-page .stat-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1; }
.progress-page .stat-label { font-size: 11px; color: #6c7a89; font-weight: 500; margin-top: 4px; text-transform: uppercase; }

.progress-page .goal-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.progress-page .goal-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
}

.progress-page .goal-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; display: flex; align-items: center; gap: 10px; }
.progress-page .goal-header h5 i { color: var(--gold); }

.progress-page .goal-header .btn-goal {
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.progress-page .goal-header .btn-goal:hover { background: rgba(255,255,255,0.3); }

.progress-page .goal-body { padding: 24px; }

.progress-page .progress-bar {
    height: 20px;
    border-radius: 10px;
    background: rgba(24,43,72,0.08);
    overflow: hidden;
}

.progress-page .progress-bar-fill {
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(90deg, var(--success), #2ecc71);
    transition: width 0.4s ease;
}

.progress-page .chart-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.progress-page .chart-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.progress-page .chart-header i { color: var(--gold); font-size: 18px; }
.progress-page .chart-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.progress-page .chart-body { padding: 24px; }

.progress-page .achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 12px;
}

.progress-page .achievement-item {
    text-align: center;
    padding: 16px 12px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.06));
    border-radius: 12px;
    transition: all 0.2s ease;
}

.progress-page .achievement-item:hover { transform: translateY(-2px); }

.progress-page .achievement-icon {
    width: 50px; height: 50px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: 0 auto 10px;
}

.progress-page .achievement-name { font-size: 12px; font-weight: 700; color: var(--navy); }

.progress-page .empty-state { text-align: center; padding: 40px 20px; }
.progress-page .empty-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.progress-page .empty-icon i { font-size: 32px; color: var(--navy); opacity: 0.4; }

@media (max-width: 992px) {
    .progress-page .page-hero { padding: 20px 24px; }
    .progress-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .progress-page .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="progress-page">
    <div class="page-hero">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="hero-icon">
                    <i class="fas fa-fire"></i>
                </div>
            </div>
            <div class="col">
                <div class="hero-streak">{{ $student->current_streak ?? 0 }}</div>
                <div class="hero-streak-label">يوم متتالي</div>
            </div>
            <div class="col text-start">
                <small class="text-white-50">أفضل سجل: {{ $student->longest_streak ?? 0 }} يوم</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon primary">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-value">{{ $student->memorized_parts }}</div>
                    <div class="stat-label">الأجزاء المحفوظة</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon success">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="stat-value">{{ $student->accuracy_rate }}%</div>
                    <div class="stat-label">معدل الدقة</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon warning">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="stat-value">{{ $goal->target_pages ?? 2 }}</div>
                    <div class="stat-label">الهدف اليومي</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon info">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-value">{{ $todayProgress?->pages_memorized ?? 0 }}</div>
                    <div class="stat-label">صفحات اليوم</div>
                </div>
            </div>
        </div>
    </div>

    <div class="goal-card">
        <div class="goal-header">
            <h5><i class="fas fa-bullseye"></i>الهدف اليومي</h5>
            <a href="{{ route('student.progress.goals') }}" class="btn-goal">
                <i class="fas fa-cog me-1"></i>تعديل الهدف
            </a>
        </div>
        <div class="goal-body">
            @php
                $goalProgress = $goal && $goal->target_pages > 0
                    ? min(100, round((($todayProgress?->pages_memorized ?? 0) / $goal->target_pages) * 100))
                    : 0;
            @endphp
            <div class="d-flex justify-content-between mb-2">
                <span class="fw-semibold">{{ $todayProgress?->pages_memorized ?? 0 }} / {{ $goal->target_pages ?? 2 }} صفحة</span>
                <span class="text-muted">{{ $goalProgress }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: {{ $goalProgress }}%"></div>
            </div>
            @if($goalProgress >= 100)
                <div class="text-center mt-3">
                    <span class="badge" style="background: linear-gradient(135deg, var(--success), #2ecc71); color: white; padding: 8px 16px; border-radius: 20px;">
                        <i class="fas fa-check me-1"></i>مكتمل!
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <i class="fas fa-chart-bar"></i>
            <h5>تقدم الأسبوع</h5>
        </div>
        <div class="chart-body">
            <canvas id="weeklyChart" height="200"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <i class="fas fa-trophy"></i>
            <h5>إنجازاتي</h5>
            <a href="{{ route('student.achievements') }}" class="btn-goal ms-auto">
                عرض الكل <i class="fas fa-arrow-left me-1"></i>
            </a>
        </div>
        <div class="chart-body">
            @if($earnedBadges->count() > 0)
                <div class="achievements-grid">
                    @foreach($earnedBadges->take(6) as $badge)
                        <div class="achievement-item">
                            <div class="achievement-icon" style="background: {{ $badge->color }}20; color: {{ $badge->color }};">
                                <i class="fas {{ $badge->icon }}"></i>
                            </div>
                            <div class="achievement-name">{{ $badge->name }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h5 class="text-muted mb-2">لا توجد إنجازات بعد</h5>
                    <p class="text-muted small mb-0">استمر في الحفظ لكسب الإنجازات!</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const weeklyData = @json($weeklyProgress);

const labels = weeklyData.map(d => d.day_name);
const pagesData = weeklyData.map(d => d.pages_memorized);
const reviewedData = weeklyData.map(d => d.pages_reviewed);

new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'صفحات محفوظة',
                data: pagesData,
                backgroundColor: 'rgba(39, 174, 96, 0.7)',
            },
            {
                label: 'صفحات مراجعة',
                data: reviewedData,
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            rtl: true,
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        },
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endsection