@extends('layouts.app')

@section('title', 'اختبار المواضع')
@section('page-title', 'اختبار المواضع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
    .exam-card {
        background: linear-gradient(145deg, #ffffff 0%, #f1f5f9 100%);
        border: 1px solid rgba(24, 43, 72, 0.08);
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .exam-card .card-header {
        background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
        color: white;
        border: none;
        padding: 16px 20px;
    }

    .exam-card .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
    }

    .verse-display {
        background: linear-gradient(145deg, #1a2a4a 0%, #2A4570 50%, #1e3a5f 100%);
        border-radius: 12px;
        padding: 40px;
        color: white;
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-family: 'Amiri', 'Scheherazade', serif;
        font-size: 30px;
        line-height: 2.4;
        direction: rtl;
        position: relative;
        overflow: hidden;
    }

    .verse-display::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 50%);
        animation: shimmer 8s ease-in-out infinite;
    }

    @keyframes shimmer {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(10%, 10%); }
    }

    .verse-display .verse-text {
        text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
        position: relative;
        z-index: 1;
    }

    .verse-display .placeholder {
        opacity: 0.7;
        font-size: 18px;
        font-family: 'Tajawal', sans-serif;
        position: relative;
        z-index: 1;
    }

    .verse-display .placeholder i {
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.7; }
        50% { transform: scale(1.1); opacity: 1; }
    }

    .scope-selector {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
    }

    .juz-grid {
        max-height: 180px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        background: white;
    }

    .juz-grid::-webkit-scrollbar {
        width: 6px;
    }

    .juz-grid::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .juz-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .juz-item {
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 8px;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        background: #f8fafc;
        font-size: 13px;
        font-weight: 500;
    }

    .juz-item:hover {
        border-color: #B4802C;
        background: #fef7ed;
    }

    .juz-item.active {
        border-color: #182B48;
        background: linear-gradient(135deg, #182B48, #2A4570);
        color: white;
    }

    .juz-item input {
        display: none;
    }

    .btn-select-all {
        background: linear-gradient(135deg, #6366f1, #818cf8);
        border: none;
        color: white;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-select-all:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-generate {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border: none;
        padding: 14px 40px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-generate::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-generate:hover::before {
        left: 100%;
    }

    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
    }

    .btn-next {
        background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
        border: none;
        padding: 12px 28px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.2s;
    }

    .btn-next:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(24, 43, 72, 0.3);
    }

    .btn-next:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .question-item {
        transition: all 0.2s ease;
        border-radius: 0;
        border-right: 3px solid transparent;
        padding: 14px 16px;
    }

    .question-item:hover {
        background: linear-gradient(90deg, #fef7ed 0%, #f1f5f9 100%);
        border-right-color: #B4802C;
        transform: translateX(-4px);
    }

    .question-item.active-review {
        background: #fef9c3;
        border-right-color: #f59e0b;
    }

    .question-item .order-badge {
        font-size: 11px;
        padding: 5px 10px;
        border-radius: 8px;
        font-weight: 600;
    }

    .exam-stats {
        display: flex;
        gap: 16px;
        align-items: center;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(255,255,255,0.1);
        border-radius: 20px;
        font-size: 13px;
    }

    .stat-item i {
        font-size: 14px;
    }

    .exam-timer {
        font-family: 'Roboto Mono', monospace;
        font-size: 14px;
        padding: 6px 14px;
        background: rgba(255,255,255,0.15);
        border-radius: 8px;
    }

    .keyboard-hint {
        font-size: 11px;
        color: #94a3b8;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 8px;
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: none;
        color: white;
    }

    .btn-warning:hover {
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }

    .highlighted-verse {
        background: linear-gradient(135deg, #fef08a, #fde047);
        color: #1e293b;
        border-radius: 4px;
        padding: 2px 8px;
        font-weight: bold;
    }

    .quran-page {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 0 0 12px 12px;
        color: #1e293b;
    }

    #pageDisplay .quran-page,
    #pageDisplay .quran-page * {
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #questionsList .card-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: white;
    }

    .btn-loading {
        position: relative;
        pointer-events: none;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        top: 50%;
        left: 50%;
        margin: -9px 0 0 -9px;
        border: 2px solid rgba(255,255,255,0.4);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .zero-errors {
        color: #059669;
        font-weight: 700;
    }

    .total-errors-badge {
        background: linear-gradient(135deg, #ef4444, #f87171);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .exam-complete-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }

    .exam-complete-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    .exam-complete-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        max-width: 400px;
        transform: scale(0.8);
        transition: transform 0.3s;
    }

    .exam-complete-overlay.show .exam-complete-card {
        transform: scale(1);
    }

    .success-checkmark {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #059669, #10b981);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .success-checkmark i {
        font-size: 40px;
        color: white;
    }

    @media (max-width: 768px) {
        .verse-display {
            padding: 25px;
            font-size: 22px;
        }

        .juz-item {
            padding: 6px 10px;
            font-size: 12px;
        }

        .exam-stats {
            flex-wrap: wrap;
            gap: 8px;
        }
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
@endpush

@section('content')
{{-- Student Selection (shown when no exam is active) --}}
<div id="student-selection" class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card exam-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>اختر الطالب</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-bold mb-3">الطالب</label>
                    <select id="studentSelect" class="form-select select2" style="font-size: 16px; padding: 12px 16px;">
                        <option value="">-- ابحث عن طالب --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->user->name ?? '' }} - {{ $student->level->name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center">
                    <button type="button" id="startExamBtn" class="btn btn-generate text-white">
                        <i class="fas fa-play me-2"></i>بدء الاختبار
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Exam Interface (hidden until exam starts) --}}
<div id="exam-interface" class="d-none">
    {{-- Exam Header --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <h4 class="mb-0"><i class="fas fa-user-graduate me-2"></i><span id="examStudentName"></span></h4>
                <span class="badge bg-primary" id="examStatusBadge">قيد التقدم</span>
                <div class="exam-stats ms-auto">
                    <div class="stat-item">
                        <i class="fas fa-list"></i>
                        <span id="questionsCount">0</span> سؤال
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <span id="totalErrors">0</span> خطأ
                    </div>
                </div>
            </div>
            <small class="text-muted mt-1 d-block" id="examDate"></small>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <button type="button" id="finishExamBtn" class="btn btn-success me-2">
                <i class="fas fa-check-circle me-1"></i>إنهاء الاختبار
            </button>
            <button type="button" id="cancelExamBtn" class="btn btn-outline-danger">
                <i class="fas fa-times-circle me-1"></i>إلغاء
            </button>
        </div>
    </div>

    {{-- Main Two-Column Layout --}}
    <div class="row">
        {{-- Left: Scope Selector + Page Display --}}
        <div class="col-lg-7">
            {{-- Scope Selector --}}
            <div class="card exam-card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>إعدادات الاختبار</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label mb-0 fw-bold">اختر الأجزاء (<span id="selectedJuzCount">0</span> محدد)</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn-select-all" id="selectAllJuz">تحديد الكل</button>
                                <button type="button" class="btn-select-all" id="clearAllJuz">إلغاء</button>
                            </div>
                        </div>
                        <div class="juz-grid">
                            <div class="row g-2">
                                @for($i = 1; $i <= 30; $i++)
                                    <div class="col-4 col-md-3">
                                        <label class="juz-item d-block" for="juz{{ $i }}">
                                            <input class="juz-checkbox" type="checkbox" value="{{ $i }}" id="juz{{ $i }}"> الجزء {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Page Display --}}
            <div class="card exam-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book-quran me-2"></i>صفحة القرآن</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-next text-white" id="nextQuestionBtn">
                            <i class="fas fa-arrow-right me-1"></i>السؤال التالي<span class="keyboard-hint">N</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="verse-display" id="pageDisplay">
                        <div class="placeholder">
                            <i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>
                            اضغط على "السؤال التالي" لعرض آية عشوائية
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Result Form + Questions Queue --}}
        <div class="col-lg-5">
            {{-- Result Entry Form --}}
            <div class="card exam-card mb-3" id="resultFormCard">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>تسجيل النتيجة</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">عدد الأخطاء</label>
                        <input type="number" id="errorsCount" class="form-control" min="0" value="0" placeholder="0" style="font-size: 16px; padding: 10px 14px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <textarea id="notesInput" class="form-control" rows="3" placeholder="ملاحظات المشرف..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" id="addResultBtn" class="btn btn-generate text-white flex-grow-1">
                            <i class="fas fa-plus me-2"></i>إضافة النتيجة
                        </button>
                        <button type="button" id="backToExamBtn" class="btn btn-outline-secondary d-none">
                            <i class="fas fa-arrow-left me-1"></i>العودة
                        </button>
                    </div>
                </div>
            </div>

            {{-- Questions Queue --}}
            <div class="card exam-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>الأسئلة</h5>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                    <div id="questionsList" class="list-group list-group-flush">
                        <div class="text-center text-muted py-5" id="noQuestionsMsg">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                            <p class="mb-0">لا توجد أسئلة بعد</p>
                            <small>اضغط على "السؤال التالي" لبدء الاختبار</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Exam Complete Overlay --}}
<div class="exam-complete-overlay" id="examCompleteOverlay">
    <div class="exam-complete-card">
        <div class="success-checkmark">
            <i class="fas fa-check"></i>
        </div>
        <h4 class="mb-3">تم إنهاء الاختبار!</h4>
        <p class="text-muted mb-4">تم حفظ جميع الإجابات بنجاح</p>
        <p class="mb-4">
            <strong id="finalQuestionsCount">0</strong> سؤال |
            <strong id="finalErrorsCount" class="text-danger">0</strong> خطأ
        </p>
        <button type="button" class="btn btn-generate text-white" onclick="location.reload()">
            <i class="fas fa-redo me-2"></i>بدء اختبار جديد
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    let examId = null;
    let currentQuestionId = null;
    let isReviewMode = false;
    let currentVerseData = null;
    let originalErrors = 0;
    let originalNotes = '';
    let questionCount = 0;
    let totalErrorsCount = 0;

    // Check for in-progress exam on page load
    checkInProgressExam();

    // Juz selection logic
    function updateJuzCount() {
        const selected = $('.juz-checkbox:checked').length;
        $('#selectedJuzCount').text(selected);
        $('#nextQuestionBtn').prop('disabled', selected === 0);
    }

    // Select All / Clear All Juz
    $('#selectAllJuz').click(function() {
        $('.juz-checkbox').prop('checked', true);
        $('.juz-item').addClass('active');
        updateJuzCount();
    });

    $('#clearAllJuz').click(function() {
        $('.juz-checkbox').prop('checked', false);
        $('.juz-item').removeClass('active');
        updateJuzCount();
    });

    $('.juz-item').click(function(e) {
        if (e.target.tagName === 'INPUT') return;
        const checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        $(this).toggleClass('active', checkbox.prop('checked'));
    });

    $('.juz-checkbox').on('change', updateJuzCount);

    // Initially disable next question button until Juz is selected
    $('#nextQuestionBtn').prop('disabled', true);

    // Delegated click for question items
    $('#questionsList').on('click', '.question-item', function() {
        const $el = $(this);
        enterReviewMode({
            id: $el.data('id'),
            surah: $el.data('surah'),
            ayah_number: $el.data('ayah'),
            page_number: $el.data('page'),
            errors_count: $el.data('errors'),
            notes: $el.data('notes'),
            verse_text: $el.data('text')
        });
    });

    // Student selection - use existing select2 if available
    if ($('.select2').length && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            placeholder: '-- ابحث عن طالب --',
            allowClear: true,
            width: '100%',
            dir: 'rtl'
        });
    }

    // Start Exam
    $('#startExamBtn').click(function() {
        const studentId = $('#studentSelect').val();
        if (!studentId) {
            toastr.warning('الرجاء اختيار الطالب');
            return;
        }
        startExam(studentId);
    });

    // Next Question
    $('#nextQuestionBtn').click(function() {
        generateQuestion();
    });

    // Back to Exam (from review mode)
    $('#backToExamBtn').click(function() {
        exitReviewMode();
    });

    // Finish Exam
    $('#finishExamBtn').click(function() {
        if (!examId) return;
        const msg = questionCount === 0
            ? 'لا توجد أسئلة مسجلة. هل أنت متأكد من إنهاء الاختبار؟'
            : 'هل أنت متأكد من إنهاء الاختبار؟';
        if (!confirm(msg)) return;
        finishExam();
    });

    // Cancel Exam
    $('#cancelExamBtn').click(function() {
        if (!examId) return;
        if (confirm('هل أنت متأكد من إلغاء الاختبار؟ سيتم حذف جميع البيانات.')) {
            cancelExam();
        }
    });

    function checkInProgressExam() {
        $.get('/api/exam/in-progress')
        .done(function(response) {
            console.log('In-progress exam check:', response);
            if (response.exam) {
                examId = response.exam.id;
                loadInProgressExam(response.exam, response.questions);
            }
        })
        .fail(function(xhr) {
            console.error('Failed to check in-progress exam:', xhr);
        });
    }

    function startExam(studentId) {
        const $btn = $('#startExamBtn');
        $btn.prop('disabled', true).addClass('btn-loading').html('<i class="fas fa-spinner fa-spin me-2"></i>جاري البدء');
        $.post('/api/exam/start', {
            _token: csrfToken,
            student_id: studentId
        }, function(response) {
            examId = response.exam.id;
            showExamInterface(response.exam);
            toastr.success('تم بدء الاختبار');
            $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-play me-2"></i>بدء الاختبار');
        }).fail(function(xhr) {
            const msg = xhr.responseJSON?.error || 'حدث خطأ أثناء بدء الاختبار';
            toastr.error(msg);
            $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-play me-2"></i>بدء الاختبار');
        });
    }

    function showExamInterface(exam) {
        $('#student-selection').addClass('d-none');
        $('#exam-interface').removeClass('d-none');
        $('#examStudentName').text(exam.student?.user?.name || exam.student?.name || 'طالب');

        // Format exam date properly
        let examDate = exam.exam_date;
        if (typeof examDate === 'object' && examDate.date) {
            examDate = examDate.date;
        }
        const dateObj = new Date(examDate);
        const formattedDate = dateObj.toLocaleDateString('ar-SA', { year: 'numeric', month: 'long', day: 'numeric' });
        $('#examDate').text('التاريخ: ' + formattedDate);

        $('#examStatusBadge').removeClass('bg-success').addClass('bg-primary').text('قيد التقدم');
        $('#questionsList').html('<div class="text-center text-muted py-5" id="noQuestionsMsg"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i><p class="mb-0">لا توجد أسئلة بعد</p><small>اضغط على "السؤال التالي" لبدء الاختبار</small></div>');
        $('#questionsCount').text('0');
        $('#totalErrors').text('0');
        $('#noQuestionsMsg').removeClass('d-none');
        $('#finishExamBtn, #cancelExamBtn, #nextQuestionBtn, #addResultBtn').prop('disabled', false);
        questionCount = 0;
        totalErrorsCount = 0;
    }

    function loadInProgressExam(exam, questions) {
        if (!exam || !exam.id || !exam.student || !exam.student.user) {
            $('#student-selection').removeClass('d-none');
            $('#exam-interface').addClass('d-none');
            examId = null;
            return;
        }
        showExamInterface(exam);
        renderQuestionsList(questions);
    }

    function generateQuestion() {
        if (isReviewMode) {
            toastr.warning('الرجاء إنهاء التعديل أولاً');
            return;
        }
        const selectedJuz = $('.juz-checkbox:checked').map(function() { return parseInt(this.value); }).get();
        if (selectedJuz.length === 0) {
            toastr.warning('الرجاء اختيار جزء واحد على الأقل');
            return;
        }
        const $btn = $('#nextQuestionBtn');
        $btn.prop('disabled', true).addClass('btn-loading').html('<i class="fas fa-spinner fa-spin me-1"></i>جاري التحميل');
        $.ajax({
            url: '{{ route("supervisor.exam-assistant.generate") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                juz_list: selectedJuz,
            },
            success: function(response) {
                if (response.success) {
                    currentVerseData = response;
                    $('#pageDisplay').html(response.page_html);
                    $('#errorsCount').val(0);
                    $('#notesInput').val('');
                    toastr.success('تم توليد السؤال');
                }
                $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-arrow-right me-1"></i>السؤال التالي<span class="keyboard-hint">N</span>');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'حدث خطأ';
                toastr.error(msg);
                $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-arrow-right me-1"></i>السؤال التالي<span class="keyboard-hint">N</span>');
            }
        });
    }

    function addResult() {
        if (!examId || !currentVerseData) {
            toastr.warning('الرجاء توليد سؤال أولاً');
            return;
        }
        const ayahData = currentVerseData.ayah;
        const verseText = ayahData?.text
            || (typeof ayahData === 'string' ? ayahData : null);
        if (!verseText) {
            console.error('Verse text missing:', currentVerseData);
            toastr.error('بيانات الآية غير مكتملة');
            return;
        }
        const $btn = $('#addResultBtn');
        $btn.prop('disabled', true).addClass('btn-loading');
        $.post('/api/exam/add-question', {
            _token: csrfToken,
            exam_id: examId,
            surah_id: currentVerseData.surah_id,
            ayah_id: ayahData.id || currentVerseData.ayah_id,
            ayah_number: ayahData.ayah_number || 0,
            page_number: ayahData.page_number || 0,
            juz_number: ayahData.juz_number || 0,
            verse_text: verseText,
            errors_count: $('#errorsCount').val(),
            notes: $('#notesInput').val()
        }, function(response) {
            appendQuestion(response.question);
            currentVerseData = null;
            $('#pageDisplay').html('<div class="placeholder"><i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>اضغط على "السؤال التالي" لعرض آية عشوائية</div>');
            $('#errorsCount').val(0);
            $('#notesInput').val('');
            toastr.success('تم إضافة النتيجة');
            $btn.prop('disabled', false).removeClass('btn-loading');
        }).fail(function(xhr) {
            console.error('addResult error:', xhr);
            const msg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'حدث خطأ';
            toastr.error(msg);
            $btn.prop('disabled', false).removeClass('btn-loading');
        });
    }

    function appendQuestion(q) {
        $('#noQuestionsMsg').addClass('d-none');
        $('#questionsList').append(buildQuestionItemHTML(q));
        questionCount++;
        totalErrorsCount += parseInt(q.errors_count) || 0;
        $('#questionsCount').text(questionCount);
        $('#totalErrors').text(totalErrorsCount);
    }

    function updateStatsOnReload() {
        questionCount = 0;
        totalErrorsCount = 0;
        $('#questionsList .question-item').each(function() {
            questionCount++;
            totalErrorsCount += parseInt($(this).data('errors') || 0);
        });
        $('#questionsCount').text(questionCount);
        $('#totalErrors').text(totalErrorsCount);
    }

    const _escapeDiv = document.createElement('div');
    function escapeHtml(text) {
        if (!text) return '';
        _escapeDiv.textContent = text;
        return _escapeDiv.innerHTML;
    }

    function buildQuestionItemHTML(q) {
        const ref = buildQuestionRef(q);
        const errorsLabel = q.errors_count === 0
            ? '<span class="zero-errors">✓ لا أخطاء</span>'
            : '<small class="text-muted">أخطاء: ' + q.errors_count + '</small>';
        return `
        <div class="list-group-item list-group-item-action question-item"
             data-id="${q.id}"
             data-surah="${escapeHtml(q.surah?.name_ar || '')}"
             data-ayah="${q.ayah_number}"
             data-page="${q.page_number}"
             data-errors="${q.errors_count}"
             data-notes="${escapeHtml(q.notes || '')}"
             data-text="${escapeHtml(q.verse_text || '')}"
             style="cursor:pointer;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-bold">${ref}</div>
                    ${errorsLabel}
                    ${q.notes ? '<div class="small text-muted mt-1">' + truncateNotes(q.notes) + '</div>' : ''}
                </div>
                <span class="badge bg-secondary order-badge">#${q.order || 1}</span>
            </div>
        </div>`;
    }

    function buildQuestionRef(q) {
        return `${q.surah?.name_ar} - آية ${q.ayah_number} - صفحة ${q.page_number}`;
    }

    function truncateNotes(notes) {
        if (!notes) return '';
        return escapeHtml(notes).substring(0, 50) + (notes.length > 50 ? '...' : '');
    }

    function renderQuestionsList(questions) {
        if (!questions || questions.length === 0) {
            $('#noQuestionsMsg').removeClass('d-none');
            questionCount = 0;
            totalErrorsCount = 0;
            $('#questionsCount').text('0');
            $('#totalErrors').text('0');
            return;
        }

        let html = '';
        questions.forEach(function(q) {
            html += buildQuestionItemHTML(q);
        });

        $('#questionsList').html(html);
        $('#noQuestionsMsg').addClass('d-none');
        updateStatsOnReload();
    }

    function enterReviewMode(question) {
        if ($('#finishExamBtn').prop('disabled')) return;
        isReviewMode = true;
        currentQuestionId = question.id;
        originalErrors = parseInt(question.errors_count) || 0;
        originalNotes = question.notes || '';
        $('#errorsCount').val(originalErrors);
        $('#notesInput').val(originalNotes);
        $('#addResultBtn').removeClass('btn-generate').addClass('btn-warning').text('حفظ التعديلات');
        $('#backToExamBtn').removeClass('d-none');
        $('#resultFormCard .card-header h5').html('<i class="fas fa-edit me-2"></i>تعديل النتيجة');
        $('.question-item.active-review').removeClass('active-review');
        $('.question-item[data-id="' + question.id + '"]').addClass('active-review');
        toastr.info('وضعية المراجعة - عدّل وحفظ');
    }

    function exitReviewMode() {
        isReviewMode = false;
        currentQuestionId = null;
        $('#errorsCount').val(0);
        $('#notesInput').val('');
        $('#addResultBtn').removeClass('btn-warning').addClass('btn-generate').text('إضافة النتيجة');
        $('#backToExamBtn').addClass('d-none');
        $('#resultFormCard .card-header h5').html('<i class="fas fa-clipboard-check me-2"></i>تسجيل النتيجة');
        $('.question-item.active-review').removeClass('active-review');
    }

    $('#addResultBtn').click(function() {
        if (isReviewMode && currentQuestionId) {
            updateQuestion();
        } else {
            addResult();
        }
    });

    function updateQuestion() {
        const $btn = $('#addResultBtn');
        $btn.prop('disabled', true).addClass('btn-loading');
        $.ajax({
            url: '/api/exam/questions/' + currentQuestionId,
            method: 'PUT',
            data: {
                _token: csrfToken,
                errors_count: $('#errorsCount').val(),
                notes: $('#notesInput').val()
            },
            success: function(response) {
                toastr.success('تم تحديث النتيجة');
                exitReviewMode();
                reloadQuestions();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'حدث خطأ';
                toastr.error(msg);
                $btn.prop('disabled', false).removeClass('btn-loading');
            }
        });
    }

    function reloadQuestions() {
        if (!examId) return;
        $.get('/api/exam/' + examId + '/questions', function(response) {
            renderQuestionsList(response.questions);
        });
    }

    function finishExam() {
        $.post('/api/exam/' + examId + '/finish', {
            _token: csrfToken
        }, function(response) {
            window.examFinished = true;
            toastr.success('تم إنهاء الاختبار بنجاح');
            $('#examStatusBadge').removeClass('bg-primary').addClass('bg-success').text('مكتمل');
            $('#finishExamBtn, #cancelExamBtn, #nextQuestionBtn, #addResultBtn').prop('disabled', true);
            $('#finalQuestionsCount').text(questionCount);
            $('#finalErrorsCount').text(totalErrorsCount);
            $('#examCompleteOverlay').addClass('show');
        }).fail(function(xhr) {
            const msg = xhr.responseJSON?.error || 'حدث خطأ';
            toastr.error(msg);
        });
    }

    function cancelExam() {
        if (!examId) return;
        $.ajax({
            url: '/api/exam/' + examId,
            method: 'DELETE',
            data: { _token: csrfToken },
            success: function() {
                examId = null;
                location.reload();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'حدث خطأ';
                toastr.error(msg);
            }
        });
    }

    // Warn before leaving with unsaved in-progress exam
    $(window).on('beforeunload', function() {
        if (examId && !window.examFinished) {
            return 'لديك اختبار قيد التنفيذ. هل أنت متأكد من المغادرة؟';
        }
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        const tag = e.target.tagName;
        const isInput = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

        // Escape → exit review mode
        if (e.key === 'Escape' && isReviewMode) {
            const currentErrors = parseInt($('#errorsCount').val()) || 0;
            const currentNotes = $('#notesInput').val() || '';
            if (currentErrors !== originalErrors || currentNotes !== originalNotes) {
                if (!confirm('توجد تغييرات غير محفوظة. هل تريد المغادرة؟')) {
                    return;
                }
            }
            exitReviewMode();
        }

        // Ignore shortcuts when in inputs (except Escape)
        if (isInput && e.key !== 'Escape') return;

        // Enter on errors_count or notes → add result
        if (e.key === 'Enter') {
            const target = e.target;
            if (target.id === 'errorsCount' || target.id === 'notesInput') {
                e.preventDefault();
                if (isReviewMode && currentQuestionId) {
                    updateQuestion();
                } else if (!isReviewMode && currentVerseData) {
                    addResult();
                }
            }
        }

        // N key → next question (when not in input)
        if (e.key === 'n' || e.key === 'N') {
            if (!isInput && !isReviewMode) {
                generateQuestion();
            }
        }
    });
});
</script>
@endpush
