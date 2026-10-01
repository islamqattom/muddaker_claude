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
    <x-page-hero icon="fas fa-calendar-check">
        <h1 class="page-banner-title">سجل الحضور</h1>
        <p class="page-banner-sub">تابع حضور أبنائك وانتظامهم في الحلقات</p>
    </x-page-hero>

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
                <i class="fas fa-list"></i>
                <span>سجل الحضور ({{ $attendances->total() }})</span>
            </div>
        </div>
        <div class="card-body-custom p-0">
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
.header-icon.success {
    background: linear-gradient(135deg, #16835a, #27ae60);
    color: #fff;
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
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
}

.stat-content { flex: 1; position: relative; z-index: 1; }

.stat-number { color: #fff; margin-bottom: 3px; }
.stat-label { color: rgba(255,255,255,0.75); }
.stat-decoration { color: #fff; opacity: 0.1; }

.filter-field { display: flex; flex-direction: column; }

.filter-actions .btn { padding: 8px 16px; }

.empty-illustration {
    width: 66px;
    height: 66px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 26px;
    color: #d4a030;
}

.date-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #182B48;
    font-size: 12.5px;
}

.date-cell i { color: #d4a030; }

.day-badge { background: #f0f4f8; color: #7f8c8d; }

.status-badge.success { background: rgba(39,174,96,0.12); color: #16835a; }
.status-badge.danger { background: rgba(192,57,43,0.12); color: #a82e1c; }
.status-badge.warning { background: rgba(243,156,18,0.12); color: #d48806; }
.status-badge.info { background: rgba(52,152,219,0.12); color: #2475a8; }

.time-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #7f8c8d;
    font-size: 12.5px;
}

.time-badge i { color: #d4a030; }

.notes-text { color: #7f8c8d; font-size: 12.5px; }
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