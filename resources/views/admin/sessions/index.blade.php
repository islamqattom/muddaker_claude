@extends('layouts.app')

@section('title', 'جلسات التسميع')
@section('page-title', 'جلسات التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">جلسات التسميع</li>
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
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #6c7a89; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.filter-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 30px;
}
.filter-header {
    display: flex; align-items: center; gap: 12px;
    padding: 18px 25px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; color: var(--primary);
}
.filter-header i { color: var(--gold); font-size: 18px; }
.filter-header span::before { content: '◆'; color: var(--gold); margin-left: 8px; font-size: 10px; }
.filter-form { padding: 25px; }
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
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; border: none; border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
}
.btn-search:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(24,43,72,0.3); }

.table-container {
    background: #fff;
    border-radius: 18px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
}
.table-header {
    display: flex; align-items: center; gap: 12px;
    padding: 18px 25px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
}
.table-header i { color: var(--gold); font-size: 18px; }
.table-header h5 { margin: 0; font-weight: 700; }
.table {
    margin: 0;
    text-align: center;
}
.table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.08) 100%);
    color: var(--primary);
    font-weight: 700;
    padding: 16px 12px;
    border: none;
    font-size: 14px;
}
.table tbody td {
    padding: 16px 12px;
    vertical-align: middle;
    border-color: #f0f4f8;
    color: var(--primary);
    font-size: 14px;
}
.table tbody tr:hover {
    background: linear-gradient(135deg, rgba(24,43,72,0.02) 0%, rgba(42,69,112,0.04) 100%);
}
.table tbody tr td:first-child { border-right: 3px solid var(--gold); }

.date-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-weight: 700;
}
.date-cell i { color: var(--gold); }

.type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 25px;
    font-size: 12px; font-weight: 600;
}
.type-badge.new_memorization { background: rgba(39,174,96,0.15); color: #27ae60; }
.type-badge.review { background: rgba(52,152,219,0.15); color: #3498db; }
.type-badge.unmastered { background: rgba(243,156,18,0.15); color: #f39c12; }
.type-badge.recitation { background: rgba(155,89,182,0.15); color: #8e44ad; }
.type-badge.mafsil { background: rgba(231,76,60,0.15); color: #c0392b; }
.type-badge i { font-size: 10px; }

.participant-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.participant-name { font-weight: 700; font-size: 14px; }
.participant-role { font-size: 11px; color: #95a5a6; }

.pages-cell {
    font-weight: 700;
    color: var(--primary);
}
.pages-cell .pages-range { color: var(--gold-dark); }

.score-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 700;
}
.score-cell i { color: var(--gold); }
.score-cell .score { color: var(--gold-dark); font-size: 15px; }

.errors-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 700;
}
.errors-cell i { color: var(--danger); }
.errors-cell .errors-count { color: var(--danger); font-size: 15px; }

.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 16px; border-radius: 25px;
    font-size: 12px; font-weight: 700;
}
.status-badge.passed, .status-badge.success { background: rgba(39,174,96,0.15); color: var(--success); }
.status-badge.passed i, .status-badge.success i { color: var(--success); }
.status-badge.failed, .status-badge.danger { background: rgba(192,57,43,0.15); color: var(--danger); }
.status-badge.failed i, .status-badge.danger i { color: var(--danger); }
.status-badge.in_progress, .status-badge.warning { background: rgba(243,156,18,0.15); color: var(--warning); }
.status-badge.in_progress i, .status-badge.warning i { color: var(--warning); }
.status-badge.not_recite, .status-badge.secondary { background: rgba(149,165,166,0.15); color: #95a5a6; }
.status-badge.incomplete { background: rgba(149,165,166,0.15); color: #95a5a6; }

.action-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 8px 14px; border-radius: 10px;
    text-decoration: none; font-weight: 600; font-size: 12px;
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
}

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
    text-align: center;
    padding: 70px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
}
.empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08) 0%, rgba(42,69,112,0.12) 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 25px;
    font-size: 42px; color: var(--gold);
    border: 3px solid rgba(212,160,48,0.15);
}
.empty-state-card h5 { color: var(--primary); font-size: 20px; font-weight: 700; margin-bottom: 10px; }
.empty-state-card p { color: #95a5a6; margin-bottom: 25px; font-size: 15px; }

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
    .table { font-size: 12px; }
    .table thead th, .table tbody td { padding: 10px 8px; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-quran"></i>
        </div>
        <div class="header-text">
            <h4>جلسات التسميع</h4>
            <p>متابعة وتسجيل جلسات تحفيظ القرآن الكريم</p>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="filter-header">
        <i class="fas fa-filter"></i>
        <span>بحث وتصفية الجلسات</span>
    </div>
    <form method="GET" class="filter-form">
        <div class="row g-3">
            <div class="col-md-2">
                <select name="supervisor_id" class="form-select" id="supervisorSelect" onchange="filterStudents()">
                    <option value="">-- جميع المشرفين --</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->user_id }}" {{ request('supervisor_id') == $supervisor->user_id ? 'selected' : '' }}>
                            {{ $supervisor->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="student_id" class="form-select" id="studentSelect">
                    <option value="">-- جميع الطلاب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}"
                                data-supervisor-id="{{ $student->supervisor_id }}"
                                {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="new_memorization" {{ request('type') == 'new_memorization' ? 'selected' : '' }}>حفظ جديد</option>
                    <option value="review" {{ request('type') == 'review' ? 'selected' : '' }}>مراجعة</option>
                    <option value="unmastered" {{ request('type') == 'unmastered' ? 'selected' : '' }}>حفظ غير متقن</option>
                    <option value="recitation" {{ request('type') == 'recitation' ? 'selected' : '' }}>سرد</option>
                    <option value="mafsil" {{ request('type') == 'mafsil' ? 'selected' : '' }}>مفصل</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                    <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                </select>
            </div>
            <div class="col-md-2" id="fromDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}">
            </div>
            <div class="col-md-2" id="toDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn-search w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>

@if($sessions->isEmpty())
    <div class="empty-state-card">
        <div class="empty-icon">
            <i class="fas fa-quran"></i>
        </div>
        <h5>لا توجد جلسات تسميع مسجلة</h5>
        <p>قم بتسجيل جلسة تسميع جديدة لطالب لتحفيظ القرآن الكريم</p>
    </div>
@else
    <div class="table-container">
        <div class="table-header">
            <i class="fas fa-list"></i>
            <h5>قائمة الجلسات</h5>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>نوع الجلسة</th>
                        <th>الطالب</th>
                        <th>المشرف</th>
                        <th>الصفحات / الأجزاء</th>
                        <th>عدد الصفحات</th>
                        <th>الأخطاء</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                    <tr>
                        <td>
                            <div class="date-cell">
                                <i class="fas fa-calendar-alt"></i>
                                {{ $session->session_date->format('Y/m/d') }}
                            </div>
                        </td>
                        <td>
                            <span class="type-badge {{ $session->type }}">
                                @if($session->type === 'new_memorization')
                                    <i class="fas fa-star"></i>حفظ جديد
                                @elseif($session->type === 'review')
                                    <i class="fas fa-redo"></i>مراجعة
                                @elseif($session->type === 'unmastered')
                                    <i class="fas fa-exclamation-circle"></i>غير متقن
                                @elseif($session->type === 'recitation')
                                    <i class="fas fa-book-open"></i>سرد
                                @else
                                    <i class="fas fa-layer-group"></i>مفصل
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="participant-cell">
                                <span class="participant-name">{{ $session->student->user->name }}</span>
                                <span class="participant-role">الطالب</span>
                            </div>
                        </td>
                        <td>
                            <div class="participant-cell">
                                <span class="participant-name">{{ $session->supervisor->name ?? '—' }}</span>
                                <span class="participant-role">المشرف</span>
                            </div>
                        </td>
                        <td>
                            <span class="pages-cell">
                                @if($session->type === 'mafsil')
                                    @php $partNumbers = $session->pages->pluck('page_number')->toArray(); @endphp
                                    {{ count($partNumbers) > 0 ? 'أجزاء ' . implode(', ', $partNumbers) : ($session->part ? 'جزء ' . $session->part->part_number : '—') }}
                                @elseif(in_array($session->type, ['unmastered', 'review', 'new_memorization']))
                                    {{ $session->start_page === $session->end_page ? 'صفحة ' . $session->start_page : 'صفحات ' . $session->start_page . '-' . $session->end_page }}
                                @else
                                    {{ $session->part ? 'جزء ' . $session->part->part_number : 'صفحات ' . $session->start_page . '-' . $session->end_page }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $session->total_pages }}</td>
                        <td>
                            @if($session->total_errors > 0)
                                <div class="errors-cell">
                                    <i class="fas fa-times-circle"></i>
                                    <span class="errors-count">{{ $session->total_errors }}</span>
                                </div>
                            @else
                                <span style="color: var(--success);">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $session->status_color }}">
                                {{ $session->status_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.sessions.show', $session->id) }}" class="action-btn view">
                                <i class="fas fa-eye"></i>
                                <span>عرض</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-section">
        {{ $sessions->withQueryString()->links() }}
    </div>
@endif

@push('scripts')
<script>
function handlePeriodChange() {
    const period = document.getElementById('periodSelect').value;
    const fromDateCol = document.getElementById('fromDateCol');
    const toDateCol = document.getElementById('toDateCol');

    if (period === 'custom') {
        fromDateCol.style.display = '';
        toDateCol.style.display = '';
    } else {
        fromDateCol.style.display = 'none';
        toDateCol.style.display = 'none';
    }
}

function filterStudents() {
    const supervisorSelect = document.getElementById('supervisorSelect');
    const studentSelect = document.getElementById('studentSelect');
    const selectedSupervisorId = supervisorSelect.value;

    const studentOptions = studentSelect.querySelectorAll('option');

    studentOptions.forEach(option => {
        if (option.value === '') return;

        const studentSupervisorId = option.getAttribute('data-supervisor-id');

        if (!selectedSupervisorId || studentSupervisorId === selectedSupervisorId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });

    studentSelect.value = '';
}

document.addEventListener('DOMContentLoaded', function() {
    filterStudents();
});
</script>
@endpush
@endsection