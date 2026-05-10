@extends('layouts.app')

@section('title', 'لوحة تحكم المشرف')
@section('page-title', 'لوحة التحكم')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')
<div class="row g-3">
    <!-- Stats Row -->
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #182B48, #2A4570);">
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
        <div class="quick-actions-card">
            <div class="qa-header">
                <div class="qa-header-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h5>الإجراءات السريعة</h5>
            </div>
            <div class="qa-body">
                <a href="{{ route('supervisor.attendance.index') }}" class="qa-item">
                    <div class="qa-item-icon success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="qa-item-content">
                        <span class="qa-item-title">تسجيل الحضور</span>
                        <span class="qa-item-desc">تسجيل حضور وغياب الطلاب</span>
                    </div>
                    <i class="fas fa-arrow-left qa-arrow"></i>
                </a>
                <a href="{{ route('supervisor.recitation.index') }}" class="qa-item primary-accent">
                    <div class="qa-item-icon primary">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <div class="qa-item-content">
                        <span class="qa-item-title">بدء جلسة تسميع</span>
                        <span class="qa-item-desc">تسميع جديد أو مراجعة</span>
                    </div>
                    <i class="fas fa-arrow-left qa-arrow"></i>
                </a>
                <a href="{{ route('supervisor.warnings.create') }}" class="qa-item">
                    <div class="qa-item-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="qa-item-content">
                        <span class="qa-item-title">إصدار إنذار</span>
                        <span class="qa-item-desc">إنذار طالب بسبب غياب أو تأخر</span>
                    </div>
                    <i class="fas fa-arrow-left qa-arrow"></i>
                </a>
                <a href="{{ route('supervisor.reports.index') }}" class="qa-item">
                    <div class="qa-item-icon info">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="qa-item-content">
                        <span class="qa-item-title">التقارير</span>
                        <span class="qa-item-desc">عرض تقارير الحضور والتسميع</span>
                    </div>
                    <i class="fas fa-arrow-left qa-arrow"></i>
                </a>
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
                                        <div class="user-avatar-sm">
                                            <i class="fas fa-user"></i>
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

@push('styles')
<style>
/* Quick Actions Card */
.quick-actions-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    overflow: hidden;
    height: 100%;
}

.qa-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 20px 22px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    position: relative;
}

.qa-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.qa-header-icon {
    width: 44px;
    height: 44px;
    background: rgba(212, 160, 48, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.qa-header h5 {
    color: #fff;
    font-weight: 700;
    font-size: 17px;
    margin: 0;
}

.qa-body {
    padding: 12px;
}

.qa-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-bottom: 8px;
    border: 1px solid transparent;
}

.qa-item:last-child {
    margin-bottom: 0;
}

.qa-item:hover {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.03) 0%, rgba(42, 69, 112, 0.06) 100%);
    border-color: rgba(212, 160, 48, 0.15);
}

.qa-item:hover .qa-arrow {
    transform: translateX(-5px);
    color: #d4a030;
}

.qa-item.primary-accent {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.05) 0%, rgba(42, 69, 112, 0.08) 100%);
    border-color: rgba(212, 160, 48, 0.2);
}

.qa-item-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.qa-item-icon.success {
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.15) 0%, rgba(46, 204, 113, 0.2) 100%);
    color: #27ae60;
}

.qa-item-icon.primary {
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    color: #d4a030;
    box-shadow: 0 4px 15px rgba(24, 43, 72, 0.25);
}

.qa-item-icon.warning {
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.15) 0%, rgba(230, 126, 34, 0.2) 100%);
    color: #f39c12;
}

.qa-item-icon.info {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.15) 0%, rgba(41, 128, 185, 0.2) 100%);
    color: #3498db;
}

.qa-item-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.qa-item-title {
    font-weight: 700;
    font-size: 14px;
    color: #182B48;
}

.qa-item-desc {
    font-size: 12px;
    color: #7f8c8d;
}

.qa-arrow {
    font-size: 12px;
    color: #bdc3c7;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .qa-item {
        padding: 12px;
    }

    .qa-item-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}
</style>
@endpush
