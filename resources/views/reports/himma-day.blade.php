@extends('layouts.app')
@section('title', 'تقرير يوم الهمة - ' . $day->format('Y/m/d'))
@section('page-title', 'تقرير يوم الهمة')
@section('role-label', $roleLabel)

@section('sidebar-menu')
@include($sidebar)
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($reportsIndexRoute) }}">التقارير</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.himma') }}">تقرير يوم الهمة</a></li>
    <li class="breadcrumb-item active">{{ $day->format('Y/m/d') }}</li>
@endsection

@php
    $queryParams = request()->except('export', 'page');
    $pdfUrl   = route($routePrefix . '.himma.day', array_merge(['date' => $day->format('Y-m-d')], $queryParams, ['export' => 'pdf']));
    $excelUrl = route($routePrefix . '.himma.day', array_merge(['date' => $day->format('Y-m-d')], $queryParams, ['export' => 'excel']));
@endphp

@section('content')
@include('admin.reports._styles')

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="rpt-header-text">
            <h4>تقرير يوم الهمة — {{ $day->translatedFormat('l، Y/m/d') }}</h4>
            <p>نتائج جلسات السرد لهذا اليوم وترتيب الطلاب</p>
        </div>
    </div>
    <div class="rpt-actions">
        <a href="{{ $pdfUrl }}" target="_blank" class="rpt-btn rpt-btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ $excelUrl }}" class="rpt-btn rpt-btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        <a href="{{ route($routePrefix . '.himma') }}" class="rpt-btn rpt-btn-ghost"><i class="fas fa-arrow-right"></i> النظرة الشاملة</a>
    </div>
</div>

<div class="rpt-kpi-grid">
    <div class="rpt-kpi" style="--kpi-color:#182B48;--kpi-bg:rgba(24,43,72,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-list"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_sessions']) }}</div>
        <div class="rpt-kpi-label">الجلسات</div>
        <div class="rpt-kpi-sub">{{ $day->format('Y/m/d') }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#3498db;--kpi-bg:rgba(52,152,219,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_students']) }}</div>
        <div class="rpt-kpi-label">عدد الطلاب</div>
        <div class="rpt-kpi-sub">{{ $day->format('Y/m/d') }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#16a085;--kpi-bg:rgba(22,160,133,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-book-open"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_parts']) }}</div>
        <div class="rpt-kpi-label">الأجزاء</div>
        <div class="rpt-kpi-sub">{{ $day->format('Y/m/d') }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#e74c3c;--kpi-bg:rgba(231,76,60,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_errors']) }}</div>
        <div class="rpt-kpi-label">الأخطاء</div>
        <div class="rpt-kpi-sub">{{ $day->format('Y/m/d') }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#f39c12;--kpi-bg:rgba(243,156,18,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-question-circle"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['total_hesitations']) }}</div>
        <div class="rpt-kpi-label">الترددات</div>
        <div class="rpt-kpi-sub">{{ $day->format('Y/m/d') }}</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#8e44ad;--kpi-bg:rgba(142,68,173,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-star"></i></div>
        <div class="rpt-kpi-value">{{ number_format($stats['avg_score'], 2) }}</div>
        <div class="rpt-kpi-label">متوسط النقاط</div>
        <div class="rpt-kpi-sub">{{ $day->format('Y/m/d') }}</div>
    </div>
</div>

<div class="rpt-chart-card" style="margin-bottom:16px">
    <h6><i class="fas fa-chart-line"></i> منافسة الأجزاء — تقدم الطلاب</h6>
    @if(!empty($competitionChart['datasets']))
        <div class="rpt-chart-wrap" style="height:340px"><canvas id="himmaCompetitionChart"></canvas></div>
    @else
        <div class="rpt-empty"><p>لا توجد بيانات لهذا اليوم</p></div>
    @endif
</div>

<div class="rpt-table-card" style="margin-bottom:24px;">
    <div class="rpt-table-header">
        <h5><i class="fas fa-list"></i> جلسات اليوم</h5>
        <span class="rpt-count-badge"><i class="fas fa-database"></i> {{ $sessions->count() }} جلسة</span>
    </div>
    @if($sessions->isEmpty())
        <div class="rpt-empty"><p>لا توجد جلسات في هذا اليوم</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>المشرف</th>
                        <th>الأجزاء</th>
                        <th data-sort="num">الأخطاء</th>
                        <th data-sort="num">الترددات</th>
                        <th data-sort="num">النقاط</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $i => $session)
                        @php $prec = app(\App\Services\HimmaReportService::class)->sessionPrecision($session); @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td style="text-align:right;font-weight:700;">{{ $session->student_name }}</td>
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

@if($students->count())
<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-trophy"></i> ترتيب طلاب اليوم</h5>
        <span class="rpt-count-badge"><i class="fas fa-user-graduate"></i> {{ $students->count() }} طالب</span>
    </div>
    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th><th>الطالب</th><th data-sort="num">الجلسات</th><th data-sort="num">الأجزاء</th>
                    <th data-sort="num">الأخطاء</th><th data-sort="num">الترددات</th><th data-sort="num">النقاط</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="text-align:right;font-weight:700;">{{ $row['name'] }}</td>
                        <td>{{ $row['sessions'] }}</td>
                        <td>{{ $row['parts'] }}</td>
                        <td style="color:#c0392b;font-weight:700;">{{ $row['errors'] }}</td>
                        <td style="color:#d97706;font-weight:700;">{{ $row['hesitations'] }}</td>
                        <td style="font-weight:800;">{{ $row['score'] }}</td>
                        <td>
                            @if($row['student_id'])
                                <a href="{{ route($routePrefix . '.himma.student', ['student' => $row['student_id']]) }}" class="rpt-btn rpt-btn-ghost" style="padding:4px 10px;font-size:12px;"><i class="fas fa-user"></i></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    if (!window.Chart) return;
    var competition = @json($competitionChart);
    var el = document.getElementById('himmaCompetitionChart');
    if (!el || !competition.datasets || !competition.datasets.length) return;
    new Chart(el, {
        type: 'line',
        data: { labels: competition.labels, datasets: competition.datasets },
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
