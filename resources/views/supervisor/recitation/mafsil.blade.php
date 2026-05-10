@extends('layouts.app')

@section('title', 'تسميع - مفصل')
@section('page-title', 'جلسة مفصل'.' - '.$mafsilType)
@section('role-label', 'مشرف')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.recitation-mafsil {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.recitation-mafsil .student-header-card {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 20px;
    position: relative;
}

.recitation-mafsil .student-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.recitation-mafsil .student-header-content {
    position: relative;
    z-index: 1;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.recitation-mafsil .student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.recitation-mafsil .student-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.recitation-mafsil .student-name {
    color: white;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.recitation-mafsil .student-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.recitation-mafsil .quran-verse-header {
    font-family: 'Amiri', serif;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
    text-align: center;
    padding: 6px 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212, 160, 48, 0.2);
}

.recitation-mafsil .compact-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 6px;
}

.recitation-mafsil .score-badge {
    font-size: 14px;
    padding: 8px 16px;
    border-radius: 10px;
}

.recitation-mafsil .islamic-border {
    position: relative;
}

.recitation-mafsil .islamic-border::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.recitation-mafsil .info-alert {
    border-radius: 12px;
    border: none;
    padding: 14px 18px;
}

.recitation-mafsil .page-card {
    border: 1px solid rgba(24, 43, 72, 0.08);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.recitation-mafsil .page-card:hover {
    border-color: var(--gold);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.recitation-mafsil .input-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    padding: 0;
}

.recitation-mafsil .input-btn:hover {
    transform: scale(1.05);
}

.recitation-mafsil .counter-input {
    width: 44px;
    height: 32px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    border: 1px solid #e9ecef;
}

.recitation-mafsil .counter-input:focus {
    border-color: var(--navy);
}

.recitation-mafsil .part-checkbox-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 14px;
    transition: all 0.2s ease;
}

.recitation-mafsil .part-checkbox-card:hover {
    border-color: var(--gold);
    background: rgba(212, 160, 48, 0.04);
}

.recitation-mafsil .summary-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.recitation-mafsil .summary-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
}

.recitation-mafsil .summary-stat {
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    transition: all 0.3s ease;
}

.recitation-mafsil .summary-stat:hover {
    transform: translateY(-2px);
}

.recitation-mafsil .summary-stat .stat-value {
    font-size: 22px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}

.recitation-mafsil .summary-stat .stat-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.recitation-mafsil .save-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.3);
}

.recitation-mafsil .save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.4);
}

.recitation-mafsil .btn-outline-secondary {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.recitation-mafsil .btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #ced4da;
}

.recitation-mafsil .btn-stop {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
}

.recitation-mafsil .btn-stop:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}

.recitation-mafsil .scoring-table {
    border-radius: 12px;
    overflow: hidden;
}

.recitation-mafsil .scoring-table thead {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
}

.recitation-mafsil .scoring-table thead th {
    padding: 12px 14px;
    font-size: 12px;
    font-weight: 600;
    border: none;
}

.recitation-mafsil .scoring-table tbody td {
    padding: 10px 14px;
    vertical-align: middle;
}

.recitation-mafsil .scoring-table tbody tr:hover {
    background: rgba(212, 160, 48, 0.04);
}

.recitation-mafsil .scoring-table tfoot {
    background: #f8f9fa;
}

.recitation-mafsil .scoring-table tfoot td {
    padding: 12px 14px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .recitation-mafsil .student-header-content {
        flex-direction: column;
        text-align: center;
    }

    .recitation-mafsil .student-info {
        flex-direction: column;
    }

    .recitation-mafsil .student-name {
        font-size: 16px;
    }

    .recitation-mafsil .scoring-table {
        font-size: 12px;
    }

    .recitation-mafsil .scoring-table thead th,
    .recitation-mafsil .scoring-table tbody td,
    .recitation-mafsil .scoring-table tfoot td {
        padding: 8px 10px;
    }
}
</style>
@endpush

@section('content')
<div class="recitation-mafsil">
<div class="row justify-content-center">
<div class="col-lg-10">

<!-- Header -->
<div class="student-header-card islamic-border">
    <div class="student-header-content">
        <div class="student-info">
            <div class="student-avatar">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <h2 class="student-name">{{ $student->user->name }}</h2>
                <div class="student-meta">
                    <span class="badge bg-white text-primary compact-badge">
                        <i class="fas fa-layer-group me-1"></i>
                        {{ $student->level->name ?? '' }}
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
            <span class="badge bg-danger compact-badge px-3">
                <i class="fas fa-star me-1"></i>مفصل {{ $mafsilType }}
            </span>
            <div class="mt-2">
                <strong class="opacity-75 small">علامة الرسوب أقل من: </strong>
                <span class="badge bg-danger score-badge">{{ $passingPercentage }}%</span>
            </div>
            @if(!$selectionMode)
            <div class="mt-1">
                <strong class="opacity-75 small">العلامة الحالية: </strong>
                <span class="badge bg-primary score-badge" id="currentScore">100%</span>
            </div>
            @endif
        </div>
    </div>
    <div class="quran-verse-header">
        <i class="fas fa-book-quran me-2"></i>
        {{ 'إِنَّ هَذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ' }}
    </div>
</div>

<!-- Part Selection Mode -->
@if($selectionMode)
<div class="card mb-4" style="border-radius: 14px; overflow: hidden;">
    <div class="card-header" style="background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); padding: 14px 18px;">
        <h6 class="mb-0 text-white"><i class="fas fa-hand-pointer me-2"></i>اختر الأجزاء</h6>
    </div>
    <div class="card-body" style="padding: 18px;">
        <div class="alert alert-info info-alert d-flex gap-3 align-items-start mb-3">
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
                    <div class="form-check part-checkbox-card h-100">
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
                <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-2"></i>رجوع
                </a>
                <button type="submit" class="save-btn px-4" id="confirmPartsBtn" disabled>
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

<div class="alert alert-info info-alert d-flex gap-3 align-items-start mb-3">
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
    <input type="hidden" name="session_date" value="{{ request('session_date', date('Y-m-d')) }}">
    <input type="hidden" name="part_id" value="{{ $parts->first()->id ?? '' }}">

    <div class="card scoring-table" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header" style="background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); padding: 12px 16px;">
            <h6 class="mb-0 text-white"><i class="fas fa-list me-2"></i>أجزاء المفصل</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="mafsilTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-book me-1"></i>الجزء</th>
                            <th><i class="fas fa-file-alt me-1"></i>الصفحات</th>
                            <th width="140"><i class="fas fa-times-circle text-danger me-1"></i>الأخطاء</th>
                            <th width="140"><i class="fas fa-question-circle text-warning me-1"></i>الترددات</th>
                            <th width="100"><i class="fas fa-minus text-secondary me-1"></i>الخصم</th>
                            <th width="110"><i class="fas fa-chart-line me-1"></i>العلامة</th>
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
                                    <button type="button" class="btn btn-outline-danger input-btn" onclick="adjustMafsil('errors-{{ $index }}', -1, {{ $index }})">
                                        <i class="fas fa-minus" style="font-size:10px;"></i>
                                    </button>
                                    <input type="number" name="pages[{{ $index }}][errors]" id="errors-{{ $index }}"
                                           class="form-control text-center fw-bold" value="0" min="0" max="50"
                                           style="height: 32px; font-size: 13px; padding: 4px;"
                                           oninput="calcMafsilRow({{ $index }})">
                                    <button type="button" class="btn btn-outline-danger input-btn" onclick="adjustMafsil('errors-{{ $index }}', 1, {{ $index }})">
                                        <i class="fas fa-plus" style="font-size:10px;"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-outline-warning input-btn" onclick="adjustMafsil('hesitations-{{ $index }}', -1, {{ $index }})">
                                        <i class="fas fa-minus" style="font-size:10px;"></i>
                                    </button>
                                    <input type="number" name="pages[{{ $index }}][hesitations]" id="hesitations-{{ $index }}"
                                           class="form-control text-center fw-bold" value="0" min="0" max="100"
                                           style="height: 32px; font-size: 13px; padding: 4px;"
                                           oninput="calcMafsilRow({{ $index }})">
                                    <button type="button" class="btn btn-outline-warning input-btn" onclick="adjustMafsil('hesitations-{{ $index }}', 1, {{ $index }})">
                                        <i class="fas fa-plus" style="font-size:10px;"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary" id="deduction-{{ $index }}">-0</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger" id="part-score-{{ $index }}">0%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"><strong><i class="fas fa-calculator me-1"></i>المجموع</strong></td>
                            <td><span class="badge bg-danger" id="total-errors">0</span></td>
                            <td><span class="badge bg-warning text-dark" id="total-hesitations">0</span></td>
                            <td><span class="badge bg-secondary" id="total-deduction">-0</span></td>
                            <td><span class="badge bg-danger fs-6" id="final-score">0%</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-3 mt-3">
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
            <button type="button" class="btn btn-secondary px-3" style="border-radius: 10px; font-size: 13px;" onclick="submitMafsilIncomplete()">
                <i class="fas fa-times-circle me-2"></i>
                لم يكمل
            </button>
            <button type="button" class="save-btn" onclick="submitMafsil()">
                <i class="fas fa-save me-2"></i>
                حفظ نتيجة المفصل
            </button>
        </div>
    </div>
</form>

<!-- Failure Modal -->
<div class="modal fade" id="failureModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-danger text-white" style="padding: 14px 20px;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تحذير - الطالب راسب</h5>
            </div>
            <div class="modal-body text-center" style="padding: 24px;">
                <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                <p class="fs-5">علامة الطالب أقل من <strong>{{ $passingPercentage }}%</strong></p>
                <p class="text-muted">هل تريد الإكمال أم الإيقاف؟</p>
            </div>
            <div class="modal-footer" style="padding: 14px 20px;">
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

document.addEventListener('DOMContentLoaded', function() {
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

    const finalScore = Math.max(0, maxScore - totalDeduction);

    document.getElementById('total-errors').textContent = totalErrors;
    document.getElementById('total-hesitations').textContent = totalHesitations;
    document.getElementById('total-deduction').textContent = '-' + totalDeduction;

    if (!hasInputBeenMade) {
        document.getElementById('final-score').textContent = passingPercentage + '% (راسب)';
        document.getElementById('currentScore').textContent = '100%';
        document.getElementById('final-score').className = 'badge bg-danger';
        document.getElementById('currentScore').className = 'badge bg-primary score-badge';
        return;
    }

    document.getElementById('final-score').textContent = finalScore.toFixed(1) + '%';
    document.getElementById('currentScore').textContent = finalScore.toFixed(1) + '%';

    const scoreEl = document.getElementById('final-score');
    const currentScoreEl = document.getElementById('currentScore');

    if (finalScore >= maxScore * 0.8) {
        scoreEl.className = 'badge bg-success';
        currentScoreEl.className = 'badge bg-success score-badge';
    } else if (finalScore >= maxScore * 0.6) {
        scoreEl.className = 'badge bg-warning text-dark';
        currentScoreEl.className = 'badge bg-warning text-dark score-badge';
    } else {
        scoreEl.className = 'badge bg-danger';
        currentScoreEl.className = 'badge bg-danger score-badge';
    }

    if (finalScore < passingPercentage && !failureShown && !failedButContinuing && hasInputBeenMade) {
        failureShown = true;
        new bootstrap.Modal(document.getElementById('failureModal')).show();
    }
}

function submitMafsil() {
    const form = document.getElementById('mafsilForm');
    const data = new FormData(form);
    for (let [key, value] of data.entries()) {
        if (key.includes('[errors]') || key.includes('[hesitations]') || key.includes('[page_number]')) {
            data.set(key, parseInt(value, 10) || 0);
        }
    }

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
        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            }
            toastr.error(errorMsg);
        }
    });
}

function stopForFailure() {
    const form = document.getElementById('mafsilForm');

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
        error: function(xhr) {
            let errorMsg = 'حدث خطأ في حفظ السجل';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            }
            toastr.error(errorMsg);
        }
    });
}

function submitMafsilIncomplete() {
    if (!confirm('هل أنت متأكد من حفظ هذه الجلسة على أنها لم يكمل؟')) {
        return;
    }
    const form = document.getElementById('mafsilForm');
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
        success: function() {
            toastr.success('تم حفظ الجلسة بنجاح (لم يكمل)');
            setTimeout(() => window.location.href = '{{ route('supervisor.recitation.index') }}', 1500);
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            }
            toastr.error(errorMsg);
        }
    });
}
</script>
@endpush