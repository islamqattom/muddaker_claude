@extends('layouts.app')

@section('title', 'إدارة التكليفات')
@section('page-title', 'إدارة التكليفات')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">إدارة التكليفات</li>
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
    --card-shadow-hover: 0 12px 40px rgba(24, 43, 72, 0.15);
}
body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }
.header-icon { position: relative; overflow: hidden; }
.header-icon::before {
    content: '';
    position: absolute;
    top: -50%; right: -50%;
    width: 100%; height: 100%;
    background: radial-gradient(circle, rgba(212,160,48,0.15) 0%, transparent 70%);
}
.header-icon i { position: relative; z-index: 1; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }
.stat-card::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}
.stat-card::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.stat-card.pending { background: linear-gradient(135deg, var(--warning) 0%, #e67e22 100%); }
.stat-card.in_progress { background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%); }
.stat-card.completed { background: linear-gradient(135deg, var(--success) 0%, #219a52 100%); }
.stat-card.cancelled { background: linear-gradient(135deg, var(--danger) 0%, #a93226 100%); }
.stat-icon i { color: #fff; }
.stat-content { flex: 1; position: relative; z-index: 1; }
.filter-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; color: var(--primary);
}
.filter-header i { color: var(--gold); font-size: 18px; }
.filter-header span::before { content: '◆'; color: var(--gold); margin-left: 8px; font-size: 10px; }
.filter-form .form-control, .filter-form .form-select {
    border-radius: 12px; border: 2px solid #e9ecef;
    padding: 12px 16px; transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
}
.filter-form .form-control:focus, .filter-form .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212,160,48,0.12);
}
.btn-search {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; border: none; border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease; font-family: 'Tajawal', sans-serif;
}
.btn-search:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(24,43,72,0.3); }
.btn-reset {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 16px;
    background: transparent; color: #7f8c8d;
    border: 2px solid #e9ecef; border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease; text-decoration: none;
    font-family: 'Tajawal', sans-serif;
}
.btn-reset:hover { border-color: var(--primary); color: var(--primary); }
.tasks-table-card {
    background: #fff; border-radius: 13px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
}
.table-header i { color: var(--gold); font-size: 20px; }
.tasks-table { width: 100%; border-collapse: collapse; }
.tasks-table thead {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
}
.tasks-table th {
    padding: 16px 16px;
    font-size: 12px; font-weight: 700;
    color: #fff; text-transform: uppercase; letter-spacing: 0.5px;
    border: none;
}
.tasks-table td {
    padding: 16px 16px;
    font-size: 14px; color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.05);
    vertical-align: middle;
}
.tasks-table tbody tr {
    transition: all 0.3s ease;
}
.tasks-table tbody tr:hover {
    background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%);
}
.task-title { font-weight: 700; color: var(--primary); font-size: 15px; }
.task-desc { color: #95a5a6; font-size: 13px; margin-top: 4px; }
.status-badge.pending { background: rgba(243,156,18,0.15); color: var(--warning); border: 1px solid rgba(243,156,18,0.2); }
.status-badge.in_progress { background: rgba(52,152,219,0.15); color: var(--info); border: 1px solid rgba(52,152,219,0.2); }
.status-badge.completed { background: rgba(39,174,96,0.15); color: var(--success); border: 1px solid rgba(39,174,96,0.2); }
.status-badge.cancelled { background: rgba(192,57,43,0.15); color: var(--danger); border: 1px solid rgba(192,57,43,0.2); }
.status-badge i { font-size: 11px; }
.overdue-badge {
    background: linear-gradient(135deg, var(--danger) 0%, #a93226 100%);
    color: #fff; padding: 4px 10px; border-radius: 12px;
    font-size: 11px; font-weight: 600; margin-right: 8px;
}
.meeting-link {
    color: var(--primary); text-decoration: none; font-weight: 600;
    transition: all 0.3s ease;
}
.meeting-link:hover { color: var(--gold); }
.meeting-link i { color: var(--gold); margin-left: 5px; }
.status-select {
    border-radius: 10px; border: 2px solid #e9ecef;
    padding: 8px 12px; font-size: 13px;
    transition: all 0.3s ease; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
}
.status-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(212,160,48,0.1); }
.pagination-section { display: flex; justify-content: center; margin-top: 16px; }
.pagination-section .pagination { gap: 4px; }
.pagination-section .page-link {
    padding: 6px 11px; border-radius: 7px; font-size: 12.5px;
    border: 2px solid #e9ecef; color: var(--primary);
    transition: all 0.3s ease;
}
.pagination-section .page-link:hover { border-color: var(--gold); color: var(--gold); padding: 6px 11px; border-radius: 7px; font-size: 12.5px;}
.pagination-section .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-color: var(--primary); color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.empty-state-card {
    text-align: center; padding: 40px 16px;
    background: #fff; border-radius: 13px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
}
.empty-state-card h5 { color: var(--primary); font-size: 18px; font-weight: 700; margin-bottom: 10px; }
.empty-state-card p { color: #95a5a6; margin-bottom: 16px; font-size: 15px; }
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
.assignment-row {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-radius: 13px; padding: 16px;
    margin-bottom: 12px; border: 2px solid rgba(24,43,72,0.06);
}
.assignment-row .form-label {
    font-weight: 600; color: #5a6c7d;
    margin-bottom: 8px; display: block;
    font-size: 14px;
}
.assignment-row .form-label i { color: var(--gold); margin-left: 6px; }
</style>

<x-page-hero icon="fas fa-tasks">
    <h1 class="page-banner-title">إدارة التكليفات</h1>
    <p class="page-banner-sub">متابعة وتتبع جميع التكليفات المكلّفة للمشرفين</p>
    <x-slot:actions>
        <div class="header-actions">
            <button type="button" class="btn btn-primary" onclick="openCreateTaskModal()">
                <i class="fas fa-plus"></i> إضافة تكليف
            </button>
        </div>
    </x-slot:actions>
</x-page-hero>

@php
    $statusCounts = [
        'pending' => $tasks->where('status', 'pending')->count(),
        'in_progress' => $tasks->where('status', 'in_progress')->count(),
        'completed' => $tasks->where('status', 'completed')->count(),
        'cancelled' => $tasks->where('status', 'cancelled')->count(),
    ];
@endphp
<div class="stats-grid">
    <div class="stat-card pending">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $statusCounts['pending'] }}</div>
            <div class="stat-label">قيد الانتظار</div>
        </div>
    </div>
    <div class="stat-card in_progress">
        <div class="stat-icon"><i class="fas fa-spinner"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $statusCounts['in_progress'] }}</div>
            <div class="stat-label">قيد التنفيذ</div>
        </div>
    </div>
    <div class="stat-card completed">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $statusCounts['completed'] }}</div>
            <div class="stat-label">مكتمل</div>
        </div>
    </div>
    <div class="stat-card cancelled">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $statusCounts['cancelled'] }}</div>
            <div class="stat-label">ملغي</div>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="filter-header">
        <i class="fas fa-filter"></i>
        <span>بحث وتصفية التكليفات</span>
    </div>
    <form method="GET" class="filter-form">
        <div class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">جميع الحالات</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                    <option value="partially_done" {{ request('status') == 'partially_done' ? 'selected' : '' }}>منجزة جزئياً</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="assignee_id" class="form-select">
                    <option value="">جميع المشرفين</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" {{ request('assignee_id') == $supervisor->id ? 'selected' : '' }}>
                            {{ $supervisor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> بحث
                </button>
                <a href="{{ route('admin.tasks.index') }}" class="btn-reset">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </div>
    </form>
</div>

@if($tasks->isEmpty())
    <div class="empty-state-card">
        <div class="empty-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <h5>لا توجد تكليفات</h5>
        <p>لم يتم إضافة أي تكليفات بعد</p>
    </div>
@else
    <div class="tasks-table-card">
        <div class="table-header">
            <i class="fas fa-list"></i>
            <span>قائمة التكليفات ({{ $tasks->total() }})</span>
        </div>
        <div class="table-responsive">
            <table class="tasks-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التكليف</th>
                        <th>المشرف</th>
                        <th>من اجتماع</th>
                        <th>الحالة</th>
                        <th>تاريخ التكليف</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr>
                        <td>
                            <span class="badge" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);color:var(--gold);width:35px;height:35px;line-height:35px;border-radius:10px;font-size:14px;font-weight:700">
                                {{ $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage() }}
                            </span>
                        </td>
                        <td>
                            <div class="task-title">{{ $task->title }}</div>
                            @if($task->description)
                                <div class="task-desc">{{ $task->description }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle" style="width:38px;height:38px;background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:16px">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <span style="font-weight:600;color:var(--primary)">{{ $task->assignee->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($task->meetingMinute)
                                <a href="{{ route('admin.meeting-minutes.show', $task->meetingMinute->id) }}" class="meeting-link">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $task->meetingMinute->meeting_date->format('Y/m/d') }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $task->status }}">
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
                            @if($task->is_overdue)
                                <span class="overdue-badge">متأخر</span>
                            @endif
                        </td>
                        <td>
                            <span style="color:#7f8c8d;font-size:13px">
                                <i class="fas fa-calendar-plus" style="color:var(--gold);margin-left:5px"></i>
                                {{ $task->created_at->format('Y/m/d') }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.tasks.updateStatus', $task->id) }}" class="d-inline">
                                @csrf
                                @method('PUT')
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                    <option value="partially_done" {{ $task->status === 'partially_done' ? 'selected' : '' }}>منجزة جزئياً</option>
                                    <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-1" onclick="openTitleModal({{ $task->id }}, '{{ addslashes($task->title) }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($tasks->hasPages())
    <div class="pagination-section">
        {{ $tasks->links() }}
    </div>
    @endif
@endif

<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%)">
                <h5 class="modal-title" id="createTaskModalLabel" style="color:#fff">
                    <i class="fas fa-tasks" style="color:var(--gold)"></i>
                    إضافة تكليف جديد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTaskForm" method="POST" action="{{ route('admin.tasks.store') }}">
                @csrf
                <div class="modal-body">
                    <div id="tasks-assignments-container">
                        <div class="assignment-row" data-index="0">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label"><i class="fas fa-user-tie" style="color:var(--gold)"></i> المشرف</label>
                                    <select name="assignments[0][supervisor_id]" class="form-select">
                                        <option value="">-- المشرف --</option>
                                        @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-tasks" style="color:var(--gold)"></i> وصف التكليف</label>
                                    <input type="text" name="assignments[0][task]" class="form-control" placeholder="وصف التكليف">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-task-assignment" style="border-radius:10px" disabled>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-add mt-3 w-100" onclick="addTaskAssignment()">
                        <i class="fas fa-plus"></i>إضافة تكليف
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);color:#fff">
                        <i class="fas fa-save"></i> إضافة التكليف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editTitleModal" tabindex="-1" aria-labelledby="editTitleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTitleModalLabel">تعديل عنوان التكليف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTitleForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editTaskId" name="task_id">
                    <div class="mb-3">
                        <label for="editTaskTitle" class="form-label">عنوان التكليف</label>
                        <input type="text" class="form-control" id="editTaskTitle" name="title" required maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create Task Modal
    const createModal = document.getElementById('createTaskModal');
    const createForm = document.getElementById('createTaskForm');

    window.openCreateTaskModal = () => {
        new bootstrap.Modal(createModal).show();
    };

    window.addTaskAssignment = function() {
        const container = document.getElementById('tasks-assignments-container');
        const count = container.querySelectorAll('.assignment-row').length;
        const supervisors = @json($supervisors);
        let options = '<option value="">-- المشرف --</option>';
        supervisors.forEach(function(s) {
            options += '<option value="' + s.id + '">' + s.name + '</option>';
        });
        const html = '<div class="assignment-row" data-index="' + count + '">' +
            '<div class="row g-3 align-items-end">' +
            '<div class="col-md-4">' +
            '<label class="form-label"><i class="fas fa-user-tie" style="color:var(--gold)"></i> المشرف</label>' +
            '<select name="assignments[' + count + '][supervisor_id]" class="form-select">' + options + '</select>' +
            '</div>' +
            '<div class="col-md-6">' +
            '<label class="form-label"><i class="fas fa-tasks" style="color:var(--gold)"></i> وصف التكليف</label>' +
            '<input type="text" name="assignments[' + count + '][task]" class="form-control" placeholder="وصف التكليف">' +
            '</div>' +
            '<div class="col-md-2">' +
            '<button type="button" class="btn btn-outline-danger w-100 remove-task-assignment" style="border-radius:10px">' +
            '<i class="fas fa-trash-alt"></i>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
    };

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-task-assignment')) {
            const row = e.target.closest('.assignment-row');
            if (row && document.querySelectorAll('.assignment-row').length > 1) {
                row.remove();
            }
        }
    });

    if (createForm) {
        createForm.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(createForm);
            const response = await fetch(createForm.action, {
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
                const text = await response.text();
                let data = {};
                try { data = JSON.parse(text); } catch {}
                alert(data.message || 'حدث خطأ أثناء إضافة التكليف');
            }
        });
    }

    // Edit Title Modal
    const editModal = document.getElementById('editTitleModal');
    const form = document.getElementById('editTitleForm');
    const taskIdInput = document.getElementById('editTaskId');
    const taskTitleInput = document.getElementById('editTaskTitle');

    window.openTitleModal = (taskId, taskTitle) => {
        taskIdInput.value = taskId;
        taskTitleInput.value = taskTitle;
        new bootstrap.Modal(editModal).show();
    };

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const taskId = taskIdInput?.value;
        const titleValue = document.getElementById('editTaskTitle').value;
        console.log('taskId:', taskId, 'titleValue from input:', titleValue);
        const params = new URLSearchParams();
        params.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        params.append('_method', 'PUT');
        params.append('title', titleValue);
        console.log('params title value:', params.get('title'));
        if (!taskId || !titleValue) {
            alert('Error: Task ID or title not found');
            return;
        }
        console.log('Request URL:', `/admin/tasks/${taskId}/title`);
        console.log('Request body:', params.toString());
        const response = await fetch(`/admin/tasks/${taskId}/title`, {
            method: 'PUT',
            body: params,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            window.location.reload();
        } else {
            const text = await response.text();
            console.log('Error response:', text);
            let data = {};
            try { data = JSON.parse(text); } catch {}
            alert(data.errors?.title?.[0] || data.message || 'حدث خطأ أثناء تحديث العنوان');
        }
    });
});
</script>

@endsection