@extends('layouts.app')
@section('title', 'لوحة تحكم الإدارة')
@section('page-title', 'لوحة التحكم الرئيسية')
@section('role-label', 'مدير النظام')
@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection
@section('content')

{{-- Welcome Section --}}
<div class="welcome-section">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
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
        <div class="col-lg-4">
            <div class="date-badge d-block text-center">
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
        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_students'] }}</div>
            <div class="stat-label">إجمالي الطلاب</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-book-quran"></i></div>
    </div>

    <div class="stat-card-modern secondary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_supervisors'] }}</div>
            <div class="stat-label">المشرفون</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-chalkboard-teacher"></i></div>
    </div>

    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_present'] }}</div>
            <div class="stat-label">حاضر اليوم</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-check-circle"></i></div>
    </div>

    <div class="stat-card-modern warning">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-microphone"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_sessions'] }}</div>
            <div class="stat-label">جلسات اليوم</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-quran"></i></div>
    </div>
</div>

{{-- Center Info (collapsible) --}}
<details class="dashboard-card dash-collapse mb-3">
    <summary>
        <span class="card-title"><i class="fas fa-mosque"></i><span>معلومات المركز</span></span>
    </summary>
    <div class="center-info-card border-0 shadow-none">
        <div class="center-info-content">
            <div class="center-icon"><i class="fas fa-mosque"></i></div>
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
</details>

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
        <div class="qa-body">
            <a href="{{ route('admin.students.create') }}" class="qa-item">
                <div class="qa-item-icon primary"><i class="fas fa-user-plus"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">إضافة طالب</span>
                    <span class="qa-item-desc">تسجيل طالب جديد</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('admin.supervisors.create') }}" class="qa-item">
                <div class="qa-item-icon purple"><i class="fas fa-user-tie"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">إضافة مشرف</span>
                    <span class="qa-item-desc">تعيين مشرف جديد</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('admin.reports.recitation') }}" class="qa-item">
                <div class="qa-item-icon info"><i class="fas fa-chart-line"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">تقرير التسميع</span>
                    <span class="qa-item-desc">عرض تقرير جلسات التسميع</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('admin.reports.attendance') }}" class="qa-item">
                <div class="qa-item-icon success"><i class="fas fa-calendar-check"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">تقرير الحضور</span>
                    <span class="qa-item-desc">عرض تقرير الحضور والغياب</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
        </div>
    </div>
</div>

{{-- Supervisor Performance (collapsible) --}}
<details class="dashboard-card dash-collapse mb-3">
    <summary>
        <span class="card-title"><i class="fas fa-users-cog"></i><span>أداء المشرفين</span></span>
    </summary>
    <div class="card-body-custom">
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
    </div>
</details>

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
                                    <div class="user-avatar"><i class="fas fa-user"></i></div>
                                    <span>{{ $session->student_name ?? ($session->student->user->name ?? 'غير معروف') }}</span>
                                </div>
                            </td>
                            <td class="supervisor-cell">{{ $session->supervisor->name ?? '—' }}</td>
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
                                    <div class="user-avatar warning"><i class="fas fa-user"></i></div>
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
