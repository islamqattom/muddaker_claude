@extends('layouts.app')

@section('title', 'تشخيص التسميع')
@section('page-title', 'تشخيص "لم يسمع"')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('content')

<x-page-hero icon="fas fa-stethoscope">
    <h1 class="page-banner-title">تشخيص "لم يسمع"</h1>
</x-page-hero>
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>سبب إنشاء "لم يسمع" لطالب</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">الطالب</label>
                            <select name="student_id" class="form-select">
                                <option value="">-- اختر طالباً --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ (string) $studentId === (string) $student->id ? 'selected' : '' }}>
                                        {{ $student->user->name ?? ('#'.$student->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">التاريخ</label>
                            <input type="date" name="date" class="form-control" value="{{ $date->toDateString() }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">فحص</button>
                        </div>
                    </form>

                    @if($diagnosis)
                        <table class="table table-sm">
                            <tbody>
                                <tr><th>الطالب</th><td>{{ $diagnosis['student']->user->name ?? '' }}</td></tr>
                                <tr><th>حالة الحضور</th><td>{{ $diagnosis['attendance_status'] ?? 'لا يوجد سجل حضور' }}</td></tr>
                                <tr><th>حاضر/متأخر</th><td>{{ $diagnosis['is_attended'] ? 'نعم' : 'لا' }}</td></tr>
                                <tr><th>يوم تسميع مطلوب</th><td>{{ $diagnosis['required_day'] ? 'نعم' : 'لا' }}</td></tr>
                                <tr><th>انتهى اليوم</th><td>{{ $diagnosis['day_ended'] ? 'نعم' : 'لا' }}</td></tr>
                                <tr><th>توجد جلسة لهذا اليوم</th><td>{{ $diagnosis['has_session'] ? 'نعم' : 'لا' }}</td></tr>
                                <tr class="table-active"><th>النتيجة</th>
                                    <td>
                                        @if($diagnosis['will_create'])
                                            <span class="badge bg-success">سيتم إنشاء "لم يسمع" الآن</span>
                                        @else
                                            <span class="badge bg-secondary">لن يتم الإنشاء</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        @if($diagnosis['sessions']->isNotEmpty())
                            <h6 class="mt-3">الجلسات المسجلة لهذا اليوم:</h6>
                            <ul class="list-group">
                                @foreach($diagnosis['sessions'] as $s)
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>{{ $s->type_label }} — {{ $s->status_label }}</span>
                                        @if($s->auto_generated)<span class="badge bg-info">تلقائية</span>@endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>إنشاء جلسات لفترة أقدم</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        الفحص التلقائي يغطي آخر {{ $autoLookbackDays }} أيام فقط. لإنشاء جلسات لفترة أقدم، اعرض المعاينة أولاً ثم أكّد.
                        ستُظهر المعاينة جدولاً بالأيام التي ستُنشأ فيها الجلسات مع عدد الحاضرين والطلاب المتأثرين.
                    </p>
                    <form method="POST" action="{{ route('admin.recitation-diagnostics.backfill') }}" id="backfillForm">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">من</label>
                                <input type="date" name="from" id="bfFrom" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">إلى</label>
                                <input type="date" name="to" id="bfTo" class="form-control" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="previewBackfill()">معاينة</button>
                                <button type="submit" class="btn btn-danger" id="bfConfirm" disabled
                                        onclick="return confirm('إنشاء جلسات "لم يسمع" لهذه الفترة؟ يُنصح بأخذ نسخة احتياطية أولاً.');">
                                    إنشاء
                                </button>
                            </div>
                        </div>
                        <div id="bfResult" class="mt-3"></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-heart-pulse me-2"></i>الحالة</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">وقت آخر فحص: <strong>{{ $lastScan ?? 'لم يُسجّل بعد' }}</strong></p>
                    <p class="mb-2 small text-muted">
                        الأيام المطلوبة حالياً:
                        @foreach($requiredDays as $d)
                            <span class="badge bg-secondary">{{ [0=>'الأحد',1=>'الإثنين',2=>'الثلاثاء',3=>'الأربعاء',4=>'الخميس',5=>'الجمعة',6=>'السبت'][$d] ?? $d }}</span>
                        @endforeach
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tools me-2"></i>أدوات السيرفر (بدون SSH)</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.recitation-diagnostics.clear-config') }}" class="mb-3"
                          onsubmit="return confirm('مسح الـ config cache؟');">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-broom me-1"></i>مسح الـ config cache
                        </button>
                    </form>

                    @if(count($pendingMigrations) > 0)
                        <div class="alert alert-warning small">
                            <strong>معلّقة ({{ count($pendingMigrations) }}):</strong>
                            <ul class="mb-1">
                                @foreach($pendingMigrations as $m)
                                    <li>{{ $m }}</li>
                                @endforeach
                            </ul>
                            خذ نسخة احتياطية من قاعدة البيانات قبل المتابعة.
                        </div>
                        <form method="POST" action="{{ route('admin.recitation-diagnostics.run-migrations') }}"
                              onsubmit="return confirm('تشغيل الـ migrations المعلّقة؟ تأكد من أخذ نسخة احتياطية.');">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-database me-1"></i>تشغيل الـ migrations
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-database me-1"></i>لا توجد migrations معلّقة
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewBackfill() {
    const from = document.getElementById('bfFrom').value;
    const to = document.getElementById('bfTo').value;
    const result = document.getElementById('bfResult');
    const confirmBtn = document.getElementById('bfConfirm');

    if (!from || !to) {
        result.innerHTML = '<span class="text-danger">يرجى تحديد الفترة.</span>';
        confirmBtn.disabled = true;
        return;
    }

    result.innerHTML = '<div style="display:flex;align-items:center;gap:10px;padding:14px;color:var(--primary);"><i class="fas fa-spinner fa-spin" style="color:#d4a030;font-size:18px;"></i><span>جارٍ حساب المعاينة...</span></div>';
    confirmBtn.disabled = true;

    fetch('{{ route('admin.recitation-diagnostics.preview') }}?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
        if (!data) { result.innerHTML = '<span class="text-danger">تعذّر الحصول على المعاينة.</span>'; return; }

        if (!data.days || data.days.length === 0) {
            result.innerHTML = '<div class="table-card mb-0"><div class="empty-state">' +
                '<div class="empty-icon" style="background:linear-gradient(135deg,#182B48,#2A4570);color:#d4a030;">' +
                '<i class="fas fa-calendar-xmark"></i></div>' +
                '<h5 style="color:var(--primary);">لا توجد جلسات لإنشائها</h5>' +
                '<p>لا توجد جلسات "لم يسمع" تطابق الفترة المختارة.</p>' +
                '</div></div>';
            confirmBtn.disabled = true;
            return;
        }

        let html = '<div class="table-card mb-0">';
        html += '<div class="table-header" style="background:linear-gradient(135deg,#fbfcfe 0%,#fcf6e8 100%);">' +
            '<div class="table-title">' +
                '<div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#182B48,#2A4570);display:flex;align-items:center;justify-content:center;color:#d4a030;flex-shrink:0;">' +
                '<i class="fas fa-calendar-days"></i></div>' +
                '<span style="color:var(--primary);font-weight:700;">معاينة جلسات "لم يسمع"</span>' +
            '</div>' +
            '<div class="table-actions">' +
                '<span class="record-count" style="background:linear-gradient(135deg,#d4a030,#B4802C);color:#fff;padding:6px 14px;border-radius:9px;">' +
                '<i class="fas fa-microphone"></i> سيتم إنشاء ' + data.count + ' جلسة</span>' +
            '</div></div>';
        html += '<div class="card-body-custom p-0"><div class="table-responsive"><table class="modern-table">';
        html += '<thead><tr>' +
            '<th style="color:var(--primary);"><i class="fas fa-calendar-day me-1" style="color:#d4a030;"></i>اليوم</th>' +
            '<th class="text-center" style="color:var(--primary);"><i class="fas fa-user-check me-1" style="color:#3498db;"></i>حاضر</th>' +
            '<th class="text-center" style="color:var(--primary);"><i class="fas fa-book me-1" style="color:#27ae60;"></i>له جلسة</th>' +
            '<th class="text-center" style="color:var(--primary);"><i class="fas fa-plus-circle me-1" style="color:#d4a030;"></i>سيُنشأ</th>' +
            '<th style="color:var(--primary);"><i class="fas fa-users me-1" style="color:#8e44ad;"></i>الطلاب</th>' +
            '</tr></thead><tbody>';

        data.days.forEach(function (day) {
            const students = (day.students || []).map(function (s) {
                const dotColor = s.status === 'late' ? '#f39c12' : '#27ae60';
                return '<span class="type-badge me-1 mb-1" style="background:#fff;border:1px solid #e9ecef;color:var(--primary);">' +
                    '<span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:' + dotColor + ';margin-inline-end:6px;vertical-align:middle;"></span>' +
                    escapeHtml(s.name) +
                    '</span>';
            }).join('');

            html += '<tr>' +
                '<td><div class="d-flex flex-column gap-1">' +
                    '<span class="day-badge" style="background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;align-self:flex-start;">' + escapeHtml(day.day_name) + '</span>' +
                    '<span class="date-badge" style="background:#fcf6e8;color:#B4802C;align-self:flex-start;">' + escapeHtml(day.date) + '</span>' +
                '</div></td>' +
                '<td class="text-center">' +
                    '<span style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:28px;padding:0 10px;border-radius:8px;background:rgba(52,152,219,.12);color:#3498db;font-weight:700;">' + day.present_count + '</span>' +
                '</td>' +
                '<td class="text-center">' +
                    '<span style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:28px;padding:0 10px;border-radius:8px;background:rgba(39,174,96,.12);color:#27ae60;font-weight:700;">' + day.already_has_session_count + '</span>' +
                '</td>' +
                '<td class="text-center">' +
                    '<span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:30px;padding:0 12px;border-radius:9px;background:linear-gradient(135deg,#d4a030,#B4802C);color:#fff;font-weight:800;box-shadow:0 2px 6px rgba(212,160,48,.35);">' + day.will_create_count + '</span>' +
                '</td>' +
                '<td>' + (students || '<span class="no-data">-</span>') + '</td>' +
            '</tr>';
        });

        html += '</tbody></table></div></div>';
        html += '<div class="table-footer" style="background:linear-gradient(135deg,#fcf6e8,#fbfcfe);">' +
            '<div style="display:flex;align-items:center;gap:10px;padding:6px 14px;background:#fff;border-radius:10px;border:1px solid #fcf6e8;">' +
                '<i class="fas fa-calculator" style="color:#d4a030;font-size:16px;"></i>' +
                '<span style="color:var(--primary);font-weight:700;">الإجمالي</span>' +
                '<span style="background:linear-gradient(135deg,#182B48,#2A4570);color:#d4a030;padding:2px 14px;border-radius:7px;font-weight:800;">' + data.count + '</span>' +
                '<span style="color:var(--primary);">جلسة</span>' +
            '</div></div>';
        html += '</div>';

        result.innerHTML = html;
        confirmBtn.disabled = data.count === 0;
    })
    .catch(function () { result.innerHTML = '<span class="text-danger">تعذّر الحصول على المعاينة.</span>'; });
}

function escapeHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>
@endpush
