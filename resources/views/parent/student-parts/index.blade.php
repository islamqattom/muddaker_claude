@extends('layouts.app')

@section('title', 'سجل أجزاء الأبناء')
@section('page-title', 'سجل أجزاء الأبناء')
@section('role-label', 'ولي الأمر')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book-quran"></i> سجل أجزاء الأبناء</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="student-selector-card">
        <h4><i class="fas fa-search"></i> اختيار ابن</h4>
        <select id="studentSelect" class="form-select" style="max-width: 400px;">
            <option value="">-- اختر الابن --</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}"
                    data-name="{{ $student->user->name }}"
                    data-level="{{ $student->level->name ?? '-' }}"
                    data-supervisor="{{ $student->supervisor->user->name ?? '-' }}">
                    {{ $student->user->name }} ({{ $student->student_number }})
                </option>
            @endforeach
        </select>
    </div>

    <div id="studentInfoCard" class="student-info-card">
        <div class="row">
            <div class="col-md-4">
                <strong>الاسم:</strong> <span id="studentName">-</span>
            </div>
            <div class="col-md-4">
                <strong>المستوى:</strong> <span id="studentLevel">-</span>
            </div>
            <div class="col-md-4">
                <strong>المشرف:</strong> <span id="studentSupervisor">-</span>
            </div>
        </div>
        <div class="mt-3">
            <a id="viewRecordLink" href="#" class="btn btn-primary">
                <i class="fas fa-list"></i> عرض السجل
            </a>
        </div>
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
            return;
        }

        $('#studentName').text(option.data('name'));
        $('#studentLevel').text(option.data('level'));
        $('#studentSupervisor').text(option.data('supervisor'));
        $('#viewRecordLink').attr('href', '/parent/student-parts/' + option.val());
        $('#studentInfoCard').show();
    });
});
</script>
@endpush