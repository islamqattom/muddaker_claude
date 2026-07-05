@extends('layouts.app')
@section('title', 'تقرير التسميع')
@section('page-title', 'تقرير التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير التسميع</li>
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
.btn-export {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
    color: #fff; border-radius: 12px;
    text-decoration: none; font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
}
.btn-export:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(192,57,43,0.3); color: #fff; }
.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 18px;
    background: transparent;
    color: #7f8c8d; border: 2px solid #e9ecef; border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
}
.btn-back:hover { border-color: var(--primary); color: var(--primary); }
.btn-back i { color: var(--gold); }

.result-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
}
.result-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.result-header i { color: var(--gold); font-size: 20px; }
.count-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
    background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%);
    color: #fff;
}

.results-table {
    width: 100%; border-collapse: collapse;
}
.results-table thead {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
}
.results-table th {
    padding: 16px 18px;
    font-size: 12px; font-weight: 700;
    text-align: center;
    border: none;
}
.results-table td {
    padding: 16px 18px;
    font-size: 14px; color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.05);
    text-align: center;
    vertical-align: middle;
}
.results-table tbody tr { transition: all 0.3s ease; }
.results-table tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%); }
.results-table tbody tr:last-child td { border-bottom: none; }

.student-cell {
    display: flex; align-items: center; justify-content: center; gap: 12px;
}
.student-avatar {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 14px;
}
.student-name { font-weight: 700; color: var(--primary); }

.row-number {
    width: 35px; height: 35px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: var(--gold);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px;
    margin: 0 auto;
}

.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.status-badge.passed { background: rgba(39,174,96,0.15); color: var(--success); }
.status-badge.failed { background: rgba(192,57,43,0.15); color: var(--danger); }
.status-badge::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

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
}

.empty-state-card {
    text-align: center;
    padding: 70px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
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

@media (max-width: 992px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-quran"></i>
        </div>
        <div class="header-text">
            <h4>تقرير التسميع</h4>
            <p>نتائج جلسات التسميع والاختبارات</p>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="filter-header">
        <i class="fas fa-filter"></i>
        <span>بحث وتصفية النتائج</span>
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
                <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                    <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                </select>
            </div>
            <div class="col-md-2" id="fromDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate ? $fromDate->format('Y-m-d') : now()->startOfMonth()->toDateString()) }}">
            </div>
            <div class="col-md-2" id="toDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate ? $toDate->format('Y-m-d') : now()->toDateString()) }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn-search w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.reports.recitation', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn-export w-100" target="_blank">
                    <i class="fas fa-file-pdf"></i>
                </a>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.reports.index') }}" class="btn-back w-100">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<div class="result-card">
    <div class="result-header">
        <i class="fas fa-list"></i>
        <span>نتائج التسميع</span>
        <span class="count-badge">{{ $sessions->total() }} جلسة</span>
    </div>
    <div style="padding: 0;">
        @if($sessions->isEmpty())
            <div class="empty-state-card">
                <div class="empty-icon">
                    <i class="fas fa-quran"></i>
                </div>
                <h5>لا توجد جلسات تسميع</h5>
                <p>لم يتم تسجيل جلسات تسميع في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>الطالب</th>
                            <th>المشرف</th>
                            <th>الجزء</th>
                            <th>الصفحات</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $index => $session)
                        <tr>
                            <td>
                                <span class="row-number">{{ $sessions->firstItem() + $index }}</span>
                            </td>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <span class="student-name">{{ $session->student->user->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td style="font-weight: 600; color: var(--primary);">{{ $session->supervisor->name ?? '-' }}</td>
                            <td style="font-weight: 600;">{{ $session->parts_label }}</td>
                            <td>{{ $session->start_page ?? '-' }} - {{ $session->end_page ?? '-' }}</td>
                            <td style="font-weight: 600;">{{ $session->session_date?->format('Y/m/d') }}</td>
                            <td>
                                <span class="status-badge {{ $session->status }}">
                                    {{ $session->status_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-section" style="padding: 20px 25px; border-top: 1px solid rgba(24,43,72,0.05);">
                {{ $sessions->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

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
