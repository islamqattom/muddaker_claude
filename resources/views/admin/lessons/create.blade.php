@extends('layouts.app')

@section('title', 'إضافة درس')
@section('page-title', 'إضافة درس جديد')

@php
    $role = auth()->user()->role;
    $roleLabel = $role === 'admin' ? 'مدير' : 'معلم';
@endphp

@section('role-label', $roleLabel)

@section('sidebar-menu')
    @include($role . '.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.index') }}">المواد الدراسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
    <li class="breadcrumb-item active">إضافة درس</li>
@endsection

@push('styles')
<style>
.form-page { font-family: 'Tajawal', sans-serif; }
.subject-color-badge { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.form-card { background: #fff; border-radius: 13px; box-shadow: 0 4px 25px rgba(24, 43, 72, 0.08); padding: 16px; border: 1px solid rgba(24,43,72,0.05); }
.form-label { color: #182B48; font-weight: 700; margin-bottom: 8px; display: block; }
.form-label .required { color: #c0392b; }
.form-control, .form-select { border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-family: 'Tajawal', sans-serif; transition: all 0.3s ease; width: 100%; }
.form-control:focus, .form-select:focus { border-color: {{ $subject->color }}; box-shadow: 0 0 0 4px {{ $subject->color }}20; outline: none; }
textarea.form-control { min-height: 100px; resize: vertical; }
.form-text { color: #7f8c8d; font-size: 12px; margin-top: 4px; }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; margin-top: 24px; border-top: 1px dashed #e9ecef; }
.btn { border-radius: 12px; padding: 12px 16px; font-weight: 700; font-family: 'Tajawal', sans-serif; border: none; }
.btn-primary { background: {{ $subject->color }}; color: #fff; }
.btn-primary:hover { color: #fff; }
.btn-secondary { background: #f0f4f8; color: #182B48; }
.repeat-box {
    background: rgba(212,160,48,0.06);
    border: 1px dashed #d4a030;
    padding: 16px;
    border-radius: 12px;
    margin-top: 16px;
}
.day-preview {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border-radius: 13px;
    background: {{ $subject->color }}15;
    color: {{ $subject->color }};
    font-weight: 700; font-size: 14px;
    margin-top: 6px;
}
</style>
@endpush

@section('content')
<div class="form-page">
    <x-page-hero icon="fas fa-plus-circle">
        <h1 class="page-banner-title">إضافة درس جديد</h1>
        <p class="page-banner-sub">المادة: <strong>{{ $subject->name }}</strong></p>
    </x-page-hero>

    <div class="form-card">
        <form method="POST" action="{{ route($role . '.lessons.store', $subject->id) }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">عنوان الدرس <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="مثال: سورة البقرة - الآيات 1-20" required>
                    @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">محتوى الدرس</label>
                    <textarea name="content" class="form-control" placeholder="ما الذي يشمله هذا الدرس؟ (آيات، صفحات، موضوع...)">{{ old('content') }}</textarea>
                    <small class="form-text">مثال: حفظ الآيات من 1 إلى 20 مع فهم المعنى الإجمالي</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">تاريخ الدرس</label>
                    <input type="date" name="lesson_date" class="form-control" value="{{ old('lesson_date') }}">
                    @error('lesson_date') <small class="text-danger">{{ $message }}</small> @enderror
                    <div id="dayPreview" class="day-preview"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>لم يتم (قيد الانتظار)</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>تم (منجز)</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>ملغى</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" placeholder="أي ملاحظات إضافية على الدرس">{{ old('notes') }}</textarea>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch" style="padding-right:2.5em">
                        <input class="form-check-input" type="checkbox" name="repeat_enabled" id="repeat_enabled" value="1" {{ old('repeat_enabled') ? 'checked' : '' }}>
                        <label class="form-check-label" for="repeat_enabled" style="color:#182B48;font-weight:600">تكرار أسبوعي (نفس اليوم كل أسبوع)</label>
                    </div>
                </div>

                <div class="col-md-12" id="repeatBox" style="display:{{ old('repeat_enabled') ? 'block' : 'none' }}">
                    <div class="repeat-box">
                        <label class="form-label">عدد الأسابيع (الحد الأقصى 52)</label>
                        <input type="number" name="repeat_weeks" class="form-control" min="1" max="52" value="{{ old('repeat_weeks', 4) }}" style="max-width:200px">
                        <small class="form-text">سيتم إنشاء درس منفصل لكل أسبوع بنفس العنوان والمحتوى</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route($role . '.subjects.show', $subject->id) }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ms-1"></i> حفظ الدرس</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const dayInput = document.querySelector('input[name="lesson_date"]');
const dayPreview = document.getElementById('dayPreview');
const dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

function updateDay() {
    if (!dayInput.value) { dayPreview.textContent = ''; return; }
    const d = new Date(dayInput.value);
    if (isNaN(d)) { dayPreview.textContent = ''; return; }
    dayPreview.innerHTML = '<i class="fas fa-calendar-day"></i> ' + dayNames[d.getDay()];
}
dayInput.addEventListener('change', updateDay);
updateDay();

document.getElementById('repeat_enabled').addEventListener('change', function() {
    document.getElementById('repeatBox').style.display = this.checked ? 'block' : 'none';
});
</script>
@endpush
