@extends('layouts.app')
@section('title','تسميع - سرد')
@section('page-title','جلسة سرد - الجزء '.$part->part_number)
@section('sidebar-menu') @include('supervisor.partials.sidebar') @endsection

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
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
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
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 6px 20px rgba(212,160,48,0.35);
}

.rec-session .student-info { display: flex; align-items: center; gap: 14px; }

.rec-session .student-avatar {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: white;
    box-shadow: 0 4px 12px rgba(212,160,48,0.3);
}

.rec-session .student-name { font-size: 18px; font-weight: 700; color: white; margin-bottom: 4px; }

.rec-session .hero-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.rec-session .compact-badge {
    font-size: 11.5px; padding: 3px 10px; border-radius: 7px;
}

.rec-session .type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 9px;
    font-size: 12.5px; font-weight: 600;
}

.rec-session .type-badge.recitation { background: rgba(142,68,173,0.25); color: var(--purple); }

.rec-session .session-info-badge {
    background: rgba(255,255,255,0.15);
    color: white;
    padding: 6px 14px; border-radius: 9px;
    font-size: 12.5px;
    backdrop-filter: blur(5px);
}

.rec-session .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 12.5px;
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
    border-radius: 13px;
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
    padding: 12px 16px;
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

.rec-session .color-badge { padding: 4px 10px; border-radius: 7px; font-size: 11.5px; font-weight: 600; }

.rec-session .page-card-body { padding: 16px; }

.rec-session .input-btn {
    width: 30px; height: 30px;
    border-radius: 8px;
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
    width: 60px; height: 40px;
    border-radius: 9px;
    font-size: 15px; font-weight: 700;
    text-align: center;
    border: 2px solid rgba(24,43,72,0.08);
    transition: all 0.3s ease;
}

.rec-session .counter-input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
}

.rec-session .error-counter-badge {
    font-size: 12.5px;
    padding: 6px 14px;
    border-radius: 9px;
}

.rec-session .info-alert {
    border-radius: 13px;
    border: none;
    padding: 14px 16px;
    background: linear-gradient(135deg, rgba(52,152,219,0.1) 0%, rgba(52,152,219,0.05) 100%);
    border-right: 4px solid var(--info);
}

.rec-session .summary-card {
    background: white;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 16px;
}

.rec-session .summary-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
}

.rec-session .summary-stat {
    padding: 14px;
    border-radius: 13px;
    text-align: center;
    transition: all 0.3s ease;
}

.rec-session .summary-stat:hover { transform: translateY(-2px); }

.rec-session .summary-stat .stat-value {
    font-size: 22px; font-weight: 800; line-height: 1;
    margin-bottom: 4px;
}

.rec-session .summary-stat .stat-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rec-session .overall-result {
    padding: 14px;
    border-radius: 13px;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    margin-top: 14px;
}

.rec-session .save-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-size: 13px; font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(24,43,72,0.3);
}

.rec-session .save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.4); }

.rec-session .btn-outline-secondary {
    border: 1px solid rgba(24,43,72,0.1);
    border-radius: 9px;
    padding: 8px 16px;
    font-size: 13px; font-weight: 600;
    color: var(--navy);
    background: white;
    transition: all 0.3s ease;
}

.rec-session .btn-outline-secondary:hover { background: rgba(24,43,72,0.05); border-color: var(--navy); }

.rec-session .btn-stop {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-size: 13px; font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(231,76,60,0.3);
}

.rec-session .btn-stop:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(231,76,60,0.4); }

.rec-session .notes-textarea {
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 9px;
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
}

.rec-session .notes-textarea:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
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
                    <i class="fas fa-book-open"></i>
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
                <span class="type-badge recitation">
                    <i class="fas fa-book-open"></i>سرد
                </span>
                <span class="session-info-badge">
                    <i class="fas fa-book me-1"></i>الجزء {{ $part->part_number }}
                    <span class="opacity-50 mx-1">|</span>
                    <i class="fas fa-file-alt me-1"></i>{{ count($pages) }} صفحة
                </span>
                <span class="error-counter-badge badge bg-danger" id="error-counter">
                    <i class="fas fa-times-circle me-1"></i>
                    الأخطاء: 0 / 6
                </span>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran me-2"></i>
            {{ 'وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا' }}
        </div>
    </div>

    <div class="info-alert d-flex gap-3 align-items-start mb-4">
        <i class="fas fa-info-circle fa-lg mt-1" style="color: var(--info);"></i>
        <div>
            <strong>السرد:</strong> تسميع الجزء {{ $part->part_number }} كاملاً ({{ count($pages) }} صفحة)
            <br>
            <span class="text-danger">في حال وصل الطالب لـ <strong>6 أخطاء</strong> يظهر تنبيه للإكمال أو التوقف</span>
        </div>
    </div>

    <form id="sardForm">
        @csrf
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="type" value="recitation">
        <input type="hidden" name="part_id" value="{{ $part->id }}">
        <input type="hidden" name="session_date" value="{{ request('session_date', date('Y-m-d')) }}">
        <input type="hidden" name="failed" id="failed" value="0">

        @foreach($pages as $index => $pageNum)
        <div class="page-card" id="page-card-{{ $index }}">
            <div class="page-card-header">
                <div class="page-title">
                    <div class="result-indicator color-gray" id="color-indicator-{{ $index }}">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--navy);">
                            <i class="fas fa-file-alt me-2 text-purple"></i>
                            صفحة <span class="text-purple">{{ $pageNum }}</span>
                        </h6>
                        <small class="text-muted">{{ $index + 1 }} / {{ count($pages) }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="not-completed-btn-{{ $index }}" onclick="toggleNotCompleted({{ $index }})" title="لم يكمل الطالب هذه الصفحة">
                        <i class="fas fa-times-circle me-1"></i>لم يكمل
                    </button>
                    <div class="color-badge" id="color-badge-{{ $index }}">
                        <span class="badge bg-secondary" style="font-size: 11px; padding: 5px 12px; border-radius: 8px;">—</span>
                    </div>
                </div>
            </div>
            <div class="page-card-body">
                <input type="hidden" name="pages[{{ $index }}][page_number]" value="{{ $pageNum }}">
                <input type="hidden" name="pages[{{ $index }}][is_completed]" id="is-completed-{{ $index }}" value="1">

                <div class="row g-4 align-items-end">
                    <div class="col-4">
                        <label class="form-label fw-bold text-danger mb-2" style="font-size: 13px;">
                            <i class="fas fa-times-circle me-1"></i>الأخطاء
                        </label>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button type="button" class="input-btn btn-danger" onclick="adjustSardValue('e-{{ $index }}', -1, {{ $index }})">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number"
                                   name="pages[{{ $index }}][errors]"
                                   id="e-{{ $index }}"
                                   class="form-control counter-input text-center fw-bold"
                                   value="0" min="0" max="20"
                                   oninput="updateSardColor({{ $index }})">
                            <button type="button" class="input-btn btn-danger" onclick="adjustSardValue('e-{{ $index }}', 1, {{ $index }})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold text-warning mb-2" style="font-size: 13px;">
                            <i class="fas fa-question-circle me-1"></i>الترددات
                        </label>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button type="button" class="input-btn btn-warning" onclick="adjustSardValue('h-{{ $index }}', -1, {{ $index }})">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number"
                                   name="pages[{{ $index }}][hesitations]"
                                   id="h-{{ $index }}"
                                   class="form-control counter-input text-center fw-bold"
                                   value="0" min="0" max="50"
                                   oninput="updateSardColor({{ $index }})">
                            <button type="button" class="input-btn btn-warning" onclick="adjustSardValue('h-{{ $index }}', 1, {{ $index }})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="result-indicator color-gray mx-auto" id="result-box-{{ $index }}" style="width: 48px; height: 48px; font-size: 18px;">
                            <i class="fas fa-question"></i>
                        </div>
                        <div class="mt-2 fw-bold" id="result-text-{{ $index }}" style="color: #95a5a6; font-size: 13px;">—</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="mb-4">
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
                <button type="button" class="btn-stop" onclick="stopSard()">
                    <i class="fas fa-stop me-2"></i>إيقاف مبكر
                </button>
                <button type="button" class="btn btn-secondary px-4" style="border-radius: 12px; font-size: 13px; font-weight: 600;" onclick="submitSardIncomplete()">
                    <i class="fas fa-times-circle me-2"></i>
                    لم يكمل
                </button>
                <button type="button" class="save-btn" onclick="submitSard()">
                    <i class="fas fa-save me-2"></i>
                    حفظ نتيجة السرد
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Max Errors Modal -->
<div class="modal fade" id="maxErrorsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-danger text-white" style="padding: 16px 20px;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تحذير - وصل للحد الأقصى من الأخطاء</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 28px;">
                <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                <p class="fs-5">وصل الطالب إلى <strong>6 أخطاء</strong> في السرد</p>
                <p class="text-muted">هل تريد الإكمال أم الإيقاف؟</p>
            </div>
            <div class="modal-footer" style="padding: 14px 20px;">
                <button type="button" class="btn btn-danger" onclick="stopSardNow()">
                    <i class="fas fa-stop me-2"></i>إيقاف
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-play me-2"></i>إكمال
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mafsil Required Modal -->
<div class="modal fade" id="mafsilRequiredModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); border: none;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-star me-2" style="color: #d4a030;"></i>
                    تنبيه المفصل
                </h5>
            </div>
            <div class="modal-body text-center p-4" id="mafsilRequiredBody"></div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary px-4" onclick="finishMafsilAlert()">
                    <i class="fas fa-check me-1"></i>تم
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let totalErrors = 0;
let maxErrorsShown = false;
const pageCount = {{ count($pages) }};
const colors = { green:'#27ae60', orange:'#f39c12', red:'#e74c3c', black:'#2c3e50', gray:'#6c757d' };

function adjustSardValue(id, delta, index) {
    if (document.getElementById('is-completed-' + index)?.value === '0') return;

    const input = document.getElementById(id);
    let val = parseInt(input.value) + delta;
    val = Math.max(parseInt(input.min), Math.min(999, val));
    input.value = val;
    updateSardColor(index);
}

function toggleNotCompleted(index) {
    const hidden = document.getElementById('is-completed-' + index);
    if (!hidden) return;

    const marking = hidden.value === '1';
    hidden.value = marking ? '0' : '1';

    const btn = document.getElementById('not-completed-btn-' + index);
    const errorsInput = document.getElementById('e-' + index);
    const hesitationsInput = document.getElementById('h-' + index);

    if (marking) {
        if (errorsInput) { errorsInput.value = 0; errorsInput.readOnly = true; errorsInput.classList.add('bg-light'); }
        if (hesitationsInput) { hesitationsInput.value = 0; hesitationsInput.readOnly = true; hesitationsInput.classList.add('bg-light'); }
        if (btn) { btn.classList.remove('btn-outline-secondary'); btn.classList.add('btn-secondary'); }
    } else {
        if (errorsInput) { errorsInput.readOnly = false; errorsInput.classList.remove('bg-light'); }
        if (hesitationsInput) { hesitationsInput.readOnly = false; hesitationsInput.classList.remove('bg-light'); }
        if (btn) { btn.classList.remove('btn-secondary'); btn.classList.add('btn-outline-secondary'); }
    }

    updateSardColor(index);
}

function updateSardColor(index) {
    if (document.getElementById('is-completed-' + index)?.value === '0') {
        const gray = colors.gray;
        document.getElementById('color-indicator-' + index).className = 'result-indicator color-gray';
        document.getElementById('color-indicator-' + index).innerHTML = '<i class="fas fa-times-circle"></i>';
        document.getElementById('color-badge-' + index).innerHTML = '<span class="badge" style="background:' + gray + '; color: white; font-size: 11px; padding: 5px 12px; border-radius: 8px;">لم يكمل</span>';
        document.getElementById('result-box-' + index).className = 'result-indicator color-gray mx-auto';
        document.getElementById('result-box-' + index).innerHTML = '<i class="fas fa-times-circle"></i>';
        document.getElementById('result-text-' + index).textContent = 'لم يكمل';
        document.getElementById('result-text-' + index).style.color = gray;
        document.getElementById('page-card-' + index).style.borderColor = gray;
        document.getElementById('page-card-' + index).style.background = gray + '11';
        updateTotalErrors();
        return;
    }

    const errors = parseInt(document.getElementById('e-'+index).value)||0;
    const hesitations = parseInt(document.getElementById('h-'+index).value)||0;
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
    badge.innerHTML = '<span class="badge" style="background:' + bgColor + '; color: white; font-size: 11px; padding: 5px 12px; border-radius: 8px;">' + label + '</span>';

    const resultBox = document.getElementById('result-box-' + index);
    resultBox.className = 'result-indicator ' + colorClass + ' mx-auto';
    resultBox.style.width = '48px';
    resultBox.style.height = '48px';
    resultBox.style.fontSize = '18px';
    resultBox.innerHTML = '<i class="fas ' + icon + '"></i>';

    document.getElementById('result-text-' + index).textContent = label;
    document.getElementById('result-text-' + index).style.color = bgColor;

    document.getElementById('page-card-'+index).style.borderColor = bgColor;
    document.getElementById('page-card-'+index).style.background = bgColor + '11';

    updateTotalErrors();
}

function updateTotalErrors() {
    let total = 0;
    for(let i=0;i<pageCount;i++) {
        if (document.getElementById('is-completed-'+i)?.value === '0') continue;
        const errors = parseInt(document.getElementById('e-'+i)?.value)||0;
        const hesitations = parseInt(document.getElementById('h-'+i)?.value)||0;
        total += errors + Math.floor(hesitations / 3);
    }
    totalErrors = total;
    const counter = document.getElementById('error-counter');
    counter.innerHTML = '<i class="fas fa-times-circle me-1"></i>الأخطاء: ' + total + ' / 6';
    if(total >= 6 && !maxErrorsShown) { maxErrorsShown=true; new bootstrap.Modal(document.getElementById('maxErrorsModal')).show(); }
}

let isSubmittingSard = false;
let pendingMafsilAlertIds = [];

function showMafsilAlert(response) {
    pendingMafsilAlertIds = (response.mafsil_alerts || []).map(a => a.id);
    const stations = (response.mafsil_alerts || [])
        .map(a => a.station)
        .filter(Boolean)
        .sort((a, b) => a - b);
    const count = response.mafsil_alerts_count || 0;
    const student = response.mafsil_alerts_student || '';
    const stationsText = stations.map(s => 'مفصل ' + s).join('، ');

    document.getElementById('mafsilRequiredBody').innerHTML =
        '<div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#d4a030,#e6b84d);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#fff;font-size:26px;">' +
        '<i class="fas fa-star"></i></div>' +
        '<p class="fs-5 mb-2" style="color:#182B48;font-weight:700;">الطالب ' + student + ' يجب أن يكون لديه مفصّل</p>' +
        '<p class="text-muted mb-2">أتمّ سرد ' + count + ' أجزاء من الحفظ الجديد</p>' +
        (stationsText ? '<span class="badge bg-danger">' + stationsText + '</span>' : '');

    new bootstrap.Modal(document.getElementById('mafsilRequiredModal')).show();
}

function finishMafsilAlert() {
    const modalEl = document.getElementById('mafsilRequiredModal');
    const instance = bootstrap.Modal.getInstance(modalEl);
    if (instance) instance.hide();

    const goToIndex = () => { window.location.href = '{{ route('supervisor.recitation.index') }}'; };

    if (pendingMafsilAlertIds.length) {
        fetch('{{ route('supervisor.alerts.acknowledge') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids: pendingMafsilAlertIds }),
        }).catch(() => {}).then(goToIndex);
    } else {
        goToIndex();
    }
}

function stopSard() {
    if (isSubmittingSard) return;
    isSubmittingSard = true;
    document.getElementById('failed').value='1';
    submitSard();
}

function stopSardNow() {
    if (isSubmittingSard) return;
    isSubmittingSard = true;
    document.getElementById('failed').value='1';
    bootstrap.Modal.getInstance(document.getElementById('maxErrorsModal')).hide();
    submitSard();
}

function submitSard() {
    const form = document.getElementById('sardForm');
    const data = new FormData(form);
    for (let [key, value] of data.entries()) {
        if (key.includes('[errors]') || key.includes('[hesitations]') || key.includes('[page_number]')) {
            data.set(key, parseInt(value, 10) || 0);
        }
    }
    $.ajax({
        url:'{{ route('supervisor.recitation.save') }}',
        method:'POST',
        data:data,
        processData:false,
        contentType:false,
        success:(response)=>{
            toastr.success('تم حفظ نتيجة السرد');
            const alerts = response && response.mafsil_alerts ? response.mafsil_alerts : [];
            if (alerts.length) {
                showMafsilAlert(response);
            } else {
                setTimeout(()=>window.location.href='{{ route('supervisor.recitation.index') }}',1500);
            }
        },
        error:(xhr)=>{
            isSubmittingSard = false;
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            toastr.error(errorMsg);
        }
    });
}

function submitSardIncomplete() {
    if (!confirm('هل أنت متأكد من حفظ هذه الجلسة على أنها لم يكمل؟')) {
        return;
    }
    const form = document.getElementById('sardForm');
    const data = new FormData(form);
    for (let [key, value] of data.entries()) {
        if (key.includes('[errors]') || key.includes('[hesitations]') || key.includes('[page_number]')) {
            data.set(key, parseInt(value, 10) || 0);
        }
    }
    $.ajax({
        url:'{{ route('supervisor.recitation.saveIncomplete') }}',
        method:'POST',
        data:data,
        processData:false,
        contentType:false,
        timeout: 30000,
        success:()=>{
            toastr.success('تم حفظ الجلسة بنجاح (لم يكمل)');
            setTimeout(()=>window.location.href='{{ route('supervisor.recitation.index') }}',1500);
        },
        error:(xhr)=>{
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
