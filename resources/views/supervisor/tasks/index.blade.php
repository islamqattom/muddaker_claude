@extends('layouts.app')

@section('title', 'تكليفاتي')
@section('page-title', 'تكليفاتي')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">تكليفاتي</li>
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
    position: relative; overflow: hidden;
}
.header-icon::before {
    content: '';
    position: absolute;
    top: -50%; right: -50%;
    width: 100%; height: 100%;
    background: radial-gradient(circle, rgba(212,160,48,0.15) 0%, transparent 70%);
}
.header-icon i { position: relative; z-index: 1; }
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    position: relative; padding: 25px;
    border-radius: 18px; overflow: hidden;
    display: flex; align-items: flex-start; gap: 18px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
}
.stat-card:hover { transform: translateY(-8px) scale(1.02); }
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
.stat-card.all { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); }
.stat-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: #fff; backdrop-filter: blur(5px);
    flex-shrink: 0; border: 1px solid rgba(255,255,255,0.1);
}
.stat-icon i { color: #fff; }
.stat-content { flex: 1; position: relative; z-index: 1; }
.stat-number { font-size: 36px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 6px; }
.stat-label { font-size: 13px; color: rgba(255,255,255,0.85); font-weight: 500; letter-spacing: 0.5px; }

.filter-tabs {
    display: flex; gap: 10px; flex-wrap: wrap;
    margin-bottom: 25px;
}
.filter-tab {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 22px; border-radius: 12px;
    text-decoration: none; font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    font-family: 'Tajawal', sans-serif;
}
.filter-tab.all { background: var(--primary); color: #fff; border-color: var(--primary); }
.filter-tab.all:hover { background: var(--primary-light); border-color: var(--primary-light); }
.filter-tab.pending { background: rgba(243,156,18,0.1); color: var(--warning); border-color: rgba(243,156,18,0.2); }
.filter-tab.pending:hover { background: var(--warning); color: #fff; border-color: var(--warning); }
.filter-tab.in_progress { background: rgba(52,152,219,0.1); color: var(--info); border-color: rgba(52,152,219,0.2); }
.filter-tab.in_progress:hover { background: var(--info); color: #fff; border-color: var(--info); }
.filter-tab.completed { background: rgba(39,174,96,0.1); color: var(--success); border-color: rgba(39,174,96,0.2); }
.filter-tab.completed:hover { background: var(--success); color: #fff; border-color: var(--success); }
.filter-tab.active { transform: scale(1.05); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.filter-tab i { font-size: 14px; }

.tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 20px;
}
.task-card {
    background: #fff; border-radius: 18px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}
.task-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.task-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--card-shadow-hover);
}
.task-card:hover::before { height: 6px; }
.task-header {
    padding: 22px 22px 0;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.task-info { flex: 1; }
.task-title { font-size: 17px; font-weight: 700; color: var(--primary); margin-bottom: 6px; }
.task-desc { font-size: 13px; color: #7f8c8d; line-height: 1.5; }
.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    white-space: nowrap;
}
.status-badge.pending { background: rgba(243,156,18,0.15); color: var(--warning); border: 1px solid rgba(243,156,18,0.2); }
.status-badge.in_progress { background: rgba(52,152,219,0.15); color: var(--info); border: 1px solid rgba(52,152,219,0.2); }
.status-badge.completed { background: rgba(39,174,96,0.15); color: var(--success); border: 1px solid rgba(39,174,96,0.2); }
.status-badge.cancelled { background: rgba(192,57,43,0.15); color: var(--danger); border: 1px solid rgba(192,57,43,0.2); }
.status-badge i { font-size: 11px; }

.task-body { padding: 18px 22px; }
.task-meta {
    display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;
}
.meta-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #7f8c8d;
}
.meta-item i { color: var(--gold); font-size: 14px; }
.meta-item span { font-weight: 600; color: var(--primary); }

.overdue-badge {
    background: linear-gradient(135deg, var(--danger) 0%, #a93226 100%);
    color: #fff; padding: 5px 12px; border-radius: 15px;
    font-size: 12px; font-weight: 600;
}
.overdue-badge i { margin-left: 5px; }

.task-footer {
    padding: 18px 22px;
    border-top: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02) 0%, rgba(42,69,112,0.04) 100%);
}
.status-select {
    width: 100%; border-radius: 12px; border: 2px solid #e9ecef;
    padding: 12px 16px; font-size: 14px;
    transition: all 0.3s ease; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
    background: #fff;
}
.status-select:focus { border-color: var(--gold); box-shadow: 0 0 0 4px rgba(212,160,48,0.12); }

.pagination-section { display: flex; justify-content: center; margin-top: 35px; }
.pagination-section .pagination { gap: 8px; }
.pagination-section .page-link {
    padding: 10px 18px; border-radius: 12px; font-size: 14px;
    border: 2px solid #e9ecef; color: var(--primary);
    transition: all 0.3s ease;
}
.pagination-section .page-link:hover { border-color: var(--gold); color: var(--gold); }
.pagination-section .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-color: var(--primary); color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}

.empty-state-card {
    text-align: center; padding: 70px 30px;
    background: #fff; border-radius: 18px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
}
.empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08) 0%, rgba(42,69,112,0.12) 100%);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin: 0 auto 25px; font-size: 42px; color: var(--gold);
    border: 3px solid rgba(212,160,48,0.15);
}
.empty-state-card h5 { color: var(--primary); font-size: 20px; font-weight: 700; margin-bottom: 10px; }
.empty-state-card p { color: #95a5a6; margin-bottom: 25px; font-size: 15px; }

@media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 992px) { .tasks-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .filter-tabs { flex-direction: column; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="header-text">
            <h4>تكليفاتي</h4>
            <p>متابعة التكليفات المكلّفة إليك</p>
        </div>
    </div>
</div>

@php
    $statusCounts = [
        'pending' => $tasks->where('status', 'pending')->count(),
        'in_progress' => $tasks->where('status', 'in_progress')->count(),
        'completed' => $tasks->where('status', 'completed')->count(),
    ];
@endphp
<div class="stats-grid">
    <div class="stat-card all">
        <div class="stat-icon"><i class="fas fa-list"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $tasks->total() }}</div>
            <div class="stat-label">جميع التكليفات</div>
        </div>
    </div>
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
</div>

<div class="filter-tabs">
    <a href="{{ route('supervisor.tasks.index', ['status' => '']) }}" class="filter-tab all {{ !request('status') ? 'active' : '' }}">
        <i class="fas fa-list"></i> الكل
    </a>
    <a href="{{ route('supervisor.tasks.index', ['status' => 'pending']) }}" class="filter-tab pending {{ request('status') == 'pending' ? 'active' : '' }}">
        <i class="fas fa-clock"></i> قيد الانتظار
    </a>
    <a href="{{ route('supervisor.tasks.index', ['status' => 'in_progress']) }}" class="filter-tab in_progress {{ request('status') == 'in_progress' ? 'active' : '' }}">
        <i class="fas fa-spinner"></i> قيد التنفيذ
    </a>
    <a href="{{ route('supervisor.tasks.index', ['status' => 'completed']) }}" class="filter-tab completed {{ request('status') == 'completed' ? 'active' : '' }}">
        <i class="fas fa-check"></i> مكتمل
    </a>
</div>

@if($tasks->isEmpty())
    <div class="empty-state-card">
        <div class="empty-icon">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <h5>لا توجد تكليفات</h5>
        <p>لم يتم تكليفك بأي مهام جديدة</p>
    </div>
@else
    <div class="tasks-grid">
        @foreach($tasks as $task)
        <div class="task-card">
            <div class="task-header">
                <div class="task-info">
                    <div class="task-title">{{ $task->title }}</div>
                    @if($task->description)
                        <div class="task-desc">{{ $task->description }}</div>
                    @endif
                </div>
                <span class="status-badge {{ $task->status }}">
                    @if($task->status === 'pending')
                        <i class="fas fa-clock"></i>
                    @elseif($task->status === 'in_progress')
                        <i class="fas fa-spinner"></i>
                    @elseif($task->status === 'partially_done')
                        <i class="fas fa-adjust"></i>
                    @elseif($task->status === 'completed')
                        <i class="fas fa-check"></i>
                    @else
                        <i class="fas fa-times"></i>
                    @endif
                    {{ $task->status_label }}
                </span>
            </div>
            <div class="task-body">
                <div class="task-meta">
                    @if($task->meetingMinute)
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>{{ $task->meetingMinute->meeting_date->format('Y/m/d') }}</span>
                    </div>
                    @endif
                    <div class="meta-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span>{{ $task->created_at->format('Y/m/d') }}</span>
                    </div>
                </div>
                @if($task->is_overdue)
                <span class="overdue-badge">
                    <i class="fas fa-exclamation-triangle"></i>
                    متأخر
                </span>
                @endif
            </div>
            <div class="task-footer">
                <form method="POST" action="{{ route('supervisor.tasks.updateStatus', $task->id) }}" class="d-inline w-100">
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
            </div>
        </div>
        @endforeach
    </div>

    @if($tasks->hasPages())
    <div class="pagination-section">
        {{ $tasks->links() }}
    </div>
    @endif
@endif

@endsection