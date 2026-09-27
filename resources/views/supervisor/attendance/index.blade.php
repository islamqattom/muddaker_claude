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

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="header-text">
            <h4>تسجيل الحضور والغياب</h4>
            <p>سجل حضور يوم {{ $date->translatedFormat('l d/m/Y') }}</p>
        </div>
    </div>
    <div class="header-actions">
        <a href="{{ route('supervisor.attendance.history') }}" class="btn btn-outline-primary">
            <i class="fas fa-history me-2"></i>سجل الحضور
        </a>
    </div>
</div>

<div class="stats-row">
    <div class="stat-item success">
        <div class="stat-item-icon"><i class="fas fa-check"></i></div>
        <div class="stat-item-content">
            <div class="stat-number">{{ $stats['present'] }}</div>
            <div class="stat-label">حاضر</div>
        </div>
    </div>
    <div class="stat-item danger">
        <div class="stat-item-icon"><i class="fas fa-times"></i></div>
        <div class="stat-item-content">
            <div class="stat-number">{{ $stats['absent'] }}</div>
            <div class="stat-label">غائب</div>
        </div>
    </div>
    <div class="stat-item warning">
        <div class="stat-item-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-item-content">
            <div class="stat-number">{{ $stats['late'] }}</div>
            <div class="stat-label">متأخر</div>
        </div>
    </div>
    <div class="stat-item info">
        <div class="stat-item-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-item-content">
            <div class="stat-number">{{ $stats['excused'] }}</div>
            <div class="stat-label">بعذر</div>
        </div>
    </div>
    <div class="stat-item secondary">
        <div class="stat-item-icon"><i class="fas fa-question"></i></div>
        <div class="stat-item-content">
            <div class="stat-number">{{ $stats['total'] - $stats['present'] - $stats['absent'] - $stats['late'] - $stats['excused'] - ($stats['holiday'] ?? 0) }}</div>
            <div class="stat-label">غير مسجل</div>
        </div>
    </div>
</div>

<div class="date-filter-card">
    <form method="GET" class="date-filter-form">
        <div class="date-filter-input">
            <i class="fas fa-calendar-alt"></i>
            <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-list"></i>
            <span>سجل الحضور</span>
        </div>
        <div class="table-actions">
            <span class="record-count">
                <i class="fas fa-users"></i>
                {{ $students->count() }} طالب
            </span>
            <div class="bulk-actions">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')" title="تحديد الكل حاضر">
                    <i class="fas fa-check"></i>
                    <span class="btn-text-mobile">تحديد الكل حاضر</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')" title="تحديد الكل غائب">
                    <i class="fas fa-times"></i>
                    <span class="btn-text-mobile">تحديد الكل غائب</span>
                </button>
            </div>
        </div>
    </div>

    @if($students->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-users"></i>
            </div>
            <h5>لا يوجد طلاب</h5>
            <p>لم يتم العثور على طلاب مسجلين تحت إشرافك</p>
        </div>
    @else
        <form action="{{ route('supervisor.attendance.store') }}" method="POST" id="attendanceForm">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <input type="hidden" name="action" id="formAction" value="store">

            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الطالب</th>
                            <th>المستوى</th>
                            <th>الحالة</th>
                            <th>وقت الحضور</th>
                            <th>ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                        @php $currentStatus = $existingAttendances[$student->id] ?? null; @endphp
                        <tr id="row-{{ $student->id }}">
                            <td class="row-num">{{ $index + 1 }}</td>
                            <td>
                                <div class="user-info-cell">
                                    <div class="user-avatar-table">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-details">
                                        <span class="user-name">{{ $student->user->name }}</span>
                                        <span class="user-username">{{ $student->student_number }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($student->level)
                                    <span class="level-badge">{{ $student->level->name }}</span>
                                @else
                                    <span class="no-data">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="attendance-status-group">
                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="present-{{ $student->id }}" value="present"
                                           {{ $currentStatus === 'present' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'present')">
                                    <label class="status-btn success" for="present-{{ $student->id }}" title="حاضر">
                                        <i class="fas fa-check"></i>
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="late-{{ $student->id }}" value="late"
                                           {{ $currentStatus === 'late' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'late')">
                                    <label class="status-btn warning" for="late-{{ $student->id }}" title="متأخر">
                                        <i class="fas fa-clock"></i>
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="absent-{{ $student->id }}" value="absent"
                                           {{ $currentStatus === 'absent' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'absent')">
                                    <label class="status-btn danger" for="absent-{{ $student->id }}" title="غائب">
                                        <i class="fas fa-times"></i>
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="excused-{{ $student->id }}" value="excused"
                                           {{ $currentStatus === 'excused' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'excused')">
                                    <label class="status-btn info" for="excused-{{ $student->id }}" title="بعذر">
                                        <i class="fas fa-file-alt"></i>
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
                                       placeholder="ملاحظة..."
                                       value="{{ optional($student->attendances->first())->notes ?? '' }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="footer-actions">
                    <button type="button" class="btn btn-outline-secondary" onclick="submitAsHoliday()">
                        <i class="fas fa-calendar-week me-2"></i>تسجيل عطلة
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>حفظ سجل الحضور
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>

<style>
.header-actions { display: flex; gap: 10px; }

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.stat-item {
    background: #fff;
    border-radius: 13px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    min-width: 0;
}

.stat-item-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
    flex-shrink: 0;
}

.stat-item.success .stat-item-icon { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.stat-item.danger .stat-item-icon { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.stat-item.warning .stat-item-icon { background: linear-gradient(135deg, #f39c12, #e67e22); }
.stat-item.info .stat-item-icon { background: linear-gradient(135deg, #3498db, #2980b9); }
.stat-item.secondary .stat-item-icon { background: linear-gradient(135deg, #7f8c8d, #95a5a6); }

.stat-item-content { flex: 1; min-width: 0; }

.date-filter-card {
    background: #fff;
    border-radius: 13px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    margin-bottom: 16px;
}

.date-filter-form { margin: 0; width: 100%; }

.date-filter-input { position: relative; display: flex; align-items: center; }

.date-filter-input i {
    position: absolute;
    right: 14px;
    color: #d4a030;
    pointer-events: none;
}

.date-filter-input input {
    padding: 10px 15px 10px 40px;
    border: 1px solid #e9ecef;
    border-radius: 9px;
    font-size: 13px;
    color: #182B48;
    width: 100%;
    max-width: 240px;
}

.bulk-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.bulk-actions .btn { white-space: nowrap; }

.attendance-status-group { display: flex; gap: 6px; }

.status-btn {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    flex-shrink: 0;
}

.status-btn:hover { transform: scale(1.1); }

.status-btn.success { background: rgba(39,174,96,0.15); color: #27ae60; border-color: rgba(39,174,96,0.3); }
.status-btn.success:hover, input:checked + .status-btn.success { background: #27ae60; color: #fff; }

.status-btn.warning { background: rgba(243,156,18,0.15); color: #f39c12; border-color: rgba(243,156,18,0.3); }
.status-btn.warning:hover, input:checked + .status-btn.warning { background: #f39c12; color: #fff; }

.status-btn.danger { background: rgba(192,57,43,0.15); color: #c0392b; border-color: rgba(192,57,43,0.3); }
.status-btn.danger:hover, input:checked + .status-btn.danger { background: #c0392b; color: #fff; }

.status-btn.info { background: rgba(52,152,219,0.15); color: #3498db; border-color: rgba(52,152,219,0.3); }
.status-btn.info:hover, input:checked + .status-btn.info { background: #3498db; color: #fff; }

.arrival-time { max-width: 120px; }

.footer-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; width: 100%; }
.footer-actions .btn { flex: 1; min-width: 160px; justify-content: center; }

/* ===========================
   MOBILE RESPONSIVE
   =========================== */
@media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .stat-item { padding: 12px; gap: 10px; }
    .stat-item-icon { width: 34px; height: 34px; font-size: 15px; }

    .date-filter-card { padding: 12px 14px; }
    .date-filter-input input { font-size: 13px; padding: 8px 12px 8px 36px; }

    .bulk-actions { width: 100%; flex-wrap: wrap; gap: 6px; }
    .bulk-actions .btn { flex: 1; min-width: 0; padding: 6px 10px; font-size: 12px; }
    .bulk-actions .btn i { margin: 0 !important; }
    .bulk-actions .btn-text-mobile { display: none; }

    .attendance-status-group { gap: 4px; }
    .status-btn { width: 30px; height: 30px; font-size: 12px; }

    .arrival-time { max-width: 100%; width: 100%; }

    .footer-actions { flex-direction: column; gap: 10px; }
    .footer-actions .btn { width: 100%; }
}

@media (max-width: 480px) {
    .stats-row { grid-template-columns: 1fr 1fr; gap: 8px; }
    .stat-item { padding: 10px; }
    .stat-item-icon { width: 32px; height: 32px; font-size: 14px; }

    .status-btn { width: 28px; height: 28px; font-size: 11px; }
    .attendance-status-group { gap: 3px; }
}
</style>

@endsection

@push('scripts')
<script>
function handleStatusChange(studentId, status) {
    const arrivalInput = document.getElementById('arrival-' + studentId);
    if (!arrivalInput) return;
    if (status === 'late') {
        arrivalInput.style.display = '';
        arrivalInput.required = true;
    } else {
        arrivalInput.style.display = 'none';
        arrivalInput.required = false;
    }
}

function markAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(radio => {
        radio.checked = true;
        const parts = radio.id.split('-');
        const studentId = parts[parts.length - 1];
        handleStatusChange(studentId, status);
    });
}

function submitAsHoliday() {
    if (confirm('هل أنت متأكد من تسجيل هذا اليوم كعطلة لجميع الطلاب؟')) {
        document.getElementById('formAction').value = 'holiday';
        document.getElementById('attendanceForm').submit();
    }
}

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