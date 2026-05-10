@extends('layouts.app')

@section('title', 'تسميع - مفصل')
@section('page-title', 'جلسة مفصل'.' - '.$mafsilType)
@section('role-label', 'مشرف')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.rec-session {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
}

.rec-session .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 20px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.rec-session .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rec-session .hero-content { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }

.rec-session .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 20px rgba(231,76,60,0.35);
}

.rec-session .student-info { display: flex; align-items: center; gap: 14px; }

.rec-session .student-avatar {
    width: 50px; height: 50px;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 4px 12px rgba(231,76,60,0.3);
}

.rec-session .student-name { font-size: 20px; font-weight: 700; color: white; margin-bottom: 4px; }

.rec-session .hero-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.rec-session .compact-badge {
    font-size: 11px; padding: 5px 12px; border-radius: 8px;
}

.rec-session .type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 12px;
    font-size: 13px; font-weight: 600;
}

.rec-session .type-badge.mafsil { background: rgba(231,76,60,0.25); color: var(--danger); }

.rec-session .session-info-badge {
    background: rgba(255,255,255,0.15);
    color: white;
    padding: 8px 16px; border-radius: 12px;
    font-size: 13px;
    backdrop-filter: blur(5px);
}

.rec-session .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    text-align: center;
    padding: 10px 20px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212,160,48,0.2);
    margin-top: 16px;
    position: relative; z-index: 1;
}

.rec-session .page-card {
    border: 1px solid rgba(24,43,72,0.08);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    margin-bottom: 16px;
}

.rec-session .page-card:hover {
    border-color: var(--gold);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.rec-session .page-card-header {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    padding: 14px 18px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid rgba(24,43,72,0.04);
}

.rec-session .page-title { display: flex; align-items: center; gap: 12px; }

.rec-session .result-indicator {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    transition: all 0.3s ease;
}

.rec-session .color-green { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; }
.rec-session .color-orange { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
.rec-session .color-red { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
.rec-session .color-black { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; }
.rec-session .color-gray { background: linear-gradient(135deg, #ecf0f1, #d5dbdb); color: #7f8c8d; }

.rec-session .color-badge { padding: 6px 14px; border-radius: 10px; font-size: 12px; font-weight: 600; }

.rec-session .page-card-body { padding: 18px; }

.rec-session .input-btn {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s ease;
    border: none;
}

.rec-session .input-btn:hover { transform: scale(1.05); }
.rec-session .input-btn.btn-danger { background: rgba(220,38,38,0.1); color: var(--danger); }
.rec-session .input-btn.btn-danger:hover { background: var(--danger); color: white; }
.rec-session .input-btn.btn-warning { background: rgba(217,119,6,0.1); color: var(--warning); }
.rec-session .input-btn.btn-warning:hover { background: var(--warning); color: white; }

.rec-session .counter-input {
    width: 60px; height: 42px;
    border-radius: 10px;
    font-size: 16px; font-weight: 700;
    text-align: center;
    border: 2px solid rgba(24,43,72,0.08);
    transition: all 0.3s ease;
}

.rec-session .counter-input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
}

.rec-session .info-alert {
    border-radius: 16px;
    border: none;
    padding: 16px 20px;
    background: linear-gradient(135deg, rgba(231,76,60,0.1) 0%, rgba(231,76,60,0.05) 100%);
    border-right: 4px solid var(--danger);
}

.rec-session .summary-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 20px;
}

.rec-session .summary-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 20px;
}

.rec-session .summary-stat {
    padding: 16px;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
}

.rec-session .summary-stat:hover { transform: translateY(-2px); }

.rec-session .summary-stat .stat-value {
    font-size: 26px; font-weight: 800; line-height: 1;
    margin-bottom: 4px;
}

.rec-session .summary-stat .stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rec-session .save-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 12px;
    padding: 12px 32px;
    font-size: 14px; font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(24,43,72,0.3);
}

.rec-session .save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.4); }

.rec-session .btn-outline-secondary {
    border: 1px solid rgba(24,43,72,0.1);
    border-radius: 12px;
    padding: 12px 20px;
    font-size: 13px; font-weight: 600;
    color: var(--navy);
    background: white;
    transition: all 0.3s ease;
}

.rec-session .btn-outline-secondary:hover { background: rgba(24,43,72,0.05); border-color: var(--navy); }

.rec-session .notes-textarea {
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.rec-session .notes-textarea:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
}

.rec-session .scoring-table {
    border-radius: 16px;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

.rec-session .scoring-table thead {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white;
}

.rec-session .scoring-table thead th {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 600;
    border: none;
}

.rec-session .scoring-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
}

.rec-session .scoring-table tbody tr:hover {
    background: rgba(212,160,48,0.04);
}

.rec-session .scoring-table tfoot {
    background: #f8f9fa;
}

.rec-session .scoring-table tfoot td {
    padding: 14px 16px;
    font-weight: 700;
}

@media (max-width: 768px) {
    .rec-session .hero-content { flex-direction: column; text-align: center; }
    .rec-session .student-info { flex-direction: column; }
    .rec-session .hero-meta { justify-content: center; }
    .rec-session .summary-stat .stat-value { font-size: 22px; }
}
</style>
@endpush

@section('content')
<div class="rec-session">
    <div class="page-hero">
        <div class="hero-content">
            <div class="student-info">
                <div class="student-avatar">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <h2 class="student-name">{{ $student->user->name }}</h2>
                    <div class="hero-meta">
                        <span class="compact-badge bg-warning text-dark">
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
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="type-badge mafsil">
                    <i class="fas fa-star"></i>مفصل {{ $mafsilType }}
                </span>
                <span class="session-info-badge">
                    <i class="fas fa-percentage me-1"></i>علامة الرسوب: {{ $passingPercentage }}%
                    <span class="opacity-50 mx-1">|</span>
                    <i class="fas fa-chart-line me-1"></i>العلامة الحالية: <span id="currentScore">100%</span>
                </span>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran me-2"></i>
            {{ 'إِنَّ هَذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ' }}
        </div>
    </div>

    <!-- Part Selection Mode -->
    @if($selectionMode)
    <div class="page-card">
        <div class="page-card-header">
            <h6 class="mb-0" style="color: var(--navy);">
                <i class="fas fa-hand-pointer me-2" style="color: var(--gold);"></i>
                اختر الأجزاء
            </h6>
        </div>
        <div class="page-card-body">
            <div class="info-alert d-flex gap-3 align-items-start mb-4">
                <i class="fas fa-info-circle fa-lg mt-1" style="color: var(--info);"></i>
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

    <div class="info-alert d-flex gap-3 align-items-start mb-4">
        <i class="fas fa-info-circle fa-lg mt-1" style="color: var(--info);"></i>
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

        <div class="scoring-table">
            <div class="card-header" style="background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); padding: 14px 18px;">
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

        <div class="mb-4 mt-4">
            <label class="form-label fw-bold mb-2" style="color: var(--navy);">
                <i class="fas fa-sticky-note me-2" style="color: var(--gold);"></i>
                ملاحظات (اختياري)
            </label>
            <textarea name="notes" class="form-control notes-textarea" rows="3" placeholder="أي ملاحظات حول الجلسة..."></textarea>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-between mt-4">
            <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i>رجوع
            </a>
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-secondary px-4" style="border-radius: 12px; font-size: 13px; font-weight: 600;" onclick="submitMafsilIncomplete()">
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
            <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header bg-danger text-white" style="padding: 16px 20px;">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تحذير - الطالب راسب</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" style="padding: 28px;">
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