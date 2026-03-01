@extends('layouts.app')
@section('title', 'تفاصيل الإنذار')
@section('page-title', 'تفاصيل الإنذار')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('parent.sessions.index') }}" class="nav-link"><i class="fas fa-microphone"></i> جلسات التسميع</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link"><i class="fas fa-calendar-check"></i> سجل الحضور</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link active">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge bg-danger">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> التقارير</a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('parent.warnings.index') }}">الإنذارات</a></li>
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
                        <td>{{ $warning->issue_date->format('d/m/Y') }} <span class="badge bg-secondary ms-1">{{ $warning->issue_day }}</span></td>
                    </tr>
                    @if($warning->from_date && $warning->to_date)
                    <tr>
                        <th>الفترة المعنية</th>
                        <td>من {{ $warning->from_date->format('d/m/Y') }} <span class="badge bg-secondary">{{ $warning->from_day }}</span> إلى {{ $warning->to_date->format('d/m/Y') }} <span class="badge bg-secondary">{{ $warning->to_day }}</span></td>
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
                        <img src="{{ $warning->student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:60px;height:60px;object-fit:cover;">
                    </div>
                    <div class="col">
                        <h5 class="mb-1">{{ $warning->student->user->name }}</h5>
                        <p class="text-muted mb-1">
                            <i class="fas fa-id-card me-1"></i>
                            {{ $warning->student->student_number }}
                        </p>
                        <span class="badge bg-light text-dark">{{ $warning->student->level->name ?? '-' }}</span>
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
                    <a href="{{ route('parent.warnings.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
