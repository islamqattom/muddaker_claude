@extends('layouts.app')

@section('title', 'تفاصيل الطالب')
@section('page-title', 'تفاصيل الطالب')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('student.sessions.index') }}" class="nav-link"><i class="fas fa-microphone"></i> جلسات التسميع</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link"><i class="fas fa-calendar-check"></i> سجل الحضور</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge bg-danger">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> التقارير</a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">تفاصيل الطالب</li>
@endsection

@section('content')
<!-- Student Info Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-4">
            <div style="width:80px;height:80px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;font-weight:bold;">
                {{ mb_substr($student->user->name, 0, 1) }}
            </div>
            <div>
                <h4 class="mb-1">{{ $student->user->name }}</h4>
                <p class="text-muted mb-2">
                    {{ $student->level->name ?? '' }} | المشرف: {{ $student->supervisor->name ?? 'غير محدد' }}
                </p>
                <div class="d-flex gap-3">
                    @if($student->current_part)
                        <span class="badge bg-primary">الجزء الحالي: {{ $student->current_part }}</span>
                    @endif
                    @if($student->current_page)
                        <span class="badge bg-secondary">صفحة {{ $student->current_page }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('parent.student.detail', $student->id) }}" class="row g-3">
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
                <a href="{{ route('parent.student.detail', $student->id) }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-redo me-1"></i>إعادة تعيين
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Sessions History -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-microphone me-2"></i>
            سجل جلسات التسميع ({{ $sessions->count() }})
        </h6>
    </div>
    <div class="card-body p-0">
        @if($sessions->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fas fa-microphone-alt fa-2x mb-2 d-block"></i>
                <p>لا توجد جلسات تسميع في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>نوع الجلسة</th>
                            <th>الجزء/الصفحات</th>
                            <th>الأخطاء</th>
                            <th>الترددات</th>
                            <th>الدرجة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                        <tr>
                            <td class="fw-semibold">{{ $session->session_date->format('Y/m/d') }}</td>
                            <td><span class="badge bg-info">{{ $session->type_label }}</span></td>
                            <td>
                                @if($session->part)
                                    جزء {{ $session->part->part_number }}
                                @else
                                    {{ $session->start_page }} - {{ $session->end_page }}
                                @endif
                            </td>
                            <td>
                                @if($session->total_errors > 0)
                                    <span class="text-danger fw-semibold">{{ $session->total_errors }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($session->total_hesitations > 0)
                                    <span class="text-warning fw-semibold">{{ $session->total_hesitations }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($session->final_score !== null)
                                    {{ number_format($session->final_score, 1) }} / {{ $session->max_score }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Attendance History -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-calendar-check me-2"></i>
            سجل الحضور ({{ $attendances->count() }})
        </h6>
    </div>
    <div class="card-body p-0">
        @if($attendances->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                <p>لا يوجد سجل حضور في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>وقت الوصول</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td class="fw-semibold">{{ $attendance->date->format('Y/m/d') }}</td>
                            <td><span class="badge bg-{{ $attendance->status_color }}">{{ $attendance->status_label }}</span></td>
                            <td>
                                @if($attendance->arrival_time)
                                    {{ \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->notes)
                                    {{ $attendance->notes }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Warnings -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            الإنذارات ({{ $warnings->count() }})
        </h6>
    </div>
    <div class="card-body p-0">
        @if($warnings->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                <p>لا توجد إنذارات</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>نوع الإنذار</th>
                            <th>العنوان</th>
                            <th>التاريخ</th>
                            <th>الرقم</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warnings as $warning)
                        <tr>
                            <td>
                                @if($warning->type === 'attendance')
                                    <span class="badge bg-warning text-dark">حضور</span>
                                @elseif($warning->type === 'recitation')
                                    <span class="badge bg-info">تسميع</span>
                                @else
                                    <span class="badge bg-secondary">عام</span>
                                @endif
                            </td>
                            <td>{{ $warning->title }}</td>
                            <td>{{ $warning->issue_date->format('Y/m/d') }}</td>
                            <td><code class="small">{{ $warning->warning_number }}</code></td>
                            <td>
                                @if($warning->is_read_by_parent)
                                    <span class="badge bg-success">مقروء</span>
                                @else
                                    <span class="badge bg-danger">جديد</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
