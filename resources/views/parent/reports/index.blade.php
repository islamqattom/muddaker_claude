@extends('layouts.app')

@section('title', 'التقارير')
@section('page-title', 'التقارير')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('parent.sessions.index') }}" class="nav-link"><i class="fas fa-microphone"></i> جلسات التسميع</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link"><i class="fas fa-calendar-check"></i> سجل الحضور</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge bg-danger">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link active"><i class="fas fa-chart-bar"></i> التقارير</a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
@endsection

@section('content')
<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('parent.reports.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>تصفية
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('parent.reports.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-redo me-1"></i>إعادة تعيين
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">إجمالي الطلاب</h6>
                        <h3 class="mb-0">{{ $students->count() }}</h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">إجمالي الجلسات</h6>
                        <h3 class="mb-0">{{ $totalSessions }}</h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-microphone fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">جلسات ناجحة</h6>
                        <h3 class="mb-0">{{ $passedSessions }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">نسبة الحضور</h6>
                        <h3 class="mb-0">{{ $attendanceRate }}%</h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Per-Student Statistics -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-users me-2"></i>
            إحصائيات كل طالب
        </h6>
    </div>
    <div class="card-body p-0">
        @if(empty($studentsData))
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                <p>لا يوجد طلاب مرتبطون بحسابك</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>المستوى</th>
                            <th>الجلسات</th>
                            <th>ناجح</th>
                            <th>راسب</th>
                            <th>حاضر</th>
                            <th>غائب</th>
                            <th>متأخر</th>
                            <th>نسبة الحضور</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsData as $data)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:36px;height:36px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;">
                                        {{ mb_substr($data['student']->user->name, 0, 1) }}
                                    </div>
                                    <span class="fw-semibold">{{ $data['student']->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($data['student']->level)
                                    <span class="badge bg-secondary">{{ $data['student']->level->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $data['sessions']['total'] }}</td>
                            <td><span class="text-success fw-semibold">{{ $data['sessions']['passed'] }}</span></td>
                            <td><span class="text-danger fw-semibold">{{ $data['sessions']['failed'] }}</span></td>
                            <td><span class="text-success">{{ $data['attendance']['present'] }}</span></td>
                            <td><span class="text-danger">{{ $data['attendance']['absent'] }}</span></td>
                            <td><span class="text-warning">{{ $data['attendance']['late'] }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:8px;">
                                        <div class="progress-bar bg-success" style="width:{{ $data['attendance']['rate'] }}%"></div>
                                    </div>
                                    <span class="fw-semibold small">{{ $data['attendance']['rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Attendance Summary -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    ملخص الحضور
                </h6>
            </div>
            <div class="card-body">
                @if($totalAttendances === 0)
                    <div class="text-center py-4 text-muted">
                        <p>لا يوجد سجل حضور في هذه الفترة</p>
                    </div>
                @else
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>حاضر</span>
                            <span class="fw-semibold">{{ $presentCount }}</span>
                        </div>
                        <div class="progress mb-3" style="height:20px;">
                            <div class="progress-bar bg-success" style="width:{{ round($presentCount/$totalAttendances*100) }}%">
                                {{ round($presentCount/$totalAttendances*100) }}%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>غائب</span>
                            <span class="fw-semibold">{{ $absentCount }}</span>
                        </div>
                        <div class="progress mb-3" style="height:20px;">
                            <div class="progress-bar bg-danger" style="width:{{ round($absentCount/$totalAttendances*100) }}%">
                                {{ round($absentCount/$totalAttendances*100) }}%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>متأخر</span>
                            <span class="fw-semibold">{{ $lateCount }}</span>
                        </div>
                        <div class="progress mb-3" style="height:20px;">
                            <div class="progress-bar bg-warning" style="width:{{ round($lateCount/$totalAttendances*100) }}%">
                                {{ round($lateCount/$totalAttendances*100) }}%
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">إجمالي</span>
                        <span class="fw-semibold">{{ $totalAttendances }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-microphone me-2"></i>
                    ملخص جلسات التسميع
                </h6>
            </div>
            <div class="card-body">
                @if($totalSessions === 0)
                    <div class="text-center py-4 text-muted">
                        <p>لا توجد جلسات تسميع في هذه الفترة</p>
                    </div>
                @else
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>ناجح</span>
                            <span class="fw-semibold">{{ $passedSessions }}</span>
                        </div>
                        <div class="progress mb-3" style="height:20px;">
                            <div class="progress-bar bg-success" style="width:{{ round($passedSessions/$totalSessions*100) }}%">
                                {{ round($passedSessions/$totalSessions*100) }}%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>راسب</span>
                            <span class="fw-semibold">{{ $failedSessions }}</span>
                        </div>
                        <div class="progress mb-3" style="height:20px;">
                            <div class="progress-bar bg-danger" style="width:{{ round($failedSessions/$totalSessions*100) }}%">
                                {{ round($failedSessions/$totalSessions*100) }}%
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">إجمالي</span>
                        <span class="fw-semibold">{{ $totalSessions }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
