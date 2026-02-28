@extends('layouts.app')
@section('title', 'بيانات الطالب')
@section('page-title', 'بيانات الطالب')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">الطلاب</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Student Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    معلومات الطالب
                </h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-auto">
                        <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                    </div>
                    <div class="col">
                        <h4 class="mb-1">{{ $student->user->name }}</h4>
                        <p class="text-muted mb-0">{{ $student->student_number }}</p>
                        @if($student->level)
                            <span class="badge bg-primary mt-2">{{ $student->level->name }}</span>
                        @endif
                    </div>
                </div>

                <table class="table table-borderless">
                    <tr>
                        <th width="150">اسم المستخدم</th>
                        <td>{{ $student->user->username ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>رقم الهاتف</th>
                        <td>{{ $student->user->phone }}</td>
                    </tr>
                    <tr>
                        <th>البريد الإلكتروني</th>
                        <td>{{ $student->user->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>الرقم الوطني</th>
                        <td>{{ $student->national_id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ الميلاد</th>
                        <td>{{ $student->birth_date?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>العنوان</th>
                        <td>{{ $student->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>المشرف</th>
                        <td>{{ $student->supervisor->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ التسجيل</th>
                        <td>{{ $student->enrollment_date?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    @if($student->notes)
                    <tr>
                        <th>ملاحظات</th>
                        <td>{{ $student->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-primary">{{ $student->recitationSessions->count() }}</div>
                        <small class="text-muted">جلسات التسميع</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-success">{{ $student->pass_rate }}%</div>
                        <small class="text-muted">نسبة النجاح</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-info">{{ $student->attendance_rate }}%</div>
                        <small class="text-muted">نسبة الحضور</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning bg-opacity-10">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-warning">{{ $student->warnings->count() }}</div>
                        <small class="text-muted">الإنذارات</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Stats -->
        @if($attendanceStats['present'] || $attendanceStats['absent'] || $attendanceStats['late'] || $attendanceStats['excused'])
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    إحصائيات الحضور
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-success bg-opacity-10">
                            <div class="fs-4 fw-bold text-success">{{ $attendanceStats['present'] }}</div>
                            <small class="text-muted">حاضر</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-danger bg-opacity-10">
                            <div class="fs-4 fw-bold text-danger">{{ $attendanceStats['absent'] }}</div>
                            <small class="text-muted">غائب</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-warning bg-opacity-10">
                            <div class="fs-4 fw-bold text-warning">{{ $attendanceStats['late'] }}</div>
                            <small class="text-muted">متأخر</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded bg-secondary bg-opacity-10">
                            <div class="fs-4 fw-bold">{{ $attendanceStats['excused'] }}</div>
                            <small class="text-muted">معذور</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Recitations -->
        @if($student->recitationSessions->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-microphone me-2"></i>
                    آخر جلسات التسميع
                </h6>
                <span class="badge bg-info">{{ $student->recitationSessions->count() }}</span>
            </div>
            <div class="card-body p-0">
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
                                <td>{{ $session->session_date?->format('d/m/Y') }}</td>
                                <td>{{ $session->part->name ?? '-' }}</td>
                                <td>{{ $session->from_page }}</td>
                                <td>{{ $session->to_page }}</td>
                                <td>
                                    @if($session->status === 'passed')
                                        <span class="badge bg-success">ناجح</span>
                                    @elseif($session->status === 'failed')
                                        <span class="badge bg-danger">راسب</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $session->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>تعديل البيانات
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>حذف الطالب
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
