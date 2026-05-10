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

<div class="page-header mb-4">
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

<div class="stats-row mb-4">
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

<div class="date-filter-card mb-4">
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
                <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
                    <i class="fas fa-check me-1"></i>تحديد الكل حاضر
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                    <i class="fas fa-times me-1"></i>تحديد الكل غائب
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
                                    <label class="status-btn success" for="present-{{ $student->id }}">
                                        <i class="fas fa-check"></i>
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="late-{{ $student->id }}" value="late"
                                           {{ $currentStatus === 'late' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'late')">
                                    <label class="status-btn warning" for="late-{{ $student->id }}">
                                        <i class="fas fa-clock"></i>
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="absent-{{ $student->id }}" value="absent"
                                           {{ $currentStatus === 'absent' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'absent')">
                                    <label class="status-btn danger" for="absent-{{ $student->id }}">
                                        <i class="fas fa-times"></i>
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $student->id }}][status]"
                                           id="excused-{{ $student->id }}" value="excused"
                                           {{ $currentStatus === 'excused' ? 'checked' : '' }}
                                           onchange="handleStatusChange({{ $student->id }}, 'excused')">
                                    <label class="status-btn info" for="excused-{{ $student->id }}">
                                        <i class="fas fa-file-alt"></i>
                                    </label>
                                </div>
                                <div class="status-labels">
                                    <span class="status-label {{ $currentStatus === 'present' ? 'active' : '' }}" data-for="present-{{ $student->id }}">حاضر</span>
                                    <span class="status-label {{ $currentStatus === 'late' ? 'active' : '' }}" data-for="late-{{ $student->id }}">متأخر</span>
                                    <span class="status-label {{ $currentStatus === 'absent' ? 'active' : '' }}" data-for="absent-{{ $student->id }}">غائب</span>
                                    <span class="status-label {{ $currentStatus === 'excused' ? 'active' : '' }}" data-for="excused-{{ $student->id }}">بعذر</span>
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
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

@media (max-width: 768px) { .page-header { flex-direction: column; align-items: flex-start; } }

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #d4a030;
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.header-actions { display: flex; gap: 10px; }

.stats-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.stat-item {
    flex: 1;
    min-width: 120px;
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.stat-item-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}

.stat-item.success .stat-item-icon { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.stat-item.danger .stat-item-icon { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.stat-item.warning .stat-item-icon { background: linear-gradient(135deg, #f39c12, #e67e22); }
.stat-item.info .stat-item-icon { background: linear-gradient(135deg, #3498db, #2980b9); }
.stat-item.secondary .stat-item-icon { background: linear-gradient(135deg, #7f8c8d, #95a5a6); }

.stat-item-content { flex: 1; }
.stat-number { font-size: 28px; font-weight: 800; color: #182B48; line-height: 1; }
.stat-label { font-size: 12px; color: #7f8c8d; margin-top: 4px; }

.date-filter-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.date-filter-form { margin: 0; }

.date-filter-input { position: relative; display: flex; align-items: center; }

.date-filter-input i {
    position: absolute;
    right: 14px;
    color: #d4a030;
}

.date-filter-input input {
    padding: 10px 15px 10px 40px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    color: #182B48;
}

.table-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
}

.table-title i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.table-actions { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }

.record-count { color: #7f8c8d; font-size: 13px; display: flex; align-items: center; gap: 8px; }
.record-count i { color: #d4a030; }

.bulk-actions { display: flex; gap: 8px; }

.modern-table { width: 100%; border-collapse: collapse; }
.modern-table thead { background: #f8f9fa; }

.modern-table th {
    padding: 16px 20px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 14px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.row-num { color: #bdc3c7; font-weight: 600; font-size: 13px; }

.user-info-cell { display: flex; align-items: center; gap: 14px; }

.user-avatar-table {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 16px;
}

.user-details { display: flex; flex-direction: column; }
.user-name { color: #182B48; font-weight: 600; font-size: 14px; }
.user-username { color: #7f8c8d; font-size: 12px; }

.level-badge {
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.no-data { color: #bdc3c7; }

.attendance-status-group { display: flex; gap: 6px; }

.status-btn {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
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

.status-labels { display: flex; gap: 8px; margin-top: 8px; }

.status-label {
    font-size: 10px;
    color: #bdc3c7;
    font-weight: 600;
    transition: all 0.2s;
}

.status-label.active { color: #182B48; }

.arrival-time { max-width: 120px; }

.table-footer {
    padding: 20px 25px;
    border-top: 1px solid #f0f4f8;
    display: flex;
    justify-content: center;
}

.footer-actions { display: flex; gap: 15px; }

.empty-state { text-align: center; padding: 60px 20px; }

.empty-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: #d4a030;
}

.empty-state h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state p { color: #7f8c8d; margin-bottom: 25px; }
</style>

@endsection

@push('scripts')
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

    document.querySelectorAll(`.status-label[data-for^="${studentId}"]`).forEach(label => {
        label.classList.remove('active');
    });
    const activeLabel = document.querySelector(`.status-label[data-for="${status}-${studentId}"]`);
    if (activeLabel) activeLabel.classList.add('active');
}

function markAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(radio => {
        radio.checked = true;
        const studentId = radio.id.split('-')[1];
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