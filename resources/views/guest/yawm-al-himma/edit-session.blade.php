@extends('guest.layout')

@php
    $selectedParts = collect(old('parts', $session->parts ?? []))
        ->map(fn ($p) => (int) $p)
        ->unique()
        ->sort()
        ->values()
        ->all();
    $existingPages = collect($session->pages_data ?? [])
        ->keyBy(fn ($p) => ((int) $p['part_number']) . '_' . ((int) $p['page_number']));
@endphp

@section('title', 'يوم الهمة - تعديل جلسة #' . $session->id)

@section('content')
<form method="POST" action="{{ route('guest.yawm-al-himma.sessions.update', ['session' => $session->id]) }}" id="editSessionForm">
    @csrf
    @method('PATCH')

    <div class="page-card">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <div style="width: 52px; height: 52px; border-radius: 12px; background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fas fa-pen"></i>
            </div>
            <div class="flex-grow-1">
                <h2 style="margin: 0; color: var(--navy); font-size: 20px; font-weight: 700;">تعديل الجلسة #{{ $session->id }}</h2>
                <p style="margin: 2px 0 0; color: #6b7a90; font-size: 13px;">يمكنك تعديل بيانات الجلسة، الأجزاء، وأخطاء وترددات كل صفحة.</p>
            </div>
            <a href="{{ route('guest.yawm-al-himma.sessions.index', ['supervisor_name' => $session->supervisor_name]) }}" class="btn-outline-navy">
                <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" style="background: #fde8e6; border: 1px solid var(--danger); color: #922b1f; border-radius: var(--radius-sm); padding: 12px 14px;">
                <strong><i class="fas fa-exclamation-circle me-1"></i> يرجى تصحيح الأخطاء التالية:</strong>
                <ul class="mb-0 mt-1" style="padding-right: 18px;">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="label-text">اسم المشرف <span style="color: var(--danger);">*</span></label>
                <input type="text" name="supervisor_name" value="{{ old('supervisor_name', $session->supervisor_name) }}" class="form-control" maxlength="255" required>
                @error('supervisor_name') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="label-text">اسم الطالب <span style="color: var(--danger);">*</span></label>
                <input type="text" name="student_name" value="{{ old('student_name', $session->student_name) }}" class="form-control" maxlength="255" required>
                @error('student_name') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="label-text">تاريخ الجلسة <span style="color: var(--danger);">*</span></label>
                <input type="date" name="session_date" value="{{ old('session_date', $session->session_date->format('Y-m-d')) }}" class="form-control" required>
                @error('session_date') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="label-text">الأجزاء <span style="color: var(--danger);">*</span></label>
            <div class="row g-2 mt-1">
                @foreach($parts as $part)
                    @php $checked = in_array($part->part_number, $selectedParts, true); @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <input type="checkbox" name="parts[]" value="{{ $part->part_number }}" id="part-{{ $part->id }}" class="part-toggle" data-part="{{ $part->part_number }}" {{ $checked ? 'checked' : '' }}>
                        <label for="part-{{ $part->id }}" class="part-card">
                            <div class="num">{{ $part->part_number }}</div>
                            <div class="lbl">{{ $part->name }}</div>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('parts') <div class="text-danger mt-2" style="font-size: 13px;">{{ $message }}</div> @enderror
            @error('parts.*') <div class="text-danger mt-2" style="font-size: 13px;">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="label-text">ملاحظات</label>
            <textarea name="notes" class="form-control" rows="3" maxlength="2000" placeholder="ملاحظات اختيارية">{{ old('notes', $session->notes) }}</textarea>
            @error('notes') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
        </div>
    </div>

    <div id="pagesContainer">
        @foreach($parts as $part)
            @php
                $isSelected = in_array($part->part_number, $selectedParts, true);
                $pageRange = range($part->start_page, $part->end_page);
            @endphp
            <div class="part-section part-pages" data-part="{{ $part->part_number }}" style="{{ $isSelected ? '' : 'display:none;' }}">
                <div class="part-section-header">
                    <h3><i class="fas fa-book-open me-2"></i>الجزء {{ $part->part_number }} - {{ $part->name }}</h3>
                    <span class="pages-range">صفحة {{ $part->start_page }} إلى {{ $part->end_page }} ({{ count($pageRange) }} صفحة)</span>
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
                        @php
                            $key = $part->part_number . '_' . $pageNum;
                            $existing = $existingPages->get($key);
                            $errorsVal = old("pages.{$key}.errors", $existing['errors'] ?? 0);
                            $hesitationsVal = old("pages.{$key}.hesitations", $existing['hesitations'] ?? 0);
                        @endphp
                        <div class="page-row" data-part="{{ $part->part_number }}" data-page="{{ $pageNum }}">
                            <div class="page-number-badge">ص {{ $pageNum }}</div>
                            <div class="errors-col">
                                <div class="scoring-input-group">
                                    <button type="button" class="counter-btn minus" onclick="adj('err-{{ $key }}', -1)">-</button>
                                    <input type="number" min="0" value="{{ $errorsVal }}"
                                           id="err-{{ $key }}"
                                           name="pages[{{ $key }}][errors]"
                                           data-part="{{ $part->part_number }}" data-page="{{ $pageNum }}" data-kind="errors"
                                           oninput="recalc()">
                                    <button type="button" class="counter-btn plus" onclick="adj('err-{{ $key }}', 1)">+</button>
                                </div>
                            </div>
                            <div class="hesitations-col">
                                <div class="scoring-input-group">
                                    <button type="button" class="counter-btn minus" onclick="adj('hes-{{ $key }}', -1)">-</button>
                                    <input type="number" min="0" value="{{ $hesitationsVal }}"
                                           id="hes-{{ $key }}"
                                           name="pages[{{ $key }}][hesitations]"
                                           data-part="{{ $part->part_number }}" data-page="{{ $pageNum }}" data-kind="hesitations"
                                           oninput="recalc()">
                                    <button type="button" class="counter-btn plus" onclick="adj('hes-{{ $key }}', 1)">+</button>
                                </div>
                            </div>
                            <input type="hidden" name="pages[{{ $key }}][part_number]" value="{{ $part->part_number }}">
                            <input type="hidden" name="pages[{{ $key }}][page_number]" value="{{ $pageNum }}">
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="page-card">
        <div class="summary-box" style="margin-top: 0;">
            <div class="summary-item">
                <div class="lbl"><i class="fas fa-times-circle"></i> إجمالي الأخطاء</div>
                <div class="val" id="totalErrors">0</div>
            </div>
            <div class="summary-item">
                <div class="lbl"><i class="fas fa-question-circle"></i> إجمالي الترددات</div>
                <div class="val" id="totalHes">0</div>
            </div>
            <div class="summary-item">
                <div class="lbl"><i class="fas fa-list"></i> عدد الصفحات</div>
                <div class="val" id="totalPages">0</div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end flex-wrap mt-4">
            <a href="{{ route('guest.yawm-al-himma.sessions.index', ['supervisor_name' => $session->supervisor_name]) }}" class="btn-outline-navy">
                إلغاء
            </a>
            <button type="submit" class="btn-gold">
                <i class="fas fa-save me-1"></i> حفظ التعديلات
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function adj(id, delta) {
    var el = document.getElementById(id);
    if (!el) return;
    var v = parseInt(el.value || 0) + delta;
    if (v < 0) v = 0;
    el.value = v;
    recalc();
}

function recalc() {
    var totalErr = 0, totalHes = 0, totalPages = 0;
    document.querySelectorAll('.part-pages').forEach(function (section) {
        if (section.style.display === 'none') return;
        section.querySelectorAll('input[data-kind="errors"]').forEach(function (inp) {
            var part = inp.dataset.part;
            var page = inp.dataset.page;
            var hesInp = section.querySelector('input[data-part="' + part + '"][data-page="' + page + '"][data-kind="hesitations"]');
            totalErr += parseInt(inp.value) || 0;
            totalHes += hesInp ? (parseInt(hesInp.value) || 0) : 0;
            totalPages += 1;
        });
    });
    document.getElementById('totalErrors').textContent = totalErr;
    document.getElementById('totalHes').textContent = totalHes;
    document.getElementById('totalPages').textContent = totalPages;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.part-toggle').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var partNum = cb.dataset.part;
            var section = document.querySelector('.part-pages[data-part="' + partNum + '"]');
            if (!section) return;
            section.style.display = cb.checked ? '' : 'none';
            recalc();
        });
    });
    recalc();
});
</script>
@endpush
