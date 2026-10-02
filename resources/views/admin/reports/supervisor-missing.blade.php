@extends('layouts.app')
@section('title', 'نواقص المشرفين — التقارير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<x-page-hero icon="fas fa-user-clock">
    <h1 class="page-banner-title">نواقص المشرفين</h1>
</x-page-hero>

@include('admin.reports._styles')

<div class="rec-session">

    {{-- Filter card --}}
    <form method="GET" action="{{ route('admin.reports.supervisor-missing') }}" class="rpt-filter">
        <div class="rpt-filter-header">
            <i class="fas fa-sliders-h"></i>
            <span>تصفية النتائج</span>
        </div>
        <div class="rpt-filter-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الفترة</label>
                    <select name="period" class="form-select">
                        @php
                            $periods = [
                                'today'   => 'اليوم',
                                'week'    => 'الأسبوع',
                                'month'   => 'الشهر',
                                'quarter' => 'الربع',
                                'half'    => 'النصف',
                                'year'    => 'السنة',
                                'all'     => 'الكل',
                                'custom'  => 'مخصص',
                            ];
                        @endphp
                        @foreach ($periods as $value => $label)
                            <option value="{{ $value }}" {{ ($period ?? 'month') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ optional($fromDate)->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ optional($toDate)->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المشرف</label>
                    <select name="supervisor_id" class="form-select">
                        <option value="">كل المشرفين</option>
                        @foreach ($supervisors as $sup)
                            <option value="{{ $sup->id }}" {{ (string)$supervisorId === (string)$sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <button type="submit" class="rpt-btn rpt-btn-primary">
                        <i class="fas fa-check"></i> تطبيق
                    </button>
                    <a href="{{ route('admin.reports.supervisor-missing') }}" class="rpt-btn rpt-btn-ghost">
                        <i class="fas fa-undo"></i> إعادة
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- KPI strip --}}
    <div class="rpt-kpi-grid">
        <div class="rpt-kpi">
            <div class="rpt-kpi-icon" style="background:rgba(192,57,43,0.12);color:var(--danger);"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="rpt-kpi-label">إجمالي النواقص</div>
                <div class="rpt-kpi-value">{{ $stats['rows_count'] }}</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-icon" style="background:rgba(243,156,18,0.12);color:var(--warning);"><i class="fas fa-book-quran"></i></div>
            <div>
                <div class="rpt-kpi-label">جلسات ناقصة</div>
                <div class="rpt-kpi-value">{{ $stats['missing_sessions'] }}</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-icon" style="background:rgba(5,150,105,0.12);color:#059669;"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="rpt-kpi-label">حضور ناقص</div>
                <div class="rpt-kpi-value">{{ $stats['missing_attendance'] }}</div>
            </div>
        </div>
    </div>

    {{-- Settings banner --}}
    <div class="info-alert mb-4 alert-legend">
        <i class="fas fa-cog"></i>
        <div class="alert-content">
            <span class="alert-legend-title"><strong>الإعدادات المستخدمة:</strong></span>
            <span class="alert-legend-rows">
                <span class="alert-legend-row">
                    <span class="alert-legend-key">أيام الجلسات المتوقعة</span>
                    <span class="badge bg-primary">{{ implode('، ', $configuredSessionDays) }}</span>
                </span>
                <span class="alert-legend-row">
                    <span class="alert-legend-key">أيام العمل المتوقعة</span>
                    <span class="badge bg-success">{{ implode('، ', $configuredAttendanceDays) }}</span>
                </span>
            </span>
        </div>
    </div>

    {{-- Results table --}}
    <div class="rpt-table-card">
        <div class="rpt-table-header">
            <h5><i class="fas fa-list"></i> النتائج ({{ $stats['rows_count'] }})</h5>
            <div class="d-flex gap-2 flex-wrap">
                @php
                    $exportParams = array_merge(request()->query(), ['export' => 'excel']);
                @endphp
                <a href="?{{ http_build_query($exportParams) }}" class="rpt-btn rpt-btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                @php $exportParams['export'] = 'print'; @endphp
                <a href="?{{ http_build_query($exportParams) }}" target="_blank" class="rpt-btn rpt-btn-ghost">
                    <i class="fas fa-print"></i> طباعة
                </a>
                @php $exportParams['export'] = 'pdf'; @endphp
                <a href="?{{ http_build_query($exportParams) }}" class="rpt-btn rpt-btn-danger">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort="num">#</th>
                        <th data-sort>التاريخ</th>
                        <th data-sort>اليوم</th>
                        <th data-sort>المشرف</th>
                        <th data-sort>الجلسة</th>
                        <th data-sort>الحضور</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $r)
                        <tr>
                            <td data-sort-value="{{ $i + 1 }}"><span class="rpt-num">{{ $i + 1 }}</span></td>
                            <td data-sort-value="{{ $r['date']->format('Y-m-d') }}" style="font-weight:600;">
                                {{ $r['date']->format('Y/m/d') }}
                            </td>
                            <td data-sort-value="{{ $r['date']->dayOfWeek }}">{{ $r['day_name'] }}</td>
                            <td>{{ $r['supervisor_name'] }}</td>
                            <td>
                                @if (! $r['session_expected'])
                                    <span class="rpt-badge" style="background:#eef0f3;color:#64748b;">—</span>
                                @elseif ($r['session_missing'])
                                    <span class="rpt-badge failed">✗ ناقصة</span>
                                @else
                                    <span class="rpt-badge passed">✓ مسجلة</span>
                                @endif
                            </td>
                            <td>
                                @if (! $r['attendance_expected'])
                                    <span class="rpt-badge" style="background:#eef0f3;color:#64748b;">—</span>
                                @elseif ($r['attendance_missing'])
                                    <span class="rpt-badge failed">✗ ناقص</span>
                                @else
                                    <span class="rpt-badge passed">✓ مسجل</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">لا توجد نواقص في هذه الفترة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection