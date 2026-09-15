@extends('layouts.app')
@section('title', 'التقارير الشاملة')
@section('page-title', 'التقارير الشاملة')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
@endsection

@php
    $periodLabels = [
        'today'   => 'اليوم',
        'week'    => 'هذا الأسبوع',
        'month'   => 'الشهر الحالي',
        'quarter' => 'الربع الحالي',
        'half'    => 'النصف الحالي',
        'year'    => 'السنة الحالية',
        'all'     => 'كل الفترات',
        'custom'  => 'فترة مخصصة',
    ];
    $periodLabel = $periodLabels[$period] ?? 'الشهر الحالي';

    $linkParams = ['period' => $period];
    if ($period === 'custom') {
        $linkParams['from_date'] = $fromDate?->format('Y-m-d');
        $linkParams['to_date']   = $toDate?->format('Y-m-d');
    }
    $recUrl = route('admin.reports.recitation', $linkParams);
    $attUrl = route('admin.reports.attendance', $linkParams);
@endphp

@section('content')
@include('admin.reports._styles')

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="rpt-header-text">
            <h4>التقارير الشاملة</h4>
            <p>عرض وإحصائيات تقارير جمعية تحفيظ القرآن الكريم</p>
        </div>
    </div>
</div>

<div class="rpt-filter">
    <div class="rpt-filter-header">
        <i class="fas fa-filter"></i>
        <span>تحديد الفترة</span>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الفترة</label>
                    <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                        <option value="all"    {{ request('period') == 'all' ? 'selected' : '' }}>الكل</option>
                        <option value="today"  {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                        <option value="week"   {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                        <option value="month"  {{ request('period') == 'month' || !request('period') ? 'selected' : '' }}>شهر</option>
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
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="rpt-btn rpt-btn-primary flex-fill"><i class="fas fa-search"></i> تطبيق</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="rpt-kpi-grid">
    <a href="{{ route('admin.students.index') }}" class="rpt-kpi" style="--kpi-color:#182B48; --kpi-bg:rgba(24,43,72,0.10); display:block; text-decoration:none;">
        <div class="rpt-kpi-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="rpt-kpi-value">{{ $totals['total_students'] }}</div>
        <div class="rpt-kpi-label">إجمالي الطلاب</div>
        <div class="rpt-kpi-sub">منذ البداية</div>
    </a>
    <a href="{{ $recUrl }}" class="rpt-kpi" style="--kpi-color:#3498db; --kpi-bg:rgba(52,152,219,0.10); display:block; text-decoration:none;">
        <div class="rpt-kpi-icon"><i class="fas fa-microphone"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total'] }}</div>
        <div class="rpt-kpi-label">جلسات الفترة</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </a>
    <a href="{{ $recUrl }}" class="rpt-kpi" style="--kpi-color:#27ae60; --kpi-bg:rgba(39,174,96,0.10); display:block; text-decoration:none;">
        <div class="rpt-kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['passed'] }}</div>
        <div class="rpt-kpi-label">جلسات ناجحة</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </a>
    <a href="{{ $recUrl }}" class="rpt-kpi" style="--kpi-color:#c0392b; --kpi-bg:rgba(192,57,43,0.10); display:block; text-decoration:none;">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['failed'] }}</div>
        <div class="rpt-kpi-label">جلسات راسبة</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </a>
    <a href="{{ $recUrl }}" class="rpt-kpi" style="--kpi-color:#d4a030; --kpi-bg:rgba(212,160,48,0.10); display:block; text-decoration:none;">
        <div class="rpt-kpi-icon"><i class="fas fa-percentage"></i></div>
        <div class="rpt-kpi-value">{{ $stats['pass_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة النجاح</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </a>
    <a href="{{ $attUrl }}" class="rpt-kpi" style="--kpi-color:#059669; --kpi-bg:rgba(5,150,105,0.10); display:block; text-decoration:none;">
        <div class="rpt-kpi-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="rpt-kpi-value">{{ $attStats['attendance_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة الحضور</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </a>
    <a href="{{ $attUrl }}" class="rpt-kpi" style="--kpi-color:#e74c3c; --kpi-bg:rgba(231,76,60,0.10); display:block; text-decoration:none;">
        <div class="rpt-kpi-icon"><i class="fas fa-user-times"></i></div>
        <div class="rpt-kpi-value">{{ $attStats['absent'] }}</div>
        <div class="rpt-kpi-label">أيام الغياب</div>
        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
    </a>
</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="rpt-chart-card" style="margin-bottom:0;">
        <h6><i class="fas fa-chart-line"></i> أداء التسميع — {{ $periodLabel }}</h6>
        @if(!empty($trend))
            <div class="rpt-chart-wrap"><canvas id="adminRecMonthChart"></canvas></div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات لهذه الفترة</p></div>
        @endif
    </div>
    <div class="rpt-chart-card" style="margin-bottom:0;">
        <h6><i class="fas fa-trophy"></i> أفضل الطلاب أداءً ({{ $periodLabel }})</h6>
        @if(!empty($topStudents))
            <div class="rpt-mini-list">
                @foreach($topStudents as $i => $s)
                    <div class="rpt-mini-row r{{ $i+1 }}">
                        <span class="rpt-mini-rank">{{ $i+1 }}</span>
                        <span class="rpt-mini-name">{{ $s['name'] }}</span>
                        <div class="rpt-mini-bar"><div class="rpt-mini-bar-fill" style="width: {{ min(100, $s['avg_score']) }}%"></div></div>
                        <span class="rpt-mini-score">{{ $s['avg_score'] }}%</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rpt-empty"><p>لا توجد بيانات لهذه الفترة</p></div>
        @endif
    </div>
</div>

<div class="reports-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#182B48,#2A4570);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#d4a030,#B4802C);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;flex-shrink:0;">
                <i class="fas fa-quran"></i>
            </div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">تقارير التسميع</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">جلسات التسميع والاختبارات</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">
                عرض تقارير شاملة لجلسات التسميع مع إحصائيات النجاح والرسوب، الرسوم البيانية، والتصدير (PDF / Excel / طباعة).
            </p>
            <a href="{{ route('admin.reports.recitation') }}" class="rpt-btn rpt-btn-primary">
                <i class="fas fa-chart-line"></i> عرض التقارير
            </a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#059669,#10b981);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#fff8e1,#d4a030);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#059669;font-size:22px;flex-shrink:0;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">تقارير الحضور</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">سجلات الحضور والانصراف</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">
                عرض تقارير حضور وانصراف الطلاب مع نسب الحضور والغياب والتأخير، الرسوم البيانية، والتصدير.
            </p>
            <a href="{{ route('admin.reports.attendance') }}" class="rpt-btn rpt-btn-success">
                <i class="fas fa-chart-line"></i> عرض التقارير
            </a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#7c3aed,#5b21b6);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#fff8e1,#d4a030);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#7c3aed;font-size:22px;flex-shrink:0;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">التقرير الربعي</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">تقرير شامل لكل ربع من السنة</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">
                تقرير ربعي يجمع إحصائيات التسميع والحضور على مدى 3 أشهر، مع رسوم بيانية شهرية وفلاتر متقدمة وتصدير.
            </p>
            <a href="{{ route('admin.reports.quarter') }}" class="rpt-btn" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;box-shadow:0 4px 12px rgba(124,58,237,0.25);">
                <i class="fas fa-chart-line"></i> عرض التقرير
            </a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#d97706,#b45309);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#fff8e1,#d4a030);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#d97706;font-size:22px;flex-shrink:0;">
                <i class="fas fa-calendar"></i>
            </div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">التقرير نصف السنوي</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">تقرير شامل لكل نصف سنة</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">
                تقرير نصف سنوي يجمع إحصائيات التسميع والحضور على مدى 6 أشهر، مع رسوم بيانية شهرية وفلاتر متقدمة وتصدير.
            </p>
            <a href="{{ route('admin.reports.half') }}" class="rpt-btn" style="background:linear-gradient(135deg,#d97706,#b45309);color:#fff;box-shadow:0 4px 12px rgba(217,119,6,0.25);">
                <i class="fas fa-chart-line"></i> عرض التقرير
            </a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#0f766e,#134e4a);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#fff8e1,#d4a030);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#0f766e;font-size:22px;flex-shrink:0;">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">التقرير السنوي</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">تقرير شامل لكل سنة كاملة</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">
                تقرير سنوي يجمع إحصائيات التسميع والحضور على مدى 12 شهراً، مع رسوم بيانية شهرية وفلاتر متقدمة وتصدير.
            </p>
            <a href="{{ route('admin.reports.year') }}" class="rpt-btn" style="background:linear-gradient(135deg,#0f766e,#134e4a);color:#fff;box-shadow:0 4px 12px rgba(15,118,110,0.25);">
                <i class="fas fa-chart-line"></i> عرض التقرير
            </a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#dbeafe,#93c5fd);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#2563eb;font-size:22px;flex-shrink:0;">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">تقرير طالب</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">جلسات التسميع والحضور لطالب محدد</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">
                اختر طالباً لعرض جلسات التسميع وسجلات الحضور الخاصة به في الفترة المختارة، مع إمكانية التصدير إلى PDF.
            </p>
            <form method="GET" action="{{ route('admin.reports.student', ['student' => '__ID__']) }}" id="adminStudentReportForm">
                <select name="student_picker" class="form-select form-select-sm mb-2" required>
                    <option value="" disabled selected>اختر طالباً…</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->user->name ?? '—' }}@if($s->student_number) — {{ $s->student_number }}@endif</option>
                    @endforeach
                </select>
                <button type="submit" class="rpt-btn" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;box-shadow:0 4px 12px rgba(37,99,235,0.25);width:100%;">
                    <i class="fas fa-file-alt"></i> عرض التقرير
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    document.getElementById('fromDateCol').style.display = period === 'custom' ? '' : 'none';
    document.getElementById('toDateCol').style.display   = period === 'custom' ? '' : 'none';
}
document.getElementById('adminStudentReportForm')?.addEventListener('submit', function (e) {
    var sel = this.querySelector('select[name="student_picker"]');
    if (!sel.value) return;
    var id = sel.value;
    this.action = this.action.replace('__ID__', encodeURIComponent(id));
});
(function () {
    if (!window.Chart) return;
    var el = document.getElementById('adminRecMonthChart');
    if (!el) return;
    var trend = @json($trend ?? []);
    var labels = trend.map(function (t) {
        var p = t.date.split('-');
        return p[2] + '/' + p[1];
    });
    var totals = trend.map(function (t) { return t.total; });
    var passes = trend.map(function (t) { return t.passed; });
    new Chart(el, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { type: 'bar',  label: 'إجمالي الجلسات', data: totals, backgroundColor: 'rgba(24,43,72,0.65)', borderColor: '#182B48', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                { type: 'line', label: 'ناجحة',            data: passes, borderColor: '#d4a030', backgroundColor: 'rgba(212,160,48,0.15)', borderWidth: 2, tension: 0.35, pointRadius: 3, yAxisID: 'y' }
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
@endsection
