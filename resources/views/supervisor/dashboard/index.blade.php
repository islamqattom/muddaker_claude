@extends('layouts.app')

@section('title', 'لوحة تحكم المشرف')
@section('page-title', 'لوحة التحكم')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    <span class="nav-section-title">الرئيسية</span>
    <a href="{{ route('supervisor.dashboard') }}" class="nav-link {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحة التحكم
    </a>

    <span class="nav-section-title mt-2">إدارة الطلاب</span>
    <a href="{{ route('supervisor.students.index') }}" class="nav-link {{ request()->routeIs('supervisor.students.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> الطلاب
    </a>
    <a href="{{ route('supervisor.attendance.index') }}" class="nav-link {{ request()->routeIs('supervisor.attendance.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> الحضور والغياب
    </a>

    <span class="nav-section-title mt-2">التسميع</span>
    <a href="{{ route('supervisor.recitation.index') }}" class="nav-link {{ request()->routeIs('supervisor.recitation.*') ? 'active' : '' }}">
        <i class="fas fa-microphone"></i> جلسة تسميع جديدة
    </a>
    <a href="{{ route('supervisor.recitation.history') }}" class="nav-link">
        <i class="fas fa-history"></i> سجل الجلسات
    </a>

    <span class="nav-section-title mt-2">التقارير والإنذارات</span>
    <a href="{{ route('supervisor.reports.index') }}" class="nav-link {{ request()->routeIs('supervisor.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i> التقارير
    </a>
    <a href="{{ route('supervisor.warnings.index') }}" class="nav-link {{ request()->routeIs('supervisor.warnings.*') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle"></i> الإنذارات
    </a>
@endsection

@section('content')
<div class="row g-3">
    <!-- Stats Row -->
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #1a5276, #2980b9);">
            <i class="fas fa-users icon"></i>
            <div class="number">{{ $stats['total_students'] }}</div>
            <div class="label">إجمالي الطلاب</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
            <i class="fas fa-user-check icon"></i>
            <div class="number">{{ $stats['today_present'] }}</div>
            <div class="label">حاضر اليوم</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <i class="fas fa-user-times icon"></i>
            <div class="number">{{ $stats['today_absent'] }}</div>
            <div class="label">غائب اليوم</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <i class="fas fa-microphone icon"></i>
            <div class="number">{{ $stats['today_sessions'] }}</div>
            <div class="label">جلسات اليوم</div>
        </div>
    </div>
</div>

<!-- Alert if no attendance today -->
@if(!$stats['attendance_recorded'] && $stats['total_students'] > 0)
<div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
    <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
    <div>
        <strong>تنبيه:</strong> لم يتم تسجيل الحضور والغياب لهذا اليوم بعد.
        <a href="{{ route('supervisor.attendance.index') }}" class="btn btn-sm btn-warning ms-3">
            <i class="fas fa-calendar-check me-1"></i> تسجيل الحضور الآن
        </a>
    </div>
</div>
@endif

<div class="row g-4 mt-1">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6><i class="fas fa-bolt me-2"></i>الإجراءات السريعة</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <a href="{{ route('supervisor.attendance.index') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar-check me-2"></i>تسجيل الحضور والغياب
                        </a>
                    </div>
                    <div class="col-12 col-md-6">
                        <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-primary w-100">
                            <i class="fas fa-microphone me-2"></i>بدء جلسة تسميع
                        </a>
                    </div>
                    <div class="col-12 col-md-6">
                        <a href="{{ route('supervisor.warnings.create') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-exclamation-triangle me-2"></i>إصدار إنذار
                        </a>
                    </div>
                    <div class="col-12 col-md-6">
                        <a href="{{ route('supervisor.reports.index') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-chart-bar me-2"></i>التقارير
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Students Status -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-users me-2"></i>حالة الطلاب اليوم</h6>
                <a href="{{ route('supervisor.attendance.index') }}" class="btn btn-sm btn-outline-primary">
                    تسجيل الحضور
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>اسم الطالب</th>
                                <th>المستوى</th>
                                <th>الحضور</th>
                                <th>جلسات اليوم</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students->take(8) as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;">
                                            {{ mb_substr($student->user->name, 0, 1) }}
                                        </div>
                                        {{ $student->user->name }}
                                    </div>
                                </td>
                                <td>{{ $student->level->name ?? '-' }}</td>
                                <td>
                                    @php $att = $student->today_attendance; @endphp
                                    @if($att)
                                        <span class="badge bg-{{ $att->status_color }}">{{ $att->status_label }}</span>
                                        @if($att->arrival_time)
                                            <small class="text-muted d-block">{{ $att->arrival_time }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">غير مسجل</span>
                                    @endif
                                </td>
                                <td>
                                    @php $sessionCount = $student->recitationSessions()->whereDate('session_date', today())->count(); @endphp
                                    <span class="badge {{ $sessionCount > 0 ? 'bg-success' : 'bg-light text-dark' }}">
                                        {{ $sessionCount }} جلسة
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('supervisor.students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    لا يوجد طلاب مسجلون
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sessions -->
<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-history me-2"></i>آخر جلسات التسميع</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الطالب</th>
                                <th>النوع</th>
                                <th>الجزء</th>
                                <th>الأخطاء</th>
                                <th>النتيجة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSessions as $session)
                            <tr>
                                <td>{{ $session->student->user->name }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $session->type_label }}</span>
                                </td>
                                <td>{{ $session->part ? 'الجزء '.$session->part->part_number : '-' }}</td>
                                <td>
                                    <span class="text-danger fw-bold">{{ $session->total_errors }}</span>
                                    <small class="text-muted">/ {{ $session->total_hesitations }} تردد</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                                </td>
                                <td><small class="text-muted">{{ $session->session_date->format('d/m/Y') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-3 text-muted">لا توجد جلسات بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Warnings -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>آخر الإنذارات</h6>
                <a href="{{ route('supervisor.warnings.create') }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($recentWarnings as $warning)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="font-size:13px;">{{ $warning->student->user->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:12px;">{{ $warning->title }}</div>
                            <span class="badge bg-{{ $warning->type === 'attendance' ? 'warning text-dark' : 'danger' }}" style="font-size:11px;">
                                {{ $warning->type_label }}
                            </span>
                        </div>
                        <small class="text-muted">{{ $warning->issue_date->format('d/m') }}</small>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success d-block"></i>
                    لا توجد إنذارات
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
