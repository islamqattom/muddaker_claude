@extends('layouts.app')
@section('title', 'التقارير')
@section('page-title', 'التقارير')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
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
            <h4>التقارير والإحصائيات</h4>
            <p>عرض شامل للتقارير والإحصائيات الخاصة بالتسميع والحضور والإنجاز</p>
        </div>
    </div>
</div>

<div class="rpt-kpi-grid">
    <div class="rpt-kpi" style="--kpi-color:#182B48;--kpi-bg:rgba(24,43,72,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total_students'] }}</div>
        <div class="rpt-kpi-label">إجمالي الطلاب</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#3498db;--kpi-bg:rgba(52,152,219,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-microphone"></i></div>
        <div class="rpt-kpi-value">{{ $stats['total_sessions'] }}</div>
        <div class="rpt-kpi-label">إجمالي الجلسات</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#27ae60;--kpi-bg:rgba(39,174,96,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['passed_sessions'] }}</div>
        <div class="rpt-kpi-label">جلسات ناجحة</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#c0392b;--kpi-bg:rgba(192,57,43,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ $stats['failed_sessions'] }}</div>
        <div class="rpt-kpi-label">جلسات راسبة</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#d4a030;--kpi-bg:rgba(212,160,48,0.10);">
        <div class="rpt-kpi-icon"><i class="fas fa-percentage"></i></div>
        <div class="rpt-kpi-value">{{ $successRate }}%</div>
        <div class="rpt-kpi-label">نسبة النجاح</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <div class="rpt-chart-card">
        <h6><i class="fas fa-chart-line"></i> أداء التسميع — الشهر الحالي</h6>
        <div class="rpt-chart-wrap"><canvas id="supRecMonthChart"></canvas></div>
    </div>
    <div class="rpt-chart-card">
        <h6><i class="fas fa-calendar-check"></i> الحضور — الشهر الحالي</h6>
        <div class="rpt-chart-wrap"><canvas id="supAttMonthChart"></canvas></div>
    </div>
</div>

<div class="rpt-chart-card" style="margin-bottom: 24px;">
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

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#182B48,#2A4570);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#d4a030,#B4802C);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;flex-shrink:0;"><i class="fas fa-quran"></i></div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">تقرير التسميع</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">جلسات التسميع والاختبارات</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">عرض تقارير شاملة لجلسات التسميع مع إحصائيات النجاح والرسوب، الرسوم البيانية، والتصدير.</p>
            <a href="{{ route('supervisor.reports.recitation') }}" class="rpt-btn rpt-btn-primary"><i class="fas fa-chart-line"></i> عرض التقرير</a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#059669,#10b981);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#fff8e1,#d4a030);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#059669;font-size:22px;flex-shrink:0;"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">تقرير الحضور</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">سجلات الحضور والانصراف</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">عرض تقارير الحضور مع نسب الحضور والغياب، الرسوم البيانية، والتصدير.</p>
            <a href="{{ route('supervisor.reports.attendance') }}" class="rpt-btn rpt-btn-success"><i class="fas fa-chart-line"></i> عرض التقرير</a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#0891b2,#06b6d4);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#fff8e1,#d4a030);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#0891b2;font-size:22px;flex-shrink:0;"><i class="fas fa-users"></i></div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">تقرير طالب</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">تقرير مفصل لكل طالب</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">تقرير مفصل لكل طالب يشمل بيانات التسميع والحضور والإنجاز.</p>
            <a href="{{ route('supervisor.students.index') }}" class="rpt-btn" style="background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;box-shadow:0 4px 12px rgba(8,145,178,0.25);"><i class="fas fa-users"></i> اختيار طالب</a>
        </div>
    </div>

    <div class="report-card" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--card-shadow);border:1px solid rgba(24,43,72,0.05);">
        <div style="background:linear-gradient(135deg,#7c3aed,#5b21b6);padding:20px;display:flex;align-items:center;gap:18px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#fff8e1,#d4a030);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#7c3aed;font-size:22px;flex-shrink:0;"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div style="color:#fff;font-size:17px;font-weight:700;">التقرير الربعي</div>
                <div style="color:rgba(255,255,255,.7);font-size:12px;margin-top:3px;">تقرير شامل لكل ربع من السنة</div>
            </div>
        </div>
        <div style="padding:20px;">
            <p style="color:#6c7a89;font-size:13px;margin-bottom:14px;line-height:1.6;">تقرير ربعي يجمع إحصائيات التسميع والحضور على مدى 3 أشهر، مع رسوم بيانية شهرية وفلاتر متقدمة وتصدير.</p>
            <a href="{{ route('supervisor.reports.quarter') }}" class="rpt-btn" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;box-shadow:0 4px 12px rgba(124,58,237,0.25);"><i class="fas fa-chart-line"></i> عرض التقرير</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    if (!window.Chart) return;
    var recTrend = @json($monthTrend ?? []);
    var attTrend = @json($attendanceTrend ?? []);
    var recEl = document.getElementById('supRecMonthChart');
    if (recEl) {
        new Chart(recEl, {
            type: 'bar',
            data: {
                labels: recTrend.map(function (t) { return t.date; }),
                datasets: [
                    { type: 'bar',  label: 'إجمالي',  data: recTrend.map(function (t) { return t.total; }), backgroundColor: 'rgba(24,43,72,0.65)', borderColor: '#182B48', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                    { type: 'line', label: 'ناجح',    data: recTrend.map(function (t) { return t.passed; }), borderColor: '#d4a030', backgroundColor: 'rgba(212,160,48,0.15)', borderWidth: 2, tension: 0.35, pointRadius: 3, yAxisID: 'y' }
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
    }
    var attEl = document.getElementById('supAttMonthChart');
    if (attEl) {
        new Chart(attEl, {
            type: 'line',
            data: {
                labels: attTrend.map(function (t) { return t.date; }),
                datasets: [
                    { label: 'إجمالي', data: attTrend.map(function (t) { return t.total; }), borderColor: '#182B48', backgroundColor: 'rgba(24,43,72,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
                    { label: 'حاضر',  data: attTrend.map(function (t) { return t.present; }), borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.12)', tension: 0.35, fill: true, pointRadius: 3, yAxisID: 'y' },
                    { label: 'نسبة الحضور %', data: attTrend.map(function (t) { return t.rate; }), borderColor: '#d4a030', borderDash: [5, 4], borderWidth: 2, tension: 0.35, pointRadius: 2, yAxisID: 'y1' }
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
})();
</script>
@endpush
@endsection
