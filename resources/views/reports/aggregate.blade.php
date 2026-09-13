@extends('layouts.app')
@section('title', 'التقرير ' . ($periodType === 'year' ? 'السنوي' : ($periodType === 'half' ? 'نصف السنوي' : 'الربعي')))
@section('page-title', 'التقرير ' . ($periodType === 'year' ? 'السنوي' : ($periodType === 'half' ? 'نصف السنوي' : 'الربعي')))
@section('role-label', $roleLabel)

@section('sidebar-menu')
@include($sidebar)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($reportsIndexRoute) }}">التقارير</a></li>
    <li class="breadcrumb-item active">{{ $periodTitle }}</li>
@endsection

@php
    $typeLabels   = ['new_memorization' => 'حفظ جديد', 'review' => 'مراجعة', 'unmastered' => 'حفظ غير متقن', 'recitation' => 'سرد', 'mafsil' => 'مفصل'];
    $statusLabels = ['present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'معذور', 'holiday' => 'عطلة'];
    $periodLabel  = $periodType === 'year' ? 'السنة' : ($periodType === 'half' ? 'النصف' : 'الربع');
    $titles       = ['quarter' => 'التقرير الربعي', 'half' => 'التقرير نصف السنوي', 'year' => 'التقرير السنوي'];

    $queryParams = request()->except('export', 'page', 'rec_page', 'att_page');
    $pdfUrl   = route($routePrefix . '.' . $periodType, array_merge($queryParams, ['export' => 'pdf']));
    $excelUrl = route($routePrefix . '.' . $periodType, array_merge($queryParams, ['export' => 'excel']));
    $printUrl = route($routePrefix . '.' . $periodType, array_merge($queryParams, ['export' => 'print']));

    $prevRec = $prevStats['rec'] ?? null;
    $prevAtt = $prevStats['att'] ?? null;
    $dr = fn ($key) => $prevRec !== null ? round(($recStats[$key] ?? 0) - ($prevRec[$key] ?? 0), 1) : null;
    $da = fn ($key) => $prevAtt !== null ? round(($attStats[$key] ?? 0) - ($prevAtt[$key] ?? 0), 1) : null;

    $selSupervisor = $showSupervisorFilter && request('supervisor_id') ? ($supervisors->firstWhere('user_id', (int) request('supervisor_id'))?->user?->name) : null;
    $selStudent    = request('student_id') ? ($students->firstWhere('id', (int) request('student_id'))?->user?->name) : null;
    $hasFilters    = $selSupervisor || $selStudent || request('type');
@endphp

@section('content')
@include('admin.reports._styles')

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="rpt-header-text">
            <h4>{{ $titles[$periodType] ?? 'التقرير' }} — {{ $periodTitle }}</h4>
            <p>{{ $fromDate->format('Y/m/d') }} — {{ $toDate->format('Y/m/d') }}</p>
        </div>
    </div>
    <div class="rpt-actions">
        <a href="{{ $pdfUrl }}" target="_blank" class="rpt-btn rpt-btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ $excelUrl }}" class="rpt-btn rpt-btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        <a href="{{ $printUrl }}" target="_blank" class="rpt-btn rpt-btn-ghost"><i class="fas fa-print"></i> طباعة</a>
    </div>
</div>

<div class="rpt-filter">
    <div class="rpt-filter-header"><i class="fas fa-filter"></i><span>فلاتر التقرير</span></div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">السنة</label>
                    <select name="year" class="form-select">
                        @php $curYear = (int) now()->year; @endphp
                        @for($y = $curYear - 2; $y <= $curYear + 1; $y++)
                            <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @if($periodType === 'quarter')
                    <div class="col-md-2">
                        <label class="form-label">الربع</label>
                        <select name="quarter" class="form-select">
                            <option value="1" {{ (int) $selectorValue === 1 ? 'selected' : '' }}>الربع الأول (يناير - مارس)</option>
                            <option value="2" {{ (int) $selectorValue === 2 ? 'selected' : '' }}>الربع الثاني (أبريل - يونيو)</option>
                            <option value="3" {{ (int) $selectorValue === 3 ? 'selected' : '' }}>الربع الثالث (يوليو - سبتمبر)</option>
                            <option value="4" {{ (int) $selectorValue === 4 ? 'selected' : '' }}>الربع الرابع (أكتوبر - ديسمبر)</option>
                        </select>
                    </div>
                @elseif($periodType === 'half')
                    <div class="col-md-2">
                        <label class="form-label">النصف</label>
                        <select name="half" class="form-select">
                            <option value="1" {{ (int) $selectorValue === 1 ? 'selected' : '' }}>النصف الأول (يناير - يونيو)</option>
                            <option value="2" {{ (int) $selectorValue === 2 ? 'selected' : '' }}>النصف الثاني (يوليو - ديسمبر)</option>
                        </select>
                    </div>
                @endif
                @if($showSupervisorFilter)
                    <div class="col-md-2">
                        <label class="form-label">المشرف</label>
                        <select name="supervisor_id" class="form-select" id="supervisorSelect" onchange="filterStudents()">
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
                    <label class="form-label">نوع التسميع</label>
                    <select name="type" class="form-select">
                        <option value="">الكل</option>
                        @foreach($typeLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="rpt-btn rpt-btn-primary flex-fill"><i class="fas fa-search"></i> تطبيق</button>
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
    @if(request('type'))<span class="rpt-chip"><i class="fas fa-bookmark"></i> {{ $typeLabels[request('type')] ?? request('type') }}</span>@endif
    <a href="{{ route($routePrefix . '.' . $periodType) }}" class="rpt-chip rpt-chip-reset"><i class="fas fa-times"></i> مسح الكل</a>
</div>
@endif

<div class="rpt-kpi-grid">
    @include('reports._kpi', ['label' => 'جلسات التسميع', 'value' => $recStats['total'], 'icon' => 'fa-microphone', 'color' => '#182B48', 'bg' => 'rgba(24,43,72,0.10)', 'delta' => $dr('total'), 'deltaSuffix' => ''])
    @include('reports._kpi', ['label' => 'نسبة النجاح', 'value' => $recStats['pass_rate'] . '%', 'icon' => 'fa-check-circle', 'color' => '#27ae60', 'bg' => 'rgba(39,174,96,0.10)', 'delta' => $dr('pass_rate')])
    @include('reports._kpi', ['label' => 'جلسات راسبة', 'value' => $recStats['failed'], 'icon' => 'fa-times-circle', 'color' => '#c0392b', 'bg' => 'rgba(192,57,43,0.10)', 'delta' => $dr('failed'), 'deltaSuffix' => '', 'deltaInvert' => true])
    @include('reports._kpi', ['label' => 'سجلات الحضور', 'value' => $attStats['total'], 'icon' => 'fa-calendar-check', 'color' => '#3498db', 'bg' => 'rgba(52,152,219,0.10)', 'delta' => $da('total'), 'deltaSuffix' => ''])
    @include('reports._kpi', ['label' => 'نسبة الحضور', 'value' => $attStats['attendance_rate'] . '%', 'icon' => 'fa-percentage', 'color' => '#059669', 'bg' => 'rgba(5,150,105,0.10)', 'delta' => $da('attendance_rate')])
</div>

<div class="rpt-grid-2">
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-bar"></i> الجلسات والنجاح — شهرياً</h6>
        @if(!empty($recTrend))
            <div class="rpt-chart-wrap"><canvas id="qRecChart"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات</p></div>
        @endif
    </div>
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-line"></i> الحضور — شهرياً</h6>
        @if(!empty($attTrend))
            <div class="rpt-chart-wrap"><canvas id="qAttChart"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات</p></div>
        @endif
    </div>
</div>

<div class="rpt-grid-2">
    @php
        $hasTypeDist   = collect($typeDistribution)->sum('count') > 0;
        $hasStatusDist = collect($statusDistribution)->sum('count') > 0;
    @endphp
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-pie"></i> أنواع الجلسات</h6>
        @if($hasTypeDist)
            <div class="rpt-chart-wrap"><canvas id="qTypeDist"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد جلسات</p></div>
        @endif
    </div>
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-pie"></i> حالات الحضور</h6>
        @if($hasStatusDist)
            <div class="rpt-chart-wrap"><canvas id="qStatusDist"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد سجلات حضور</p></div>
        @endif
    </div>
</div>

@if(!empty($top))
<div class="rpt-chart-card" style="margin-bottom: 24px;">
    <h6><i class="fas fa-trophy"></i> أفضل الطلاب أداءً في {{ $periodLabel }}</h6>
    <div class="rpt-mini-list">
        @foreach($top as $i => $s)
            <div class="rpt-mini-row r{{ $i+1 }}">
                <span class="rpt-mini-rank">{{ $i+1 }}</span>
                <span class="rpt-mini-name">{{ $s['name'] }}</span>
                <div class="rpt-mini-bar"><div class="rpt-mini-bar-fill" style="width: {{ min(100, $s['avg_score']) }}%"></div></div>
                <span class="rpt-mini-score">{{ $s['avg_score'] }}%</span>
            </div>
        @endforeach
    </div>
</div>
@endif

@include('reports._written_report', ['breakdown' => $breakdown, 'periodLabel' => $periodLabel])

@include('reports._student_summary', ['summary' => $summary, 'showRecitation' => true, 'showAttendance' => true, 'studentRouteName' => $studentRouteName])

@push('scripts')
<script>
function filterStudents() {
    var supEl = document.getElementById('supervisorSelect');
    if (!supEl) return;
    var sup = supEl.value;
    var sel = document.getElementById('studentSelect');
    Array.from(sel.options).forEach(function (o) {
        if (!o.value) return;
        o.style.display = (!sup || o.getAttribute('data-supervisor-id') === sup) ? '' : 'none';
    });
    sel.value = '';
}
document.addEventListener('DOMContentLoaded', function () { filterStudents(); });

(function () {
    if (!window.Chart) return;
    var recTrend = @json($recTrend ?? []);
    var attTrend = @json($attTrend ?? []);

    var months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    function monthLabel(m) {
        if (!m) return '';
        var p = m.split('-');
        return months[parseInt(p[1], 10) - 1] + ' ' + p[0];
    }
    var monthKeys = [].concat(recTrend.map(function (t) { return t.month; }), attTrend.map(function (t) { return t.month; }))
        .filter(function (v, i, a) { return a.indexOf(v) === i; }).sort();
    var labels = monthKeys.map(monthLabel);

    function lookup(series, key) {
        var map = {};
        series.forEach(function (r) { map[r.month] = r[key] || 0; });
        return monthKeys.map(function (m) { return map[m] || 0; });
    }

    var elRec = document.getElementById('qRecChart');
    if (elRec) {
        new Chart(elRec, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { type: 'bar',  label: 'إجمالي', data: lookup(recTrend, 'total'),  backgroundColor: 'rgba(24,43,72,0.65)', borderColor: '#182B48', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                    { type: 'bar',  label: 'ناجح',   data: lookup(recTrend, 'passed'), backgroundColor: 'rgba(39,174,96,0.65)',  borderColor: '#27ae60', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                    { type: 'line', label: 'نسبة النجاح %', data: lookup(recTrend, 'pass_rate'), borderColor: '#d4a030', backgroundColor: 'rgba(212,160,48,0.15)', borderWidth: 2, tension: 0.35, pointRadius: 4, yAxisID: 'y1' }
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

    var elAtt = document.getElementById('qAttChart');
    if (elAtt) {
        new Chart(elAtt, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { type: 'bar',  label: 'حاضر',  data: lookup(attTrend, 'present'), backgroundColor: 'rgba(5,150,105,0.65)', borderColor: '#059669', borderWidth: 1, borderRadius: 6, stack: 's', yAxisID: 'y' },
                    { type: 'bar',  label: 'متأخر', data: lookup(attTrend, 'late'),    backgroundColor: 'rgba(243,156,18,0.65)', borderColor: '#f39c12', borderWidth: 1, borderRadius: 6, stack: 's', yAxisID: 'y' },
                    { type: 'bar',  label: 'غائب',  data: lookup(attTrend, 'absent'),  backgroundColor: 'rgba(192,57,43,0.65)', borderColor: '#c0392b', borderWidth: 1, borderRadius: 6, stack: 's', yAxisID: 'y' },
                    { type: 'bar',  label: 'بعذر',  data: lookup(attTrend, 'excused'), backgroundColor: 'rgba(52,152,219,0.65)', borderColor: '#3498db', borderWidth: 1, borderRadius: 6, stack: 's', yAxisID: 'y' },
                    { type: 'line', label: 'نسبة الحضور %', data: lookup(attTrend, 'rate'), borderColor: '#d4a030', backgroundColor: 'rgba(212,160,48,0.15)', borderWidth: 2, tension: 0.35, pointRadius: 4, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { stacked: true, beginAtZero: true, ticks: { font: { size: 10 }, precision: 0 }, grid: { color: '#f1f5f9' } },
                    y1: { beginAtZero: true, position: 'right', max: 100, ticks: { font: { size: 10 }, callback: function (v) { return v + '%'; } }, grid: { display: false } }
                }
            }
        });
    }

    function donut(id, data) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: data.map(function (t) { return t.label; }),
                datasets: [{ data: data.map(function (t) { return t.count; }), backgroundColor: data.map(function (t) { return t.color; }), borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } } }
            }
        });
    }
    donut('qTypeDist', @json($typeDistribution ?? []));
    donut('qStatusDist', @json($statusDistribution ?? []));
})();
</script>
@endpush
@endsection
