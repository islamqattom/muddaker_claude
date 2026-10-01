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

<div class="portal-container">
    <x-page-hero icon="fas fa-chart-bar">
        <h1 class="page-banner-title">التقارير</h1>
        <p class="page-banner-sub">إحصائيات وإنجازات أبنائك</p>
    </x-page-hero>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $students->count() }}</div>
                <div class="stat-label">إجمالي الطلاب</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-microphone"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $totalSessions }}</div>
                <div class="stat-label">إجمالي الجلسات</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-quran"></i>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $passedSessions }}</div>
                <div class="stat-label">جلسات ناجحة</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-trophy"></i>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
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
                    <div class="empty-illustration">
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
                                <td class="fw-bold">{{ $data['sessions']['total'] }}</td>
                                <td><span class="text-success fw-bold">{{ $data['sessions']['passed'] }}</span></td>
                                <td><span class="text-danger fw-bold">{{ $data['sessions']['failed'] }}</span></td>
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
            <div class="filter-field custom-date-range" style="display: {{ request('period') == 'custom' ? 'flex' : 'none' }}; flex-direction: column;">
                <label class="filter-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ optional($fromDate)->format('Y-m-d') }}">
            </div>
            <div class="filter-field custom-date-range" style="display: {{ request('period') == 'custom' ? 'flex' : 'none' }}; flex-direction: column;">
                <label class="filter-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ optional($toDate)->format('Y-m-d') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    <span>تصفية</span>
                </button>
                <a href="{{ route('parent.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i>
                    <span>إعادة تعيين</span>
                </a>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="summary-card">
                <div class="summary-header">
                    <div class="summary-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span>ملخص الحضور</span>
                </div>
                <div class="summary-body">
                    @if($totalAttendances === 0)
                        <div class="empty-state">
                            <div class="empty-illustration">
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

        <div class="col-lg-6 mb-4">
            <div class="summary-card">
                <div class="summary-header">
                    <div class="summary-icon">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <span>ملخص جلسات التسميع</span>
                </div>
                <div class="summary-body">
                    @if($totalSessions === 0)
                        <div class="empty-state">
                            <div class="empty-illustration">
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

    @include('admin.reports._styles')

    @include('reports._written_report', [
        'breakdown'    => $breakdown,
        'periodLabel'  => $periodLabel,
        'studentLabel' => 'طالب',
        'pdfUrl'       => fn ($sid) => route('parent.reports.index', array_merge(request()->query(), ['student_id' => $sid, 'export' => 'pdf'])),
    ])
</div>

<style>
.stat-card.primary { background: linear-gradient(135deg, #182B48, #2A4570); }
.stat-card.success { background: linear-gradient(135deg, #16835a, #27ae60); }
.stat-card.warning { background: linear-gradient(135deg, #d48806, #f39c12); }
.stat-card.purple { background: linear-gradient(135deg, #7d3c98, #9b59b6); }

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

.progress-container {
    width: 100px;
    height: 10px;
    background: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar-custom {
    height: 100%;
    background: linear-gradient(90deg, #16835a, #27ae60);
    border-radius: 5px;
    transition: width 0.3s ease;
}

.rate-label {
    font-size: 12px;
    font-weight: 700;
    color: #16835a;
    margin-top: 6px;
    display: block;
}

.filter-field { display: flex; flex-direction: column; }

.summary-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
    height: 100%;
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
    color: #182B48;
    font-weight: 700;
    font-size: 14px;
}

.summary-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 13px;
}

.summary-body { padding: 16px; }

.progress-section { margin-bottom: 16px; }

.progress-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    min-width: 80px;
}

.label-text { color: #7f8c8d; font-size: 12.5px; }
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

.progress-fill.success { background: linear-gradient(90deg, #16835a, #27ae60); }
.progress-fill.danger { background: linear-gradient(90deg, #a82e1c, #c0392b); }
.progress-fill.warning { background: linear-gradient(90deg, #d48806, #f39c12); }
.progress-fill.info { background: linear-gradient(90deg, #2475a8, #3498db); }

.progress-percent {
    font-size: 12.5px;
    font-weight: 700;
    color: #182B48;
    min-width: 50px;
    text-align: left;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 14px;
    border-top: 1px solid #f0f4f8;
}

.summary-total span { color: #7f8c8d; font-size: 12.5px; }
.summary-total strong { font-size: 18px; color: #182B48; }
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