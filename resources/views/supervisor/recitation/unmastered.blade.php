@extends('layouts.app')

@section('title', 'تسميع - حفظ جديد')
@section('page-title', 'جلسة تسميع - حفظ جديد')
@section('role-label', 'مشرف')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">

<!-- Session Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:50px;height:50px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;font-weight:bold;">
                        {{ mb_substr($student->user->name, 0, 1) }}
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $student->user->name }}</h5>
                        <small class="text-muted">{{ $student->level->name }} | {{ $student->student_number }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="fas fa-book-open me-2"></i>حفظ جديد
                </span>
                <span class="badge bg-secondary fs-6 px-3 py-2 me-2">
                    الجزء {{ $part->part_number }} | {{ $pagesCount }} صفحات
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Recitation Form -->
<form id="recitationForm">
    @csrf
    <input type="hidden" name="student_id" value="{{ $student->id }}">
    <input type="hidden" name="type" value="new_memorization">
    <input type="hidden" name="part_id" value="{{ $part->id }}">
    <input type="hidden" name="session_date" value="{{ date('Y-m-d') }}">

    <!-- Pages -->
    @foreach($pages as $index => $pageNum)
    <div class="card mb-3 page-card" id="page-card-{{ $index }}">
        <div class="card-header d-flex justify-content-between align-items-center" id="page-header-{{ $index }}">
            <div class="d-flex align-items-center gap-3">
                <div class="page-color-indicator" id="color-indicator-{{ $index }}"
                     style="width:30px;height:30px;border-radius:50%;background:#dee2e6;border:2px solid #adb5bd;transition:all 0.3s;"></div>
                <h6 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    صفحة رقم <strong>{{ $pageNum }}</strong>
                    <small class="text-muted">({{ $index + 1 }} من {{ count($pages) }})</small>
                </h6>
            </div>
            <div class="color-badge" id="color-badge-{{ $index }}">
                <span class="badge bg-secondary">في الانتظار</span>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" name="pages[{{ $index }}][page_number]" value="{{ $pageNum }}">

            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        <i class="fas fa-times-circle text-danger me-1"></i>
                        عدد الأخطاء
                    </label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-danger" onclick="adjustValue('errors-{{ $index }}', -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number"
                               name="pages[{{ $index }}][errors]"
                               id="errors-{{ $index }}"
                               class="form-control text-center fw-bold fs-4"
                               value="0" min="0" max="20"
                               oninput="updatePageColor({{ $index }})">
                        <button type="button" class="btn btn-outline-danger" onclick="adjustValue('errors-{{ $index }}', 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        <i class="fas fa-question-circle text-warning me-1"></i>
                        عدد الترددات
                    </label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-warning" onclick="adjustValue('hesitations-{{ $index }}', -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number"
                               name="pages[{{ $index }}][hesitations]"
                               id="hesitations-{{ $index }}"
                               class="form-control text-center fw-bold fs-4"
                               value="0" min="0" max="50"
                               oninput="updatePageColor({{ $index }})">
                        <button type="button" class="btn btn-outline-warning" onclick="adjustValue('hesitations-{{ $index }}', 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="p-2 rounded text-center fw-bold" id="result-box-{{ $index }}" style="min-height:50px;display:flex;align-items:center;justify-content:center;background:#f0f4f8;font-size:13px;transition:all 0.3s;">
                        —
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Summary Card -->
    <div class="card mb-4" id="summaryCard" style="display:none;">
        <div class="card-header">
            <h6><i class="fas fa-chart-bar me-2"></i>ملخص الجلسة</h6>
        </div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-3">
                    <div class="text-danger fw-bold fs-3" id="sum-errors">0</div>
                    <small>إجمالي الأخطاء</small>
                </div>
                <div class="col-3">
                    <div class="text-warning fw-bold fs-3" id="sum-hesitations">0</div>
                    <small>إجمالي الترددات</small>
                </div>
                <div class="col-3">
                    <div class="text-success fw-bold fs-3" id="sum-passed">0</div>
                    <small>صفحات ناجحة</small>
                </div>
                <div class="col-3">
                    <div class="text-dark fw-bold fs-3" id="sum-failed">0</div>
                    <small>صفحات راسبة</small>
                </div>
            </div>
            <div class="mt-3 p-3 rounded text-center fw-bold fs-5" id="overall-result">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">ملاحظات (اختياري)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="أي ملاحظات حول الجلسة..."></textarea>
    </div>

    <div class="d-flex gap-3 justify-content-between">
        <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary btn-lg px-4">
            <i class="fas fa-arrow-right me-2"></i>رجوع
        </a>
        <button type="button" class="btn btn-primary btn-lg px-5" onclick="submitSession()">
            <i class="fas fa-save me-2"></i>
            حفظ الجلسة
        </button>
    </div>
</form>

</div>
</div>
@endsection

@push('scripts')
<script>
const pageCount = {{ count($pages) }};

function adjustValue(id, delta) {
    const input = document.getElementById(id);
    let val = parseInt(input.value) + delta;
    val = Math.max(parseInt(input.min), Math.min(parseInt(input.max || 999), val));
    input.value = val;
    // Extract index from id
    const idx = id.split('-').pop();
    updatePageColor(idx);
}

function updatePageColor(index) {
    const errors = parseInt(document.getElementById('errors-' + index).value) || 0;
    const hesitations = parseInt(document.getElementById('hesitations-' + index).value) || 0;

    // حساب إجمالي الخطأ المكافئ: 3 ترددات = 1 خطأ دائماً
    const totalEquivalent = errors + Math.floor(hesitations / 3);

    let color, label, bgColor, passed;

    if (totalEquivalent < 1) {
        // أقل من 1 خطأ equivalent = أخضر
        color = '#27ae60'; label = 'ممتاز 🟢'; bgColor = '#d5f5e3'; passed = true;
    } else if (totalEquivalent < 2) {
        // من 1 إلى أقل من 2 = برتقالي
        color = '#f39c12'; label = 'جيد 🟠'; bgColor = '#fef9e7'; passed = true;
    } else if ((totalEquivalent === 2 && hesitations === 0) || (hesitations === 6 && totalEquivalent === 2) || (hesitations === 3 && totalEquivalent === 2)) {
        // أحمر: 2 خطأ بدون ترديد فقط، أو 2 equivalent مع 6 ترديدات، أو 2 equivalent مع 3 ترديدات
        color = '#e74c3c'; label = 'ضعيف 🔴'; bgColor = '#fadbd8'; passed = true;
    } else if (totalEquivalent === 2 && hesitations >= 1) {
        // أسود: 2 خطأ + 1 ترديد أو أكثر
        color = '#2c3e50'; label = 'راسب ⚫'; bgColor = '#d5d8dc'; passed = false;
    } else if (totalEquivalent >= 3) {
        // 3 خطأ equivalent أو أكثر = أسود
        color = '#2c3e50'; label = 'راسب ⚫'; bgColor = '#d5d8dc'; passed = false;
    } else {
        color = '#27ae60'; label = 'ممتاز 🟢'; bgColor = '#d5f5e3'; passed = true;
    }

    document.getElementById('color-indicator-' + index).style.background = color;
    document.getElementById('color-indicator-' + index).style.borderColor = color;

    const badge = document.getElementById('color-badge-' + index);
    badge.innerHTML = `<span class="badge" style="background:${color};color:${color === '#f39c12' ? '#000' : '#fff'}">${label}</span>`;

    const resultBox = document.getElementById('result-box-' + index);
    resultBox.style.background = bgColor;
    resultBox.style.color = color;
    resultBox.textContent = label;

    updateSummary();
}

function updateSummary() {
    let totalErrors = 0, totalHesitations = 0, passed = 0, failed = 0;

    for (let i = 0; i < pageCount; i++) {
        const errors = parseInt(document.getElementById('errors-' + i)?.value) || 0;
        const hesitations = parseInt(document.getElementById('hesitations-' + i)?.value) || 0;
        totalErrors += errors;
        totalHesitations += hesitations;

        // حساب إجمالي الخطأ المكافئ: 3 ترددات = 1 خطأ
        const totalEquivalent = errors + Math.floor(hesitations / 3);
        if ((totalEquivalent === 2 && hesitations === 0) || (hesitations === 6 && totalEquivalent === 2) || (hesitations === 3 && totalEquivalent === 2)) {
            passed++;
        }
        // الأسود: 2 خطأ + 1 ترديد أو أكثر (معادل >= 2 مع ترديدات)، أو 3+ أخطاء معادلة
        else if ((totalEquivalent === 2 && hesitations >= 1) || totalEquivalent >= 3) failed++;
        else passed++;
    }

    document.getElementById('sum-errors').textContent = totalErrors;
    document.getElementById('sum-hesitations').textContent = totalHesitations;
    document.getElementById('sum-passed').textContent = passed;
    document.getElementById('sum-failed').textContent = failed;
    document.getElementById('summaryCard').style.display = 'block';

    const overallResult = document.getElementById('overall-result');
    if (failed === 0) {
        overallResult.className = 'mt-3 p-3 rounded text-center fw-bold fs-5 bg-success text-white';
        overallResult.textContent = '✅ ناجح في الجلسة';
    } else {
        overallResult.className = 'mt-3 p-3 rounded text-center fw-bold fs-5 bg-danger text-white';
        overallResult.textContent = `❌ راسب - ${failed} صفحة راسبة`;
    }
}

function submitSession() {
    const form = document.getElementById('recitationForm');
    const data = new FormData(form);

    // Validate all pages have been entered
    let allFilled = true;
    for (let i = 0; i < pageCount; i++) {
        const errors = document.getElementById('errors-' + i)?.value;
        if (errors === null || errors === '') { allFilled = false; break; }
    }

    if (!allFilled) {
        toastr.warning('يرجى ملء بيانات جميع الصفحات');
        return;
    }

    $.ajax({
        url: '{{ route('supervisor.recitation.save') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        success: function(response) {
            toastr.success('تم حفظ جلسة التسميع بنجاح');
            setTimeout(() => window.location.href = '{{ route('supervisor.recitation.index') }}', 1500);
        },
        error: function(xhr) {
            if (xhr.responseJSON?.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat().join('\n');
                toastr.error(errors);
            } else {
                // If validation passes but redirect happens
                window.location.href = '{{ route('supervisor.recitation.index') }}';
            }
        }
    });
}
</script>
@endpush
