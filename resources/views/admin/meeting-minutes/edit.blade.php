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
    .section-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .section-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .section-header h5 {
        margin: 0;
        font-weight: 600;
    }
    .section-body {
        padding: 20px;
    }
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #444;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title i {
        color: #667eea;
    }
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 10px 14px;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
    }
    .form-label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
    }
    .supervisor-item {
        background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 10px;
        border: 1px solid #e8ecf4;
        transition: all 0.3s ease;
    }
    .supervisor-item:hover {
        border-color: #667eea;
        transform: translateX(-2px);
    }
    .supervisor-item.excluded {
        opacity: 0.5;
        background: #f5f5f5;
    }
    .manual-attendee-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 8px;
    }
    .agenda-card {
        background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
        border: 1px solid #e8ecf4;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .agenda-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102,126,234,0.15);
    }
    .badge-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 500;
    }
    .task-card {
        background: #fff;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid #e8ecf4;
        transition: all 0.3s ease;
    }
    .task-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .axis-row {
        background: #fafafa;
        border-radius: 10px;
        margin-bottom: 8px;
        padding: 8px;
    }
    .axis-row input, .axis-row textarea {
        border-radius: 8px;
    }
    .student-table {
        border-radius: 12px;
        overflow: hidden;
    }
    .student-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .student-table thead th {
        font-weight: 600;
        border: none;
        padding: 14px 12px;
    }
    .student-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f0f0f0;
    }
    .student-row:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
    }
    .student-row td {
        padding: 14px 12px;
        vertical-align: middle;
    }
    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 8px;
    }
    .avatar-purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .avatar-gray {
        background: linear-gradient(135deg, #868f96 0%, #596164 100%);
        color: white;
    }
    .attendance-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .attendance-bar {
        width: 60px;
        height: 6px;
        background: #e0e0e0;
        border-radius: 3px;
        overflow: hidden;
    }
    .attendance-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .recitation-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        margin: 2px;
    }
    .assignment-row-custom {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 10px;
        border: 1px solid #e8ecf4;
    }
    .agenda-note-card {
        background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 10px;
        border: 1px solid #e8ecf4;
    }
    .btn-primary-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102,126,234,0.4);
    }
    .btn-secondary-custom {
        background: #f0f0f0;
        border: none;
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        color: #666;
        transition: all 0.3s ease;
    }
    .btn-secondary-custom:hover {
        background: #e0e0e0;
        color: #333;
    }
    .btn-add {
        background: transparent;
        border: 2px dashed #667eea;
        border-radius: 10px;
        color: #667eea;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn-add:hover {
        background: rgba(102,126,234,0.1);
        border-style: solid;
    }
    .divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
        margin: 20px 0;
    }
    .empty-state {
        text-align: center;
        padding: 30px;
        color: #999;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.3;
    }
</style>

<div class="section-card">
    <div class="section-header">
        <i class="fas fa-edit"></i>
        <h5>تعديل محضر اجتماع</h5>
    </div>
    <div class="section-body">
        <form method="POST" action="{{ route('admin.meeting-minutes.update', $meetingMinute->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label">تاريخ الاجتماع <span class="text-danger">*</span></label>
                    <input type="date" name="meeting_date" class="form-control @error('meeting_date') is-invalid @enderror" value="{{ old('meeting_date', $meetingMinute->meeting_date->toDateString()) }}" required>
                    @error('meeting_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="divider"></div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-section-title">
                        <i class="fas fa-users"></i>
                        <span>الحضور</span>
                    </div>
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
                                        <div class="avatar-circle avatar-purple">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold supervisor-name" style="{{ $isExcluded ? 'text-decoration: line-through; color: #999;' : '' }}">{{ $supervisor->name }}</span>
                                            <span class="text-muted small d-block">{{ $supervisor->role === 'admin' ? 'مدير' : 'مشرف' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <select name="attendees[{{ $supervisor->id }}][is_present]" class="form-select form-select-sm" style="width: 100px;">
                                            <option value="1" {{ $isPresent ? 'selected' : '' }}>حاضر</option>
                                            <option value="0" {{ !$isPresent ? 'selected' : '' }}>غائب</option>
                                        </select>
                                        <button type="button" class="btn btn-sm toggle-exclude {{ $isExcluded ? 'btn-success' : 'btn-outline-danger' }}" onclick="toggleExclude(this)">
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

                <div class="col-md-6">
                    <div class="form-section-title">
                        <i class="fas fa-user-plus"></i>
                        <span>إضافة حضور يدوي</span>
                    </div>
                    <div id="manual-attendees-container">
                        @php $manualAttendees = old('manual_attendees', $meetingMinute->manual_attendees ?? [['name' => '']]); @endphp
                        @foreach($manualAttendees as $i => $attendee)
                        <div class="manual-attendee-row mb-2">
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" name="manual_attendees[{{ $i }}][name]" class="form-control" placeholder="اسم الحضور" value="{{ $attendee['name'] ?? '' }}">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-manual-attendee" style="width: 40px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-add w-100 mt-2" onclick="addManualAttendee()">
                        <i class="fas fa-plus me-2"></i>إضافة حضور
                    </button>
                </div>
            </div>

            <div class="divider"></div>

            <div class="form-section-title">
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
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge-custom agenda-number">{{ $i + 1 }}</span>
                            @if($i >= 6)
                            <button type="button" class="btn btn-sm btn-outline-danger remove-agenda">
                                <i class="fas fa-times"></i>
                            </button>
                            @else
                            <span class="badge bg-secondary">افتراضي</span>
                            @endif
                        </div>
                        <input type="text" name="agenda[]" class="form-control agenda-input" placeholder="البند {{ $i + 1 }}" value="{{ is_array($item) ? ($item['text'] ?? '') : $item }}">
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-add mt-3" onclick="addAgendaItem()">
                <i class="fas fa-plus me-2"></i>إضافة بند جديد
            </button>

            @if(isset($changedTasks) && $changedTasks->count() > 0)
            <div class="divider"></div>
            <div class="form-section-title">
                <i class="fas fa-sync-alt" style="color: #f59e0b;"></i>
                <span>التكليفات المتغيرة</span>
                <span class="badge bg-warning text-dark ms-2">{{ $changedTasks->count() }}</span>
            </div>
            <div class="row g-3">
                @foreach($changedTasks as $task)
                <div class="col-md-6">
                    <div class="task-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-semibold">{{ $task->title }}</span>
                                <span class="text-muted small d-block">
                                    {{ $task->assignee->name ?? '-' }}
                                    @if($task->meetingMinute)
                                        <span class="mx-1">•</span>
                                        {{ $task->meetingMinute->meeting_date->format('Y/m/d') }}
                                    @endif
                                </span>
                            </div>
                            <span class="badge bg-{{ $task->status_color }} task-status-badge">{{ $task->status_label }}</span>
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
            @endif

            @if(isset($tasksFromAllMeetings) && $tasksFromAllMeetings->count() > 0)
            <div class="divider"></div>
            <div class="form-section-title">
                <i class="fas fa-tasks" style="color: #10b981;"></i>
                <span>متابعة التكليفات</span>
                <span class="badge bg-success text-white ms-2">{{ $tasksFromAllMeetings->count() }}</span>
            </div>
            <div class="row g-3">
                @foreach($tasksFromAllMeetings as $task)
                <div class="col-md-6">
                    <div class="task-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-semibold">{{ $task->title }}</span>
                                <span class="text-muted small d-block">
                                    {{ $task->assignee->name ?? '-' }}
                                    @if($task->meetingMinute)
                                        <span class="mx-1">•</span>
                                        {{ $task->meetingMinute->meeting_date->format('Y/m/d') }}
                                    @endif
                                </span>
                            </div>
                            <span class="badge bg-{{ $task->status_color }} task-status-badge">{{ $task->status_label }}</span>
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
            @endif

            <div class="divider"></div>

            <div class="form-section-title">
                <i class="fas fa-calendar-alt" style="color: #8b5cf6;"></i>
                <span>برنامج الدّوام</span>
            </div>
            <div class="row g-2" id="program-axes-body">
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
                @endphp
                @foreach($savedAxes as $i => $axis)
                <div class="col-md-6">
                    <div class="axis-row">
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="text" name="program_axes_names[]" class="form-control form-control-sm" value="{{ $axis }}" placeholder="المحور">
                            </div>
                            <div class="col-8">
                                <textarea name="program_axes[]" class="form-control form-control-sm" rows="2" placeholder="التفاصيل">{{ old('program_axes.' . $i, $meetingMinute->program_axes[$i] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(isset($supervisorStudents) && $supervisorStudents->count() > 0)
            <div class="divider"></div>

            <div class="form-section-title">
                <i class="fas fa-user-graduate" style="color: #ec4899;"></i>
                <span>ملخص متابعة الطلاب</span>
            </div>
            <div class="table-responsive student-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>المشرف</th>
                            <th>الطالب</th>
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
                                <td class="text-center">
                                    <span class="badge rounded-circle" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; width: 28px; height: 28px; line-height: 28px;">{{ $rowIndex }}</span>
                                </td>
                                @if($studentIndex == 0)
                                <td rowspan="{{ $studentCount }}">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle avatar-purple">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <span class="fw-medium">{{ $supervisor->name }}</span>
                                    </div>
                                </td>
                                @endif
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle avatar-gray">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span>{{ $student->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($student->last_three_attendances && $student->last_three_attendances->count() > 0)
                                        @php
                                            $present = $student->last_three_attendances->whereIn('status', ['present', 'late'])->count();
                                            $total = $student->last_three_attendances->count();
                                            $rate = $present / $total;
                                            $colorClass = $rate >= 1 ? 'success' : ($rate >= 0.66 ? 'warning' : 'danger');
                                            $colorHex = $rate >= 1 ? '#10b981' : ($rate >= 0.66 ? '#f59e0b' : '#ef4444');
                                        @endphp
                                        <div class="attendance-indicator">
                                            <div class="attendance-bar">
                                                <div class="attendance-bar-fill" style="width: {{ $rate * 100 }}%; background: {{ $colorHex }};"></div>
                                            </div>
                                            <span class="badge" style="background: {{ $colorHex }}; color: white;">{{ $present }}/{{ $total }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->last_two_recitations && $student->last_two_recitations->count() > 0)
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @foreach($student->last_two_recitations as $recitation)
                                            <div class="recitation-badge" style="background: rgba(102,126,234,0.1); border: 1px solid {{ $recitation->status_color }};">
                                                <span style="color: {{ $recitation->status_color }};">{{ $recitation->type_label }}</span>
                                                <a href="{{ route('admin.sessions.show', $recitation->id) }}" target="_blank" class="text-{{ $recitation->status_color }} ms-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @php $rowIndex++; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div class="divider"></div>

            <div class="form-section-title">
                <i class="fas fa-clipboard-list" style="color: #3b82f6;"></i>
                <span>التكليفات الجديدة</span>
            </div>
            <div id="assignments-container">
                @php $savedAssignments = old('assignments', [['supervisor_id' => '', 'task' => '']]); @endphp
                @foreach($savedAssignments as $i => $assignment)
                <div class="assignment-row-custom" data-index="{{ $i }}">
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
                            <button type="button" class="btn btn-outline-danger w-100 remove-assignment">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-add mt-3" onclick="addAssignment()">
                <i class="fas fa-plus me-2"></i>إضافة تكليف
            </button>

            <div class="divider"></div>

            <div class="form-section-title">
                <i class="fas fa-file-alt" style="color: #06b6d4;"></i>
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

            <div class="divider"></div>

            <div class="d-flex gap-3 justify-content-center">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="fas fa-save me-2"></i>تحديث المحضر
                </button>
                <a href="{{ route('admin.meeting-minutes.show', $meetingMinute->id) }}" class="btn btn-secondary-custom">
                    <i class="fas fa-times me-2"></i>إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addManualAttendee() {
    var container = $('#manual-attendees-container');
    var count = container.find('.manual-attendee-row').length;
    var html = '<div class="manual-attendee-row mb-2">' +
        '<div class="d-flex gap-2 align-items-center">' +
        '<input type="text" name="manual_attendees[' + count + '][name]" class="form-control" placeholder="اسم الحضور">' +
        '<button type="button" class="btn btn-outline-danger btn-sm remove-manual-attendee" style="width: 40px;">' +
        '<i class="fas fa-trash"></i>' +
        '</button>' +
        '</div>' +
        '</div>';
    container.append(html);
}

function addAgendaItem() {
    var container = $('#agenda-container');
    var count = container.find('.agenda-item').length;
    var html = '<div class="col-md-6 agenda-item" data-index="' + count + '">' +
        '<div class="agenda-card p-3 h-100">' +
        '<div class="d-flex align-items-center justify-content-between mb-2">' +
        '<span class="badge-custom agenda-number">' + (count + 1) + '</span>' +
        '<button type="button" class="btn btn-outline-danger btn-sm remove-agenda">' +
        '<i class="fas fa-times"></i>' +
        '</button>' +
        '</div>' +
        '<input type="text" name="agenda[]" class="form-control agenda-input" placeholder="البند ' + (count + 1) + '">' +
        '</div>' +
        '</div>';
    container.append(html);
    renumberAgenda();
}

function renumberAgenda() {
    var idx = 1;
    $('.agenda-item').each(function() {
        $(this).find('.agenda-number').text(idx);
        $(this).find('.agenda-input').attr('placeholder', 'البند ' + idx);
        $(this).attr('data-index', idx - 1);
        idx++;
    });
}

function toggleExclude(btn) {
    var item = $(btn).closest('.supervisor-item');
    var excludeInput = item.find('.exclude-input');
    var nameSpan = item.find('.supervisor-name');

    if (item.hasClass('excluded')) {
        item.removeClass('excluded');
        $(btn).removeClass('btn-success').addClass('btn-outline-danger');
        $(btn).html('<i class="fas fa-times"></i> استبعاد');
        excludeInput.prop('disabled', true);
        nameSpan.css('text-decoration', '').css('color', '');
    } else {
        item.addClass('excluded');
        $(btn).addClass('btn-success').removeClass('btn-outline-danger');
        $(btn).html('<i class="fas fa-check"></i> مُدرج');
        excludeInput.prop('disabled', false);
        nameSpan.css('text-decoration', 'line-through').css('color', '#999');
    }
}

function renumberAssignments() {
    var idx = 0;
    $('#assignments-container .assignment-row-custom').each(function() {
        $(this).attr('data-index', idx);
        $(this).find('select').attr('name', 'assignments[' + idx + '][supervisor_id]');
        $(this).find('input[type="text"]').attr('name', 'assignments[' + idx + '][task]');
        idx++;
    });
}

function addAssignment() {
    var container = $('#assignments-container');
    var count = container.find('.assignment-row-custom').length;
    var supervisors = @json($supervisors);
    var options = '<option value="">-- المشرف --</option>';
    supervisors.forEach(function(s) {
        options += '<option value="' + s.id + '">' + s.name + '</option>';
    });
    var html = '<div class="assignment-row-custom" data-index="' + count + '">' +
        '<div class="row g-3">' +
        '<div class="col-md-4">' +
        '<select name="assignments[' + count + '][supervisor_id]" class="form-select">' + options + '</select>' +
        '</div>' +
        '<div class="col-md-6">' +
        '<input type="text" name="assignments[' + count + '][task]" class="form-control" placeholder="وصف التكليف">' +
        '</div>' +
        '<div class="col-md-2">' +
        '<button type="button" class="btn btn-outline-danger w-100 remove-assignment">' +
        '<i class="fas fa-trash"></i>' +
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
    var badge = select.closest('.task-card').find('.task-status-badge');
    var newStatus = select.value;
    badge.removeClass('bg-warning bg-info bg-success bg-danger').addClass('bg-' + statusColors[newStatus]);
    badge.text(statusLabels[newStatus]);
}

$(document).on('click', '.remove-manual-attendee', function() {
    $(this).closest('.manual-attendee-row').remove();
});

$(document).on('click', '.remove-agenda', function() {
    $(this).closest('.agenda-item').remove();
    renumberAgenda();
});

$(document).on('click', '.remove-assignment', function() {
    $(this).closest('.assignment-row-custom').remove();
    renumberAssignments();
});
</script>
@endpush