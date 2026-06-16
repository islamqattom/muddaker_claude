@extends('guest.layout', ['step' => 3])

@section('title', 'يوم الهمة - تقييم الصفحات')

@section('content')
<form method="POST" action="{{ route('guest.yawm-al-himma.save') }}" id="evalForm">
    @csrf
    <input type="hidden" name="supervisor_name" value="{{ $supervisorName }}">
    <input type="hidden" name="student_name" value="{{ $studentName }}">
    @if($studentId)<input type="hidden" name="student_id" value="{{ $studentId }}">@endif
    <input type="hidden" name="session_date" value="{{ date('Y-m-d') }}">

    @php $selectedParts = $parts->pluck('part_number')->toArray(); @endphp
    @foreach($selectedParts as $p)
        <input type="hidden" name="parts[]" value="{{ $p }}">
    @endforeach

    <div class="page-card">
        <h2><i class="fas fa-clipboard-check me-2" style="color: var(--gold);"></i>تسجيل الأخطاء والترددات</h2>

        <div class="alert alert-gold">
            <i class="fas fa-user-tie me-1"></i> المشرف: <strong>{{ $supervisorName }}</strong>
            <span class="mx-2">|</span>
            <i class="fas fa-user-graduate me-1"></i> الطالب: <strong>{{ $studentName }}</strong>
            <span class="mx-2">|</span>
            <i class="fas fa-calendar me-1"></i> التاريخ: <strong>{{ date('Y-m-d') }}</strong>
        </div>
    </div>

    @foreach($parts as $part)
    @php
        $startPage = $part->start_page;
        $endPage = $part->end_page;
        $pageRange = range($startPage, $endPage);
    @endphp
    <div class="part-section">
        <div class="part-section-header">
            <h3><i class="fas fa-book-open me-2"></i>الجزء {{ $part->part_number }} - {{ $part->name }}</h3>
            <span class="pages-range">صفحة {{ $startPage }} إلى {{ $endPage }} ({{ count($pageRange) }} صفحة)</span>
        </div>
        <div class="part-section-body">

        <div class="page-row header">
            <div>الصفحة</div>
            <div><i class="fas fa-times-circle" style="color: var(--danger);"></i> الأخطاء</div>
            <div><i class="fas fa-question-circle" style="color: #b36b00;"></i> الترددات</div>
        </div>

        <div class="mobile-legend">
            <div><i class="fas fa-times-circle" style="color: var(--danger);"></i> الأخطاء</div>
            <div><i class="fas fa-question-circle" style="color: #b36b00;"></i> الترددات</div>
        </div>

        @foreach($pageRange as $pageNum)
        <div class="page-row" data-part="{{ $part->part_number }}" data-page="{{ $pageNum }}">
            <div class="page-number-badge">ص {{ $pageNum }}</div>
            <div class="errors-col">
                <div class="scoring-input-group">
                    <button type="button" class="counter-btn minus"
                            onclick="adj('err-{{ $part->part_number }}-{{ $pageNum }}', -1)">-</button>
                    <input type="number" min="0" value="0"
                           id="err-{{ $part->part_number }}-{{ $pageNum }}"
                           name="pages[{{ $part->part_number }}_{{ $pageNum }}][errors]"
                           data-part="{{ $part->part_number }}" data-page="{{ $pageNum }}" data-kind="errors"
                           oninput="recalc()">
                    <button type="button" class="counter-btn plus"
                            onclick="adj('err-{{ $part->part_number }}-{{ $pageNum }}', 1)">+</button>
                </div>
            </div>
            <div class="hesitations-col">
                <div class="scoring-input-group">
                    <button type="button" class="counter-btn minus"
                            onclick="adj('hes-{{ $part->part_number }}-{{ $pageNum }}', -1)">-</button>
                    <input type="number" min="0" value="0"
                           id="hes-{{ $part->part_number }}-{{ $pageNum }}"
                           name="pages[{{ $part->part_number }}_{{ $pageNum }}][hesitations]"
                           data-part="{{ $part->part_number }}" data-page="{{ $pageNum }}" data-kind="hesitations"
                           oninput="recalc()">
                    <button type="button" class="counter-btn plus"
                            onclick="adj('hes-{{ $part->part_number }}-{{ $pageNum }}', 1)">+</button>
                </div>
            </div>
            <input type="hidden" name="pages[{{ $part->part_number }}_{{ $pageNum }}][part_number]" value="{{ $part->part_number }}">
            <input type="hidden" name="pages[{{ $part->part_number }}_{{ $pageNum }}][page_number]" value="{{ $pageNum }}">
        </div>
        @endforeach
        </div>
    </div>
    @endforeach

    <div class="page-card">
        <h2><i class="fas fa-sticky-note me-2" style="color: var(--gold);"></i>ملاحظات (اختياري)</h2>
        <textarea name="notes" class="form-control" rows="3" placeholder="أي ملاحظات إضافية..."></textarea>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="lbl"><i class="fas fa-times-circle"></i> إجمالي الأخطاء</div>
            <div class="val" id="totalErrors">0</div>
        </div>
        <div class="summary-item">
            <div class="lbl"><i class="fas fa-question-circle"></i> إجمالي الترددات</div>
            <div class="val" id="totalHes">0</div>
        </div>
    </div>

    <div class="d-flex justify-content-between gap-2 flex-wrap mt-4">
        <a href="{{ route('guest.yawm-al-himma.index') }}" class="btn-outline-navy">
            <i class="fas fa-arrow-right me-1"></i> إلغاء
        </a>
        <button type="submit" class="btn-gold">
            <i class="fas fa-save me-1"></i> حفظ التقييم
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function adj(id, delta) {
    const el = document.getElementById(id);
    if (!el) return;
    let v = parseInt(el.value || 0) + delta;
    if (v < 0) v = 0;
    el.value = v;
    recalc();
}

function recalc() {
    let totalErr = 0, totalHes = 0;
    document.querySelectorAll('input[data-kind="errors"]').forEach(inp => {
        const part = inp.dataset.part;
        const page = inp.dataset.page;
        const hes = parseInt(document.querySelector(`input[data-part="${part}"][data-page="${page}"][data-kind="hesitations"]`).value) || 0;
        const err = parseInt(inp.value) || 0;
        totalErr += err;
        totalHes += hes;
    });
    document.getElementById('totalErrors').textContent = totalErr;
    document.getElementById('totalHes').textContent = totalHes;
}
recalc();
</script>
@endpush
