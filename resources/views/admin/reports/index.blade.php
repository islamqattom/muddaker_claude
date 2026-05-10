@extends('layouts.app')
@section('title', 'التقارير الشاملة')
@section('page-title', 'التقارير الشاملة')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
@endsection

@section('content')
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
    --card-shadow-hover: 0 12px 40px rgba(24, 43, 72, 0.15);
}

body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #6c7a89; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    position: relative;
    padding: 25px;
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 18px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
}
.stat-card:hover { transform: translateY(-8px) scale(1.02); }
.stat-card::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}
.stat-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.stat-card.primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); }
.stat-card.info { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.stat-card.success { background: linear-gradient(135deg, var(--success) 0%, #219a52 100%); }
.stat-card.danger { background: linear-gradient(135deg, var(--danger) 0%, #a93226 100%); }
.stat-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: #fff;
    backdrop-filter: blur(5px);
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.1);
}
.stat-icon i { color: var(--gold); }
.stat-content { flex: 1; position: relative; z-index: 1; }
.stat-number { font-size: 36px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 6px; }
.stat-label { font-size: 13px; color: rgba(255,255,255,0.85); font-weight: 500; letter-spacing: 0.5px; }
.stat-decoration {
    position: absolute;
    bottom: 15px; left: 15px;
    font-size: 50px; opacity: 0.1; color: #fff;
}

.reports-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    margin-bottom: 30px;
}
.report-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}
.report-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0; left: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.report-card:hover { transform: translateY(-8px); box-shadow: var(--card-shadow-hover); }
.report-card:hover::before { height: 7px; }
.report-header {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: 28px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
}
.report-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: #fff;
    box-shadow: 0 4px 15px rgba(212,160,48,0.35);
}
.report-title { color: #fff; font-size: 18px; font-weight: 700; }
.report-subtitle { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 4px; }
.report-body { padding: 25px; }
.report-description { color: #6c7a89; font-size: 15px; margin-bottom: 20px; line-height: 1.6; }

.report-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; border-radius: 12px;
    text-decoration: none; font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.report-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); color: #fff; }
.report-btn i { color: var(--gold); }

.success-rate-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
}
.success-rate-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.success-rate-header i { color: var(--gold); font-size: 20px; }
.success-rate-body { padding: 25px 28px; }

.progress-container { margin-bottom: 20px; }
.progress-bar-custom {
    height: 30px;
    background: linear-gradient(90deg, var(--success), #2ecc71, var(--success));
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    font-size: 14px;
    position: relative;
    overflow: hidden;
}
.progress-bar-custom::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shimmer 2s infinite;
}
@keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

.progress-stats { display: flex; justify-content: space-between; margin-top: 15px; }
.progress-stat { display: flex; align-items: center; gap: 8px; }
.progress-stat .dot { width: 12px; height: 12px; border-radius: 50%; }
.progress-stat .dot.success { background: var(--success); }
.progress-stat .dot.danger { background: var(--danger); }
.progress-stat .label { font-size: 13px; color: #6c7a89; }
.progress-stat .value { font-weight: 700; color: var(--primary); }

@media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 992px) {
    .reports-grid { grid-template-columns: 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
}
@media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="header-text">
            <h4>التقارير الشاملة</h4>
            <p>عرض وإحصائيات تقارير جمعية تحفيظ القرآن الكريم</p>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_students'] }}</div>
            <div class="stat-label">إجمالي الطلاب</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-users"></i></div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon"><i class="fas fa-microphone"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_sessions'] }}</div>
            <div class="stat-label">إجمالي الجلسات</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-quran"></i></div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['passed_sessions'] }}</div>
            <div class="stat-label">جلسات ناجحة</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-star"></i></div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['failed_sessions'] }}</div>
            <div class="stat-label">جلسات راسبة</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-exclamation"></i></div>
    </div>
</div>

<div class="reports-grid">
    <div class="report-card">
        <div class="report-header">
            <div class="report-icon">
                <i class="fas fa-quran"></i>
            </div>
            <div>
                <div class="report-title">تقارير التسميع</div>
                <div class="report-subtitle">جلسات التسميع والاختبارات</div>
            </div>
        </div>
        <div class="report-body">
            <p class="report-description">عرض تقارير شاملة لجلسات التسميع والاختبارات الشهرية والنهائية مع إحصائيات النجاح والرسوب</p>
            <a href="{{ route('admin.reports.recitation') }}" class="report-btn">
                <i class="fas fa-chart-line"></i>
                عرض التقارير
            </a>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <div class="report-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="report-title">تقارير الحضور</div>
                <div class="report-subtitle">سجلات الحضور والانصراف</div>
            </div>
        </div>
        <div class="report-body">
            <p class="report-description">عرض تقارير حضور وانصراف الطلاب مع نسب الحضور والغياب والتأخير لكل طالب</p>
            <a href="{{ route('admin.reports.attendance') }}" class="report-btn">
                <i class="fas fa-chart-line"></i>
                عرض التقارير
            </a>
        </div>
    </div>
</div>

@if($stats['total_sessions'] > 0)
<div class="success-rate-card">
    <div class="success-rate-header">
        <i class="fas fa-percentage"></i>
        <span>نسبة النجاح الإجمالية</span>
    </div>
    <div class="success-rate-body">
        @php
            $successRate = round(($stats['passed_sessions'] / $stats['total_sessions']) * 100);
        @endphp
        <div class="progress-container">
            <div class="progress-bar-custom" style="width: {{ $successRate }}%; background: linear-gradient(90deg, var(--success) 0%, #2ecc71 50%, var(--success) 100%);">
                {{ $successRate }}% نجاح
            </div>
        </div>
        <div class="progress-stats">
            <div class="progress-stat">
                <span class="dot success"></span>
                <span class="label">ناجح:</span>
                <span class="value">{{ $stats['passed_sessions'] }} جلسة</span>
            </div>
            <div class="progress-stat">
                <span class="dot danger"></span>
                <span class="label">راسب:</span>
                <span class="value">{{ $stats['failed_sessions'] }} جلسة</span>
            </div>
        </div>
    </div>
</div>
@endif
@endsection