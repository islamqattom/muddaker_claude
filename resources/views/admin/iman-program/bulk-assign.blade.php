@extends('layouts.app')

@section('title', 'تعيين برنامج إيماني لعدة مستخدمين')
@section('page-title', 'تعيين برنامج إيماني لعدة مستخدمين')
@section('role-label', 'إدارة')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.bulk-assign-page { --gold:#d4a030; --gold-light:#e6b84d; --navy:#182B48; --navy-light:#2A4570; --success:#27ae60; --danger:#c0392b; --radius:16px; --shadow-sm:0 2px 10px rgba(24,43,72,0.05); }
.bulk-assign-page .page-hero { background:linear-gradient(135deg,var(--navy) 0%,var(--navy-light) 100%); border-radius:var(--radius); padding:24px 30px; margin-bottom:24px; color:#fff; display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
.bulk-assign-page .page-hero h1 { margin:0; font-size:22px; font-weight:700; }
.bulk-assign-page .page-hero p { color:rgba(255,255,255,0.8); margin:0; font-size:13px; }
.bulk-assign-page .hero-icon { width:56px; height:56px; border-radius:14px; background:linear-gradient(135deg,var(--gold),var(--gold-light)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:24px; }
.bulk-assign-page .form-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow-sm); padding:26px; margin-bottom:22px; border:1px solid rgba(24,43,72,0.05); }
.bulk-assign-page .form-card h5 { color:var(--navy); font-weight:800; margin-bottom:16px; }
.bulk-assign-page .form-card h5 i { color:var(--gold); margin-inline-end:8px; }
.bulk-assign-page .form-card p.help { color:#6b7a90; font-size:13px; margin-bottom:18px; }
.bulk-assign-page .criterion-row { display:grid; grid-template-columns:1fr 180px 200px 50px; gap:10px; align-items:center; margin-bottom:10px; padding:10px; border-radius:12px; background:#f8f9fc; }
.bulk-assign-page .criterion-row .form-control { min-width:0; }
.bulk-assign-page .criterion-row .remove-btn { background:rgba(192,57,43,0.1); color:var(--danger); border:none; width:40px; height:40px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.bulk-assign-page .criterion-row .remove-btn:hover { background:var(--danger); color:#fff; }
.bulk-assign-page .type-select { display:flex; gap:8px; align-items:center; }
.bulk-assign-page .type-select label { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border:1.5px solid #d6dbe5; border-radius:10px; cursor:pointer; font-size:13px; font-weight:600; color:var(--navy); }
.bulk-assign-page .type-select input { display:none; }
.bulk-assign-page .type-select label:has(input:checked) { background:rgba(212,160,48,0.1); border-color:var(--gold); }
.bulk-assign-page .add-criterion-btn { background:rgba(24,43,72,0.06); color:var(--navy); border:2px dashed rgba(24,43,72,0.2); padding:12px 20px; border-radius:12px; font-weight:700; width:100%; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
.bulk-assign-page .add-criterion-btn:hover { background:var(--navy); color:#fff; border-color:var(--navy); }
.bulk-assign-page .target-group { background:#f8f9fc; border-radius:12px; padding:16px; border:1px solid rgba(24,43,72,0.05); }
.bulk-assign-page .target-group h6 { color:var(--navy); font-weight:800; margin:0 0 10px; display:flex; align-items:center; gap:8px; }
.bulk-assign-page .target-group h6 i { color:var(--gold); }
.bulk-assign-page .form-check-input:checked { background-color:var(--gold); border-color:var(--gold); }
.bulk-assign-page .form-check-label { font-weight:600; color:var(--navy); cursor:pointer; }
.bulk-assign-page .user-search { background:#fff; border:1.5px solid #d6dbe5; border-radius:10px; padding:8px 12px; font-size:13px; width:100%; margin-bottom:10px; }
.bulk-assign-page .user-search:focus { border-color:var(--gold); outline:none; }
.bulk-assign-page .user-list { max-height:280px; overflow-y:auto; background:#fff; border:1px solid #e8ecf2; border-radius:10px; padding:6px; }
.bulk-assign-page .user-list .user-item { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; cursor:pointer; transition:background 0.15s; }
.bulk-assign-page .user-list .user-item:hover { background:#f8f9fc; }
.bulk-assign-page .user-list .user-item .form-check-input { margin:0; cursor:pointer; }
.bulk-assign-page .user-list .user-item .avatar { width:32px; height:32px; border-radius:8px; background:linear-gradient(135deg,var(--gold),var(--gold-light)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0; }
.bulk-assign-page .user-list .user-item .avatar.sup { background:linear-gradient(135deg,var(--navy),var(--navy-light)); }
.bulk-assign-page .user-list .user-item .name { color:var(--navy); font-weight:600; font-size:13px; flex:1; }
.bulk-assign-page .user-list .user-item .meta { color:#6b7a90; font-size:11px; }
.bulk-assign-page .user-list .empty-state { padding:24px 12px; text-align:center; color:#6b7a90; font-size:13px; }
.bulk-assign-page .user-list.disabled-list { opacity:0.55; pointer-events:none; }
.bulk-assign-page .selected-pill { display:inline-flex; align-items:center; gap:5px; background:rgba(212,160,48,0.15); color:var(--navy); font-weight:700; font-size:12px; padding:3px 10px; border-radius:20px; margin-inline-start:6px; }
.bulk-assign-page .form-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:8px; }
.bulk-assign-page .btn-gold { background:linear-gradient(135deg,var(--gold),var(--gold-light)); color:#fff; border:none; padding:12px 28px; border-radius:12px; font-weight:700; cursor:pointer; }
.bulk-assign-page .btn-gold:hover { transform:translateY(-1px); }
.bulk-assign-page .btn-gold:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
.bulk-assign-page .btn-cancel { background:#f0f4f8; color:var(--navy); border:none; padding:12px 22px; border-radius:12px; font-weight:700; text-decoration:none; }
.bulk-assign-page .btn-cancel:hover { background:#dee5ed; }
.bulk-assign-page .summary-box { background:rgba(212,160,48,0.08); border:1px solid rgba(212,160,48,0.25); border-radius:12px; padding:14px 18px; color:var(--navy); font-weight:700; display:flex; align-items:center; gap:10px; }
.bulk-assign-page .summary-box i { color:var(--gold); font-size:18px; }
</style>
@endpush

@section('content')
<div class="bulk-assign-page">
    <div class="page-hero">
        <div class="hero-icon"><i class="fas fa-bullhorn"></i></div>
        <div>
            <h1>تعيين برنامج إيماني لعدة مستخدمين</h1>
            <p>أنشئ برنامجاً بمعايير محددة مرة واحدة وعيّنه لكل الطلاب والمشرفين بنقرة واحدة</p>
        </div>
        <div style="margin-right: auto;">
            <a href="{{ route('admin.iman-program.index') }}" class="btn-cancel"><i class="fas fa-arrow-right"></i> رجوع</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.iman-program.bulk-store') }}" id="bulkAssignForm">
        @csrf

        <div class="form-card">
            <h5><i class="fas fa-circle-info"></i> بيانات البرنامج</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">اسم البرنامج <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="255"
                           value="{{ old('name') }}" placeholder="مثال: برنامج رمضان الإيماني">
                    <small class="text-muted">سيتم تخطي أي مستخدم لديه برنامج بنفس الاسم.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ البدء</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <input type="text" class="form-control" value="نشط" disabled>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="2" maxlength="2000" placeholder="وصف مختصر للبرنامج (اختياري)">{{ old('description') }}</textarea>
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
                        $rowName = is_array($row) ? ($row['name'] ?? '') : $row->name;
                        $rowType = is_array($row) ? ($row['type'] ?? 'check') : $row->type;
                        $rowTarget = is_array($row) ? ($row['target_value'] ?? null) : $row->target_value;
                    @endphp
                    <div class="criterion-row" data-idx="{{ $i }}">
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
                <i class="fas fa-plus"></i> إضافة معيار جديد
            </button>
        </div>

        <div class="form-card">
            <h5><i class="fas fa-users"></i> المستخدمون المستهدفون</h5>
            <p class="help">اختر لمن تريد تعيين هذا البرنامج. يمكنك اختيار <strong>جميع الطلاب</strong> أو <strong>جميع المشرفين</strong> بنقرة واحدة، أو تحديد مستخدمين بعينهم.</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="target-group">
                        <h6>
                            <i class="fas fa-user-graduate"></i> الطلاب
                            <span class="selected-pill" id="student-selected-pill" style="display:none;">0</span>
                        </h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="all_students" name="all_students" value="1" {{ old('all_students') ? 'checked' : '' }}>
                            <label class="form-check-label" for="all_students">
                                جميع الطلاب ({{ $students->count() }})
                            </label>
                        </div>
                        <input type="text" class="user-search" data-search-target="student-list" placeholder="ابحث عن طالب...">
                        <div class="user-list {{ old('all_students') ? 'disabled-list' : '' }}" id="student-list">
                            @forelse($students as $s)
                                <label class="user-item" data-name="{{ $s->user->name }} {{ $s->student_number ?? '' }}">
                                    <input class="form-check-input user-checkbox" type="checkbox" name="student_ids[]" value="{{ $s->id }}" data-group="student" {{ in_array($s->id, (array) old('student_ids', [])) ? 'checked' : '' }} {{ old('all_students') ? 'disabled' : '' }}>
                                    <div class="avatar">{{ mb_substr($s->user->name ?? '؟', 0, 1) }}</div>
                                    <div style="flex:1; min-width:0;">
                                        <div class="name">{{ $s->user->name }}</div>
                                        @if($s->student_number)
                                            <div class="meta">#{{ $s->student_number }}</div>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <div class="empty-state">لا يوجد طلاب في النظام.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="target-group">
                        <h6>
                            <i class="fas fa-user-tie"></i> المشرفون
                            <span class="selected-pill" id="supervisor-selected-pill" style="display:none;">0</span>
                        </h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="all_supervisors" name="all_supervisors" value="1" {{ old('all_supervisors') ? 'checked' : '' }}>
                            <label class="form-check-label" for="all_supervisors">
                                جميع المشرفين ({{ $supervisors->count() }})
                            </label>
                        </div>
                        <input type="text" class="user-search" data-search-target="supervisor-list" placeholder="ابحث عن مشرف...">
                        <div class="user-list {{ old('all_supervisors') ? 'disabled-list' : '' }}" id="supervisor-list">
                            @forelse($supervisors as $sup)
                                <label class="user-item" data-name="{{ $sup->name }}">
                                    <input class="form-check-input user-checkbox" type="checkbox" name="supervisor_ids[]" value="{{ $sup->id }}" data-group="supervisor" {{ in_array($sup->id, (array) old('supervisor_ids', [])) ? 'checked' : '' }} {{ old('all_supervisors') ? 'disabled' : '' }}>
                                    <div class="avatar sup">{{ mb_substr($sup->name ?? '؟', 0, 1) }}</div>
                                    <div style="flex:1; min-width:0;">
                                        <div class="name">{{ $sup->name }}</div>
                                        <div class="meta">{{ $sup->email }}</div>
                                    </div>
                                </label>
                            @empty
                                <div class="empty-state">لا يوجد مشرفين في النظام.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-box mt-3" id="summary-box">
                <i class="fas fa-info-circle"></i>
                <span>سيتم إنشاء البرنامج لـ <strong id="target-count">0</strong> مستخدم.</span>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.iman-program.index') }}" class="btn-cancel">إلغاء</a>
            <button type="submit" class="btn-gold" id="submit-btn">
                <i class="fas fa-paper-plane"></i> تعيين البرنامج للمستخدمين المحددين
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let idx = {{ $items->count() }};
const list = document.getElementById('criteria-list');
const studentCount = {{ $students->count() }};
const supervisorCount = {{ $supervisors->count() }};

document.getElementById('add-criterion').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'criterion-row';
    row.dataset.idx = idx;
    row.innerHTML = `
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

const allStudentsBox = document.getElementById('all_students');
const allSupervisorsBox = document.getElementById('all_supervisors');
const studentList = document.getElementById('student-list');
const supervisorList = document.getElementById('supervisor-list');
const studentPill = document.getElementById('student-selected-pill');
const supervisorPill = document.getElementById('supervisor-selected-pill');
const targetCountEl = document.getElementById('target-count');
const submitBtn = document.getElementById('submit-btn');

function visibleChecked(listEl) {
    return Array.from(listEl.querySelectorAll('.user-item')).filter(function (item) {
        return item.style.display !== 'none';
    }).map(function (item) { return item.querySelector('.user-checkbox'); }).filter(function (cb) { return cb.checked; });
}

function totalChecked(listEl) {
    return Array.from(listEl.querySelectorAll('.user-checkbox')).filter(function (cb) { return cb.checked; }).length;
}

function syncAllBox(box, listEl, pillEl) {
    const checkboxes = listEl.querySelectorAll('.user-checkbox');
    if (box.checked) {
        listEl.classList.add('disabled-list');
        checkboxes.forEach(function (cb) { cb.checked = true; cb.disabled = true; });
    } else {
        listEl.classList.remove('disabled-list');
        checkboxes.forEach(function (cb) { cb.disabled = false; });
    }
    updateCount();
}

allStudentsBox.addEventListener('change', function () { syncAllBox(allStudentsBox, studentList, studentPill); });
allSupervisorsBox.addEventListener('change', function () { syncAllBox(allSupervisorsBox, supervisorList, supervisorPill); });

document.querySelectorAll('.user-checkbox').forEach(function (cb) {
    cb.addEventListener('change', updateCount);
});

document.querySelectorAll('.user-search').forEach(function (input) {
    input.addEventListener('input', function () {
        const target = document.getElementById(input.dataset.searchTarget);
        const term = input.value.trim().toLowerCase();
        target.querySelectorAll('.user-item').forEach(function (item) {
            const name = (item.dataset.name || '').toLowerCase();
            item.style.display = name.indexOf(term) !== -1 ? '' : 'none';
        });
    });
});

function updateCount() {
    const studentTotal = allStudentsBox.checked ? studentCount : totalChecked(studentList);
    const supervisorTotal = allSupervisorsBox.checked ? supervisorCount : totalChecked(supervisorList);

    if (allStudentsBox.checked) {
        studentPill.textContent = 'الكل (' + studentCount + ')';
        studentPill.style.display = 'inline-flex';
    } else {
        const n = totalChecked(studentList);
        studentPill.textContent = n;
        studentPill.style.display = n > 0 ? 'inline-flex' : 'none';
    }
    if (allSupervisorsBox.checked) {
        supervisorPill.textContent = 'الكل (' + supervisorCount + ')';
        supervisorPill.style.display = 'inline-flex';
    } else {
        const n = totalChecked(supervisorList);
        supervisorPill.textContent = n;
        supervisorPill.style.display = n > 0 ? 'inline-flex' : 'none';
    }

    const total = studentTotal + supervisorTotal;
    targetCountEl.textContent = total;
    submitBtn.disabled = total === 0;
    const span = document.querySelector('#summary-box span');
    if (total === 0) {
        span.innerHTML = 'الرجاء اختيار طالب واحد على الأقل أو مشرف.';
    } else {
        span.innerHTML = 'سيتم إنشاء البرنامج لـ <strong>' + total + '</strong> مستخدم. سيتم تخطي من لديه برنامج بنفس الاسم.';
    }
}

document.getElementById('bulkAssignForm').addEventListener('submit', function () {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جارٍ التعيين...';
});

updateCount();
</script>
@endpush
