@extends('layouts.app')
@section('title', 'سجل أجزاء الأبناء')
@section('page-title', 'سجل أجزاء الأبناء')
@section('role-label', 'ولي الأمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('content')

<div class="portal-container">
    <x-page-hero icon="fas fa-book-quran">
        <h1 class="page-banner-title">سجل أجزاء الأبناء</h1>
        <p class="page-banner-sub">تابع تقدم أبنائك في حفظ أجزاء القرآن الكريم</p>
    </x-page-hero>

    <div class="student-selector-card">
        <div class="selector-header">
            <div class="selector-icon">
                <i class="fas fa-search"></i>
            </div>
            <h5>اختيار الابن</h5>
        </div>
        <select id="studentSelect" class="form-select">
            <option value="">-- اختر الابن --</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}"
                    data-name="{{ $student->user->name }}"
                    data-level="{{ $student->level->name ?? '-' }}"
                    data-supervisor="{{ $student->supervisor->name ?? '-' }}">
                    {{ $student->user->name }} ({{ $student->student_number }})
                </option>
            @endforeach
        </select>
    </div>

    <div id="studentInfoCard" class="student-info-card" style="display: none;">
        <div class="student-info-header">
            <div class="student-avatar-lg">
                <i class="fas fa-user"></i>
            </div>
            <div class="student-info-details">
                <h5 id="studentName">-</h5>
                <p>المستوى: <span id="studentLevel">-</span> | المشرف: <span id="studentSupervisor">-</span></p>
            </div>
        </div>
        <div class="student-info-actions">
            <a id="viewRecordLink" href="#" class="btn btn-primary">
                <i class="fas fa-list"></i>
                <span>عرض السجل</span>
            </a>
        </div>
    </div>
</div>

<style>
.student-selector-card {
    background: linear-gradient(135deg, #182B48 0%, #1a3356 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
}

.selector-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.selector-icon {
    width: 48px;
    height: 48px;
    background: rgba(212,160,48,0.2);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.selector-header h5 {
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.student-selector-card .form-select {
    max-width: 450px;
    padding: 10px 14px;
    border-radius: 9px;
    font-size: 13px;
    border: 2px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.student-selector-card .form-select option {
    color: #182B48;
    background: #fff;
}

.student-info-card {
    background: #fff;
    border-radius: 13px;
    padding: 16px;
    box-shadow: 0 2px 10px rgba(24,43,72,0.04);
    border: 1px solid #f0f4f8;
}

.student-info-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 14px;
}

.student-avatar-lg {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.student-info-details h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 3px 0;
}

.student-info-details p {
    color: #95a5a6;
    font-size: 12.5px;
    margin: 0;
}

.student-info-details span {
    color: #182B48;
    font-weight: 600;
}

.student-info-actions .btn {
    padding: 8px 16px;
    border-radius: 9px;
    font-size: 13px;
}
</style>

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
@endsection