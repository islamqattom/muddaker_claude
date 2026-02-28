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
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تعديل إنذار
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.warnings.update', $warning->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">الطالب <span class="text-danger">*</span></label>
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

                    <div class="mb-3">
                        <label class="form-label">نوع الإنذار <span class="text-danger">*</span></label>
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

                    <div class="mb-3">
                        <label class="form-label">عنوان الإنذار <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $warning->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">محتوى الإنذار <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="4" required>{{ old('content', $warning->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاريخ الإصدار <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', $warning->issue_date?->format('Y-m-d')) }}" required>
                                @error('issue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ملاحظات إضافية</label>
                                <input type="text" name="additional_notes" class="form-control" value="{{ old('additional_notes', $warning->additional_notes) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Attendance fields -->
                    <div id="attendanceFields" style="display: {{ $warning->type === 'attendance' ? 'block' : 'none' }};">
                        <h6 class="border-bottom pb-2 mb-3 text-warning">
                            <i class="fas fa-calendar-times me-1"></i>تفاصيل غياب الحضور
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">من تاريخ</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ old('from_date', $warning->from_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">إلى تاريخ</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ old('to_date', $warning->to_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">إجمالي الأيام</label>
                                    <input type="number" name="total_days" class="form-control" value="{{ old('total_days', $warning->total_days) }}" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">أيام الغياب</label>
                                    <input type="number" name="absent_days" class="form-control" value="{{ old('absent_days', $warning->absent_days) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">أيام التأخير</label>
                                    <input type="number" name="late_days" class="form-control" value="{{ old('late_days', $warning->late_days) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recitation fields -->
                    <div id="recitationFields" style="display: {{ $warning->type === 'recitation' ? 'block' : 'none' }};">
                        <h6 class="border-bottom pb-2 mb-3 text-info">
                            <i class="fas fa-microphone me-1"></i>تفاصيل فشل التسميع
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">إجمالي الجلسات</label>
                                    <input type="number" name="total_sessions" class="form-control" value="{{ old('total_sessions', $warning->total_sessions) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">جلسات راسبة</label>
                                    <input type="number" name="failed_sessions" class="form-control" value="{{ old('failed_sessions', $warning->failed_sessions) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>تحديث
                        </button>
                        <a href="{{ route('admin.warnings.show', $warning->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>رجوع
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
