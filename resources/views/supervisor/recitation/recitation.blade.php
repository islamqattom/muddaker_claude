@extends('layouts.app')
@section('title','تسميع - سرد')
@section('page-title','جلسة سرد - الجزء '.$part->part_number)
@section('sidebar-menu') @include('supervisor.partials.sidebar') @endsection

@push('styles')
<style>
.recitation-sard {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.recitation-sard .page-card {
    border: 1px solid rgba(24, 43, 72, 0.08);
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.recitation-sard .page-card:hover {
    border-color: var(--gold);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.recitation-sard .page-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
    padding: 8px 12px;
}

.recitation-sard .result-indicator {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.recitation-sard .color-green { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; }
.recitation-sard .color-orange { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
.recitation-sard .color-red { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
.recitation-sard .color-black { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; }
.recitation-sard .color-gray { background: linear-gradient(135deg, #ecf0f1, #d5dbdb); color: #7f8c8d; }

.recitation-sard .input-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    padding: 0;
}

.recitation-sard .input-btn:hover {
    transform: scale(1.05);
}

.recitation-sard .counter-input {
    width: 44px;
    height: 32px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    border: 1px solid #e9ecef;
}

.recitation-sard .counter-input:focus {
    border-color: var(--navy);
}

.recitation-sard .student-header-card {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 20px;
    position: relative;
}

.recitation-sard .student-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.recitation-sard .student-header-content {
    position: relative;
    z-index: 1;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.recitation-sard .student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.recitation-sard .student-avatar {
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

.recitation-sard .student-name {
    color: white;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.recitation-sard .student-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.recitation-sard .summary-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.recitation-sard .summary-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
}

.recitation-sard .summary-stat {
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    transition: all 0.3s ease;
}

.recitation-sard .summary-stat:hover {
    transform: translateY(-2px);
}

.recitation-sard .summary-stat .stat-value {
    font-size: 22px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}

.recitation-sard .summary-stat .stat-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.recitation-sard .overall-result {
    padding: 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    margin-top: 12px;
}

.recitation-sard .save-btn {
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

.recitation-sard .save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.4);
}

.recitation-sard .btn-outline-secondary {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.recitation-sard .btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #ced4da;
}

.recitation-sard .btn-stop {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
}

.recitation-sard .btn-stop:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}

.recitation-sard .islamic-border {
    position: relative;
}

.recitation-sard .islamic-border::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.recitation-sard .quran-verse-header {
    font-family: 'Amiri', serif;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
    text-align: center;
    padding: 6px 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212, 160, 48, 0.2);
}

.recitation-sard .compact-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 6px;
}

.recitation-sard .error-counter-badge {
    font-size: 13px;
    padding: 8px 16px;
    border-radius: 10px;
}

.recitation-sard .info-alert {
    border-radius: 12px;
    border: none;
    padding: 14px 18px;
}

@media (max-width: 768px) {
    .recitation-sard .student-header-content {
        flex-direction: column;
        text-align: center;
    }

    .recitation-sard .student-info {
        flex-direction: column;
    }

    .recitation-sard .student-name {
        font-size: 16px;
    }

    .recitation-sard .summary-stat .stat-value {
        font-size: 18px;
    }

    .recitation-sard .result-indicator {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}
</style>
@endpush

@section('content')
<div class="recitation-sard">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="alert alert-info info-alert d-flex gap-3 align-items-start">
                <i class="fas fa-info-circle fa-lg mt-1"></i>
                <div>
                    <strong>السرد:</strong> تسميع الجزء {{ $part->part_number }} كاملاً ({{ count($pages) }} صفحة)
                    <br>
                    <span class="text-danger">في حال وصل الطالب لـ <strong>6 أخطاء</strong> يظهر تنبيه للإكمال أو التوقف</span>
                </div>
            </div>

            <div class="student-header-card islamic-border">
                <div class="student-header-content">
                    <div class="student-info">
                        <div class="student-avatar">
                            <i class="fas fa-user"></i>
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
                        <span class="badge bg-white text-primary compact-badge px-3">
                            <i class="fas fa-book-open me-1"></i>سرد
                        </span>
                        <div class="mt-2">
                            <span class="badge bg-danger error-counter-badge" id="error-counter">
                                <i class="fas fa-times-circle me-1"></i>
                                الأخطاء: 0 / 6
                            </span>
                        </div>
                    </div>
                </div>
                <div class="quran-verse-header">
                    <i class="fas fa-book-quran me-2"></i>
                    {{ 'وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا' }}
                </div>
            </div>

            <form id="sardForm">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="type" value="recitation">
                <input type="hidden" name="part_id" value="{{ $part->id }}">
                <input type="hidden" name="session_date" value="{{ request('session_date', date('Y-m-d')) }}">
                <input type="hidden" name="failed" id="failed" value="0">

                <div class="row g-2 mb-2">
                    @foreach($pages as $index => $pageNum)
                    <div class="col-md-4 col-lg-3">
                        <div class="card page-card islamic-border" id="page-{{ $index }}" style="margin-bottom: 8px;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="result-indicator color-gray" id="color-indicator-{{ $index }}">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold" style="font-size:13px;">
                                            <i class="fas fa-file-alt me-1 text-primary"></i>
                                            ص {{ $pageNum }}
                                        </h6>
                                    </div>
                                </div>
                                <span class="badge bg-secondary compact-badge" id="status-badge-{{ $index }}">—</span>
                            </div>
                            <div class="card-body p-2">
                                <input type="hidden" name="pages[{{ $index }}][page_number]" value="{{ $pageNum }}">
                                <div class="row g-1 align-items-end">
                                    <div class="col-6">
                                        <label class="form-label fw-bold text-danger mb-0" style="font-size:9px;">
                                            <i class="fas fa-times-circle me-1"></i>أخطاء
                                        </label>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-outline-danger input-btn" onclick="adjustSardValue('e-{{ $index }}', -1, {{ $index }})">
                                                <i class="fas fa-minus" style="font-size:10px;"></i>
                                            </button>
                                            <input type="number" name="pages[{{ $index }}][errors]" id="e-{{ $index }}"
                                                   class="form-control form-control-sm text-center fw-bold" value="0" min="0"
                                                   style="width: 44px; height: 32px; font-size: 13px; padding: 4px;"
                                                   oninput="updateSardColor({{ $index }})">
                                            <button type="button" class="btn btn-outline-danger input-btn" onclick="adjustSardValue('e-{{ $index }}', 1, {{ $index }})">
                                                <i class="fas fa-plus" style="font-size:10px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold text-warning mb-0" style="font-size:9px;">
                                            <i class="fas fa-question-circle me-1"></i>ترددات
                                        </label>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-outline-warning input-btn" onclick="adjustSardValue('h-{{ $index }}', -1, {{ $index }})">
                                                <i class="fas fa-minus" style="font-size:10px;"></i>
                                            </button>
                                            <input type="number" name="pages[{{ $index }}][hesitations]" id="h-{{ $index }}"
                                                   class="form-control form-control-sm text-center fw-bold" value="0" min="0"
                                                   style="width: 44px; height: 32px; font-size: 13px; padding: 4px;"
                                                   oninput="updateSardColor({{ $index }})">
                                            <button type="button" class="btn btn-outline-warning input-btn" onclick="adjustSardValue('h-{{ $index }}', 1, {{ $index }})">
                                                <i class="fas fa-plus" style="font-size:10px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
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
                        <button type="button" class="btn-stop" onclick="stopSard()">
                            <i class="fas fa-stop me-2"></i>إيقاف مبكر
                        </button>
                        <button type="button" class="btn btn-secondary px-3" style="border-radius: 10px; font-size: 13px;" onclick="submitSardIncomplete()">
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
    </div>
</div>

<!-- Max Errors Modal -->
<div class="modal fade" id="maxErrorsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-danger text-white" style="padding: 14px 20px;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تحذير - وصل للحد الأقصى من الأخطاء</h5>
            </div>
            <div class="modal-body text-center" style="padding: 24px;">
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
@endsection

@push('scripts')
<script>
let totalErrors = 0;
let maxErrorsShown = false;
const pageCount = {{ count($pages) }};
const colors = { green:'#27ae60', orange:'#f39c12', red:'#e74c3c', black:'#2c3e50', gray:'#ecf0f1' };

function adjustSardValue(id, delta, index) {
    const input = document.getElementById(id);
    let val = parseInt(input.value) + delta;
    val = Math.max(parseInt(input.min), Math.min(999, val));
    input.value = val;
    updateSardColor(index);
}

function updateSardColor(index) {
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
    } else if (totalEquivalent >= 3) {
        colorClass = 'color-black'; label = 'راسب'; icon = 'fa-ban';
    } else {
        colorClass = 'color-gray'; label = '—'; icon = 'fa-hourglass-half';
    }

    const indicator = document.getElementById('color-indicator-' + index);
    indicator.className = 'result-indicator ' + colorClass;
    indicator.innerHTML = '<i class="fas ' + icon + '"></i>';

    const badge = document.getElementById('status-badge-' + index);
    const bgColor = colorClass === 'color-orange' ? '#f39c12' : colorClass === 'color-green' ? '#27ae60' : colorClass === 'color-red' ? '#e74c3c' : colorClass === 'color-black' ? '#2c3e50' : '#6c757d';
    badge.className = 'badge compact-badge';
    badge.style.background = bgColor;
    badge.style.color = 'white';
    badge.textContent = label;

    document.getElementById('page-'+index).style.borderColor = bgColor;
    document.getElementById('page-'+index).style.background = bgColor + '11';

    updateTotalErrors();
}

function updateTotalErrors() {
    let total = 0;
    for(let i=0;i<pageCount;i++) {
        const errors = parseInt(document.getElementById('e-'+i)?.value)||0;
        const hesitations = parseInt(document.getElementById('h-'+i)?.value)||0;
        total += errors + Math.floor(hesitations / 3);
    }
    totalErrors = total;
    const counter = document.getElementById('error-counter');
    counter.innerHTML = '<i class="fas fa-times-circle me-1"></i>الأخطاء: ' + total + ' / 6';
    counter.className = total >= 6 ? 'badge bg-danger error-counter-badge' : 'badge bg-danger error-counter-badge';
    if(total >= 6 && !maxErrorsShown) { maxErrorsShown=true; new bootstrap.Modal(document.getElementById('maxErrorsModal')).show(); }
}

let isSubmittingSard = false;

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
        success:()=>{
            toastr.success('تم حفظ نتيجة السرد');
            setTimeout(()=>window.location.href='{{ route('supervisor.recitation.index') }}',1500);
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