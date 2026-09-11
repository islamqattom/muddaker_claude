@extends('guest.layout', ['step' => 1])

@section('title', 'يوم الهمة - البيانات الأساسية')

@section('content')
<div class="page-card">
    <h2><i class="fas fa-user-edit me-2" style="color: var(--gold);"></i>البيانات الأساسية</h2>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('guest.yawm-al-himma.parts') }}">
        @csrf

        <div class="mb-3">
            <label class="label-text"><i class="fas fa-user-tie me-1"></i>اسم المشرف</label>
            <input type="text" name="supervisor_name" class="form-control" required
                   placeholder="أدخل اسم المشرف" value="{{ old('supervisor_name') }}">
        </div>

        <div class="mb-3">
            <label class="label-text"><i class="fas fa-user-graduate me-1"></i>اختر الطالب</label>
            <select name="student_id" id="studentSelect" class="form-select" required>
                <option value="">-- اختر الطالب من القائمة --</option>
                @foreach($students as $s)
                    <option value="{{ $s['id'] }}" {{ old('student_id') == $s['id'] ? 'selected' : '' }}>{{ $s['name'] }}</option>
                @endforeach
                <option value="manual" {{ old('student_id') === 'manual' ? 'selected' : '' }}>أو إدخال اسم يدوياً...</option>
            </select>
        </div>

        <div class="mb-4" id="manualNameWrap" style="display: none;">
            <label class="label-text"><i class="fas fa-pen me-1"></i>اسم الطالب (إدخال يدوي)</label>
            <input type="text" name="student_name_manual" id="studentNameManual" class="form-control"
                   placeholder="اكتب اسم الطالب" value="{{ old('student_name_manual') }}">
        </div>

        <div class="d-flex justify-content-between gap-2 flex-wrap">
            <button type="submit" class="btn-gold">
                <i class="fas fa-arrow-left me-1"></i> التالي: اختيار الأجزاء
            </button>
        </div>
    </form>
</div>

<div class="alert alert-gold d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div><i class="fas fa-info-circle me-1"></i> هذه الصفحة مخصصة ليوم الهمة - يمكن للمشرف أو ولي الأمر تسجيل تقييم الطالب دون الحاجة لتسجيل دخول.</div>
    <a href="{{ route('guest.yawm-al-himma.sessions.index') }}" style="color: var(--navy); font-weight: 700; text-decoration: underline;">
        <i class="fas fa-edit me-1"></i> تعديل جلسات سابقة
    </a>
</div>
@endsection

@push('scripts')
<script>
    const sel = document.getElementById('studentSelect');
    const wrap = document.getElementById('manualNameWrap');
    const manual = document.getElementById('studentNameManual');

    function toggleManual() {
        if (sel.value === 'manual') {
            wrap.style.display = 'block';
            manual.required = true;
        } else {
            wrap.style.display = 'none';
            manual.required = false;
        }
    }
    sel.addEventListener('change', toggleManual);
    toggleManual();
</script>
@endpush
