@extends('layouts.app')

@section('title', 'تقاريري')
@section('page-title', 'تقاريري')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">تقاريري</li>
@endsection

@section('content')
<!-- Statistics Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">إجمالي الجلسات</h6>
                        <h3 class="mb-0">{{ $totalSessions }}</h3>
                    </div>
                    <div class="text-primary">
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
                        <h6 class="text-muted mb-1">نسبة الحضور</h6>
                        <h3 class="mb-0">{{ $attendanceRate }}%</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-calendar-check fa-2x"></i>
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
                        <h6 class="text-muted mb-1">نسبة النجاح</h6>
                        <h3 class="mb-0">{{ $passRate }}%</h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-graduation-cap fa-2x"></i>
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
                        <h6 class="text-muted mb-1">الأجزاء المحفوظة</h6>
                        <h3 class="mb-0">{{ $memorizedParts }}</h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-book fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Sessions -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    آخر جلسات التسميع
                </h6>
            </div>
            <div class="card-body p-0">
                @if($recentSessions->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-microphone-alt fa-2x mb-2 d-block"></i>
                        <p>لا توجد جلسات</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @foreach($recentSessions as $session)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $session->type_label }}</div>
                                        <small class="text-muted">
                                            @if($session->type === 'mafsil')
                                                {{ $session->pages->pluck('page.part_id')->filter()->unique()->map(fn($id) => 'الجزء '.$id)->join('، ') }}
                                            @elseif($session->part)
                                                جزء {{ $session->part->part_number }}
                                            @else
                                                ص {{ $session->start_page }} - {{ $session->end_page }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $session->session_date->format('Y/m/d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    آخر سجلات الحضور
                </h6>
            </div>
            <div class="card-body p-0">
                @if($recentAttendances->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                        <p>لا يوجد سجل حضور</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @foreach($recentAttendances as $attendance)
                                <tr>
                                    <td class="fw-semibold">{{ $attendance->date->format('Y/m/d') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $attendance->status_color }}">{{ $attendance->status_label }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        @if($attendance->arrival_time)
                                            {{ \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i') }}
                                        @else
                                            -
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
    </div>
</div>
@endsection
