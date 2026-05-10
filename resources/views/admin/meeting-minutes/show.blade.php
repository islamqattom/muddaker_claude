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
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-calendar me-2"></i>
                    محضر اجتماع
                </h6>
                <div>
                    @if($meetingMinute->status === 'draft')
                        <form method="POST" action="{{ route('admin.meeting-minutes.approve', $meetingMinute->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check me-1"></i> اعتماد
                            </button>
                        </form>
                        <a href="{{ route('admin.meeting-minutes.edit', $meetingMinute->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> تعديل
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">التاريخ</label>
                        <div class="fw-semibold">{{ $meetingMinute->meeting_date->format('Y/m/d') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">الوقت</label>
                        <div class="fw-semibold">{{ $meetingMinute->meeting_time ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">الحالة</label>
                        <div>
                            <span class="badge bg-{{ $meetingMinute->status_color }}">
                                {{ $meetingMinute->status_label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    الحضور
                </h6>
            </div>
            <div class="card-body">
                @php
                    $attendeesData = $meetingMinute->attendees ?? [];
                    $manualAttendees = $meetingMinute->manual_attendees ?? [];
                    $totalAttendees = (is_array($attendeesData) ? count($attendeesData) : 0) + (is_array($manualAttendees) ? count($manualAttendees) : 0);
                @endphp
                @if($totalAttendees > 0)
                    <ul class="list-unstyled mb-0">
                        @if(is_array($attendeesData))
                            @foreach($attendeesData as $attendee)
                            @if(($attendee['is_present'] ?? false) || ($attendee['is_present'] ?? true))
                            <li class="mb-2">
                                <i class="fas fa-user-check text-success me-2"></i>
                                {{ $attendee['name'] ?? '' }}
                                @if(!empty($attendee['role']))
                                    <span class="text-muted"> - {{ $attendee['role'] === 'admin' ? 'مدير' : 'مشرف' }}</span>
                                @endif
                            </li>
                            @endif
                            @endforeach
                        @endif
                        @if(is_array($manualAttendees))
                            @foreach($manualAttendees as $manual)
                            @if(!empty($manual['name']))
                            <li class="mb-2">
                                <i class="fas fa-user-check text-success me-2"></i>
                                {{ $manual['name'] }}
                                <span class="text-muted"> - إضافة يدوية</span>
                            </li>
                            @endif
                            @endforeach
                        @endif
                    </ul>
                @else
                    <p class="text-muted mb-0">لا يوجد حضور</p>
                @endif
            </div>
        </div>

        @if($meetingMinute->agenda)
            @php
                $agendaData = $meetingMinute->agenda;
                if (is_string($agendaData)) {
                    $agendaData = json_decode($agendaData, true) ?? [];
                }
            @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        جدول الأعمال
                    </h6>
                </div>
                <div class="card-body">
                    @if(is_array($agendaData) && count($agendaData) > 0)
                        <ol class="mb-0 pe-3">
                            @foreach($agendaData as $item)
                            <li class="mb-1">{{ is_array($item) ? ($item['text'] ?? '') : $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-muted mb-0">لا يوجد جدول أعمال</p>
                    @endif
                </div>
            </div>
        @endif

        @if($meetingMinute->agenda_notes)
            @php
                $agendaNotesData = $meetingMinute->agenda_notes;
                if (is_string($agendaNotesData)) {
                    $agendaNotesData = json_decode($agendaNotesData, true) ?: [];
                }
            @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-sticky-note me-2"></i>
                        تفاصيل بنود جدول الأعمال
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($agendaNotesData as $index => $note)
                        @if(!empty($note))
                            @php
                                $agendaData = $meetingMinute->agenda;
                                if (is_string($agendaData)) {
                                    $agendaData = json_decode($agendaData, true) ?: [];
                                }
                                $itemText = isset($agendaData[$index]) ? (is_array($agendaData[$index]) ? ($agendaData[$index]['text'] ?? '') : $agendaData[$index]) : '';
                                $excludedItems = ['المحضر السابق', 'برنامج الدّوام', 'وضع الطّلاب', 'المتابعات'];
                            @endphp
                            @if(!in_array($itemText, $excludedItems))
                            <div class="mb-3">
                                <strong class="text-primary">{{ $itemText }}</strong>
                                <p class="mb-0 mt-1 text-muted">{{ $note }}</p>
                            </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-week me-2"></i>
                    برنامج الدّوام
                </h6>
            </div>
            <div class="card-body p-0">
                @php
                    $programAxes = $meetingMinute->program_axes ?? [];
                    if (is_string($programAxes)) {
                        $programAxes = json_decode($programAxes, true) ?? [];
                    }
                    $defaultAxes = [
                        'التّمهيدية' => '',
                        'المتوسّطة' => '',
                        'المتقدّمة' => '',
                        'التّفسير' => '',
                        'السّيرة' => '',
                        'الفقه' => '',
                        'النّحو' => '',
                        'اليوم الآخر' => ''
                    ];
                    $axes = !empty($programAxes) ? array_merge($defaultAxes, $programAxes) : $defaultAxes;
                @endphp
                <table class="table table-bordered mb-0">
                    <tbody>
                        @foreach($axes as $axisName => $axisDetails)
                        @if($axisName !== '')
                        <tr>
                            <td class="fw-bold text-primary align-middle" style="width: 150px; background-color: #f8f9fa;">
                                {{ $axisName }}
                            </td>
                            <td>
                                <div class="p-2">
                                    <p class="mb-0 text-dark">{{ !empty($axisDetails) ? nl2br(e($axisDetails)) : '<span class="text-muted">...</span>' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($meetingMinute->tasks && $meetingMinute->tasks->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>
                    التكليفات
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>المشرف</th>
                            <th>التكليف</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meetingMinute->tasks as $task)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $task->assignee->name ?? '-' }}</td>
                            <td>{{ $task->title }}</td>
                            <td>
                                <span class="badge bg-{{ $task->status_color }}">{{ $task->status_label }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    معلومات المحضر
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">تاريخ الإنشاء</label>
                    <div class="fw-semibold">{{ $meetingMinute->created_at->format('Y/m/d H:i') }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">المنشئ</label>
                    <div class="fw-semibold">{{ $meetingMinute->creator->name ?? '-' }}</div>
                </div>
                @if($meetingMinute->next_meeting_date)
                <div class="mb-3">
                    <label class="text-muted small">تاريخ الاجتماع القادم</label>
                    <div class="fw-semibold">{{ $meetingMinute->next_meeting_date->format('Y/m/d') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection