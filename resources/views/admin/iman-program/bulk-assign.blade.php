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
.bulk-assign-page .page-hero { background:linear-gradient(135deg,var(--navy) 0%,var(--navy-light) 100%); border-radius:14px; padding:16px 22px; margin-bottom:16px; color:#fff; display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.bulk-assign-page .page-hero h1 { margin:0; font-size:18px; font-weight:700; }
.bulk-assign-page .page-hero p { color:rgba(255,255,255,0.8); margin:0; font-size:13px; }
.bulk-assign-page .hero-icon { width:48px; height:48px; border-radius:13px; background:linear-gradient(135deg,var(--gold),var(--gold-light)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px; }
.bulk-assign-page .form-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow-sm); padding:16px; margin-bottom:16px; border:1px solid rgba(24,43,72,0.05); }
.bulk-assign-page .form-card h5 { color:var(--navy); font-weight:800; margin-bottom:16px; }
.bulk-assign-page .form-card h5 i { color:var(--gold); margin-inline-end:8px; }
.bulk-assign-page .form-card p.help { color:#6b7a90; font-size:13px; margin-bottom:16px; }
.bulk-assign-page .criterion-row { display:grid; grid-template-columns:1fr 180px 200px 50px; gap:10px; align-items:center; margin-bottom:10px; padding:10px; border-radius:12px; background:#f8f9fc; }
.bulk-assign-page .criterion-row .form-control { min-width:0; }
.bulk-assign-page .criterion-row .remove-btn { background:rgba(192,57,43,0.1); color:var(--danger); border:none; width:40px; height:40px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.bulk-assign-page .criterion-row .remove-btn:hover { background:var(--danger); color:#fff; }
.bulk-assign-page .type-select { display:flex; gap:8px; align-items:center; }
.bulk-assign-page .type-select label { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border:1.5px solid #d6dbe5; border-radius:10px; cursor:pointer; font-size:13px; font-weight:600; color:var(--navy); }
.bulk-assign-page .type-select input { display:none; }
.bulk-assign-page .type-select label:has(input:checked) { background:rgba(212,160,48,0.1); border-color:var(--gold); }
.bulk-assign-page .add-criterion-btn { background:rgba(24,43,72,0.06); color:var(--navy); border:2px dashed rgba(24,43,72,0.2); padding:12px 16px; border-radius:12px; font-weight:700; width:100%; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
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
.bulk-assign-page .user-list.disabled-list { opacity:0.55; pointer-events:none; }
.bulk-assign-page .selected-pill { display:inline-flex; align-items:center; gap:5px; background:rgba(212,160,48,0.15); color:var(--navy); font-weight:700; font-size:12px; padding:3px 10px; border-radius:13px; margin-inline-start:6px; }
.bulk-assign-page .form-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:8px; }
.bulk-assign-page .btn-gold { background:linear-gradient(135deg,var(--gold),var(--gold-light)); color:#fff; border:none; padding:12px 16px; border-radius:12px; font-weight:700; cursor:pointer; }
.bulk-assign-page .btn-gold:hover { transform:translateY(-1px); }
.bulk-assign-page .btn-gold:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
.bulk-assign-page .btn-cancel { background:#f0f4f8; color:var(--navy); border:none; padding:12px 16px; border-radius:12px; font-weight:700; text-decoration:none; }
.bulk-assign-page .btn-cancel:hover { background:#dee5ed; }
.bulk-assign-page .summary-box { background:rgba(212,160,48,0.08); border:1px solid rgba(212,160,48,0.25); border-radius:12px; padding:14px 16px; color:var(--navy); font-weight:700; display:flex; align-items:center; gap:10px; }
.bulk-assign-page .summary-box i { color:var(--gold); font-size:18px; }
.bulk-assign-page .mode-toggle { display:inline-flex; background:#fff; border-radius:13px; padding:5px; box-shadow:0 2px 10px rgba(24,43,72,0.08); border:1px solid rgba(24,43,72,0.06); margin-bottom:16px; }
.bulk-assign-page .mode-toggle button { border:none; background:transparent; color:#6b7a90; font-weight:700; font-size:14px; padding:10px 16px; border-radius:10px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.2s; }
.bulk-assign-page .mode-toggle button.active { background:linear-gradient(135deg,var(--navy),var(--navy-light)); color:#fff; box-shadow:0 2px 8px rgba(24,43,72,0.25); }
.bulk-assign-page .mode-toggle button:hover:not(.active) { background:#f0f4f8; color:var(--navy); }
.bulk-assign-page .program-picker { background:#fff; border:1.5px solid #d6dbe5; border-radius:12px; padding:16px; margin-bottom:16px; }
.bulk-assign-page .program-picker label.form-label { color:var(--navy); font-weight:700; margin-bottom:8px; }
.bulk-assign-page .program-picker select { width:100%; padding:10px 14px; border:1.5px solid #d6dbe5; border-radius:10px; font-size:14px; font-weight:600; color:var(--navy); background:#fff; }
.bulk-assign-page .program-picker select:focus { border-color:var(--gold); outline:none; }
.bulk-assign-page .program-info { display:flex; gap:14px; flex-wrap:wrap; padding:12px 14px; background:#f8f9fc; border-radius:10px; margin-top:10px; font-size:13px; color:var(--navy); }
.bulk-assign-page .program-info .pill { background:rgba(24,43,72,0.08); color:var(--navy); padding:4px 12px; border-radius:13px; font-weight:700; }
.bulk-assign-page .program-info .pill.gold { background:rgba(212,160,48,0.18); }
.bulk-assign-page .edit-banner { background:rgba(39,174,96,0.10); border:1px solid rgba(39,174,96,0.30); border-radius:12px; padding:14px 16px; color:#1e7e3e; font-weight:700; display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.bulk-assign-page .edit-banner i { font-size:18px; }
.bulk-assign-page .targets-disabled .target-group { opacity:0.45; pointer-events:none; }
.bulk-assign-page .delete-card { background:#fff; border:1.5px solid rgba(192,57,43,0.30); border-radius:12px; padding:16px; margin-bottom:16px; }
.bulk-assign-page .delete-warning { background:rgba(192,57,43,0.08); border:1px solid rgba(192,57,43,0.25); border-radius:12px; padding:14px 16px; color:#922b21; font-weight:700; display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.bulk-assign-page .delete-warning i { font-size:18px; }
.bulk-assign-page .delete-card .confirm-row { display:flex; align-items:flex-start; gap:10px; padding:12px 14px; background:#fef5f4; border-radius:10px; margin-top:14px; }
.bulk-assign-page .delete-card .confirm-row input { margin-top:4px; }
.bulk-assign-page .delete-card .confirm-row label { font-weight:600; color:#922b21; cursor:pointer; font-size:14px; }
.bulk-assign-page .owners-preview { background:#f8f9fc; border:1px solid #e8ecf2; border-radius:10px; padding:10px 14px; margin-top:12px; max-height:200px; overflow-y:auto; }
.bulk-assign-page .owners-preview .owner-chip { display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #e8ecf2; padding:4px 10px; border-radius:13px; margin:3px 4px 3px 0; font-size:12px; font-weight:600; color:var(--navy); }
.bulk-assign-page .owners-preview .owner-chip i { color:var(--gold); font-size:11px; }
.bulk-assign-page .owners-preview .owner-chip.user i { color:var(--navy); }
.bulk-assign-page .btn-danger-solid { background:linear-gradient(135deg,#c0392b,#e74c3c); color:#fff; border:none; padding:12px 16px; border-radius:12px; font-weight:700; cursor:pointer; }
.bulk-assign-page .btn-danger-solid:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(192,57,43,0.30); }
.bulk-assign-page .btn-danger-solid:disabled { opacity:0.5; cursor:not-allowed; transform:none; box-shadow:none; }
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
        <div style="margin-right:auto">
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
        <input type="hidden" name="mode" id="modeInput" value="{{ request('mode', old('mode', 'create')) }}">
        <input type="hidden" name="source_program_id" id="sourceProgramId" value="{{ request('program', old('source_program_id', '')) }}">

        <div class="mode-toggle" role="tablist">
            <button type="button" data-mode="create" class="{{ request('mode', 'create') === 'create' ? 'active' : '' }}" id="modeCreateBtn">
                <i class="fas fa-plus-circle"></i> إنشاء برنامج جديد
            </button>
            <button type="button" data-mode="edit" class="{{ request('mode') === 'edit' ? 'active' : '' }}" id="modeEditBtn">
                <i class="fas fa-pen-to-square"></i> تعديل برنامج موجود
            </button>
            <button type="button" data-mode="delete" class="{{ request('mode') === 'delete' ? 'active' : '' }}" id="modeDeleteBtn">
                <i class="fas fa-trash"></i> حذف برنامج
            </button>
        </div>

        <div class="program-picker" id="programPicker" style="{{ in_array(request('mode'), ['edit', 'delete']) ? '':'display:none;' }}">
            <label class="form-label"><i class="fas fa-folder-open" style="color:var(--gold)"></i> <span id="pickerLabel">اختر البرنامج</span></label>
            <select id="programSelect">
                <option value="">— اختر برنامجاً —</option>
                @foreach($existingPrograms as $p)
                    <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-owners="{{ $p->owners_count }}" data-criteria="{{ $p->criteria_count }}" {{ request('program') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} — {{ $p->owners_count }} مستخدم · {{ $p->criteria_count }} معيار
                    </option>
                @endforeach
            </select>
            <div class="program-info" id="programInfo" style="display:none">
                <span class="pill gold" id="piOwners">0 مستخدم</span>
                <span class="pill" id="piCriteria">0 معيار</span>
            </div>
        </div>

        <div class="edit-banner" id="editBanner" style="{{ request('mode') === 'edit' ? '':'display:none;' }}">
            <i class="fas fa-circle-info"></i>
            <span>وضع التعديل: سيتم تحديث المعايير لجميع المستخدمين الذين لديهم هذا البرنامج بنفس الاسم.</span>
        </div>

        <div class="delete-card" id="deleteCard" style="{{ request('mode') === 'delete' ? '':'display:none;' }}">
            <div class="delete-warning">
                <i class="fas fa-triangle-exclamation"></i>
                <span>تحذير: سيؤدي الحذف إلى إزالة البرنامج وجميع معاييره وإدخالاته اليومية نهائياً للمستخدمين المدرجين أدناه. لا يمكن التراجع.</span>
            </div>
            <div id="deleteAffectedWrap" style="display:none">
                <label class="form-label" style="color:var(--navy);font-weight:700">المستخدمون المتأثرون بالحذف:</label>
                <div class="owners-preview" id="deleteOwnersList"></div>
            </div>
            <div class="confirm-row">
                <input class="form-check-input" type="checkbox" id="confirmDeleteBox" name="confirm_delete" value="1">
                <label for="confirmDeleteBox">أؤكد رغبتي في حذف هذا البرنامج من جميع المستخدمين ولا أستطيع التراجع.</label>
            </div>
        </div>

        <div class="form-card">
            <h5><i class="fas fa-circle-info"></i> بيانات البرنامج</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">اسم البرنامج <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="programNameInput" class="form-control" required maxlength="255"
                           value="{{ old('name') }}" placeholder="مثال: برنامج رمضان الإيماني">
                    <small class="text-muted" id="nameHelp">سيتم تخطي أي مستخدم لديه برنامج بنفس الاسم.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ البدء</label>
                    <input type="date" name="start_date" id="programStartDate" class="form-control" value="{{ old('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <input type="text" class="form-control" value="نشط" disabled>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" id="programDescription" class="form-control" rows="2" maxlength="2000" placeholder="وصف مختصر للبرنامج (اختياري)">{{ old('description') }}</textarea>
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
                               style="{{ $rowtype === 'number' ? '':'display:none;' }}">
                        <button type="button" class="remove-btn" title="حذف"><i class="fas fa-times"></i></button>
                    </div>
                @endforeach
            </div>

            <button type="button" class="add-criterion-btn" id="add-criterion">
                <i class="fas fa-plus"></i> إضافة معيار جديد
            </button>
        </div>

        <div class="form-card" id="targetsCard">
            <h5><i class="fas fa-users"></i> المستخدمون المستهدفون</h5>
            <p class="help" id="targetsHelp">اختر لمن تريد تعيين هذا البرنامج. يمكنك اختيار <strong>جميع الطلاب</strong> أو <strong>جميع المشرفين</strong> بنقرة واحدة، أو تحديد مستخدمين بعينهم.</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="target-group">
                        <h6>
                            <i class="fas fa-user-graduate"></i> الطلاب
                            <span class="selected-pill" id="student-selected-pill" style="display:none">0</span>
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
                                    <div style="flex:1;min-width:0">
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
                            <span class="selected-pill" id="supervisor-selected-pill" style="display:none">0</span>
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
                                    <div style="flex:1;min-width:0">
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
                <span id="summaryText">سيتم إنشاء البرنامج لـ <strong id="target-count">0</strong> مستخدم.</span>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.iman-program.index') }}" class="btn-cancel">إلغاء</a>
            <button type="submit" class="btn-gold" id="submit-btn">
                <i class="fas fa-paper-plane"></i> <span id="submitLabel">تعيين البرنامج للمستخدمين المحددين</span>
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
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let currentMode = '{{ request('mode', 'create') }}';

function buildCriterionRowHTML(i, name, type, target) {
    const isNum = type === 'number';
    const targetStyle = isNum ? '' : 'display:none;';
    const checkChecked = (!type || type === 'check') ? 'checked' : '';
    const numChecked = type === 'number' ? 'checked' : '';
    return `
        <input type="text" name="criteria[${i}][name]" class="form-control" required maxlength="255" placeholder="اسم المعيار" value="${(name || '').replace(/"/g, '&quot;')}">
        <div class="type-select">
            <label><input type="radio" name="criteria[${i}][type]" value="check" ${checkChecked}><span><i class="fas fa-check"></i> علامة</span></label>
            <label><input type="radio" name="criteria[${i}][type]" value="number" ${numChecked}><span><i class="fas fa-hashtag"></i> رقم</span></label>
        </div>
        <input type="number" min="0" name="criteria[${i}][target_value]" class="form-control target-input" placeholder="الهدف اليومي" value="${target ?? ''}" style="${targetStyle}">
        <button type="button" class="remove-btn" title="حذف"><i class="fas fa-times"></i></button>
    `;
}

function attachRowEvents(row) {
    const radios = row.querySelectorAll('input[type="radio"][name$="[type]"]');
    const targetInput = row.querySelector('.target-input');
    radios.forEach(r => {
        r.addEventListener('change', function () {
            if (this.value === 'number') { targetInput.style.display = ''; } else { targetInput.style.display = 'none'; targetInput.value = ''; }
        });
    });
}

document.getElementById('add-criterion').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'criterion-row';
    row.dataset.idx = idx;
    row.innerHTML = buildCriterionRowHTML(idx, '', 'check', '');
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

document.querySelectorAll('.criterion-row').forEach(attachRowEvents);

const allStudentsBox = document.getElementById('all_students');
const allSupervisorsBox = document.getElementById('all_supervisors');
const studentList = document.getElementById('student-list');
const supervisorList = document.getElementById('supervisor-list');
const studentPill = document.getElementById('student-selected-pill');
const supervisorPill = document.getElementById('supervisor-selected-pill');
const targetCountEl = document.getElementById('target-count');
const submitBtn = document.getElementById('submit-btn');
const submitLabel = document.getElementById('submitLabel');
const summaryText = document.getElementById('summaryText');
const nameHelp = document.getElementById('nameHelp');
const targetsCard = document.getElementById('targetsCard');
const targetsHelp = document.getElementById('targetsHelp');
const programPicker = document.getElementById('programPicker');
const pickerLabel = document.getElementById('pickerLabel');
const programSelect = document.getElementById('programSelect');
const programInfo = document.getElementById('programInfo');
const piOwners = document.getElementById('piOwners');
const piCriteria = document.getElementById('piCriteria');
const editBanner = document.getElementById('editBanner');
const deleteCard = document.getElementById('deleteCard');
const deleteAffectedWrap = document.getElementById('deleteAffectedWrap');
const deleteOwnersList = document.getElementById('deleteOwnersList');
const confirmDeleteBox = document.getElementById('confirmDeleteBox');
const programNameInput = document.getElementById('programNameInput');
const programStartDate = document.getElementById('programStartDate');
const programDescription = document.getElementById('programDescription');
const sourceProgramId = document.getElementById('sourceProgramId');
const modeInput = document.getElementById('modeInput');
const formCard = document.querySelectorAll('.form-card');
const bulkForm = document.getElementById('bulkAssignForm');

function totalChecked(listEl) {
    return Array.from(listEl.querySelectorAll('.user-checkbox')).filter(function (cb) { return cb.checked; }).length;
}

function syncAllBox(box, listEl) {
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

allStudentsBox.addEventListener('change', function () { syncAllBox(allStudentsBox, studentList); });
allSupervisorsBox.addEventListener('change', function () { syncAllBox(allSupervisorsBox, supervisorList); });

document.querySelectorAll('.user-checkbox').forEach(function (cb) { cb.addEventListener('change', updateCount); });

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
    if (currentMode === 'edit') { return; }
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
    if (total === 0) {
        summaryText.innerHTML = 'الرجاء اختيار طالب واحد على الأقل أو مشرف.';
    } else {
        summaryText.innerHTML = 'سيتم إنشاء البرنامج لـ <strong>' + total + '</strong> مستخدم. سيتم تخطي من لديه برنامج بنفس الاسم.';
    }
}

function setMode(mode) {
    currentMode = mode;
    modeInput.value = mode;
    document.getElementById('modeCreateBtn').classList.toggle('active', mode === 'create');
    document.getElementById('modeEditBtn').classList.toggle('active', mode === 'edit');
    document.getElementById('modeDeleteBtn').classList.toggle('active', mode === 'delete');
    programPicker.style.display = (mode === 'edit' || mode === 'delete') ? '' : 'none';
    editBanner.style.display = mode === 'edit' ? '' : 'none';
    deleteCard.style.display = mode === 'delete' ? '' : 'none';
    pickerLabel.textContent = mode === 'delete' ? 'اختر البرنامج المراد حذفه' : 'اختر البرنامج المراد تعديله';

    formCard.forEach(function (card) { card.style.display = ''; });
    targetsCard.style.display = '';
    bulkForm.setAttribute('novalidate', mode === 'delete' ? 'novalidate' : '');

    if (mode === 'edit') {
        targetsCard.classList.add('targets-disabled');
        allStudentsBox.checked = false;
        allSupervisorsBox.checked = false;
        syncAllBox(allStudentsBox, studentList);
        syncAllBox(allSupervisorsBox, supervisorList);
        submitBtn.className = 'btn-gold';
        submitLabel.textContent = 'حفظ المعايير لكل المستخدمين';
        nameHelp.textContent = 'سيتم تحديث الاسم والوصف وتاريخ البدء لجميع نسخ هذا البرنامج.';
        if (programSelect.value) { sourceProgramId.value = programSelect.value; }
        if (programSelect.selectedOptions[0] && programSelect.selectedOptions[0].dataset.owners) {
            summaryText.innerHTML = 'سيتم تحديث البرنامج لـ <strong>' + programSelect.selectedOptions[0].dataset.owners + '</strong> مستخدم يملك نسخة من هذا البرنامج.';
        } else {
            summaryText.innerHTML = 'اختر برنامجاً من القائمة لتحميل بياناته ومعاينة عدد المستخدمين.';
        }
    } else if (mode === 'delete') {
        targetsCard.style.display = 'none';
        formCard.forEach(function (card) {
            if (card.id === 'targetsCard') return;
            card.style.display = 'none';
        });
        list.innerHTML = '';
        idx = 0;
        submitBtn.className = 'btn-danger-solid';
        submitBtn.disabled = true;
        submitLabel.textContent = 'حذف البرنامج';
        sourceProgramId.value = '';
        if (programSelect.value) { sourceProgramId.value = programSelect.value; }
        if (programSelect.selectedOptions[0] && programSelect.selectedOptions[0].dataset.owners) {
            summaryText.innerHTML = 'سيتم حذف البرنامج لـ <strong>' + programSelect.selectedOptions[0].dataset.owners + '</strong> مستخدم. ضع علامة التأكيد ثم اضغط زر الحذف.';
        } else {
            summaryText.innerHTML = 'اختر برنامجاً من القائمة لمعاينة المستخدمين المتأثرين.';
        }
        confirmDeleteBox.checked = false;
    } else {
        targetsCard.classList.remove('targets-disabled');
        submitBtn.className = 'btn-gold';
        submitLabel.textContent = 'تعيين البرنامج للمستخدمين المحددين';
        nameHelp.textContent = 'سيتم تخطي أي مستخدم لديه برنامج بنفس الاسم.';
        sourceProgramId.value = '';
        if (list.children.length === 0) {
            document.getElementById('add-criterion').click();
        }
        updateCount();
    }
}

document.getElementById('modeCreateBtn').addEventListener('click', function () { setMode('create'); });
document.getElementById('modeEditBtn').addEventListener('click', function () { setMode('edit'); });
document.getElementById('modeDeleteBtn').addEventListener('click', function () { setMode('delete'); });

function loadProgram(programId) {
    if (!programId) {
        programInfo.style.display = 'none';
        if (currentMode === 'delete') { deleteAffectedWrap.style.display = 'none'; }
        return;
    }
    sourceProgramId.value = programId;
    fetch('{{ url('admin/iman-program/bulk-assign/load') }}/' + programId, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        credentials: 'same-origin',
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        programNameInput.value = data.name;
        programStartDate.value = data.start_date || '';
        programDescription.value = data.description || '';
        programInfo.style.display = 'flex';
        piOwners.textContent = data.owners_count + ' مستخدم';
        piCriteria.textContent = data.criteria.length + ' معيار';

        if (currentMode === 'edit') {
            renderCriteria(data.criteria);
            summaryText.innerHTML = 'سيتم تحديث البرنامج لـ <strong>' + data.owners_count + '</strong> مستخدم يملك نسخة من هذا البرنامج.';
        } else if (currentMode === 'delete') {
            renderDeleteOwners(data.owners || []);
            summaryText.innerHTML = 'سيتم حذف البرنامج لـ <strong>' + data.owners_count + '</strong> مستخدم. ضع علامة التأكيد ثم اضغط زر الحذف.';
            submitBtn.disabled = !confirmDeleteBox.checked;
        } else {
            renderCriteria(data.criteria);
        }
    })
    .catch(function () {
        programInfo.style.display = 'none';
        if (currentMode === 'delete') { deleteAffectedWrap.style.display = 'none'; }
    });
}

function renderDeleteOwners(owners) {
    if (!owners || owners.length === 0) {
        deleteAffectedWrap.style.display = 'none';
        return;
    }
    deleteOwnersList.innerHTML = owners.map(function (o) {
        const cls = o.type === 'user' ? 'user' : '';
        const icon = o.type === 'user' ? 'fa-user-tie' : 'fa-user-graduate';
        return '<span class="owner-chip ' + cls + '"><i class="fas ' + icon + '"></i> ' + o.name + '</span>';
    }).join('');
    deleteAffectedWrap.style.display = 'block';
}

function renderCriteria(criteria) {
    list.innerHTML = '';
    idx = 0;
    criteria.forEach(function (c) {
        const row = document.createElement('div');
        row.className = 'criterion-row';
        row.dataset.idx = idx;
        row.innerHTML = buildCriterionRowHTML(idx, c.name, c.type, c.target_value);
        list.appendChild(row);
        attachRowEvents(row);
        idx++;
    });
    if (criteria.length === 0) {
        document.getElementById('add-criterion').click();
    }
}

programSelect.addEventListener('change', function () { loadProgram(this.value); });

confirmDeleteBox.addEventListener('change', function () {
    if (currentMode === 'delete') {
        submitBtn.disabled = !this.checked || !sourceProgramId.value;
    }
});

document.getElementById('bulkAssignForm').addEventListener('submit', function () {
    submitBtn.disabled = true;
    const icon = (currentMode === 'edit' || currentMode === 'delete') ? 'fa-rotate' : 'fa-spinner';
    const text = currentMode === 'delete' ? 'جارٍ الحذف...' : 'جارٍ الحفظ...';
    submitBtn.innerHTML = '<i class="fas ' + icon + ' fa-spin"></i> ' + text;
});

setMode(currentMode);
if ((currentMode === 'edit' || currentMode === 'delete') && programSelect.value) {
    loadProgram(programSelect.value);
}
</script>
@endpush
