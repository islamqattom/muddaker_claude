@extends('layouts.app')

@section('title', 'تعديل محضر اجتماع')
@section('page-title', 'تعديل محضر اجتماع')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.meeting-minutes.index') }}">محاضر الاجتماعات</a></li>
    <li class="breadcrumb-item active">تعديل المحضر</li>
@endsection

@section('content')
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
}
body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }
.form-container {
    background: #fff;
    border-radius: 13px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
}
.form-section {
    padding: 16px;
    border-bottom: 1px solid #f0f4f8;
}
.form-section:last-child { border-bottom: none; }
.section-title {
    font-size: 18px; font-weight: 700; color: var(--primary);
    margin-bottom: 16px; padding-bottom: 15px;
    border-bottom: 3px solid var(--gold);
    display: flex; align-items: center; gap: 12px;
}
.section-title i { color: var(--gold); font-size: 18px; }
.section-title span::before { content: '◆'; color: var(--gold); margin-left: 10px; font-size: 10px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
.form-group { margin-bottom: 5px; }
.form-label {
    font-weight: 600; color: #5a6c7d;
    margin-bottom: 10px; display: block;
    font-size: 14px;
}
.form-label i { color: var(--gold); margin-left: 6px; }
.form-control, .form-select {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
}
.form-control:focus, .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212,160,48,0.12);
}
.supervisor-item {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-radius: 13px; padding: 16px 16px;
    margin-bottom: 12px;
    border: 2px solid rgba(24,43,72,0.06);
    transition: all 0.3s ease;
}
.supervisor-item:hover {
    border-color: var(--gold);
    transform: translateX(-3px);
    box-shadow: 0 4px 15px rgba(24,43,72,0.08);
}
.supervisor-item.excluded { opacity: 0.5; background: #f5f5f5; }
.avatar-circle {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
}
.avatar-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: var(--gold);
}
.avatar-secondary {
    background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%);
    color: #fff;
}
.supervisor-name { font-weight: 600; color: var(--primary); }
.supervisor-role { font-size: 12px; color: #95a5a6; }
.toggle-btn {
    padding: 8px 16px; border-radius: 10px;
    font-size: 12px; font-weight: 600;
    border: none; cursor: pointer;
    transition: all 0.3s ease;
}
.toggle-exclude { background: rgba(192,57,43,0.1); color: var(--danger); }
.toggle-exclude:hover { background: var(--danger); color: #fff; }
.supervisor-item.excluded .toggle-exclude { background: var(--success); color: #fff; }
.manual-attendee-row {
    background: #f8f9fa; border-radius: 12px; padding: 12px;
    margin-bottom: 10px; border: 2px dashed #e9ecef;
}
.btn-add {
    background: transparent;
    border: 2px dashed var(--gold);
    border-radius: 12px;
    color: var(--gold);
    padding: 12px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    display: flex; align-items: center; gap: 8px;
}
.btn-add:hover { background: rgba(212,160,48,0.08); border-style: solid; }
.btn-add i { font-size: 14px; }
.agenda-card {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border: 2px solid rgba(24,43,72,0.06);
    border-radius: 13px;
    transition: all 0.3s ease;
}
.agenda-card:hover {
    border-color: var(--gold);
    box-shadow: 0 4px 15px rgba(212,160,48,0.1);
}
.badge-custom {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: var(--gold);
    padding: 8px 16px; border-radius: 13px;
    font-weight: 600; font-size: 13px;
}
.task-card {
    background: #fff; border-radius: 13px; padding: 16px;
    margin-bottom: 14px; border: 2px solid rgba(24,43,72,0.06);
    transition: all 0.3s ease;
}
.task-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(24,43,72,0.1); }
.axis-row {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-radius: 12px; margin-bottom: 10px; padding: 12px;
    border: 1px solid rgba(24,43,72,0.05);
}
.student-table {
    border-radius: 13px; overflow: hidden;
    border: 2px solid rgba(24,43,72,0.06);
}
.student-table thead {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
}
.student-table thead th {
    font-weight: 700; border: none; padding: 16px 12px;
    font-size: 13px; text-align: center; vertical-align: middle;
    white-space: nowrap;
}
.student-row {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f0f4f8;
}
.student-row:hover { background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%); }
.student-row td { padding: 12px 10px; vertical-align: middle; text-align: center; }
.attendance-indicator { display: flex; flex-direction: column; align-items: center; gap: 5px; }
.attendance-bar { width: 70px; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden; }
.attendance-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
.recitation-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 13px;
    font-size: 0.8rem; margin: 2px;
    background: linear-gradient(135deg, rgba(212,160,48,0.1) 0%, rgba(180,128,44,0.15) 100%);
}
.assignment-row {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-radius: 13px; padding: 16px;
    margin-bottom: 12px; border: 2px solid rgba(24,43,72,0.06);
}
.agenda-note-card {
    background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%);
    border-radius: 13px; padding: 16px;
    margin-bottom: 12px; border: 2px solid rgba(212,160,48,0.1);
}
.divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
    margin: 25px 0;
}
.btn-save {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; border: none; border-radius: 13px;
    font-weight: 700; font-size: 15px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.btn-save:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); }
.btn-save i { color: var(--gold); }
.btn-cancel {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 16px;
    background: transparent;
    color: #7f8c8d; border: 2px solid #e9ecef; border-radius: 13px;
    font-weight: 600; font-size: 15px;
    transition: all 0.3s ease;
    text-decoration: none;
    font-family: 'Tajawal', sans-serif;
}
.btn-cancel:hover { border-color: var(--danger); color: var(--danger); }
.section-badge {
    background: var(--gold); color: #fff;
    padding: 4px 12px; border-radius: 13px;
    font-size: 12px; font-weight: 600;
    margin-right: 10px;
}
.status-badge-sm {
    padding: 5px 12px; border-radius: 13px;
    font-size: 11px; font-weight: 600;
}
.badge-warning { background: rgba(243,156,18,0.15); color: var(--warning); }
.badge-success { background: rgba(39,174,96,0.15); color: var(--success); }
.badge-info { background: rgba(52,152,219,0.15); color: var(--info); }
.badge-danger { background: rgba(192,57,43,0.15); color: var(--danger); }
/* Responsive Styles */
@media (max-width: 768px) {
.form-section {
        padding: 16px 15px;
    }
.form-section .section-title {
        font-size: 16px;
        margin-bottom: 15px;
        padding-bottom: 10px;
    }
.form-grid {
        grid-template-columns: 1fr;
    }
.supervisor-item {
        padding: 12px 15px;
    }
.supervisor-item .d-flex {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start !important;
    }
.supervisor-item .d-flex.gap-2 {
        flex-direction: row;
        flex-wrap: wrap;
    }
.btn-add {
        padding: 10px 16px;
        font-size: 13px;
    }
.btn-save, .btn-cancel {
        width: 100%;
        justify-content: center;
        padding: 12px 16px;
        font-size: 14px;
    }
.d-flex.gap-3.justify-content-center.flex-wrap {
        flex-direction: column;
    }
.divider {
        margin: 15px 0;
    }
.axis-row .row.g-2 {
        flex-direction: column;
    }
.axis-row .col-4, .axis-row .col-8 {
        max-width: 100%;
        flex: 0 0 100%;
    }
.axis-row {
        margin-bottom: 15px;
    }
.assignment-row .row.g-3 > div {
        margin-bottom: 10px;
    }
.assignment-row .row.g-3 {
        flex-direction: column;
    }
.table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
.student-table {
        font-size: 12px;
    }
.student-table thead th, .student-table tbody td {
        padding: 10px 8px;
        white-space: nowrap;
    }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-edit"></i>
        </div>
        <div class="header-text">
            <h4>تعديل محضر اجتماع</h4>
            <p>تعديل بيانات محضر جلسة جمعية تحفيظ القرآن الكريم</p>
        </div>
    </div>
</div>

<div class="form-container">
    <form method="POST" action="{{ route('admin.meeting-minutes.update', $meetingMinute->id) }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-calendar-alt"></i>
                <span>معلومات الاجتماع</span>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-day"></i>تاريخ الاجتماع</label>
                    <input type="date" name="meeting_date" class="form-control @error('meeting_date') is-invalid @enderror" value="{{ old('meeting_date', $meetingMinute->meeting_date->toDateString()) }}" required>
                    @error('meeting_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-users"></i>
                <span>الحضور</span>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <h6 class="mb-3" style="color:var(--primary);font-weight:600">
                        <i class="fas fa-user-tie" style="color:var(--gold);margin-left:8px"></i>
                        المشرفون
                    </h6>
                    <div id="supervisors-list">
                        @php
                            $excludedIds = old('excluded_supervisors', $meetingMinute->excluded_supervisors ?? []);
                            $attendeesData = old('attendees', $meetingMinute->attendees ?? []);
                            $attendeesByUserId = collect($attendeesData)->keyBy('user_id');
                        @endphp
                        @foreach($supervisors as $supervisor)
                            @php
                                $isExcluded = in_array($supervisor->id, $excludedIds);
                                $attendeeData = $attendeesByUserId->get($supervisor->id);
                                $isPresent = $attendeeData['is_present'] ?? true;
                            @endphp
                            <div class="supervisor-item {{ $isExcluded ? 'excluded' : '' }}" data-supervisor-id="{{ $supervisor->id }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle avatar-primary">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="me-3">
                                            <span class="fw-semibold supervisor-name" style="{{ $isexcluded ? 'text-decoration:line-through;color:#999;':'' }}">{{ $supervisor->name }}</span>
                                            <span class="text-muted small d-block supervisor-role">{{ $supervisor->role === 'admin' ? 'مدير' : 'مشرف' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <select name="attendees[{{ $supervisor->id }}][is_present]" class="form-select form-select-sm" style="width:100px">
                                            <option value="1" {{ $isPresent ? 'selected' : '' }}>حاضر</option>
                                            <option value="0" {{ !$isPresent ? 'selected' : '' }}>غائب</option>
                                        </select>
                                        <button type="button" class="toggle-btn toggle-exclude" onclick="toggleExclude(this)">
                                            <i class="fas fa-{{ $isExcluded ? 'check' : 'times' }}"></i>
                                            {{ $isExcluded ? 'مُدرج' : 'استبعاد' }}
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="attendees[{{ $supervisor->id }}][user_id]" value="{{ $supervisor->id }}">
                                <input type="hidden" name="attendees[{{ $supervisor->id }}][name]" value="{{ $supervisor->name }}">
                                <input type="hidden" name="attendees[{{ $supervisor->id }}][role]" value="{{ $supervisor->role }}">
                                <input type="hidden" name="excluded_supervisors[]" value="{{ $supervisor->id }}" class="exclude-input" {{ $isExcluded ? '' : 'disabled' }}>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-6">
                    <h6 class="mb-3" style="color:var(--primary);font-weight:600">
                        <i class="fas fa-user-plus" style="color:var(--gold);margin-left:8px"></i>
                        إضافة حضور يدوي
                    </h6>
                    <div id="manual-attendees-container">
                        @php $manualAttendees = old('manual_attendees', $meetingMinute->manual_attendees ?? [['name' => '']]); @endphp
                        @foreach($manualAttendees as $i => $attendee)
                        <div class="manual-attendee-row">
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" name="manual_attendees[{{ $i }}][name]" class="form-control" placeholder="اسم الحضور" value="{{ $attendee['name'] ?? '' }}">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-manual-attendee" style="width:42px;border-radius:10px">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-add w-100 mt-3" onclick="addManualAttendee()">
                        <i class="fas fa-plus"></i>إضافة حضور
                    </button>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-list-ul"></i>
                <span>جدول الأعمال</span>
            </div>
            <div class="row g-3" id="agenda-container">
                @php
                    $defaultAgenda = [
                        'المحضر السابق',
                        'برنامج الدّوام',
                        'وضع الطّلاب',
                        'المتابعات',
                        'الإجازة',
                        'المنهاج'
                    ];
                    $agendaItems = old('agenda', $meetingMinute->agenda ?? $defaultAgenda);
                    if (is_string($agendaItems)) {
                        $agendaItems = json_decode($agendaItems, true) ?: $defaultAgenda;
                    }
                    if (!is_array($agendaItems)) {
                        $agendaItems = $defaultAgenda;
                    }
                @endphp
                @foreach($agendaItems as $i => $item)
                <div class="col-md-6 agenda-item" data-index="{{ $i }}">
                    <div class="agenda-card p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge-custom agenda-number">{{ $i + 1 }}</span>
                            @if($i >= 6)
                            <button type="button" class="btn btn-sm btn-outline-danger remove-agenda" style="border-radius:8px">
                                <i class="fas fa-times"></i>
                            </button>
                            @else
                            <span class="section-badge">افتراضي</span>
                            @endif
                        </div>
                        <input type="text" name="agenda[]" class="form-control agenda-input" placeholder="البند {{ $i + 1 }}" value="{{ is_array($item) ? ($item['text'] ?? '') : $item }}">
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn-add mt-4" onclick="addAgendaItem()">
                <i class="fas fa-plus"></i>إضافة بند جديد
            </button>
        </div>

        @if(isset($changedTasks) && $changedTasks->count() > 0)
        <div class="divider"></div>
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-sync-alt" style="color:var(--warning)"></i>
                <span>التكليفات المتغيرة</span>
                <span class="section-badge">{{ $changedTasks->count() }}</span>
            </div>
            <div class="row g-3">
                @foreach($changedTasks as $task)
                <div class="col-md-6">
                    <div class="task-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="fw-semibold" style="color:var(--primary)">{{ $task->title }}</span>
                                <span class="text-muted small d-block">
                                    {{ $task->assignee->name ?? '-' }}
                                    @if($task->meetingMinute)
                                        <span class="mx-2">•</span>
                                        {{ $task->meetingMinute->meeting_date->format('Y/m/d') }}
                                    @endif
                                </span>
                            </div>
                            <span class="status-badge-sm badge-{{ $task->status_color }}">{{ $task->status_label }}</span>
                        </div>
                        <select name="task_status[{{ $task->id }}]" class="form-select form-select-sm" onchange="updateTaskStatusColor(this)">
                            <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                            <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                            <option value="cancelled" {{ $task->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                        </select>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(isset($tasksFromAllMeetings) && $tasksFromAllMeetings->count() > 0)
        <div class="divider"></div>
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-tasks" style="color:var(--success)"></i>
                <span>متابعة التكليفات</span>
                <span class="section-badge">{{ $tasksFromAllMeetings->count() }}</span>
            </div>
            <div class="row g-3">
                @foreach($tasksFromAllMeetings as $task)
                <div class="col-md-6">
                    <div class="task-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="fw-semibold" style="color:var(--primary)">{{ $task->title }}</span>
                                <span class="text-muted small d-block">
                                    {{ $task->assignee->name ?? '-' }}
                                    @if($task->meetingMinute)
                                        <span class="mx-2">•</span>
                                        {{ $task->meetingMinute->meeting_date->format('Y/m/d') }}
                                    @endif
                                </span>
                            </div>
                            <span class="status-badge-sm badge-{{ $task->status_color }}">{{ $task->status_label }}</span>
                        </div>
                        <select name="task_status[{{ $task->id }}]" class="form-select form-select-sm" onchange="updateTaskStatusColor(this)">
                            <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                            <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                            <option value="cancelled" {{ $task->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                        </select>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="divider"></div>

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-calendar-week" style="color:var(--purple)"></i>
                <span>برنامج الدّوام</span>
            </div>
            <div id="program-axes-body">
                @php
                    $defaultAxes = [
                        'التّمهيدية',
                        'المتوسّطة',
                        'المتقدّمة',
                        'التّفسير',
                        'السّيرة',
                        'الفقه',
                        'النّحو',
                        'اليوم الآخر'
                    ];
                    $savedAxes = old('program_axes_names', $meetingMinute->program_axes_names ?? $defaultAxes);
                    if (is_string($savedAxes)) {
                        $savedAxes = json_decode($savedAxes, true) ?: $defaultAxes;
                    }
                    if (!is_array($savedAxes)) {
                        $savedAxes = $defaultAxes;
                    }
                @endphp
                <div class="row g-2">
                    @foreach($savedAxes as $i => $axis)
                    <?php $programAxesArray = is_array($meetingMinute->program_axes ?? null) ? $meetingMinute->program_axes : (json_decode($meetingMinute->program_axes ?? '[]', true) ?: []); ?>
                    <div class="col-md-6">
                        <div class="axis-row">
                            <div class="row g-2 align-items-center">
                                <div class="col-4">
                                    <input type="text" name="program_axes_names[]" class="form-control form-control-sm" value="{{ $axis }}" placeholder="المحور">
                                </div>
                                <div class="col-8">
                                    <textarea name="program_axes[]" class="form-control form-control-sm" rows="2" placeholder="التفاصيل">{{ old('program_axes.' . $i, $programAxesArray[$i] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if(isset($supervisorStudents) && $supervisorStudents->count() > 0)
        <div class="divider"></div>
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-user-graduate" style="color:#ec4899"></i>
                <span>ملخص متابعة الطلاب</span>
            </div>
            <div class="table-responsive">
                <table class="table student-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:60px">#</th>
                            <th class="text-center">المشرف</th>
                            <th class="text-center">الطالب</th>
                            <th class="text-center">الحضور</th>
                            <th class="text-center">التسميع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowIndex = 1; @endphp
                        @foreach($supervisorStudents as $supervisorIndex => $supervisor)
                            @php $studentCount = count($supervisor->students_with_data); @endphp
                            @foreach($supervisor->students_with_data as $studentIndex => $student)
                            <tr class="student-row">
                                <td class="text-center align-middle">
                                    <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);color:var(--gold);width:32px;height:32px;font-size:13px;font-weight:700">{{ $rowIndex }}</span>
                                </td>
                                @if($studentIndex == 0)
                                <td class="text-center align-middle" rowspan="{{ $studentCount }}">
                                    <div class="supervisor-cell d-flex align-items-center justify-content-center gap-2 py-2">
                                        <div class="avatar-circle avatar-primary flex-shrink-0">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <span class="fw-bold" style="color:var(--primary)">{{ $supervisor->name }}</span>
                                    </div>
                                </td>
                                @endif
                                <td class="text-center align-middle">
                                    <div class="student-cell d-flex align-items-center justify-content-center gap-2 py-2">
                                        <div class="avatar-circle avatar-secondary flex-shrink-0">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <span style="color:var(--primary);font-weight:600">{{ $student->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    @if($student->last_three_attendances && $student->last_three_attendances->count() > 0)
                                        @php
                                            $presentCount = $student->last_three_attendances->where('status', 'present')->count();
                                            $totalDays = $student->last_three_attendances->count();
                                            $attendanceRatio = $presentCount . '/' . $totalDays;
                                            $ratioColor = $presentCount == $totalDays ? '#27ae60' : ($presentCount >= ($totalDays / 2) ? '#f39c12' : '#c0392b');
                                        @endphp
                                        <span class="attendance-ratio-badge d-inline-flex align-items-center justify-content-center rounded-pill" style="background:{{ $ratioColor }};color:#fff;padding:6px 16px;font-weight:700;font-size:14px">
                                            {{ $attendanceRatio }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($student->last_two_recitations && $student->last_two_recitations->count() > 0)
                                        <div class="recitations-wrapper d-flex flex-column gap-2">
                                            @foreach($student->last_two_recitations as $recitation)
                                            <div class="recitation-card d-flex align-items-center gap-2" style="background:linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);border:1px solid rgba(24,43,72,0.1);border-radius:10px;padding:10px 12px">
                                                <span class="recitation-type fw-bold" style="color:var(--primary);font-size:13px;flex:1">{{ $recitation->type_label }}</span>
                                                <span class="status-indicator d-inline-flex align-items-center gap-2" style="color:{{ $recitation->status_color }};font-size:12px;font-weight:600">
                                                    <span style="width:8px;height:8px;background:{{ $recitation->status_color }};border-radius:50%"></span>
                                                    {{ $recitation->status_label }}
                                                </span>
                                                <a href="{{ route('admin.sessions.show', $recitation->id) }}" target="_blank" class="view-btn d-inline-flex align-items-center justify-content-center" style="width:26px;height:26px;background:linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);border-radius:6px;color:#fff;text-decoration:none;transition:all 0.3s ease">
                                                    <i class="fas fa-eye" style="font-size:10px"></i>
                                                </a>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:13px">—</span>
                                    @endif
                                </td>
                            </tr>
                            @php $rowIndex++; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($meetingMinute->tasks && $meetingMinute->tasks->count() > 0)
        <div class="divider"></div>
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-edit" style="color:var(--warning)"></i>
                <span>تعديل التكليفات القائمة</span>
                <span class="section-badge">{{ $meetingMinute->tasks->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%)">
                            <th class="text-center" style="color:var(--gold);font-weight:700;padding:14px 12px">#</th>
                            <th style="color:var(--gold);font-weight:700;padding:14px 12px">المشرف</th>
                            <th style="color:var(--gold);font-weight:700;padding:14px 12px">وصف التكليف</th>
                            <th style="color:var(--gold);font-weight:700;padding:14px 12px">الحالة</th>
                            <th class="text-center" style="color:var(--gold);font-weight:700;padding:14px 12px">الحالة الحالية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meetingMinute->tasks as $index => $task)
                        <tr style="border-bottom:2px solid rgba(24,43,72,0.08)">
                            <td class="text-center align-middle" style="padding:12px">
                                <span class="fw-bold" style="color:var(--primary);font-size:16px">{{ $index + 1 }}</span>
                            </td>
                            <td style="padding:12px">
                                <span class="fw-semibold" style="color:var(--primary)">{{ $task->assignee->name ?? '-' }}</span>
                            </td>
                            <td style="padding:12px">
                                <input type="text" name="task_titles[{{ $task->id }}]" class="form-control" value="{{ $task->title }}" placeholder="وصف التكليف">
                            </td>
                            <td style="padding:12px;min-width:160px">
                                <select name="task_status[{{ $task->id }}]" class="form-select">
                                    <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="cancelled" {{ $task->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                            </td>
                            <td class="text-center align-middle" style="padding:12px">
                                <span class="badge badge-{{ $task->status_color }}">{{ $task->status_label }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="divider"></div>

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-clipboard-list" style="color:var(--info)"></i>
                <span>التكليفات الجديدة</span>
            </div>
            <div id="assignments-container">
                @php $savedAssignments = old('assignments', [['supervisor_id' => '', 'task' => '']]); @endphp
                @foreach($savedAssignments as $i => $assignment)
                <div class="assignment-row" data-index="{{ $i }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select name="assignments[{{ $i }}][supervisor_id]" class="form-select">
                                <option value="">-- المشرف --</option>
                                @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}" {{ ($assignment['supervisor_id'] ?? '') == $supervisor->id ? 'selected' : '' }}>
                                    {{ $supervisor->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="assignments[{{ $i }}][task]" class="form-control" placeholder="وصف التكليف" value="{{ $assignment['task'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100 remove-assignment" style="border-radius:10px">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn-add mt-3" onclick="addAssignment()">
                <i class="fas fa-plus"></i>إضافة تكليف
            </button>
        </div>

        <div class="divider"></div>

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-file-alt" style="color:#06b6d4"></i>
                <span>تفاصيل بنود جدول الأعمال</span>
            </div>
            <div id="agenda-notes-container">
                @php
                    $savedAgendaNotes = old('agenda_notes', $meetingMinute->agenda_notes ?? []);
                    if (is_string($savedAgendaNotes)) {
                        $savedAgendaNotes = json_decode($savedAgendaNotes, true) ?: [];
                    }
                @endphp
                @foreach($agendaItems as $i => $item)
                    @php
                        $itemText = is_array($item) ? ($item['text'] ?? '') : $item;
                        $excludedItems = ['المحضر السابق', 'برنامج الدّوام', 'وضع الطّلاب', 'المتابعات'];
                    @endphp
                    @if(!in_array($itemText, $excludedItems))
                    <div class="agenda-note-card" data-index="{{ $i }}" data-item-text="{{ $itemText }}">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <span class="badge-custom">{{ $itemText }}</span>
                            </div>
                            <div class="col-md-9">
                                <textarea name="agenda_notes[{{ $i }}]" class="form-control" rows="2" placeholder="أدخل التفاصيل...">{{ $savedAgendaNotes[$i] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="divider"></div>

        <div class="form-section text-center">
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>تحديث المحضر
                </button>
                <a href="{{ route('admin.meeting-minutes.show', $meetingMinute->id) }}" class="btn-cancel">
                    <i class="fas fa-times"></i>إلغاء
                </a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function addManualAttendee() {
    var container = $('#manual-attendees-container');
    var count = container.find('.manual-attendee-row').length;
    var html = '<div class="manual-attendee-row">' +
        '<div class="d-flex gap-2 align-items-center">' +
        '<input type="text" name="manual_attendees[' + count + '][name]" class="form-control" placeholder="اسم الحضور">' +
        '<button type="button" class="btn btn-outline-danger btn-sm remove-manual-attendee" style="width:42px;border-radius:10px">' +
        '<i class="fas fa-trash-alt"></i>' +
        '</button>' +
        '</div>' +
        '</div>';
    container.append(html);
}

function addAgendaItem() {
    var container = $('#agenda-container');
    var count = container.find('.agenda-item').length;
    var newIndex = count;
    var html = '<div class="col-md-6 agenda-item" data-index="' + newIndex + '">' +
        '<div class="agenda-card p-3 h-100">' +
        '<div class="d-flex align-items-center justify-content-between mb-3">' +
        '<span class="badge-custom agenda-number">' + (count + 1) + '</span>' +
        '<button type="button" class="btn btn-sm btn-outline-danger remove-agenda" style="border-radius:8px">' +
        '<i class="fas fa-times"></i>' +
        '</button>' +
        '</div>' +
        '<input type="text" name="agenda[]" class="form-control agenda-input" placeholder="البند ' + (count + 1) + '">' +
        '</div>' +
        '</div>';
    container.append(html);
    renumberAgenda();

    var notesContainer = $('#agenda-notes-container');
    var notesHtml = '<div class="agenda-note-card" data-index="' + newIndex + '" data-item-text="">' +
        '<div class="row g-3 align-items-center">' +
        '<div class="col-md-3">' +
        '<span class="badge-custom"></span>' +
        '</div>' +
        '<div class="col-md-9">' +
        '<textarea name="agenda_notes[' + newIndex + ']" class="form-control" rows="2" placeholder="أدخل التفاصيل..."></textarea>' +
        '</div>' +
        '</div>' +
        '</div>';
    notesContainer.append(notesHtml);

    container.find('.agenda-item[data-index="' + newIndex + '"] .agenda-input').focus();
}

$(document).on('input', '.agenda-input', function() {
    var itemText = $(this).val();
    var index = $(this).closest('.agenda-item').attr('data-index');
    var noteCard = $('.agenda-note-card[data-index="' + index + '"]');
    if (noteCard.length) {
        noteCard.find('.badge-custom').first().text(itemText);
        noteCard.attr('data-item-text', itemText);
    }
});

function renumberAgenda() {
    var idx = 1;
    $('.agenda-item').each(function() {
        $(this).find('.agenda-number').text(idx);
        $(this).find('.agenda-input').attr('placeholder', 'البند ' + idx);
        $(this).attr('data-index', idx - 1);
        idx++;
    });
    renumberAgendaNotes();
}

function renumberAgendaNotes() {
    var idx = 0;
    $('.agenda-item').each(function() {
        var itemText = $(this).find('.agenda-input').val() || 'البند ' + (idx + 1);
        var noteCard = $('.agenda-note-card[data-index="' + $(this).attr('data-index') + '"]');
        if (noteCard.length) {
            noteCard.attr('data-index', idx);
            noteCard.find('.badge-custom').first().text(itemText);
            noteCard.find('textarea').attr('name', 'agenda_notes[' + idx + ']');
            noteCard.attr('data-item-text', itemText);
        }
        idx++;
    });
}

function toggleExclude(btn) {
    var item = $(btn).closest('.supervisor-item');
    var excludeInput = item.find('.exclude-input');
    var nameSpan = item.find('.supervisor-name');

    if (item.hasClass('excluded')) {
        item.removeClass('excluded');
        $(btn).removeClass('btn-success').addClass('toggle-exclude');
        $(btn).html('<i class="fas fa-times"></i> استبعاد');
        excludeInput.prop('disabled', true);
        nameSpan.css('text-decoration', '').css('color', '');
    } else {
        item.addClass('excluded');
        $(btn).addClass('btn-success').removeClass('toggle-exclude');
        $(btn).html('<i class="fas fa-check"></i> مُدرج');
        excludeInput.prop('disabled', false);
        nameSpan.css('text-decoration', 'line-through').css('color', '#999');
    }
}

function renumberAssignments() {
    var idx = 0;
    $('#assignments-container .assignment-row').each(function() {
        $(this).attr('data-index', idx);
        $(this).find('select').attr('name', 'assignments[' + idx + '][supervisor_id]');
        $(this).find('input[type="text"]').attr('name', 'assignments[' + idx + '][task]');
        idx++;
    });
}

function addAssignment() {
    var container = $('#assignments-container');
    var count = container.find('.assignment-row').length;
    var supervisors = @json($supervisors);
    var options = '<option value="">-- المشرف --</option>';
    supervisors.forEach(function(s) {
        options += '<option value="' + s.id + '">' + s.name + '</option>';
    });
    var html = '<div class="assignment-row" data-index="' + count + '">' +
        '<div class="row g-3">' +
        '<div class="col-md-4">' +
        '<select name="assignments[' + count + '][supervisor_id]" class="form-select">' + options + '</select>' +
        '</div>' +
        '<div class="col-md-6">' +
        '<input type="text" name="assignments[' + count + '][task]" class="form-control" placeholder="وصف التكليف">' +
        '</div>' +
        '<div class="col-md-2">' +
        '<button type="button" class="btn btn-outline-danger w-100 remove-assignment" style="border-radius:10px">' +
        '<i class="fas fa-trash-alt"></i>' +
        '</button>' +
        '</div>' +
        '</div>' +
        '</div>';
    container.append(html);
}

function updateTaskStatusColor(select) {
    var statusColors = {
        'pending': 'warning',
        'in_progress': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    var statusLabels = {
        'pending': 'قيد الانتظار',
        'in_progress': 'قيد التنفيذ',
        'completed': 'مكتمل',
        'cancelled': 'ملغي'
    };
    var badge = $(select).closest('.task-card').find('.status-badge-sm');
    var newStatus = select.value;
    badge.removeClass('badge-warning badge-info badge-success badge-danger').addClass('badge-' + statusColors[newStatus]);
    badge.text(statusLabels[newStatus]);
}

$(document).on('click', '.remove-manual-attendee', function() {
    $(this).closest('.manual-attendee-row').remove();
});

$(document).on('click', '.remove-agenda', function() {
    var index = $(this).closest('.agenda-item').attr('data-index');
    $(this).closest('.agenda-item').remove();
    $('.agenda-note-card[data-index="' + index + '"]').remove();
    renumberAgenda();
});

$(document).on('click', '.remove-assignment', function() {
    $(this).closest('.assignment-row').remove();
    renumberAssignments();
});
</script>
@endpush
@endsection