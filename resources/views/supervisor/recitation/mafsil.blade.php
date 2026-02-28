@extends('layouts.app')

@section('title', 'تسميع - مفصل')
@section('page-title', 'جلسة مفصل'.' - '.$mafsilType)
@section('role-label', 'مشرف')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-10">

<!-- Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:50px;height:50px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;font-weight:bold;">
                        {{ mb_substr($student->user->name, 0, 1) }}
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $student->user->name }}</h5>
                        <small class="text-muted">{{ $student->level->name ?? '' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge bg-danger fs-6 px-3 py-2">
                    <i class="fas fa-star me-2"></i>مفصل {{ $mafsilType }}
                </span>
                <div class="mt-2">
                    <strong>علامة الرسوب أقل من: </strong>
                    <span class="badge bg-danger fs-5">{{ $passingPercentage }}%</span>
                </div>
                @if(!$selectionMode)
                <div class="mt-1">
                    <strong>العلامة الحالية: </strong>
                    <span class="badge bg-primary fs-5" id="currentScore">100%</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Part Selection Mode -->
@if($selectionMode)
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="fas fa-hand-pointer me-2"></i>اختر الأجزاء</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info d-flex gap-3 align-items-start">
            <i class="fas fa-info-circle fa-lg mt-1"></i>
            <div>
                <strong>اختر {{ $partsCount }} أجزاء</strong> من أجزاء القرآن الكريم التي تريد اختبارها
            </div>
        </div>

        <form id="partSelectionForm" method="POST" action="{{ route('supervisor.recitation.mafsil.post') }}">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <input type="hidden" name="mafsil_type" value="{{ $mafsilType }}">

            <div class="row g-3 mb-3">
                @foreach($allParts as $part)
                <div class="col-md-4 col-sm-6">
                    <div class="form-check card h-100 part-checkbox-card">
                        <input class="form-check-input part-checkbox" type="checkbox"
                               name="selected_parts[]"
                               value="{{ $part->part_number }}"
                               id="part-{{ $part->part_number }}">
                        <label class="form-check-label w-100" for="part-{{ $part->part_number }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>الجزء {{ $part->part_number }}</strong>
                                <small class="text-muted">{{ $part->start_page }} - {{ $part->end_page }}</small>
                            </div>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="alert alert-warning d-none" id="selectionWarning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="selectionCount">0</span> / {{ $partsCount }} أجزاء محددة
            </div>

            <div class="d-flex gap-3 justify-content-between mt-4">
                <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-right me-2"></i>رجوع
                </a>
                <button type="submit" class="btn btn-primary btn-lg px-5" id="confirmPartsBtn" disabled>
                    <i class="fas fa-check me-2"></i>
                    تأكيد الأجزاء
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const requiredParts = {{ $partsCount }};

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.part-checkbox');
    const warningEl = document.getElementById('selectionWarning');
    const countEl = document.getElementById('selectionCount');
    const btn = document.getElementById('confirmPartsBtn');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.part-checkbox:checked').length;

            countEl.textContent = checkedCount;

            if (checkedCount > 0 && checkedCount < requiredParts) {
                warningEl.classList.remove('d-none');
                warningEl.className = 'alert alert-warning';
                btn.disabled = true;
            } else if (checkedCount === requiredParts) {
                warningEl.classList.add('d-none');
                btn.disabled = false;
            } else {
                warningEl.classList.remove('d-none');
                warningEl.className = 'alert alert-danger';
                btn.disabled = true;
            }
        });
    });
});
</script>
@endpush

@else
<!-- Scoring Mode -->

<!-- Scoring Rules -->
<div class="alert alert-info d-flex gap-3 align-items-start">
    <i class="fas fa-info-circle fa-lg mt-1"></i>
    <div>
        <strong>نظام الخصم:</strong>
        كل خطأ = <span class="badge bg-danger">-3 علامات</span>
        | كل تردد = <span class="badge bg-warning text-dark">-0.33 علامة</span> (يُجبر عند 3+)
        | العلامة النهائية لا تقل عن <span class="badge bg-secondary">{{ $passingPercentage }}</span>
    </div>
</div>

<form id="mafsilForm">
    @csrf
    <input type="hidden" name="student_id" value="{{ $student->id }}">
    <input type="hidden" name="type" value="mafsil">
    <input type="hidden" name="mafsil_type" value="{{ $mafsilType }}">
    <input type="hidden" name="session_date" value="{{ date('Y-m-d') }}">

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>أجزاء المفصل</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="mafsilTable">
                    <thead class="table-light">
                        <tr>
                            <th>الجزء</th>
                            <th>الصفحات</th>
                            <th width="150">الأخطاء</th>
                            <th width="150">الترددات</th>
                            <th width="120">الخصم</th>
                            <th width="120">العلامة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parts as $index => $part)
                        <tr id="mafsil-row-{{ $index }}">
                            <td>
                                <strong>الجزء {{ $part->part_number }}</strong>
                                <input type="hidden" name="pages[{{ $index }}][page_number]" value="{{ $part->part_number }}">
                            </td>
                            <td>
                                <small class="text-muted">{{ $part->start_page }} - {{ $part->end_page }}</small>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="adjustMafsil('errors-{{ $index }}', -1, {{ $index }})">-</button>
                                    <input type="number" name="pages[{{ $index }}][errors]" id="errors-{{ $index }}"
                                           class="form-control text-center fw-bold" value="0" min="0" max="50"
                                           oninput="calcMafsilRow({{ $index }})">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="adjustMafsil('errors-{{ $index }}', 1, {{ $index }})">+</button>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="adjustMafsil('hesitations-{{ $index }}', -1, {{ $index }})">-</button>
                                    <input type="number" name="pages[{{ $index }}][hesitations]" id="hesitations-{{ $index }}"
                                           class="form-control text-center fw-bold" value="0" min="0" max="100"
                                           oninput="calcMafsilRow({{ $index }})">
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="adjustMafsil('hesitations-{{ $index }}', 1, {{ $index }})">+</button>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary" id="deduction-{{ $index }}">-0</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6" id="part-score-{{ $index }}">0%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="2"><strong>المجموع</strong></td>
                            <td><span class="badge bg-danger" id="total-errors">0</span></td>
                            <td><span class="badge bg-warning text-dark" id="total-hesitations">0</span></td>
                            <td><span class="badge bg-secondary" id="total-deduction">-0</span></td>
                            <td><span class="badge bg-danger fs-5" id="final-score">0%</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">ملاحظات</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات..."></textarea>
    </div>

    <div class="d-flex gap-3 justify-content-between mt-4">
        <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-right me-2"></i>رجوع
        </a>
        <button type="button" class="btn btn-danger btn-lg px-5" onclick="submitMafsil()">
            <i class="fas fa-save me-2"></i>
            حفظ نتيجة المفصل
        </button>
    </div>
</form>

<!-- Failure Modal -->
<div class="modal fade" id="failureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ تحذير - الطالب راسب</h5>
            </div>
            <div class="modal-body text-center">
                <p class="fs-5">علامة الطالب أقل من <strong>{{ $passingPercentage }}%</strong></p>
                <p>هل تريد الإكمال أم الإيقاف؟</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="stopForFailure()">
                    <i class="fas fa-stop me-2"></i>إيقاف
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="continueAfterFailure()">
                    <i class="fas fa-play me-2"></i>إكمال
                </button>
            </div>
        </div>
    </div>
</div>

@endif

</div>
</div>
@endsection

@push('scripts')
<script>
const maxScore = {{ $maxScore }};
const partsCount = {{ $partsCount }};
const passingPercentage = {{ $passingPercentage }};
let hasInputBeenMade = false;
let failureShown = false;
let failedButContinuing = false;
let initialFailedRecordSaved = false;

// Initialize with 0% (starting grade is failed)
document.addEventListener('DOMContentLoaded', function() {
    // Initially show 100% - no inputs made yet
    document.getElementById('currentScore').textContent = '100%';
});

function adjustMafsil(id, delta, index) {
    const input = document.getElementById(id);
    let val = parseInt(input.value) + delta;
    val = Math.max(0, val);
    input.value = val;
    hasInputBeenMade = true;
    calcMafsilRow(index);
}

function calcMafsilRow(index) {
    const errors = parseInt(document.getElementById('errors-' + index).value) || 0;
    const hesitations = parseInt(document.getElementById('hesitations-' + index).value) || 0;

    hasInputBeenMade = true;

    const errorDeduction = errors * 3;
    const hesitationDeduction = Math.floor(hesitations / 3) + ((hesitations % 3) * 0.33);
    const totalDeduction = errorDeduction + hesitationDeduction;
    const rowScore = Math.max(0, maxScore - totalDeduction);

    document.getElementById('deduction-' + index).textContent = '-' + totalDeduction;
    document.getElementById('part-score-' + index).textContent = rowScore.toFixed(1) + '%';

    updateTotals();
}

function updateTotals() {
    let totalErrors = 0, totalHesitations = 0, totalDeduction = 0;

    for (let i = 0; i < partsCount; i++) {
        const errors = parseInt(document.getElementById('errors-' + i)?.value) || 0;
        const hesitations = parseInt(document.getElementById('hesitations-' + i)?.value) || 0;
        totalErrors += errors;
        totalHesitations += hesitations;
        totalDeduction += (errors * 3) + Math.floor(hesitations / 3) + ((hesitations % 3) * 0.33);
    }

    // Calculate final score: maxScore - deductions
    const finalScore = Math.max(0, maxScore - totalDeduction);

    document.getElementById('total-errors').textContent = totalErrors;
    document.getElementById('total-hesitations').textContent = totalHesitations;
    document.getElementById('total-deduction').textContent = '-' + totalDeduction;

    // If no input has been made yet, show passing percentage (failed) for final-score, 100% for currentScore
    if (!hasInputBeenMade) {
        document.getElementById('final-score').textContent = passingPercentage + '% (راسب)';
        document.getElementById('currentScore').textContent = '100%';
        document.getElementById('final-score').className = 'badge bg-danger fs-5';
        document.getElementById('currentScore').className = 'badge bg-primary fs-5';
        return;
    }

    document.getElementById('final-score').textContent = finalScore.toFixed(1) + '%';
    document.getElementById('currentScore').textContent = finalScore.toFixed(1) + '%';

    // Color the final score based on the calculated score
    const scoreEl = document.getElementById('final-score');
    const currentScoreEl = document.getElementById('currentScore');

    if (finalScore >= maxScore * 0.8) {
        scoreEl.className = 'badge bg-success fs-5';
        currentScoreEl.className = 'badge bg-success fs-5';
    } else if (finalScore >= maxScore * 0.6) {
        scoreEl.className = 'badge bg-warning text-dark fs-5';
        currentScoreEl.className = 'badge bg-warning text-dark fs-5';
    } else {
        scoreEl.className = 'badge bg-danger fs-5';
        currentScoreEl.className = 'badge bg-danger fs-5';
    }

    // Check if score falls below passing threshold and show failure modal
    if (finalScore < passingPercentage && !failureShown && !failedButContinuing && hasInputBeenMade) {
        failureShown = true;
        new bootstrap.Modal(document.getElementById('failureModal')).show();
    }
}

function submitMafsil() {
    const form = document.getElementById('mafsilForm');
    const data = new FormData(form);

    // If already saved an initial failed record, we need to indicate this is the final submission
    if (failedButContinuing && initialFailedRecordSaved) {
        data.append('is_final_submission', '1');
    }

    $.ajax({
        url: '{{ route('supervisor.recitation.save') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        success: function() {
            toastr.success('تم حفظ نتيجة المفصل بنجاح');
            setTimeout(() => window.location.href = '{{ route('supervisor.recitation.index') }}', 1500);
        },
        error: function() {
            window.location.href = '{{ route('supervisor.recitation.index') }}';
        }
    });
}

function stopForFailure() {
    const form = document.getElementById('mafsilForm');

    // Add hidden input to indicate failed due to failure
    let failedInput = form.querySelector('input[name="failed"]');
    if (!failedInput) {
        failedInput = document.createElement('input');
        failedInput.type = 'hidden';
        failedInput.name = 'failed';
        form.appendChild(failedInput);
    }
    failedInput.value = '1';

    bootstrap.Modal.getInstance(document.getElementById('failureModal')).hide();
    submitMafsil();
}

function continueAfterFailure() {
    const form = document.getElementById('mafsilForm');
    const data = new FormData(form);
    data.append('failed_continued', '1');

    failedButContinuing = true;

    // Save the initial failed record via AJAX
    $.ajax({
        url: '{{ route('supervisor.recitation.saveFailedContinue') }}',
        method: 'POST',
        data: data,
        processData: false,
        contentType: false,
        success: function() {
            initialFailedRecordSaved = true;
            toastr.info('تم تسجيل أن الطالب راسب وسيكمل');
        },
        error: function() {
            toastr.error('حدث خطأ في حفظ السجل');
        }
    });
}
</script>
@endpush
