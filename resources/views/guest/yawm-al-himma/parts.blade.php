@extends('guest.layout', ['step' => 2])

@section('title', 'يوم الهمة - اختيار الأجزاء')

@section('content')
<div class="page-card">
    <h2><i class="fas fa-book-quran me-2" style="color: var(--gold);"></i>اختر أجزاء السرد</h2>

    <div class="alert alert-gold mb-3">
        <i class="fas fa-user-tie me-1"></i> المشرف: <strong>{{ $supervisorName }}</strong>
        <span class="mx-2">|</span>
        <i class="fas fa-user-graduate me-1"></i> الطالب: <strong>{{ $studentName }}</strong>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('guest.yawm-al-himma.evaluate') }}">
        @csrf
        <input type="hidden" name="supervisor_name" value="{{ $supervisorName }}">
        <input type="hidden" name="student_name" value="{{ $studentName }}">
        @if($student)<input type="hidden" name="student_id" value="{{ $student->id }}">@endif

        <div class="alert alert-info" style="background:#eaf4fb;border-color:#3498db;color:#1a4a7a;border-radius:var(--radius-sm);">
            <i class="fas fa-info-circle me-1"></i>
            اختر الأجزاء التي سيسردها الطالب، ثم اضغط "التالي" لتسجيل الأخطاء والترددات لكل صفحة.
        </div>

        <div class="row g-2 mb-3">
            @foreach($parts as $part)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <input type="checkbox" name="selected_parts[]" value="{{ $part->part_number }}"
                       id="part-{{ $part->part_number }}" class="part-checkbox">
                <label for="part-{{ $part->part_number }}" class="part-card">
                    <div class="num">{{ $part->part_number }}</div>
                    <div class="lbl">{{ $part->name }}</div>
                    <div class="lbl">ص {{ $part->start_page }} - {{ $part->end_page }}</div>
                </label>
            </div>
            @endforeach
        </div>

        <div class="alert alert-warning" id="selectionWarning" style="display:none; border-radius:var(--radius-sm);">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <span id="selectionWarningText"></span>
        </div>

        <div class="d-flex justify-content-between gap-2 flex-wrap">
            <a href="{{ route('guest.yawm-al-himma.index') }}" class="btn-outline-navy">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
            <button type="submit" id="submitBtn" class="btn-gold" disabled>
                <i class="fas fa-arrow-left me-1"></i> التالي: تقييم الصفحات
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const checkboxes = document.querySelectorAll('.part-checkbox');
    const submitBtn = document.getElementById('submitBtn');
    const warning = document.getElementById('selectionWarning');
    const warningText = document.getElementById('selectionWarningText');

    function updateSelection() {
        const checked = document.querySelectorAll('.part-checkbox:checked');
        submitBtn.disabled = checked.length === 0;
        if (checked.length === 0) {
            warning.style.display = 'block';
            warningText.textContent = 'الرجاء اختيار جزء واحد على الأقل';
        } else {
            warning.style.display = 'none';
        }
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateSelection));
    updateSelection();
</script>
@endpush
