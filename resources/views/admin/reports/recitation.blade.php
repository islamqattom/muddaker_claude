@extends('layouts.app')
@section('title', 'تقرير التسميع')
@section('page-title', 'تقرير التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير التسميع</li>
@endsection

@section('content')
@include('admin.reports._styles')

@php
    $queryParams = request()->except('export', 'page');
    $pdfUrl   = route('admin.reports.recitation', array_merge($queryParams, ['export' => 'pdf']));
    $excelUrl = route('admin.reports.recitation', array_merge($queryParams, ['export' => 'excel']));
    $printUrl = route('admin.reports.recitation', array_merge($queryParams, ['export' => 'print']));
@endphp

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

<div class="rpt-kpi-grid">
    <div class="rpt-kpi" style="--kpi-color:#182B48;--kpi-bg:rgba(24,43,72,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-list"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total'] }}</div>
        <div class="rpt-kpi-label">إجمالي الجلسات</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#27ae60;--kpi-bg:rgba(39,174,96,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['passed'] }}</div>
        <div class="rpt-kpi-label">ناجح</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#c0392b;--kpi-bg:rgba(192,57,43,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['failed'] }}</div>
        <div class="rpt-kpi-label">راسب</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#d4a030;--kpi-bg:rgba(212,160,48,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-percentage"></i></div>
        <div class="rpt-kpi-value">{{ $stats['pass_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة النجاح</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#8e44ad;--kpi-bg:rgba(142,68,173,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-star"></i></div>
        <div class="rpt-kpi-value">{{ $stats['avg_score'] }}%</div>
        <div class="rpt-kpi-label">متوسط الدرجة</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#3498db;--kpi-bg:rgba(52,152,219,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-book"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total_pages'] }}</div>
        <div class="rpt-kpi-label">إجمالي الصفحات</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px;">
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-area"></i> اتجاه الجلسات والنجاح خلال الفترة</h6>
        <div class="rpt-chart-wrap"><canvas id="adminRecTrend"></canvas></div>
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

<div class="rpt-filter">
    <div class="rpt-filter-header">
        <i class="fas fa-filter"></i>
        <span>بحث وتصفية النتائج</span>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">المشرف</label>
                    <select name="supervisor_id" class="form-select" id="supervisorSelect" onchange="filterStudents()">
                        <option value="">الكل</option>
                        @foreach($supervisors as $supervisor)
                            <option value="{{ $supervisor->user_id }}" {{ request('supervisor_id') == $supervisor->user_id ? 'selected' : '' }}>{{ $supervisor->user->name }}</option>
                        @endforeach
                    </select>
                </div>
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
                        <th data-sort>الجزء</th>
                        <th data-sort>الصفحات</th>
                        <th data-sort="num">الدرجة</th>
                        <th data-sort>الحالة</th>
                        <th data-sort>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $index => $session)
                    <tr>
                        <td data-sort-value="{{ $sessions->firstItem() + $index }}"><span class="rpt-num">{{ $sessions->firstItem() + $index }}</span></td>
                        <td data-sort-value="{{ $session->student->user->name ?? '' }}">
                            <a href="{{ route('admin.students.show', $session->student_id) }}" class="rpt-student">
                                <span class="rpt-student-avatar"><i class="fas fa-user-graduate"></i></span>
                                {{ $session->student->user->name ?? '—' }}
                            </a>
                        </td>
                        <td data-sort-value="{{ $session->supervisor->name ?? '' }}" style="font-weight:600;color:var(--primary);">{{ $session->supervisor->name ?? '—' }}</td>
                        <td data-sort-value="{{ $session->parts_label }}" style="font-weight:600;">{{ $session->parts_label }}</td>
                        <td data-sort-value="{{ ($session->start_page ?? 0) + ($session->end_page ?? 0) }}">{{ $session->start_page ?? '—' }} - {{ $session->end_page ?? '—' }}</td>
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
    var sup = document.getElementById('supervisorSelect').value;
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
    var el = document.getElementById('adminRecTrend');
    if (!el) return;
    var trend = @json($trend ?? []);
    new Chart(el, {
        type: 'line',
        data: {
            labels: trend.map(function (t) { return t.date; }),
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
})();
</script>
@endpush
@endsection
