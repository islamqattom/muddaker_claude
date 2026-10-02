@extends('layouts.app')
@section('title', 'تقرير الحضور')
@section('page-title', 'تقرير الحضور')
@section('role-label', $roleLabel)

@section('sidebar-menu')
@include($sidebar)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($reportsIndexRoute) }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير الحضور</li>
@endsection

@php
    $statusLabels = ['present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'معذور', 'holiday' => 'عطلة'];
    $periodLabels = ['today' => 'اليوم', 'week' => 'هذا الأسبوع', 'month' => 'هذا الشهر', 'quarter' => 'هذا الربع', 'half' => 'هذا النصف', 'year' => 'هذه السنة', 'all' => 'كل الفترات', 'custom' => 'فترة مخصصة'];

    $queryParams = request()->except('export', 'page', 'rec_page', 'att_page');
    $pdfUrl   = route($routePrefix . '.attendance', array_merge($queryParams, ['export' => 'pdf']));
    $excelUrl = route($routePrefix . '.attendance', array_merge($queryParams, ['export' => 'excel']));
    $printUrl = route($routePrefix . '.attendance', array_merge($queryParams, ['export' => 'print']));

    $prev = $prevStats ?? null;
    $d = fn ($key) => $prev !== null ? round(($stats[$key] ?? 0) - ($prev[$key] ?? 0), 1) : null;

    $selSupervisor = $showSupervisorFilter && request('supervisor_id') ? ($supervisors->firstWhere('user_id', (int) request('supervisor_id'))?->user?->name) : null;
    $selStudent    = request('student_id') ? ($students->firstWhere('id', (int) request('student_id'))?->user?->name) : null;
    $hasFilters    = $selSupervisor || $selStudent || request('status') || (request('period') && request('period') !== 'all');
@endphp

@section('content')
@include('admin.reports._styles', ['accentColor' => '#059669', 'accentColor2' => '#10b981'])

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="rpt-header-text">
            <h4>تقرير الحضور</h4>
            <p>سجلات الحضور والانصراف</p>
        </div>
    </div>
    <div class="rpt-actions">
        <a href="{{ $pdfUrl }}" target="_blank" class="rpt-btn rpt-btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ $excelUrl }}" class="rpt-btn rpt-btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        <a href="{{ $printUrl }}" target="_blank" class="rpt-btn rpt-btn-ghost"><i class="fas fa-print"></i> طباعة</a>
    </div>
</div>

<div class="rpt-filter">
    <div class="rpt-filter-header">
        <i class="fas fa-filter"></i>
        <span>بحث وتصفية السجلات</span>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                @if($showSupervisorFilter)
                    <div class="col-md-2">
                        <label class="form-label">المشرف</label>
                        <select name="supervisor_id" class="form-select" id="supervisorSelect" onchange="filterStudents(true)">
                            <option value="">الكل</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->user_id }}" {{ request('supervisor_id') == $supervisor->user_id ? 'selected' : '' }}>{{ $supervisor->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">الطالب</label>
                    <select name="student_id" class="form-select" id="studentSelect">
                        <option value="">الكل</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" data-supervisor-id="{{ $student->supervisor_id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>{{ $student->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الفترة</label>
                    <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                        <option value="all"     {{ request('period') == 'all' ? 'selected' : '' }}>الكل</option>
                        <option value="today"   {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                        <option value="week"    {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                        <option value="month"   {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                        <option value="custom"  {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
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

@if($hasFilters)
<div class="rpt-chips">
    <span class="rpt-chips-label">الفلاتر النشطة:</span>
    @if($selSupervisor)<span class="rpt-chip"><i class="fas fa-user-tie"></i> {{ $selSupervisor }}</span>@endif
    @if($selStudent)<span class="rpt-chip"><i class="fas fa-user-graduate"></i> {{ $selStudent }}</span>@endif
    @if(request('status'))<span class="rpt-chip"><i class="fas fa-flag"></i> {{ $statusLabels[request('status')] ?? request('status') }}</span>@endif
    @if(request('period'))<span class="rpt-chip"><i class="fas fa-calendar"></i> {{ $periodLabels[request('period')] ?? request('period') }}</span>@endif
    <a href="{{ route($routePrefix . '.attendance') }}" class="rpt-chip rpt-chip-reset"><i class="fas fa-times"></i> مسح الكل</a>
</div>
@endif

<div class="rpt-kpi-grid">
    @include('reports._kpi', ['label' => 'إجمالي السجلات', 'value' => $stats['total'], 'icon' => 'fa-list', 'color' => '#182B48', 'bg' => 'rgba(24,43,72,0.10)', 'delta' => $d('total'), 'deltaSuffix' => ''])
    @include('reports._kpi', ['label' => 'الحضور', 'value' => $stats['attendance'], 'icon' => 'fa-check-circle', 'color' => '#059669', 'bg' => 'rgba(5,150,105,0.10)', 'delta' => $d('attendance'), 'deltaSuffix' => ''])
    @include('reports._kpi', ['label' => 'غائب', 'value' => $stats['absent'], 'icon' => 'fa-times-circle', 'color' => '#c0392b', 'bg' => 'rgba(192,57,43,0.10)', 'delta' => $d('absent'), 'deltaSuffix' => '', 'deltaInvert' => true])
    @include('reports._kpi', ['label' => 'متأخر', 'value' => $stats['late'], 'icon' => 'fa-clock', 'color' => '#f39c12', 'bg' => 'rgba(243,156,18,0.10)', 'delta' => $d('late'), 'deltaSuffix' => '', 'deltaInvert' => true])
    @include('reports._kpi', ['label' => 'بعذر', 'value' => $stats['excused'], 'icon' => 'fa-file-alt', 'color' => '#3498db', 'bg' => 'rgba(52,152,219,0.10)', 'delta' => $d('excused'), 'deltaSuffix' => ''])
    @include('reports._kpi', ['label' => 'نسبة الحضور', 'value' => $stats['attendance_rate'] . '%', 'icon' => 'fa-percentage', 'color' => '#8e44ad', 'bg' => 'rgba(142,68,173,0.10)', 'delta' => $d('attendance_rate')])
</div>

<div class="rpt-grid-2">
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-line"></i> اتجاه الحضور اليومي</h6>
        @if(!empty($trend))
            <div class="rpt-chart-wrap"><canvas id="attTrend"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات في هذه الفترة</p></div>
        @endif
    </div>
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-pie"></i> توزيع حالات الحضور</h6>
        @if(collect($statusDistribution)->sum('count') > 0)
            <div class="rpt-chart-wrap"><canvas id="attStatusDist"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد سجلات حضور</p></div>
        @endif
    </div>
</div>

@include('reports._student_summary', ['summary' => $summary, 'showRecitation' => false, 'showAttendance' => true, 'studentRouteName' => $studentRouteName])

<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-list"></i> سجلات الحضور</h5>
        <span class="rpt-count-badge"><i class="fas fa-database"></i> {{ $attendances->total() }} سجل</span>
    </div>
    @if($attendances->isEmpty())
        <div class="rpt-empty">
            <div class="rpt-empty-icon"><i class="fas fa-calendar-minus"></i></div>
            <h5>لا توجد سجلات حضور</h5>
            <p>لم يتم تسجيل حضور في هذه الفترة</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort="num">#</th>
                        <th data-sort>الطالب</th>
                        <th data-sort>المشرف</th>
                        <th data-sort>التاريخ</th>
                        <th data-sort>اليوم</th>
                        <th data-sort>وقت الحضور</th>
                        <th data-sort>وقت الانصراف</th>
                        <th data-sort>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $index => $a)
                    <tr>
                        <td data-sort-value="{{ $attendances->firstItem() + $index }}"><span class="rpt-num">{{ $attendances->firstItem() + $index }}</span></td>
                        <td data-sort-value="{{ $a->student->user->name ?? '' }}" style="text-align:right;">
                            <a href="{{ route($studentRouteName, $a->student_id) }}" class="rpt-student">
                                <span class="rpt-student-avatar"><i class="fas fa-user-graduate"></i></span>
                                {{ $a->student->user->name ?? '—' }}
                            </a>
                        </td>
                        <td data-sort-value="{{ $a->supervisor->name ?? '' }}" style="font-weight:600;color:var(--primary);">{{ $a->supervisor->name ?? '—' }}</td>
                        <td data-sort-value="{{ $a->date?->format('Y-m-d') }}" style="font-weight:600;">{{ $a->date?->format('Y/m/d') }}</td>
                        <td data-sort-value="{{ $a->date?->format('Y-m-d') }}" style="font-weight:600;">{{ $a->day ?? '—' }}</td>
                        <td>{{ $a->arrival_time ?? '—' }}</td>
                        <td>{{ $a->departure_time ?? '—' }}</td>
                        <td><span class="rpt-badge {{ $a->status }}">{{ $statusLabels[$a->status] ?? $a->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="rpt-pagination">{{ $attendances->withQueryString()->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    document.getElementById('fromDateCol').style.display = period === 'custom' ? '' : 'none';
    document.getElementById('toDateCol').style.display   = period === 'custom' ? '' : 'none';
}
function filterStudents(clearSelection) {
    var supEl = document.getElementById('supervisorSelect');
    if (!supEl) return;
    var sup = supEl.value;
    var sel = document.getElementById('studentSelect');
    Array.from(sel.options).forEach(function (o) {
        if (!o.value) return;
        o.style.display = (!sup || o.getAttribute('data-supervisor-id') === sup) ? '' : 'none';
    });
    // Keep the current student on page load; only reset it when the
    // supervisor itself changes.
    if (clearSelection) {
        sel.value = '';
    }
}
document.addEventListener('DOMContentLoaded', function () { filterStudents(false); });

(function () {
    if (!window.Chart) return;
    var trend = @json($trend ?? []);
    var el = document.getElementById('attTrend');
    if (el) {
        new Chart(el, {
            type: 'line',
            data: {
                labels: trend.map(function (t) { var p = t.date.split('-'); return p[2] + '/' + p[1]; }),
                datasets: [
                    { label: 'إجمالي', data: trend.map(function (t) { return t.total; }), borderColor: '#182B48', backgroundColor: 'rgba(24,43,72,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
                    { label: 'الحضور',  data: trend.map(function (t) { return t.present; }), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
                    { label: 'نسبة الحضور %', data: trend.map(function (t) { return t.rate; }), borderColor: '#d4a030', borderDash: [5, 4], borderWidth: 2, tension: 0.35, pointRadius: 2, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 }, precision: 0 }, grid: { color: '#f1f5f9' } },
                    y1: { beginAtZero: true, position: 'right', max: 100, ticks: { font: { size: 10 }, callback: function (v) { return v + '%'; } }, grid: { display: false } }
                }
            }
        });
    }

    var statuses = @json($statusDistribution ?? []);
    var elStatus = document.getElementById('attStatusDist');
    if (elStatus) {
        new Chart(elStatus, {
            type: 'doughnut',
            data: {
                labels: statuses.map(function (s) { return s.label; }),
                datasets: [{ data: statuses.map(function (s) { return s.count; }), backgroundColor: statuses.map(function (s) { return s.color; }), borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } } }
            }
        });
    }
})();
</script>
@endpush
@endsection
