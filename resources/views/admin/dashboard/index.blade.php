@extends('layouts.app')
@section('title', 'لوحة تحكم الإدارة')
@section('page-title', 'لوحة التحكم الرئيسية')
@section('role-label', 'مدير النظام')
@section('sidebar-menu')
<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> لوحة التحكم
</a>
<span class="nav-section-title mt-2">إدارة المستخدمين</span>
<a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <i class="fas fa-user-graduate"></i> الطلاب
    <span class="badge bg-primary">{{ \App\Models\Student::count() }}</span>
</a>
<a href="{{ route('admin.supervisors.index') }}" class="nav-link {{ request()->routeIs('admin.supervisors.*') ? 'active' : '' }}">
    <i class="fas fa-user-tie"></i> المشرفون
</a>
<a href="{{ route('admin.parents.index') }}" class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> أولياء الأمور
</a>
<span class="nav-section-title mt-2">الإعدادات</span>
<a href="{{ route('admin.levels.index') }}" class="nav-link {{ request()->routeIs('admin.levels.*') ? 'active' : '' }}">
    <i class="fas fa-layer-group"></i> المستويات
</a>
<a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <i class="fas fa-cog"></i> إعدادات النظام
</a>
<span class="nav-section-title mt-2">التقارير والإنذارات</span>
<a href="{{ route('admin.sessions.index') }}" class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
    <i class="fas fa-microphone"></i> جلسات التسميع
</a>
<a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
    <i class="fas fa-chart-line"></i> التقارير الشاملة
</a>
<a href="{{ route('admin.warnings.index') }}" class="nav-link {{ request()->routeIs('admin.warnings.*') ? 'active' : '' }}">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
</a>
@endsection
@section('content')
<div class="row g-4">
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#182B48,#2A4570);">
            <i class="fas fa-user-graduate icon"></i>
            <div class="number">{{ $stats['total_students'] }}</div>
            <div class="label">إجمالي الطلاب</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#8e44ad,#9b59b6);">
            <i class="fas fa-user-tie icon"></i>
            <div class="number">{{ $stats['total_supervisors'] }}</div>
            <div class="label">المشرفون</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#2ecc71);">
            <i class="fas fa-user-check icon"></i>
            <div class="number">{{ $stats['today_present'] }}</div>
            <div class="label">حاضر اليوم</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#e67e22);">
            <i class="fas fa-microphone icon"></i>
            <div class="number">{{ $stats['today_sessions'] }}</div>
            <div class="label">جلسات اليوم</div>
        </div>
    </div>
</div>

{{-- Center Info & Working Hours --}}
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-university text-primary fa-lg me-3"></i>
                    <div>
                        <strong class="text-primary">{{ config('app.center_name', 'مركز التسميع') }}</strong>
                        @if(config('app.center_phone') || config('app.center_email'))
                            <span class="text-muted me-2">|</span>
                            @if(config('app.center_phone'))<span class="small"><i class="fas fa-phone me-1"></i>{{ config('app.center_phone') }}</span>@endif
                            @if(config('app.center_email'))<span class="small me-2"><i class="fas fa-envelope me-1"></i>{{ config('app.center_email') }}</span>@endif
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary"><i class="fas fa-clock me-1"></i>{{ config('app.work_start_time', '08:00') }} - {{ config('app.work_end_time', '20:00') }}</span>
                    <span class="badge bg-secondary ms-1">{{ config('app.work_days', 'saturday,sunday,monday,tuesday,thursday') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Supervisor Performance --}}
<div class="row g-4 mt-1">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-user-tie me-2"></i>@lang('Supervisor Performance')</h5>
    </div>
    @forelse($supervisorsPerformance as $sp)
        <x-supervisor-performance-card :supervisor="$sp" :perf="$sp['data']" />
    @empty
        <div class="col-12">
            <p class="text-muted">@lang('No supervisors found')</p>
        </div>
    @endforelse
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-bar me-2"></i>إحصائيات آخر 7 أيام</h6>
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6><i class="fas fa-bolt me-2"></i>إجراءات سريعة</h6></div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>إضافة طالب جديد
                    </a>
                    <a href="{{ route('admin.supervisors.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i>إضافة مشرف جديد
                    </a>
                    <a href="{{ route('admin.reports.recitation') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>تقرير التسميع
                    </a>
                    <a href="{{ route('admin.reports.attendance') }}" class="btn btn-outline-success">
                        <i class="fas fa-calendar me-2"></i>تقرير الحضور
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-history me-2"></i>آخر جلسات التسميع</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>الطالب</th><th>المشرف</th><th>النوع</th><th>النتيجة</th><th>التاريخ</th></tr></thead>
                        <tbody>
                            @forelse($recentSessions as $session)
                            <tr>
                                <td>{{ $session->student->user->name }}</td>
                                <td><small>{{ $session->supervisor->name }}</small></td>
                                <td><span class="badge bg-info text-dark">{{ $session->type_label }}</span></td>
                                <td><span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span></td>
                                <td><small>{{ $session->session_date->format('d/m') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted">لا توجد جلسات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-exclamation-triangle me-2"></i>آخر الإنذارات</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>الطالب</th><th>النوع</th><th>المشرف</th><th>التاريخ</th></tr></thead>
                        <tbody>
                            @forelse($recentWarnings as $warning)
                            <tr>
                                <td>{{ $warning->student->user->name }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $warning->type_label }}</span></td>
                                <td><small>{{ $warning->issuedBy->name }}</small></td>
                                <td><small>{{ $warning->issue_date->format('d/m') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">لا توجد إنذارات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const weeklyData = @json($weeklySessions);
const ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: weeklyData.map(d => d.date),
        datasets: [
            { label: 'جلسات ناجحة', data: weeklyData.map(d => d.passed), backgroundColor: '#27ae60' },
            { label: 'جلسات راسبة', data: weeklyData.map(d => d.failed), backgroundColor: '#c0392b' },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endpush
