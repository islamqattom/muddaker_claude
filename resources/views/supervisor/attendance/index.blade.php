@extends('layouts.app')

@section('title', 'تسجيل الحضور والغياب')
@section('page-title', 'الحضور والغياب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الحضور والغياب</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">التاريخ</label>
                <input type="date" id="dateFilter" class="form-control"
                       value="{{ $date->toDateString() }}"
                       onchange="window.location='{{ route('supervisor.attendance.index') }}?date='+this.value">
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <div class="text-center p-2 rounded bg-success text-white flex-fill">
                        <div class="fw-bold fs-4">{{ $stats['present'] }}</div>
                        <small>حاضر</small>
                    </div>
                    <div class="text-center p-2 rounded bg-danger text-white flex-fill">
                        <div class="fw-bold fs-4">{{ $stats['absent'] }}</div>
                        <small>غائب</small>
                    </div>
                    <div class="text-center p-2 rounded bg-warning text-dark flex-fill">
                        <div class="fw-bold fs-4">{{ $stats['late'] }}</div>
                        <small>متأخر</small>
                    </div>
                    <div class="text-center p-2 rounded bg-info text-white flex-fill">
                        <div class="fw-bold fs-4">{{ $stats['excused'] }}</div>
                        <small>بعذر</small>
                    </div>
                    <div class="text-center p-2 rounded bg-secondary text-white flex-fill">
                        <div class="fw-bold fs-4">{{ $stats['total'] - $stats['present'] - $stats['absent'] - $stats['late'] - $stats['excused'] - ($stats['holiday'] ?? 0) }}</div>
                        <small>غير مسجل</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-calendar-check me-2"></i>
            سجل حضور يوم {{ $date->translatedFormat('l d/m/Y') }}
        </h6>
        <div class="d-flex gap-2">
            <a href="{{ route('supervisor.attendance.history') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-history me-1"></i>سجل الحضور
            </a>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
                <i class="fas fa-check me-1"></i>تحديد الكل حاضر
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                <i class="fas fa-times me-1"></i>تحديد الكل غائب
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        @if($students->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                لا يوجد طلاب مسجلون تحت إشرافك
            </div>
        @else
        <form action="{{ route('supervisor.attendance.store') }}" method="POST" id="attendanceForm">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>اسم الطالب</th>
                            <th>المستوى</th>
                            <th width="300">الحالة</th>
                            <th width="150">وقت الحضور</th>
                            <th width="200">ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                        @php $currentStatus = $existingAttendances[$student->id] ?? null; @endphp
                        <tr id="row-{{ $student->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $student->user->name }}</div>
                                        <small class="text-muted">{{ $student->student_number }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $student->level->name ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="btn-group attendance-btns" role="group">
                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="present-{{ $student->id }}" value="present"
                                           {{ $currentStatus === 'present' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'present')">
                                    <label class="btn btn-sm btn-outline-success" for="present-{{ $student->id }}">
                                        <i class="fas fa-check me-1"></i>حاضر
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="late-{{ $student->id }}" value="late"
                                           {{ $currentStatus === 'late' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'late')">
                                    <label class="btn btn-sm btn-outline-warning" for="late-{{ $student->id }}">
                                        <i class="fas fa-clock me-1"></i>متأخر
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="absent-{{ $student->id }}" value="absent"
                                           {{ $currentStatus === 'absent' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'absent')">
                                    <label class="btn btn-sm btn-outline-danger" for="absent-{{ $student->id }}">
                                        <i class="fas fa-times me-1"></i>غائب
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="excused-{{ $student->id }}" value="excused"
                                           {{ $currentStatus === 'excused' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'excused')">
                                    <label class="btn btn-sm btn-outline-info" for="excused-{{ $student->id }}">
                                        <i class="fas fa-file-alt me-1"></i>بعذر
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="time"
                                       name="attendance[{{ $student->id }}][arrival_time]"
                                       class="form-control form-control-sm arrival-time"
                                       id="arrival-{{ $student->id }}"
                                       style="{{ $currentStatus === 'late' ? '' : 'display:none;' }}"
                                       value="{{ optional($student->attendances->first())->arrival_time ? \Carbon\Carbon::parse(optional($student->attendances->first())->arrival_time)->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="text"
                                       name="attendance[{{ $student->id }}][notes]"
                                       class="form-control form-control-sm"
                                       placeholder="ملاحظة...">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted">{{ $students->count() }} طالب</span>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save me-2"></i>
                    حفظ سجل الحضور
                </button>
            </div>
        </form>
        @endif

        <form action="{{ route('supervisor.attendance.holiday') }}" method="POST" class="mt-3 text-end">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <button type="submit" class="btn btn-purple" onclick="return confirm('هل أنت متأكد من تسجيل هذا اليوم كعطلة لجميع الطلاب؟');">
                <i class="fas fa-calendar-week me-2"></i>
                عطلة
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
.btn-purple {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
}
.btn-purple:hover {
    background-color: #5a32a3;
    border-color: #5a32a3;
    color: white;
}
</style>
<script>
function handleStatusChange(studentId, status) {
    const arrivalInput = document.getElementById('arrival-' + studentId);
    if (status === 'late') {
        arrivalInput.style.display = '';
        arrivalInput.required = true;
    } else {
        arrivalInput.style.display = 'none';
        arrivalInput.required = false;
    }
    // Color the row
    const row = document.getElementById('row-' + studentId);
    let colorClass = 'table-';
    if (status === 'present') colorClass += 'success';
    else if (status === 'absent') colorClass += 'danger';
    else if (status === 'late') colorClass += 'warning';
    else if (status === 'excused') colorClass += 'info';
    else if (status === 'holiday') colorClass = '';
    row.className = colorClass;
}

function markAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(radio => {
        radio.checked = true;
        const studentId = radio.id.split('-')[1];
        handleStatusChange(studentId, status);
    });
}

// Initialize colors on load
document.addEventListener('DOMContentLoaded', function() {
    @foreach($students as $student)
    @php $status = $existingAttendances[$student->id] ?? null; @endphp
    @if($status)
        handleStatusChange({{ $student->id }}, '{{ $status }}');
    @endif
    @endforeach
});
</script>
@endpush
