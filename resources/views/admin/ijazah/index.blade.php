@extends('layouts.app')

@section('title', 'الإجازة')
@section('page-title', 'إدارة الإجازات')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.ijazah-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.ijazah-page .hero-section {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.ijazah-page .hero-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.ijazah-page .hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 16px;
}

.ijazah-page .hero-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: white;
    box-shadow: 0 6px 20px rgba(212, 160, 48, 0.35);
    flex-shrink: 0;
}

.ijazah-page .hero-title {
    color: white; font-size: 22px; font-weight: 700;
    margin-bottom: 4px;
}

.ijazah-page .hero-sub {
    color: rgba(255,255,255,0.75); font-size: 13px; margin: 0;
}

.ijazah-page .form-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(24, 43, 72, 0.04);
    overflow: hidden;
    margin-bottom: 20px;
}

.ijazah-page .card-header-custom {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.ijazah-page .card-header-custom i { color: var(--gold); font-size: 18px; }
.ijazah-page .card-header-custom h5 { color: white; font-weight: 700; margin: 0; font-size: 16px; }

.ijazah-page .student-select-card {
    padding: 20px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.ijazah-page .student-select-card:hover {
    border-color: var(--gold);
    box-shadow: 0 4px 15px rgba(212, 160, 48, 0.15);
}

.ijazah-page .student-select-card.selected {
    border-color: var(--navy);
    background: rgba(24, 43, 72, 0.03);
}

.ijazah-page .student-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
}

.ijazah-page .student-level {
    font-size: 12px;
    color: #7f8c8d;
}

.ijazah-page .parts-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

.ijazah-page .part-check-item {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 14px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.ijazah-page .part-check-item:hover {
    border-color: var(--gold);
    transform: translateY(-2px);
}

.ijazah-page .part-check-item.checked {
    border-color: var(--success);
    background: rgba(39, 174, 96, 0.08);
}

.ijazah-page .part-check-item .part-num {
    font-size: 20px;
    font-weight: 800;
    color: var(--navy);
}

.ijazah-page .part-check-item .part-name {
    font-size: 11px;
    color: #7f8c8d;
    margin-top: 4px;
}

.ijazah-page .part-check-item input[type="checkbox"] {
    display: none;
}

.ijazah-page .part-check-item .check-icon {
    display: none;
    color: var(--success);
    margin-top: 4px;
}

.ijazah-page .part-check-item.checked .check-icon {
    display: block;
}

.ijazah-page .btn-gold {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, var(--gold), #B4802C);
    color: #fff; border: none; border-radius: 12px;
    font-weight: 700; font-size: 15px;
    transition: all 0.3s ease;
}

.ijazah-page .btn-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 160, 48, 0.35);
}

.ijazah-page .ijazah-table {
    width: 100%;
    border-collapse: collapse;
}

.ijazah-page .ijazah-table th {
    background: rgba(24, 43, 72, 0.05);
    color: var(--navy);
    font-weight: 700;
    font-size: 13px;
    padding: 12px 16px;
    text-align: center;
}

.ijazah-page .ijazah-table td {
    padding: 14px 16px;
    text-align: center;
    border-bottom: 1px solid rgba(24, 43, 72, 0.04);
}

.ijazah-page .ijazah-table tbody tr:hover {
    background: rgba(212, 160, 48, 0.04);
}

.ijazah-page .status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px; font-weight: 600;
}

.ijazah-page .status-badge.completed {
    background: rgba(39, 174, 96, 0.15);
    color: #27ae60;
}

.ijazah-page .remove-btn {
    background: rgba(192, 57, 43, 0.1);
    color: #c0392b;
    border: none;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.ijazah-page .remove-btn:hover {
    background: rgba(192, 57, 43, 0.2);
}

.ijazah-page .empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #7f8c8d;
}

.ijazah-page .empty-state i {
    font-size: 40px;
    opacity: 0.3;
    margin-bottom: 10px;
}

.filter-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}

@media (max-width: 768px) {
    .ijazah-page .parts-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>
@endpush

@section('content')
<div class="ijazah-page">
    @if(session('success'))
        <div class="alert alert-success mb-4" style="background: rgba(39,174,96,0.1); border: 1px solid rgba(39,174,96,0.3); border-radius: 12px; color: #27ae60; padding: 16px 20px;">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="hero-section" style="position: relative;">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-award"></i>
            </div>
            <div>
                <h1 class="hero-title">تسجيل الإجازة</h1>
                <p class="hero-sub">سجل إجازة الطالب في القرآن الكريم - يمكن إضافة طالب لأي مشرف</p>
            </div>
        </div>
        <button type="button" class="btn btn-warning position-absolute fw-bold" style="top: 20px; left: 20px; z-index: 10;" onclick="openAddStudentModal()">
            <i class="fas fa-users me-2"></i>الطلاب
        </button>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('admin.ijazah.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">المشرف</label>
                    <select name="supervisor_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- جميع المشرفين --</option>
                        @foreach($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}" {{ request('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                {{ $supervisor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">بحث بالاسم</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="اسم الطالب...">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="form-card">
        <div class="card-header-custom">
            <i class="fas fa-user-plus"></i>
            <h5>تسجيل إجازة طالب</h5>
        </div>
        <div class="card-body p-4">
            <form id="ijazahForm" method="POST" action="{{ route('admin.ijazah.store') }}">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">المشرف المسؤول</label>
                        <select name="supervisor_id" id="supervisorSelect" class="form-select form-select-lg" required onchange="loadStudents()">
                            <option value="">-- اختر مشرف --</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">الطالب</label>
                        <select name="student_id" id="studentSelect" class="form-select form-select-lg" required onchange="loadStudentIjazah()" disabled>
                            <option value="">-- اختر مشرف أولاً --</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold mb-3">
                        <i class="fas fa-check-square text-success me-1"></i>
                        الأجزاء المقروءة (اختر من 1 إلى 30)
                    </label>
                    <div class="parts-grid" id="partsGrid">
                        @foreach($parts as $part)
                            <label class="part-check-item" data-part-id="{{ $part->id }}" data-part-num="{{ $part->part_number }}">
                                <input type="checkbox" name="parts[]" value="{{ $part->id }}" onchange="togglePart(this)">
                                <div class="part-num">{{ $part->part_number }}</div>
                                <div class="part-name">{{ Str::limit($part->name, 10) }}</div>
                                <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn-gold" id="submitBtn" disabled>
                    <i class="fas fa-save"></i>
                    تسجيل الإجازة
                </button>
            </form>
        </div>
    </div>

    @if($ijazahRecords->count() > 0)
    <div class="form-card">
        <div class="card-header-custom">
            <i class="fas fa-list"></i>
            <h5>سجل الإجازات المسجلة</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="ijazah-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>المشرف</th>
                            <th>الجزء</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ijazahRecords as $studentId => $records)
                            @foreach($records as $ijazah)
                                <tr>
                                    <td><span style="color: var(--gold); font-weight: 700;">{{ $loop->parent->index + 1 }}</span></td>
                                    <td class="fw-bold" style="color: var(--navy);">{{ $ijazah->student->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $ijazah->supervisor->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge completed">
                                            <i class="fas fa-check-circle"></i>
                                            جزء {{ $ijazah->quranPart->part_number }}
                                        </span>
                                    </td>
                                    <td style="color: #7f8c8d; font-size: 13px;">{{ $ijazah->completed_at ? $ijazah->completed_at->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.ijazah.destroy') }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="student_id" value="{{ $ijazah->student_id }}">
                                            <input type="hidden" name="quran_part_id" value="{{ $ijazah->quran_part_id }}">
                                            <input type="hidden" name="supervisor_id" value="{{ $ijazah->supervisor_id }}">
                                            <button type="submit" class="remove-btn" onclick="return confirm('هل تريد حذف تسجيل الإجازة؟')">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: white;">
                    <h5 class="modal-title"><i class="fas fa-users me-2"></i>إدارة الطلاب والمشرفين</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-2" style="border-color: #27ae60;">
                                <div class="card-header bg-success text-white py-3">
                                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>تعيين طالب لمشرف</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">اختر المشرف والطالب لتعيين الطالب للمشرف</p>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">المشرف</label>
                                        <select id="modalSupervisorSelect" class="form-select" onchange="loadModalStudents()">
                                            <option value="">-- اختر مشرف --</option>
                                            @foreach($supervisors as $supervisor)
                                                <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">الطالب</label>
                                        <select id="modalStudentSelect" class="form-select" onchange="updateAddStudentBtn()">
                                            <option value="">-- اختر مشرف أولاً --</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-success w-100" id="addStudentBtn" onclick="addStudentToSupervisor()" disabled>
                                        <i class="fas fa-user-plus me-2"></i>تعيين طالب
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 border-2" style="border-color: #c0392b;">
                                <div class="card-header bg-danger text-white py-3">
                                    <h5 class="mb-0"><i class="fas fa-user-minus me-2"></i>حذف طالب من المشرف</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">سيتم حذف جميع سجلات الإجازة للطالب</p>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">المشرف</label>
                                        <select id="removeSupervisorSelect" class="form-select" onchange="loadStudentsForRemoval()">
                                            <option value="">-- اختر مشرف --</option>
                                            @foreach($supervisors as $supervisor)
                                                <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">الطالب</label>
                                        <select id="removeStudentSelect" class="form-select" onchange="updateRemoveStudentBtn()">
                                            <option value="">-- اختر مشرف أولاً --</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-danger w-100" id="removeStudentBtn" onclick="removeStudentFromSupervisor()" disabled>
                                        <i class="fas fa-trash me-2"></i>حذف الطالب
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-5" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let studentsWithIjazah = @json($studentsWithIjazah);
let allStudents = @json($allStudents);
let ijazahData = @json($ijazahRecords);

function togglePart(checkbox) {
    const label = checkbox.closest('.part-check-item');
    if (checkbox.checked) {
        label.classList.add('checked');
    } else {
        label.classList.remove('checked');
    }
}

function loadStudents() {
    const supervisorId = document.getElementById('supervisorSelect').value;
    const studentSelect = document.getElementById('studentSelect');
    const submitBtn = document.getElementById('submitBtn');

    studentSelect.innerHTML = '<option value="">-- جاري التحميل --</option>';
    studentSelect.disabled = true;
    submitBtn.disabled = true;

    resetParts();

    if (!supervisorId) {
        studentSelect.innerHTML = '<option value="">-- اختر مشرف أولاً --</option>';
        return;
    }

    const filteredStudents = allStudents.filter(s => s.ijazah_supervisor_id == supervisorId);

    if (filteredStudents.length === 0) {
        studentSelect.innerHTML = '<option value="">-- لا يوجد طلاب --</option>';
        return;
    }

    studentSelect.innerHTML = '<option value="">-- اختر طالب --</option>';
    filteredStudents.forEach(student => {
        const option = document.createElement('option');
        option.value = student.id;
        option.textContent = student.user.name;
        studentSelect.appendChild(option);
    });
    studentSelect.disabled = false;
}

function loadStudentIjazah() {
    const studentId = document.getElementById('studentSelect').value;
    const submitBtn = document.getElementById('submitBtn');

    resetParts();

    if (!studentId) {
        submitBtn.disabled = true;
        return;
    }

    submitBtn.disabled = false;

    if (ijazahData[studentId]) {
        ijazahData[studentId].forEach(record => {
            const label = document.querySelector(`.part-check-item[data-part-id="${record.quran_part_id}"]`);
            if (label) {
                const cb = label.querySelector('input[type="checkbox"]');
                cb.checked = true;
                cb.disabled = true;
                label.classList.add('checked');
                label.style.opacity = '0.5';
            }
        });
    }
}

function resetParts() {
    document.querySelectorAll('.part-check-item').forEach(label => {
        const cb = label.querySelector('input[type="checkbox"]');
        cb.checked = false;
        cb.disabled = false;
        label.classList.remove('checked');
        label.style.opacity = '1';
    });
}

document.getElementById('ijazahForm').addEventListener('submit', function(e) {
    const supervisorId = document.getElementById('supervisorSelect').value;
    const studentId = document.getElementById('studentSelect').value;

    if (!supervisorId || !studentId) {
        e.preventDefault();
        alert('يرجى اختيار المشرف والطالب');
    }
});

function openAddStudentModal() {
    document.getElementById('modalSupervisorSelect').value = '';
    document.getElementById('modalStudentSelect').innerHTML = '<option value="">-- اختر مشرف أولاً --</option>';
    document.getElementById('modalStudentSelect').disabled = true;
    document.getElementById('addStudentBtn').disabled = true;

    var modalEl = document.getElementById('addStudentModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function loadModalStudents() {
    const supervisorId = document.getElementById('modalSupervisorSelect').value;
    const studentSelect = document.getElementById('modalStudentSelect');
    const addStudentBtn = document.getElementById('addStudentBtn');

    studentSelect.innerHTML = '<option value="">-- جاري التحميل --</option>';
    studentSelect.disabled = true;
    addStudentBtn.disabled = true;

    if (!supervisorId) {
        studentSelect.innerHTML = '<option value="">-- اختر مشرف أولاً --</option>';
        return;
    }

    const filteredStudents = allStudents;

    if (filteredStudents.length === 0) {
        studentSelect.innerHTML = '<option value="">-- لا توجد طلاب --</option>';
        return;
    }

    studentSelect.innerHTML = '<option value="">-- اختر طالب --</option>';
    filteredStudents.forEach(student => {
        const option = document.createElement('option');
        option.value = student.id;
        option.textContent = student.user.name + ' (مشرف: ' + (student.supervisor?.name || 'بدون مشرف') + ')' + (student.ijazah_supervisor ? ' - إجازة: ' + student.ijazah_supervisor.name : '');
        studentSelect.appendChild(option);
    });
    studentSelect.disabled = false;
}

function updateAddStudentBtn() {
    const studentId = document.getElementById('modalStudentSelect').value;
    document.getElementById('addStudentBtn').disabled = !studentId;
}

function addStudentToSupervisor() {
    const supervisorId = document.getElementById('modalSupervisorSelect').value;
    const studentId = document.getElementById('modalStudentSelect').value;

    if (!supervisorId || !studentId) {
        alert('يرجى اختيار المشرف والطالب');
        return;
    }

    const formData = new FormData();
    formData.append('student_id', studentId);
    formData.append('supervisor_id', supervisorId);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route('admin.ijazah.assignStudent') }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('supervisorSelect').value = supervisorId;
            loadStudents();
            setTimeout(() => {
                document.getElementById('studentSelect').value = studentId;
                if (document.getElementById('studentSelect').value == studentId) {
                    loadStudentIjazah();
                    document.getElementById('submitBtn').disabled = false;
                }
            }, 200);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addStudentModal')).hide();
            alert('تم تعيين الطالب بنجاح');
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء الاتصال بالخادم');
    });
}

function loadStudentsForRemoval() {
    const supervisorId = document.getElementById('removeSupervisorSelect').value;
    const studentSelect = document.getElementById('removeStudentSelect');
    const removeStudentBtn = document.getElementById('removeStudentBtn');

    studentSelect.innerHTML = '<option value="">-- جاري التحميل --</option>';
    studentSelect.disabled = true;
    removeStudentBtn.disabled = true;

    if (!supervisorId) {
        studentSelect.innerHTML = '<option value="">-- اختر مشرف أولاً --</option>';
        return;
    }

    const filteredStudents = allStudents.filter(s => s.ijazah_supervisor_id == supervisorId);

    if (filteredStudents.length === 0) {
        studentSelect.innerHTML = '<option value="">-- لا يوجد طلاب لهذا المشرف --</option>';
        return;
    }

    studentSelect.innerHTML = '<option value="">-- اختر طالب --</option>';
    filteredStudents.forEach(student => {
        const option = document.createElement('option');
        option.value = student.id;
        option.textContent = student.user.name;
        studentSelect.appendChild(option);
    });
    studentSelect.disabled = false;
}

function updateRemoveStudentBtn() {
    const studentId = document.getElementById('removeStudentSelect').value;
    document.getElementById('removeStudentBtn').disabled = !studentId;
}

function removeStudentFromSupervisor() {
    const studentId = document.getElementById('removeStudentSelect').value;

    if (!studentId) {
        alert('يرجى اختيار الطالب');
        return;
    }

    if (!confirm('هل تريد حذف سجل الإجازة لهذا الطالب؟')) {
        return;
    }

    const formData = new FormData();
    formData.append('student_id', studentId);
    formData.append('supervisor_id', document.getElementById('removeSupervisorSelect').value);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route('admin.ijazah.removeStudent') }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addStudentModal')).hide();
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء الاتصال بالخادم');
    });
}
</script>
@endpush