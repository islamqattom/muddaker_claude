@extends('layouts.app')

@section('title', 'بيانات الطالب')
@section('page-title', 'بيانات الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.students.index') }}">الطلاب</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Student Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>معلومات الطالب</h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:70px;height:70px;object-fit:cover;">
                    </div>
                    <div class="col">
                        <h4 class="mb-1">{{ $student->user->name }}</h4>
                        <p class="text-muted mb-1">
                            <i class="fas fa-id-card me-1"></i>
                            رقم الطالب: <code>{{ $student->student_number }}</code>
                        </p>
                        @if($student->level)
                            <span class="badge bg-secondary">{{ $student->level->name }}</span>
                        @endif
                    </div>
                </div>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <th width="150">الرقم الوطني</th>
                        <td>{{ $student->national_id }}</td>
                        <th>تاريخ الميلاد</th>
                        <td>{{ $student->birth_date?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>رقم الهاتف</th>
                        <td>{{ $student->phone ?? '-' }}</td>
                        <th>العنوان</th>
                        <td>{{ $student->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ التسجيل</th>
                        <td>{{ $student->enrollment_date?->format('d/m/Y') ?? '-' }}</td>
                        <th>الأجزاء المحفوظة</th>
                        <td>{{ $student->memorized_parts ?? 0 }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card bg-primary bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-primary">{{ $student->attendance_rate }}%</div>
                        <small class="text-muted">نسبة الحضور</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-success bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-success">{{ $student->pass_rate }}%</div>
                        <small class="text-muted">نسبة النجاح</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-info bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-info">{{ $student->recitationSessions->count() }}</div>
                        <small class="text-muted">جلسات التسميع</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-warning bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-warning">{{ $student->warnings->count() }}</div>
                        <small class="text-muted">الإنذارات</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Charts -->
        <x-student-progress-chart :student-id="$student->id" :velocity-data="$velocityData" />

        <div class="row">
            <div class="col-md-8">
                <x-page-difficulty-heatmap :student-id="$student->id" :heatmap-data="$heatmapData" />
            </div>
            <div class="col-md-4">
                <x-recent-session-timeline :sessions="$recentSessions" />
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>سجل الحضور الأخير</h6>
            </div>
            <div class="card-body p-0">
                @if($student->attendances->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                        <p class="mb-0">لا يوجد سجل حضور</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($attendance->status === 'present')
                                            <span class="badge bg-success">حاضر</span>
                                        @elseif($attendance->status === 'absent')
                                            <span class="badge bg-danger">غائب</span>
                                        @elseif($attendance->status === 'late')
                                            <span class="badge bg-warning text-dark">متأخر</span>
                                        @elseif($attendance->status === 'excused')
                                            <span class="badge bg-info">معذور</span>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->notes ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Recitation Sessions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-microphone me-2"></i>جلسات التسميع الأخيرة</h6>
            </div>
            <div class="card-body p-0">
                @if($student->recitationSessions->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-book-open fa-2x mb-2 d-block"></i>
                        <p class="mb-0">لا يوجد جلسات تسميع</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الجزء</th>
                                    <th>من صفحة</th>
                                    <th>إلى صفحة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->recitationSessions as $session)
                                <tr>
                                    <td>{{ $session->session_date->format('d/m/Y') }}</td>
                                    <td>{{ $session->part->name ?? '-' }}</td>
                                    <td>{{ $session->start_page }}</td>
                                    <td>{{ $session->end_page }}</td>
                                    <td>
                                        @if($session->status === 'passed')
                                            <span class="badge bg-success">ناجح</span>
                                        @elseif($session->status === 'failed')
                                            <span class="badge bg-danger">راسب</span>
                                        @else
                                            <span class="badge bg-secondary">قيد التسميع</span>
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
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>الإنذارات</h6>
            </div>
            <div class="card-body p-0">
                @if($student->warnings->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                        <p class="mb-0">لا يوجد إنذارات</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الإنذار</th>
                                    <th>النوع</th>
                                    <th>العنوان</th>
                                    <th>التاريخ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->warnings as $warning)
                                <tr>
                                    <td><code>{{ $warning->warning_number }}</code></td>
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
                                    <td>{{ $warning->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('supervisor.warnings.show', $warning->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>الإجراءات</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('supervisor.students.edit', $student->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل البيانات
                    </a>
                    <a href="{{ route('supervisor.recitation.index') }}?student_id={{ $student->id }}" class="btn btn-primary">
                        <i class="fas fa-microphone me-2"></i>تسميع جديد
                    </a>
                    <a href="{{ route('supervisor.attendance.index') }}?student_id={{ $student->id }}" class="btn btn-info">
                        <i class="fas fa-calendar-check me-2"></i>تسجيل حضور
                    </a>
                    <a href="{{ route('supervisor.warnings.create') }}?student_id={{ $student->id }}" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>إنذار جديد
                    </a>
                    <a href="{{ route('supervisor.reports.student', $student->id) }}" class="btn btn-success">
                        <i class="fas fa-chart-bar me-2"></i>عرض التقارير
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-2"></i>حذف الطالب
                    </button>
                    <a href="{{ route('supervisor.students.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">حذف الطالب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف الطالب <strong>{{ $student->user->name }}</strong>؟</p>
                <p class="text-danger small">سيتم حذف جميع بيانات الطالب بما في ذلك:</p>
                <ul class="text-danger small">
                    <li>سجلات الحضور والغياب</li>
                    <li>جلسات التسميع</li>
                    <li>الإنذارات</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('supervisor.students.destroy', $student->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
