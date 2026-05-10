@extends('layouts.app')
@section('title', 'عرض محضر اجتماع')
@section('page-title', 'عرض محضر اجتماع')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.meeting-minutes.index') }}">محاضر الاجتماعات</a></li>
    <li class="breadcrumb-item active">عرض المحضر</li>
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

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 18px; display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }
.header-actions { display: flex; gap: 12px; flex-wrap: wrap; }

.btn-action {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 22px; border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    text-decoration: none;
    font-family: 'Tajawal', sans-serif;
}
.btn-approve { background: linear-gradient(135deg, var(--success) 0%, #219a52 100%); color: #fff; }
.btn-approve:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39,174,96,0.35); }
.btn-edit { background: linear-gradient(135deg, var(--warning) 0%, #e67e22 100%); color: #fff; }
.btn-edit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(243,156,18,0.35); }
.btn-back { background: #fff; color: var(--primary); border: 2px solid #e9ecef; }
.btn-back:hover { border-color: var(--primary); }
.btn-back i { color: var(--gold); }

.info-card {
    background: #fff; border-radius: 20px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 25px;
}
.info-header {
    display: flex; align-items: center; gap: 22px;
    padding: 28px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    position: relative;
}
.info-header::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.info-icon {
    width: 65px; height: 65px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 4px 15px rgba(212,160,48,0.35);
}
.info-content h5 { color: #fff; font-size: 20px; font-weight: 700; margin: 0 0 12px 0; }
.meeting-meta { display: flex; gap: 12px; flex-wrap: wrap; }
.meta-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 20px;
    font-size: 14px; font-weight: 500;
    background: rgba(255,255,255,0.15);
    color: #fff;
    backdrop-filter: blur(5px);
}
.meta-badge i { color: var(--gold); }
.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 20px;
    font-size: 13px; font-weight: 600;
}
.status-badge.draft { background: rgba(243,156,18,0.25); color: #f1c40f; border: 1px solid rgba(243,156,18,0.3); }
.status-badge.approved { background: rgba(39,174,96,0.25); color: #2ecc71; border: 1px solid rgba(39,174,96,0.3); }
.status-badge.archived { background: rgba(149,165,166,0.25); color: #bdc3c7; border: 1px solid rgba(149,165,166,0.3); }
.status-badge i { font-size: 12px; }

.section-card {
    background: #fff; border-radius: 20px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden; margin-bottom: 25px;
    transition: all 0.3s ease;
}
.section-card:hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(24,43,72,0.1); }
.section-header {
    display: flex; align-items: center; gap: 12px;
    padding: 20px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.section-header i { color: var(--gold); font-size: 20px; }
.section-body { padding: 25px 28px; }

.attendance-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; }
.attendee-item {
    display: flex; align-items: center; gap: 14px;
    padding: 18px; background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-radius: 14px; border: 1px solid rgba(24,43,72,0.05);
    transition: all 0.3s ease;
}
.attendee-item:hover { transform: translateX(-4px); border-color: var(--gold); }
.attendee-avatar {
    width: 50px; height: 50px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 14px; display: flex; align-items: center; justify-content: center;
    color: var(--gold); font-size: 20px;
    flex-shrink: 0;
}
.attendee-avatar.manual { background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%); }
.attendee-info { flex: 1; }
.attendee-name { font-weight: 700; color: var(--primary); display: block; font-size: 15px; }
.attendee-role { font-size: 12px; color: #95a5a6; margin-top: 2px; }
.present-icon { color: var(--success); font-size: 20px; }

.empty-section { text-align: center; padding: 40px; color: #95a5a6; }
.empty-section i { font-size: 50px; margin-bottom: 15px; color: var(--gold); opacity: 0.4; }

.agenda-list { margin: 0; padding-right: 0; list-style: none; }
.agenda-item {
    padding: 16px 20px; margin-bottom: 12px;
    background: linear-gradient(135deg, rgba(212,160,48,0.06) 0%, rgba(180,128,44,0.08) 100%);
    border-radius: 12px; color: var(--primary);
    font-weight: 600; font-size: 15px;
    border-right: 4px solid var(--gold);
    transition: all 0.3s ease;
}
.agenda-item:hover { transform: translateX(-3px); background: linear-gradient(135deg, rgba(212,160,48,0.1) 0%, rgba(180,128,44,0.12) 100%); }

.note-card {
    background: #fff; border-radius: 14px;
    border: 2px solid rgba(212,160,48,0.1);
    overflow: hidden; margin-bottom: 15px;
}
.note-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 22px;
    background: linear-gradient(135deg, rgba(212,160,48,0.1) 0%, rgba(180,128,44,0.12) 100%);
    border-bottom: 1px solid rgba(212,160,48,0.1);
    font-weight: 700; color: var(--primary);
}
.note-header i { color: var(--gold); font-size: 18px; }
.note-body { padding: 18px 22px; color: #5a6c7d; line-height: 1.8; font-size: 15px; }

.program-table { width: 100%; border-collapse: collapse; }
.program-table td { padding: 16px 22px; border-bottom: 1px solid rgba(24,43,72,0.05); vertical-align: middle; }
.axis-name {
    width: 160px; background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    font-weight: 700; color: var(--primary);
    font-size: 15px;
}
.axis-details { color: #5a6c7d; font-size: 14px; line-height: 1.7; }

.tasks-table { width: 100%; border-collapse: collapse; }
.tasks-table thead { background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%); }
.tasks-table th {
    padding: 16px 22px;
    font-size: 12px; font-weight: 700;
    color: var(--primary);
    text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 2px solid rgba(24,43,72,0.08);
}
.tasks-table td {
    padding: 16px 22px;
    font-size: 14px; color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.05);
    vertical-align: middle;
}
.tasks-table tbody tr { transition: all 0.3s ease; }
.tasks-table tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.04) 0%, rgba(180,128,44,0.06) 100%); }
.index-cell { color: var(--gold); font-weight: 700; }

.status-badge-task {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.status-badge-task.pending { background: rgba(243,156,18,0.15); color: var(--warning); }
.status-badge-task.in_progress { background: rgba(52,152,219,0.15); color: var(--info); }
.status-badge-task.completed { background: rgba(39,174,96,0.15); color: var(--success); }
.status-badge-task.cancelled { background: rgba(192,57,43,0.15); color: var(--danger); }

.sidebar-card {
    background: #fff; border-radius: 20px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    position: sticky;
    top: 20px;
}
.sidebar-header {
    display: flex; align-items: center; gap: 12px;
    padding: 20px 25px;
    background: linear-gradient(135deg, rgba(24,43,72,0.05) 0%, rgba(26,51,86,0.08) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.sidebar-header i { color: var(--gold); font-size: 18px; }
.sidebar-body { padding: 22px 25px; }
.info-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 0; border-bottom: 1px solid rgba(24,43,72,0.05);
}
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 13px; color: #95a5a6; }
.info-value { font-size: 14px; font-weight: 700; color: var(--primary); }
.info-value i { color: var(--gold); margin-left: 5px; }

@media (max-width: 992px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-file-signature"></i>
        </div>
        <div class="header-text">
            <h4>عرض محضر اجتماع</h4>
            <p>تفاصيل ومحتوى محضر جلسة جمعية تحفيظ القرآن الكريم</p>
        </div>
    </div>
    <div class="header-actions">
        @if($meetingMinute->status === 'draft')
            <form method="POST" action="{{ route('admin.meeting-minutes.approve', $meetingMinute->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn-action btn-approve">
                    <i class="fas fa-check-circle"></i>اعتماد
                </button>
            </form>
            <a href="{{ route('admin.meeting-minutes.edit', $meetingMinute->id) }}" class="btn-action btn-edit">
                <i class="fas fa-edit"></i>تعديل
            </a>
        @endif
        <a href="{{ route('admin.meeting-minutes.index') }}" class="btn-action btn-back">
            <i class="fas fa-arrow-right"></i>العودة
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="info-card">
            <div class="info-header">
                <div class="info-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="info-content">
                    <h5>معلومات الاجتماع</h5>
                    <div class="meeting-meta">
                        <span class="meta-badge date">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $meetingMinute->meeting_date->format('Y/m/d') }}
                        </span>
                        @if($meetingMinute->meeting_time)
                        <span class="meta-badge time">
                            <i class="fas fa-clock"></i>
                            {{ $meetingMinute->meeting_time }}
                        </span>
                        @endif
                        <span class="status-badge {{ $meetingMinute->status }}">
                            @if($meetingMinute->status === 'draft')
                                <i class="fas fa-pen"></i>مسودة
                            @elseif($meetingMinute->status === 'approved')
                                <i class="fas fa-check-circle"></i>معتمد
                            @else
                                <i class="fas fa-archive"></i>مؤرشف
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @php
            $attendeesData = $meetingMinute->attendees ?? [];
            $manualAttendees = $meetingMinute->manual_attendees ?? [];
            $totalAttendees = (is_array($attendeesData) ? count(array_filter($attendeesData, fn($a) => $a['is_present'] ?? false)) : 0) + (is_array($manualAttendees) ? count(array_filter($manualAttendees, fn($a) => !empty($a['name']))) : 0);
        @endphp
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-users"></i>
                <span>الحضور ({{ $totalAttendees }})</span>
            </div>
            <div class="section-body">
                @if($totalAttendees > 0)
                    <div class="attendance-grid">
                        @if(is_array($attendeesData))
                            @foreach($attendeesData as $attendee)
                            @if($attendee['is_present'] ?? false)
                            <div class="attendee-item">
                                <div class="attendee-avatar">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="attendee-info">
                                    <span class="attendee-name">{{ $attendee['name'] ?? '' }}</span>
                                    <span class="attendee-role">
                                        {{ ($attendee['role'] ?? '') === 'admin' ? 'مدير' : 'مشرف' }}
                                    </span>
                                </div>
                                <i class="fas fa-check-circle present-icon"></i>
                            </div>
                            @endif
                            @endforeach
                        @endif
                        @if(is_array($manualAttendees))
                            @foreach($manualAttendees as $manual)
                            @if(!empty($manual['name']))
                            <div class="attendee-item">
                                <div class="attendee-avatar manual">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="attendee-info">
                                    <span class="attendee-name">{{ $manual['name'] }}</span>
                                    <span class="attendee-role">إضافي</span>
                                </div>
                                <i class="fas fa-check-circle present-icon"></i>
                            </div>
                            @endif
                            @endforeach
                        @endif
                    </div>
                @else
                    <div class="empty-section">
                        <i class="fas fa-users-slash"></i>
                        <p>لا يوجد حضور</p>
                    </div>
                @endif
            </div>
        </div>

        @php
            $agendaData = $meetingMinute->agenda;
            if (is_string($agendaData)) { $agendaData = json_decode($agendaData, true) ?? []; }
        @endphp
        @if(is_array($agendaData) && count($agendaData) > 0)
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-list-ul"></i>
                <span>جدول الأعمال</span>
            </div>
            <div class="section-body">
                <ol class="agenda-list" style="counter-reset: agenda;">
                    @foreach($agendaData as $item)
                    <li class="agenda-item">{{ is_array($item) ? ($item['text'] ?? '') : $item }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
        @endif

        @if($meetingMinute->agenda_notes)
            @php
                $agendaNotesData = $meetingMinute->agenda_notes;
                if (is_string($agendaNotesData)) { $agendaNotesData = json_decode($agendaNotesData, true) ?: []; }
            @endphp
            @php
                $excludedItems = ['المحضر السابق', 'برنامج الدّوام', 'وضع الطّلاب', 'المتابعات'];
            @endphp
            @foreach($agendaNotesData as $index => $note)
                @if(!empty($note) && isset($agendaData[$index]))
                    @php
                        $itemText = is_array($agendaData[$index]) ? ($agendaData[$index]['text'] ?? '') : $agendaData[$index];
                    @endphp
                    @if(!in_array($itemText, $excludedItems))
                    <div class="note-card">
                        <div class="note-header">
                            <i class="fas fa-sticky-note"></i>
                            <span>{{ $itemText }}</span>
                        </div>
                        <div class="note-body">
                            {{ $note }}
                        </div>
                    </div>
                    @endif
                @endif
            @endforeach
        @endif

        @php
            $programAxesNames = $meetingMinute->program_axes_names ?? [];
            if (is_string($programAxesNames)) { $programAxesNames = json_decode($programAxesNames, true) ?? []; }
            $programAxesDetails = $meetingMinute->program_axes ?? [];
            if (is_string($programAxesDetails)) { $programAxesDetails = json_decode($programAxesDetails, true) ?? []; }
            $axes = [];
            $maxLen = max(count($programAxesNames), count($programAxesDetails));
            for ($i = 0; $i < $maxLen; $i++) {
                $name = $programAxesNames[$i] ?? '';
                $details = $programAxesDetails[$i] ?? '';
                if (!empty($name)) {
                    $axes[$name] = $details;
                }
            }
        @endphp
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-calendar-week"></i>
                <span>برنامج الدّوام</span>
            </div>
            <div class="section-body" style="padding: 0;">
                <table class="program-table">
                    <tbody>
                        @foreach($axes as $axisName => $axisDetails)
                        <tr>
                            <td class="axis-name">{{ $axisName }}</td>
                            <td class="axis-details">
                                @if(!empty($axisDetails))
                                    {{ nl2br(e($axisDetails)) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($meetingMinute->tasks && $meetingMinute->tasks->count() > 0)
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-tasks"></i>
                <span>التكليفات ({{ $meetingMinute->tasks->count() }})</span>
            </div>
            <div class="section-body" style="padding: 0;">
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المشرف</th>
                            <th>التكليف</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meetingMinute->tasks as $task)
                        <tr>
                            <td class="index-cell">{{ $loop->iteration }}</td>
                            <td>{{ $task->assignee->name ?? '-' }}</td>
                            <td>{{ $task->title }}</td>
                            <td>
                                <span class="status-badge-task {{ $task->status }}">
                                    @if($task->status === 'pending')
                                        <i class="fas fa-clock"></i>
                                    @elseif($task->status === 'in_progress')
                                        <i class="fas fa-spinner"></i>
                                    @elseif($task->status === 'completed')
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-times"></i>
                                    @endif
                                    {{ $task->status_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <i class="fas fa-info-circle"></i>
                <span>معلومات المحضر</span>
            </div>
            <div class="sidebar-body">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar-plus"></i>تاريخ الإنشاء</span>
                    <span class="info-value">{{ $meetingMinute->created_at->format('Y/m/d H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user"></i>المنشئ</span>
                    <span class="info-value">{{ $meetingMinute->creator->name ?? '-' }}</span>
                </div>
                @if($meetingMinute->next_meeting_date)
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar-plus"></i>الاجتماع القادم</span>
                    <span class="info-value">{{ $meetingMinute->next_meeting_date->format('Y/m/d') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection