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
    $successRate = $stats['total_sessions'] > 0
        ? round(($stats['passed_sessions'] / $stats['total_sessions']) * 100)
        : 0;
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

<div class="rpt-kpi-grid">
    <div class="rpt-kpi" style="--kpi-color:#182B48; --kpi-bg:rgba(24,43,72,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total_students'] }}</div>
        <div class="rpt-kpi-label">إجمالي الطلاب</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#3498db; --kpi-bg:rgba(52,152,219,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-microphone"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total_sessions'] }}</div>
        <div class="rpt-kpi-label">إجمالي الجلسات</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#27ae60; --kpi-bg:rgba(39,174,96,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['passed_sessions'] }}</div>
        <div class="rpt-kpi-label">جلسات ناجحة</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#c0392b; --kpi-bg:rgba(192,57,43,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['failed_sessions'] }}</div>
        <div class="rpt-kpi-label">جلسات راسبة</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#d4a030; --kpi-bg:rgba(212,160,48,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-percentage"></i></div>
        <div class="rpt-kpi-value">{{ $successRate }}%</div>
        <div class="rpt-kpi-label">نسبة النجاح الإجمالية</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#8e44ad; --kpi-bg:rgba(142,68,173,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-star"></i></div>
        <div class="rpt-kpi-value">{{ $stats['avg_score'] }}%</div>
        <div class="rpt-kpi-label">متوسط الدرجات</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-line"></i> أداء التسميع — الشهر الحالي</h6>
        <div class="rpt-chart-wrap"><canvas id="adminRecMonthChart"></canvas></div>
    </div>
    <div class="rpt-chart-card">
        <h6><i class="fas fa-trophy"></i> أفضل الطلاب أداءً (الشهر)</h6>
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
            <div class="rpt-empty"><p>لا توجد بيانات لهذا الشهر</p></div>
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
</div>

@if($stats['total_sessions'] > 0)
<div class="rpt-chart-card">
    <h6><i class="fas fa-percentage"></i> نسبة النجاح الإجمالية</h6>
    <div style="height:32px;background:#e9ecef;border-radius:16px;overflow:hidden;position:relative;">
        <div style="height:100%;width:{{ $successRate }}%;background:linear-gradient(90deg,#27ae60,#2ecc71);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:13px;transition:width 1s ease;">
            {{ $successRate }}% نجاح
        </div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:14px;font-size:13px;">
        <span style="color:#6c7a89;"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#27ae60;margin-left:6px;"></span>ناجح: <b style="color:#182B48;">{{ $stats['passed_sessions'] }} جلسة</b></span>
        <span style="color:#6c7a89;"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#c0392b;margin-left:6px;"></span>راسب: <b style="color:#182B48;">{{ $stats['failed_sessions'] }} جلسة</b></span>
    </div>
</div>
@endif

@push('scripts')
<script>
(function () {
    if (!window.Chart) return;
    var el = document.getElementById('adminRecMonthChart');
    if (!el) return;
    var trend = @json($monthTrend ?? []);
    var labels = trend.map(function (t) { return t.date; });
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
