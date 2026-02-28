@extends('layouts.app')
@section('title', 'تقرير ' . $student->user->name)
@section('page-title', 'تقرير الطالب')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name }}</li>
@endsection

@section('content')
<!-- Student Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-1">
                <div class="student-avatar" style="width:60px;height:60px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:24px;">
                    {{ mb_substr($student->user->name, 0, 1) }}
                </div>
            </div>
            <div class="col-md-4">
                <h4 class="mb-1">{{ $student->user->name }}</h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-layer-group me-1"></i>
                    المستوى: {{ $student->level->name ?? '-' }}
                </p>
            </div>
            <div class="col-md-4">
                <p class="mb-1"><strong>رقم الطالب:</strong> {{ $student->student_number }}</p>
                <p class="mb-0"><strong>المشرف:</strong> {{ $student->supervisor->user->name ?? '-' }}</p>
            </div>
            <div class="col-md-3 text-end">
                @if(isset($fromDate) && isset($toDate))
                <a href="{{ route('supervisor.reports.student', array_merge(['student' => $student->id, 'from_date' => $fromDate->toDateString(), 'to_date' => $toDate->toDateString()], ['export' => 'pdf'])) }}" class="btn btn-outline-danger" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>تصدير PDF
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Period Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('supervisor.reports.student', $student->id) }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate->toDateString() }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate->toDateString() }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>بحث
                </button>
            </div>
            <div class="col-md-2 text-end">
                <span class="badge bg-primary fs-6">
                    {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Summary -->
<div class="row mb-4">
    <!-- Attendance Stats -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>إحصائيات الحضور</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-3 rounded bg-success text-white">
                            <div class="fs-2 fw-bold">{{ $attendanceStats['present'] }}</div>
                            <div>حاضر</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 rounded bg-danger text-white">
                            <div class="fs-2 fw-bold">{{ $attendanceStats['absent'] }}</div>
                            <div>غائب</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded bg-warning text-dark">
                            <div class="fs-2 fw-bold">{{ $attendanceStats['late'] }}</div>
                            <div>متأخر</div>
                        </div>
                    </div>
                    <div class">
                        <div="col-6 class="p-3 rounded bg-info text-dark">
                            <div class="fs-2 fw-bold">
                                @php
                                    $attendancePercentage = $attendanceStats['total'] > 0
                                        ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100)
                                        : 0;
                                @endphp
                                {{ $attendancePercentage }}%
                            </div>
                            <div>نسبة الحضور</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recitation Stats -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-quran me-2"></i>إحصائيات التسميع</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-3 rounded bg-primary text-white">
                            <div class="fs-2 fw-bold">{{ $sessionStats['total'] }}</div>
                            <div>إجمالي الجلسات</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 rounded bg-success text-white">
                            <div class="fs-2 fw-bold">{{ $sessionStats['passed'] }}</div>
                            <div>ناجح</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded bg-danger text-white">
                            <div class="fs-2 fw-bold">{{ $sessionStats['failed'] }}</div>
                            <div>راسب</div>
                        </div>
                    </div>
                    <div class="col-6">
                        @php
                            $passRate = $sessionStats['total'] > 0
                                ? round(($sessionStats['passed'] / $sessionStats['total']) * 100)
                                : 0;
                        @endphp
                        <div class="p-3 rounded bg-warning text-dark">
                            <div class="fs-2 fw-bold">{{ $passRate }}%</div>
                            <div>نسبة النجاح</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recitation Sessions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>جلسات التسميع</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>النوع</th>
                        <th>الجزء</th>
                        <th class="text-center">الصفحات</th>
                        <th class="text-center">الأخطاء</th>
                        <th class="text-center">الترددات</th>
                        <th class="text-center">العلامة</th>
                        <th class="text-center">الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $session->type_label }}</span></td>
                            <td>
                                @if($session->part)
                                    <span class="text-primary">الجزء {{ $session->part->part_number }}</span>
                                @elseif($session->mafsil_type)
                                    <span class="text-danger">مفصل ({{ $session->mafsil_type }})</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->total_pages)
                                    <span class="badge bg-dark">{{ $session->total_pages }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $session->total_errors ?? 0 }}</td>
                            <td class="text-center">{{ $session->total_hesitations ?? 0 }}</td>
                            <td class="text-center">
                                @if($session->final_score !== null)
                                    @php
                                        $scoreClass = $session->final_score >= 90 ? 'bg-success' : ($session->final_score >= 80 ? 'bg-warning text-dark' : 'bg-danger');
                                    @endphp
                                    <span class="badge {{ $scoreClass }}">{{ number_format($session->final_score, 1) }}%</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                            </td>
                            <td><small class="text-muted">{{ $session->session_date->format('Y/m/d') }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد جلسات تسميع
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Attendance Records -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>سجلات الحضور</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الحالة</th>
                        <th>وقت الحضور</th>
                        <th>ملاحظة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>
                                @switch($attendance->status)
                                    @case('present')
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>حاضر</span>
                                        @break
                                    @case('absent')
                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>غائب</span>
                                        @break
                                    @case('late')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>متأخر</span>
                                        @break
                                    @case('excused')
                                        <span class="badge bg-info text-dark"><i class="fas fa-file-alt me-1"></i>بعذر</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">غير محدد</span>
                                @endswitch
                            </td>
                            <td>{{ $attendance->arrival_time ?? '-' }}</td>
                            <td>{{ $attendance->notes ?? '-' }}</td>
                            <td><small class="text-muted">{{ $attendance->date->format('Y/m/d') }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد سجلات حضور
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
