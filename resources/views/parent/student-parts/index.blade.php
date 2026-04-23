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
    .student-selector-card h4 {
        font-weight: 600;
        margin-bottom: 16px;
    }
    .student-info-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        display: none;
    }
    .student-info-card.active {
        display: block;
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
                <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->student_number }})</option>
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
        var studentId = $(this).val();
        if (!studentId) {
            $('#studentInfoCard').removeClass('active');
            return;
        }

        $.get('/parent/student/' + studentId, function(student) {
            $('#studentName').text(student.user.name);
            $('#studentLevel').text(student.level ? student.level.name : '-');
            $('#studentSupervisor').text(student.supervisor ? student.supervisor.user.name : '-');
            $('#viewRecordLink').attr('href', '/parent/student-parts/' + studentId);
            $('#studentInfoCard').addClass('active');
        });
    });
});
</script>
@endpush