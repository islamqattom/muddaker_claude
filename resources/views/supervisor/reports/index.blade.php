@extends('layouts.app')
@section('title', 'التقارير')
@section('page-title', 'التقارير')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
@endsection

@section('content')
<div class="row">
    <!-- تقرير التسميع -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-quran fa-4x text-primary"></i>
                </div>
                <h4 class="mb-3">تقرير التسميع</h4>
                <p class="text-muted mb-4">
                    عرض إحصائيات وتسجيلات جلسات التسميع للطلاب<br>
                    مع إمكانية التصفية حسب الطالب والنوع والفترة الزمنية
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('supervisor.reports.recitation') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-chart-bar me-2"></i>عرض التقرير
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- تقرير الحضور والغياب -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-calendar-check fa-4x text-success"></i>
                </div>
                <h4 class="mb-3">تقرير الحضور والغياب</h4>
                <p class="text-muted mb-4">
                    عرض سجلات الحضور والغياب للطلاب<br>
                    مع إحصائيات كاملة ونسبة الحضور
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('supervisor.reports.attendance') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-chart-bar me-2"></i>عرض التقرير
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Summary -->
<div class="card mt-2">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>ملخص سريع</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            @php
                $currentMonth = \Carbon\Carbon::now()->startOfMonth();
                $currentMonthEnd = \Carbon\Carbon::now()->endOfMonth();
            @endphp
            <div class="col-md-3 mb-3">
                <div class="p-3 rounded bg-primary bg-opacity-10">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <div class="fs-3 fw-bold text-primary">{{ $students->count() }}</div>
                    <div class="text-muted">إجمالي الطلاب</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="p-3 rounded bg-success bg-opacity-10">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <div class="fs-3 fw-bold text-success">{{ \App\Models\RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', auth()->id()))->whereBetween('session_date', [$currentMonth, $currentMonthEnd])->count() }}</div>
                    <div class="text-muted">جلسات هذا الشهر</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="p-3 rounded bg-warning bg-opacity-10">
                    <i class="fas fa-calendar-alt fa-2x text-warning mb-2"></i>
                    <div class="fs-3 fw-bold text-warning">{{ \App\Models\Attendance::where('supervisor_id', auth()->id())->whereBetween('date', [$currentMonth, $currentMonthEnd])->count() }}</div>
                    <div class="text-muted">سجلات الحضور</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="p-3 rounded bg-info bg-opacity-10">
                    <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                    @php
                        $totalAttendance = \App\Models\Attendance::where('supervisor_id', auth()->id())->whereBetween('date', [$currentMonth, $currentMonthEnd])->count();
                        $presentAttendance = \App\Models\Attendance::where('supervisor_id', auth()->id())->whereBetween('date', [$currentMonth, $currentMonthEnd])->where('status', 'present')->count();
                        $percentage = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100) : 0;
                    @endphp
                    <div class="fs-3 fw-bold text-info">{{ $percentage }}%</div>
                    <div class="text-muted">نسبة الحضور</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
