@extends('layouts.app')

@section('title', 'تعديل مادة')
@section('page-title', 'تعديل مادة دراسية')

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
    <li class="breadcrumb-item active">تعديل: {{ $subject->name }}</li>
@endsection

@push('styles')
<style>
.form-page { font-family: 'Tajawal', sans-serif; }
.form-card { background: #fff; border-radius: 13px; box-shadow: 0 4px 25px rgba(24, 43, 72, 0.08); padding: 16px; border: 1px solid rgba(24,43,72,0.05); }
.form-label { color: #182B48; font-weight: 700; margin-bottom: 8px; display: block; }
.form-label .required { color: #c0392b; }
.form-control, .form-select { border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-family: 'Tajawal', sans-serif; transition: all 0.3s ease; width: 100%; }
.form-control:focus, .form-select:focus { border-color: #d4a030; box-shadow: 0 0 0 4px rgba(212,160,48,0.12); outline: none; }
textarea.form-control { min-height: 100px; resize: vertical; }
.color-picker { display: flex; gap: 10px; flex-wrap: wrap; }
.color-swatch { width: 40px; height: 40px; border-radius: 10px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s ease; }
.color-swatch:hover { transform: scale(1.1); }
.color-swatch.active { border-color: #182B48; box-shadow: 0 0 0 3px rgba(212,160,48,0.2); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; margin-top: 24px; border-top: 1px dashed #e9ecef; }
.btn { border-radius: 12px; padding: 12px 16px; font-weight: 700; font-family: 'Tajawal', sans-serif; border: none; }
.btn-primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); color: #fff; }
.btn-primary:hover { color: #fff; }
.btn-secondary { background: #f0f4f8; color: #182B48; }
.btn-secondary:hover { background: #e9ecef; color: #182B48; }
</style>
@endpush

@section('content')
<div class="form-page">
    <x-page-hero icon="fas fa-edit">
        <h1 class="page-banner-title">تعديل: {{ $subject->name }}</h1>
        <p class="page-banner-sub">قم بتحديث بيانات المادة الدراسية</p>
    </x-page-hero>

    <div class="form-card">
        <form method="POST" action="{{ route($role . '.subjects.update', $subject->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">اسم المادة <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">وصف المادة</label>
                    <textarea name="description" class="form-control">{{ old('description', $subject->description) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">لون المادة</label>
                    <input type="hidden" name="color" id="colorInput" value="{{ old('color', $subject->color) }}">
                    <div class="color-picker">
                        @foreach(\App\Models\Subject::PALETTE as $c)
                            <div class="color-swatch {{ old('color', $subject->color) === $c ? 'active' : '' }}" data-color="{{ $c }}" style="background:{{ $c }}"></div>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch" style="padding-right:2.5em">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $subject->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active" style="color:#182B48;font-weight:600">المادة نشطة</label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route($role . '.subjects.index') }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ms-1"></i> حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.color-swatch').forEach(sw => {
    sw.addEventListener('click', function() {
        document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('colorInput').value = this.dataset.color;
    });
});
</script>
@endpush
