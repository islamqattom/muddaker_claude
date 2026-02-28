@extends('layouts.app')

@section('title', 'تفاصيل الإنذار')
@section('page-title', 'تفاصيل الإنذار')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.warnings.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">تفاصيل الإنذار</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Warning Details Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تفاصيل الإنذار
                </h6>
                <span class="badge bg-{{ $warning->type === 'attendance' ? 'warning' : ($warning->type === 'recitation' ? 'info' : 'secondary') }} fs-6">
                    {{ $warning->type_label }}
                </span>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">رقم الإنذار</th>
                        <td><code class="fs-6">{{ $warning->warning_number }}</code></td>
                    </tr>
                    <tr>
                        <th>تاريخ الإصدار</th>
                        <td>{{ $warning->issue_date->format('d/m/Y') }}</td>
                    </tr>
                    @if($warning->from_date && $warning->to_date)
                    <tr>
                        <th>الفترة المعنية</th>
                        <td>من {{ $warning->from_date->format('d/m/Y') }} إلى {{ $warning->to_date->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>المشرف</th>
                        <td>{{ $warning->issuedBy->name }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Student Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>معلومات الطالب</h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="student-avatar" style="width:60px;height:60px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:24px;">
                            {{ mb_substr($warning->student->user->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">{{ $warning->student->user->name }}</h5>
                        <p class="text-muted mb-1">
                            <i class="fas fa-id-card me-1"></i>
                            {{ $warning->student->student_number }}
                        </p>
                        <span class="badge bg-light text-dark">{{ $warning->student->level->name ?? '-' }}</span>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('supervisor.students.show', $warning->student->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>عرض الملف
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning Content Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>محتوى الإنذار</h6>
            </div>
            <div class="card-body">
                <h5 class="card-title mb-3">{{ $warning->title }}</h5>
                <div class="content-box p-3 rounded" style="background:#fffdf5; border:1px solid #f0e6d2;">
                    {{ $warning->content }}
                </div>

                @if($warning->additional_notes)
                <div class="mt-3">
                    <h6><i class="fas fa-sticky-note me-2"></i>ملاحظات إضافية</h6>
                    <div class="p-3 rounded bg-light">
                        {{ $warning->additional_notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Stats Card -->
        @if($warning->type === 'attendance' && $warning->total_days)
        <div class="card mb-4">
            <div class="card-header bg-warning bg-opacity-10">
                <h6 class="mb-0 text-warning"><i class="fas fa-calendar-times me-2"></i>إحصائيات الحضور والغياب</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-success bg-opacity-10">
                            <div class="fs-3 fw-bold text-success">{{ $warning->total_days - $warning->absent_days - $warning->late_days }}</div>
                            <small class="text-muted">أيام الحضور</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-danger bg-opacity-10">
                            <div class="fs-3 fw-bold text-danger">{{ $warning->absent_days }}</div>
                            <small class="text-muted">أيام الغياب</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-warning bg-opacity-10">
                            <div class="fs-3 fw-bold text-warning">{{ $warning->late_days }}</div>
                            <small class="text-muted">أيام التأخير</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-secondary bg-opacity-10">
                            <div class="fs-3 fw-bold">{{ $warning->total_days }}</div>
                            <small class="text-muted">الإجمالي</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($warning->type === 'recitation' && $warning->total_sessions)
        <div class="card mb-4">
            <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 text-info"><i class="fas fa-microphone me-2"></i>إحصائيات التسميع</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="text-center p-3 rounded bg-secondary bg-opacity-10">
                            <div class="fs-3 fw-bold">{{ $warning->total_sessions }}</div>
                            <small class="text-muted">إجمالي الجلسات</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-center p-3 rounded bg-success bg-opacity-10">
                            <div class="fs-3 fw-bold text-success">{{ $warning->total_sessions - $warning->failed_sessions }}</div>
                            <small class="text-muted">جلسات ناجحة</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-center p-3 rounded bg-danger bg-opacity-10">
                            <div class="fs-3 fw-bold text-danger">{{ $warning->failed_sessions }}</div>
                            <small class="text-muted">جلسات راسبة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>الإجراءات</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('supervisor.warnings.print', $warning->id) }}" class="btn btn-success">
                        <i class="fas fa-print me-2"></i>طباعة الإنذار
                    </a>
                    <a href="{{ route('supervisor.warnings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
