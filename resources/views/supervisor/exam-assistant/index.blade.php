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
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    .verse-display {
        background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
        border-radius: 15px;
        padding: 40px;
        color: white;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-family: 'Amiri', 'Scheherazade', serif;
        font-size: 28px;
        line-height: 2.2;
        direction: rtl;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
    }

    .verse-display .verse-text {
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }

    .verse-display .placeholder {
        opacity: 0.6;
        font-size: 18px;
        font-family: 'Tajawal', sans-serif;
    }

    .scope-selector {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .scope-option {
        cursor: pointer;
        padding: 12px 20px;
        border-radius: 10px;
        border: 2px solid transparent;
        transition: all 0.2s;
        background: white;
    }

    .scope-option:hover {
        border-color: #B4802C;
    }

    .scope-option.active {
        border-color: #182B48;
        background: #182B48;
        color: white;
    }

    .scope-option input {
        display: none;
    }

    .btn-generate {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        border: none;
        padding: 14px 40px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
    }

    .btn-next {
        background: linear-gradient(135deg, #182B48, #2A4570);
        border: none;
        padding: 12px 30px;
        font-size: 14px;
        border-radius: 8px;
    }

    .btn-copy {
        background: #6c757d;
        border: none;
        padding: 12px 30px;
        font-size: 14px;
        border-radius: 8px;
    }

    .verse-reference {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        color: #6c757d;
    }

    .question-item {
        transition: all 0.2s;
        border-radius: 0;
        border-right: 3px solid transparent;
    }

    .question-item:hover {
        background: #f1f5f9;
        border-right-color: #B4802C;
    }

    .question-item:active {
        background: #e2e8f0;
    }

    #resultFormCard .card-header {
        border-bottom: 1px solid #e2e8f0;
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: none;
        color: white;
    }

    .highlighted-verse {
        background-color: #fef08a;
        color: #1e293b;
        border-radius: 4px;
        padding: 2px 6px;
        font-weight: bold;
    }

    .quran-page {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 0 0 15px 15px;
    }

    @media (max-width: 768px) {
        .verse-display {
            padding: 25px;
            font-size: 22px;
        }

        .scope-option {
            padding: 10px 15px;
            font-size: 14px;
        }
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade:wght@400;700&display=swap" rel="stylesheet">
@endpush

@section('content')
{{-- Student Selection (shown when no exam is active) --}}
<div id="student-selection" class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card exam-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>اختر الطالب</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">الطالب</label>
                    <select id="studentSelect" class="form-select select2">
                        <option value="">-- ابحث عن طالب --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} - {{ $student->level->name ?? '' }}</option>
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
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <h4 class="mb-0"><i class="fas fa-user-graduate me-2"></i><span id="examStudentName"></span></h4>
                <span class="badge bg-primary" id="examStatusBadge">قيد التقدم</span>
            </div>
            <small class="text-muted" id="examDate"></small>
        </div>
        <div class="col-md-4 text-end">
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
        {{-- Left: Page Display --}}
        <div class="col-lg-7">
            <div class="card exam-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-book-quran me-2"></i>صفحة القرآن</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-next text-white" id="nextQuestionBtn">
                            <i class="fas fa-arrow-right me-1"></i>السؤال التالي
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="verse-display" id="pageDisplay" style="border-radius: 15px;">
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
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>تسجيل النتيجة</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">عدد الأخطاء</label>
                        <input type="number" id="errorsCount" class="form-control" min="0" value="0" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
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
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>الأسئلة (<span id="questionsCount">0</span>)</h5>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <div id="questionsList" class="list-group list-group-flush">
                        <div class="text-center text-muted py-4" id="noQuestionsMsg">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            لا توجد أسئلة بعد
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let examId = null;
    let currentQuestionId = null;
    let isReviewMode = false;
    let currentVerseData = null;

    // Check for in-progress exam on page load
    checkInProgressExam();

    // Student selection - use existing select2 if available, otherwise use datalist
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

    // Add Result
    $('#addResultBtn').click(function() {
        if (!examId || !currentVerseData) {
            toastr.warning('الرجاء توليد سؤال أولاً');
            return;
        }
        addResult();
    });

    // Back to Exam (from review mode)
    $('#backToExamBtn').click(function() {
        exitReviewMode();
    });

    // Finish Exam
    $('#finishExamBtn').click(function() {
        if (!examId) return;
        if (confirm('هل أنت متأكد من إنهاء الاختبار؟')) {
            finishExam();
        }
    });

    // Cancel Exam
    $('#cancelExamBtn').click(function() {
        if (!examId) return;
        if (confirm('هل أنت متأكد من إلغاء الاختبار؟ سيتم حذف جميع البيانات.')) {
            cancelExam();
        }
    });

    function checkInProgressExam() {
        $.get('/api/exam/in-progress', function(response) {
            if (response.exam) {
                examId = response.exam.id;
                loadInProgressExam(response.exam, response.questions);
            }
        });
    }

    function startExam(studentId) {
        $.post('/api/exam/start', {
            _token: '{{ csrf_token() }}',
            student_id: studentId
        }, function(response) {
            examId = response.exam.id;
            showExamInterface(response.exam);
            toastr.success('تم بدء الاختبار');
        }).fail(function(xhr) {
            toastr.error('حدث خطأ أثناء بدء الاختبار');
        });
    }

    function showExamInterface(exam) {
        $('#student-selection').addClass('d-none');
        $('#exam-interface').removeClass('d-none');
        $('#examStudentName').text(exam.student?.name || 'طالب');
        $('#examDate').text('التاريخ: ' + exam.exam_date);
        $('#examStatusBadge').removeClass('bg-success').addClass('bg-primary').text('قيد التقدم');
    }

    function loadInProgressExam(exam, questions) {
        showExamInterface(exam);
        renderQuestionsList(questions);
    }

    function generateQuestion() {
        if (isReviewMode) {
            toastr.warning('الرجاء إنهاء التعديل أولاً');
            return;
        }
        $.ajax({
            url: '{{ route("supervisor.exam-assistant.generate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                scope_type: 'juz',
                start_juz: 1,
                end_juz: 30,
            },
            success: function(response) {
                if (response.success) {
                    currentVerseData = response;
                    $('#pageDisplay').html(response.page_html);
                    $('#errorsCount').val(0);
                    $('#notesInput').val('');
                    toastr.success('تم توليد السؤال');
                }
            },
            error: function() {
                toastr.error('حدث خطأ');
            }
        });
    }

    function addResult() {
        $.post('/api/exam/add-question', {
            _token: '{{ csrf_token() }}',
            exam_id: examId,
            surah_id: currentVerseData.surah_id,
            ayah_id: currentVerseData.ayah_id,
            ayah_number: currentVerseData.ayah_number,
            page_number: currentVerseData.page_number,
            juz_number: currentVerseData.juz_number,
            verse_text: currentVerseData.ayah.text || currentVerseData.ayah,
            errors_count: $('#errorsCount').val(),
            notes: $('#notesInput').val()
        }, function(response) {
            renderQuestionsList([response.question]);
            currentVerseData = null;
            $('#pageDisplay').html('<div class="placeholder"><i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>اضغط على "السؤال التالي" لعرض آية عشوائية</div>');
            $('#errorsCount').val(0);
            $('#notesInput').val('');
            toastr.success('تم إضافة النتيجة');
        }).fail(function(xhr) {
            toastr.error('حدث خطأ');
        });
    }

    function renderQuestionsList(questions) {
        if (!questions || questions.length === 0) {
            $('#noQuestionsMsg').removeClass('d-none');
            $('#questionsCount').text('0');
            return;
        }

        let html = '';
        questions.forEach(function(q) {
            const ref = q.surah?.name_ar + ' - آية ' + q.ayah_number + ' - صفحة ' + q.page_number;
            html += `
            <div class="list-group-item list-group-item-action question-item"
                 data-id="${q.id}"
                 data-surah="${q.surah?.name_ar || ''}"
                 data-ayah="${q.ayah_number}"
                 data-page="${q.page_number}"
                 data-errors="${q.errors_count}"
                 data-notes="${q.notes || ''}"
                 data-text="${q.verse_text || ''}"
                 style="cursor:pointer;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">${ref}</div>
                        <small class="text-muted">أخطاء: ${q.errors_count}</small>
                        ${q.notes ? '<div class="small text-muted mt-1">' + q.notes.substring(0, 50) + '...</div>' : ''}
                    </div>
                    <span class="badge bg-secondary">#${q.order || 1}</span>
                </div>
            </div>`;
        });

        $('#questionsList').html(html);
        $('#noQuestionsMsg').addClass('d-none');
        $('#questionsCount').text(questions.length);

        // Bind click to review
        $('.question-item').off('click').on('click', function() {
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
    }

    function enterReviewMode(question) {
        isReviewMode = true;
        currentQuestionId = question.id;
        $('#errorsCount').val(question.errors_count);
        $('#notesInput').val(question.notes);
        $('#addResultBtn').removeClass('btn-generate').addClass('btn-warning').text('حفظ التعديلات');
        $('#backToExamBtn').removeClass('d-none');
        $('#resultFormCard .card-header h5').html('<i class="fas fa-edit me-2"></i>تعديل النتيجة');
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
    }

    $('#addResultBtn').click(function() {
        if (isReviewMode && currentQuestionId) {
            updateQuestion();
        } else {
            addResult();
        }
    });

    function updateQuestion() {
        $.ajax({
            url: '/api/exam/questions/' + currentQuestionId,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                errors_count: $('#errorsCount').val(),
                notes: $('#notesInput').val()
            },
            success: function(response) {
                toastr.success('تم تحديث النتيجة');
                exitReviewMode();
                reloadQuestions();
            },
            error: function() {
                toastr.error('حدث خطأ');
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
            _token: '{{ csrf_token() }}'
        }, function(response) {
            toastr.success('تم إنهاء الاختبار بنجاح');
            $('#examStatusBadge').removeClass('bg-primary').addClass('bg-success').text('مكتمل');
            $('#finishExamBtn, #cancelExamBtn, #nextQuestionBtn').prop('disabled', true);
        }).fail(function() {
            toastr.error('حدث خطأ');
        });
    }

    function cancelExam() {
        // For now, just reload - could add cancel API endpoint
        location.reload();
    }
});
</script>
@endpush
