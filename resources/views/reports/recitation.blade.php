@extends('layouts.app')
@section('title', 'تقرير التسميع')
@section('page-title', 'تقرير التسميع')
@section('role-label', $roleLabel)

@section('sidebar-menu')
@include($sidebar)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($reportsIndexRoute) }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير التسميع</li>
@endsection

@php
    $typeLabels   = ['new_memorization' => 'حفظ جديد', 'review' => 'مراجعة', 'unmastered' => 'حفظ غير متقن', 'recitation' => 'سرد', 'mafsil' => 'مفصل'];
    $statusLabels = ['passed' => 'ناجح', 'failed' => 'راسب', 'in_progress' => 'قيد التنفيذ'];
    $periodLabels = ['today' => 'اليوم', 'week' => 'هذا الأسبوع', 'month' => 'هذا الشهر', 'quarter' => 'هذا الربع', 'half' => 'هذا النصف', 'year' => 'هذه السنة', 'all' => 'كل الفترات', 'custom' => 'فترة مخصصة'];

    $queryParams = request()->except('export', 'page', 'rec_page', 'att_page');
    $pdfUrl   = route($routePrefix . '.recitation', array_merge($queryParams, ['export' => 'pdf']));
    $excelUrl = route($routePrefix . '.recitation', array_merge($queryParams, ['export' => 'excel']));
    $printUrl = route($routePrefix . '.recitation', array_merge($queryParams, ['export' => 'print']));

    $prev = $prevStats ?? null;
    $d = fn ($key) => $prev !== null ? round(($stats[$key] ?? 0) - ($prev[$key] ?? 0), 1) : null;

    $selSupervisor = $showSupervisorFilter && request('supervisor_id') ? ($supervisors->firstWhere('user_id', (int) request('supervisor_id'))?->user?->name) : null;
    $selStudent    = request('student_id') ? ($students->firstWhere('id', (int) request('student_id'))?->user?->name) : null;
    $hasFilters    = $selSupervisor || $selStudent || request('type') || request('status') || (request('period') && request('period') !== 'all');
@endphp

@section('content')
@include('admin.reports._styles')

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-quran"></i></div>
        <div class="rpt-header-text">
            <h4>تقرير التسميع</h4>
            <p>نتائج جلسات التسميع والاختبارات</p>
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
        <span>بحث وتصفية النتائج</span>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
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
    @if(request('type'))<span class="rpt-chip"><i class="fas fa-bookmark"></i> {{ $typeLabels[request('type')] ?? request('type') }}</span>@endif
    @if(request('status'))<span class="rpt-chip"><i class="fas fa-flag"></i> {{ $statusLabels[request('status')] ?? request('status') }}</span>@endif
    @if(request('period'))<span class="rpt-chip"><i class="fas fa-calendar"></i> {{ $periodLabels[request('period')] ?? request('period') }}</span>@endif
    <a href="{{ route($routePrefix . '.recitation') }}" class="rpt-chip rpt-chip-reset"><i class="fas fa-times"></i> مسح الكل</a>
</div>
@endif

<div class="rpt-kpi-grid">
    @include('reports._kpi', ['label' => 'إجمالي الجلسات', 'value' => $stats['total'], 'icon' => 'fa-list', 'color' => '#182B48', 'bg' => 'rgba(24,43,72,0.10)', 'delta' => $d('total'), 'deltaSuffix' => ''])
    @include('reports._kpi', ['label' => 'ناجح', 'value' => $stats['passed'], 'icon' => 'fa-check-circle', 'color' => '#27ae60', 'bg' => 'rgba(39,174,96,0.10)', 'delta' => $d('passed'), 'deltaSuffix' => ''])
    @include('reports._kpi', ['label' => 'راسب', 'value' => $stats['failed'], 'icon' => 'fa-times-circle', 'color' => '#c0392b', 'bg' => 'rgba(192,57,43,0.10)', 'delta' => $d('failed'), 'deltaSuffix' => '', 'deltaInvert' => true])
    @include('reports._kpi', ['label' => 'نسبة النجاح', 'value' => $stats['pass_rate'] . '%', 'icon' => 'fa-percentage', 'color' => '#d4a030', 'bg' => 'rgba(212,160,48,0.10)', 'delta' => $d('pass_rate')])
    @include('reports._kpi', ['label' => 'إجمالي الصفحات', 'value' => $stats['total_pages'], 'icon' => 'fa-book', 'color' => '#3498db', 'bg' => 'rgba(52,152,219,0.10)', 'delta' => $d('total_pages'), 'deltaSuffix' => ''])
</div>

<div class="rpt-grid-2">
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-area"></i> اتجاه الجلسات والنجاح</h6>
        @if(!empty($trend))
            <div class="rpt-chart-wrap"><canvas id="recTrend"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات في هذه الفترة</p></div>
        @endif
    </div>
    <div class="rpt-chart-card">
        <h6><i class="fas fa-trophy"></i> أفضل 5 طلاب</h6>
        @if(!empty($top))
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
        @else
            <div class="rpt-empty"><p>لا توجد بيانات</p></div>
        @endif
    </div>
</div>

<div class="rpt-grid-2">
    @php
        $hasTypeDist = collect($typeDistribution)->sum('count') > 0;
    @endphp
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-pie"></i> أنواع الجلسات</h6>
        @if($hasTypeDist)
            <div class="rpt-chart-wrap"><canvas id="recTypeDist"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد جلسات</p></div>
        @endif
    </div>
</div>

@include('reports._student_summary', ['summary' => $summary, 'showRecitation' => true, 'showAttendance' => false, 'studentRouteName' => $studentRouteName])

<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-list"></i> نتائج التسميع</h5>
        <span class="rpt-count-badge"><i class="fas fa-database"></i> {{ $sessions->total() }} جلسة</span>
    </div>
    @if($sessions->isEmpty())
        <div class="rpt-empty">
            <div class="rpt-empty-icon"><i class="fas fa-quran"></i></div>
            <h5>لا توجد جلسات تسميع</h5>
            <p>لم يتم تسجيل جلسات تسميع في هذه الفترة</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort="num">#</th>
                        <th data-sort>الطالب</th>
                        <th data-sort>المشرف</th>
                        <th data-sort>النوع</th>
                        <th data-sort>الجزء</th>
                        <th data-sort>الصفحات</th>
                        <th data-sort="num">الأخطاء</th>
                        <th data-sort="num">الترددات</th>
                        <th data-sort="num">الدرجة</th>
                        <th data-sort>الحالة</th>
                        <th data-sort>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $index => $session)
                    <tr>
                        <td data-sort-value="{{ $sessions->firstItem() + $index }}"><span class="rpt-num">{{ $sessions->firstItem() + $index }}</span></td>
                        <td data-sort-value="{{ $session->student->user->name ?? '' }}" style="text-align:right;">
                            <a href="{{ route($studentRouteName, $session->student_id) }}" class="rpt-student">
                                <span class="rpt-student-avatar"><i class="fas fa-user-graduate"></i></span>
                                {{ $session->student->user->name ?? '—' }}
                            </a>
                        </td>
                        <td data-sort-value="{{ $session->supervisor->name ?? '' }}" style="font-weight:600;color:var(--primary);">{{ $session->supervisor->name ?? '—' }}</td>
                        <td data-sort-value="{{ $session->type_label }}"><span class="rpt-badge" style="background:rgba(24,43,72,0.06);color:var(--primary);">{{ $session->type_label }}</span></td>
                        <td data-sort-value="{{ $session->parts_label }}" style="font-weight:600;">{{ $session->parts_label }}</td>
                        <td data-sort-value="{{ ($session->start_page ?? 0) + ($session->end_page ?? 0) }}">{{ $session->start_page ?? '—' }} - {{ $session->end_page ?? '—' }}</td>
                        <td data-sort-value="{{ $session->total_errors ?? 0 }}" style="color:#c0392b;font-weight:700;">{{ $session->total_errors ?? 0 }}</td>
                        <td data-sort-value="{{ $session->total_hesitations ?? 0 }}" style="color:#d97706;font-weight:700;">{{ $session->total_hesitations ?? 0 }}</td>
                        <td data-sort-value="{{ $session->final_score ?? -1 }}">
                            @if($session->final_score !== null)
                                @php $sc = $session->final_score; @endphp
                                <span class="rpt-score {{ $sc >= 90 ? 'high' : ($sc >= 75 ? 'medium' : 'low') }}">{{ number_format($sc, 1) }}%</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="rpt-badge {{ $session->status }}">{{ $session->status_label }}</span></td>
                        <td data-sort-value="{{ $session->session_date?->format('Y-m-d') }}" style="font-weight:600;">{{ $session->session_date?->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="rpt-pagination">{{ $sessions->withQueryString()->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    document.getElementById('fromDateCol').style.display = period === 'custom' ? '' : 'none';
    document.getElementById('toDateCol').style.display   = period === 'custom' ? '' : 'none';
}
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
    var trend = @json($trend ?? []);
    var el = document.getElementById('recTrend');
    if (el) {
        new Chart(el, {
            type: 'line',
            data: {
                labels: trend.map(function (t) { var p = t.date.split('-'); return p[2] + '/' + p[1]; }),
                datasets: [
                    { label: 'إجمالي', data: trend.map(function (t) { return t.total; }), borderColor: '#182B48', backgroundColor: 'rgba(24,43,72,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
                    { label: 'ناجح',  data: trend.map(function (t) { return t.passed; }), borderColor: '#27ae60', backgroundColor: 'rgba(39,174,96,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
                    { label: 'نسبة النجاح %', data: trend.map(function (t) { return t.pass_rate; }), borderColor: '#d4a030', borderDash: [5, 4], borderWidth: 2, tension: 0.35, pointRadius: 2, yAxisID: 'y1' }
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

    var types = @json($typeDistribution ?? []);
    var elType = document.getElementById('recTypeDist');
    if (elType) {
        new Chart(elType, {
            type: 'doughnut',
            data: {
                labels: types.map(function (t) { return t.label; }),
                datasets: [{ data: types.map(function (t) { return t.count; }), backgroundColor: types.map(function (t) { return t.color; }), borderWidth: 2, borderColor: '#fff' }]
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
