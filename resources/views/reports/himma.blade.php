@extends('layouts.app')
@section('title', 'تقرير يوم الهمة')
@section('page-title', 'تقرير يوم الهمة')
@section('role-label', $roleLabel)

@section('sidebar-menu')
@include($sidebar)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($reportsIndexRoute) }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير يوم الهمة</li>
@endsection

@php
    $periodLabels = ['today' => 'اليوم', 'week' => 'هذا الأسبوع', 'month' => 'هذا الشهر', 'quarter' => 'هذا الربع', 'half' => 'هذا النصف', 'year' => 'هذه السنة', 'all' => 'كل الفترات', 'custom' => 'فترة مخصصة'];
    $periodLabel = $periodLabels[$period] ?? 'كل الفترات';

    $queryParams = request()->except('export', 'page');
    $pdfUrl   = route($routePrefix . '.himma', array_merge($queryParams, ['export' => 'pdf']));
    $excelUrl = route($routePrefix . '.himma', array_merge($queryParams, ['export' => 'excel']));
@endphp

@section('content')
@include('admin.reports._styles')

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-mosque"></i></div>
        <div class="rpt-header-text">
            <h4>تقرير يوم الهمة</h4>
            <p>نظرة شاملة على جميع أيام الهمة وترتيب الطلاب</p>
        </div>
    </div>
    <div class="rpt-actions">
        <a href="{{ $pdfUrl }}" target="_blank" class="rpt-btn rpt-btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ $excelUrl }}" class="rpt-btn rpt-btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        @if(isset($himmaIndexRoute))
            <a href="{{ route($himmaIndexRoute) }}" class="rpt-btn rpt-btn-ghost"><i class="fas fa-arrow-right"></i> أيام الهمة</a>
        @endif
    </div>
</div>

<div class="rpt-filter">
    <div class="rpt-filter-header"><i class="fas fa-filter"></i> <span>بحث وتصفية</span></div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                @if($showSupervisorFilter)
                    <div class="col-md-2">
                        <label class="form-label">المشرف</label>
                        <input type="text" name="supervisor_name" class="form-control" value="{{ request('supervisor_name') }}" placeholder="اسم المشرف">
                    </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label">الطالب</label>
                    <select name="student_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($studentOptions as $student)
                            <option value="{{ $student->id }}" {{ (string) request('student_id') === (string) $student->id ? 'selected' : '' }}>{{ $student->user->name ?? $student->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الفترة</label>
                    <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                        <option value="all"    {{ request('period') == 'all' ? 'selected' : '' }}>الكل</option>
                        <option value="today"  {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                        <option value="week"   {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                        <option value="month"  {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                    </select>
                </div>
                <div class="col-md-2" id="fromDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate ? $fromDate->format('Y-m-d') : now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="col-md-2" id="toDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate ? $toDate->format('Y-m-d') : now()->toDateString()) }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="rpt-btn rpt-btn-primary flex-fill"><i class="fas fa-search"></i> بحث</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="rpt-kpi-grid">
    <div class="rpt-kpi" style="--kpi-color:#182B48;--kpi-bg:rgba(24,43,72,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-list"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_sessions']) }}</div>
        <div class="rpt-kpi-label">إجمالي الجلسات</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#3498db;--kpi-bg:rgba(52,152,219,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_students']) }}</div>
        <div class="rpt-kpi-label">عدد الطلاب</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#d4a030;--kpi-bg:rgba(212,160,48,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_days']) }}</div>
        <div class="rpt-kpi-label">عدد الأيام</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#16a085;--kpi-bg:rgba(22,160,133,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-book-open"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_parts']) }}</div>
        <div class="rpt-kpi-label">إجمالي الأجزاء</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#e74c3c;--kpi-bg:rgba(231,76,60,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_errors']) }}</div>
        <div class="rpt-kpi-label">إجمالي الأخطاء</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#f39c12;--kpi-bg:rgba(243,156,18,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-question-circle"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_hesitations']) }}</div>
        <div class="rpt-kpi-label">إجمالي الترددات</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#8e44ad;--kpi-bg:rgba(142,68,173,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-star"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['avg_score'], 2) }}</div>
        <div class="rpt-kpi-label">متوسط النقاط / جلسة</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-bottom:16px">
    <div class="rpt-chart-card" style="margin-bottom:0">
        <h6><i class="fas fa-chart-line"></i> الجلسات والأخطاء حسب اليوم</h6>
        @if(count($charts['days']['labels']))
            <div class="rpt-chart-wrap"><canvas id="himmaDaysChart"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات لهذه الفترة</p></div>
        @endif
    </div>
    <div class="rpt-chart-card" style="margin-bottom:0">
        <h6><i class="fas fa-chart-bar"></i> عدد الأجزاء لكل طالب</h6>
        @if(count($charts['leaderboard']['labels']))
            <div class="rpt-chart-wrap"><canvas id="himmaPartsChart"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات لهذه الفترة</p></div>
        @endif
    </div>
    <div class="rpt-chart-card" style="margin-bottom:0">
        <h6><i class="fas fa-award"></i> نقاط الدقة لكل طالب</h6>
        @if(count($charts['precision']['labels']))
            <div class="rpt-chart-wrap"><canvas id="himmaPrecisionChart"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات لهذه الفترة</p></div>
        @endif
    </div>
</div>

<div class="rpt-table-card" style="margin-bottom:24px;">
    <div class="rpt-table-header">
        <h5><i class="fas fa-calendar-alt"></i> ملخص الأيام</h5>
        <span class="rpt-count-badge"><i class="fas fa-calendar-day"></i> {{ $days->count() }} يوم</span>
    </div>
    @if($days->isEmpty())
        <div class="rpt-empty"><p>لا توجد أيام في هذه الفترة</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>اليوم</th>
                        <th data-sort="num">الجلسات</th>
                        <th data-sort="num">الطلاب</th>
                        <th data-sort="num">الأجزاء</th>
                        <th data-sort="num">الأخطاء</th>
                        <th data-sort="num">الترددات</th>
                        <th data-sort="num">النقاط</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                        <tr>
                            <td>{{ $day['date'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('l') }}</td>
                            <td>{{ $day['sessions'] }}</td>
                            <td>{{ $day['students'] }}</td>
                            <td>{{ $day['parts'] }}</td>
                            <td style="color:#c0392b;font-weight:700;">{{ $day['errors'] }}</td>
                            <td style="color:#d97706;font-weight:700;">{{ $day['hesitations'] }}</td>
                            <td style="font-weight:700;">{{ $day['score'] }}</td>
                            <td><a href="{{ route($routePrefix . '.himma.day', ['date' => $day['date']]) }}" class="rpt-btn rpt-btn-ghost" style="padding:4px 10px;font-size:12px;"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-trophy"></i> ترتيب الطلاب</h5>
        <span class="rpt-count-badge"><i class="fas fa-user-graduate"></i> {{ $students->count() }} طالب</span>
    </div>
    @if($students->isEmpty())
        <div class="rpt-empty"><p>لا توجد بيانات لعرضها</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th data-sort="num">الجلسات</th>
                        <th data-sort="num">الأجزاء</th>
                        <th data-sort="num">الأخطاء</th>
                        <th data-sort="num">الترددات</th>
                        <th data-sort="num">النقاط</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $row)
                        <tr>
                            <td><span class="rpt-badge" style="background:rgba(212,160,48,0.15);color:#182B48;">{{ $i + 1 }}</span></td>
                            <td style="text-align:right;font-weight:700;">{{ $row['name'] }}</td>
                            <td>{{ $row['sessions'] }}</td>
                            <td>{{ $row['parts'] }}</td>
                            <td style="color:#c0392b;font-weight:700;">{{ $row['errors'] }}</td>
                            <td style="color:#d97706;font-weight:700;">{{ $row['hesitations'] }}</td>
                            <td style="font-weight:800;">{{ $row['score'] }}</td>
                            <td>
                                @if($row['student_id'])
                                    <a href="{{ route($routePrefix . '.himma.student', ['student' => $row['student_id']]) }}" class="rpt-btn rpt-btn-ghost" style="padding:4px 10px;font-size:12px;"><i class="fas fa-user"></i> تقرير الطالب</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    document.getElementById('fromDateCol').style.display = period === 'custom' ? '' : 'none';
    document.getElementById('toDateCol').style.display   = period === 'custom' ? '' : 'none';
}
(function () {
    if (!window.Chart) return;
    var days = @json($charts['days']);
    var parts = @json($charts['leaderboard']);
    var precision = @json($charts['precision']);
    var palette = ['#182B48', '#d4a030', '#27ae60', '#e74c3c', '#2980b9', '#8e44ad', '#16a085', '#f39c12'];

    var elDays = document.getElementById('himmaDaysChart');
    if (elDays) {
        new Chart(elDays, {
            type: 'bar',
            data: {
                labels: days.labels,
                datasets: [
                    { type: 'bar', label: 'الجلسات', data: days.sessions, backgroundColor: 'rgba(24,43,72,0.65)', borderRadius: 6, yAxisID: 'y' },
                    { type: 'line', label: 'الأخطاء', data: days.errors, borderColor: '#e74c3c', backgroundColor: 'rgba(231,76,60,0.15)', borderWidth: 2, tension: 0.35, pointRadius: 3, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, position: 'right', ticks: { font: { size: 10 }, precision: 0 }, grid: { color: '#f1f5f9' } },
                    y1: { beginAtZero: true, position: 'left', ticks: { font: { size: 10 }, precision: 0 }, grid: { display: false } }
                }
            }
        });
    }

    var elParts = document.getElementById('himmaPartsChart');
    if (elParts) {
        new Chart(elParts, {
            type: 'bar',
            data: { labels: parts.labels, datasets: [{ label: 'عدد الأجزاء', data: parts.data, backgroundColor: palette, borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    var elPrec = document.getElementById('himmaPrecisionChart');
    if (elPrec) {
        new Chart(elPrec, {
            type: 'bar',
            data: { labels: precision.labels, datasets: [{ label: 'النقاط', data: precision.data, backgroundColor: '#d4a030', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }
})();
</script>
@endpush
