@extends('layouts.app')

@section('title', 'لوحة تحكم المشرف')
@section('page-title', 'لوحة التحكم')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')

<div class="welcome-section">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <div class="welcome-card">
                <div class="welcome-icon">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="welcome-content">
                    <h4>مرحباً بك {{ auth()->user()->name }}</h4>
                    <p>نظام إدارة جلسات القرآن الكريم</p>
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

@if(!$stats['attendance_recorded'] && $stats['total_students'] > 0)
<div class="alert-warning-custom mb-3">
    <div class="alert-content">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>تنبيه:</strong> لم يتم تسجيل الحضور والغياب لهذا اليوم بعد.
            <a href="{{ route('supervisor.attendance.index') }}" class="alert-link">سجل الحضور الآن</a>
        </div>
    </div>
</div>
@endif

<div class="stats-grid">
    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_students'] }}</div>
            <div class="stat-label">إجمالي الطلاب</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-user-graduate"></i></div>
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

    <div class="stat-card-modern danger">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-user-times"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_absent'] }}</div>
            <div class="stat-label">غائب اليوم</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-times-circle"></i></div>
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

<div class="dashboard-grid">
    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-users"></i>
                <span>حالة الطلاب اليوم</span>
            </div>
            <a href="{{ route('supervisor.attendance.index') }}" class="view-all-btn">
                تسجيل الحضور <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
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
                            <td class="row-num">{{ $index + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar"><i class="fas fa-user"></i></div>
                                    <span>{{ $student->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($student->level)
                                    <span class="level-badge">{{ $student->level->name }}</span>
                                @else
                                    <span class="no-data">-</span>
                                @endif
                            </td>
                            <td>
                                @php $att = $student->today_attendance; @endphp
                                @if($att)
                                    <span class="badge-modern {{ $att->status_color }}">{{ $att->status_label }}</span>
                                    @if($att->arrival_time)
                                        <small class="text-muted d-block">{{ $att->arrival_time }}</small>
                                    @endif
                                @else
                                    <span class="badge-modern secondary">غير مسجل</span>
                                @endif
                            </td>
                            <td>
                                @php $sessionCount = $student->recitationSessions()->whereDate('session_date', today())->count(); @endphp
                                <span class="badge-modern {{ $sessionCount > 0 ? 'success' : 'secondary' }}">
                                    {{ $sessionCount }} جلسة
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('supervisor.students.show', $student) }}" class="action-btn view" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-cell">لا يوجد طلاب</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card actions-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-bolt"></i>
                <span>إجراءات سريعة</span>
            </div>
        </div>
        <div class="qa-body">
            <a href="{{ route('supervisor.attendance.index') }}" class="qa-item">
                <div class="qa-item-icon success"><i class="fas fa-calendar-check"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">تسجيل الحضور</span>
                    <span class="qa-item-desc">تسجيل حضور وغياب الطلاب</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('supervisor.recitation.index') }}" class="qa-item">
                <div class="qa-item-icon primary"><i class="fas fa-microphone"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">بدء جلسة تسميع</span>
                    <span class="qa-item-desc">تسميع جديد أو مراجعة</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('supervisor.warnings.create') }}" class="qa-item">
                <div class="qa-item-icon warning"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">إصدار إنذار</span>
                    <span class="qa-item-desc">إنذار طالب بسبب غياب أو تأخر</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('supervisor.reports.index') }}" class="qa-item">
                <div class="qa-item-icon info"><i class="fas fa-chart-bar"></i></div>
                <div class="qa-item-content">
                    <span class="qa-item-title">التقارير</span>
                    <span class="qa-item-desc">عرض تقارير الحضور والتسميع</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
        </div>
    </div>
</div>

<div class="tables-grid">
    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-history"></i>
                <span>آخر جلسات التسميع</span>
            </div>
            <a href="{{ route('supervisor.sessions.index') }}" class="view-all-btn">
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
                            <th>الجزء</th>
                            <th>الأخطاء</th>
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
                                    <span>{{ $session->student->user->name }}</span>
                                </div>
                            </td>
                            <td><span class="badge-modern info">{{ $session->type_label }}</span></td>
                            <td>{{ $session->parts_label !== '-' ? $session->parts_label : '-' }}</td>
                            <td>
                                <span class="text-danger fw-bold">{{ $session->total_errors }}</span>
                                <small class="text-muted">/ {{ $session->total_hesitations }} تردد</small>
                            </td>
                            <td><span class="badge-modern {{ $session->status_color }}">{{ $session->status_label }}</span></td>
                            <td class="date-cell">{{ $session->session_date->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-cell">لا توجد جلسات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-exclamation-triangle"></i>
                <span>آخر الإنذارات</span>
            </div>
            <a href="{{ route('supervisor.warnings.create') }}" class="view-all-btn">
                إضافة <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            @forelse($recentWarnings as $warning)
            <div class="warning-item">
                <div class="warning-content">
                    <div class="fw-bold warning-title">{{ $warning->student->user->name ?? '-' }}</div>
                    <div class="text-muted warning-desc">{{ $warning->title }}</div>
                    <span class="badge-modern warning">{{ $warning->type_label }}</span>
                </div>
                <small class="date-cell">{{ $warning->issue_date->format('d/m') }}</small>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>لا توجد إنذارات</p>
            </div>
            @endforelse
        </div>
    </div>

    @if($isRequiredDay)
    <div class="dashboard-card table-card mt-3">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-user-clock"></i>
                <span>حضروا ولم يسمّعوا اليوم ({{ $presentNotRecited->count() }})</span>
            </div>
            <a href="{{ route('supervisor.recitation.index') }}" class="view-all-btn">
                التسميع <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            @forelse($presentNotRecited as $attendance)
            <div class="warning-item">
                <div class="warning-content">
                    <div class="fw-bold warning-title">{{ $attendance->student->user->name ?? '-' }}</div>
                    <div class="text-muted warning-desc">
                        {{ $attendance->status === 'late' ? 'متأخر' : 'حاضر' }}
                    </div>
                </div>
                <a class="btn btn-sm btn-primary" href="{{ route('supervisor.recitation.start') }}?student_id={{ $attendance->student_id }}">
                    <i class="fas fa-play me-1"></i>بدء جلسة
                </a>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>تم تسميع جميع الحاضرين</p>
            </div>
            @endforelse
        </div>
    </div>
    @endif
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
</script>
@endpush
