@extends('layouts.app')
@section('title', 'سجل الحضور')
@section('page-title', 'سجل الحضور')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.attendance.index') }}">الحضور والغياب</a></li>
    <li class="breadcrumb-item active">السجل</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-history me-2"></i>
            سجل الحضور والغياب
        </h6>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('supervisor.attendance.history') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الطالب</label>
                    <select name="student_id" class="form-select">
                        <option value="">-- جميع الطلاب --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                                {{ $student->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>بحث
                    </button>
                </div>
            </div>
        </form>

        <!-- Results Summary -->
        <div class="mb-3">
            <span class="badge bg-primary fs-6">
                <i class="fas fa-list me-1"></i>عدد النتائج: {{ $attendances->total() }}
            </span>
            @if($studentId)
                @php $selectedStudent = $students->firstWhere('id', $studentId) @endphp
                @if($selectedStudent)
                    <span class="badge bg-info text-dark ms-1">
                        <i class="fas fa-user-graduate me-1"></i>{{ $selectedStudent->user->name }}
                    </span>
                @endif
            @endif
        </div>

        <!-- Attendance Table -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:60px">#</th>
                        <th>الطالب</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>وقت الحضور</th>
                        <th>ملاحظات</th>
                        <th width="80">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $attendance)
                        <tr>
                            <td class="text-center text-muted">{{ $attendances->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $attendance->student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                    <div class="fw-semibold">{{ $attendance->student->user->name }}</div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">{{ $attendance->date->format('Y/m/d') }}</small>
                                <div class="text-muted small">{{ $attendance->date->format('l') }}</div>
                            </td>
                            <td class="text-center">
                                @switch($attendance->status)
                                    @case('present')
                                        <span class="badge bg-success">حاضر</span>
                                        @break
                                    @case('absent')
                                        <span class="badge bg-danger">غائب</span>
                                        @break
                                    @case('late')
                                        <span class="badge bg-warning text-dark">متأخر</span>
                                        @break
                                    @case('excused')
                                        <span class="badge bg-info">معتذر</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="text-center">
                                @if($attendance->arrival_time)
                                    <span class="text-muted">{{ $attendance->arrival_time }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $attendance->notes ?? '-' }}</small>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="حذف" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $attendance->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $attendance->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">حذف سجل الحضور</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من حذف سجل حضور الطالب <strong>{{ $attendance->student->user->name }}</strong>؟</p>
                                                <p class="text-muted small">بتاريخ: {{ $attendance->date->format('Y/m/d') }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('supervisor.attendance.destroy', $attendance->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">حذف</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد سجلات في الفترة المحددة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attendances->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $attendances->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
