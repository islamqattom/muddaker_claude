@extends('layouts.app')
@section('title', 'سجل الحضور')
@section('page-title', 'سجل الحضور')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">سجل الحضور</li>
@endsection
@section('content')

<div class="portal-container">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon success">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="header-text">
                <h4>سجل الحضور</h4>
                <p>تابع حضور أبنائك وانتظامهم في الحلقات</p>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['present'] }}</div>
                <div class="stat-label">حاضر</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-user-check"></i>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['absent'] }}</div>
                <div class="stat-label">غائب</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-user-times"></i>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['late'] }}</div>
                <div class="stat-label">متأخر</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-calendar-minus"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['excused'] }}</div>
                <div class="stat-label">بعذر</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('parent.attendance.index') }}" class="filter-grid">
            <div class="filter-field">
                <label class="filter-label">الطالب</label>
                <select name="student_id" class="form-select">
                    <option value="">كل الطلاب</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">الفترة</label>
                <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                    <option value="all" {{ $period == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>الأسبوع</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>الشهر</option>
                    <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>مخصص</option>
                </select>
            </div>
            <div class="filter-field custom-date-range" style="display: {{ $period == 'custom' ? 'flex' : 'none' }}; flex-direction: column;">
                <label class="filter-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="filter-field custom-date-range" style="display: {{ $period == 'custom' ? 'flex' : 'none' }}; flex-direction: column;">
                <label class="filter-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    <span>بحث</span>
                </button>
                <a href="{{ route('parent.attendance.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon">
                    <i class="fas fa-list"></i>
                </div>
                <span>سجل الحضور ({{ $attendances->total() }})</span>
            </div>
        </div>
        <div class="card-body-custom">
            @if($attendances->isEmpty())
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5>لا يوجد سجل حضور</h5>
                    <p>لم يتم تسجيل حضور في هذه الفترة</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>اليوم</th>
                                <th>الطالب</th>
                                <th>الحالة</th>
                                <th>وقت الوصول</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $index => $attendance)
                            <tr>
                                <td class="row-num">{{ $attendances->firstItem() + $index }}</td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar"></i>
                                        {{ $attendance->date->format('Y/m/d') }}
                                    </div>
                                </td>
                                <td><span class="day-badge">{{ $attendance->day }}</span></td>
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="user-name">{{ $attendance->student->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @switch($attendance->status)
                                        @case('present')
                                            <span class="status-badge success">
                                                <i class="fas fa-check"></i>
                                                <span>حاضر</span>
                                            </span>
                                            @break
                                        @case('absent')
                                            <span class="status-badge danger">
                                                <i class="fas fa-times"></i>
                                                <span>غائب</span>
                                            </span>
                                            @break
                                        @case('late')
                                            <span class="status-badge warning">
                                                <i class="fas fa-clock"></i>
                                                <span>متأخر</span>
                                            </span>
                                            @break
                                        @case('excused')
                                            <span class="status-badge info">
                                                <i class="fas fa-shield-alt"></i>
                                                <span>بعذر</span>
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($attendance->arrival_time)
                                        <span class="time-badge">
                                            <i class="fas fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i') }}
                                        </span>
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="pagination-wrapper">
                        {{ $attendances->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.portal-container {
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    box-shadow: 0 10px 30px rgba(24,43,72,0.2);
}

.header-icon.success {
    background: linear-gradient(135deg, #16835a, #27ae60);
    color: #fff;
}

.header-text h4 {
    color: #182B48;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.header-text p {
    color: #95a5a6;
    font-size: 14px;
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 992px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card {
    position: relative;
    padding: 28px;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 18px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}

.stat-card.success { background: linear-gradient(135deg, #16835a, #27ae60); }
.stat-card.danger { background: linear-gradient(135deg, #a82e1c, #c0392b); }
.stat-card.warning { background: linear-gradient(135deg, #d48806, #f39c12); }
.stat-card.info { background: linear-gradient(135deg, #2475a8, #3498db); }

.stat-glow {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.stat-icon-wrapper { position: relative; z-index: 1; }

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
}

.stat-content { flex: 1; position: relative; z-index: 1; }

.stat-number {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    margin-bottom: 6px;
}

.stat-label {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    font-weight: 500;
}

.stat-decoration {
    position: absolute;
    bottom: 12px;
    left: 18px;
    font-size: 45px;
    opacity: 0.1;
    color: #fff;
}

.filter-card {
    background: #fff;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
    margin-bottom: 30px;
}

.filter-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr auto;
    gap: 18px;
    align-items: end;
}

@media (max-width: 992px) { .filter-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 576px) { .filter-grid { grid-template-columns: 1fr; } }

.filter-field { display: flex; flex-direction: column; }

.filter-label {
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.filter-actions .btn {
    padding: 12px 20px;
}

.table-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
}

.table-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.card-body-custom { padding: 0; }

.empty-state {
    text-align: center;
    padding: 70px 30px;
}

.empty-illustration {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 40px;
    color: #d4a030;
}

.empty-state h5 {
    color: #182B48;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

.empty-state p { color: #95a5a6; }

.table-responsive { overflow-x: auto; }

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead { background: #f8f9fa; }

.modern-table th {
    padding: 18px 22px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 18px 22px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.row-num {
    color: #bdc3c7;
    font-weight: 700;
    font-size: 13px;
}

.date-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #182B48;
}

.date-cell i { color: #d4a030; }

.day-badge {
    background: #f0f4f8;
    color: #7f8c8d;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.user-info-cell { display: flex; align-items: center; gap: 14px; }

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.user-name { color: #182B48; font-weight: 600; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.success { background: rgba(39,174,96,0.12); color: #16835a; }
.status-badge.danger { background: rgba(192,57,43,0.12); color: #a82e1c; }
.status-badge.warning { background: rgba(243,156,18,0.12); color: #d48806; }
.status-badge.info { background: rgba(52,152,219,0.12); color: #2475a8; }

.time-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #7f8c8d;
    font-size: 13px;
}

.time-badge i { color: #d4a030; }

.notes-text { color: #7f8c8d; font-size: 13px; }

.table-footer {
    padding: 20px 28px;
    border-top: 1px solid #f0f4f8;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination { margin: 0; gap: 6px; }
.pagination-wrapper .page-link {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 14px;
    border: 1px solid #e9ecef;
    color: #182B48;
}
.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-color: #182B48;
    color: #fff;
}
.pagination-wrapper .page-item.disabled .page-link { color: #bdc3c7; }
</style>

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    var customDateFields = document.querySelectorAll('.custom-date-range');

    if (period === 'custom') {
        customDateFields.forEach(function(field) {
            field.style.display = 'flex';
        });
    } else {
        customDateFields.forEach(function(field) {
            field.style.display = 'none';
        });
    }
}
</script>
@endpush
@endsection