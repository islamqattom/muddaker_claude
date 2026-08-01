@extends('layouts.app')

@section('title', 'تعديل درس')
@section('page-title', 'تعديل درس')

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
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.index') }}">المواد</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
    <li class="breadcrumb-item active">تعديل درس</li>
@endsection

@push('styles')
<style>
.form-page { font-family: 'Tajawal', sans-serif; }
.page-header { display: flex; gap: 20px; align-items: center; margin-bottom: 30px; flex-wrap: wrap; }
.subject-color-badge { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 26px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 13px; margin: 0; }
.form-card { background: #fff; border-radius: 18px; box-shadow: 0 4px 25px rgba(24, 43, 72, 0.08); padding: 30px; border: 1px solid rgba(24,43,72,0.05); }
.form-label { color: #182B48; font-weight: 700; margin-bottom: 8px; display: block; }
.form-label .required { color: #c0392b; }
.form-control, .form-select { border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-family: 'Tajawal', sans-serif; transition: all 0.3s ease; width: 100%; }
.form-control:focus, .form-select:focus { border-color: {{ $subject->color }}; box-shadow: 0 0 0 4px {{ $subject->color }}20; outline: none; }
textarea.form-control { min-height: 100px; resize: vertical; }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; margin-top: 24px; border-top: 1px dashed #e9ecef; }
.btn { border-radius: 12px; padding: 12px 28px; font-weight: 700; font-family: 'Tajawal', sans-serif; border: none; }
.btn-primary { background: {{ $subject->color }}; color: #fff; }
.btn-primary:hover { color: #fff; }
.btn-secondary { background: #f0f4f8; color: #182B48; }
.day-preview { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 20px; background: {{ $subject->color }}15; color: {{ $subject->color }}; font-weight: 700; font-size: 13px; margin-top: 6px; }
</style>
@endpush

@section('content')
<div class="form-page">
    <div class="page-header">
        <div class="subject-color-badge" style="background: {{ $subject->color }};"><i class="fas fa-edit"></i></div>
        <div class="header-text">
            <h4>تعديل درس</h4>
            <p>المادة: <strong>{{ $subject->name }}</strong></p>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route($role . '.lessons.update', [$subject->id, $lesson->id]) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">عنوان الدرس <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">محتوى الدرس</label>
                    <textarea name="content" class="form-control">{{ old('content', $lesson->content) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">تاريخ الدرس</label>
                    <input type="date" name="lesson_date" class="form-control" value="{{ old('lesson_date', $lesson->lesson_date ? $lesson->lesson_date->format('Y-m-d') : '') }}">
                    <div id="dayPreview" class="day-preview"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="pending" {{ old('status', $lesson->status) === 'pending' ? 'selected' : '' }}>لم يتم</option>
                        <option value="completed" {{ old('status', $lesson->status) === 'completed' ? 'selected' : '' }}>تم</option>
                        <option value="cancelled" {{ old('status', $lesson->status) === 'cancelled' ? 'selected' : '' }}>ملغى</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control">{{ old('notes', $lesson->notes) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route($role . '.subjects.show', $subject->id) }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ms-1"></i> حفظ التعديلات</button>
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
</script>
@endpush
