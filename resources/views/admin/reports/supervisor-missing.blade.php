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
                        <tr class="rpt-row-detail"
                            style="cursor:pointer;"
                            title="عرض التفاصيل"
                            data-supervisor-id="{{ $r['supervisor_id'] }}"
                            data-date="{{ $r['date']->format('Y-m-d') }}">
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

{{-- Row detail modal --}}
<div class="modal fade" id="missingDetailModal" tabindex="-1" aria-labelledby="missingDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%)">
                <h5 class="modal-title" id="missingDetailModalLabel" style="color:#fff">
                    <i class="fas fa-user-clock" style="color:var(--gold)"></i>
                    تفاصيل النواقص
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div id="missingDetailSummary" class="mb-3"></div>
                <div id="missingDetailLoading" class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin"></i> جارٍ التحميل...
                </div>
                <div class="table-responsive" id="missingDetailTableWrap" style="display:none">
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الطالب</th>
                                <th>الجلسة</th>
                                <th>الحضور</th>
                            </tr>
                        </thead>
                        <tbody id="missingDetailBody"></tbody>
                    </table>
                </div>
                <div id="missingDetailEmpty" class="text-center text-muted py-4" style="display:none">
                    لا يوجد طلاب مرتبطون بهذا المشرف
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('missingDetailModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;

    const modal       = new bootstrap.Modal(modalEl);
    const detailUrl   = @json(route('admin.reports.supervisor-missing.details'));
    const summaryEl   = document.getElementById('missingDetailSummary');
    const loadingEl   = document.getElementById('missingDetailLoading');
    const tableWrapEl = document.getElementById('missingDetailTableWrap');
    const bodyEl      = document.getElementById('missingDetailBody');
    const emptyEl     = document.getElementById('missingDetailEmpty');

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function statusBadge(expected, recorded, label, raw, missingLabel) {
        if (!expected) {
            return '<span class="rpt-badge" style="background:#eef0f3;color:#64748b;">—</span>';
        }
        if (recorded) {
            return '<span class="rpt-badge ' + esc(raw || 'passed') + '">' + esc(label || 'مسجل') + '</span>';
        }
        return '<span class="rpt-badge failed">✗ ' + esc(missingLabel) + '</span>';
    }

    function renderSummary(data) {
        const sessionBadge = !data.session_expected
            ? '<span class="rpt-badge" style="background:#eef0f3;color:#64748b;">—</span>'
            : (data.session_missing
                ? '<span class="rpt-badge failed">✗ ناقصة</span>'
                : '<span class="rpt-badge passed">✓ مسجلة</span>');
        const attendanceBadge = !data.attendance_expected
            ? '<span class="rpt-badge" style="background:#eef0f3;color:#64748b;">—</span>'
            : (data.attendance_missing
                ? '<span class="rpt-badge failed">✗ ناقص</span>'
                : '<span class="rpt-badge passed">✓ مسجل</span>');

        summaryEl.innerHTML =
            '<div class="d-flex flex-wrap gap-3 align-items-center mb-2">' +
                '<span><i class="fas fa-user-tie" style="color:var(--gold)"></i> ' + esc(data.supervisor_name) + '</span>' +
                '<span><i class="fas fa-calendar-day" style="color:var(--gold)"></i> ' + esc(data.date) + ' — ' + esc(data.day_name) + '</span>' +
                '<span><i class="fas fa-users" style="color:var(--gold)"></i> ' + esc(data.students_count) + ' طالب</span>' +
            '</div>' +
            '<div class="d-flex flex-wrap gap-3">' +
                '<span>الجلسة: ' + sessionBadge + '</span>' +
                '<span>الحضور: ' + attendanceBadge + '</span>' +
                (data.is_holiday ? '<span class="rpt-badge holiday">عطلة</span>' : '') +
            '</div>';
    }

    function renderRows(data) {
        bodyEl.innerHTML = '';

        if (!data.students || !data.students.length) {
            tableWrapEl.style.display = 'none';
            emptyEl.style.display = '';
            return;
        }

        data.students.forEach(function (s, i) {
            const sessionCell = statusBadge(
                data.session_expected,
                s.session_recorded,
                (s.session_type_label ? (s.session_type_label + (s.session_status_label ? ' · ' + s.session_status_label : '')) : s.session_status_label),
                s.session_status,
                'ناقصة'
            );
            const attendanceCell = statusBadge(
                data.attendance_expected,
                s.attendance_recorded,
                s.attendance_status_label,
                s.attendance_status,
                'ناقص'
            );

            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td><span class="rpt-num">' + (i + 1) + '</span></td>' +
                '<td>' + esc(s.name) + (s.student_number ? ' <small class="text-muted">#' + esc(s.student_number) + '</small>' : '') + '</td>' +
                '<td>' + sessionCell + '</td>' +
                '<td>' + attendanceCell + '</td>';
            bodyEl.appendChild(tr);
        });

        emptyEl.style.display = 'none';
        tableWrapEl.style.display = '';
    }

    document.querySelectorAll('tr.rpt-row-detail').forEach(function (row) {
        row.addEventListener('click', function () {
            const supervisorId = row.dataset.supervisorId;
            const date         = row.dataset.date;

            summaryEl.innerHTML = '';
            bodyEl.innerHTML    = '';
            tableWrapEl.style.display = 'none';
            emptyEl.style.display     = 'none';
            loadingEl.style.display   = '';
            loadingEl.innerHTML       = '<i class="fas fa-spinner fa-spin"></i> جارٍ التحميل...';
            modal.show();

            const url = detailUrl
                + '?supervisor_id=' + encodeURIComponent(supervisorId)
                + '&date=' + encodeURIComponent(date);

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (res) {
                    if (!res.ok) { throw new Error('failed'); }
                    return res.json();
                })
                .then(function (data) {
                    loadingEl.style.display = 'none';
                    renderSummary(data);
                    renderRows(data);
                })
                .catch(function () {
                    loadingEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> تعذر تحميل التفاصيل</span>';
                });
        });
    });
});
</script>
@endpush