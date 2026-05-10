@extends('layouts.app')

@section('title', 'التقارير')
@section('page-title', 'التقارير')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="header-text">
            <h4>التقارير</h4>
            <p>إحصائيات وإنجازات أبنائك</p>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $students->count() }}</div>
            <div class="stat-label">إجمالي الطلاب</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-user-graduate"></i>
        </div>
    </div>

    <div class="stat-card-modern secondary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-microphone"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $totalSessions }}</div>
            <div class="stat-label">إجمالي الجلسات</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-quran"></i>
        </div>
    </div>

    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $passedSessions }}</div>
            <div class="stat-label">جلسات ناجحة</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-trophy"></i>
        </div>
    </div>

    <div class="stat-card-modern warning">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $attendanceRate }}%</div>
            <div class="stat-label">نسبة الحضور</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-chart-pie"></i>
        </div>
    </div>
</div>

{{-- Per-Student Statistics --}}
<div class="table-card mb-4">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-users"></i>
            <span>إحصائيات كل طالب</span>
        </div>
    </div>
    <div class="card-body-custom p-0">
        @if(empty($studentsData))
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5>لا يوجد طلاب مرتبطون بحسابك</h5>
            </div>
        @else
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>المستوى</th>
                            <th>الجلسات</th>
                            <th>ناجح</th>
                            <th>راسب</th>
                            <th>حاضر</th>
                            <th>غائب</th>
                            <th>متأخر</th>
                            <th>نسبة الحضور</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsData as $data)
                        <tr>
                            <td>
                                <div class="user-info-cell">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span class="user-name">{{ $data['student']->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($data['student']->level)
                                    <span class="level-badge">{{ $data['student']->level->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $data['sessions']['total'] }}</td>
                            <td><span class="text-success fw-semibold">{{ $data['sessions']['passed'] }}</span></td>
                            <td><span class="text-danger fw-semibold">{{ $data['sessions']['failed'] }}</span></td>
                            <td><span class="text-success">{{ $data['attendance']['present'] }}</span></td>
                            <td><span class="text-danger">{{ $data['attendance']['absent'] }}</span></td>
                            <td><span class="text-warning">{{ $data['attendance']['late'] }}</span></td>
                            <td>
                                <div class="progress-container">
                                    <div class="progress-bar-custom" style="width:{{ $data['attendance']['rate'] }}%"></div>
                                </div>
                                <span class="rate-label">{{ $data['attendance']['rate'] }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Filter Card --}}
<div class="filter-card">
    <form method="GET" action="{{ route('parent.reports.index') }}" class="filter-grid">
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
                <option value="all" {{ request('period', 'month') == 'all' ? 'selected' : '' }}>الكل</option>
                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>الأسبوع</option>
                <option value="month" {{ request('period', 'month') == 'month' ? 'selected' : '' }}>الشهر</option>
                <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
            </select>
        </div>
        <div class="filter-field custom-date-range" style="display: {{ request('period') == 'custom' ? 'block' : 'none' }};">
            <label class="filter-label">من تاريخ</label>
            <input type="date" name="from_date" class="form-control" value="{{ optional($fromDate)->format('Y-m-d') }}">
        </div>
        <div class="filter-field custom-date-range" style="display: {{ request('period') == 'custom' ? 'block' : 'none' }};">
            <label class="filter-label">إلى تاريخ</label>
            <input type="date" name="to_date" class="form-control" value="{{ optional($toDate)->format('Y-m-d') }}">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter me-1"></i>تصفية
            </button>
            <a href="{{ route('parent.reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-redo me-1"></i>إعادة تعيين
            </a>
        </div>
    </form>
</div>

{{-- Summary Charts --}}
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="summary-card">
            <div class="summary-header">
                <i class="fas fa-calendar-check"></i>
                <span>ملخص الحضور</span>
            </div>
            <div class="summary-body">
                @if($totalAttendances === 0)
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <p>لا يوجد سجل حضور في هذه الفترة</p>
                    </div>
                @else
                    <div class="progress-section">
                        <div class="progress-item">
                            <div class="progress-label">
                                <span class="label-text">حاضر</span>
                                <span class="label-value">{{ $presentCount }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill success" style="width:{{ round($presentCount/$totalAttendances*100) }}%"></div>
                            </div>
                            <span class="progress-percent">{{ round($presentCount/$totalAttendances*100) }}%</span>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span class="label-text">غائب</span>
                                <span class="label-value">{{ $absentCount }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill danger" style="width:{{ round($absentCount/$totalAttendances*100) }}%"></div>
                            </div>
                            <span class="progress-percent">{{ round($absentCount/$totalAttendances*100) }}%</span>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span class="label-text">متأخر</span>
                                <span class="label-value">{{ $lateCount }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill warning" style="width:{{ round($lateCount/$totalAttendances*100) }}%"></div>
                            </div>
                            <span class="progress-percent">{{ round($lateCount/$totalAttendances*100) }}%</span>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span class="label-text">بعذر</span>
                                <span class="label-value">{{ $excusedCount }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill info" style="width:{{ round($excusedCount/$totalAttendances*100) }}%"></div>
                            </div>
                            <span class="progress-percent">{{ round($excusedCount/$totalAttendances*100) }}%</span>
                        </div>
                    </div>
                    <div class="summary-total">
                        <span>إجمالي السجلات</span>
                        <strong>{{ $totalAttendances }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="summary-card">
            <div class="summary-header">
                <i class="fas fa-microphone"></i>
                <span>ملخص جلسات التسميع</span>
            </div>
            <div class="summary-body">
                @if($totalSessions === 0)
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-microphone-alt"></i>
                        </div>
                        <p>لا توجد جلسات تسميع في هذه الفترة</p>
                    </div>
                @else
                    <div class="progress-section">
                        <div class="progress-item">
                            <div class="progress-label">
                                <span class="label-text">ناجح</span>
                                <span class="label-value">{{ $passedSessions }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill success" style="width:{{ round($passedSessions/$totalSessions*100) }}%"></div>
                            </div>
                            <span class="progress-percent">{{ round($passedSessions/$totalSessions*100) }}%</span>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span class="label-text">راسب</span>
                                <span class="label-value">{{ $failedSessions }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill danger" style="width:{{ round($failedSessions/$totalSessions*100) }}%"></div>
                            </div>
                            <span class="progress-percent">{{ round($failedSessions/$totalSessions*100) }}%</span>
                        </div>
                    </div>
                    <div class="summary-total">
                        <span>إجمالي الجلسات</span>
                        <strong>{{ $totalSessions }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #d4a030;
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card-modern {
    position: relative;
    padding: 25px;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    transition: all 0.3s ease;
}

.stat-card-modern:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.2); }
.stat-card-modern.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card-modern.secondary { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); }
.stat-card-modern.success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card-modern.warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }

.stat-card-bg {
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.stat-icon {
    width: 55px;
    height: 55px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    backdrop-filter: blur(5px);
    flex-shrink: 0;
}

.stat-content { flex: 1; position: relative; z-index: 1; }
.stat-number { font-size: 32px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 5px; }
.stat-label { font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 500; }
.stat-decoration { position: absolute; bottom: 10px; left: 15px; font-size: 40px; opacity: 0.15; color: #fff; }

.table-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
}

.table-title i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.card-body-custom { padding: 25px; }

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 28px;
    color: #d4a030;
}

.empty-state h5 { color: #182B48; font-size: 16px; font-weight: 700; margin-bottom: 5px; }
.empty-state p { color: #7f8c8d; font-size: 13px; }

.table-responsive { overflow-x: auto; }

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead { background: #f8f9fa; }

.modern-table th {
    padding: 16px 20px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 16px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.user-info-cell { display: flex; align-items: center; gap: 12px; }
.user-name { color: #182B48; font-weight: 600; }

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.level-badge {
    display: inline-block;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.progress-container {
    width: 80px;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar-custom {
    height: 100%;
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.rate-label {
    font-size: 12px;
    font-weight: 600;
    color: #27ae60;
    margin-top: 5px;
    display: block;
}

.filter-card {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    margin-bottom: 25px;
}

.filter-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr 2fr;
    gap: 15px;
    align-items: end;
}

@media (max-width: 992px) { .filter-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 576px) { .filter-grid { grid-template-columns: 1fr; } }

.filter-field { display: flex; flex-direction: column; }
.filter-label { color: #182B48; font-size: 13px; font-weight: 600; margin-bottom: 8px; }
.filter-actions { display: flex; gap: 10px; }

.summary-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    height: 100%;
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
    color: #182B48;
    font-weight: 700;
    font-size: 16px;
}

.summary-header i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.summary-body { padding: 25px; }

.progress-section { margin-bottom: 20px; }

.progress-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    min-width: 80px;
}

.label-text { color: #7f8c8d; font-size: 14px; }
.label-value { font-weight: 700; color: #182B48; }

.progress-track {
    flex: 1;
    height: 10px;
    background: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 0.3s ease;
}

.progress-fill.success { background: linear-gradient(90deg, #27ae60, #2ecc71); }
.progress-fill.danger { background: linear-gradient(90deg, #c0392b, #e74c3c); }
.progress-fill.warning { background: linear-gradient(90deg, #f39c12, #e67e22); }
.progress-fill.info { background: linear-gradient(90deg, #3498db, #2980b9); }

.progress-percent {
    font-size: 13px;
    font-weight: 700;
    color: #182B48;
    min-width: 45px;
    text-align: left;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #f0f4f8;
}

.summary-total span { color: #7f8c8d; font-size: 14px; }
.summary-total strong { font-size: 18px; color: #182B48; }
</style>

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    var customDateFields = document.querySelectorAll('.custom-date-range');

    if (period === 'custom') {
        customDateFields.forEach(function(field) {
            field.style.display = 'block';
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