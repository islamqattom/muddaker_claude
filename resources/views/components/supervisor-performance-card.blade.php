{{-- resources/views/components/supervisor-performance-card.blade.php --}}
@props(['supervisor', 'perf'])

@php
    $sid = $supervisor['supervisor_id'];

    $levelMap = [
        'excellent'        => ['label' => 'ممتاز',   'class' => 'lvl-excellent', 'icon' => 'fa-trophy'],
        'good'             => ['label' => 'جيد',     'class' => 'lvl-good',      'icon' => 'fa-thumbs-up'],
        'average'          => ['label' => 'متوسط',   'class' => 'lvl-average',   'icon' => 'fa-minus-circle'],
        'needs_improvement'=> ['label' => 'يحتاج تحسين', 'class' => 'lvl-poor', 'icon' => 'fa-exclamation-triangle'],
    ];
    $level = $levelMap[$perf->performanceLevel] ?? $levelMap['needs_improvement'];

    $score = (float) $perf->performanceScore;

    $passWidth     = min(100, max(0, (float) $perf->passRate));
    $attendWidth   = min(100, max(0, (float) $perf->attendanceRate));
    $scoreWidth    = min(100, max(0, $score));
    $perStuWidth   = min(100, max(0, ((float) $perf->evaluationsPerStudent / 5) * 100));
@endphp

<div class="sp-card sp-{{ $perf->performanceLevel }}">
    <div class="sp-card-bg"></div>

    <div class="sp-header">
        <div class="sp-rank">
            @if($perf->rank > 0)
                <span class="sp-rank-num">#{{ $perf->rank }}</span>
                <span class="sp-rank-total">من {{ $perf->totalSupervisors }}</span>
            @else
                <span class="sp-rank-num">—</span>
            @endif
        </div>

        <div class="sp-avatar">
            <i class="fas fa-user-tie"></i>
        </div>

        <div class="sp-info">
            <h6 class="sp-name">{{ $supervisor['name'] }}</h6>
            <span class="sp-level {{ $level['class'] }}">
                <i class="fas {{ $level['icon'] }}"></i>
                {{ $level['label'] }}
            </span>
        </div>

        <div class="sp-score" title="الأداء العام">
            <div class="sp-score-ring">
                <svg viewBox="0 0 36 36" class="sp-svg">
                    <path class="sp-ring-bg"
                          d="M18 2a16 16 0 1 1 0 32 16 16 0 0 1 0-32"/>
                    <path class="sp-ring-fg"
                          stroke-dasharray="{{ $scoreWidth }}, 100"
                          d="M18 2a16 16 0 1 1 0 32 16 16 0 0 1 0-32"/>
                </svg>
                <span class="sp-score-num">{{ round($score, 0) }}</span>
            </div>
            <span class="sp-score-label">الأداء</span>
        </div>
    </div>

    <div class="sp-metrics">
        <div class="sp-metric">
            <div class="sp-metric-row">
                <span class="sp-metric-label"><i class="fas fa-user-graduate"></i> الطلاب</span>
                <span class="sp-metric-value">{{ $perf->studentsCount }}</span>
            </div>
            <div class="sp-metric-sub">معدل التقييم: {{ $perf->evaluationsPerStudent }}</div>
        </div>

        <div class="sp-metric">
            <div class="sp-metric-row">
                <span class="sp-metric-label"><i class="fas fa-microphone-alt"></i> تقييمات الشهر</span>
                <span class="sp-metric-value">{{ $perf->evaluationsThisMonth }}</span>
            </div>
            <div class="sp-bar">
                <div class="sp-bar-fill" style="width: {{ $perStuWidth }}%"></div>
            </div>
        </div>

        <div class="sp-metric">
            <div class="sp-metric-row">
                <span class="sp-metric-label"><i class="fas fa-chart-line"></i> متوسط الدرجة</span>
                <span class="sp-metric-value">
                    {{ $perf->avgScoreThisMonth > 0 ? $perf->avgScoreThisMonth . '%' : '—' }}
                </span>
            </div>
            <div class="sp-bar sp-bar-score">
                <div class="sp-bar-fill" style="width: {{ min(100, max(0, (float) $perf->avgScoreThisMonth)) }}%"></div>
            </div>
        </div>

        <div class="sp-metric">
            <div class="sp-metric-row">
                <span class="sp-metric-label"><i class="fas fa-check-circle"></i> نسبة النجاح</span>
                <span class="sp-metric-value">
                    {{ $perf->passRate > 0 ? $perf->passRate . '%' : '—' }}
                </span>
            </div>
            <div class="sp-bar sp-bar-pass">
                <div class="sp-bar-fill" style="width: {{ $passWidth }}%"></div>
            </div>
        </div>

        <div class="sp-metric">
            <div class="sp-metric-row">
                <span class="sp-metric-label"><i class="fas fa-calendar-check"></i> الحضور</span>
                <span class="sp-metric-value">
                    {{ $perf->attendanceRate > 0 ? $perf->attendanceRate . '%' : '—' }}
                </span>
            </div>
            <div class="sp-bar sp-bar-attend">
                <div class="sp-bar-fill" style="width: {{ $attendWidth }}%"></div>
            </div>
        </div>

        <div class="sp-metric sp-metric-split">
            <div class="sp-split">
                <i class="fas fa-check"></i>
                <span class="sp-split-num">{{ $perf->passedCount }}</span>
                <span class="sp-split-label">ناجح</span>
            </div>
            <div class="sp-split sp-split-fail">
                <i class="fas fa-times"></i>
                <span class="sp-split-num">{{ $perf->failedCount }}</span>
                <span class="sp-split-label">راسب</span>
            </div>
        </div>
    </div>

    @if(!empty($perf->weeklyTrend['labels'] ?? []))
        <div class="sp-chart">
            <div class="sp-chart-title">
                <i class="fas fa-chart-area"></i>
                آخر 4 أسابيع
            </div>
            <canvas id="spChart{{ $sid }}"></canvas>
        </div>
    @endif
</div>

<style>
.sp-card {
    position: relative;
    background: #fff;
    border-radius: 18px;
    padding: 20px;
    border: 1px solid #eef2f7;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}
.sp-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.10);
}
.sp-card-bg {
    position: absolute; inset: 0 0 auto 0; height: 4px;
}
.sp-excellent .sp-card-bg { background: linear-gradient(90deg, #d4a030, #f1c75b); }
.sp-good      .sp-card-bg { background: linear-gradient(90deg, #182B48, #3a5a8a); }
.sp-average   .sp-card-bg { background: linear-gradient(90deg, #f39c12, #f7b733); }
.sp-needs_improvement .sp-card-bg { background: linear-gradient(90deg, #e74c3c, #ff7675); }

.sp-card.sp-excellent { border-color: rgba(212,160,48,.35); }
.sp-card.sp-good      { border-color: rgba(24,43,72,.25); }
.sp-card.sp-average   { border-color: rgba(243,156,18,.35); }
.sp-card.sp-needs_improvement { border-color: rgba(231,76,60,.35); }

.sp-header {
    display: grid;
    grid-template-columns: auto auto 1fr auto;
    align-items: center;
    gap: 12px;
    padding-bottom: 14px;
    margin-bottom: 14px;
    border-bottom: 1px dashed #eef2f7;
}

.sp-rank {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 48px;
    padding: 6px 8px;
    border-radius: 10px;
    background: #f8fafc;
}
.sp-rank-num {
    font-size: 16px;
    font-weight: 800;
    color: #182B48;
    line-height: 1;
}
.sp-rank-total {
    font-size: 9px;
    color: #94a3b8;
    margin-top: 2px;
}

.sp-avatar {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
}
.sp-excellent .sp-avatar { background: linear-gradient(135deg, #d4a030, #B4802C); color: #fff; }

.sp-info { min-width: 0; }
.sp-name {
    margin: 0 0 4px 0;
    color: #182B48;
    font-size: 15px;
    font-weight: 700;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sp-level {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 11px; font-weight: 700;
}
.sp-level i { font-size: 10px; }
.lvl-excellent { background: rgba(212,160,48,.12); color: #8a6a18; }
.lvl-good      { background: rgba(24,43,72,.10);  color: #182B48; }
.lvl-average   { background: rgba(243,156,18,.12); color: #a16207; }
.lvl-poor      { background: rgba(231,76,60,.12);  color: #b91c1c; }

.sp-score {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.sp-score-ring {
    position: relative;
    width: 54px; height: 54px;
}
.sp-svg { transform: rotate(-90deg); }
.sp-ring-bg {
    fill: none;
    stroke: #eef2f7;
    stroke-width: 3;
}
.sp-ring-fg {
    fill: none;
    stroke: #d4a030;
    stroke-width: 3;
    stroke-linecap: round;
    transition: stroke-dasharray .6s ease;
}
.sp-good .sp-ring-fg             { stroke: #182B48; }
.sp-average .sp-ring-fg          { stroke: #f39c12; }
.sp-needs_improvement .sp-ring-fg { stroke: #e74c3c; }
.sp-excellent .sp-ring-fg        { stroke: #d4a030; }

.sp-score-num {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 800; color: #182B48;
}
.sp-score-label { font-size: 10px; color: #94a3b8; font-weight: 600; }

.sp-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 14px;
    margin-bottom: 14px;
}
.sp-metric { display: flex; flex-direction: column; gap: 6px; }
.sp-metric-row {
    display: flex; align-items: center; justify-content: space-between;
}
.sp-metric-label {
    font-size: 11px; color: #64748b; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px;
}
.sp-metric-label i { color: #d4a030; font-size: 10px; }
.sp-metric-value { font-size: 14px; font-weight: 800; color: #182B48; }
.sp-metric-sub   { font-size: 10px; color: #94a3b8; }

.sp-bar {
    height: 6px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
}
.sp-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #d4a030, #B4802C);
    border-radius: 999px;
    transition: width .6s ease;
}
.sp-bar-score .sp-bar-fill { background: linear-gradient(90deg, #182B48, #3a5a8a); }
.sp-bar-pass  .sp-bar-fill { background: linear-gradient(90deg, #16a085, #1abc9c); }
.sp-bar-attend .sp-bar-fill { background: linear-gradient(90deg, #2980b9, #5dade2); }

.sp-metric-split {
    grid-column: 1 / -1;
    flex-direction: row;
    gap: 10px;
}
.sp-split {
    flex: 1;
    display: flex; align-items: center; gap: 8px;
    background: rgba(22,160,133,.08);
    color: #16a085;
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 11px;
}
.sp-split-fail { background: rgba(231,76,60,.08); color: #e74c3c; }
.sp-split-num   { font-size: 14px; font-weight: 800; color: inherit; }
.sp-split-label { font-weight: 600; }

.sp-chart {
    margin-top: 6px;
    padding-top: 12px;
    border-top: 1px dashed #eef2f7;
}
.sp-chart-title {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 6px;
    display: inline-flex; align-items: center; gap: 6px;
}
.sp-chart canvas { width: 100% !important; height: 90px !important; }
</style>

@once
    @push('scripts')
    <script>
    window.renderSupervisorPerformanceCharts = window.renderSupervisorPerformanceCharts || function () {
        document.querySelectorAll('[id^="spChart"]').forEach(function (canvas) {
            if (canvas.dataset.rendered === '1') return;
            var payload = canvas.dataset.payload ? JSON.parse(canvas.dataset.payload) : null;
            if (!payload) return;
            canvas.dataset.rendered = '1';
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: payload.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'تقييمات',
                            data: payload.evaluations,
                            backgroundColor: 'rgba(212,160,48,.55)',
                            borderColor: '#d4a030',
                            borderWidth: 1,
                            borderRadius: 6,
                            yAxisID: 'y',
                            order: 2,
                        },
                        {
                            type: 'line',
                            label: 'متوسط الدرجة',
                            data: payload.avgScore,
                            borderColor: '#182B48',
                            backgroundColor: 'rgba(24,43,72,.12)',
                            borderWidth: 2,
                            tension: .35,
                            pointRadius: 3,
                            pointBackgroundColor: '#182B48',
                            yAxisID: 'y1',
                            order: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, labels: { font: { size: 10 }, boxWidth: 10 } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { beginAtZero: true, ticks: { font: { size: 9 }, precision: 0 }, grid: { color: '#f1f5f9' } },
                        y1: { beginAtZero: true, position: 'right', max: 100, ticks: { font: { size: 9 }, callback: function(v){return v + '%'} }, grid: { display: false } }
                    }
                }
            });
        });
    };
    document.addEventListener('DOMContentLoaded', window.renderSupervisorPerformanceCharts);
    </script>
    @endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var canvas = document.getElementById('spChart{{ $sid }}');
    if (!canvas) return;
    canvas.dataset.payload = @json($perf->weeklyTrend);
    if (window.renderSupervisorPerformanceCharts) window.renderSupervisorPerformanceCharts();
});
</script>
@endpush
