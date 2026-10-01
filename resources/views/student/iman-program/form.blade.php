@extends('layouts.app')

@php
    $oldCriteria = old('criteria');
    $items = $oldCriteria !== null
        ? collect($oldCriteria)
        : ($criteria->isNotEmpty() ? $criteria : collect([['name' => '', 'type' => 'check', 'target_value' => null]]));
@endphp

@section('title', $program ? 'تعديل البرنامج الإيماني' : 'برنامج إيماني جديد')
@section('page-title', $program ? 'تعديل البرنامج الإيماني' : 'برنامج إيماني جديد')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.iman-form-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --danger: #c0392b;
    --radius: 14px;
    --shadow-sm: 0 2px 10px rgba(24,43,72,0.05);
}

.iman-form-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    padding: 16px 22px;
    margin-bottom: 16px;
    color: #fff;
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
}
.iman-form-page .page-hero h1 { margin: 0; font-size: 18px; font-weight: 700; }
.iman-form-page .page-hero p { color: rgba(255,255,255,0.8); margin: 0; font-size: 12.5px; }
.iman-form-page .hero-icon {
    width: 48px; height: 48px; border-radius: 13px;
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px;
    flex-shrink: 0;
}
.iman-form-page .form-card {
    background: #fff; border-radius: var(--radius);
    box-shadow: var(--shadow-sm); padding: 16px; margin-bottom: 16px;
    border: 1px solid rgba(24,43,72,0.05);
}
.iman-form-page .form-card h5 { color: var(--navy); font-weight: 800; margin-bottom: 12px; }
.iman-form-page .form-card h5 i { color: var(--gold); margin-inline-end: 8px; }
.iman-form-page .form-card p.help { color: #6b7a90; font-size: 12.5px; margin-bottom: 14px; }

.iman-form-page .criterion-row {
    display: grid; grid-template-columns: 1fr 180px 200px 50px;
    gap: 10px; align-items: center; margin-bottom: 10px;
    padding: 10px; border-radius: 12px; background: #f8f9fc;
}
.iman-form-page .criterion-row .form-control { min-width: 0; }
@media (max-width: 768px) {
    .iman-form-page .criterion-row { grid-template-columns: 1fr auto; gap: 10px; padding: 12px; }
    .iman-form-page .criterion-row > input[type="text"] { grid-column: 1 / -1; width: 100%; font-size: 16px; padding: 12px 14px; }
    .iman-form-page .criterion-row > .target-input { grid-column: 1 / -1; width: 100%; font-size: 16px; padding: 12px 14px; }
    .iman-form-page .criterion-row .type-select { grid-column: 1 / 2; width: 100%; flex-wrap: wrap; }
    .iman-form-page .criterion-row .type-select label { flex: 1 1 0; justify-content: center; padding: 10px 8px; }
    .iman-form-page .criterion-row .remove-btn { grid-column: 2 / 3; height: 42px; align-self: center; }
}
.iman-form-page .criterion-row .remove-btn {
    background: rgba(192,57,43,0.1); color: var(--danger); border: none;
    width: 30px; height: 30px; border-radius: 8px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.iman-form-page .criterion-row .remove-btn:hover { background: var(--danger); color: #fff; }
.iman-form-page .type-select {
    display: flex; gap: 8px; align-items: center;
}
.iman-form-page .type-select label {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border: 1.5px solid #d6dbe5; border-radius: 8px;
    cursor: pointer; font-size: 12.5px; font-weight: 600; color: var(--navy);
}
.iman-form-page .type-select input { display: none; }
.iman-form-page .type-select input:checked + span { color: var(--gold); }
.iman-form-page .type-select label:has(input:checked) {
    background: rgba(212,160,48,0.1); border-color: var(--gold);
}

.iman-form-page .add-criterion-btn {
    background: rgba(24,43,72,0.06); color: var(--navy);
    border: 2px dashed rgba(24,43,72,0.2); padding: 8px 16px;
    border-radius: 9px; font-weight: 700; font-size: 13px; width: 100%; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.iman-form-page .add-criterion-btn:hover { background: var(--navy); color: #fff; border-color: var(--navy); }

.iman-form-page .form-actions {
    display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px;
    flex-wrap: wrap;
}
@media (max-width: 576px) {
    .iman-form-page .form-actions { justify-content: stretch; }
    .iman-form-page .form-actions .btn-gold,
    .iman-form-page .form-actions .btn-cancel { flex: 1; text-align: center; }
    .iman-form-page .form-card { padding: 16px; }
    .iman-form-page .page-hero { padding: 14px 16px; }
}
.iman-form-page .btn-gold {
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: #fff; border: none; padding: 8px 16px; border-radius: 9px;
    font-weight: 700; font-size: 13px; cursor: pointer;
}
.iman-form-page .btn-gold:hover { transform: translateY(-1px); }
.iman-form-page .btn-cancel {
    background: #f0f4f8; color: var(--navy); border: none;
    padding: 8px 16px; border-radius: 9px; font-weight: 700; font-size: 13px; text-decoration: none;
}
.iman-form-page .btn-cancel:hover { background: #dee5ed; }
</style>
@endpush

@section('content')
<div class="iman-form-page">
    <x-page-hero icon="fas fa-seedling">
        <h1 class="page-banner-title">{{ $program ? 'تعديل البرنامج الإيماني' : 'برنامج إيماني جديد' }}</h1>
        <p class="page-banner-sub">حدد اسم البرنامج ومعايير الالتزام اليومي (علامة ✅ أو قيمة عددية)</p>
        <x-slot:actions>
            <a href="{{ route('student.iman-program.index') }}" class="btn-cancel"><i class="fas fa-arrow-right"></i> رجوع</a>
        </x-slot:actions>
    </x-page-hero>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $program ? route('student.iman-program.update', $program) : route('student.iman-program.store') }}">
        @csrf
        @if($program) @method('PUT') @endif

        <div class="form-card">
            <h5><i class="fas fa-circle-info"></i> بيانات البرنامج الأساسية</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">اسم البرنامج <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="255"
                           value="{{ old('name', $program->name ?? '') }}"
                           placeholder="مثال: برنامج رمضان الإيماني">
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ البدء</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ old('start_date', isset($program) && $program->start_date ? $program->start_date->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $program->is_active ?? 1) ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ old('is_active', $program->is_active ?? 1) == 0 ? 'selected' : '' }}>مؤرشف</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="2" maxlength="2000"
                              placeholder="وصف مختصر للبرنامج (اختياري)">{{ old('description', $program->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h5><i class="fas fa-list-check"></i> معايير البرنامج</h5>
            <p class="help">أضف المعايير التي تريد الالتزام بها يومياً. اختر نوع المعيار:
                <strong>علامة</strong> لتسجيل تم/لم يتم، أو <strong>قيمة عددية</strong> لتحديد عدد أو مقدار.</p>

            <div id="criteria-list">
                @foreach($items as $i => $row)
                    @php
                        $rowId = $row['id'] ?? null;
                        $rowName = is_array($row) ? ($row['name'] ?? '') : $row->name;
                        $rowType = is_array($row) ? ($row['type'] ?? 'check') : $row->type;
                        $rowTarget = is_array($row) ? ($row['target_value'] ?? null) : $row->target_value;
                    @endphp
                    <div class="criterion-row" data-idx="{{ $i }}">
                        <input type="hidden" name="criteria[{{ $i }}][id]" value="{{ $rowId }}">
                        <input type="text" name="criteria[{{ $i }}][name]" class="form-control" required maxlength="255"
                               placeholder="اسم المعيار (مثال: صلاة الفجر)"
                               value="{{ $rowName }}">
                        <div class="type-select">
                            <label>
                                <input type="radio" name="criteria[{{ $i }}][type]" value="check" {{ $rowType === 'check' ? 'checked' : '' }}>
                                <span><i class="fas fa-check"></i> علامة</span>
                            </label>
                            <label>
                                <input type="radio" name="criteria[{{ $i }}][type]" value="number" {{ $rowType === 'number' ? 'checked' : '' }}>
                                <span><i class="fas fa-hashtag"></i> رقم</span>
                            </label>
                        </div>
                        <input type="number" min="0" name="criteria[{{ $i }}][target_value]" class="form-control target-input"
                               placeholder="الهدف اليومي" value="{{ $rowTarget }}"
                               style="{{ $rowType === 'number' ? '' : 'display:none;' }}">
                        <button type="button" class="remove-btn" title="حذف"><i class="fas fa-times"></i></button>
                    </div>
                @endforeach
            </div>

            <button type="button" class="add-criterion-btn" id="add-criterion">
                <i class="fas fa-plus"></i> إضافة معيار جديد
            </button>
        </div>

        <div class="form-actions">
            <a href="{{ route('student.iman-program.index') }}" class="btn-cancel">إلغاء</a>
            <button type="submit" class="btn-gold">
                <i class="fas fa-save"></i> {{ $program ? 'حفظ التغييرات' : 'إنشاء البرنامج' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let idx = {{ $items->count() }};
const list = document.getElementById('criteria-list');

document.getElementById('add-criterion').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'criterion-row';
    row.dataset.idx = idx;
    row.innerHTML = `
        <input type="hidden" name="criteria[${idx}][id]" value="">
        <input type="text" name="criteria[${idx}][name]" class="form-control" required maxlength="255" placeholder="اسم المعيار">
        <div class="type-select">
            <label>
                <input type="radio" name="criteria[${idx}][type]" value="check" checked>
                <span><i class="fas fa-check"></i> علامة</span>
            </label>
            <label>
                <input type="radio" name="criteria[${idx}][type]" value="number">
                <span><i class="fas fa-hashtag"></i> رقم</span>
            </label>
        </div>
        <input type="number" min="0" name="criteria[${idx}][target_value]" class="form-control target-input" placeholder="الهدف اليومي" style="display:none;">
        <button type="button" class="remove-btn" title="حذف"><i class="fas fa-times"></i></button>
    `;
    list.appendChild(row);
    attachRowEvents(row);
    idx++;
});

list.addEventListener('click', function (e) {
    if (e.target.closest('.remove-btn')) {
        const row = e.target.closest('.criterion-row');
        if (list.children.length > 1) {
            row.remove();
        } else {
            row.querySelector('input[type="text"]').value = '';
        }
    }
});

function attachRowEvents(row) {
    const radios = row.querySelectorAll('input[type="radio"][name$="[type]"]');
    const targetInput = row.querySelector('.target-input');
    radios.forEach(r => {
        r.addEventListener('change', function () {
            if (this.value === 'number') {
                targetInput.style.display = '';
            } else {
                targetInput.style.display = 'none';
                targetInput.value = '';
            }
        });
    });
}

document.querySelectorAll('.criterion-row').forEach(attachRowEvents);
</script>
@endpush
