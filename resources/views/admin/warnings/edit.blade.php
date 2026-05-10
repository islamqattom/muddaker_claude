@extends('layouts.app')
@section('title', 'تعديل إنذار')
@section('page-title', 'تعديل إنذار')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.warnings.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">تعديل الإنذار</li>
@endsection

@section('content')
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
}

body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #6c7a89; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.form-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 25px;
}
.form-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.form-header i { color: var(--gold); font-size: 20px; }
.form-body { padding: 25px 28px; }

.form-group { margin-bottom: 20px; }
.form-label {
    font-weight: 600; color: #5a6c7d;
    margin-bottom: 10px; display: block;
    font-size: 14px;
}
.form-label i { color: var(--gold); margin-left: 6px; }
.form-label .required { color: var(--danger); }
.form-control, .form-select {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
}
.form-control:focus, .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212,160,48,0.12);
}
.form-control.is-invalid, .form-select.is-invalid { border-color: var(--danger); }
.invalid-feedback { color: var(--danger); font-size: 13px; margin-top: 5px; }

.section-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(24,43,72,0.1), transparent);
    margin: 25px 0;
}

.fields-section {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border: 1px solid rgba(24,43,72,0.08);
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
}
.fields-section-header {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 15px;
    font-weight: 700; font-size: 15px;
}
.fields-section-header i { color: var(--gold); }

.action-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
    text-decoration: none;
}
.action-btn.submit {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.action-btn.submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); }
.action-btn.cancel {
    background: transparent;
    color: #7f8c8d; border: 2px solid #e9ecef;
}
.action-btn.cancel:hover { border-color: var(--danger); color: var(--danger); }
.action-btn i { color: var(--gold); }

@media (max-width: 992px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="header-text">
            <h4>تعديل الإنذار</h4>
            <p>تعديل بيانات إنذار طالب</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="border-radius: 12px; margin-bottom: 20px;">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="border-radius: 12px; margin-bottom: 20px;">
        <ul style="margin: 0; padding-right: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-8 mx-auto">
        <form method="POST" action="{{ route('admin.warnings.update', $warning->id) }}">
            @csrf
            @method('PUT')

            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>معلومات الإنذار</span>
                </div>
                <div class="form-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user-graduate"></i>الطالب
                                <span class="required">*</span>
                            </label>
                            <select name="student_id" class="form-select select2 @error('student_id') is-invalid @enderror" required>
                                <option value="">اختر الطالب</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id', $warning->student_id) == $student->id ? 'selected' : '' }}>
                                        {{ $student->user->name }} ({{ $student->student_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-exclamation-circle"></i>نوع الإنذار
                                <span class="required">*</span>
                            </label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" id="warningType" required>
                                <option value="">اختر النوع</option>
                                <option value="attendance" {{ old('type', $warning->type) == 'attendance' ? 'selected' : '' }}>إنذار حضور</option>
                                <option value="recitation" {{ old('type', $warning->type) == 'recitation' ? 'selected' : '' }}>إنذار تسميع</option>
                                <option value="general" {{ old('type', $warning->type) == 'general' ? 'selected' : '' }}>إنذار عام</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-heading"></i>عنوان الإنذار
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $warning->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-file-alt"></i>محتوى الإنذار
                            <span class="required">*</span>
                        </label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="4" required>{{ old('content', $warning->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-calendar-day"></i>تاريخ الإصدار
                                <span class="required">*</span>
                            </label>
                            <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', $warning->issue_date?->format('Y-m-d')) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-sticky-note"></i>ملاحظات إضافية</label>
                            <input type="text" name="additional_notes" class="form-control" value="{{ old('additional_notes', $warning->additional_notes) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card" id="attendanceFields" style="display: {{ $warning->type === 'attendance' ? 'block' : 'none' }};">
                <div class="form-header">
                    <i class="fas fa-calendar-times" style="color: var(--warning);"></i>
                    <span>تفاصيل غياب الحضور</span>
                </div>
                <div class="form-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-calendar"></i>من تاريخ</label>
                            <input type="date" name="from_date" class="form-control" value="{{ old('from_date', $warning->from_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-calendar"></i>إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control" value="{{ old('to_date', $warning->to_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-calendar-plus"></i>إجمالي الأيام</label>
                            <input type="number" name="total_days" class="form-control" value="{{ old('total_days', $warning->total_days) }}" min="0">
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-times-circle"></i>أيام الغياب</label>
                            <input type="number" name="absent_days" class="form-control" value="{{ old('absent_days', $warning->absent_days) }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-clock"></i>أيام التأخير</label>
                            <input type="number" name="late_days" class="form-control" value="{{ old('late_days', $warning->late_days) }}" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card" id="recitationFields" style="display: {{ $warning->type === 'recitation' ? 'block' : 'none' }};">
                <div class="form-header">
                    <i class="fas fa-microphone" style="color: var(--info);"></i>
                    <span>تفاصيل فشل التسميع</span>
                </div>
                <div class="form-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-microphone"></i>إجمالي الجلسات</label>
                            <input type="number" name="total_sessions" class="form-control" value="{{ old('total_sessions', $warning->total_sessions) }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-times-circle"></i>جلسات راسبة</label>
                            <input type="number" name="failed_sessions" class="form-control" value="{{ old('failed_sessions', $warning->failed_sessions) }}" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <button type="submit" class="action-btn submit">
                    <i class="fas fa-save"></i>تحديث
                </button>
                <a href="{{ route('admin.warnings.show', $warning->id) }}" class="action-btn cancel">
                    <i class="fas fa-arrow-right"></i>رجوع
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        dir: 'rtl',
        placeholder: 'اختر الطالب'
    });

    $('#warningType').on('change', function() {
        const type = $(this).val();
        $('#attendanceFields').hide();
        $('#recitationFields').hide();

        if (type === 'attendance') {
            $('#attendanceFields').show();
        } else if (type === 'recitation') {
            $('#recitationFields').show();
        }
    });
});
</script>
@endpush