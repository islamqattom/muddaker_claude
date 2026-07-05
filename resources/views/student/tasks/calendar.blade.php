@extends('layouts.app')

@section('title', 'تقويم المهام')
@section('page-title', 'تقويم المهام')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">تقويم المهام</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
.calendar-page { font-family: 'Tajawal', sans-serif; }
.page-header {
    display: flex; justify-content: space-between; align-items: center;
    gap: 20px; margin-bottom: 30px; flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    border-radius: 18px; display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 8px 25px rgba(39,174,96,0.25);
}
.header-text h4 { color: #182B48; font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }
.calendar-container {
    background: #fff; border-radius: 18px;
    box-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
    padding: 25px; border: 1px solid rgba(24,43,72,0.05);
}
.fc { font-family: 'Tajawal', sans-serif; }
.fc .fc-toolbar-title { font-size: 1.4em; color: #182B48; font-weight: 700; }
.fc .fc-button {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    border: none; border-radius: 10px;
    padding: 10px 20px; font-family: 'Tajawal', sans-serif; font-weight: 600;
}
.fc .fc-button:hover { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); }
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%);
}
.fc .fc-daygrid-day-number { font-size: 14px; font-weight: 600; color: #182B48; padding: 8px; }
.fc .fc-daygrid-day.fc-day-today { background: rgba(46, 204, 113, 0.1); }
.fc-event {
    border: none; border-radius: 8px; padding: 4px 8px;
    font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
}
.fc-event:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.fc-event-task-pending { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: #fff; }
.fc-event-task-in_progress { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #fff; }
.fc-event-task-partially_done { background: linear-gradient(135deg, #8e44ad 0%, #732d91 100%); color: #fff; }
.fc-event-task-completed { background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); color: #fff; }
.fc-event-task-overdue { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); color: #fff; }
.task-modal .modal-header {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: #fff;
}
.task-modal .modal-title { color: #fff; font-weight: 700; }
.task-modal .btn-close-white { filter: brightness(0) invert(1); }
.form-control, .form-select {
    border-radius: 12px; border: 2px solid #e9ecef;
    padding: 12px 16px; transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
}
.form-control:focus, .form-select:focus {
    border-color: #27ae60;
    box-shadow: 0 0 0 4px rgba(39,174,96,0.12);
}
.legend-container {
    display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;
    padding: 15px 20px;
    background: linear-gradient(135deg, rgba(39,174,96,0.04) 0%, rgba(46,204,113,0.06) 100%);
    border-radius: 12px;
}
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #5a6c7d; }
.legend-color { width: 20px; height: 20px; border-radius: 6px; }
.legend-pending { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.legend-in_progress { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.legend-partially_done { background: linear-gradient(135deg, #8e44ad 0%, #732d91 100%); }
.legend-completed { background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); }
.legend-overdue { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); }
.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.status-badge.pending { background: rgba(243,156,18,0.15); color: #f39c12; }
.status-badge.in_progress { background: rgba(52,152,219,0.15); color: #3498db; }
.status-badge.partially_done { background: rgba(142,68,173,0.15); color: #8e44ad; }
.status-badge.completed { background: rgba(39,174,96,0.15); color: #27ae60; }
.status-badge.cancelled { background: rgba(192,57,43,0.15); color: #c0392b; }
.status-badge.overdue { background: rgba(192,57,43,0.15); color: #c0392b; }
.detail-label { color: #182B48; font-weight: 700; margin-bottom: 4px; display: block; }
.time-hint { font-size: 12px; color: #7f8c8d; margin-top: 4px; }
.status-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.status-actions .btn { padding: 8px 14px; font-size: 13px; flex: 1; min-width: 110px; }
.btn-status-pending { background: rgba(243,156,18,0.15); color: #f39c12; border: 1px solid rgba(243,156,18,0.3); }
.btn-status-pending:hover { background: #f39c12; color: #fff; }
.btn-status-in_progress { background: rgba(52,152,219,0.15); color: #3498db; border: 1px solid rgba(52,152,219,0.3); }
.btn-status-in_progress:hover { background: #3498db; color: #fff; }
.btn-status-partially_done { background: rgba(142,68,173,0.15); color: #8e44ad; border: 1px solid rgba(142,68,173,0.3); }
.btn-status-partially_done:hover { background: #8e44ad; color: #fff; }
.btn-status-completed { background: rgba(39,174,96,0.15); color: #27ae60; border: 1px solid rgba(39,174,96,0.3); }
.btn-status-completed:hover { background: #27ae60; color: #fff; }

/* ============== RESPONSIVE STYLES ============== */

/* Tablets and small desktops */
@media (max-width: 992px) {
    .page-header { gap: 15px; margin-bottom: 20px; }
    .header-icon { width: 60px; height: 60px; font-size: 26px; border-radius: 16px; }
    .header-text h4 { font-size: 20px; }
    .calendar-container { padding: 15px; border-radius: 14px; }
    .legend-container { gap: 15px; padding: 12px 15px; }
    .legend-item { font-size: 12px; }
    .legend-color { width: 18px; height: 18px; }
}

/* Tablets portrait and large phones */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .page-header .btn { width: 100%; }
    .header-content { gap: 15px; }
    .header-icon { width: 55px; height: 55px; font-size: 24px; border-radius: 14px; }
    .header-text h4 { font-size: 18px; }
    .header-text p { font-size: 13px; }
    .legend-container {
        gap: 10px;
        padding: 10px 12px;
        justify-content: center;
    }
    .legend-item { font-size: 11px; }
    .legend-color { width: 16px; height: 16px; border-radius: 5px; }
    .calendar-container { padding: 10px; border-radius: 12px; }

    /* FullCalendar mobile adjustments */
    .fc .fc-toolbar.fc-header-toolbar {
        flex-direction: column;
        gap: 10px;
    }
    .fc .fc-toolbar-chunk { display: flex; justify-content: center; flex-wrap: wrap; gap: 5px; }
    .fc .fc-toolbar-title { font-size: 1.1em !important; }
    .fc .fc-button { padding: 6px 10px !important; font-size: 12px !important; }
    .fc .fc-button-group { flex-wrap: wrap; }
    .fc .fc-toolbar.fc-footer-toolbar { margin-top: 10px; }

    .fc .fc-daygrid-day-number { font-size: 11px; padding: 4px; }
    .fc-col-header-cell-cushion { font-size: 11px; padding: 6px 4px; }
    .fc-event { font-size: 10px !important; padding: 2px 4px !important; }

    /* Form checkbox touch-friendly on mobile */
    .form-check { padding-right: 1.75em; padding-left: 0; }
    .form-check-input { margin-right: -1.75em; width: 1.25em; height: 1.25em; cursor: pointer; }

    /* Modal mobile */
    .modal-dialog { margin: 0.5rem; max-width: calc(100% - 1rem); }
    .modal-content { border-radius: 12px; }
    .task-modal .modal-header { padding: 12px 15px; }
    .task-modal .modal-title { font-size: 15px; }
    .modal-body { padding: 15px; }
    .modal-footer { padding: 10px 15px; flex-wrap: wrap; gap: 8px; }
    .modal-footer .btn { flex: 1; min-width: 100px; }

    .time-hint { font-size: 11px; }

    /* Form fields stack on mobile */
    .modal-body .row > [class*="col-"] { margin-bottom: 0; }
}

/* Small phones */
@media (max-width: 480px) {
    .header-text h4 { font-size: 16px; }
    .header-text p { font-size: 12px; }
    .header-icon { width: 50px; height: 50px; font-size: 22px; }

    .legend-container { gap: 8px; padding: 8px 10px; }
    .legend-item { font-size: 10px; gap: 5px; }
    .legend-color { width: 14px; height: 14px; }

    .calendar-container { padding: 8px; }
    .fc .fc-toolbar-title { font-size: 1em !important; }
    .fc .fc-button { padding: 5px 8px !important; font-size: 11px !important; }

    .modal-dialog { margin: 0.25rem; max-width: calc(100% - 0.5rem); }
    .modal-body { padding: 12px; }
    .modal-header, .modal-footer { padding: 10px 12px; }
    .task-modal .modal-title { font-size: 14px; }

    .form-control, .form-select { padding: 10px 12px; font-size: 14px; }
    .form-label { font-size: 13px; }

    .status-badge { font-size: 11px; padding: 4px 10px; }
    .detail-label { font-size: 12px; }

    .status-actions { flex-direction: column; }
    .status-actions .btn { width: 100%; min-width: auto; }
}

/* Landscape phone / very small heights */
@media (max-height: 500px) and (orientation: landscape) {
    .modal-dialog { max-height: 95vh; }
    .modal-body { max-height: 60vh; overflow-y: auto; }
}

/* ============== LIST VIEW STYLES (REDESIGNED) ============== */
.fc-list { background: transparent !important; border: none !important; }

/* Day header */
.fc-list-day {
    background: transparent !important;
    border-radius: 16px !important;
    box-shadow: none !important;
    margin-bottom: 18px !important;
    overflow: visible !important;
}
.fc-list-day-cushion {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%) !important;
    color: #fff !important;
    font-weight: 700;
    padding: 14px 20px !important;
    border-radius: 14px !important;
    font-size: 15px;
    box-shadow: 0 8px 20px rgba(39,174,96,0.18);
    display: flex !important;
    align-items: center !important;
    gap: 14px;
    position: relative;
    overflow: hidden;
}
.fc-list-day-cushion::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}
.fc-list-day-cushion .fc-list-day-text {
    color: #fff !important;
    text-decoration: none !important;
    font-size: 15px;
    font-weight: 700;
    flex: 1;
    order: 2;
}
.fc-list-day-cushion .fc-list-day-number {
    background: #d4a030 !important;
    color: #182B48 !important;
    text-decoration: none !important;
    font-weight: 800;
    font-size: 18px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    float: none !important;
    margin: 0 !important;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    order: 1;
    flex-shrink: 0;
}
.fc-list-day-cushion .fc-list-day-side-text {
    color: rgba(255,255,255,0.7) !important;
    font-size: 12px;
    font-weight: 500;
    order: 3;
}

/* Empty day styling */
.fc-list-day:not(.has-events) .fc-list-day-cushion {
    background: #f8f9fa !important;
    color: #7f8c8d !important;
    box-shadow: none;
    border: 1px dashed #dee2e6;
}
.fc-list-day:not(.has-events) .fc-list-day-cushion::after { display: none; }
.fc-list-day:not(.has-events) .fc-list-day-text {
    color: #7f8c8d !important;
    font-weight: 500;
}
.fc-list-day:not(.has-events) .fc-list-day-number {
    background: #dee2e6 !important;
    color: #7f8c8d !important;
}

/* Task card */
.fc-list-event {
    background: #fff !important;
    border: 1px solid #f0f4f8 !important;
    padding: 0 !important;
    margin: 10px 0 0 0 !important;
    border-radius: 14px !important;
    box-shadow: 0 2px 12px rgba(24,43,72,0.05) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    display: flex !important;
    align-items: stretch;
    gap: 0;
}
.fc-list-event::before {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--event-color, #27ae60);
    transition: width 0.3s ease;
}
.fc-list-event:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(39,174,96,0.12) !important;
    border-color: rgba(39,174,96,0.2) !important;
}
.fc-list-event:hover::before { width: 6px; }
.fc-list-event.fc-event-task-completed { opacity: 0.85; }
.fc-list-event.fc-event-task-completed .list-task-title { text-decoration: line-through; color: #7f8c8d; }

/* Time column */
.fc-list-event-time {
    font-weight: 600;
    color: #182B48;
    font-size: 13px;
    padding: 18px 16px 18px 12px !important;
    min-width: 130px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    border-left: 1px solid #f0f4f8;
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    flex-shrink: 0;
}
.list-time-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(39,174,96,0.12);
    color: #1e8449;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    line-height: 1.2;
}
.list-time-chip i { color: #27ae60; font-size: 11px; }
.list-time-chip.all-day {
    background: rgba(212,160,48,0.15);
    color: #b4802c;
}
.list-time-chip.all-day i { color: #d4a030; }

/* Hide the default graphic */
.fc-list-event-graphic { display: none !important; }

/* Title/content */
.fc-list-event-title {
    padding: 16px 18px !important;
    font-size: 14px;
    font-weight: 500;
    color: #2c3e50;
    display: flex !important;
    align-items: center;
    gap: 12px;
    flex: 1;
    flex-wrap: wrap;
}
.fc-list-event-title a { display: none !important; }

.list-task-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    width: 100%;
    flex-wrap: wrap;
}
.list-task-main { flex: 1; min-width: 0; }
.list-task-title {
    display: block;
    font-size: 15px;
    font-weight: 700;
    color: #182B48;
    line-height: 1.4;
    margin-bottom: 4px;
    word-break: break-word;
}
.list-task-desc {
    display: block;
    font-size: 12px;
    color: #7f8c8d;
    line-height: 1.4;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
}
.list-task-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
}
.list-assignee-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(39,174,96,0.1);
    color: #1e8449;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.list-assignee-chip i { color: #27ae60; font-size: 11px; }
.list-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
}
.list-status-badge::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}
.list-status-badge.pending { background: rgba(243,156,18,0.15); color: #d68910; }
.list-status-badge.in_progress { background: rgba(52,152,219,0.15); color: #2874a6; }
.list-status-badge.partially_done { background: rgba(142,68,173,0.15); color: #6c3483; }
.list-status-badge.completed { background: rgba(39,174,96,0.15); color: #1e8449; }
.list-status-badge.cancelled { background: rgba(127,140,141,0.15); color: #566573; }
.list-status-badge.overdue {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: #fff;
    animation: pulseBadge 1.8s infinite;
}
.list-overdue-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #c0392b;
    color: #fff;
    font-size: 10px;
    padding: 4px 9px;
    border-radius: 12px;
    font-weight: 700;
    animation: pulseBadge 1.8s infinite;
}
.list-overdue-badge i { font-size: 9px; }
.list-arrow {
    color: #bdc3c7;
    font-size: 14px;
    transition: all 0.3s ease;
    margin-right: 4px;
}
.fc-list-event:hover .list-arrow { color: #27ae60; transform: translateX(-3px); }

@keyframes pulseBadge {
    0%, 100% { box-shadow: 0 0 0 0 rgba(192,57,43,0.5); }
    50% { box-shadow: 0 0 0 6px rgba(192,57,43,0); }
}

/* Empty list state */
.fc-list-empty {
    background: #fff;
    padding: 60px 20px;
    text-align: center;
    color: #7f8c8d;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(24,43,72,0.05);
    border: 2px dashed #e9ecef;
}
.fc-list-empty-cushion { font-size: 15px; font-weight: 500; }
.fc-list-empty-cushion .empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 16px;
    background: linear-gradient(135deg, rgba(39,174,96,0.1), rgba(46,204,113,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #27ae60;
}

/* RTL */
.fc[dir="rtl"] .fc-list-event-time {
    border-left: 1px solid #f0f4f8;
    border-right: none;
    padding: 18px 12px 18px 16px !important;
}
.fc[dir="rtl"] .fc-list-event::before {
    right: auto;
    left: 0;
}
.fc[dir="rtl"] .fc-list-event:hover { transform: translateY(-2px); }
.fc[dir="rtl"] .list-arrow { transform: scaleX(-1); }
.fc[dir="rtl"] .fc-list-event:hover .list-arrow { transform: scaleX(-1) translateX(-3px); }

@media (max-width: 768px) {
    .fc-list-day-cushion { padding: 12px 14px !important; gap: 10px; }
    .fc-list-day-cushion .fc-list-day-number { width: 36px; height: 36px; font-size: 14px; }
    .fc-list-day-cushion .fc-list-day-text { font-size: 13px; }
    .fc-list-event { flex-direction: column !important; }
    .fc-list-event-time {
        border-left: none !important;
        border-bottom: 1px solid #f0f4f8;
        padding: 10px 14px !important;
        min-width: 100%;
        justify-content: flex-start;
        background: #f8f9fa;
    }
    .list-task-content { flex-direction: column; align-items: flex-start; }
    .list-task-meta { width: 100%; }
    .list-arrow { display: none; }
    .fc-list-event-title { padding: 12px 14px !important; }
}
@media (max-width: 480px) {
    .fc-list-day-cushion { font-size: 13px; }
    .fc-list-day-cushion .fc-list-day-number { width: 32px; height: 32px; font-size: 13px; }
    .list-task-title { font-size: 14px; }
    .list-status-badge { font-size: 10px; padding: 4px 10px; }
    .list-assignee-chip { font-size: 11px; padding: 4px 8px; }
}
</style>
@endpush

@section('content')
<div class="calendar-page">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="header-text">
                <h4>تقويم مهامي</h4>
                <p>إدارة المهام الشخصية والمكلف بها</p>
            </div>
        </div>
        <button class="btn btn-primary" onclick="openCreateTaskModal()" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); border: none;">
            <i class="fas fa-plus"></i> إضافة مهمة شخصية
        </button>
    </div>

    <div class="legend-container">
        <div class="legend-item">
            <div class="legend-color legend-pending"></div>
            <span>لم تتم</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-in_progress"></div>
            <span>قيد التنفيذ</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-partially_done"></div>
            <span>منجزة جزئياً</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-completed"></div>
            <span>منجزة</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-overdue"></div>
            <span>متأخر</span>
        </div>
    </div>

    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</div>

<div class="modal fade task-modal" id="createTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus"></i>
                    إضافة مهمة شخصية
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTaskForm" method="POST" action="{{ route('student.tasks.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">عنوان المهمة <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="مثال: مراجعة جزء عم" required maxlength="500">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف (اختياري)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="تفاصيل المهمة"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ الاستحقاق <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" id="taskDueDate" required>
                            <div class="time-hint">اترك الوقت فارغاً ليوم كامل</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">من الساعة (اختياري)</label>
                            <input type="time" name="due_time" class="form-control" id="taskDueTime">
                            <div class="time-hint">وقت بداية المهمة</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">إلى الساعة (اختياري)</label>
                            <input type="time" name="end_time" class="form-control" id="taskEndTime">
                            <div class="time-hint">يجب أن يكون بعد وقت البداية</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="repeatTask" name="repeat_enabled" value="1">
                            <label class="form-check-label" for="repeatTask">
                                <i class="fas fa-repeat"></i> تكرار المهمة على عدة أيام
                            </label>
                        </div>
                    </div>

                    <div class="row" id="repeatRangeRow" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="repeat_start_date" class="form-control" id="repeatStartDate">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="repeat_end_date" class="form-control" id="repeatEndDate">
                            <div class="time-hint">الحد الأقصى 60 يوم</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: #fff;">
                        <i class="fas fa-save"></i> حفظ المهمة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="taskDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tasks"></i>
                    تفاصيل المهمة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="detail-label">العنوان</span>
                    <p id="taskDetailTitle" class="fw-semibold"></p>
                </div>
                <div class="mb-3">
                    <span class="detail-label">الوصف</span>
                    <p id="taskDetailDescription" class="text-muted"></p>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <span class="detail-label">الحالة الحالية</span>
                        <p id="taskDetailStatus"></p>
                    </div>
                    <div class="col-6 mb-3">
                        <span class="detail-label">المسند إليه</span>
                        <p id="taskDetailAssignee" class="text-muted fw-bold"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <span class="detail-label">أسندت بواسطة</span>
                        <p id="taskDetailAssigner" class="text-muted"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <span class="detail-label">تاريخ الاستحقاق</span>
                        <p id="taskDetailDue" class="text-muted"></p>
                    </div>
                    <div class="col-6 mb-3" id="taskDetailTimeCol" style="display: none;">
                        <span class="detail-label">من الساعة</span>
                        <p id="taskDetailTime" class="text-muted"></p>
                    </div>
                </div>
                <div class="row" id="taskDetailEndTimeRow" style="display: none;">
                    <div class="col-6 mb-3">
                        <span class="detail-label">إلى الساعة</span>
                        <p id="taskDetailEndTime" class="text-muted"></p>
                    </div>
                </div>

                <hr>

                <div class="mb-2">
                    <span class="detail-label"><i class="fas fa-check-circle"></i> تحديث حالة المهمة</span>
                </div>
                <div class="status-actions">
                    <button type="button" class="btn btn-status-pending" data-status="pending">
                        <i class="fas fa-clock"></i> لم تتم
                    </button>
                    <button type="button" class="btn btn-status-in_progress" data-status="in_progress">
                        <i class="fas fa-spinner"></i> قيد التنفيذ
                    </button>
                    <button type="button" class="btn btn-status-partially_done" data-status="partially_done">
                        <i class="fas fa-adjust"></i> منجزة جزئياً
                    </button>
                    <button type="button" class="btn btn-status-completed" data-status="completed">
                        <i class="fas fa-check"></i> منجزة
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/ar.global.min.js"></script>
<script>
let currentTaskId = null;
const updateStatusUrlTemplate = @json(route('student.tasks.updateStatus', ['task' => '__TASK_ID__']));

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        locale: 'ar',
        direction: 'rtl',
        height: 'auto',
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        nowIndicator: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '23:00:00',
        events: @json($tasksJson),
        eventContent: function(arg) {
            var timeStr = '';
            if (arg.event.start && !arg.event.allDay) {
                var startTime = formatTime12(arg.event.start);
                timeStr = '<span style="font-weight:700; margin-left:4px;">' + startTime + '</span>';
                if (arg.event.end) {
                    var endTime = formatTime12(arg.event.end);
                    if (endTime !== startTime) {
                        timeStr += '<span style="font-weight:500; margin-left:2px;"> - ' + endTime + '</span>';
                    }
                }
            }
            return { html: '<div class="fc-event-title">' + timeStr + escapeHtml(arg.event.title) + '</div>' };
        },
        eventClassNames: function(arg) {
            var classes = ['fc-event-task-' + arg.event.extendedProps.status];
            if (arg.event.extendedProps.isOverdue) {
                classes.push('fc-event-task-overdue');
            }
            return classes;
        },
        select: function(info) {
            var startStr = info.startStr;
            var endStr = info.endStr;
            var startDateStr = startStr.split('T')[0];
            var endDateOnlyStr = endStr ? endStr.split('T')[0] : startDateStr;
            var isMultiDay = endStr && endDateOnlyStr !== startDateStr;

            document.getElementById('taskDueDate').value = startDateStr;
            var timeInput = document.getElementById('taskDueTime');
            var endInput = document.getElementById('taskEndTime');
            if (startStr.includes('T')) {
                timeInput.value = startStr.split('T')[1].substring(0, 5);
                if (endInput && endStr && endStr.includes('T')) {
                    endInput.value = endStr.split('T')[1].substring(0, 5);
                } else {
                    endInput.value = '';
                }
            } else {
                timeInput.value = '';
                if (endInput) endInput.value = '';
            }

            if (isMultiDay) {
                var repeatCheckbox = document.getElementById('repeatTask');
                var repeatRangeRow = document.getElementById('repeatRangeRow');
                var repeatStart = document.getElementById('repeatStartDate');
                var repeatEnd = document.getElementById('repeatEndDate');
                if (repeatCheckbox) repeatCheckbox.checked = true;
                if (repeatRangeRow) repeatRangeRow.style.display = '';
                if (repeatStart) repeatStart.value = startDateStr;
                if (repeatEnd) repeatEnd.value = endDateOnlyStr;
            }

            new bootstrap.Modal(document.getElementById('createTaskModal')).show();
            calendar.unselect();
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            var task = info.event;
            var props = task.extendedProps;
            currentTaskId = task.id;

            document.getElementById('taskDetailTitle').textContent = task.title;
            document.getElementById('taskDetailDescription').textContent = props.description || 'لا يوجد وصف';
            document.getElementById('taskDetailStatus').innerHTML = '<span class="status-badge ' + props.status + '">' + props.statusLabel + '</span>';
            document.getElementById('taskDetailAssignee').textContent = props.assigneeName || 'أنا';
            var assignerText = props.isSelfAssigned ? 'أنا (مهمة شخصية)' : (props.assignerName || '-');
            document.getElementById('taskDetailAssigner').textContent = assignerText;
            document.getElementById('taskDetailDue').textContent = props.dueDate || '-';

            var timeCol = document.getElementById('taskDetailTimeCol');
            var endTimeRow = document.getElementById('taskDetailEndTimeRow');
            if (props.dueTime) {
                timeCol.style.display = '';
                document.getElementById('taskDetailTime').textContent = formatTime12Str(props.dueTime);
                if (props.endTime) {
                    endTimeRow.style.display = '';
                    document.getElementById('taskDetailEndTime').textContent = formatTime12Str(props.endTime);
                } else {
                    endTimeRow.style.display = 'none';
                }
            } else {
                timeCol.style.display = 'none';
            }

            document.querySelectorAll('.status-actions .btn').forEach(function(btn) {
                var isCurrent = btn.getAttribute('data-status') === props.status;
                btn.style.outline = isCurrent ? '3px solid currentColor' : 'none';
                btn.style.fontWeight = isCurrent ? '700' : '500';
            });

            new bootstrap.Modal(document.getElementById('taskDetailModal')).show();
        },
        eventDidMount: function(info) {
            if (info.view.type === 'listWeek') {
                customizeListEvent(info);
            }
            var tip = info.event.extendedProps.description || info.event.title;
            if (info.event.extendedProps.dueTime) {
                var timeRange = '⏰ ' + formatTime12Str(info.event.extendedProps.dueTime);
                if (info.event.extendedProps.endTime) {
                    timeRange += ' - ' + formatTime12Str(info.event.extendedProps.endTime);
                }
                tip = timeRange + ' | ' + tip;
            }
            info.el.title = tip;
        },
        viewDidMount: function(info) {
            if (info.view.type === 'listWeek') {
                customizeListDayHeaders();
            }
        },
        datesSet: function(info) {
            if (info.view.type === 'listWeek') {
                setTimeout(function() { customizeListDayHeaders(); }, 30);
            }
        }
    });

    calendar.render();

    window.openCreateTaskModal = function() {
        document.getElementById('taskDueDate').value = '';
        document.getElementById('taskDueTime').value = '';
        document.getElementById('taskEndTime').value = '';
        document.getElementById('repeatTask').checked = false;
        document.getElementById('repeatRangeRow').style.display = 'none';
        document.getElementById('repeatStartDate').value = '';
        document.getElementById('repeatEndDate').value = '';
        new bootstrap.Modal(document.getElementById('createTaskModal')).show();
    };

    var repeatCheckbox = document.getElementById('repeatTask');
    if (repeatCheckbox) {
        repeatCheckbox.addEventListener('change', function() {
            var rangeRow = document.getElementById('repeatRangeRow');
            if (this.checked) {
                rangeRow.style.display = '';
                var dueDate = document.getElementById('taskDueDate').value;
                if (dueDate) {
                    document.getElementById('repeatStartDate').value = dueDate;
                    document.getElementById('repeatEndDate').value = dueDate;
                }
            } else {
                rangeRow.style.display = 'none';
            }
        });
    }

    var createForm = document.getElementById('createTaskForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            var formData = new FormData(createForm);
            try {
                var response = await fetch(createForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    var data = {};
                    try { data = await response.json(); } catch (err) {}
                    alert(data.message || 'حدث خطأ أثناء إضافة المهمة');
                }
            } catch (err) {
                alert('حدث خطأ في الاتصال: ' + err.message);
            }
        });
    }

    document.querySelectorAll('.status-actions .btn').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            if (!currentTaskId) {
                alert('لم يتم تحديد المهمة');
                return;
            }

            var status = this.getAttribute('data-status');
            var url = updateStatusUrlTemplate.replace('__TASK_ID__', currentTaskId);

            var fd = new FormData();
            fd.append('status', status);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            fd.append('_method', 'PUT');

            try {
                var response = await fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    var data = {};
                    try { data = await response.json(); } catch (err) {}
                    alert(data.message || 'حدث خطأ أثناء تحديث الحالة');
                }
            } catch (err) {
                alert('حدث خطأ في الاتصال: ' + err.message);
            }
        });
    });
});

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function buildListEventContent(arg) {
    var event = arg.event;
    var props = event.extendedProps || {};
    var statusClass = props.statusClass || props.status || 'pending';

    var timeContent = '';
    if (event.allDay) {
        timeContent = '<i class="fas fa-calendar-day"></i> يوم كامل';
    } else if (event.start) {
        var startTime = formatTime12(event.start);
        var endTime = event.end ? formatTime12(event.end) : '';
        timeContent = startTime;
        if (endTime && endTime !== startTime) {
            timeContent += ' - ' + endTime;
        }
    }

    var graphicHtml = '<span class="fc-list-event-dot list-status-dot ' + statusClass + '"></span>';

    var titleHtml = '<span class="list-task-title">' + escapeHtml(event.title) + '</span>';
    if (props.isOverdue) {
        titleHtml += ' <span class="list-overdue-badge">متأخر</span>';
    }

    return [
        { html: '<span class="list-time-display">' + timeContent + '</span>' },
        { html: graphicHtml },
        { html: titleHtml }
    ];
}

function customizeListEvent(info) {
    var el = info.el;
    var event = info.event;
    var props = event.extendedProps || {};
    var status = props.status || 'pending';

    var colorMap = {
        'pending': '#f39c12',
        'in_progress': '#3498db',
        'partially_done': '#8e44ad',
        'completed': '#27ae60',
        'cancelled': '#95a5a6',
        'overdue': '#c0392b'
    };
    el.style.setProperty('--event-color', colorMap[status] || '#27ae60');

    var timeEl = el.querySelector('.fc-list-event-time');
    if (timeEl && event.start) {
        if (event.allDay) {
            timeEl.innerHTML = '<span class="list-time-chip all-day"><i class="fas fa-sun"></i> يوم كامل</span>';
        } else {
            var startTime = formatTime12(event.start);
            var endTime = event.end ? formatTime12(event.end) : '';
            if (endTime && endTime !== startTime) {
                timeEl.innerHTML = '<span class="list-time-chip"><i class="fas fa-clock"></i> ' + startTime + ' → ' + endTime + '</span>';
            } else {
                timeEl.innerHTML = '<span class="list-time-chip"><i class="fas fa-clock"></i> ' + startTime + '</span>';
            }
        }
    }

    var titleEl = el.querySelector('.fc-list-event-title');
    if (titleEl) {
        var titleText = event.title || '';
        var statusLabel = props.statusLabel || 'لم تتم';
        var statusClass = props.status || 'pending';
        var overdueHtml = props.isOverdue ? '<span class="list-overdue-badge"><i class="fas fa-exclamation-circle"></i> متأخر</span>' : '';
        var assigneeHtml = props.assigneeName ? '<span class="list-assignee-chip"><i class="fas fa-user"></i> ' + escapeHtml(props.assigneeName) + '</span>' : '';
        var descHtml = props.description ? '<span class="list-task-desc">' + escapeHtml(props.description) + '</span>' : '';

        titleEl.innerHTML =
            '<div class="list-task-content">' +
                '<div class="list-task-main">' +
                    '<span class="list-task-title">' + escapeHtml(titleText) + '</span>' +
                    descHtml +
                '</div>' +
                '<div class="list-task-meta">' +
                    assigneeHtml +
                    '<span class="list-status-badge ' + statusClass + '">' + escapeHtml(statusLabel) + '</span>' +
                    overdueHtml +
                    '<i class="fas fa-chevron-left list-arrow"></i>' +
                '</div>' +
            '</div>';
    }
}

function customizeListDayHeaders() {
    document.querySelectorAll('.fc-list-day').forEach(function(dayEl) {
        var hasEvents = dayEl.querySelector('.fc-list-event') !== null;
        if (hasEvents) {
            dayEl.classList.add('has-events');
        } else {
            dayEl.classList.remove('has-events');
        }
    });

    var emptyEl = document.querySelector('.fc-list-empty');
    if (emptyEl && !emptyEl.querySelector('.empty-icon')) {
        var cushion = emptyEl.querySelector('.fc-list-empty-cushion');
        if (cushion) {
            cushion.innerHTML =
                '<div class="empty-icon"><i class="fas fa-calendar-check"></i></div>' +
                '<div style="font-size:16px; font-weight:700; color:#182B48; margin-bottom:6px;">لا توجد مهام</div>' +
                '<div style="font-size:13px; color:#7f8c8d;">استمتع بوقتك، لا توجد مهام مجدولة في هذه الفترة</div>';
        }
    }
}

function formatTime12(date) {
    if (!date) return '';
    var h = date.getHours();
    var m = String(date.getMinutes()).padStart(2, '0');
    var period = h >= 12 ? 'م' : 'ص';
    var h12 = h % 12;
    if (h12 === 0) h12 = 12;
    return h12 + ':' + m + ' ' + period;
}

function formatTime12Str(timeStr) {
    if (!timeStr) return '';
    var parts = timeStr.split(':');
    if (parts.length < 2) return timeStr;
    var h = parseInt(parts[0], 10);
    var m = parts[1];
    var period = h >= 12 ? 'م' : 'ص';
    var h12 = h % 12;
    if (h12 === 0) h12 = 12;
    return h12 + ':' + m + ' ' + period;
}
</script>
@endpush
