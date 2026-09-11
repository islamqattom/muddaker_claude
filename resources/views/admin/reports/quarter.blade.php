@extends('layouts.app')
@section('title', 'التقرير الربعي')
@section('page-title', 'التقرير الربعي')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">التقرير الربعي</li>
@endsection

@php
    $arabicQuarter = [1 => 'الأول', 2 => 'الثاني', 3 => 'الثالث', 4 => 'الرابع'][$quarter] ?? $quarter;
    $queryParams = request()->except('export', 'page');
    $pdfUrl   = route('admin.reports.quarter', array_merge($queryParams, ['export' => 'pdf']));
    $excelUrl = route('admin.reports.quarter', array_merge($queryParams, ['export' => 'excel']));
    $printUrl = route('admin.reports.quarter', array_merge($queryParams, ['export' => 'print']));
@endphp

@section('content')
@include('admin.reports._styles')

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="rpt-header-text">
            <h4>التقرير الربعي — الربع {{ $arabicQuarter }} {{ $year }}</h4>
            <p>{{ $fromDate->format('Y/m/d') }} — {{ $toDate->format('Y/m/d') }}</p>
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
        <div class="rpt-kpi-icon"><i class="fas fa-microphone"></i></div>
        <div class="rpt-kpi-value">{{ $recStats['total'] }}</div>
        <div class="rpt-kpi-label">جلسات التسميع</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#27ae60;--kpi-bg:rgba(39,174,96,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="rpt-kpi-value">{{ $recStats['pass_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة النجاح</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#d4a030;--kpi-bg:rgba(212,160,48,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-star"></i></div>
        <div class="rpt-kpi-value">{{ $recStats['avg_score'] }}%</div>
        <div class="rpt-kpi-label">متوسط الدرجة</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#c0392b;--kpi-bg:rgba(192,57,43,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ $recStats['failed'] }}</div>
        <div class="rpt-kpi-label">جلسات راسبة</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#3498db;--kpi-bg:rgba(52,152,219,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="rpt-kpi-value">{{ $attStats['total'] }}</div>
        <div class="rpt-kpi-label">سجلات الحضور</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#059669;--kpi-bg:rgba(5,150,105,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-percentage"></i></div>
        <div class="rpt-kpi-value">{{ $attStats['attendance_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة الحضور</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-bar"></i> الجلسات والنجاح — شهرياً</h6>
        <div class="rpt-chart-wrap"><canvas id="qRecChart"></canvas></div>
    </div>
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-line"></i> الحضور — شهرياً</h6>
        <div class="rpt-chart-wrap"><canvas id="qAttChart"></canvas></div>
    </div>
</div>

@if(!empty($top))
<div class="rpt-chart-card" style="margin-bottom: 24px;">
    <h6><i class="fas fa-trophy"></i> أفضل الطلاب أداءً في الربع</h6>
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

<div class="rpt-filter">
    <div class="rpt-filter-header"><i class="fas fa-filter"></i><span>فلاتر التقرير</span></div>
    <div class="rpt-filter-body">
        <form method="GET" id="quarterFilter">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">السنة</label>
                    <select name="year" class="form-select">
                        @php $curYear = (int) now()->year; @endphp
                        @for($y = $curYear - 2; $y <= $curYear + 1; $y++)
                            <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الربع</label>
                    <select name="quarter" class="form-select">
                        <option value="1" {{ (int)$quarter === 1 ? 'selected' : '' }}>الربع الأول (يناير - مارس)</option>
                        <option value="2" {{ (int)$quarter === 2 ? 'selected' : '' }}>الربع الثاني (أبريل - يونيو)</option>
                        <option value="3" {{ (int)$quarter === 3 ? 'selected' : '' }}>الربع الثالث (يوليو - سبتمبر)</option>
                        <option value="4" {{ (int)$quarter === 4 ? 'selected' : '' }}>الربع الرابع (أكتوبر - ديسمبر)</option>
                    </select>
                </div>
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
                    <label class="form-label">نوع التسميع</label>
                    <select name="type" class="form-select">
                        <option value="">الكل</option>
                        <option value="new_memorization" {{ request('type') == 'new_memorization' ? 'selected' : '' }}>حفظ جديد</option>
                        <option value="review"          {{ request('type') == 'review' ? 'selected' : '' }}>مراجعة</option>
                        <option value="unmastered"      {{ request('type') == 'unmastered' ? 'selected' : '' }}>حفظ غير متقن</option>
                        <option value="recitation"      {{ request('type') == 'recitation' ? 'selected' : '' }}>سرد</option>
                        <option value="mafsil"          {{ request('type') == 'mafsil' ? 'selected' : '' }}>مفصل</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="rpt-btn rpt-btn-primary flex-fill"><i class="fas fa-search"></i> تطبيق</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="rpt-table-card" style="margin-bottom: 24px;">
    <div class="rpt-table-header">
        <h5><i class="fas fa-microphone"></i> جلسات التسميع</h5>
        <span class="rpt-count-badge">{{ $recSessions->total() }} جلسة</span>
    </div>
    @if($recSessions->isEmpty())
        <div class="rpt-empty"><p>لا توجد جلسات في هذا الربع</p></div>
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
                        <th data-sort="num">الأخطاء</th>
                        <th data-sort="num">الترددات</th>
                        <th data-sort="num">الدرجة</th>
                        <th data-sort>الحالة</th>
                        <th data-sort>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recSessions as $index => $s)
                    <tr>
                        <td data-sort-value="{{ $recSessions->firstItem() + $index }}"><span class="rpt-num">{{ $recSessions->firstItem() + $index }}</span></td>
                        <td data-sort-value="{{ $s->student->user->name ?? '' }}" style="text-align:right;">
                            <a href="{{ route('admin.students.show', $s->student_id) }}" class="rpt-student">
                                <span class="rpt-student-avatar"><i class="fas fa-user-graduate"></i></span>
                                {{ $s->student->user->name ?? '—' }}
                            </a>
                        </td>
                        <td data-sort-value="{{ $s->supervisor->name ?? '' }}" style="font-weight:600;color:var(--primary);">{{ $s->supervisor->name ?? '—' }}</td>
                        <td data-sort-value="{{ $s->type_label }}"><span class="rpt-badge" style="background:rgba(24,43,72,0.06);color:var(--primary);">{{ $s->type_label }}</span></td>
                        <td data-sort-value="{{ $s->parts_label }}" style="font-weight:600;">{{ $s->parts_label }}</td>
                        <td data-sort-value="{{ $s->total_errors ?? 0 }}" style="color:#c0392b;font-weight:700;">{{ $s->total_errors ?? 0 }}</td>
                        <td data-sort-value="{{ $s->total_hesitations ?? 0 }}" style="color:#d97706;font-weight:700;">{{ $s->total_hesitations ?? 0 }}</td>
                        <td data-sort-value="{{ $s->final_score ?? -1 }}">
                            @if($s->final_score !== null)
                                @php $sc = $s->final_score; @endphp
                                <span class="rpt-score {{ $sc >= 90 ? 'high' : ($sc >= 75 ? 'medium' : 'low') }}">{{ number_format($sc, 1) }}%</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="rpt-badge {{ $s->status }}">{{ $s->status_label }}</span></td>
                        <td data-sort-value="{{ $s->session_date?->format('Y-m-d') }}" style="font-weight:600;">{{ $s->session_date?->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="rpt-pagination">{{ $recSessions->withQueryString()->links() }}</div>
    @endif
</div>

<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-calendar-check"></i> سجلات الحضور</h5>
        <span class="rpt-count-badge">{{ $attAttendances->total() }} سجل</span>
    </div>
    @if($attAttendances->isEmpty())
        <div class="rpt-empty"><p>لا توجد سجلات حضور في هذا الربع</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort="num">#</th>
                        <th data-sort>الطالب</th>
                        <th data-sort>المشرف</th>
                        <th data-sort>التاريخ</th>
                        <th data-sort>وقت الحضور</th>
                        <th data-sort>وقت الانصراف</th>
                        <th data-sort>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attAttendances as $index => $a)
                    <tr>
                        <td data-sort-value="{{ $attAttendances->firstItem() + $index }}"><span class="rpt-num">{{ $attAttendances->firstItem() + $index }}</span></td>
                        <td data-sort-value="{{ $a->student->user->name ?? '' }}" style="text-align:right;">
                            <a href="{{ route('admin.students.show', $a->student_id) }}" class="rpt-student">
                                <span class="rpt-student-avatar"><i class="fas fa-user-graduate"></i></span>
                                {{ $a->student->user->name ?? '—' }}
                            </a>
                        </td>
                        <td data-sort-value="{{ $a->supervisor->name ?? '' }}" style="font-weight:600;color:var(--primary);">{{ $a->supervisor->name ?? '—' }}</td>
                        <td data-sort-value="{{ $a->date?->format('Y-m-d') }}" style="font-weight:600;">{{ $a->date?->format('Y/m/d') }}</td>
                        <td>{{ $a->arrival_time ?? '—' }}</td>
                        <td>{{ $a->departure_time ?? '—' }}</td>
                        <td><span class="rpt-badge {{ $a->status }}">{{ ['present'=>'حاضر','absent'=>'غائب','late'=>'متأخر','excused'=>'معذور','holiday'=>'عطلة'][$a->status] ?? $a->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="rpt-pagination">{{ $attAttendances->withQueryString()->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
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
    var recTrend = @json($recTrend ?? []);
    var attTrend = @json($attTrend ?? []);

    var monthLabels = [].concat(recTrend.map(function (t) { return t.month; }), attTrend.map(function (t) { return t.month; }))
        .filter(function (v, i, a) { return a.indexOf(v) === i; }).sort();

    function monthLabel(m) {
        if (!m) return '';
        var parts = m.split('-');
        var months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        return months[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
    }
    var labels = monthLabels.map(monthLabel);

    function lookup(series, key) {
        var map = {};
        series.forEach(function (r) { map[r.month] = r[key] || 0; });
        return monthLabels.map(function (m) { return map[m] || 0; });
    }

    var el1 = document.getElementById('qRecChart');
    if (el1) {
        new Chart(el1, {
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

    var el2 = document.getElementById('qAttChart');
    if (el2) {
        new Chart(el2, {
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
})();
</script>
@endpush
@endsection
