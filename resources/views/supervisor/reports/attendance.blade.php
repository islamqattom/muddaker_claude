@extends('layouts.app')
@section('title', 'تقرير الحضور والغياب')
@section('page-title', 'تقرير الحضور والغياب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير الحضور</li>
@endsection

@push('styles')
<style>
.att-report {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.att-report .page-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.att-report .page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.att-report .page-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 16px;
}

.att-report .page-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 6px 18px rgba(5, 150, 105, 0.4);
}

.att-report .page-title {
    color: white;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 4px;
}

.att-report .page-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    margin: 0;
}

.att-report .filter-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.att-report .filter-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.att-report .filter-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
}

.att-report .filter-header i {
    color: var(--gold);
}

.att-report .filter-body {
    padding: 20px;
}

.att-report .form-select, .att-report .form-control {
    border: 2px solid rgba(24, 43, 72, 0.08);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.att-report .form-select:focus, .att-report .form-control:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 160, 48, 0.15);
}

.att-report .btn-filter {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
}

.att-report .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(24, 43, 72, 0.3);
}

.att-report .btn-export {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
}

.att-report .btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(220, 38, 38, 0.3);
}

.att-report .stat-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
    transition: all 0.3s ease;
}

.att-report .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.att-report .stat-card-body {
    padding: 20px;
    text-align: center;
    position: relative;
}

.att-report .stat-card-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    border-radius: 0 0 3px 3px;
}

.att-report .stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 22px;
}

.att-report .stat-value {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 4px;
}

.att-report .stat-label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
}

.att-report .stat-primary .stat-card-body::before { background: linear-gradient(90deg, var(--navy), var(--gold)); }
.att-report .stat-primary .stat-value { color: var(--navy); }
.att-report .stat-primary .stat-icon { background: linear-gradient(135deg, rgba(24, 43, 72, 0.1), rgba(42, 69, 112, 0.15)); color: var(--navy); }

.att-report .stat-success .stat-card-body::before { background: #059669; }
.att-report .stat-success .stat-value { color: #059669; }
.att-report .stat-success .stat-icon { background: linear-gradient(135deg, rgba(5, 150, 105, 0.1), rgba(16, 185, 129, 0.15)); color: #059669; }

.att-report .stat-danger .stat-card-body::before { background: #dc2626; }
.att-report .stat-danger .stat-value { color: #dc2626; }
.att-report .stat-danger .stat-icon { background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(239, 68, 68, 0.15)); color: #dc2626; }

.att-report .stat-warning .stat-card-body::before { background: #d97706; }
.att-report .stat-warning .stat-value { color: #d97706; }
.att-report .stat-warning .stat-icon { background: linear-gradient(135deg, rgba(217, 119, 6, 0.1), rgba(245, 158, 11, 0.15)); color: #d97706; }

.att-report .stat-info .stat-card-body::before { background: #0891b2; }
.att-report .stat-info .stat-value { color: #0891b2; }
.att-report .stat-info .stat-icon { background: linear-gradient(135deg, rgba(8, 145, 178, 0.1), rgba(6, 182, 212, 0.15)); color: #0891b2; }

.att-report .stat-purple .stat-card-body::before { background: #7c3aed; }
.att-report .stat-purple .stat-value { color: #7c3aed; }
.att-report .stat-purple .stat-icon { background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(139, 92, 246, 0.15)); color: #7c3aed; }

.att-report .table-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.att-report .table-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.att-report .table-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.att-report .table-header h5 i {
    color: var(--gold);
}

.att-report .table-header .badge {
    background: rgba(255,255,255,0.2);
    color: white;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 20px;
}

.att-report .table {
    margin: 0;
}

.att-report .table thead th {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04) 0%, rgba(42, 69, 112, 0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 13px;
    color: var(--navy);
    border: none;
    text-align: center;
}

.att-report .table thead th:first-child {
    text-align: center;
}

.att-report .table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-color: rgba(24, 43, 72, 0.04);
    font-size: 14px;
    text-align: center;
}

.att-report .table tbody tr {
    transition: all 0.2s ease;
}

.att-report .table tbody tr:hover {
    background: linear-gradient(90deg, rgba(5, 150, 105, 0.04) 0%, rgba(16, 185, 129, 0.02) 100%);
}

.att-report .table tbody tr:hover td:first-child {
    border-right: 3px solid #059669;
}

.att-report .student-link {
    color: var(--navy);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.att-report .student-link:hover {
    color: var(--gold);
}

.att-report .status-badge {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.att-report .status-present {
    background: linear-gradient(135deg, rgba(5, 150, 105, 0.15), rgba(16, 185, 129, 0.2));
    color: #059669;
}

.att-report .status-absent {
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.15), rgba(239, 68, 68, 0.2));
    color: #dc2626;
}

.att-report .status-late {
    background: linear-gradient(135deg, rgba(217, 119, 6, 0.15), rgba(245, 158, 11, 0.2));
    color: #d97706;
}

.att-report .status-excused {
    background: linear-gradient(135deg, rgba(8, 145, 178, 0.15), rgba(6, 182, 212, 0.2));
    color: #0891b2;
}

.att-report .status-holiday {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.15), rgba(139, 92, 246, 0.2));
    color: #7c3aed;
}

.att-report .time-text {
    color: #6b7280;
    font-size: 13px;
}

.att-report .notes-text {
    color: #6b7280;
    font-size: 13px;
}

.att-report .empty-state {
    text-align: center;
    padding: 60px 20px;
}

.att-report .empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(5, 150, 105, 0.06), rgba(16, 185, 129, 0.08));
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.att-report .empty-icon i {
    font-size: 32px;
    color: #059669;
    opacity: 0.5;
}

.att-report .islamic-pattern {
    position: relative;
}

.att-report .islamic-pattern::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.att-report .progress-ring {
    width: 60px;
    height: 60px;
    margin: 0 auto 8px;
}

.att-report .progress-ring-circle {
    transition: stroke-dashoffset 0.35s;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
}

.att-report .progress-ring-text {
    font-size: 18px;
    font-weight: 800;
    fill: var(--navy);
}

@media (max-width: 768px) {
    .att-report .stat-card {
        margin-bottom: 12px;
    }
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="att-report">
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <h1 class="page-title">تقرير الحضور والغياب</h1>
                <p class="page-subtitle">عرض سجلات الحضور والغياب للطلاب مع إحصائيات كاملة</p>
            </div>
        </div>
    </div>

    <div class="filter-card mb-4 islamic-pattern">
        <div class="filter-header">
            <i class="fas fa-filter"></i>
            <h5>تصفية التقرير</h5>
        </div>
        <div class="filter-body">
            <form method="GET" action="{{ route('supervisor.reports.attendance') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">الطالب</label>
                    <select name="student_id" class="form-select">
                        <option value="">-- جميع الطلاب --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">الفترة</label>
                    <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                        <option value="month" {{ request('period', 'month') == 'month' ? 'selected' : '' }}>شهر</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                    </select>
                </div>
                <div class="col-md-2" id="fromDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label fw-bold">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="col-md-2" id="toDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label fw-bold">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @if(isset($attendances) && $attendances->isNotEmpty())
                <div class="col-md-1">
                    <a href="{{ route('supervisor.reports.attendance', array_merge(request()->except('export'), ['export' => 'pdf'])) }}" class="btn btn-export w-100" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    @if(isset($attendances))
    <div class="row g-4 mb-4">
        <div class="col-md-2 col-6">
            <div class="stat-card stat-primary">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">إجمالي السجلات</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card stat-success">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['present'] }}</div>
                    <div class="stat-label">حاضر</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card stat-danger">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['absent'] }}</div>
                    <div class="stat-label">غائب</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card stat-warning">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">{{ $stats['late'] }}</div>
                    <div class="stat-label">متأخر</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card stat-info">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-value">{{ $stats['excused'] }}</div>
                    <div class="stat-label">بعذر</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card stat-purple">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    @php
                        $percentage = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100) : 0;
                    @endphp
                    <div class="stat-value">{{ $percentage }}%</div>
                    <div class="stat-label">نسبة الحضور</div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h5>
                <i class="fas fa-calendar-alt"></i>
                سجلات الحضور
            </h5>
            <span class="badge">
                <i class="fas fa-calendar me-1"></i>
                {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>الحالة</th>
                        <th>وقت الحضور</th>
                        <th>ملاحظة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('supervisor.reports.student', $attendance->student->id) }}" class="student-link">
                                    {{ $attendance->student->user->name }}
                                </a>
                            </td>
                            <td>
                                @switch($attendance->status)
                                    @case('present')
                                        <span class="status-badge status-present">
                                            <i class="fas fa-check"></i>
                                            حاضر
                                        </span>
                                        @break
                                    @case('absent')
                                        <span class="status-badge status-absent">
                                            <i class="fas fa-times"></i>
                                            غائب
                                        </span>
                                        @break
                                    @case('late')
                                        <span class="status-badge status-late">
                                            <i class="fas fa-clock"></i>
                                            متأخر
                                        </span>
                                        @break
                                    @case('excused')
                                        <span class="status-badge status-excused">
                                            <i class="fas fa-file-alt"></i>
                                            بعذر
                                        </span>
                                        @break
                                    @case('holiday')
                                        <span class="status-badge status-holiday">
                                            <i class="fas fa-calendar-week"></i>
                                            عطلة
                                        </span>
                                        @break
                                    @default
                                        <span class="status-badge" style="background: rgba(107, 114, 128, 0.1); color: #6b7280;">غير محدد</span>
                                @endswitch
                            </td>
                            <td>
                                @if($attendance->arrival_time)
                                    <span class="time-text">{{ $attendance->arrival_time }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->notes)
                                    <span class="notes-text">{{ $attendance->notes }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $attendance->date->format('Y/m/d') }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h5 class="text-muted mb-2">لا توجد سجلات</h5>
                                    <p class="text-muted small">لا توجد سجلات حضور في الفترة المحددة</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

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
</script>
@endpush