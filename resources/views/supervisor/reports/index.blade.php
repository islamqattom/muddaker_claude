@extends('layouts.app')
@section('title', 'التقارير')
@section('page-title', 'التقارير')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
@endsection

@php
$reports = [
    [
        'title' => 'تقرير التسميع',
        'desc' => 'عرض إحصائيات وتسجيلات جلسات التسميع للطلاب مع إمكانية التصفية حسب الطالب والنوع والفترة الزمنية',
        'icon' => 'fa-quran',
        'color' => 'primary',
        'gradient' => 'linear-gradient(135deg, #182B48 0%, #2A4570 100%)',
        'icon_bg' => 'linear-gradient(135deg, #d4a030 0%, #e6b84d 100%)',
        'link' => route('supervisor.reports.recitation'),
        'stat_color' => '#d4a030',
        'features' => ['إحصائيات شاملة', 'تصفية متقدمة', 'تصدير PDF']
    ],
    [
        'title' => 'تقرير الحضور والغياب',
        'desc' => 'عرض سجلات الحضور والغياب للطلاب مع إحصائيات كاملة ونسبة الحضور',
        'icon' => 'fa-calendar-check',
        'color' => 'success',
        'gradient' => 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
        'icon_bg' => 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
        'link' => route('supervisor.reports.attendance'),
        'stat_color' => '#059669',
        'features' => ['نسبة الحضور', 'حضور الطلاب', 'سجل كامل']
    ],
    [
        'title' => 'تقرير الطلاب',
        'desc' => 'تقرير مفصل لكل طالب يشمل进度 التسميع والحضور والإنجاز',
        'icon' => 'fa-users',
        'color' => 'info',
        'gradient' => 'linear-gradient(135deg, #0891b2 0%, #06b6d4 100%)',
        'icon_bg' => 'linear-gradient(135deg, #0891b2 0%, #06b6d4 100%)',
        'link' => route('supervisor.students.index'),
        'stat_color' => '#0891b2',
        'features' => ['بيانات شاملة', 'تقييم الأداء', 'سجل متكامل']
    ],
    [
        'title' => 'إحصائيات عامة',
        'desc' => 'عرض ملخص الإحصائيات العامة للمرافقين والإحصائيات الموسمية',
        'icon' => 'fa-chart-pie',
        'color' => 'warning',
        'gradient' => 'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
        'icon_bg' => 'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
        'link' => '#',
        'stat_color' => '#d97706',
        'features' => ['ملخص شامل', 'تقارير موسمية', 'تحليل البيانات']
    ]
];
@endphp

@push('styles')
<style>
.reports-index {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.reports-index .hero-section {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.reports-index .hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.reports-index .hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 20px;
}

.reports-index .hero-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    box-shadow: 0 8px 24px rgba(212, 160, 48, 0.4);
    flex-shrink: 0;
}

.reports-index .hero-text h1 {
    color: white;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 6px;
}

.reports-index .hero-text p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    margin: 0;
}

.reports-index .report-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.reports-index .report-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
}

.reports-index .report-card-header {
    padding: 20px 24px;
    position: relative;
    overflow: hidden;
}

.reports-index .report-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.reports-index .report-card-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    margin-bottom: 16px;
}

.reports-index .report-card-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 8px;
}

.reports-index .report-card-desc {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 16px;
}

.reports-index .report-card-features {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.reports-index .feature-badge {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04) 0%, rgba(42, 69, 112, 0.06) 100%);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: var(--navy);
    display: flex;
    align-items: center;
    gap: 4px;
}

.reports-index .feature-badge i {
    color: var(--gold);
}

.reports-index .report-card-footer {
    padding: 16px 24px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.03) 100%);
    border-top: 1px solid rgba(24, 43, 72, 0.04);
}

.reports-index .report-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    width: 100%;
}

.reports-index .report-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    color: white;
}

.reports-index .quick-stats {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.reports-index .quick-stats-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 18px 24px;
    position: relative;
    overflow: hidden;
}

.reports-index .quick-stats-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.reports-index .quick-stats-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.reports-index .quick-stats-header h5 i {
    color: var(--gold);
}

.reports-index .stat-card {
    padding: 20px;
    text-align: center;
    border-left: 1px solid rgba(24, 43, 72, 0.06);
    transition: all 0.3s ease;
}

.reports-index .stat-card:hover {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.03) 100%);
}

.reports-index .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 20px;
}

.reports-index .stat-value {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 4px;
}

.reports-index .stat-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.reports-index .islamic-border {
    border-radius: 16px;
    position: relative;
}

.reports-index .islamic-border::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
    border-radius: 0 0 0 4px;
}

.reports-index .bismillah {
    font-family: 'Amiri', serif;
    font-size: 28px;
    color: var(--navy);
    text-align: center;
    margin-bottom: 20px;
    opacity: 0.6;
}

@media (max-width: 768px) {
    .reports-index .stat-card {
        border-left: none;
        border-bottom: 1px solid rgba(24, 43, 72, 0.06);
    }
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="reports-index">
    <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>

    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="hero-text">
                <h1>التقارير والإحصائيات</h1>
                <p>عرض شامل للتقارير والإحصائيات الخاصة بالتسميع والحضور والإنجاز</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach($reports as $report)
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-card-header" style="background: {{ $report['gradient'] }};">
                    <div class="report-card-icon" style="background: {{ $report['icon_bg'] }};">
                        <i class="fas {{ $report['icon'] }}"></i>
                    </div>
                    <h4 class="report-card-title" style="color: white;">{{ $report['title'] }}</h4>
                </div>
                <div class="card-body" style="padding: 20px 24px;">
                    <p class="report-card-desc">{{ $report['desc'] }}</p>
                    <div class="report-card-features">
                        @foreach($report['features'] as $feature)
                        <span class="feature-badge">
                            <i class="fas fa-check-circle"></i>
                            {{ $feature }}
                        </span>
                        @endforeach
                    </div>
                </div>
                <div class="report-card-footer">
                    <a href="{{ $report['link'] }}" class="report-btn" style="background: {{ $report['gradient'] }}; color: white;">
                        <i class="fas fa-arrow-right"></i>
                        عرض التقرير
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="quick-stats islamic-border">
        <div class="quick-stats-header">
            <h5>
                <i class="fas fa-chart-line"></i>
                ملخص سريع لهذا الشهر
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                @php
                    $currentMonth = \Carbon\Carbon::now()->startOfMonth();
                    $currentMonthEnd = \Carbon\Carbon::now()->endOfMonth();
                    $totalStudents = $students->count();
                    $monthSessions = \App\Models\RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', auth()->id()))->whereBetween('session_date', [$currentMonth, $currentMonthEnd])->count();
                    $monthAttendances = \App\Models\Attendance::where('supervisor_id', auth()->id())->whereBetween('date', [$currentMonth, $currentMonthEnd])->count();
                    $totalAttendance = \App\Models\Attendance::where('supervisor_id', auth()->id())->whereBetween('date', [$currentMonth, $currentMonthEnd])->count();
                    $presentAttendance = \App\Models\Attendance::where('supervisor_id', auth()->id())->whereBetween('date', [$currentMonth, $currentMonthEnd])->where('status', 'present')->count();
                    $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100) : 0;
                @endphp
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(24, 43, 72, 0.1), rgba(42, 69, 112, 0.15)); color: var(--navy);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value" style="color: var(--navy);">{{ $totalStudents }}</div>
                        <div class="stat-label">إجمالي الطلاب</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(212, 160, 48, 0.15), rgba(230, 184, 77, 0.2)); color: var(--gold);">
                            <i class="fas fa-book-quran"></i>
                        </div>
                        <div class="stat-value" style="color: var(--gold);">{{ $monthSessions }}</div>
                        <div class="stat-label">جلسات التسميع</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(5, 150, 105, 0.15), rgba(16, 185, 129, 0.2)); color: #059669;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-value" style="color: #059669;">{{ $monthAttendances }}</div>
                        <div class="stat-label">سجلات الحضور</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(167, 139, 250, 0.2)); color: #8b5cf6;">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-value" style="color: #8b5cf6;">{{ $attendanceRate }}%</div>
                        <div class="stat-label">نسبة الحضور</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection