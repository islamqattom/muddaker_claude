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

@section('content')
@include('admin.reports._styles', ['accentColor' => '#059669', 'accentColor2' => '#10b981'])

@php
    $queryParams = request()->except('export', 'page');
    $pdfUrl   = route('supervisor.reports.attendance', array_merge($queryParams, ['export' => 'pdf']));
    $excelUrl = route('supervisor.reports.attendance', array_merge($queryParams, ['export' => 'excel']));
    $printUrl = route('supervisor.reports.attendance', array_merge($queryParams, ['export' => 'print']));
@endphp

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="rpt-header-text">
            <h4>تقرير الحضور والغياب</h4>
            <p>عرض سجلات الحضور والغياب للطلاب مع إحصائيات كاملة</p>
        </div>
    </div>
    <div class="rpt-actions">
        <a href="{{ $pdfUrl }}" target="_blank" class="rpt-btn rpt-btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ $excelUrl }}" class="rpt-btn rpt-btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        <a href="{{ $printUrl }}" target="_blank" class="rpt-btn rpt-btn-ghost"><i class="fas fa-print"></i> طباعة</a>
    </div>
</div>

<div class="rpt-kpi-grid">
    <div class="rpt-kpi" style="--kpi-color:#182B48;--kpi-bg:rgba(24,43,72,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-list"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total'] }}</div>
        <div class="rpt-kpi-label">إجمالي السجلات</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#059669;--kpi-bg:rgba(5,150,105,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['present'] }}</div>
        <div class="rpt-kpi-label">حاضر</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#c0392b;--kpi-bg:rgba(192,57,43,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['absent'] }}</div>
        <div class="rpt-kpi-label">غائب</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#f39c12;--kpi-bg:rgba(243,156,18,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-clock"></i></div>
        <div class="rpt-kpi-value">{{ $stats['late'] }}</div>
        <div class="rpt-kpi-label">متأخر</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#3498db;--kpi-bg:rgba(52,152,219,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-file-alt"></i></div>
        <div class="rpt-kpi-value">{{ $stats['excused'] }}</div>
        <div class="rpt-kpi-label">بعذر</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#8e44ad;--kpi-bg:rgba(142,68,173,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-percentage"></i></div>
        <div class="rpt-kpi-value">{{ $stats['attendance_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة الحضور</div>
    </div>
</div>

<div class="rpt-chart-card">
    <h6><i class="fas fa-chart-line"></i> اتجاه الحضور اليومي</h6>
    <div class="rpt-chart-wrap"><canvas id="supAttTrend"></canvas></div>
</div>

<div class="rpt-filter">
    <div class="rpt-filter-header"><i class="fas fa-filter"></i><span>تصفية التقرير</span></div>
    <div class="rpt-filter-body">
        <form method="GET" action="{{ route('supervisor.reports.attendance') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الطالب</label>
                    <select name="student_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>{{ $student->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الفترة</label>
                    <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                        <option value="all"    {{ request('period') == 'all' ? 'selected' : '' }}>الكل</option>
                        <option value="today"  {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                        <option value="week"   {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                        <option value="month"  {{ request('period', 'month') == 'month' ? 'selected' : '' }}>شهر</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                    </select>
                </div>
                <div class="col-md-2" id="fromDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate ? $fromDate->format('Y-m-d') : now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="col-md-2" id="toDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
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

<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-calendar-alt"></i> سجلات الحضور</h5>
        <span class="rpt-count-badge">
            <i class="fas fa-calendar"></i>
            {{ $fromDate ? $fromDate->format('Y/m/d') : '—' }} - {{ $toDate ? $toDate->format('Y/m/d') : '—' }}
        </span>
    </div>
    @if($attendances->isEmpty())
        <div class="rpt-empty">
            <div class="rpt-empty-icon"><i class="fas fa-inbox"></i></div>
            <h5>لا توجد سجلات</h5>
            <p>لا توجد سجلات حضور في الفترة المحددة</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort="num">#</th>
                        <th data-sort>الطالب</th>
                        <th data-sort>الحالة</th>
                        <th data-sort>وقت الحضور</th>
                        <th data-sort>ملاحظة</th>
                        <th data-sort>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $index => $a)
                    <tr>
                        <td data-sort-value="{{ $attendances->firstItem() + $index }}"><span class="rpt-num">{{ $attendances->firstItem() + $index }}</span></td>
                        <td data-sort-value="{{ $a->student->user->name ?? '' }}">
                            <a href="{{ route('supervisor.reports.student', $a->student_id) }}" class="rpt-student">
                                <span class="rpt-student-avatar"><i class="fas fa-user-graduate"></i></span>
                                {{ $a->student->user->name ?? '—' }}
                            </a>
                        </td>
                        <td><span class="rpt-badge {{ $a->status }}">{{ ['present'=>'حاضر','absent'=>'غائب','late'=>'متأخر','excused'=>'معذور','holiday'=>'عطلة'][$a->status] ?? $a->status }}</span></td>
                        <td>{{ $a->arrival_time ?? '—' }}</td>
                        <td>{{ $a->notes ?? '—' }}</td>
                        <td data-sort-value="{{ $a->date?->format('Y-m-d') }}" style="font-weight:600;">{{ $a->date?->format('Y/m/d') }}</td>
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
(function () {
    if (!window.Chart) return;
    var el = document.getElementById('supAttTrend');
    if (!el) return;
    var trend = @json($trend ?? []);
    new Chart(el, {
        type: 'line',
        data: {
            labels: trend.map(function (t) { return t.date; }),
            datasets: [
                { label: 'إجمالي', data: trend.map(function (t) { return t.total; }), borderColor: '#182B48', backgroundColor: 'rgba(24,43,72,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
                { label: 'حاضر',  data: trend.map(function (t) { return t.present; }), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
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
})();
</script>
@endpush
@endsection
