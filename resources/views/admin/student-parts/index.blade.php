@extends('layouts.app')
@section('title', 'سجل أجزاء الطلاب')
@section('page-title', 'سجل أجزاء الطلاب')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">سجل أجزاء الطلاب</li>
@endsection

@push('styles')
<style>
/* Student Selector Card */
.student-selector-section {
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    border-radius: 13px;
    padding: 16px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}
.student-selector-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -20%;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(212,160,48,0.2) 0%, transparent 70%);
    border-radius: 50%;
}
.student-selector-section::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: 0;
    left: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}
.section-header-quran {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 16px;
    position: relative;
    z-index: 1;
}
.section-icon-large {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(212,160,48,0.4);
}
.section-title-text h4 {
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 4px 0;
}
.section-title-text p {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    margin: 0;
}
.search-wrapper {
    position: relative;
    z-index: 1;
}
.select2-wrapper {
    position: relative;
}
.select2-wrapper::before {
    content: '';
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23d4a030' viewBox='0 0 24 24'%3E%3Cpath d='M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'/%3E%3C/svg%3E") no-repeat center;
    pointer-events: none;
}
.student-select-custom {
    width: 100%;
    max-width: 500px;
    padding: 16px 16px 16px 50px;
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.95);
    font-size: 15px;
    font-family: 'Tajawal', sans-serif;
    color: #182B48;
    cursor: pointer;
    transition: all 0.3s ease;
}
.student-select-custom:focus {
    outline: none;
    border-color: #d4a030;
    box-shadow: 0 0 0 4px rgba(212,160,48,0.2);
}
/* Student Info Card */
.student-info-display {
    background: #fff;
    border-radius: 13px;
    padding: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    display: none;
}
.student-info-display.show {
    display: block;
    animation: slideUp 0.4s ease;
}
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.student-profile-header {
    display: flex;
    align-items: center;
    gap: 25px;
    padding-bottom: 25px;
    border-bottom: 2px solid #f0f4f8;
    margin-bottom: 16px;
}
.student-avatar-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 32px;
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.student-info-main h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 8px 0;
}
.student-info-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.info-tag {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fa;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    color: #7f8c8d;
}
.info-tag i {
    color: #d4a030;
}
.info-tag strong {
    color: #182B48;
}
.view-record-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-top: 20px;
}
.view-record-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(24,43,72,0.3);
    color: #fff;
}
.view-record-btn i {
    color: #d4a030;
}
/* Empty Selection State */
.empty-selection {
    text-align: center;
    padding: 40px 16px;
}
.empty-icon-large {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(212,160,48,0.1), rgba(180,128,44,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 42px;
    color: #d4a030;
}
.empty-selection h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}
.empty-selection p {
    color: #7f8c8d;
    font-size: 14px;
}
/* Responsive */
@media (max-width: 768px) {
.student-selector-section { padding: 16px; }
.section-header-quran { flex-direction: column; align-items: flex-start; gap: 15px; }
.student-profile-header { flex-direction: column; text-align: center; }
.student-info-meta { justify-content: center; }
}
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-icon quran">
            <i class="fas fa-book-quran"></i>
        </div>
        <div class="header-text">
            <h4>سجل أجزاء الطلاب</h4>
            <p>البحث عن طالب لعرض سجل أجزاء التسميع</p>
        </div>
    </div>
</div>

{{-- Student Selector Section --}}
<div class="student-selector-section">
    <div class="section-header-quran">
        <div class="section-icon-large">
            <i class="fas fa-search"></i>
        </div>
        <div class="section-title-text">
            <h4>البحث عن طالب</h4>
            <p>اختر الطالب من القائمة لعرض سجل أجزاء التسميع الخاص به</p>
        </div>
    </div>

    <div class="search-wrapper">
        <div class="select2-wrapper">
            <select id="studentSelect" class="student-select-custom">
                <option value="">-- اختر الطالب --</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}"
                        data-name="{{ $student->user->name }}"
                        data-level="{{ $student->level->name ?? '-' }}"
                        data-supervisor="{{ $student->supervisor->name ?? '-' }}"
                        data-number="{{ $student->student_number }}">
                        {{ $student->user->name }} ({{ $student->student_number }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Student Info Display --}}
<div id="studentInfoCard" class="student-info-display">
    <div class="student-profile-header">
        <div class="student-avatar-large" id="avatarInitial">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="student-info-main">
            <h5 id="displayName">-</h5>
            <div class="student-info-meta">
                <div class="info-tag">
                    <i class="fas fa-layer-group"></i>
                    <span>المستوى:</span>
                    <strong id="displayLevel">-</strong>
                </div>
                <div class="info-tag">
                    <i class="fas fa-user-tie"></i>
                    <span>المشرف:</span>
                    <strong id="displaySupervisor">-</strong>
                </div>
                <div class="info-tag">
                    <i class="fas fa-id-badge"></i>
                    <span>رقم الطالب:</span>
                    <strong id="displayNumber">-</strong>
                </div>
            </div>
        </div>
    </div>

    <a id="viewRecordLink" href="#" class="view-record-btn">
        <i class="fas fa-book-open"></i>
        <span>عرض سجل الأجزاء</span>
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

{{-- Empty State (shown by default) --}}
<div id="emptyState" class="table-card">
    <div class="empty-selection">
        <div class="empty-icon-large">
            <i class="fas fa-book-quran"></i>
        </div>
        <h5>اختر طالباً</h5>
        <p>قم باختيار طالب من القائمة أعلاه لعرض سجل أجزاء التسميع الخاص به</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    $('#studentSelect').on('change', function() {
        var option = $(this).find(':selected');

        if (!option.val()) {
            $('#studentInfoCard').removeClass('show');
            $('#emptyState').show();
            return;
        }

        var name = option.data('name');
        var level = option.data('level');
        var supervisor = option.data('supervisor');
        var number = option.data('number');

        $('#displayName').text(name);
        $('#displayLevel').text(level);
        $('#displaySupervisor').text(supervisor);
        $('#displayNumber').text(number);
        $('#avatarInitial').html('<i class="fas fa-user-graduate"></i>');
        $('#viewRecordLink').attr('href', '/admin/student-parts/' + option.val());

        $('#emptyState').hide();
        $('#studentInfoCard').addClass('show');
    });
});
</script>
@endpush