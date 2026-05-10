@extends('layouts.app')
@section('title', 'لوحة تحكم الإدارة')
@section('page-title', 'لوحة التحكم الرئيسية')
@section('role-label', 'مدير النظام')
@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection
@section('content')

{{-- Welcome Section --}}
<div class="welcome-section mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="welcome-card">
                <div class="welcome-icon">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="welcome-content">
                    <h4>مرحباً بك في نظام مدكر</h4>
                    <p>نظام التسميع الإلكتروني لإدارة جلسات القرآن الكريم</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="date-badge">
                <i class="fas fa-calendar-alt me-2"></i>
                <span id="currentDate"></span>
            </div>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_students'] }}</div>
            <div class="stat-label">إجمالي الطلاب</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-book-quran"></i>
        </div>
    </div>

    <div class="stat-card-modern secondary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_supervisors'] }}</div>
            <div class="stat-label">المشرفون</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
    </div>

    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_present'] }}</div>
            <div class="stat-label">حاضر اليوم</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>

    <div class="stat-card-modern warning">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-microphone"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_sessions'] }}</div>
            <div class="stat-label">جلسات اليوم</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-quran"></i>
        </div>
    </div>
</div>

{{-- Center Info --}}
<div class="center-info-card mb-4">
    <div class="center-info-content">
        <div class="center-icon">
            <i class="fas fa-mosque"></i>
        </div>
        <div class="center-details">
            <h5>{{ config('app.center_name', 'مركز التسميع') }}</h5>
            <div class="center-meta">
                @if(config('app.center_phone'))
                    <span><i class="fas fa-phone me-1"></i>{{ config('app.center_phone') }}</span>
                @endif
                @if(config('app.center_email'))
                    <span><i class="fas fa-envelope me-1"></i>{{ config('app.center_email') }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="center-work-time">
        <div class="time-badge">
            <i class="fas fa-clock"></i>
            <span>{{ config('app.work_start_time', '08:00') }} - {{ config('app.work_end_time', '20:00') }}</span>
        </div>
        <div class="days-badge">
            @foreach(explode(',', config('app.work_days', 'saturday,sunday,monday,tuesday,thursday')) as $day)
                <span class="day-tag">{{ __($day) }}</span>
            @endforeach
        </div>
    </div>
</div>

{{-- Main Content Grid --}}
<div class="dashboard-grid">
    {{-- Chart Section --}}
    <div class="dashboard-card chart-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-chart-bar"></i>
                <span>إحصائيات آخر 7 أيام</span>
            </div>
        </div>
        <div class="card-body-custom">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="dashboard-card actions-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-bolt"></i>
                <span>إجراءات سريعة</span>
            </div>
        </div>
        <div class="card-body-custom">
            <div class="actions-grid">
                <a href="{{ route('admin.students.create') }}" class="action-btn primary">
                    <div class="action-icon"><i class="fas fa-user-plus"></i></div>
                    <span>إضافة طالب</span>
                </a>
                <a href="{{ route('admin.supervisors.create') }}" class="action-btn secondary">
                    <div class="action-icon"><i class="fas fa-user-tie"></i></div>
                    <span>إضافة مشرف</span>
                </a>
                <a href="{{ route('admin.reports.recitation') }}" class="action-btn info">
                    <div class="action-icon"><i class="fas fa-chart-line"></i></div>
                    <span>تقرير التسميع</span>
                </a>
                <a href="{{ route('admin.reports.attendance') }}" class="action-btn success">
                    <div class="action-icon"><i class="fas fa-calendar-check"></i></div>
                    <span>تقرير الحضور</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Supervisor Performance --}}
<div class="section-header">
    <div class="section-icon"><i class="fas fa-users-cog"></i></div>
    <h5>أداء المشرفين</h5>
</div>
<div class="supervisors-grid">
    @forelse($supervisorsPerformance as $sp)
        <x-supervisor-performance-card :supervisor="$sp" :perf="$sp['data']" />
    @empty
        <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <p>لا يوجد مشرفون</p>
        </div>
    @endforelse
</div>

{{-- Tables Section --}}
<div class="tables-grid">
    {{-- Recent Sessions --}}
    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-microphone-alt"></i>
                <span>آخر جلسات التسميع</span>
            </div>
            <a href="{{ route('admin.sessions.index') }}" class="view-all-btn">
                عرض الكل <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>المشرف</th>
                            <th>النوع</th>
                            <th>النتيجة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSessions as $session)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                    <span>{{ $session->student->user->name }}</span>
                                </div>
                            </td>
                            <td class="supervisor-cell">{{ $session->supervisor->name }}</td>
                            <td><span class="badge-modern info">{{ $session->type_label }}</span></td>
                            <td><span class="badge-modern {{ $session->status_color }}">{{ $session->status_label }}</span></td>
                            <td class="date-cell">{{ $session->session_date->format('d/m') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-cell">لا توجد جلسات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Warnings --}}
    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-exclamation-triangle"></i>
                <span>آخر الإنذارات</span>
            </div>
            <a href="{{ route('admin.warnings.index') }}" class="view-all-btn">
                عرض الكل <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>النوع</th>
                            <th>المشرف</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWarnings as $warning)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar warning">
                                    <i class="fas fa-user"></i>
                                </div>
                                    <span>{{ $warning->student->user->name }}</span>
                                </div>
                            </td>
                            <td><span class="badge-modern warning">{{ $warning->type_label }}</span></td>
                            <td class="supervisor-cell">{{ $warning->issuedBy->name }}</td>
                            <td class="date-cell">{{ $warning->issue_date->format('d/m') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="empty-cell">لا توجد إنذارات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Welcome Section */
.welcome-section {
    margin-bottom: 25px;
}

.welcome-card {
    display: flex;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, #182B48 0%, #1a3356 100%);
    padding: 25px 30px;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(212,160,48,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.welcome-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.welcome-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(212,160,48,0.4);
    position: relative;
    z-index: 1;
}

.welcome-content {
    position: relative;
    z-index: 1;
}

.welcome-content h4 {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.welcome-content p {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    margin: 0;
}

.date-badge {
    background: rgba(255,255,255,0.1);
    padding: 12px 20px;
    border-radius: 12px;
    color: #d4a030;
    font-weight: 600;
    font-size: 14px;
    border: 1px solid rgba(212,160,48,0.2);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 576px) {
    .stats-grid { grid-template-columns: 1fr; }
}

.stat-card-modern {
    position: relative;
    padding: 25px;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    transition: all 0.3s ease;
}

.stat-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.stat-card-modern.primary {
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
}

.stat-card-modern.secondary {
    background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
}

.stat-card-modern.success {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
}

.stat-card-modern.warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-card-bg {
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.stat-icon {
    width: 55px;
    height: 55px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    backdrop-filter: blur(5px);
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
    position: relative;
    z-index: 1;
}

.stat-number {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: rgba(255,255,255,0.8);
    font-weight: 500;
}

.stat-decoration {
    position: absolute;
    bottom: 10px;
    left: 15px;
    font-size: 40px;
    opacity: 0.15;
    color: #fff;
}

/* Center Info Card */
.center-info-card {
    background: #fff;
    border-radius: 16px;
    padding: 25px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

@media (max-width: 768px) {
    .center-info-card { flex-direction: column; text-align: center; }
}

.center-info-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.center-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #d4a030;
}

.center-details h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.center-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.center-meta span {
    color: #7f8c8d;
    font-size: 13px;
}

.center-meta i {
    color: #d4a030;
    margin-left: 4px;
}

.center-work-time {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-end;
}

@media (max-width: 768px) {
    .center-work-time { align-items: center; }
}

.time-badge {
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(26,51,86,0.1));
    padding: 8px 16px;
    border-radius: 8px;
    color: #182B48;
    font-weight: 600;
    font-size: 14px;
}

.time-badge i {
    color: #d4a030;
    margin-left: 8px;
}

.days-badge {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .days-badge { justify-content: center; }
}

.day-tag {
    background: #f0f4f8;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    color: #182B48;
    font-weight: 500;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) {
    .dashboard-grid { grid-template-columns: 1fr; }
}

.dashboard-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.card-header-custom {
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.card-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
}

.card-title i {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.card-body-custom {
    padding: 25px;
}

/* Actions Card */
.actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px 15px;
    border-radius: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    border: 2px solid transparent;
}

.action-btn:hover {
    transform: translateY(-3px);
}

.action-btn.primary {
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    color: #fff;
}

.action-btn.primary:hover {
    box-shadow: 0 10px 30px rgba(24,43,72,0.3);
}

.action-btn.secondary {
    background: rgba(142,68,173,0.1);
    color: #8e44ad;
    border-color: rgba(142,68,173,0.2);
}

.action-btn.secondary:hover {
    background: #8e44ad;
    color: #fff;
}

.action-btn.info {
    background: rgba(52,152,219,0.1);
    color: #3498db;
    border-color: rgba(52,152,219,0.2);
}

.action-btn.info:hover {
    background: #3498db;
    color: #fff;
}

.action-btn.success {
    background: rgba(39,174,96,0.1);
    color: #27ae60;
    border-color: rgba(39,174,96,0.2);
}

.action-btn.success:hover {
    background: #27ae60;
    color: #fff;
}

.action-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.action-btn.secondary .action-icon,
.action-btn.info .action-icon,
.action-btn.success .action-icon {
    background: rgba(0,0,0,0.05);
}

.action-btn span {
    font-size: 13px;
    font-weight: 600;
}

/* Section Header */
.section-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.section-header h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.section-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
}

/* Supervisors Grid */
.supervisors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

/* Tables Grid */
.tables-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) {
    .tables-grid { grid-template-columns: 1fr; }
}

.view-all-btn {
    color: #d4a030;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.view-all-btn:hover {
    color: #B4802C;
}

.view-all-btn i {
    font-size: 11px;
}

/* Data Table */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8f9fa;
}

.data-table th {
    padding: 14px 20px;
    font-size: 12px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
}

.data-table td {
    padding: 14px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.data-table tbody tr {
    transition: all 0.2s;
}

.data-table tbody tr:hover {
    background: #fafbfc;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.user-avatar.warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
}

.supervisor-cell {
    color: #7f8c8d;
    font-size: 13px;
}

.date-cell {
    color: #7f8c8d;
    font-size: 13px;
}

.badge-modern {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.badge-modern.info {
    background: rgba(52,152,219,0.15);
    color: #3498db;
}

.badge-modern.success, .badge-modern.green {
    background: rgba(39,174,96,0.15);
    color: #27ae60;
}

.badge-modern.danger, .badge-modern.red {
    background: rgba(192,57,43,0.15);
    color: #c0392b;
}

.badge-modern.warning {
    background: rgba(243,156,18,0.15);
    color: #f39c12;
}

.badge-modern.orange {
    background: rgba(230,126,34,0.15);
    color: #e67e22;
}

.empty-cell, .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #bdc3c7;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.empty-state i {
    font-size: 40px;
    color: #d4a030;
    opacity: 0.5;
}
</style>

@endsection

@push('scripts')
<script>
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('ar-SA', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

const weeklyData = @json($weeklySessions);
const ctx = document.getElementById('weeklyChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: weeklyData.map(d => d.date),
        datasets: [
            {
                label: 'جلسات ناجحة',
                data: weeklyData.map(d => d.passed),
                backgroundColor: 'rgba(39,174,96,0.8)',
                borderColor: '#27ae60',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            },
            {
                label: 'جلسات راسبة',
                data: weeklyData.map(d => d.failed),
                backgroundColor: 'rgba(192,57,43,0.8)',
                borderColor: '#c0392b',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
                rtl: true,
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 20,
                    font: { family: 'Tajawal', size: 12 }
                }
            },
            tooltip: {
                rtl: true,
                textDirection: 'rtl',
                backgroundColor: '#182B48',
                titleFont: { family: 'Tajawal', size: 14 },
                bodyFont: { family: 'Tajawal', size: 13 },
                padding: 15,
                cornerRadius: 10
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.05)',
                    drawBorder: false
                },
                ticks: {
                    font: { family: 'Tajawal', size: 11 },
                    color: '#7f8c8d'
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    font: { family: 'Tajawal', size: 11 },
                    color: '#7f8c8d'
                }
            }
        }
    }
});
</script>
@endpush