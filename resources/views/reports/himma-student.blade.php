@extends('layouts.app')
@section('title', 'تقرير يوم الهمة - ' . ($student->user->name ?? ''))
@section('page-title', 'تقرير يوم الهمة - طالب')
@section('role-label', $roleLabel)

@section('sidebar-menu')
@include($sidebar)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($reportsIndexRoute) }}">التقارير</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.himma') }}">تقرير يوم الهمة</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name ?? '' }}</li>
@endsection

@php
    $periodLabels = ['today' => 'اليوم', 'week' => 'هذا الأسبوع', 'month' => 'هذا الشهر', 'quarter' => 'هذا الربع', 'half' => 'هذا النصف', 'year' => 'هذه السنة', 'all' => 'كل الفترات', 'custom' => 'فترة مخصصة'];
    $periodLabel = $periodLabels[$period] ?? 'كل الفترات';

    $queryParams = request()->except('export', 'page');
    $pdfUrl   = route($routePrefix . '.himma.student', array_merge(['student' => $student->id], $queryParams, ['export' => 'pdf']));
    $excelUrl = route($routePrefix . '.himma.student', array_merge(['student' => $student->id], $queryParams, ['export' => 'excel']));
@endphp

@section('content')
@include('admin.reports._styles')

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="rpt-header-text">
            <h4>تقرير يوم الهمة — {{ $student->user->name ?? '' }}</h4>
            <p>{{ $student->level->name ?? '' }} • الفترة: {{ $periodLabel }}</p>
        </div>
    </div>
    <div class="rpt-actions">
        <a href="{{ $pdfUrl }}" target="_blank" class="rpt-btn rpt-btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ $excelUrl }}" class="rpt-btn rpt-btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        <a href="{{ route($routePrefix . '.himma') }}" class="rpt-btn rpt-btn-ghost"><i class="fas fa-arrow-right"></i> النظرة الشاملة</a>
    </div>
</div>

<div class="rpt-filter">
    <div class="rpt-filter-header"><i class="fas fa-filter"></i> <span>تحديد الفترة</span></div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
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
                <div class="col-md-3" id="fromDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate ? $fromDate->format('Y-m-d') : now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="col-md-3" id="toDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
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
        <div class="rpt-kpi-label">الجلسات</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#d4a030;--kpi-bg:rgba(212,160,48,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_days']) }}</div>
        <div class="rpt-kpi-label">أيام الحضور</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#16a085;--kpi-bg:rgba(22,160,133,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-book-open"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_parts']) }}</div>
        <div class="rpt-kpi-label">الأجزاء</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#e74c3c;--kpi-bg:rgba(231,76,60,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_errors']) }}</div>
        <div class="rpt-kpi-label">الأخطاء</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#f39c12;--kpi-bg:rgba(243,156,18,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-question-circle"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_hesitations']) }}</div>
        <div class="rpt-kpi-label">الترددات</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#8e44ad;--kpi-bg:rgba(142,68,173,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-star"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_score'], 2) }}</div>
        <div class="rpt-kpi-label">إجمالي النقاط</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </div>
</div>

@if(count($charts['days']['labels']))
<div class="rpt-chart-card" style="margin-bottom:16px">
    <h6><i class="fas fa-chart-line"></i> تطور الجلسات والنقاط</h6>
    <div class="rpt-chart-wrap"><canvas id="himmaStudentChart"></canvas></div>
</div>
@endif

<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-list"></i> جلسات الطالب</h5>
        <span class="rpt-count-badge"><i class="fas fa-database"></i> {{ $sessions->count() }} جلسة</span>
    </div>
    @if($sessions->isEmpty())
        <div class="rpt-empty"><p>لا توجد جلسات لهذه الفترة</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>اليوم</th>
                        <th>المشرف</th>
                        <th>الأجزاء</th>
                        <th data-sort="num">الأخطاء</th>
                        <th data-sort="num">الترددات</th>
                        <th data-sort="num">النقاط</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        @php $prec = app(\App\Services\HimmaReportService::class)->sessionPrecision($session); @endphp
                        <tr>
                            <td>{{ $session->session_date?->format('Y/m/d') }}</td>
                            <td>{{ $session->session_date?->translatedFormat('l') }}</td>
                            <td>{{ $session->supervisor_name ?: '—' }}</td>
                            <td style="text-align:right;">
                                @foreach((array) $session->parts as $p)
                                    <span class="rpt-badge" style="background:rgba(24,43,72,0.08);color:#182B48;margin:1px;">{{ $p }}</span>
                                @endforeach
                            </td>
                            <td style="color:#c0392b;font-weight:700;">{{ (int) $session->total_errors }}</td>
                            <td style="color:#d97706;font-weight:700;">{{ (int) $session->total_hesitations }}</td>
                            <td style="font-weight:800;">{{ number_format($prec['score'], 2) }}</td>
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
    var el = document.getElementById('himmaStudentChart');
    if (!el || !days.labels.length) return;
    new Chart(el, {
        type: 'line',
        data: {
            labels: days.labels,
            datasets: [
                { label: 'الجلسات', data: days.sessions, borderColor: '#182B48', backgroundColor: 'rgba(24,43,72,0.12)', tension: 0.35, fill: true, pointRadius: 3 },
                { label: 'الأخطاء', data: days.errors, borderColor: '#e74c3c', backgroundColor: 'rgba(231,76,60,0.10)', tension: 0.35, fill: true, pointRadius: 3 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { font: { size: 10 }, precision: 0 }, grid: { color: '#f1f5f9' } }
            }
        }
    });
})();
</script>
@endpush
