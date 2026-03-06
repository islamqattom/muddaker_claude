@extends('layouts.app')
@section('title','تسميع - سرد')
@section('page-title','جلسة سرد - الجزء '.$part->part_number)
@section('sidebar-menu') @include('supervisor.partials.sidebar') @endsection
@section('content')
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>السرد:</strong> تسميع الجزء {{ $part->part_number }} كاملاً ({{ count($pages) }} صفحة)
    | في حال وصل الطالب لـ <strong>6 أخطاء</strong> يظهر تنبيه للإكمال أو التوقف
</div>
<div class="card mb-3">
    <div class="card-body d-flex align-items-center gap-3">
        <div style="width:50px;height:50px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:20px;">
            {{ mb_substr($student->user->name,0,1) }}
        </div>
        <div>
            <h5 class="mb-0">{{ $student->user->name }}</h5>
            <span class="badge bg-info text-dark">سرد | الجزء {{ $part->part_number }}</span>
            <span class="badge bg-secondary ms-1" id="error-counter">الأخطاء: 0 / 6</span>
        </div>
    </div>
</div>
<form id="sardForm">
    @csrf
    <input type="hidden" name="student_id" value="{{ $student->id }}">
    <input type="hidden" name="type" value="recitation">
    <input type="hidden" name="part_id" value="{{ $part->id }}">
    <input type="hidden" name="session_date" value="{{ date('Y-m-d') }}">
    <input type="hidden" name="failed" id="failedFlag" value="0">
    <div class="row g-2">
        @foreach($pages as $index => $pageNum)
        <div class="col-md-4 col-lg-3">
            <div class="card page-card" id="page-{{ $index }}">
                <div class="card-body p-2">
                    <div class="text-center mb-2 fw-bold" style="font-size:12px;">ص {{ $pageNum }}</div>
                    <input type="hidden" name="pages[{{ $index }}][page_number]" value="{{ $pageNum }}">
                    <div class="row g-1">
                        <div class="col-6">
                            <label style="font-size:10px;" class="d-block text-center text-danger">أخطاء</label>
                            <input type="number" name="pages[{{ $index }}][errors]" id="e-{{ $index }}"
                                   class="form-control form-control-sm text-center" value="0" min="0"
                                   oninput="updateSardColor({{ $index }})">
                        </div>
                        <div class="col-6">
                            <label style="font-size:10px;" class="d-block text-center text-warning">ترددات</label>
                            <input type="number" name="pages[{{ $index }}][hesitations]" id="h-{{ $index }}"
                                   class="form-control form-control-sm text-center" value="0" min="0"
                                   oninput="updateSardColor({{ $index }})">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="d-flex gap-3 mt-4">
        <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-right me-2"></i>رجوع</a>
        <button type="button" class="btn btn-warning btn-lg" onclick="stopSard()"><i class="fas fa-stop me-2"></i>إيقاف مبكر</button>
        <button type="button" class="btn btn-primary btn-lg flex-fill" onclick="submitSard()"><i class="fas fa-save me-2"></i>حفظ نتيجة السرد</button>
    </div>
</form>
<!-- Max Errors Modal -->
<div class="modal fade" id="maxErrorsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">⚠️ تحذير - وصل للحد الأقصى من الأخطاء</h5></div>
            <div class="modal-body text-center"><p class="fs-5">وصل الطالب إلى <strong>6 أخطاء</strong> في السرد</p><p>هل تريد الإكمال أم الإيقاف؟</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="stopSardNow()"><i class="fas fa-stop me-2"></i>إيقاف</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="fas fa-play me-2"></i>إكمال</button>
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
const colors = { green:'#27ae60', orange:'#f39c12', red:'#e74c3c', black:'#2c3e50' };

function updateSardColor(index) {
    const errors = parseInt(document.getElementById('e-'+index).value)||0;
    // For recitation (sard), hesitations are ignored - only errors count
    let color;
    if(errors === 0) color='green';
    else if(errors === 1) color='orange';
    else if(errors === 2) color='red';
    else color='black';
    document.getElementById('page-'+index).style.borderColor = colors[color];
    document.getElementById('page-'+index).style.background = colors[color]+'22';
    updateTotalErrors();
}

function updateTotalErrors() {
    let total = 0;
    for(let i=0;i<pageCount;i++) {
        const errors = parseInt(document.getElementById('e-'+i)?.value)||0;
        const hesitations = parseInt(document.getElementById('h-'+i)?.value)||0;
        // For recitation (sard): 3 hesitations = 1 error
        total += errors + Math.floor(hesitations / 3);
    }
    totalErrors = total;
    const counter = document.getElementById('error-counter');
    counter.textContent = `الأخطاء: ${total} / 6`;
    counter.className = total >= 6 ? 'badge bg-danger ms-1' : 'badge bg-secondary ms-1';
    if(total >= 6 && !maxErrorsShown) { maxErrorsShown=true; new bootstrap.Modal(document.getElementById('maxErrorsModal')).show(); }
}

function stopSard() { document.getElementById('failedFlag').value='1'; submitSard(); }
function stopSardNow() { document.getElementById('failedFlag').value='1'; bootstrap.Modal.getInstance(document.getElementById('maxErrorsModal')).hide(); submitSard(); }

function submitSard() {
    const data = new FormData(document.getElementById('sardForm'));
    $.ajax({ url:'{{ route('supervisor.recitation.save') }}', method:'POST', data:data, processData:false, contentType:false,
        success:()=>{ toastr.success('تم حفظ نتيجة السرد'); setTimeout(()=>window.location.href='{{ route('supervisor.recitation.index') }}',1500); },
        error:()=>{ window.location.href='{{ route('supervisor.recitation.index') }}'; }
    });
}
</script>
@endpush
