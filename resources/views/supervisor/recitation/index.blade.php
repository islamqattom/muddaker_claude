@extends('layouts.app')

@section('title', 'التسميع')
@section('page-title', 'جلسة تسميع جديدة')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-user-graduate me-2"></i>اختر الطالب ونوع التسميع</h6>
            </div>
            <div class="card-body">
                <form id="sessionStartForm">
                    <div class="mb-3">
                        <label class="form-label">الطالب <span class="text-danger">*</span></label>
                        <select name="student_id" id="studentSelect" class="form-select" required onchange="loadStudentInfo()">
                            <option value="">-- اختر الطالب --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}"
                                    data-level="{{ $student->level->name ?? '' }}"
                                    data-memorization="{{ $student->level->new_memorization_pages ?? 3 }}"
                                    data-review="{{ $student->level->review_pages ?? 6 }}"
                                    data-part="{{ $student->current_part }}"
                                    data-page="{{ $student->current_page }}">
                                    {{ $student->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Student Info Preview -->
                    <div id="studentInfo" class="d-none mb-3 p-3 bg-light rounded">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="fw-bold text-primary" id="si-level">-</div>
                                <small class="text-muted">المستوى</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-success" id="si-memorization">-</div>
                                <small class="text-muted">صفحات الحفظ</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-info" id="si-review">-</div>
                                <small class="text-muted">صفحات المراجعة</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">تاريخ التسميع <span class="text-muted">(اتركه فارغاً للتاريخ الحالي)</span></label>
                        <input type="date" name="session_date" id="sessionDate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">نوع التسميع <span class="text-danger">*</span></label>
                        <div class="row g-2" id="sessionTypes">
                            @php
                            $types = [
                                ['value' => 'new_memorization', 'label' => 'حفظ جديد', 'icon' => 'fa-book-open', 'color' => 'primary'],
                                ['value' => 'review', 'label' => 'مراجعة', 'icon' => 'fa-redo', 'color' => 'success'],
                                ['value' => 'unmastered', 'label' => 'حفظ غير متقن', 'icon' => 'fa-exclamation', 'color' => 'warning'],
                                ['value' => 'recitation', 'label' => 'سرد', 'icon' => 'fa-scroll', 'color' => 'info'],
                                ['value' => 'mafsil', 'label' => 'مفصل', 'icon' => 'fa-star', 'color' => 'danger'],
                                ['value' => 'not_recitate', 'label' => 'لم يسمع', 'icon' => 'fa-times-circle', 'color' => 'secondary'],
                            ];
                            @endphp
                            @foreach($types as $type)
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="type-{{ $type['value'] }}"
                                       value="{{ $type['value'] }}" onchange="showTypeOptions('{{ $type['value'] }}')">
                                <label class="btn btn-outline-{{ $type['color'] }} w-100 py-3" for="type-{{ $type['value'] }}">
                                    <i class="fas {{ $type['icon'] }} d-block mb-1 fs-5"></i>
                                    {{ $type['label'] }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Dynamic Options -->
                    <div id="options-new_memorization" class="d-none session-options">
                        <div class="row g-2">
                            <div class="col-8">
                                <label class="form-label">الجزء</label>
                                <select name="part_id_new" class="form-select" onchange="updatePartInfo('new', this.value)">
                                    @foreach($parts as $part)
                                        <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">رقم الصفحة</label>
                                <select name="page_number_new" class="form-select" id="page_number_new" required onchange="checkRemainingPages('new')">
                                </select>
                            </div>
                        </div>
                        <!-- Continuation Part (hidden by default) -->
                        <div id="continuation_part_new_wrapper" class="row g-2 mt-2 d-none">
                            <div class="col-12">
                                <label class="form-label text-info">الجزء المكمل (لإكمال الصفحات المتبقية)</label>
                                <select name="continuation_part_new" class="form-select" id="continuation_part_new">
                                    <option value="">-- اختر الجزء المكمل --</option>
                                    @foreach($parts as $part)
                                        <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Add Pages Button -->
                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddPagesModal('new_memorization')">
                                <i class="fas fa-plus me-1"></i>إضافة صفحات
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="openRepeatedPagesModal('new_memorization')">
                                <i class="fas fa-redo me-1"></i>صفحات معادة
                            </button>
                        </div>
                    </div>

                    <div id="options-review" class="d-none session-options">
                        <div class="row g-2">
                            <div class="col-8">
                                <label class="form-label">الجزء</label>
                                <select name="part_id_review" class="form-select" onchange="updatePartInfo('review', this.value)">
                                    @foreach($parts as $part)
                                        <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">رقم الصفحة</label>
                                <select name="page_number_review" class="form-select" id="page_number_review" required onchange="checkRemainingPages('review')">
                                </select>
                            </div>
                        </div>
                        <!-- Continuation Part (hidden by default) -->
                        <div id="continuation_part_review_wrapper" class="row g-2 mt-2 d-none">
                            <div class="col-12">
                                <label class="form-label text-info">الجزء المكمل (لإكمال الصفحات المتبقية)</label>
                                <select name="continuation_part_review" class="form-select" id="continuation_part_review">
                                    <option value="">-- اختر الجزء المكمل --</option>
                                    @foreach($parts as $part)
                                        <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Add Pages & Repeated Pages Buttons -->
                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddPagesModal('review')">
                                <i class="fas fa-plus me-1"></i>إضافة صفحات
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="openRepeatedPagesModal('review')">
                                <i class="fas fa-redo me-1"></i>صفحات معادة
                            </button>
                        </div>
                    </div>

                    <div id="options-unmastered" class="d-none session-options">
                        <div class="row g-2">
                            <div class="col-8">
                                <label class="form-label">الجزء</label>
                                <select name="part_id_unmastered" class="form-select" onchange="updatePartInfo('unmastered', this.value)">
                                    @foreach($parts as $part)
                                        <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">رقم الصفحة</label>
                                <select name="page_number_unmastered" class="form-select" id="page_number_unmastered" required>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">عدد الصفحات</label>
                                <div class="btn-group w-100">
                                    @foreach([5, 10, 15, 20] as $count)
                                    <input type="radio" class="btn-check" name="unmastered_count" id="uc-{{ $count }}" value="{{ $count }}" {{ $count === 5 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary" for="uc-{{ $count }}">{{ $count }}</label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <!-- Add Pages Button -->
                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddPagesModal('unmastered')">
                                <i class="fas fa-plus me-1"></i>إضافة صفحات
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="openRepeatedPagesModal('unmastered')">
                                <i class="fas fa-redo me-1"></i>صفحات معادة
                            </button>
                        </div>
                    </div>

                    <div id="options-recitation" class="d-none session-options">
                        <div class="col-12">
                            <label class="form-label">اختر الجزء للسرد</label>
                            <select name="part_id_recitation" class="form-select">
                                @foreach($parts as $part)
                                    <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}">الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="options-mafsil" class="d-none session-options">
                        <label class="form-label">نوع المفصل</label>
                        <div class="row g-2">
                            @php $mafsilTypes = [
                                [3, '90%'], [6, '90%'], [10, '80%'],
                                [15, '80%'], [20, '75%'], [25, '75%'], [30, '75%']
                            ]; @endphp
                            @foreach($mafsilTypes as [$num, $score])
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="mafsil_type" id="mt-{{ $num }}" value="{{ $num }}">
                                <label class="btn btn-outline-danger w-100 py-2" for="mt-{{ $num }}">
                                    <div class="fw-bold">{{ $num }}</div>
                                    <small>علامة الرسوب أقل من: {{ $score }}</small>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="options-not_recitate" class="d-none session-options">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            سيتم تسجيل الطالب على أنه لم يسمع بدون تقييم أي صفحات
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary w-100 py-3 fs-5" onclick="startSession()">
                            <i class="fas fa-play me-2"></i>
                            بدء الجلسة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Color Guide -->
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>نظام الألوان والتقييم</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-white" style="background:#27ae60;">
                            <div class="fw-bold fs-5 mb-1">
                                <span class="color-dot" style="background:#fff;"></span> أخضر - ممتاز
                            </div>
                            <div style="font-size:13px;">لم يخطئ الطالب أو تردد بحد أقصى ترددين</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-dark" style="background:#f39c12;">
                            <div class="fw-bold fs-5 mb-1">
                                <span class="color-dot" style="background:#fff;"></span> برتقالي - جيد
                            </div>
                            <div style="font-size:13px;">خطأ واحد أو من 3 إلى 5 ترددات</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-white" style="background:#c0392b;">
                            <div class="fw-bold fs-5 mb-1">
                                <span class="color-dot" style="background:#fff;"></span> أحمر - ضعيف
                            </div>
                            <div style="font-size:13px;">خطآن أو ما يعادلهما من الترددات</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-white" style="background:#2c3e50;">
                            <div class="fw-bold fs-5 mb-1">
                                <span class="color-dot" style="background:#fff;"></span> أسود - راسب
                            </div>
                            <div style="font-size:13px;">خطآن + تردد واحد أو أكثر = رسوب</div>
                        </div>
                    </div>
                </div>

                <style>
                    .color-dot {
                        display: inline-block;
                        width: 14px;
                        height: 14px;
                        border-radius: 50%;
                        margin-left: 8px;
                        vertical-align: middle;
                    }
                </style>

                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-primary">المفاصل وعلاماتها:</h6>
                    <table class="table table-sm mb-0">
                        <tr><td><strong>مفصل 3 و6</strong></td><td>علامة الرسوب أقل من: <span class="badge bg-success">90%</span></td></tr>
                        <tr><td><strong>مفصل 10 و15</strong></td><td>علامة الرسوب أقل من: <span class="badge bg-warning text-dark">80%</span></td></tr>
                        <tr><td><strong>مفصل 20 و25 و30</strong></td><td>علامة الرسوب أقل من: <span class="badge bg-danger">75%</span></td></tr>
                    </table>
                    <small class="text-muted mt-2 d-block">خطأ = -3 علامات | كل 3 ترددات = -1 علامة</small>
                </div>
            </div>
        </div>

        <!-- Today's Sessions -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-list me-2"></i>جلسات اليوم</h6>
            </div>
            <div class="card-body p-0">
                @php
                    $todaySessions = \App\Models\RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', auth()->id()))
                        ->whereDate('session_date', today())
                        ->with(['student.user', 'part'])
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp
                @forelse($todaySessions as $session)
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">{{ $session->student->user->name }}</div>
                        <small class="text-muted">
                            {{ $session->type_label }}
                            @if($session->part) - الجزء {{ $session->part->part_number }} @endif
                        </small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                        <div class="text-muted" style="font-size:11px;">
                            {{ $session->total_errors }} خطأ | {{ $session->total_hesitations }} تردد
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">لا توجد جلسات اليوم</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal for Not Recitate -->
<div class="modal fade" id="notRecitateModal" tabindex="-1" aria-labelledby="notRecitateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notRecitateModalLabel">لم يسمع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="notRecitateForm" method="POST" action="{{ route('supervisor.recitation.save-not-recitate') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="notRecitateStudentId">
                    <div class="mb-3">
                        <label class="form-label">التاريخ</label>
                        <input type="date" name="session_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">السبب (اختياري)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="أدخل سبب عدم التسميع"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Adding Pages (إضافة صفحات) -->
<div class="modal fade" id="addPagesModal" tabindex="-1" aria-labelledby="addPagesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPagesModalLabel">إضافة صفحات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="additionalPagesContainer">
                    <!-- Dynamic rows will be added here -->
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addAdditionalPageRow()">
                    <i class="fas fa-plus me-1"></i>إضافة صف
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="applyAdditionalPages()">تطبيق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Repeated Pages (صفحات معادة) -->
<div class="modal fade" id="repeatedPagesModal" tabindex="-1" aria-labelledby="repeatedPagesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repeatedPagesModalLabel">صفحات معادة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="repeatedPagesContainer">
                    <!-- Dynamic rows will be added here -->
                </div>
                <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addRepeatedPageRow()">
                    <i class="fas fa-plus me-1"></i>إضافة صف
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" onclick="applyRepeatedPages()">تطبيق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// تهيئة حقول أرقام الصفحات عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // تهيئة حقول الصفحات لكل نوع
    ['new', 'review', 'unmastered'].forEach(function(type) {
        const select = document.querySelector(`select[name="part_id_${type}"]`);
        if (select) {
            updatePartInfo(type, select.value);
        }
    });
});

function updatePartInfo(type, partId) {
    const partSelect = document.querySelector(`select[name="part_id_${type}"]`);
    const pageSelect = document.getElementById(`page_number_${type}`);

    if (!partSelect || !pageSelect) return;

    const selectedOption = partSelect.options[partSelect.selectedIndex];
    const startPage = parseInt(selectedOption.dataset.startPage);
    const endPage = parseInt(selectedOption.dataset.endPage);

    // مسح الخيارات الحالية
    pageSelect.innerHTML = '';

    // إنشاء خيارات الصفحات
    for (let page = startPage; page <= endPage; page++) {
        const option = document.createElement('option');
        option.value = page;
        option.textContent = 'صفحة ' + page;
        pageSelect.appendChild(option);
    }

    // تعيين الخاصية min و max للتحقق
    pageSelect.dataset.minPage = startPage;
    pageSelect.dataset.maxPage = endPage;

    // التحقق من الصفحات المتبقية بعد تحديث الصفحة
    checkRemainingPages(type);
}

function checkRemainingPages(type) {
    const studentSelect = document.getElementById('studentSelect');
    const selectedOption = studentSelect.options[studentSelect.selectedIndex];

    if (!studentSelect.value) return;

    // الحصول على عدد الصفحات المطلوبة حسب النوع
    let requiredPages = 0;
    if (type === 'new') {
        requiredPages = parseInt(selectedOption.dataset.memorization) || 3;
    } else if (type === 'review') {
        requiredPages = parseInt(selectedOption.dataset.review) || 6;
    }

    // الحصول على معلومات الجزء المحدد
    const partSelect = document.querySelector(`select[name="part_id_${type}"]`);
    const pageSelect = document.getElementById(`page_number_${type}`);
    const continuationWrapper = document.getElementById(`continuation_part_${type}_wrapper`);

    if (!partSelect || !pageSelect || !continuationWrapper) return;

    const selectedPartOption = partSelect.options[partSelect.selectedIndex];
    const endPage = parseInt(selectedPartOption.dataset.endPage);
    const selectedPage = parseInt(pageSelect.value);

    // حساب الصفحات المتبقية في الجزء الحالي
    const remainingPages = endPage - selectedPage + 1;

    // إذا كانت الصفحات المتبقية أقل من المطلوبة، إظهار اختيار الجزء المكمل
    if (remainingPages < requiredPages) {
        continuationWrapper.classList.remove('d-none');
    } else {
        continuationWrapper.classList.add('d-none');
        // إعادة تعيين القيمة
        const continuationSelect = document.getElementById(`continuation_part_${type}`);
        if (continuationSelect) continuationSelect.value = '';
    }
}

function loadStudentInfo() {
    const select = document.getElementById('studentSelect');
    const option = select.options[select.selectedIndex];
    const info = document.getElementById('studentInfo');

    if (select.value) {
        document.getElementById('si-level').textContent = option.dataset.level || '-';
        document.getElementById('si-memorization').textContent = option.dataset.memorization + ' صفحات';
        document.getElementById('si-review').textContent = option.dataset.review + ' صفحات';
        info.classList.remove('d-none');
    } else {
        info.classList.add('d-none');
    }
}

function showTypeOptions(type) {
    document.querySelectorAll('.session-options').forEach(el => el.classList.add('d-none'));
    const opt = document.getElementById('options-' + type);
    if (opt) {
        opt.classList.remove('d-none');
        // تهيئة أرقام الصفحات عند إظهار الخيارات
        if (type === 'new_memorization') {
            updatePartInfo('new', document.querySelector('select[name="part_id_new"]').value);
        } else if (type === 'review') {
            updatePartInfo('review', document.querySelector('select[name="part_id_review"]').value);
        } else if (type === 'unmastered') {
            updatePartInfo('unmastered', document.querySelector('select[name="part_id_unmastered"]').value);
        }
    }
}

// Store additional/repeated pages data
let additionalPagesData = [];
let repeatedPagesData = [];
let currentSessionType = '';

// Parts data for dropdowns
const partsData = @json($parts);

function openAddPagesModal(type) {
    currentSessionType = type;
    additionalPagesData = [];
    const container = document.getElementById('additionalPagesContainer');
    container.innerHTML = '';
    // Add first row by default
    addAdditionalPageRow();
    const modal = new bootstrap.Modal(document.getElementById('addPagesModal'));
    modal.show();
}

function addAdditionalPageRow() {
    const container = document.getElementById('additionalPagesContainer');
    const rowId = 'add-page-row-' + container.children.length;
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-end additional-page-row';
    row.id = rowId;

    // Build part options
    let partOptions = '<option value="">-- اختر الجزء --</option>';
    partsData.forEach(part => {
        partOptions += `<option value="${part.id}" data-start-page="${part.start_page}" data-end-page="${part.end_page}">الجزء ${part.part_number} (ص ${part.start_page} - ${part.end_page})</option>`;
    });

    row.innerHTML = `
        <div class="col-5">
            <label class="form-label mb-1">الجزء</label>
            <select name="part_id_add" class="form-select form-select-sm" onchange="updatePageOptions('${rowId}', this.value)">
                ${partOptions}
            </select>
        </div>
        <div class="col-4">
            <label class="form-label mb-1">الصفحة</label>
            <select name="page_number_add" class="form-select form-select-sm" id="page_${rowId}">
                <option value="">-- اختر الجزء أولاً --</option>
            </select>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePageRow('${rowId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}

function updatePageOptions(rowId, partId) {
    const row = document.getElementById(rowId);
    const partSelect = row.querySelector('select[name="part_id_add"]');
    const pageSelect = document.getElementById('page_' + rowId);

    if (!partId) {
        pageSelect.innerHTML = '<option value="">-- اختر الجزء أولاً --</option>';
        return;
    }

    const selectedOption = partSelect.options[partSelect.selectedIndex];
    const startPage = parseInt(selectedOption.dataset.startPage);
    const endPage = parseInt(selectedOption.dataset.endPage);

    pageSelect.innerHTML = '<option value="">-- اختر الصفحة --</option>';
    for (let page = startPage; page <= endPage; page++) {
        const option = document.createElement('option');
        option.value = page;
        option.textContent = 'صفحة ' + page;
        pageSelect.appendChild(option);
    }
}

function removePageRow(rowId) {
    const row = document.getElementById(rowId);
    row.remove();
}

function applyAdditionalPages() {
    const rows = document.querySelectorAll('.additional-page-row');
    additionalPagesData = [];

    rows.forEach(row => {
        const partId = row.querySelector('select[name="part_id_add"]').value;
        const pageNumber = row.querySelector('select[name="page_number_add"]').value;
        if (partId && pageNumber) {
            additionalPagesData.push({ part_id: partId, page_number: parseInt(pageNumber) });
        }
    });

    if (additionalPagesData.length === 0) {
        toastr.warning('يرجى اختيار صفحة واحدة على الأقل');
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('addPagesModal')).hide();
    toastr.success('تمت إضافة ' + additionalPagesData.length + ' صفحة');
}

// Repeated Pages Modal Functions
function openRepeatedPagesModal(type) {
    currentSessionType = type;
    repeatedPagesData = [];
    const container = document.getElementById('repeatedPagesContainer');
    container.innerHTML = '';
    // Add first row by default
    addRepeatedPageRow(type);
    const modal = new bootstrap.Modal(document.getElementById('repeatedPagesModal'));
    modal.show();
}

function addRepeatedPageRow(type) {
    const container = document.getElementById('repeatedPagesContainer');
    const rowId = 'repeated-page-row-' + container.children.length;
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-end repeated-page-row';
    row.id = rowId;

    // Determine which part selector to use based on type
    let currentPartId = '';
    if (type === 'review') {
        currentPartId = document.querySelector('select[name="part_id_review"]')?.value || '';
    } else if (type === 'new_memorization') {
        currentPartId = document.querySelector('select[name="part_id_new"]')?.value || '';
    } else if (type === 'unmastered') {
        currentPartId = document.querySelector('select[name="part_id_unmastered"]')?.value || '';
    }

    let partOptions = '<option value="">-- اختر الجزء --</option>';
    partsData.forEach(part => {
        const selected = part.id == currentPartId ? 'selected' : '';
        partOptions += `<option value="${part.id}" data-start-page="${part.start_page}" data-end-page="${part.end_page}" ${selected}>الجزء ${part.part_number} (ص ${part.start_page} - ${part.end_page})</option>`;
    });

    row.innerHTML = `
        <div class="col-5">
            <label class="form-label mb-1">الجزء</label>
            <select name="part_id_repeat" class="form-select form-select-sm" onchange="updateRepeatedPageOptions('${rowId}', this.value)">
                ${partOptions}
            </select>
        </div>
        <div class="col-4">
            <label class="form-label mb-1">الصفحة</label>
            <select name="page_number_repeat" class="form-select form-select-sm" id="page_repeat_${rowId}">
                <option value="">-- اختر الصفحة --</option>
            </select>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRepeatedPageRow('${rowId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(row);

    // If current part is selected, populate pages
    if (currentPartId) {
        updateRepeatedPageOptions(rowId, currentPartId);
    }
}

function updateRepeatedPageOptions(rowId, partId) {
    const row = document.getElementById(rowId);
    const partSelect = row.querySelector('select[name="part_id_repeat"]');
    const pageSelect = document.getElementById('page_repeat_' + rowId);

    if (!partId) {
        pageSelect.innerHTML = '<option value="">-- اختر الجزء أولاً --</option>';
        return;
    }

    const selectedOption = partSelect.options[partSelect.selectedIndex];
    const startPage = parseInt(selectedOption.dataset.startPage);
    const endPage = parseInt(selectedOption.dataset.endPage);

    pageSelect.innerHTML = '<option value="">-- اختر الصفحة --</option>';
    for (let page = startPage; page <= endPage; page++) {
        const option = document.createElement('option');
        option.value = page;
        option.textContent = 'صفحة ' + page;
        pageSelect.appendChild(option);
    }
}

function removeRepeatedPageRow(rowId) {
    const row = document.getElementById(rowId);
    row.remove();
}

function applyRepeatedPages() {
    const rows = document.querySelectorAll('.repeated-page-row');
    repeatedPagesData = [];

    rows.forEach(row => {
        const partId = row.querySelector('select[name="part_id_repeat"]').value;
        const pageNumber = row.querySelector('select[name="page_number_repeat"]').value;
        if (partId && pageNumber) {
            repeatedPagesData.push({ part_id: partId, page_number: parseInt(pageNumber) });
        }
    });

    if (repeatedPagesData.length === 0) {
        toastr.warning('يرجى اختيار صفحة واحدة على الأقل');
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('repeatedPagesModal')).hide();
    toastr.success('تمت إضافة ' + repeatedPagesData.length + ' صفحة معادة');
}

function startSession() {
    const studentId = document.getElementById('studentSelect').value;
    const typeEl = document.querySelector('input[name="type"]:checked');
    const sessionDateInput = document.getElementById('sessionDate');
    const sessionDate = sessionDateInput.value || new Date().toISOString().split('T')[0];

    if (!studentId) {
        toastr.warning('يرجى اختيار الطالب');
        return;
    }
    if (!typeEl) {
        toastr.warning('يرجى اختيار نوع التسميع');
        return;
    }

    const type = typeEl.value;
    let url = '';

    // Build additional params
    let additionalPagesParam = '';
    if (additionalPagesData.length > 0) {
        additionalPagesParam = '&additional_pages=' + encodeURIComponent(JSON.stringify(additionalPagesData));
    }

    // Build repeated pages params (for review, new_memorization, unmastered)
    let repeatedPagesParam = '';
    if (repeatedPagesData.length > 0 && (type === 'review' || type === 'new_memorization' || type === 'unmastered')) {
        repeatedPagesParam = '&repeated_pages=' + encodeURIComponent(JSON.stringify(repeatedPagesData));
    }

    if (type === 'new_memorization') {
        const partId = document.querySelector('select[name="part_id_new"]').value;
        const pageNumber = document.getElementById('page_number_new').value;
        // التحقق من نطاق الصفحة
        const pageSelect = document.getElementById('page_number_new');
        const minPage = parseInt(pageSelect.dataset.minPage);
        const maxPage = parseInt(pageSelect.dataset.maxPage);
        const page = parseInt(pageNumber);

        if (page < minPage || page > maxPage) {
            toastr.warning('رقم الصفحة يجب أن يكون بين ' + minPage + ' و ' + maxPage);
            return;
        }
        // التحقق من الجزء المكمل
        const continuationPart = document.querySelector('select[name="continuation_part_new"]')?.value;
        let continuationParam = '';
        if (continuationPart) {
            continuationParam = `&continuation_part_id=${continuationPart}`;
        }
        url = `{{ route('supervisor.recitation.new-memorization') }}?student_id=${studentId}&part_id=${partId}&page_number=${pageNumber}&session_date=${sessionDate}${continuationParam}${additionalPagesParam}${repeatedPagesParam}`;
    } else if (type === 'review') {
        const partId = document.querySelector('select[name="part_id_review"]').value;
        const pageNumber = document.getElementById('page_number_review').value;
        // التحقق من نطاق الصفحة
        const pageSelect = document.getElementById('page_number_review');
        const minPage = parseInt(pageSelect.dataset.minPage);
        const maxPage = parseInt(pageSelect.dataset.maxPage);
        const page = parseInt(pageNumber);

        if (page < minPage || page > maxPage) {
            toastr.warning('رقم الصفحة يجب أن يكون بين ' + minPage + ' و ' + maxPage);
            return;
        }
        // التحقق من الجزء المكمل
        const continuationPartReview = document.querySelector('select[name="continuation_part_review"]')?.value;
        let continuationParamReview = '';
        if (continuationPartReview) {
            continuationParamReview = `&continuation_part_id=${continuationPartReview}`;
        }
        url = `{{ route('supervisor.recitation.review') }}?student_id=${studentId}&part_id=${partId}&page_number=${pageNumber}&session_date=${sessionDate}${continuationParamReview}${additionalPagesParam}${repeatedPagesParam}`;
    } else if (type === 'unmastered') {
        const partId = document.querySelector('select[name="part_id_unmastered"]').value;
        const pageNumber = document.getElementById('page_number_unmastered').value;
        const count = document.querySelector('input[name="unmastered_count"]:checked')?.value || 5;
        // التحقق من نطاق الصفحة
        const pageSelect = document.getElementById('page_number_unmastered');
        const minPage = parseInt(pageSelect.dataset.minPage);
        const maxPage = parseInt(pageSelect.dataset.maxPage);
        const page = parseInt(pageNumber);

        if (page < minPage || page > maxPage) {
            toastr.warning('رقم الصفحة يجب أن يكون بين ' + minPage + ' و ' + maxPage);
            return;
        }
        url = `{{ route('supervisor.recitation.unmastered') }}?student_id=${studentId}&part_id=${partId}&pages_count=${count}&page_number=${pageNumber}&session_date=${sessionDate}${additionalPagesParam}${repeatedPagesParam}`;
    } else if (type === 'recitation') {
        const partId = document.querySelector('select[name="part_id_recitation"]').value;
        url = `{{ route('supervisor.recitation.recitation') }}?student_id=${studentId}&part_id=${partId}&session_date=${sessionDate}`;
    } else if (type === 'mafsil') {
        const mafsilType = document.querySelector('input[name="mafsil_type"]:checked')?.value;
        if (!mafsilType) { toastr.warning('يرجى اختيار نوع المفصل'); return; }
        url = `{{ route('supervisor.recitation.mafsil') }}?student_id=${studentId}&mafsil_type=${mafsilType}&session_date=${sessionDate}`;
    } else if (type === 'not_recitate') {
        // Show modal for not_recitate
        document.getElementById('notRecitateStudentId').value = studentId;
        const modal = new bootstrap.Modal(document.getElementById('notRecitateModal'));
        modal.show();
        return;
    }

    if (url) window.location.href = url;
}
</script>
@endpush
