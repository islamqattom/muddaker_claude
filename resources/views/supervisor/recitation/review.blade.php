@extends('layouts.app')

@section('title', 'تسميع - مراجعة')
@section('page-title', 'جلسة تسميع - مراجعة')
@section('role-label', 'مشرف')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.recitation-review {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.recitation-review .page-card {
    border: 1px solid rgba(24, 43, 72, 0.08);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.recitation-review .page-card:hover {
    border-color: var(--gold);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.recitation-review .page-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
    padding: 12px 16px;
}

.recitation-review .result-indicator {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.recitation-review .color-green { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; }
.recitation-review .color-orange { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
.recitation-review .color-red { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
.recitation-review .color-black { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; }
.recitation-review .color-gray { background: linear-gradient(135deg, #ecf0f1, #d5dbdb); color: #7f8c8d; }

.recitation-review .input-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.recitation-review .input-btn:hover {
    transform: scale(1.05);
}

.recitation-review .counter-input {
    width: 56px;
    height: 40px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    border: 1px solid #e9ecef;
}

.recitation-review .counter-input:focus {
    border-color: var(--navy);
}

.recitation-review .student-header-card {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 20px;
    position: relative;
}

.recitation-review .student-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.recitation-review .student-header-content {
    position: relative;
    z-index: 1;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.recitation-review .student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.recitation-review .student-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    box-shadow: 0 4px 12px rgba(212, 160, 48, 0.3);
}

.recitation-review .student-name {
    color: white;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.recitation-review .student-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.recitation-review .summary-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.recitation-review .summary-header {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    padding: 14px 18px;
}

.recitation-review .summary-stat {
    padding: 14px;
    border-radius: 10px;
    text-align: center;
    transition: all 0.3s ease;
}

.recitation-review .summary-stat:hover {
    transform: translateY(-2px);
}

.recitation-review .summary-stat .stat-value {
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}

.recitation-review .summary-stat .stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.recitation-review .overall-result {
    padding: 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    margin-top: 12px;
}

.recitation-review .save-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 32px;
    font-size: 14px;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.3);
}

.recitation-review .save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.4);
}

.recitation-review .btn-outline-secondary {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.recitation-review .btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #ced4da;
}

.recitation-review .islamic-border {
    position: relative;
}

.recitation-review .islamic-border::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.recitation-review .quran-verse-header {
    font-family: 'Amiri', serif;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.7);
    text-align: center;
    padding: 8px 20px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212, 160, 48, 0.2);
}

.recitation-review .compact-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 6px;
}

@media (max-width: 768px) {
    .recitation-review .student-header-content {
        flex-direction: column;
        text-align: center;
    }

    .recitation-review .student-info {
        flex-direction: column;
    }

    .recitation-review .student-name {
        font-size: 16px;
    }

    .recitation-review .summary-stat .stat-value {
        font-size: 20px;
    }

    .recitation-review .result-indicator {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}
</style>
@endpush

@section('content')
<div class="recitation-review">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="student-header-card islamic-border">
                <div class="student-header-content">
                    <div class="student-info">
                        <div class="student-avatar">
                            <i class="fas fa-redo"></i>
                        </div>
                        <div>
                            <h2 class="student-name">{{ $student->user->name }}</h2>
                            <div class="student-meta">
                                <span class="badge bg-success text-white compact-badge">
                                    <i class="fas fa-layer-group me-1"></i>
                                    {{ $student->level->name }}
                                </span>
                                @if($student->student_number)
                                <span class="text-white opacity-75 small">
                                    <i class="fas fa-id-card me-1"></i>
                                    {{ $student->student_number }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-white text-start">
                        <span class="badge bg-success compact-badge px-3">
                            <i class="fas fa-redo me-1"></i>مراجعة
                        </span>
                        <div class="mt-2 opacity-75 small">
                            <i class="fas fa-book me-1"></i>
                            الجزء {{ $part->part_number }} |
                            <i class="fas fa-file-alt me-1"></i>
                            {{ $pagesCount }} صفحات
                            @if($continuationPart)
                                <span class="text-warning">+ الجزء {{ $continuationPart->part_number }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="quran-verse-header">
                    <i class="fas fa-book-quran me-2"></i>
                    {{ 'وَأَقِمِ الصَّلَاةَ لِذِكْرِي' }}
                </div>
            </div>

            <form id="recitationForm">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="type" value="review">
                <input type="hidden" name="part_id" value="{{ $part->id }}">
                <input type="hidden" name="session_date" value="{{ request('session_date', date('Y-m-d')) }}">

                @foreach($pages as $index => $pageNum)
                <div class="card mb-3 page-card islamic-border" id="page-card-{{ $index }}">
                    <div class="card-header d-flex justify-content-between align-items-center" id="page-header-{{ $index }}">
                        <div class="d-flex align-items-center gap-2">
                            <div class="result-indicator color-gray" id="color-indicator-{{ $index }}">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-file-alt me-1 text-success"></i>
                                    صفحة <span class="text-success">{{ $pageNum }}</span>
                                </h6>
                                <small class="text-muted">{{ $index + 1 }} / {{ count($pages) }}</small>
                            </div>
                        </div>
                        <div class="color-badge" id="color-badge-{{ $index }}">
                            <span class="badge bg-secondary compact-badge">في الانتظار</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <input type="hidden" name="pages[{{ $index }}][page_number]" value="{{ $pageNum }}">

                        <div class="row g-2 align-items-end">
                            <div class="col-4">
                                <label class="form-label fw-bold text-danger small mb-1">
                                    <i class="fas fa-times-circle me-1"></i>الأخطاء
                                </label>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <button type="button" class="btn btn-outline-danger input-btn" onclick="adjustValue('errors-{{ $index }}', -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number"
                                           name="pages[{{ $index }}][errors]"
                                           id="errors-{{ $index }}"
                                           class="form-control counter-input text-center fw-bold"
                                           value="0" min="0" max="20"
                                           oninput="updatePageColor({{ $index }})">
                                    <button type="button" class="btn btn-outline-danger input-btn" onclick="adjustValue('errors-{{ $index }}', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-bold text-warning small mb-1">
                                    <i class="fas fa-question-circle me-1"></i>الترددات
                                </label>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <button type="button" class="btn btn-outline-warning input-btn" onclick="adjustValue('hesitations-{{ $index }}', -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number"
                                           name="pages[{{ $index }}][hesitations]"
                                           id="hesitations-{{ $index }}"
                                           class="form-control counter-input text-center fw-bold"
                                           value="0" min="0" max="50"
                                           oninput="updatePageColor({{ $index }})">
                                    <button type="button" class="btn btn-outline-warning input-btn" onclick="adjustValue('hesitations-{{ $index }}', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="result-indicator color-gray mx-auto" id="result-box-{{ $index }}">
                                    <i class="fas fa-question"></i>
                                </div>
                                <div class="mt-1 fw-bold small" id="result-text-{{ $index }}">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="summary-card mb-3" id="summaryCard" style="display:none;">
                    <div class="summary-header">
                        <h5 class="mb-0 text-white"><i class="fas fa-chart-bar me-2"></i>ملخص الجلسة</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 text-center">
                            <div class="col-6 col-md-3">
                                <div class="summary-stat bg-danger bg-opacity-10">
                                    <div class="stat-value text-danger" id="sum-errors">0</div>
                                    <div class="stat-label text-muted">الأخطاء</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="summary-stat bg-warning bg-opacity-10">
                                    <div class="stat-value text-warning" id="sum-hesitations">0</div>
                                    <div class="stat-label text-muted">الترددات</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="summary-stat bg-success bg-opacity-10">
                                    <div class="stat-value text-success" id="sum-passed">0</div>
                                    <div class="stat-label text-muted">ناجحة</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="summary-stat bg-dark bg-opacity-10">
                                    <div class="stat-value text-dark" id="sum-failed">0</div>
                                    <div class="stat-label text-muted">راسبة</div>
                                </div>
                            </div>
                        </div>
                        <div class="overall-result" id="overall-result"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">
                        <i class="fas fa-sticky-note text-secondary me-1"></i>
                        ملاحظات (اختياري)
                    </label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="أي ملاحظات حول الجلسة..."></textarea>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-between mt-4">
                    <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary px-3" style="border-radius: 10px; font-size: 13px;" onclick="submitSessionIncomplete()">
                            <i class="fas fa-times-circle me-2"></i>
                            لم يكمل
                        </button>
                        <button type="button" class="save-btn" onclick="submitSession()">
                            <i class="fas fa-save me-2"></i>
                            حفظ الجلسة
                        </button>
                    </div>
                </div>
            </form>

        </div>
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
    const idx = id.split('-').pop();
    updatePageColor(idx);
}

function updatePageColor(index) {
    const errors = parseInt(document.getElementById('errors-' + index).value) || 0;
    const hesitations = parseInt(document.getElementById('hesitations-' + index).value) || 0;
    const totalEquivalent = errors + Math.floor(hesitations / 3);

    let colorClass, label, icon;
    if (totalEquivalent < 1) {
        colorClass = 'color-green'; label = 'ممتاز'; icon = 'fa-check';
    } else if (totalEquivalent < 2) {
        colorClass = 'color-orange'; label = 'جيد'; icon = 'fa-minus';
    } else if ((totalEquivalent === 2 && hesitations === 0) || (hesitations === 6 && totalEquivalent === 2) || (hesitations === 3 && totalEquivalent === 2)) {
        colorClass = 'color-red'; label = 'ضعيف'; icon = 'fa-times';
    } else if (totalEquivalent === 2 && hesitations >= 1) {
        colorClass = 'color-black'; label = 'راسب'; icon = 'fa-ban';
    } else if (totalEquivalent >= 3) {
        colorClass = 'color-black'; label = 'راسب'; icon = 'fa-ban';
    } else {
        colorClass = 'color-green'; label = 'ممتاز'; icon = 'fa-check';
    }

    const indicator = document.getElementById('color-indicator-' + index);
    indicator.className = 'result-indicator ' + colorClass;
    indicator.innerHTML = '<i class="fas ' + icon + '"></i>';

    const badge = document.getElementById('color-badge-' + index);
    const bgColor = colorClass === 'color-orange' ? '#f39c12' : colorClass === 'color-green' ? '#27ae60' : colorClass === 'color-red' ? '#e74c3c' : colorClass === 'color-black' ? '#2c3e50' : '#6c757d';
    badge.innerHTML = `<span class="badge compact-badge" style="background:${bgColor};color:white;">${label}</span>`;

    const resultBox = document.getElementById('result-box-' + index);
    resultBox.className = 'result-indicator ' + colorClass;
    resultBox.innerHTML = '<i class="fas ' + icon + '"></i>';

    document.getElementById('result-text-' + index).textContent = label;

    updateSummary();
}

function updateSummary() {
    let totalErrors = 0, totalHesitations = 0, passed = 0, failed = 0;

    for (let i = 0; i < pageCount; i++) {
        const errors = parseInt(document.getElementById('errors-' + i)?.value) || 0;
        const hesitations = parseInt(document.getElementById('hesitations-' + i)?.value) || 0;
        totalErrors += errors;
        totalHesitations += hesitations;

        const totalEquivalent = errors + Math.floor(hesitations / 3);
        if ((totalEquivalent === 2 && hesitations === 0) || (hesitations === 6 && totalEquivalent === 2) || (hesitations === 3 && totalEquivalent === 2)) {
            passed++;
        } else if ((totalEquivalent === 2 && hesitations >= 1) || totalEquivalent >= 3) {
            failed++;
        } else {
            passed++;
        }
    }

    document.getElementById('sum-errors').textContent = totalErrors;
    document.getElementById('sum-hesitations').textContent = totalHesitations;
    document.getElementById('sum-passed').textContent = passed;
    document.getElementById('sum-failed').textContent = failed;
    document.getElementById('summaryCard').style.display = 'block';

    const overallResult = document.getElementById('overall-result');
    if (failed === 0) {
        overallResult.className = 'overall-result text-white';
        overallResult.style.background = 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)';
        overallResult.innerHTML = '<i class="fas fa-check-circle me-2"></i>ناجح في الجلسة';
    } else {
        overallResult.className = 'overall-result text-white';
        overallResult.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
        overallResult.innerHTML = '<i class="fas fa-times-circle me-2"></i>راسب - ' + failed + ' صفحة راسبة';
    }
}

function submitSession() {
    const form = document.getElementById('recitationForm');
    const data = new FormData(form);
    for (let [key, value] of data.entries()) {
        if (key.includes('[errors]') || key.includes('[hesitations]') || key.includes('[page_number]')) {
            data.set(key, parseInt(value, 10) || 0);
        }
    }

    let allFilled = true;
    for (let i = 0; i < pageCount; i++) {
        if (!document.getElementById('errors-' + i)?.value) { allFilled = false; break; }
    }

    if (!allFilled) { toastr.warning('يرجى ملء بيانات جميع الصفحات'); return; }

    $.ajax({
        url: '{{ route('supervisor.recitation.save') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        success: function(response) {
            toastr.success('تم حفظ جلسة المراجعة بنجاح');
            setTimeout(() => window.location.href = '{{ route('supervisor.recitation.index') }}', 1500);
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
            else if (xhr.responseJSON && xhr.responseJSON.errors) errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            toastr.error(errorMsg);
        }
    });
}

function submitSessionIncomplete() {
    if (!confirm('هل أنت متأكد من حفظ هذه الجلسة على أنها لم يكمل؟')) return;

    const form = document.getElementById('recitationForm');
    const data = new FormData(form);
    for (let [key, value] of data.entries()) {
        if (key.includes('[errors]') || key.includes('[hesitations]') || key.includes('[page_number]')) {
            data.set(key, parseInt(value, 10) || 0);
        }
    }

    $.ajax({
        url: '{{ route('supervisor.recitation.saveIncomplete') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        timeout: 30000,
        success: function(response) {
            toastr.success('تم حفظ الجلسة بنجاح (لم يكمل)');
            setTimeout(() => window.location.href = '{{ route('supervisor.recitation.index') }}', 1500);
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
            else if (xhr.responseJSON && xhr.responseJSON.errors) errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            toastr.error(errorMsg);
        }
    });
}
</script>
@endpush