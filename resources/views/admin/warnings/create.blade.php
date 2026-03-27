@extends('layouts.app')
@section('title', 'إضافة إنذار')
@section('page-title', 'إضافة إنذار')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.warnings.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">إضافة إنذار</li>
@endsection

@section('content')
<form action="{{ route('admin.warnings.store') }}" method="POST" id="warningForm">
    @csrf
    <input type="hidden" name="attendance_data" id="attendanceData">
    <input type="hidden" name="recitation_data" id="recitationData">
    <input type="hidden" name="test_data" id="testData">
    <div class="row">
        <!-- Student Selection -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>معلومات الطالب</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اختر الطالب <span class="text-danger">*</span></label>
                            <select name="student_id" id="studentSelect" class="form-select select2 @error('student_id') is-invalid @enderror" required>
                                <option value="">-- اختر الطالب --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->user->name }} - {{ $student->student_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">نوع الإنذار <span class="text-danger">*</span></label>
                            <select name="type" id="typeSelect" class="form-select @error('type') is-invalid @enderror" required onchange="toggleWarningFields()">
                                <option value="">-- اختر النوع --</option>
                                <option value="attendance" {{ old('type') == 'attendance' ? 'selected' : '' }}>إنذار حضور</option>
                                <option value="recitation" {{ old('type') == 'recitation' ? 'selected' : '' }}>إنذار تسميع</option>
                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>إنذار عام</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">تاريخ الإنذار <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Data Section -->
            <div class="card mb-4" id="attendanceDataSection" style="display: none;">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0 text-warning"><i class="fas fa-calendar-alt me-2"></i>سجل الحضور (اختياري)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">بدء من تاريخ</label>
                            <input type="date" class="form-control" id="attendanceFromDate" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning" onclick="fetchAttendance()">
                                <i class="fas fa-search me-1"></i>بحث
                            </button>
                        </div>
                    </div>
                    <div class="mt-3" id="attendanceTableContainer" style="display: none;">
                        <table class="table table-sm table-bordered" id="attendanceTable">
                            <thead class="table-light">
                                <tr>
                                    <th>اليوم</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>وقت الحضور</th>
                                    <th>الملاحظات</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                            </tbody>
                        </table>
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            يمكنك حذف أي صف غير مرغوب فيه قبل إصدار الإنذار
                        </div>
                    </div>
                    <div class="mt-2" id="noAttendanceMessage" style="display: none;">
                        <div class="alert alert-info mb-0">لا توجد سجلات حضور للفترة المحددة</div>
                    </div>
                </div>
            </div>

            <!-- Recitation Data Section -->
            <div class="card mb-4" id="recitationDataSection" style="display: none;">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="mb-0 text-info"><i class="fas fa-microphone-alt me-2"></i>سجل التسميع (اختياري)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">بدء من تاريخ</label>
                            <input type="date" class="form-control" id="recitationFromDate" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info" onclick="fetchRecitations()">
                                <i class="fas fa-search me-1"></i>بحث
                            </button>
                        </div>
                    </div>
                    <div class="mt-3" id="recitationTableContainer" style="display: none;">
                        <table class="table table-sm table-bordered" id="recitationTable">
                            <thead class="table-light">
                                <tr>
                                    <th>اليوم</th>
                                    <th>التاريخ</th>
                                    <th>نوع التسميع</th>
                                    <th>المطلوب تسميعه</th>
                                    <th>الصفحات التي نجح بها</th>
                                    <th>الصفحات التي رسب بها</th>
                                    <th>الملاحظات</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="recitationTableBody">
                            </tbody>
                        </table>
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            يمكنك حذف أي صف غير مرغوب فيه قبل إصدار الإنذار
                        </div>
                    </div>
                    <div class="mt-2" id="noRecitationMessage" style="display: none;">
                        <div class="alert alert-info mb-0">لا توجد سجلات تسميع للفترة المحددة</div>
                    </div>
                </div>
            </div>

            <!-- Tests Data Section -->
            <div class="card mb-4" id="testDataSection">
                <div class="card-header bg-primary bg-opacity-10">
                    <h6 class="mb-0 text-primary"><i class="fas fa-clipboard-list me-2"></i>الاختبارات</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="testTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45%;">الاختبار</th>
                                    <th style="width: 45%;">ملاحظات</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="testTableBody">
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addTestRow()">
                        <i class="fas fa-plus me-1"></i>إضافة صف
                    </button>
                    <div class="text-muted small mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        يمكنك إضافة صفوف متعددة للاختبارات
                    </div>
                </div>
            </div>

            <!-- Warning Content -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>محتوى الإنذار</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">عنوان الإنذار <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="مثال: إنذار غياب متكرر" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">محتوى الإنذار <span class="text-danger">*</span></label>
                        <textarea name="content" rows="6" class="form-control @error('content') is-invalid @enderror" placeholder="اكتب تفاصيل الإنذار..." required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات إضافية</label>
                        <textarea name="additional_notes" rows="3" class="form-control" placeholder="أي ملاحظات إضافية...">{{ old('additional_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Stats Preview -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>معاينة</h6>
                </div>
                <div class="card-body">
                    @if($selectedStudent)
                        <div class="text-center mb-4">
                            <img src="{{ $selectedStudent->user->avatar_url }}" alt="Avatar" class="rounded-circle mb-2" style="width:60px;height:60px;object-fit:cover;">
                            <h5>{{ $selectedStudent->user->name }}</h5>
                            <p class="text-muted mb-1">{{ $selectedStudent->student_number }}</p>
                            <span class="badge bg-light text-dark">{{ $selectedStudent->level->name ?? '-' }}</span>
                        </div>

                        @if($attendanceStats)
                            <div class="alert alert-warning">
                                <h6 class="alert-heading"><i class="fas fa-calendar me-2"></i>إحصائيات الحضور</h6>
                                <div class="row text-center g-2">
                                    <div class="col-6">
                                        <div class="text-success fw-bold">{{ $attendanceStats['present'] }}</div>
                                        <small>حاضر</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-danger fw-bold">{{ $attendanceStats['absent'] }}</div>
                                        <small>غائب</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-warning fw-bold">{{ $attendanceStats['late'] }}</div>
                                        <small>متأخر</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="fw-bold">{{ $attendanceStats['total'] }}</div>
                                        <small>الإجمالي</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($recitationStats)
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-microphone me-2"></i>إحصائيات التسميع</h6>
                                <div class="row text-center g-2">
                                    <div class="col-6">
                                        <div class="fw-bold">{{ $recitationStats['total'] }}</div>
                                        <small>الإجمالي</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-success fw-bold">{{ $recitationStats['passed'] }}</div>
                                        <small>ناجح</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-danger fw-bold">{{ $recitationStats['failed'] }}</div>
                                        <small>راسب</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                            <p>اختر طالباً لعرض الإحصائيات</p>
                        </div>
                    @endif

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>إصدار الإنذار
                        </button>
                        <a href="{{ route('admin.warnings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template for attendance table rows -->
<template id="attendanceRowTemplate">
    <tr>
        <td class="att-day"></td>
        <td class="att-date"></td>
        <td class="att-status"></td>
        <td class="att-time"></td>
        <td class="att-notes"></td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteAttendanceRow(__INDEX__)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Template for recitation table rows -->
<template id="recitationRowTemplate">
    <tr>
        <td class="rec-day"></td>
        <td class="rec-date"></td>
        <td class="rec-type"></td>
        <td class="rec-total"></td>
        <td class="rec-passed"></td>
        <td class="rec-failed"></td>
        <td class="rec-notes"></td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteRecitationRow(__INDEX__)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let attendanceData = [];
let recitationData = [];
let testData = [];

function toggleWarningFields() {
    const type = document.getElementById('typeSelect').value;
    const attendanceDataSection = document.getElementById('attendanceDataSection');
    const recitationDataSection = document.getElementById('recitationDataSection');

    attendanceDataSection.style.display = (type === 'attendance' || type === 'general') ? 'block' : 'none';
    recitationDataSection.style.display = (type === 'recitation' || type === 'general') ? 'block' : 'none';
}

// Auto-fetch stats when student is selected
document.getElementById('studentSelect').addEventListener('change', function() {
    const studentId = this.value;
    if (studentId) {
        window.location = '{{ route("admin.warnings.create") }}?student_id=' + studentId;
    }
});

// Check if student is already selected on page load and reload if stats are missing
document.addEventListener('DOMContentLoaded', function() {
    const studentSelect = document.getElementById('studentSelect');
    const studentId = studentSelect.value;

    if (studentId && !document.getElementById('attendanceStats') && !document.querySelector('.alert-warning')) {
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('student_id')) {
            window.location = '{{ route("admin.warnings.create") }}?student_id=' + studentId;
        }
    }
});

// Set text content safely (prevents XSS)
function setText(el, text) {
    el.textContent = text || '-';
}

// Set HTML-safe text using createElement approach
function createTextSpan(text) {
    const span = document.createElement('span');
    span.textContent = text || '-';
    return span;
}

// Fetch attendance data via AJAX
function fetchAttendance() {
    const studentId = document.getElementById('studentSelect').value;
    if (!studentId) {
        alert('يرجى اختيار طالب أولاً');
        return;
    }

    const fromDate = document.getElementById('attendanceFromDate').value;
    const url = '{{ route("admin.warnings.attendance", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            attendanceData = data;
            renderAttendanceTable();
            updateHiddenInputs();
        })
        .catch(error => {
            console.error('Error fetching attendance:', error);
            alert('حدث خطأ أثناء جلب بيانات الحضور');
        });
}

// Render attendance table using template cloning
function renderAttendanceTable() {
    const tbody = document.getElementById('attendanceTableBody');
    const container = document.getElementById('attendanceTableContainer');
    const noDataMsg = document.getElementById('noAttendanceMessage');
    const template = document.getElementById('attendanceRowTemplate');

    tbody.innerHTML = '';

    if (attendanceData.length === 0) {
        container.style.display = 'none';
        noDataMsg.style.display = 'block';
        return;
    }

    container.style.display = 'block';
    noDataMsg.style.display = 'none';

    const statusLabels = {
        'present': 'حاضر',
        'absent': 'غائب',
        'late': 'متأخر',
        'excused': 'غائب بعذر',
        'holiday': 'عطلة'
    };
    const statusColors = {
        'present': 'success',
        'absent': 'danger',
        'late': 'warning',
        'excused': 'info',
        'holiday': 'secondary'
    };

    attendanceData.forEach(function(record, index) {
        const clone = template.content.cloneNode(true);
        const tr = clone.querySelector('tr');

        // Set delete button index
        const delBtn = clone.querySelector('.btn-danger');
        delBtn.setAttribute('onclick', 'deleteAttendanceRow(' + index + ')');

        // Set cell contents using textContent (safe)
        setText(clone.querySelector('.att-day'), record.day);
        setText(clone.querySelector('.att-date'), record.date ? record.date.split('T')[0] : '-');
        setText(clone.querySelector('.att-time'), record.arrival_time || '-');
        setText(clone.querySelector('.att-notes'), record.notes || '-');

        // Status badge
        const statusCell = clone.querySelector('.att-status');
        const badge = document.createElement('span');
        badge.className = 'badge bg-' + (statusColors[record.status] || 'secondary');
        badge.textContent = statusLabels[record.status] || record.status || '-';
        statusCell.appendChild(badge);

        tbody.appendChild(clone);
    });
}

function deleteAttendanceRow(index) {
    attendanceData.splice(index, 1);
    renderAttendanceTable();
    updateHiddenInputs();
}

// Fetch recitation data via AJAX
function fetchRecitations() {
    const studentId = document.getElementById('studentSelect').value;
    if (!studentId) {
        alert('يرجى اختيار طالب أولاً');
        return;
    }

    const fromDate = document.getElementById('recitationFromDate').value;
    const url = '{{ route("admin.warnings.recitations", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            recitationData = data;
            renderRecitationTable();
            updateHiddenInputs();
        })
        .catch(error => {
            console.error('Error fetching recitations:', error);
            alert('حدث خطأ أثناء جلب بيانات التسميع');
        });
}

// Render recitation table using template cloning
function renderRecitationTable() {
    const tbody = document.getElementById('recitationTableBody');
    const container = document.getElementById('recitationTableContainer');
    const noDataMsg = document.getElementById('noRecitationMessage');
    const template = document.getElementById('recitationRowTemplate');

    tbody.innerHTML = '';

    if (recitationData.length === 0) {
        container.style.display = 'none';
        noDataMsg.style.display = 'block';
        return;
    }

    container.style.display = 'block';
    noDataMsg.style.display = 'none';

    const typeLabels = {
        'new_memorization': 'حفظ جديد',
        'review': 'مراجعة',
        'unmastered': 'حفظ غير متقن',
        'recitation': 'سرد',
        'mafsil': 'مفصل',
        'not_recitate': 'لم يسمع'
    };

    recitationData.forEach(function(record, index) {
        const clone = template.content.cloneNode(true);

        // Set delete button index
        const delBtn = clone.querySelector('.btn-danger');
        delBtn.setAttribute('onclick', 'deleteRecitationRow(' + index + ')');

        // Set cell contents using textContent (safe)
        setText(clone.querySelector('.rec-day'), record.day);
        setText(clone.querySelector('.rec-date'), record.session_date ? record.session_date.split('T')[0] : '-');
        setText(clone.querySelector('.rec-type'), typeLabels[record.type] || record.type || '-');
        setText(clone.querySelector('.rec-total'), record.total_pages || 0);
        setText(clone.querySelector('.rec-passed'), record.passed_pages || 0);
        setText(clone.querySelector('.rec-failed'), record.failed_pages || 0);

        // Notes input
        const notesCell = clone.querySelector('.rec-notes');
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.placeholder = 'أضف ملاحظات...';
        input.value = record.notes || '';
        input.addEventListener('change', function() {
            updateRecitationNotes(index, input.value);
        });
        notesCell.appendChild(input);

        tbody.appendChild(clone);
    });
}

function updateRecitationNotes(index, notes) {
    recitationData[index].notes = notes;
    updateHiddenInputs();
}

function deleteRecitationRow(index) {
    recitationData.splice(index, 1);
    renderRecitationTable();
    updateHiddenInputs();
}

function updateHiddenInputs() {
    document.getElementById('attendanceData').value = JSON.stringify(attendanceData);
    document.getElementById('recitationData').value = JSON.stringify(recitationData);
    document.getElementById('testData').value = JSON.stringify(testData);
}

// Test rows using textContent (no innerHTML)
function addTestRow() {
    const tbody = document.getElementById('testTableBody');
    const rowIndex = testData.length;
    testData.push({ test: '', notes: '' });

    const tr = document.createElement('tr');
    tr.id = 'testRow_' + rowIndex;

    const td1 = document.createElement('td');
    const input1 = document.createElement('input');
    input1.type = 'text';
    input1.className = 'form-control form-control-sm';
    input1.placeholder = 'أدخل اسم الاختبار...';
    input1.addEventListener('change', function() { updateTestRow(rowIndex, 'test', input1.value); });
    td1.appendChild(input1);

    const td2 = document.createElement('td');
    const input2 = document.createElement('input');
    input2.type = 'text';
    input2.className = 'form-control form-control-sm';
    input2.placeholder = 'أضف ملاحظات...';
    input2.addEventListener('change', function() { updateTestRow(rowIndex, 'notes', input2.value); });
    td2.appendChild(input2);

    const td3 = document.createElement('td');
    const delBtn = document.createElement('button');
    delBtn.type = 'button';
    delBtn.className = 'btn btn-sm btn-danger';
    delBtn.innerHTML = '<i class="fas fa-trash"></i>';
    delBtn.addEventListener('click', function() { deleteTestRow(rowIndex); });
    td3.appendChild(delBtn);

    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);
    tbody.appendChild(tr);
}

function updateTestRow(index, field, value) {
    if (testData[index]) {
        testData[index][field] = value;
        updateHiddenInputs();
    }
}

function deleteTestRow(index) {
    testData.splice(index, 1);
    renderTestTable();
    updateHiddenInputs();
}

function renderTestTable() {
    const tbody = document.getElementById('testTableBody');
    tbody.innerHTML = '';
    testData.forEach(function(item, index) {
        const tr = document.createElement('tr');
        tr.id = 'testRow_' + index;

        const td1 = document.createElement('td');
        const input1 = document.createElement('input');
        input1.type = 'text';
        input1.className = 'form-control form-control-sm';
        input1.placeholder = 'أدخل اسم الاختبار...';
        input1.value = item.test || '';
        input1.addEventListener('change', function() { updateTestRow(index, 'test', input1.value); });
        td1.appendChild(input1);

        const td2 = document.createElement('td');
        const input2 = document.createElement('input');
        input2.type = 'text';
        input2.className = 'form-control form-control-sm';
        input2.placeholder = 'أضف ملاحظات...';
        input2.value = item.notes || '';
        input2.addEventListener('change', function() { updateTestRow(index, 'notes', input2.value); });
        td2.appendChild(input2);

        const td3 = document.createElement('td');
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-sm btn-danger';
        delBtn.innerHTML = '<i class="fas fa-trash"></i>';
        delBtn.addEventListener('click', function() { deleteTestRow(index); });
        td3.appendChild(delBtn);

        tr.appendChild(td1);
        tr.appendChild(td2);
        tr.appendChild(td3);
        tbody.appendChild(tr);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleWarningFields();
    updateHiddenInputs();
});
</script>
@endpush
