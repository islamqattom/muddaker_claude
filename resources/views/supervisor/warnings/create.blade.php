@extends('layouts.app')

@section('title', 'إنذار جديد')
@section('page-title', 'إنذار جديد')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.warnings.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">إنذار جديد</li>
@endsection

@section('content')
<form action="{{ route('supervisor.warnings.store') }}" method="POST" id="warningForm">
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
                            <select name="student_id" id="studentSelect" class="form-select @error('student_id') is-invalid @enderror" required>
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
                        <a href="{{ route('supervisor.warnings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
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

    // Show attendance table for attendance or general types
    attendanceDataSection.style.display = (type === 'attendance' || type === 'general') ? 'block' : 'none';

    // Show recitation table for recitation or general types
    recitationDataSection.style.display = (type === 'recitation' || type === 'general') ? 'block' : 'none';
}

// Auto-fetch stats when student is selected
document.getElementById('studentSelect').addEventListener('change', function() {
    const studentId = this.value;
    if (studentId) {
        fetchStudentStats(studentId);
    } else {
        clearStudentStats();
    }
});

// Fetch student stats via AJAX
function fetchStudentStats(studentId) {
    const url = '{{ route("supervisor.warnings.studentStats", ":student") }}'.replace(':student', studentId);

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                toastr.error(data.error);
                return;
            }
            updateStudentPreview(data);
        })
        .catch(error => {
            console.error('Error fetching student stats:', error);
            toastr.error('حدث خطأ أثناء جلب بيانات الطالب');
        });
}

// Update the sidebar with student stats
function updateStudentPreview(data) {
    const previewCard = document.querySelector('.sticky-top .card-body');
    const studentInfo = previewCard.querySelector('.text-center.mb-4');
    if (studentInfo) {
        studentInfo.remove();
    }
    const alerts = previewCard.querySelectorAll('.alert');
    alerts.forEach(el => el.remove());

    const hr = previewCard.querySelector('hr');
    if (hr) {
        hr.remove();
    }

    previewCard.insertBefore(createStudentPreviewHTML(data), previewCard.firstChild);
}

function createStudentPreviewHTML(data) {
    const s = data.student;
    const a = data.attendance_stats;
    const r = data.recitation_stats;

    const wrapper = document.createElement('div');

    const infoDiv = document.createElement('div');
    infoDiv.className = 'text-center mb-4';
    infoDiv.innerHTML = `
        <img src="${s.avatar_url}" alt="Avatar" class="rounded-circle mb-2" style="width:60px;height:60px;object-fit:cover;">
        <h5>${escapeHtml(s.name)}</h5>
        <p class="text-muted mb-1">${escapeHtml(s.student_number)}</p>
        <span class="badge bg-light text-dark">${escapeHtml(s.level_name)}</span>
    `;
    wrapper.appendChild(infoDiv);

    if (a && a.total > 0) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning';
        alertDiv.innerHTML = `
            <h6 class="alert-heading"><i class="fas fa-calendar me-2"></i>إحصائيات الحضور</h6>
            <div class="row text-center g-2">
                <div class="col-6">
                    <div class="text-success fw-bold">${a.present}</div>
                    <small>حاضر</small>
                </div>
                <div class="col-6">
                    <div class="text-danger fw-bold">${a.absent}</div>
                    <small>غائب</small>
                </div>
                <div class="col-6">
                    <div class="text-warning fw-bold">${a.late}</div>
                    <small>متأخر</small>
                </div>
                <div class="col-6">
                    <div class="fw-bold">${a.total}</div>
                    <small>الإجمالي</small>
                </div>
            </div>
        `;
        wrapper.appendChild(alertDiv);
    }

    if (r && r.total > 0) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info';
        alertDiv.innerHTML = `
            <h6 class="alert-heading"><i class="fas fa-microphone me-2"></i>إحصائيات التسميع</h6>
            <div class="row text-center g-2">
                <div class="col-6">
                    <div class="fw-bold">${r.total}</div>
                    <small>الإجمالي</small>
                </div>
                <div class="col-6">
                    <div class="text-success fw-bold">${r.passed}</div>
                    <small>ناجح</small>
                </div>
                <div class="col-6">
                    <div class="text-danger fw-bold">${r.failed}</div>
                    <small>راسب</small>
                </div>
            </div>
        `;
        wrapper.appendChild(alertDiv);
    }

    return wrapper;
}

function clearStudentStats() {
    const previewCard = document.querySelector('.sticky-top .card-body');
    const studentInfo = previewCard.querySelector('.text-center.mb-4');
    if (studentInfo) studentInfo.remove();
    const alerts = previewCard.querySelectorAll('.alert');
    alerts.forEach(el => el.remove());
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Fetch attendance data via AJAX
function fetchAttendance() {
    const studentId = document.getElementById('studentSelect').value;
    if (!studentId) {
        toastr.warning('يرجى اختيار طالب أولاً');
        return;
    }

    const fromDate = document.getElementById('attendanceFromDate').value;
    const url = '{{ route("supervisor.warnings.attendance", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;
    const tableContainer = document.getElementById('attendanceTableContainer');
    const originalContent = tableContainer.innerHTML;
    tableContainer.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><div class="mt-2">جاري التحميل...</div></div>';

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                tableContainer.innerHTML = originalContent;
                toastr.error(data.error);
                return;
            }
            const isEmpty = Array.isArray(data) && data.length === 0;
            if (isEmpty) toastr.info('لا توجد سجلات حضور للفترة المحددة');
            attendanceData = isEmpty ? [] : (Array.isArray(data) ? data : []);
            renderAttendanceTable();
            updateHiddenInputs();
        })
        .catch(error => {
            console.error('Error fetching attendance:', error);
            tableContainer.innerHTML = originalContent;
            toastr.error('حدث خطأ أثناء جلب بيانات الحضور. يرجى المحاولة مرة أخرى.');
        });
}

// Render attendance table
function renderAttendanceTable() {
    const tbody = document.getElementById('attendanceTableBody');
    const container = document.getElementById('attendanceTableContainer');
    const noDataMsg = document.getElementById('noAttendanceMessage');

    // Guard: if elements don't exist (e.g., hidden section not rendered), skip
    if (!tbody || !container || !noDataMsg) return;

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

    attendanceData.forEach(function(record, index) {
        const row = document.createElement('tr');
        // Format date to show only date part (without time)
        const formattedDate = record.date ? (record.date.includes('T') ? record.date.split('T')[0] : record.date) : '-';
        row.innerHTML = `
            <td>${escapeHtml(record.day || '-')}</td>
            <td>${escapeHtml(formattedDate)}</td>
            <td><span class="badge bg-${getStatusColor(record.status)}">${escapeHtml(statusLabels[record.status] || record.status)}</span></td>
            <td>${escapeHtml(record.arrival_time || '-')}</td>
            <td>${escapeHtml(record.notes || '-')}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteAttendanceRow(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getStatusColor(status) {
    const colors = {
        'present': 'success',
        'absent': 'danger',
        'late': 'warning',
        'excused': 'info',
        'holiday': 'secondary'
    };
    return colors[status] || 'secondary';
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
        toastr.warning('يرجى اختيار طالب أولاً');
        return;
    }

    const fromDate = document.getElementById('recitationFromDate').value;
    const url = '{{ route("supervisor.warnings.recitations", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;
    const tableContainer = document.getElementById('recitationTableContainer');
    const originalContent = tableContainer.innerHTML;
    tableContainer.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><div class="mt-2">جاري التحميل...</div></div>';

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                tableContainer.innerHTML = originalContent;
                toastr.error(data.error);
                return;
            }
            const isEmpty = Array.isArray(data) && data.length === 0;
            if (isEmpty) toastr.info('لا توجد سجلات تسميع للفترة المحددة');
            recitationData = isEmpty ? [] : (Array.isArray(data) ? data : []);
            renderRecitationTable();
            updateHiddenInputs();
        })
        .catch(error => {
            console.error('Error fetching recitations:', error);
            tableContainer.innerHTML = originalContent;
            toastr.error('حدث خطأ أثناء جلب بيانات التسميع. يرجى المحاولة مرة أخرى.');
        });
}

// Render recitation table
function renderRecitationTable() {
    const tbody = document.getElementById('recitationTableBody');
    const container = document.getElementById('recitationTableContainer');
    const noDataMsg = document.getElementById('noRecitationMessage');

    // Guard: if elements don't exist (e.g., hidden section not rendered), skip
    if (!tbody || !container || !noDataMsg) return;

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
        const row = document.createElement('tr');
        // Format date to show only date part (without time)
        const formattedDate = record.session_date ? (record.session_date.includes('T') ? record.session_date.split('T')[0] : record.session_date) : '-';
        // Get existing notes or empty string for manual entry
        const existingNotes = record.notes || '';
        // Use passed_pages and failed_pages from API (calculated from pages table)
        const passedPages = record.passed_pages || 0;
        const failedPages = record.failed_pages || 0;
        const totalPages = record.total_pages || 0;

        row.innerHTML = `
            <td>${escapeHtml(record.day || '-')}</td>
            <td>${escapeHtml(formattedDate)}</td>
            <td>${escapeHtml(typeLabels[record.type] || record.type)}</td>
            <td>${totalPages}</td>
            <td>${passedPages}</td>
            <td>${failedPages}</td>
            <td>
                <input type="text" class="form-control form-control-sm"
                    placeholder="أضف ملاحظات..."
                    value="${escapeHtml(existingNotes)}"
                    onchange="updateRecitationNotes(${index}, this.value)">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteRecitationRow(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
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

// Add a new test row
function addTestRow() {
    const tbody = document.getElementById('testTableBody');
    const rowIndex = testData.length;

    testData.push({ test: '', notes: '' });

    const row = document.createElement('tr');
    row.id = 'testRow_' + rowIndex;
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm"
                placeholder="أدخل اسم الاختبار..."
                onchange="updateTestRow(${rowIndex}, 'test', this.value)">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm"
                placeholder="أضف ملاحظات..."
                onchange="updateTestRow(${rowIndex}, 'notes', this.value)">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTestRow(${rowIndex})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
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
        const row = document.createElement('tr');
        row.id = 'testRow_' + index;
        row.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm"
                    placeholder="أدخل اسم الاختبار..."
                    value="${escapeHtml(item.test || '')}"
                    onchange="updateTestRow(${index}, 'test', this.value)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm"
                    placeholder="أضف ملاحظات..."
                    value="${escapeHtml(item.notes || '')}"
                    onchange="updateTestRow(${index}, 'notes', this.value)">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteTestRow(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleWarningFields();
    updateHiddenInputs();
});
</script>
@endpush
