@extends('layouts.app')

@section('title', 'إدارة معايير برنامج الطالب')
@section('page-title', 'إدارة معايير برنامج الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.manage-criteria-page { --gold:#d4a030; --gold-light:#e6b84d; --navy:#182B48; --navy-light:#2A4570; --success:#27ae60; --danger:#c0392b; --radius:16px; --shadow-sm:0 2px 10px rgba(24,43,72,0.05); }
.manage-criteria-page .page-hero { background:linear-gradient(135deg,var(--navy) 0%,var(--navy-light) 100%); border-radius:var(--radius); padding:24px 30px; margin-bottom:24px; color:#fff; display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
.manage-criteria-page .page-hero h1 { margin:0; font-size:22px; font-weight:700; }
.manage-criteria-page .page-hero p { color:rgba(255,255,255,0.8); margin:0; font-size:13px; }
.manage-criteria-page .hero-icon { width:56px; height:56px; border-radius:14px; background:linear-gradient(135deg,var(--gold),var(--gold-light)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:24px; }
.manage-criteria-page .student-badge { background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); padding:6px 14px; border-radius:20px; font-size:13px; display:inline-flex; align-items:center; gap:6px; margin-top:6px; }
.manage-criteria-page .form-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow-sm); padding:26px; margin-bottom:22px; border:1px solid rgba(24,43,72,0.05); }
.manage-criteria-page .form-card h5 { color:var(--navy); font-weight:800; margin-bottom:16px; }
.manage-criteria-page .form-card h5 i { color:var(--gold); margin-inline-end:8px; }
.manage-criteria-page .form-card p.help { color:#6b7a90; font-size:13px; margin-bottom:18px; }
.manage-criteria-page .criterion-row { display:grid; grid-template-columns:1fr 180px 200px 50px; gap:10px; align-items:center; margin-bottom:10px; padding:10px; border-radius:12px; background:#f8f9fc; }
.manage-criteria-page .criterion-row .remove-btn { background:rgba(192,57,43,0.1); color:var(--danger); border:none; width:40px; height:40px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.manage-criteria-page .criterion-row .remove-btn:hover { background:var(--danger); color:#fff; }
.manage-criteria-page .type-select { display:flex; gap:8px; align-items:center; }
.manage-criteria-page .type-select label { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border:1.5px solid #d6dbe5; border-radius:10px; cursor:pointer; font-size:13px; font-weight:600; color:var(--navy); }
.manage-criteria-page .type-select input { display:none; }
.manage-criteria-page .type-select label:has(input:checked) { background:rgba(212,160,48,0.1); border-color:var(--gold); }
.manage-criteria-page .add-criterion-btn { background:rgba(24,43,72,0.06); color:var(--navy); border:2px dashed rgba(24,43,72,0.2); padding:12px 20px; border-radius:12px; font-weight:700; width:100%; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
.manage-criteria-page .add-criterion-btn:hover { background:var(--navy); color:#fff; border-color:var(--navy); }
.manage-criteria-page .form-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:8px; }
.manage-criteria-page .btn-gold { background:linear-gradient(135deg,var(--gold),var(--gold-light)); color:#fff; border:none; padding:12px 28px; border-radius:12px; font-weight:700; cursor:pointer; }
.manage-criteria-page .btn-gold:hover { transform:translateY(-1px); }
.manage-criteria-page .btn-cancel { background:#f0f4f8; color:var(--navy); border:none; padding:12px 22px; border-radius:12px; font-weight:700; text-decoration:none; }
.manage-criteria-page .btn-cancel:hover { background:#dee5ed; }
</style>
@endpush

@section('content')
<div class="manage-criteria-page">
    <div class="page-hero">
        <div class="hero-icon"><i class="fas fa-user-graduate"></i></div>
        <div>
            <h1>إدارة معايير برنامج الطالب</h1>
            <p>أضف أو عدّل المعايير الخاصة ببرنامج الطالب</p>
            <div class="student-badge">
                <i class="fas fa-user"></i>
                الطالب: {{ $student->user->name }}
                @if($student->student_number) ({{ $student->student_number }}) @endif
            </div>
            <div class="student-badge" style="margin-inline-start:8px;">
                <i class="fas fa-seedling"></i>
                البرنامج: {{ $program->name }}
            </div>
        </div>
        <div style="margin-right: auto;">
            <a href="{{ route('supervisor.iman-program.index', ['scope' => 'student', 'student_id' => $student->id]) }}" class="btn-cancel"><i class="fas fa-arrow-right"></i> رجوع</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('supervisor.iman-program.update-criteria', $program) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="student_id" value="{{ $student->id }}">

        <div class="form-card">
            <h5><i class="fas fa-list-check"></i> معايير الطالب</h5>
            <p class="help">هذه المعايير خاصة بالطالب <strong>{{ $student->user->name }}</strong> فقط. اختر نوع المعيار:
                <strong>علامة</strong> لتسجيل تم/لم يتم، أو <strong>قيمة عددية</strong> لتحديد عدد أو مقدار.</p>

            <div id="criteria-list">
                @foreach($items as $i => $row)
                    @php
                        $rowId = is_array($row) ? ($row['id'] ?? null) : ($row->id ?? null);
                        $rowName = is_array($row) ? ($row['name'] ?? '') : $row->name;
                        $rowType = is_array($row) ? ($row['type'] ?? 'check') : $row->type;
                        $rowTarget = is_array($row) ? ($row['target_value'] ?? null) : $row->target_value;
                    @endphp
                    <div class="criterion-row" data-idx="{{ $i }}">
                        <input type="hidden" name="criteria[{{ $i }}][id]" value="{{ $rowId }}">
                        <input type="text" name="criteria[{{ $i }}][name]" class="form-control" required maxlength="255"
                               placeholder="اسم المعيار" value="{{ $rowName }}">
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
                <i class="fas fa-plus"></i> إضافة معيار جديد للطالب
            </button>
        </div>

        <div class="form-actions">
            <a href="{{ route('supervisor.iman-program.index', ['scope' => 'student', 'student_id' => $student->id]) }}" class="btn-cancel">إلغاء</a>
            <button type="submit" class="btn-gold">
                <i class="fas fa-save"></i> حفظ معايير الطالب
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
            <label><input type="radio" name="criteria[${idx}][type]" value="check" checked><span><i class="fas fa-check"></i> علامة</span></label>
            <label><input type="radio" name="criteria[${idx}][type]" value="number"><span><i class="fas fa-hashtag"></i> رقم</span></label>
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
        if (list.children.length > 1) { row.remove(); } else { row.querySelector('input[type="text"]').value = ''; }
    }
});

function attachRowEvents(row) {
    const radios = row.querySelectorAll('input[type="radio"][name$="[type]"]');
    const targetInput = row.querySelector('.target-input');
    radios.forEach(r => {
        r.addEventListener('change', function () {
            if (this.value === 'number') { targetInput.style.display = ''; } else { targetInput.style.display = 'none'; targetInput.value = ''; }
        });
    });
}
document.querySelectorAll('.criterion-row').forEach(attachRowEvents);
</script>
@endpush
