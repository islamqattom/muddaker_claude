@extends('layouts.app')

@section('title', 'تشخيص التسميع')
@section('page-title', 'تشخيص "لم يسمع"')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('content')
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
                        <div id="bfResult" class="mt-2 small"></div>
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

    if (!from || !to) { result.textContent = 'يرجى تحديد الفترة.'; return; }

    fetch('{{ route('admin.recitation-diagnostics.preview') }}?from=' + from + '&to=' + to, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
        if (!data) { result.textContent = 'تعذّر الحصول على المعاينة.'; return; }
        result.innerHTML = 'سيتم إنشاء <strong>' + data.count + '</strong> جلسة "لم يسمع" لهذه الفترة.';
        confirmBtn.disabled = data.count === 0;
    })
    .catch(() => { result.textContent = 'تعذّر الحصول على المعاينة.'; });
}
</script>
@endpush
