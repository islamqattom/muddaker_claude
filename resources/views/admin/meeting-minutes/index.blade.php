@extends('layouts.app')
@section('title', 'محاضر الاجتماعات')
@section('page-title', 'محاضر الاجتماعات')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">محاضر الاجتماعات</li>
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
.header-text p::before {
    content: '✦';
    color: var(--gold);
    margin-left: 8px;
}
@keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }
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
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.stat-card.primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); }
.stat-card.warning { background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); }
.stat-card.success { background: linear-gradient(135deg, var(--success) 0%, #219a52 100%); }
.stat-card.info { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); }
.stat-icon i { color: var(--gold); }
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
.filter-form .row { gap: 15px; }
.filter-form .form-control, .filter-form .form-select {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s ease;
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
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
}
.btn-search:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(24,43,72,0.3); }
.btn-reset {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 16px;
    background: transparent;
    color: #7f8c8d; border: 2px solid #e9ecef; border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    font-family: 'Tajawal', sans-serif;
}
.btn-reset:hover { border-color: var(--primary); color: var(--primary); }
.meetings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
    margin-bottom: 16px;
}
.meeting-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}
.meeting-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0; left: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.meeting-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--card-shadow-hover);
}
.meeting-card:hover::before { height: 8px; }
.meeting-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
}
.meeting-date {
    display: flex; align-items: center; gap: 12px;
    color: #fff; font-weight: 700; font-size: 17px;
}
.meeting-date i { color: var(--gold); font-size: 20px; }
.status-badge.draft { background: rgba(243,156,18,0.25); color: #f1c40f; border: 1px solid rgba(243,156,18,0.3); }
.status-badge.approved { background: rgba(39,174,96,0.25); color: #2ecc71; border: 1px solid rgba(39,174,96,0.3); }
.status-badge.archived { background: rgba(149,165,166,0.25); color: #bdc3c7; border: 1px solid rgba(149,165,166,0.3); }
.status-badge i { font-size: 11px; }
.meeting-body { padding: 16px 16px; }
.meeting-meta {
    display: flex; gap: 25px; margin-bottom: 16px;
    flex-wrap: wrap;
}
.meta-item {
    display: flex; align-items: center; gap: 8px;
    color: #7f8c8d; font-size: 14px;
}
.meta-item i { color: var(--gold); font-size: 14px; }
.meta-item span { font-weight: 500; color: #5a6c7d; }
.meeting-stats { display: flex; gap: 12px; flex-wrap: wrap; }
.stat-item {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border-radius: 12px;
    font-size: 13px; color: var(--primary);
    font-weight: 600;
    border: 1px solid rgba(24,43,72,0.06);
}
.stat-item i { color: var(--gold); font-size: 14px; }
.stat-item .stat-value { color: var(--gold-dark); font-weight: 700; }
.meeting-actions {
    display: flex; gap: 10px;
    padding: 16px 16px;
    border-top: 1px solid #f0f4f8;
    background: #fafbfc;
}
.action-btn {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 15px; border-radius: 12px;
    text-decoration: none; font-weight: 600; font-size: 13px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
}
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
    text-align: center;
    padding: 40px 16px;
    background: #fff;
    border-radius: 13px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
}
.empty-state-card h5 { color: var(--primary); font-size: 18px; font-weight: 700; margin-bottom: 10px; }
.empty-state-card p { color: #95a5a6; margin-bottom: 16px; font-size: 15px; }
@media (max-width: 768px) {
.meetings-grid { grid-template-columns: 1fr; }
}
</style>

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="header-text">
            <h4>محاضر الاجتماعات</h4>
            <p>إدارة وتتبع محاضر جلسات جمعية تحفيظ القرآن الكريم</p>
        </div>
    </div>
    <a href="{{ route('admin.meeting-minutes.create') }}" class="add-btn">
        <i class="fas fa-plus-circle"></i>
        إضافة محضر جديد
    </a>
</div>

{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $meetingMinutes->total() }}</div>
            <div class="stat-label">إجمالي المحاضر</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-copy"></i></div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon"><i class="fas fa-pen-fancy"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $meetingMinutes->where('status', 'draft')->count() }}</div>
            <div class="stat-label">مسودات</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-edit"></i></div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon"><i class="fas fa-stamp"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $meetingMinutes->where('status', 'approved')->count() }}</div>
            <div class="stat-label">معتمدة</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-check"></i></div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon"><i class="fas fa-box-archive"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $meetingMinutes->where('status', 'archived')->count() }}</div>
            <div class="stat-label">مؤرشفة</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-archive"></i></div>
    </div>
</div>

{{-- Filter Card --}}
<div class="filter-card">
    <div class="filter-header">
        <i class="fas fa-filter"></i>
        <span>بحث وتصفية المحاضر</span>
    </div>
    <form method="GET" class="filter-form">
        <div class="row g-3">
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">جميع الحالات</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="من تاريخ">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="إلى تاريخ">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> بحث
                </button>
                <a href="{{ route('admin.meeting-minutes.index') }}" class="btn-reset">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Meetings Grid --}}
@if($meetingMinutes->isEmpty())
    <div class="empty-state-card">
        <div class="empty-icon">
            <i class="fas fa-calendar-minus"></i>
        </div>
        <h5>لا توجد محاضر اجتماعات</h5>
        <p>ابدأ بإضافة محضر اجتماع جديد لجمعية تحفيظ القرآن الكريم</p>
        <a href="{{ route('admin.meeting-minutes.create') }}" class="btn-search">
            <i class="fas fa-plus me-2"></i>إضافة محضر
        </a>
    </div>
@else
    <div class="meetings-grid">
        @foreach($meetingMinutes as $minute)
        <div class="meeting-card">
            <div class="meeting-header">
                <div class="meeting-date">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $minute->meeting_date->format('Y/m/d') }}
                </div>
                <div class="status-badge {{ $minute->status }}">
                    @if($minute->status === 'draft')
                        <i class="fas fa-pen"></i>مسودة
                    @elseif($minute->status === 'approved')
                        <i class="fas fa-check-circle"></i>معتمد
                    @else
                        <i class="fas fa-archive"></i>مؤرشف
                    @endif
                </div>
            </div>
            <div class="meeting-body">
                <div class="meeting-meta">
                    @if($minute->meeting_time)
                    <span class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span>{{ $minute->meeting_time }}</span>
                    </span>
                    @endif
                    <span class="meta-item">
                        <i class="fas fa-user-shield"></i>
                        <span>{{ $minute->creator->name ?? '-' }}</span>
                    </span>
                </div>

                @php
                    $attendeesData = $minute->attendees ?? [];
                    $manualAttendees = $minute->manual_attendees ?? [];
                    $totalAttendees = (is_array($attendeesData) ? count(array_filter($attendeesData, fn($a) => $a['is_present'] ?? false)) : 0) + (is_array($manualAttendees) ? count(array_filter($manualAttendees, fn($a) => !empty($a['name']))) : 0);
                    $tasksCount = $minute->tasks ? $minute->tasks->count() : 0;
                @endphp

                <div class="meeting-stats">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span class="stat-value">{{ $totalAttendees }}</span> حضور
                    </div>
                    @if($tasksCount > 0)
                    <div class="stat-item">
                        <i class="fas fa-tasks"></i>
                        <span class="stat-value">{{ $tasksCount }}</span> تكليف
                    </div>
                    @endif
                </div>
            </div>
            <div class="meeting-actions">
                <a href="{{ route('admin.meeting-minutes.show', $minute->id) }}" class="action-btn view">
                    <i class="fas fa-eye"></i>
                    <span>عرض</span>
                </a>
                <a href="{{ route('admin.meeting-minutes.edit', $minute->id) }}" class="action-btn edit">
                    <i class="fas fa-edit"></i>
                    <span>تعديل</span>
                </a>
                @if($minute->status === 'draft')
                <form action="{{ route('admin.meeting-minutes.destroy', $minute->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل تريد حذف هذا المحضر؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn delete">
                        <i class="fas fa-trash-alt"></i>
                        <span>حذف</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-section">
        {{ $meetingMinutes->withQueryString()->links() }}
    </div>
@endif

@endsection