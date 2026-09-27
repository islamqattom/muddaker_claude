@extends('layouts.app')
@section('title', 'الإنذارات')
@section('page-title', 'الإنذارات')
@section('role-label', 'مشرف')

@section('sidebar-menu')
@include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإنذارات</li>
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

.filter-card {
    padding: 0;
    margin-bottom: 16px;
}
.filter-header {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; color: var(--primary);
}
.filter-header i { color: var(--gold); font-size: 18px; }
.filter-header span::before { content: '◆'; color: var(--gold); margin-left: 8px; font-size: 10px; }
.filter-form { padding: 16px; }
.filter-form .form-select {
    border-radius: 9px;
    border: 2px solid #e9ecef;
    padding: 10px 14px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
}
.filter-form .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212,160,48,0.12);
}

.warnings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}
.warning-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}
.warning-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0; left: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--danger), #c0392b, var(--danger));
}
.warning-card:hover { transform: translateY(-8px); box-shadow: var(--card-shadow-hover); }
.warning-card:hover::before { height: 7px; }
.warning-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
}
.warning-date {
    display: flex; align-items: center; gap: 12px;
    color: #fff; font-weight: 700; font-size: 14px;
}
.warning-date i { color: var(--gold); font-size: 18px; }
.type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
    backdrop-filter: blur(5px);
}
.type-badge.attendance { background: rgba(243,156,18,0.25); color: #f1c40f; border: 1px solid rgba(243,156,18,0.3); }
.type-badge.recitation { background: rgba(52,152,219,0.25); color: #5dade2; border: 1px solid rgba(52,152,219,0.3); }
.type-badge.general { background: rgba(149,165,166,0.25); color: #bdc3c7; border: 1px solid rgba(149,165,166,0.3); }
.type-badge i { font-size: 11px; }

.warning-body { padding: 14px 16px; }
.warning-student { display: flex; align-items: center; gap: 15px; margin-bottom: 12px; }
.student-avatar {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 16px;
}
.student-info { flex: 1; }
.student-name { font-weight: 700; color: var(--primary); font-size: 13px; }
.student-number { font-size: 11.5px; color: #95a5a6; }

.warning-number {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 12px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border-radius: 9px;
    border: 1px solid rgba(24,43,72,0.06);
    font-size: 12.5px; color: var(--primary);
    font-weight: 600;
    margin-bottom: 12px;
}
.warning-number i { color: var(--gold); font-size: 14px; }

.warning-title {
    font-size: 14px; font-weight: 700;
    color: var(--primary); margin-bottom: 10px;
}

.warning-meta { display: flex; gap: 12px; flex-wrap: wrap; }
.meta-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; color: #6c7a89;
}
.meta-item i { color: var(--gold); font-size: 12px; }

.warning-footer {
    display: flex; gap: 10px;
    padding: 12px 16px;
    border-top: 1px solid #f0f4f8;
    background: #fafbfc;
}
.action-btn {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 8px 12px; border-radius: 9px;
    text-decoration: none; font-weight: 600; font-size: 12.5px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
}
.action-btn.view {
    background: linear-gradient(135deg, rgba(52,152,219,0.1) 0%, rgba(41,128,185,0.15) 100%);
    color: #3498db;
    border: 1px solid rgba(52,152,219,0.2);
}
.action-btn.view:hover {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52,152,219,0.3);
}
.action-btn.print {
    background: linear-gradient(135deg, rgba(39,174,96,0.1) 0%, rgba(30,140,80,0.15) 100%);
    color: #27ae60;
    border: 1px solid rgba(39,174,96,0.2);
}
.action-btn.print:hover {
    background: linear-gradient(135deg, #27ae60 0%, #219a52 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(39,174,96,0.3);
}
.action-btn.delete {
    background: linear-gradient(135deg, rgba(192,57,43,0.1) 0%, rgba(173,51,38,0.15) 100%);
    color: #c0392b;
    border: 1px solid rgba(192,57,43,0.2);
}
.action-btn.delete:hover {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(192,57,43,0.3);
}

.pagination-section { display: flex; justify-content: center; margin-top: 16px; }
.pagination-section .pagination { gap: 4px; }
.pagination-section .page-link {
    padding: 6px 11px; border-radius: 7px; font-size: 12.5px;
    border: 1px solid #e9ecef; color: var(--primary);
    transition: all 0.3s ease;
}
.pagination-section .page-link:hover { border-color: var(--gold); color: var(--gold); }
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
.empty-icon {
    width: 66px; height: 66px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08) 0%, rgba(42,69,112,0.12) 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
    font-size: 26px; color: var(--gold);
    border: 3px solid rgba(212,160,48,0.15);
}
.empty-state-card h5 { color: var(--primary); font-size: 15px; font-weight: 700; margin-bottom: 10px; }
.empty-state-card p { color: #95a5a6; margin-bottom: 16px; font-size: 13px; }

@media (max-width: 768px) {
    .warnings-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="header-text">
            <h4>الإنذارات</h4>
            <p>سجل ومتابعة إنذارات الطلاب</p>
        </div>
    </div>
    <a href="{{ route('supervisor.warnings.create') }}" class="add-btn">
        <i class="fas fa-plus-circle"></i>
        إضافة إنذار
    </a>
</div>

<div class="filter-card">
    <div class="filter-header">
        <i class="fas fa-filter"></i>
        <span>تصفية الإنذارات</span>
    </div>
    <form method="GET" class="filter-form">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="type" onchange="this.form.submit()">
                    <option value="">جميع الأنواع</option>
                    <option value="attendance" {{ request('type') == 'attendance' ? 'selected' : '' }}>إنذار حضور</option>
                    <option value="recitation" {{ request('type') == 'recitation' ? 'selected' : '' }}>إنذار تسميع</option>
                    <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>إنذار عام</option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="student_id" onchange="this.form.submit()">
                    <option value="">جميع الطلاب</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
</div>

@if($warnings->isEmpty())
    <div class="empty-state-card">
        <div class="empty-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h5>لا توجد إنذارات مسجلة</h5>
        <p>لم يتم إصدار أي إنذار للطلاب حتى الآن</p>
        <a href="{{ route('supervisor.warnings.create') }}" class="add-btn" style="display: inline-flex; margin: 0 auto;">
            <i class="fas fa-plus-circle"></i>
            إضافة إنذار جديد
        </a>
    </div>
@else
    <div class="warnings-grid">
        @foreach($warnings as $warning)
        <div class="warning-card">
            <div class="warning-header">
                <div class="warning-date">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $warning->issue_date->format('Y/m/d') }}
                </div>
                <div class="type-badge {{ $warning->type }}">
                    @if($warning->type === 'attendance')
                        <i class="fas fa-calendar-times"></i>حضور
                    @elseif($warning->type === 'recitation')
                        <i class="fas fa-microphone"></i>تسميع
                    @else
                        <i class="fas fa-exclamation-circle"></i>عام
                    @endif
                </div>
            </div>
            <div class="warning-body">
                <div class="warning-student">
                    <div class="student-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="student-info">
                        <div class="student-name">{{ $warning->student->user->name }}</div>
                        <div class="student-number">{{ $warning->student->student_number }}</div>
                    </div>
                </div>

                <div class="warning-number">
                    <i class="fas fa-hashtag"></i>
                    رقم الإنذار: {{ $warning->warning_number }}
                </div>

                <div class="warning-title">{{ $warning->title }}</div>

                <div class="warning-meta">
                    <span class="meta-item">
                        <i class="fas fa-user-tie"></i>
                        {{ $warning->issuedBy->name }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-calendar-day"></i>
                        {{ $warning->issue_day }}
                    </span>
                </div>
            </div>
            <div class="warning-footer">
                <a href="{{ route('supervisor.warnings.show', $warning->id) }}" class="action-btn view">
                    <i class="fas fa-eye"></i>
                    <span>عرض</span>
                </a>
                <a href="{{ route('supervisor.warnings.print', $warning->id) }}" class="action-btn print">
                    <i class="fas fa-print"></i>
                    <span>طباعة</span>
                </a>
                <form action="{{ route('supervisor.warnings.destroy', $warning->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإنذار؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn delete">
                        <i class="fas fa-trash-alt"></i>
                        <span>حذف</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-section">
        {{ $warnings->links() }}
    </div>
@endif
@endsection
