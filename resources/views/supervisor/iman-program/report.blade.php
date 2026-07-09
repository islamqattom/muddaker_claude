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
        border-radius: 16px;
        padding: 14px 22px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        margin-bottom: 20px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .report-tab {
        padding: 10px 20px;
        border-radius: 10px;
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
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .report-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid #eef1f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .report-card-header h3 { margin: 0; font-size: 18px; color: #182B48; }
    .report-card-body { padding: 0; }
    .report-table { width: 100%; border-collapse: collapse; }
    .report-table th, .report-table td {
        padding: 12px 16px;
        text-align: right;
        border-bottom: 1px solid #eef1f6;
        font-size: 14px;
    }
    .report-table thead th {
        background: rgba(24, 43, 72, 0.04);
        color: #182B48;
        font-weight: 700;
        font-size: 13px;
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
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 22px;
    }
    .summary-label { font-size: 12px; color: #6b7a90; }
    .summary-value { font-size: 22px; font-weight: 800; color: #182B48; }
    .empty-state {
        background: #fff;
        border-radius: 16px;
        padding: 60px 20px;
        text-align: center;
        color: #6b7a90;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
    }
    .empty-state i { font-size: 50px; opacity: 0.3; color: #182B48; }
    .empty-state h5 { color: #182B48; margin-top: 14px; }
    .week-picker {
        background: #fff;
        border-radius: 16px;
        padding: 18px 22px;
        box-shadow: 0 2px 10px rgba(24, 43, 72, 0.05);
        margin-bottom: 20px;
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

<div style="background:linear-gradient(135deg,#182B48 0%,#2A4570 100%);border-radius:16px;padding:24px 30px;margin-bottom:24px;color:#fff;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
    <div style="width:60px;height:60px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;">
        <i class="fas fa-chart-column"></i>
    </div>
    <div>
        <h1 style="margin:0;font-size:22px;font-weight:700;">{{ $title }}</h1>
        <p style="margin:0;color:rgba(255,255,255,0.8);font-size:13px;">{{ $subtitle }}</p>
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
        <div class="col-md-9">
            <label class="form-label">الأسبوع</label>
            <input type="date" name="week" class="form-control" value="{{ $weekStart->toDateString() }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> عرض</button>
        </div>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;">
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

@if($type === 'students')
    @forelse($rows as $row)
        <div class="report-card">
            <div class="report-card-header">
                <div>
                    <h3>
                        <i class="fas fa-user-graduate" style="color:#d4a030;margin-left:8px;"></i>
                        {{ $studentLabel ? $studentLabel($row['student']) : ($row['student']->user->name ?? '—') }}
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
                    <div style="padding:24px 22px;text-align:center;color:#6b7a90;">
                        <i class="fas fa-info-circle"></i> لا توجد برامج إيمانية لهذا الطالب.
                    </div>
                @else
                    @foreach($reportCollection as $programRow)
                        <div style="padding:18px 22px;border-bottom:1px solid #eef1f6;">
                            <h4 style="margin:0 0 6px;font-size:16px;color:#182B48;">
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
               style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;padding:12px 24px;border-radius:12px;font-weight:700;text-decoration:none;">
                <i class="fas fa-plus-circle"></i> أنشئ برنامجك الشخصي
            </a>
        </div>
    @endforelse
@endif
@endsection
