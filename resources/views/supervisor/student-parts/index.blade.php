@extends('layouts.app')

@section('title', 'سجل أجزاء الطالب')
@section('page-title', 'أجزاء الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
    .student-selector-card {
        background: var(--primary);
        color: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .student-info-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="student-selector-card">
        <h4><i class="fas fa-search"></i> البحث عن طالب</h4>
        <select id="studentSelect" class="form-select" style="max-width: 400px;">
            <option value="">-- اختر الطالب --</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}"
                    data-name="{{ $student->user->name }}"
                    data-level="{{ $student->level->name ?? '-' }}"
                    data-supervisor="-"
                    data-number="{{ $student->student_number }}">
                    {{ $student->user->name }} ({{ $student->student_number }})
                </option>
            @endforeach
        </select>
    </div>

    <div id="studentInfoCard" class="student-info-card">
        <div class="row">
            <div class="col-md-3">
                <strong>الاسم:</strong> <span id="studentName">-</span>
            </div>
            <div class="col-md-3">
                <strong>المستوى:</strong> <span id="studentLevel">-</span>
            </div>
            <div class="col-md-3">
                <strong>المشرف:</strong> <span id="studentSupervisor">-</span>
            </div>
            <div class="col-md-3">
                <strong>رقم الطالب:</strong> <span id="studentNumber">-</span>
            </div>
        </div>
        <div class="mt-3">
            <a id="viewRecordLink" href="#" class="btn btn-primary">
                <i class="fas fa-list"></i> عرض السجل
            </a>
        </div>
    </div>

    <div id="partsContainer" style="display: none;">
        <p class="text-muted">اختر طالباً لعرض سجل أجزاء الطالب</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#studentSelect').on('change', function() {
        var option = $(this).find(':selected');
        if (!option.val()) {
            $('#studentInfoCard').hide();
            $('#partsContainer').hide();
            return;
        }

        $('#studentName').text(option.data('name'));
        $('#studentLevel').text(option.data('level'));
        $('#studentSupervisor').text(option.data('supervisor'));
        $('#studentNumber').text(option.data('number'));
        $('#viewRecordLink').attr('href', '/supervisor/student-parts/' + option.val());
        $('#studentInfoCard').show();
    });
});
</script>
@endpush
