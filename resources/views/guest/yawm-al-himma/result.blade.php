@extends('guest.layout')

@section('title', 'يوم الهمة - نتيجة التقييم')

@section('content')
<div class="page-card">
    <div class="text-center mb-4">
        <i class="fas fa-check-circle" style="font-size: 60px; color: var(--success);"></i>
        <h2 class="mt-3">تم حفظ التقييم بنجاح</h2>
    </div>

    @if(session('supervisor_updated'))
        <div class="alert alert-success text-center" style="background: #e8f5ec; border: 1px solid #27ae60; color: #1e7e3a; border-radius: var(--radius-sm); padding: 10px 14px;">
            <i class="fas fa-check me-1"></i> تم تحديث اسم المشرف
        </div>
    @endif

    <div class="alert alert-gold">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <i class="fas fa-user-tie me-1"></i> المشرف:
                <strong id="supervisorDisplay">{{ $session->supervisor_name }}</strong>
            </div>
            <button type="button" class="btn btn-sm btn-outline-navy" id="editSupervisorBtn" style="padding: 4px 12px; font-size: 13px;">
                <i class="fas fa-pen me-1"></i> تعديل اسم المشرف
            </button>
        </div>
        <div id="supervisorEditForm" style="display: none; margin-top: 12px;">
            <form method="POST" action="{{ route('guest.yawm-al-himma.supervisor.update', ['session' => $session->id]) }}" class="d-flex gap-2 align-items-center flex-wrap">
                @csrf
                @method('PATCH')
                <input type="text" name="supervisor_name" value="{{ old('supervisor_name', $session->supervisor_name) }}" class="form-control" style="flex: 1; min-width: 220px;" maxlength="255" required>
                <button type="submit" class="btn-gold" style="padding: 8px 18px; font-size: 14px;">
                    <i class="fas fa-save me-1"></i> حفظ
                </button>
                <button type="button" class="btn btn-outline-navy" id="cancelSupervisorBtn" style="padding: 8px 14px; font-size: 14px;">
                    إلغاء
                </button>
            </form>
            @error('supervisor_name')
                <div class="text-danger mt-2" style="font-size: 13px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="mt-2"><i class="fas fa-user-graduate me-1"></i> الطالب: <strong>{{ $session->student_name }}</strong></div>
        <div><i class="fas fa-calendar me-1"></i> التاريخ: <strong>{{ $session->session_date->format('Y-m-d') }}</strong></div>
    </div>

    <h3 style="color: var(--navy); font-size: 16px; font-weight: 700; margin-top: 18px;">
        <i class="fas fa-book-quran me-1"></i> الأجزاء ({{ count($session->parts) }})
    </h3>
    <div class="mb-3">
        @foreach($session->parts as $p)
            <span class="badge" style="background: var(--gold-soft); color: var(--navy); padding: 6px 12px; border-radius: 8px; margin: 2px;">الجزء {{ $p }}</span>
        @endforeach
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="lbl">إجمالي الأخطاء</div>
            <div class="val">{{ $session->total_errors }}</div>
        </div>
        <div class="summary-item">
            <div class="lbl">إجمالي الترددات</div>
            <div class="val">{{ $session->total_hesitations }}</div>
        </div>
    </div>

    @if($session->notes)
    <div class="mt-3">
        <strong>ملاحظات:</strong>
        <p class="text-muted">{{ $session->notes }}</p>
    </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('guest.yawm-al-himma.index') }}" class="btn-gold">
            <i class="fas fa-plus me-1"></i> تسجيل تقييم جديد
        </a>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('editSupervisorBtn');
    var form = document.getElementById('supervisorEditForm');
    var cancel = document.getElementById('cancelSupervisorBtn');
    if (!btn || !form) return;
    btn.addEventListener('click', function () {
        form.style.display = 'block';
        btn.style.display = 'none';
        var input = form.querySelector('input[name="supervisor_name"]');
        if (input) { input.focus(); input.select(); }
    });
    if (cancel) {
        cancel.addEventListener('click', function () {
            form.style.display = 'none';
            btn.style.display = '';
        });
    }
})();
</script>
@endpush
@endsection
