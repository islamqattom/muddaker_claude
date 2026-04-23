@extends('layouts.app')

@section('title', 'سجل أجزاء الطلاب —لوحة التحكم')
@section('page-title', 'سجل أجزاء الطلاب')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
    .parts-table th { background: #f1f3f5; font-weight: 600; }
    .parts-table .completed { background-color: #d4edda !important; }
    .parts-table .not-completed { background-color: #f8d7da !important; }
    .override-badge {
        background: #fff3cd;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .status-mafsil { color: #0d6efd; font-weight: bold; }
    .status-mutqin { color: #28a745; font-weight: bold; }
    .status-not-completed { color: #dc3545; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book-quran"></i> سجل أجزاء الطلاب</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="student-selector-card" style="background: var(--primary); color: white; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h4><i class="fas fa-search"></i> البحث عن طالب</h4>
        <select id="studentSelect" class="form-select" style="max-width: 400px;">
            <option value="">-- اختر الطالب --</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->student_number }})</option>
            @endforeach
        </select>
    </div>

    <div id="studentInfoCard" class="student-info-card" style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: none;">
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
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#studentSelect').on('change', function() {
        var studentId = $(this).val();
        if (!studentId) {
            $('#studentInfoCard').hide();
            return;
        }

        $.get('/admin/students/' + studentId + '/info', function(student) {
            $('#studentName').text(student.name);
            $('#studentLevel').text(student.level_name);
            $('#studentSupervisor').text(student.supervisor_name || '-');
            $('#studentNumber').text(student.student_number);
            $('#viewRecordLink').attr('href', '/admin/student-parts/' + studentId);
            $('#studentInfoCard').show();
        });
    });
});
</script>
@endpush