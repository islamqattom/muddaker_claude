{{-- resources/views/components/supervisor-performance-card.blade.php --}}
@props(['supervisor', 'perf'])

@php
    $sid = $supervisor['supervisor_id'];

    $levelMap = [
        'excellent'         => ['label' => 'ممتاز',        'class' => 'lvl-excellent', 'icon' => 'fa-trophy'],
        'good'              => ['label' => 'جيد',          'class' => 'lvl-good',      'icon' => 'fa-thumbs-up'],
        'average'           => ['label' => 'متوسط',        'class' => 'lvl-average',   'icon' => 'fa-minus-circle'],
        'needs_improvement' => ['label' => 'يحتاج تحسين',  'class' => 'lvl-poor',      'icon' => 'fa-exclamation-triangle'],
    ];
    $level = $levelMap[$perf->performanceLevel] ?? $levelMap['needs_improvement'];

    $score       = (float) $perf->performanceScore;
    $scoreWidth  = min(100, max(0, $score));
    $passWidth   = min(100, max(0, (float) $perf->passRate));
    $attendWidth = min(100, max(0, (float) $perf->attendanceRate));
    $perStuWidth = min(100, max(0, ((float) $perf->evaluationsPerStudent / 5) * 100));
    $hasChart    = !empty($perf->weeklyTrend['labels'] ?? []);
@endphp

<div class="sp-card sp-{{ $perf->performanceLevel }}">
    <span class="sp-accent"></span>

    {{-- Header --}}
    <div class="sp-head">
        <div class="sp-identity">
            <div class="sp-avatar"><i class="fas fa-user-tie"></i></div>
            <div class="sp-meta">
                <h6 class="sp-name" title="{{ $supervisor['name'] }}">{{ $supervisor['name'] }}</h6>
                <span class="sp-level {{ $level['class'] }}">
                    <i class="fas {{ $level['icon'] }}"></i> {{ $level['label'] }}
                </span>
            </div>
        </div>

        <div class="sp-score">
            @if($perf->rank > 0)
                <span class="sp-rank" title="الترتيب">#{{ $perf->rank }}<small>/{{ $perf->totalSupervisors }}</small></span>
            @endif
            <div class="sp-ring-wrap">
                <svg viewBox="0 0 36 36" class="sp-ring" aria-hidden="true">
                    <path class="sp-ring-bg" pathLength="100"
                          d="M18 2a16 16 0 1 1 0 32 16 16 0 0 1 0-32"/>
                    <path class="sp-ring-fg" pathLength="100"
                          stroke-dasharray="{{ $scoreWidth }} 100"
                          d="M18 2a16 16 0 1 1 0 32 16 16 0 0 1 0-32"/>
                </svg>
                <span class="sp-score-num">{{ round($score) }}</span>
            </div>
            <span class="sp-score-cap">الأداء العام</span>
        </div>
    </div>

    {{-- Metric tiles --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-ico"><i class="fas fa-user-graduate"></i></span>
            <div class="sp-stat-body">
                <span class="sp-stat-val">{{ $perf->studentsCount }}</span>
                <span class="sp-stat-lbl">الطلاب</span>
            </div>
        </div>
        <div class="sp-stat">
            <span class="sp-stat-ico"><i class="fas fa-microphone-alt"></i></span>
            <div class="sp-stat-body">
                <span class="sp-stat-val">{{ $perf->evaluationsThisMonth }}</span>
                <span class="sp-stat-lbl">تقييمات الشهر</span>
            </div>
        </div>
        <div class="sp-stat">
            <span class="sp-stat-ico"><i class="fas fa-chart-line"></i></span>
            <div class="sp-stat-body">
                <span class="sp-stat-val">{{ $perf->avgScoreThisMonth > 0 ? $perf->avgScoreThisMonth . '%' : '—' }}</span>
                <span class="sp-stat-lbl">متوسط الدرجة</span>
            </div>
        </div>
        <div class="sp-stat">
            <span class="sp-stat-ico"><i class="fas fa-calendar-check"></i></span>
            <div class="sp-stat-body">
                <span class="sp-stat-val">{{ $perf->attendanceRate > 0 ? $perf->attendanceRate . '%' : '—' }}</span>
                <span class="sp-stat-lbl">نسبة الحضور</span>
            </div>
        </div>
    </div>

    {{-- Progress bars --}}
    <div class="sp-bars">
        <div class="sp-bar-row">
            <span class="sp-bar-lbl">نسبة النجاح <b>{{ $perf->passRate > 0 ? $perf->passRate . '%' : '—' }}</b></span>
            <div class="sp-bar sp-bar-pass"><div class="sp-bar-fill" style="width: {{ $passWidth }}%"></div></div>
        </div>
        <div class="sp-bar-row">
            <span class="sp-bar-lbl">معدل التقييم لكل طالب <b>{{ $perf->evaluationsPerStudent }}</b></span>
            <div class="sp-bar"><div class="sp-bar-fill" style="width: {{ $perStuWidth }}%"></div></div>
        </div>
        <div class="sp-bar-row">
            <span class="sp-bar-lbl">الحضور <b>{{ $perf->attendanceRate > 0 ? $perf->attendanceRate . '%' : '—' }}</b></span>
            <div class="sp-bar sp-bar-attend"><div class="sp-bar-fill" style="width: {{ $attendWidth }}%"></div></div>
        </div>
    </div>

    {{-- Pass / Fail --}}
    <div class="sp-split">
        <span class="sp-split-item sp-split-pass"><i class="fas fa-check"></i> {{ $perf->passedCount }} <small>ناجح</small></span>
        <span class="sp-split-item sp-split-fail"><i class="fas fa-times"></i> {{ $perf->failedCount }} <small>راسب</small></span>
    </div>

    {{-- Weekly trend chart --}}
    @if($hasChart)
        <div class="sp-chart">
            <div class="sp-chart-title"><i class="fas fa-chart-area"></i> آخر 4 أسابيع</div>
            <canvas id="spChart{{ $sid }}" data-payload="{{ json_encode($perf->weeklyTrend) }}"></canvas>
        </div>
    @endif
</div>

@once
@push('scripts')
<script>
(function () {
    function renderSupervisorCharts() {
        if (!window.Chart) return;
        document.querySelectorAll('canvas[data-payload]').forEach(function (canvas) {
            if (canvas.dataset.rendered === '1') return;
            var payload;
            try { payload = JSON.parse(canvas.dataset.payload); } catch (e) { return; }
            if (!payload || !payload.labels) return;
            canvas.dataset.rendered = '1';
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: payload.labels,
                    datasets: [
                        { type: 'bar', label: 'تقييمات', data: payload.evaluations, backgroundColor: 'rgba(212,160,48,.55)', borderColor: '#d4a030', borderWidth: 1, borderRadius: 6, yAxisID: 'y', order: 2 },
                        { type: 'line', label: 'متوسط الدرجة', data: payload.avgScore, borderColor: '#182B48', backgroundColor: 'rgba(24,43,72,.12)', borderWidth: 2, tension: .35, pointRadius: 3, pointBackgroundColor: '#182B48', yAxisID: 'y1', order: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, labels: { font: { size: 10 }, boxWidth: 10 } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { beginAtZero: true, ticks: { font: { size: 9 }, precision: 0 }, grid: { color: '#f1f5f9' } },
                        y1: { beginAtZero: true, position: 'right', max: 100, ticks: { font: { size: 9 }, callback: function (v) { return v + '%'; } }, grid: { display: false } }
                    }
                }
            });
        });
    }
    document.addEventListener('DOMContentLoaded', renderSupervisorCharts);
})();
</script>
@endpush
@endonce

@once
<style>
.sp-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: #fff;
    border-radius: 16px;
    padding: 18px;
    border: 1px solid #eef2f7;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}
.sp-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.10);
}
.sp-accent {
    position: absolute; inset: 0 0 auto 0; height: 4px;
    border-radius: 16px 16px 0 0;
}
.sp-excellent .sp-accent { background: linear-gradient(90deg, #d4a030, #f1c75b); }
.sp-good      .sp-accent { background: linear-gradient(90deg, #182B48, #3a5a8a); }
.sp-average   .sp-accent { background: linear-gradient(90deg, #f39c12, #f7b733); }
.sp-needs_improvement .sp-accent { background: linear-gradient(90deg, #e74c3c, #ff7675); }

/* Header */
.sp-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding-top: 4px;
}
.sp-identity { display: flex; align-items: center; gap: 12px; min-width: 0; }
.sp-avatar {
    width: 46px; height: 46px; flex-shrink: 0;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030; font-size: 19px;
}
.sp-excellent .sp-avatar { background: linear-gradient(135deg, #d4a030, #B4802C); color: #fff; }
.sp-meta { min-width: 0; }
.sp-name {
    margin: 0 0 5px;
    color: #182B48; font-size: 15px; font-weight: 700;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 190px;
}
.sp-level {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 999px;
    font-size: 11px; font-weight: 700;
}
.sp-level i { font-size: 10px; }
.lvl-excellent { background: rgba(212,160,48,.14); color: #8a6a18; }
.lvl-good      { background: rgba(24,43,72,.10);  color: #182B48; }
.lvl-average   { background: rgba(243,156,18,.15); color: #a16207; }
.lvl-poor      { background: rgba(231,76,60,.13);  color: #b91c1c; }

/* Score ring */
.sp-score {
    position: relative;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    flex-shrink: 0;
}
.sp-ring-wrap { position: relative; width: 58px; height: 58px; }
.sp-ring { width: 100%; height: 100%; display: block; transform: rotate(-90deg); }
.sp-ring-bg { fill: none; stroke: #eef2f7; stroke-width: 3; }
.sp-ring-fg {
    fill: none; stroke: #d4a030; stroke-width: 3; stroke-linecap: round;
    transition: stroke-dasharray .6s ease;
}
.sp-good .sp-ring-fg             { stroke: #182B48; }
.sp-average .sp-ring-fg          { stroke: #f39c12; }
.sp-needs_improvement .sp-ring-fg { stroke: #e74c3c; }
.sp-score-num {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 800; color: #182B48;
}
.sp-score-cap { font-size: 10px; color: #94a3b8; font-weight: 600; }
.sp-rank {
    position: absolute; top: -4px; inset-inline-start: -8px;
    background: #182B48; color: #fff;
    font-size: 10px; font-weight: 800;
    padding: 2px 7px; border-radius: 999px;
    box-shadow: 0 3px 8px rgba(24,43,72,.25);
}
.sp-rank small { font-weight: 600; opacity: .7; }

/* Stat tiles */
.sp-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.sp-stat {
    display: flex; align-items: center; gap: 10px;
    background: #f8fafc; border: 1px solid #eef2f7;
    border-radius: 12px; padding: 10px 12px;
}
.sp-stat-ico {
    width: 34px; height: 34px; flex-shrink: 0;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(24,43,72,.06); color: #182B48; font-size: 13px;
}
.sp-stat-body { display: flex; flex-direction: column; min-width: 0; }
.sp-stat-val { font-size: 15px; font-weight: 800; color: #182B48; line-height: 1.1; }
.sp-stat-lbl { font-size: 11px; color: #94a3b8; font-weight: 600; }

/* Bars */
.sp-bars { display: flex; flex-direction: column; gap: 9px; }
.sp-bar-row { display: flex; flex-direction: column; gap: 5px; }
.sp-bar-lbl {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 11px; color: #64748b; font-weight: 600;
}
.sp-bar-lbl b { color: #182B48; font-weight: 800; }
.sp-bar { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.sp-bar-fill {
    height: 100%; border-radius: 999px;
    background: linear-gradient(90deg, #d4a030, #B4802C);
    transition: width .6s ease;
}
.sp-bar-pass   .sp-bar-fill { background: linear-gradient(90deg, #16a085, #1abc9c); }
.sp-bar-attend .sp-bar-fill { background: linear-gradient(90deg, #2980b9, #5dade2); }

/* Pass / fail */
.sp-split { display: flex; gap: 10px; }
.sp-split-item {
    flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 8px 10px; border-radius: 10px;
    font-size: 14px; font-weight: 800;
}
.sp-split-item small { font-size: 11px; font-weight: 600; opacity: .85; }
.sp-split-pass { background: rgba(22,160,133,.10); color: #16a085; }
.sp-split-fail { background: rgba(231,76,60,.10); color: #e74c3c; }

/* Chart */
.sp-chart { padding-top: 12px; border-top: 1px dashed #eef2f7; }
.sp-chart-title {
    font-size: 11px; color: #64748b; font-weight: 600;
    margin-bottom: 6px; display: inline-flex; align-items: center; gap: 6px;
}
.sp-chart canvas { width: 100% !important; height: 92px !important; }

@media (max-width: 420px) {
    .sp-stats { grid-template-columns: 1fr; }
}
</style>
@endonce
