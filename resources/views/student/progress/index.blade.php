@extends('layouts.app')

@section('title', 'تقدمي')
@section('page-title', 'تقدمي في الحفظ')
@section('role-label', 'طالب')

@section('sidebar-menu')
    <span class="nav-section-title">الرئيسية</span>
    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحتي الشخصية
    </a>
    <span class="nav-section-title mt-2">القرآن الكريم</span>
    <a href="{{ route('student.quran') }}" class="nav-link {{ request()->routeIs('student.quran*') ? 'active' : '' }}">
        <i class="fas fa-book-quran"></i> قراءة القرآن
    </a>
    <span class="nav-section-title mt-2">المتابعة</span>
    <a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
        <i class="fas fa-microphone"></i> جلسات التسميع
    </a>
    <span class="nav-section-title mt-2">التقدم</span>
    <a href="{{ route('student.progress') }}" class="nav-link {{ request()->routeIs('student.progress*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> تقدمي
    </a>
    <a href="{{ route('student.achievements') }}" class="nav-link {{ request()->routeIs('student.achievements*') ? 'active' : '' }}">
        <i class="fas fa-trophy"></i> إنجازاتي
    </a>
    <a href="{{ route('student.bookmarks') }}" class="nav-link {{ request()->routeIs('student.bookmarks*') ? 'active' : '' }}">
        <i class="fas fa-bookmark"></i> علاماتي
    </a>
    <span class="nav-section-title mt-2">الإعدادات</span>
    <a href="{{ route('student.profile.index') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
        <i class="fas fa-user-cog"></i> ملفي الشخصي
    </a>
@endsection

@section('content')
<!-- Streak Banner -->
<div class="card mb-4" style="background: linear-gradient(135deg, #FF6B35, #FF8C42); color: #fff;">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-4 text-center">
                <i class="fas fa-fire fa-3x opacity-75"></i>
            </div>
            <div class="col-8">
                <h3 class="mb-0">{{ $student->current_streak ?? 0 }}</h3>
                <small>يوم متتالي</small>
                <div class="mt-1">
                    <small class="opacity-75">أفضل سجل: {{ $student->longest_streak ?? 0 }} يوم</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-primary fw-bold" style="font-size: 1.5rem;">{{ $student->memorized_parts }}</div>
            <small class="text-muted">الأجزاء المحفوظة</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-success fw-bold" style="font-size: 1.5rem;">{{ $student->accuracy_rate }}%</div>
            <small class="text-muted">معدل الدقة</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-warning fw-bold" style="font-size: 1.5rem;">{{ $goal->target_pages ?? 2 }}</div>
            <small class="text-muted">الهدف اليومي</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-info fw-bold" style="font-size: 1.5rem;">{{ $todayProgress?->pages_memorized ?? 0 }}</div>
            <small class="text-muted">صفحات اليوم</small>
        </div>
    </div>
</div>

<!-- Daily Goal Progress -->
<div class="card mb-4">
    <div class="card-header">
        <h6><i class="fas fa-bullseye me-2"></i>الهدف اليومي</h6>
    </div>
    <div class="card-body">
        @php
            $goalProgress = $goal && $goal->target_pages > 0
                ? min(100, round((($todayProgress?->pages_memorized ?? 0) / $goal->target_pages) * 100))
                : 0;
        @endphp
        <div class="d-flex justify-content-between mb-2">
            <span>{{ $todayProgress?->pages_memorized ?? 0 }} / {{ $goal->target_pages ?? 2 }} صفحة</span>
            <span class="text-muted">{{ $goalProgress }}%</span>
        </div>
        <div class="progress" style="height: 20px;">
            <div class="progress-bar bg-success {{ $goalProgress >= 100 ? 'progress-bar-striped' : '' }}"
                 style="width: {{ $goalProgress }}%">
                @if($goalProgress >= 100)
                    <i class="fas fa-check"></i> مكتمل!
                @endif
            </div>
        </div>
        <div class="mt-2">
            <a href="{{ route('student.progress.goals') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-cog me-1"></i>تعديل الهدف
            </a>
        </div>
    </div>
</div>

<!-- Weekly Progress Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h6><i class="fas fa-chart-bar me-2"></i>تقدم الأسبوع</h6>
    </div>
    <div class="card-body">
        <canvas id="weeklyChart" height="200"></canvas>
    </div>
</div>

<!-- Achievements Preview -->
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h6><i class="fas fa-trophy me-2"></i>إنجازاتي</h6>
        <a href="{{ route('student.achievements') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
    </div>
    <div class="card-body">
        @if($earnedBadges->count() > 0)
            <div class="row g-2">
                @foreach($earnedBadges->take(6) as $badge)
                    <div class="col-4 col-lg-2 text-center">
                        <div class="p-2 rounded" style="background: {{ $badge->color }}20;">
                            <i class="fas {{ $badge->icon }} fa-2x" style="color: {{ $badge->color }};"></i>
                            <div class="small mt-1 fw-bold">{{ $badge->name }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-3">
                <i class="fas fa-trophy fa-3x opacity-25 mb-2"></i>
                <p class="mb-0">لا توجد إنجازات بعد</p>
                <small>استمر في الحفظ لكسب الإنجازات!</small>
            </div>
        @endif
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
