@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
    .report-tabs {
        background: #fff;
        border-radius: 13px;
        padding: 12px 16px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        margin-bottom: 16px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .report-tab {
        padding: 8px 16px;
        border-radius: 9px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f0f4f8;
        color: #182B48;
        transition: all 0.2s ease;
    }
    .report-tab:hover { background: rgba(24, 43, 72, 0.08); }
    .report-tab.active {
        background: linear-gradient(135deg, #182B48, #2A4570);
        color: #fff;
    }
    .report-card {
        background: #fff;
        border-radius: 13px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        margin-bottom: 16px;
        overflow: hidden;
    }
    .report-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #eef1f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .report-card-header h3 { margin: 0; font-size: 15px; color: #182B48; }
    .report-card-body { padding: 0; }
    .report-table { width: 100%; border-collapse: collapse; }
    .report-table th, .report-table td {
        padding: 11px 14px;
        text-align: right;
        border-bottom: 1px solid #eef1f6;
        font-size: 13px;
    }
    .report-table thead th {
        background: rgba(24, 43, 72, 0.04);
        color: #182B48;
        font-weight: 700;
        font-size: 11px;
    }
    .report-table tbody tr:last-child td { border-bottom: none; }
    .criterion-name { color: #182B48; font-weight: 700; }
    .criterion-meta { color: #6b7a90; font-size: 12px; margin-top: 4px; }
    .progress {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .progress-bar-wrap {
        flex: 1;
        height: 8px;
        background: #eef1f6;
        border-radius: 999px;
        overflow: hidden;
        min-width: 80px;
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .progress-bar-fill.low { background: linear-gradient(135deg, #e67e22, #f39c12); }
    .progress-bar-fill.very-low { background: linear-gradient(135deg, #c0392b, #e74c3c); }
    .progress-text { font-weight: 700; color: #182B48; min-width: 42px; text-align: left; }
    .day-cell {
        text-align: center;
        font-size: 12px;
        color: #182B48;
        min-width: 70px;
    }
    .day-cell .day-name { display: block; font-weight: 700; color: #182B48; }
    .day-cell .day-num { display: block; color: #6b7a90; font-size: 11px; }
    .day-cell .value {
        display: inline-block;
        margin-top: 4px;
        padding: 2px 8px;
        border-radius: 8px;
        background: #f0f4f8;
        color: #6b7a90;
        font-weight: 700;
        font-size: 12px;
    }
    .day-cell .value.done {
        background: rgba(39, 174, 96, 0.12);
        color: #27ae60;
    }
    .summary-card {
        background: #fff;
        border-radius: 13px;
        padding: 14px 16px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .summary-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
    }
    .summary-label { font-size: 12px; color: #6b7a90; }
    .summary-value { font-size: 22px; font-weight: 800; color: #182B48; }
    .empty-state {
        background: #fff;
        border-radius: 13px;
        padding: 40px 16px;
        text-align: center;
        color: #6b7a90;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
    }
    .empty-state i { font-size: 40px; opacity: 0.3; color: #182B48; }
    .empty-state h5 { color: #182B48; margin-top: 14px; }
    .week-picker {
        background: #fff;
        border-radius: 13px;
        padding: 16px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
@php
    $today = \Carbon\Carbon::today()->toDateString();
    $dayNames = [
        0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء',
        4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت',
    ];
    $progressClass = function ($percent) {
        if ($percent >= 70) return '';
        if ($percent >= 40) return 'low';
        return 'very-low';
    };
@endphp

<div style="background:linear-gradient(135deg,#182B48 0%,#2A4570 100%);border-radius:14px;padding:16px 22px;margin-bottom:16px;color:#fff;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
    <div style="width:48px;height:48px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;">
        <i class="fas fa-chart-column"></i>
    </div>
    <div>
        <h1 style="margin:0;font-size:18px;font-weight:700;">{{ $title }}</h1>
        <p style="margin:0;color:rgba(255,255,255,0.8);font-size:12.5px;">{{ $subtitle }}</p>
    </div>
</div>

<div class="report-tabs">
    <a href="{{ route('supervisor.iman-program.index') }}" class="report-tab {{ request()->routeIs('supervisor.iman-program.index') ? 'active' : '' }}">
        <i class="fas fa-star-and-crescent"></i> البرامج
    </a>
    <a href="{{ route('supervisor.iman-program.reports.students') }}" class="report-tab {{ $type === 'students' ? 'active' : '' }}">
        <i class="fas fa-user-graduate"></i> تقرير الطلاب
    </a>
    <a href="{{ route('supervisor.iman-program.reports.self') }}" class="report-tab {{ $type === 'self' ? 'active' : '' }}">
        <i class="fas fa-user"></i> تقريري
    </a>
</div>

<div class="week-picker">
    <form method="GET" action="{{ route('supervisor.iman-program.reports', ['type' => $type]) }}" class="row g-3 align-items-end">
        @if($type === 'students')
            <div class="col-md-6">
                <label class="form-label">اختر طالباً لعرض تقريره الكامل</label>
                <select name="student_id" class="form-select" onchange="this.form.submit()">
                    <option value="">— جميع الطلاب —</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ ($selectedStudent && $selectedStudent->id === $s->id) ? 'selected' : '' }}>
                            {{ $s->user->name }} {{ $s->student_number ? '(' . $s->student_number . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">الأسبوع</label>
                <input type="date" name="week" class="form-control" value="{{ $weekStart->toDateString() }}">
            </div>
        @else
            <div class="col-md-9">
                <label class="form-label">الأسبوع</label>
                <input type="date" name="week" class="form-control" value="{{ $weekStart->toDateString() }}">
            </div>
        @endif
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> عرض</button>
        </div>
    </form>
</div>

@if(!$selectedStudent)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:16px;">
    <div class="summary-card">
        <div class="summary-icon" style="background:linear-gradient(135deg,#182B48,#2A4570);"><i class="fas fa-bullseye"></i></div>
        <div>
            <div class="summary-label">إجمالي المهام الأسبوعية</div>
            <div class="summary-value">{{ $totals['total'] }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background:linear-gradient(135deg,#27ae60,#2ecc71);"><i class="fas fa-circle-check"></i></div>
        <div>
            <div class="summary-label">المهام المنجزة</div>
            <div class="summary-value">{{ $totals['done'] }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background:linear-gradient(135deg,#d4a030,#e6b84d);"><i class="fas fa-chart-pie"></i></div>
        <div>
            <div class="summary-label">نسبة الإنجاز</div>
            <div class="summary-value">{{ $totals['percent'] }}%</div>
        </div>
    </div>
</div>
@endif

@if($type === 'students' && !$selectedStudent)
    @php
        $chartLabels = [];
        $chartDone = [];
        $chartTotal = [];
        $chartPct = [];
        foreach ($rows as $row) {
            $reportCollection = collect($row['report']);
            $studentDone = $reportCollection->sum(fn ($r) => $r['done'] ?? 0);
            $studentTotal = $reportCollection->sum(fn ($r) => $r['total'] ?? 0);
            $chartLabels[] = $row['student']->user->name ?? '—';
            $chartDone[] = $studentDone;
            $chartTotal[] = $studentTotal;
            $chartPct[] = $studentTotal > 0 ? round(($studentDone / $studentTotal) * 100) : 0;
        }
    @endphp
    @if(count($chartLabels) > 0)
        <div class="report-card">
            <div class="report-card-header">
                <h3>
                    <i class="fas fa-chart-column" style="color:#d4a030;margin-left:8px;"></i>
                    نسبة إنجاز الطلاب هذا الأسبوع
                </h3>
                <small style="color:#6b7a90;">{{ $weekStart->format('Y/m/d') }} - {{ $weekEnd->format('Y/m/d') }}</small>
            </div>
            <div style="padding:16px;position:relative;height:320px;">
                <canvas id="studentsCompletionChart"></canvas>
            </div>
        </div>
    @endif
@elseif($type === 'self')
    @php
        $chartLabels = [];
        $chartPct = [];
        $chartDone = [];
        $chartTotal = [];
        foreach ($rows as $programRow) {
            $criteriaCollection = collect($programRow['criteria'] ?? []);
            $progDone = $criteriaCollection->sum(fn ($c) => $c['done'] ?? 0);
            $progTotal = $criteriaCollection->sum(fn ($c) => $c['total'] ?? 0);
            $chartLabels[] = $programRow['program']->name ?? '—';
            $chartDone[] = $progDone;
            $chartTotal[] = $progTotal;
            $chartPct[] = $progTotal > 0 ? round(($progDone / $progTotal) * 100) : 0;
        }
    @endphp
    @if(count($chartLabels) > 0)
        <div class="report-card">
            <div class="report-card-header">
                <h3>
                    <i class="fas fa-chart-column" style="color:#d4a030;margin-left:8px;"></i>
                    نسبة إنجاز برامجي هذا الأسبوع
                </h3>
                <small style="color:#6b7a90;">{{ $weekStart->format('Y/m/d') }} - {{ $weekEnd->format('Y/m/d') }}</small>
            </div>
            <div style="padding:16px;position:relative;height:320px;">
                <canvas id="studentsCompletionChart"></canvas>
            </div>
        </div>
    @endif
@endif

@if($type === 'students')
    @if($selectedStudent && $reportData)
        @include('partials.iman-program.student-report-body', [
            'data' => $reportData,
            'formAction' => route('supervisor.iman-program.reports.students', ['student_id' => $selectedStudent->id]),
            'compareAction' => route('supervisor.iman-program.reports.students', ['student_id' => $selectedStudent->id]),
            'criteriaAction' => route('supervisor.iman-program.reports.students', ['student_id' => $selectedStudent->id]),
            'backUrl' => route('supervisor.iman-program.reports.students'),
            'headerTitle' => 'تقرير الطالب: ' . ($selectedStudent->user->name ?? ''),
        ])
    @else
        @forelse($rows as $row)
            <div class="report-card">
                <div class="report-card-header">
                    <div>
                        <h3>
                            <i class="fas fa-user-graduate" style="color:#d4a030;margin-left:8px;"></i>
                            <a href="{{ route('supervisor.iman-program.reports.students', ['student_id' => $row['student']->id]) }}"
                               style="color:#182B48;text-decoration:none;">
                                {{ $studentLabel ? $studentLabel($row['student']) : ($row['student']->user->name ?? '—') }}
                            </a>
                        </h3>
                        <small style="color:#6b7a90;">
                            {{ $row['programs']->count() }} برنامج
                            @php $reportCollection = collect($row['report']); @endphp
                            @if($row['programs']->count() > 0 && $reportCollection->isNotEmpty())
                                • {{ $reportCollection->sum(fn($r) => $r['done']) }} / {{ $reportCollection->sum(fn($r) => $r['total']) }} منجز
                            @endif
                        </small>
                    </div>
                    <div class="progress" style="min-width:200px;">
                        <div class="progress-bar-wrap">
                            @php
                                $progTotal = $reportCollection->sum(fn($r) => $r['total']);
                                $progDone = $reportCollection->sum(fn($r) => $r['done']);
                                $progPct = $progTotal > 0 ? round(($progDone / $progTotal) * 100) : 0;
                            @endphp
                            <div class="progress-bar-fill {{ $progressClass($progPct) }}" style="width: {{ $progPct }}%;"></div>
                        </div>
                        <div class="progress-text">{{ $progPct }}%</div>
                    </div>
                </div>
                <div class="report-card-body">
                    @if($reportCollection->isEmpty())
                        <div style="padding:16px;text-align:center;color:#6b7a90;">
                            <i class="fas fa-info-circle"></i> لا توجد برامج إيمانية لهذا الطالب.
                        </div>
                    @else
                        @foreach($reportCollection as $programRow)
                            <div style="padding:16px;border-bottom:1px solid #eef1f6;">
                                <h4 style="margin:0 0 6px;font-size:15px;color:#182B48;">
                                    <i class="fas fa-seedling" style="color:#27ae60;margin-left:6px;"></i>
                                    {{ $programRow['program']->name }}
                                </h4>
                                <small style="color:#6b7a90;">
                                    {{ $programRow['program']->is_active ? 'نشط' : 'متوقف' }}
                                    @php $criteriaCollection = collect($programRow['criteria']); @endphp
                                    • {{ $criteriaCollection->count() }} معيار
                                </small>
                                <div class="table-responsive" style="margin-top:12px;">
                                    <table class="report-table">
                                        <thead>
                                            <tr>
                                                <th>المعيار</th>
                                                @foreach(($criteriaCollection->first()['values'] ?? []) as $val)
                                                    <th class="day-cell">
                                                        <span class="day-name">{{ $dayNames[$val['date']->dayOfWeek] ?? '' }}</span>
                                                        <span class="day-num">{{ $val['date']->format('m-d') }}</span>
                                                    </th>
                                                @endforeach
                                                <th style="width:160px;">الإنجاز</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($criteriaCollection as $criterion)
                                                <tr>
                                                    <td>
                                                        <div class="criterion-name">{{ $criterion['criterion']->name }}</div>
                                                        <div class="criterion-meta">
                                                            @if($criterion['criterion']->type === 'check')
                                                                <i class="fas fa-check-square"></i> علامة (تم/لم يتم)
                                                            @else
                                                                <i class="fas fa-hashtag"></i> رقم — الهدف: {{ $criterion['criterion']->target_value ?? 0 }}
                                                            @endif
                                                        </div>
                                                    </td>
                                                    @foreach($criterion['values'] as $val)
                                                        @php
                                                            $entry = $val['entry'];
                                                            $isDone = false;
                                                            $display = '—';
                                                            if ($entry) {
                                                                if ($criterion['criterion']->type === 'check') {
                                                                    $isDone = (bool) $entry->is_checked;
                                                                    $display = $isDone ? '✓' : '✗';
                                                        } else {
                                                            $isDone = (int) $entry->value >= (int) ($criterion['criterion']->target_value ?? 0) && (int) $criterion['criterion']->target_value > 0;
                                                            $display = (int) $entry->value;
                                                        }
                                                            }
                                                        @endphp
                                                        <td class="day-cell">
                                                            <span class="value {{ $isDone ? 'done' : '' }}">{{ $display }}</span>
                                                        </td>
                                                    @endforeach
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar-wrap">
                                                                <div class="progress-bar-fill {{ $progressClass($criterion['percent']) }}" style="width: {{ $criterion['percent'] }}%;"></div>
                                                            </div>
                                                            <div class="progress-text">{{ $criterion['percent'] }}%</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <h5>لا يوجد طلاب</h5>
                <p>لا يوجد طلاب مرتبطون بحسابك حالياً.</p>
            </div>
        @endforelse
    @endif
@else
    @forelse($rows as $programRow)
        <div class="report-card">
            <div class="report-card-header">
                <div>
                    <h3>
                        <i class="fas fa-seedling" style="color:#27ae60;margin-left:8px;"></i>
                        {{ $programRow['program']->name }}
                    </h3>
                    <small style="color:#6b7a90;">
                        {{ $programRow['program']->is_active ? 'نشط' : 'متوقف' }}
                        @php $criteriaCollection = collect($programRow['criteria']); @endphp
                        • {{ $criteriaCollection->count() }} معيار
                    </small>
                </div>
                <div class="progress" style="min-width:200px;">
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill {{ $progressClass($programRow['percent']) }}" style="width: {{ $programRow['percent'] }}%;"></div>
                    </div>
                    <div class="progress-text">{{ $programRow['percent'] }}%</div>
                </div>
            </div>
            <div class="report-card-body">
                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>المعيار</th>
                                @foreach(($criteriaCollection->first()['values'] ?? []) as $val)
                                    <th class="day-cell">
                                        <span class="day-name">{{ $dayNames[$val['date']->dayOfWeek] ?? '' }}</span>
                                        <span class="day-num">{{ $val['date']->format('m-d') }}</span>
                                    </th>
                                @endforeach
                                <th style="width:160px;">الإنجاز</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criteriaCollection as $criterion)
                                <tr>
                                    <td>
                                        <div class="criterion-name">{{ $criterion['criterion']->name }}</div>
                                        <div class="criterion-meta">
                                            @if($criterion['criterion']->type === 'check')
                                                <i class="fas fa-check-square"></i> علامة (تم/لم يتم)
                                            @else
                                                <i class="fas fa-hashtag"></i> رقم — الهدف: {{ $criterion['criterion']->target_value ?? 0 }}
                                            @endif
                                        </div>
                                    </td>
                                    @foreach($criterion['values'] as $val)
                                        @php
                                            $entry = $val['entry'];
                                            $isDone = false;
                                            $display = '—';
                                            if ($entry) {
                                                if ($criterion['criterion']->type === 'check') {
                                                    $isDone = (bool) $entry->is_checked;
                                                    $display = $isDone ? '✓' : '✗';
                                                } else {
                                                    $isDone = (int) $entry->value >= (int) ($criterion['criterion']->target_value ?? 0) && (int) $criterion['criterion']->target_value > 0;
                                                    $display = (int) $entry->value;
                                                }
                                            }
                                        @endphp
                                        <td class="day-cell">
                                            <span class="value {{ $isDone ? 'done' : '' }}">{{ $display }}</span>
                                        </td>
                                    @endforeach
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar-wrap">
                                                <div class="progress-bar-fill {{ $progressClass($criterion['percent']) }}" style="width: {{ $criterion['percent'] }}%;"></div>
                                            </div>
                                            <div class="progress-text">{{ $criterion['percent'] }}%</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-star-and-crescent"></i>
            <h5>لا توجد برامج شخصية</h5>
            <p>ابدأ بإنشاء برنامجك الإيماني الشخصي لمتابعة التزامك اليومي.</p>
            <a href="{{ route('supervisor.iman-program.create', ['scope' => 'self']) }}"
               style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;padding:8px 16px;border-radius:9px;font-weight:700;text-decoration:none;">
                <i class="fas fa-plus-circle"></i> أنشئ برنامجك الشخصي
            </a>
        </div>
    @endforelse
@endif
@endsection

@if(count($chartLabels ?? []) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const canvas = document.getElementById('studentsCompletionChart');
    if (!canvas) return;

    const labels = @json($chartLabels);
    const pct = @json($chartPct);
    const done = @json($chartDone);
    const total = @json($chartTotal);

    const colors = pct.map(v => v >= 70 ? 'rgba(39,174,96,0.85)' : (v >= 40 ? 'rgba(243,156,18,0.85)' : 'rgba(192,57,43,0.85)'));
    const borderColors = pct.map(v => v >= 70 ? '#27ae60' : (v >= 40 ? '#f39c12' : '#c0392b'));

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'نسبة الإنجاز %',
                data: pct,
                backgroundColor: colors,
                borderColor: borderColors,
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(24,43,72,0.95)',
                    titleColor: '#d4a030',
                    bodyColor: '#fff',
                    borderColor: '#d4a030',
                    borderWidth: 1,
                    rtl: true,
                    padding: 10,
                    callbacks: {
                        afterLabel: function (ctx) {
                            const i = ctx.dataIndex;
                            return ' منجز: ' + done[i] + ' / ' + total[i];
                        }
                    }
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(24,43,72,0.04)' },
                    ticks: { color: '#6b7a90', font: { size: 11, weight: '600' } },
                },
                y: {
                    beginAtZero: true,
                    max: 100,
                    position: 'right',
                    title: { display: true, text: 'نسبة الإنجاز %', color: '#6b7a90', font: { size: 12 } },
                    grid: { color: 'rgba(24,43,72,0.05)' },
                    ticks: { color: '#6b7a90', callback: v => v + '%' },
                },
            },
        },
    });
})();
</script>
@endpush
@endif
