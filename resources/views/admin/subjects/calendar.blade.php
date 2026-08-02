@extends('layouts.app')

@section('title', 'تقويم: ' . $subject->name)
@section('page-title', 'تقويم دروس: ' . $subject->name)

@php
    $role = auth()->user()->role;
    $roleLabel = $role === 'admin' ? 'مدير' : 'معلم';
@endphp

@section('role-label', $roleLabel)

@section('sidebar-menu')
    @include($role . '.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.index') }}">المواد الدراسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
    <li class="breadcrumb-item active">التقويم</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
.calendar-page { font-family: 'Tajawal', sans-serif; }
.page-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 24px; flex-wrap: wrap; }
.header-content { display: flex; align-items: center; gap: 16px; flex: 1; }
.subject-color-badge { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; box-shadow: 0 6px 18px rgba(0,0,0,0.15); }
.header-text h4 { color: #182B48; font-size: 22px; font-weight: 800; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 13px; margin: 0; }
.btn { border-radius: 12px; padding: 10px 22px; font-weight: 700; font-family: 'Tajawal', sans-serif; border: none; }

.calendar-container { background: #fff; border-radius: 18px; box-shadow: 0 4px 25px rgba(24, 43, 72, 0.08); padding: 25px; border: 1px solid rgba(24,43,72,0.05); }
.fc { font-family: 'Tajawal', sans-serif; }
.fc .fc-toolbar-title { font-size: 1.4em; color: #182B48; font-weight: 700; }
.fc .fc-button { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); border: none; border-radius: 10px; padding: 10px 18px; font-family: 'Tajawal', sans-serif; font-weight: 600; }
.fc .fc-button:hover { background: linear-gradient(135deg, #2A4570 0%, #182B48 100%); }
.fc .fc-button-primary:not(:disabled).fc-button-active { background: {{ $subject->color }}; }
.fc-daygrid-day-number { font-size: 14px; font-weight: 600; color: #182B48; padding: 8px; }
.fc-daygrid-day.fc-day-today { background: rgba(212, 160, 48, 0.1); }
.fc-event { border: none; border-radius: 8px; padding: 4px 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
.fc-event:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.fc .fc-popover { border-radius: 12px; box-shadow: 0 8px 30px rgba(24,43,72,0.15); }
.fc .fc-popover-header { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); color: #fff; border-radius: 12px 12px 0 0; font-weight: 700; }

.legend { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; padding: 12px 18px; background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%); border-radius: 12px; }
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #5a6c7d; }
.legend-color { width: 18px; height: 18px; border-radius: 5px; }
.legend-pending { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.legend-completed { background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); }
.legend-cancelled { background: linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%); }
.legend-overdue { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); }

.lesson-modal .modal-header { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); color: #fff; }
.lesson-modal .modal-title { color: #fff; font-weight: 700; }
.lesson-modal .btn-close-white { filter: brightness(0) invert(1); }
.detail-label { color: #182B48; font-weight: 700; margin-bottom: 4px; display: block; font-size: 13px; }
.detail-value { color: #5a6c7d; font-size: 14px; line-height: 1.6; }
.status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.status-badge.pending { background: rgba(243,156,18,0.15); color: #f39c12; }
.status-badge.completed { background: rgba(39,174,96,0.15); color: #27ae60; }
.status-badge.cancelled { background: rgba(127,140,141,0.15); color: #7f8c8d; }
.status-badge.overdue { background: rgba(192,57,43,0.15); color: #c0392b; }

@media (max-width: 768px) {
    .fc .fc-toolbar.fc-header-toolbar { flex-direction: column; gap: 10px; }
    .fc .fc-toolbar-title { font-size: 1.1em !important; }
    .fc .fc-button { padding: 6px 10px !important; font-size: 12px !important; }
}
</style>
@endpush

@section('content')
<div class="calendar-page">
    <div class="page-header">
        <div class="header-content">
            <div class="subject-color-badge" style="background: {{ $subject->color }};">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="header-text">
                <h4>تقويم: {{ $subject->name }}</h4>
                <p>عرض الدروس زمنياً مع إمكانية تعديل حالتها</p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route($role . '.subjects.show', $subject->id) }}" class="btn btn-secondary" style="background: #f0f4f8; color: #182B48;">
                <i class="fas fa-list ms-1"></i> القائمة
            </a>
            <a href="{{ route($role . '.lessons.create', $subject->id) }}" class="btn" style="background: {{ $subject->color }}; color: #fff;">
                <i class="fas fa-plus-circle ms-1"></i> إضافة درس
            </a>
        </div>
    </div>

    <div class="legend">
        <div class="legend-item"><div class="legend-color legend-pending"></div> لم يتم</div>
        <div class="legend-item"><div class="legend-color legend-completed"></div> تم</div>
        <div class="legend-item"><div class="legend-color legend-cancelled"></div> ملغى</div>
        <div class="legend-item"><div class="legend-color legend-overdue"></div> متأخر</div>
    </div>

    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</div>

<div class="modal fade lesson-modal" id="lessonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book-open ms-2"></i> تفاصيل الدرس</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="lessonModalBody">
            </div>
            <div class="modal-footer" id="lessonModalFooter" style="background: #f8f9fa; border-top: 1px solid #e9ecef;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
const events = @json($events);
const subjectColor = @json($subject->color);

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ar',
        direction: 'rtl',
        firstDay: 0,
        height: 720,
        headerToolbar: {
            right: 'dayGridMonth,timeGridWeek,listMonth',
            center: 'title',
            left: 'prev,next today'
        },
        buttonText: {
            today: 'اليوم',
            month: 'شهر',
            week: 'أسبوع',
            day: 'يوم',
            list: 'قائمة'
        },
        events: events,
        eventClick: function(info) {
            showLessonDetails(info.event.extendedProps, info.event.id);
        },
        dayMaxEvents: 3,
    });
    calendar.render();
});

const rolePrefix = @json($role);

function showLessonDetails(props, lessonId) {
    const statusBadge = `<span class="status-badge ${props.isOverdue ? 'overdue' : props.status}">${props.isOverdue ? 'متأخر' : props.statusLabel}</span>`;
    const timeRange = (props.startTime ? props.startTime : '-') + (props.endTime ? ' - ' + props.endTime : '');

    let html = `
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h5 style="color: #182B48; font-weight: 800; margin: 0;">${escapeHtml(props.title)}</h5>
            ${statusBadge}
        </div>
        <div class="mb-3">
            <span class="detail-label">المادة</span>
            <div class="detail-value">
                <span style="display: inline-block; width: 12px; height: 12px; background: ${props.subjectColor}; border-radius: 4px; margin-left: 6px;"></span>
                ${escapeHtml(props.subjectName)}
            </div>
        </div>
        <div class="row g-3">
            <div class="col-6">
                <span class="detail-label">التاريخ</span>
                <div class="detail-value">${props.dayName} - ${props.lessonDate}</div>
            </div>
            <div class="col-6">
                <span class="detail-label">الوقت</span>
                <div class="detail-value">${timeRange}</div>
            </div>
        </div>
    `;
    if (props.content) {
        html += `
            <div class="mt-3">
                <span class="detail-label">محتوى الدرس</span>
                <div class="detail-value" style="background: #f8f9fa; padding: 12px; border-radius: 10px; border-right: 3px solid ${props.subjectColor};">${escapeHtml(props.content).replace(/\n/g, '<br>')}</div>
            </div>
        `;
    }
    if (props.notes) {
        html += `
            <div class="mt-3">
                <span class="detail-label">ملاحظات</span>
                <div class="detail-value" style="background: rgba(212,160,48,0.06); padding: 12px; border-radius: 10px;">${escapeHtml(props.notes).replace(/\n/g, '<br>')}</div>
            </div>
        `;
    }

    document.getElementById('lessonModalBody').innerHTML = html;

    const subjectId = props.subjectId;
    let footer = `
        <a href="/${rolePrefix}/subjects/${subjectId}/lessons/${lessonId}" class="btn" style="background: #3498db; color: #fff; border-radius: 10px;">
            <i class="fas fa-eye ms-1"></i> عرض كامل
        </a>
        <a href="/${rolePrefix}/subjects/${subjectId}/lessons/${lessonId}/edit" class="btn" style="background: #8e44ad; color: #fff; border-radius: 10px;">
            <i class="fas fa-edit ms-1"></i> تعديل
        </a>
    `;
    if (props.status !== 'completed') {
        footer += `<button type="button" class="btn" style="background: #27ae60; color: #fff; border-radius: 10px;" onclick="setLessonStatus(${subjectId}, ${lessonId}, 'completed')">
            <i class="fas fa-check ms-1"></i> تم
        </button>`;
    } else {
        footer += `<button type="button" class="btn" style="background: #f39c12; color: #fff; border-radius: 10px;" onclick="setLessonStatus(${subjectId}, ${lessonId}, 'pending')">
            <i class="fas fa-undo ms-1"></i> إعادة
        </button>`;
    }
    document.getElementById('lessonModalFooter').innerHTML = `
        <button type="button" class="btn" style="background: #f0f4f8; color: #182B48; border-radius: 10px;" data-bs-dismiss="modal">إغلاق</button>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">${footer}</div>
    `;

    new bootstrap.Modal(document.getElementById('lessonModal')).show();
}

function setLessonStatus(subjectId, lessonId, status) {
    const url = `/${rolePrefix}/subjects/${subjectId}/lessons/${lessonId}/status`;
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 800);
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>
@endpush
