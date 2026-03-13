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
                                    <option value="{{ $student->id }}" {{ old('student_id', request('student_id')) == $student->id ? 'selected' : '' }}>
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

                    <!-- Date Range -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from_date" class="form-control" value="{{ old('from_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control" value="{{ old('to_date') }}">
                        </div>
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

            <!-- Attendance Fields -->
            <div class="card mb-4" id="attendanceFields" style="display: none;">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0 text-warning"><i class="fas fa-calendar-times me-2"></i>إحصائيات الحضور والغياب</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">أيام الحضور</label>
                            <input type="number" name="present_days" class="form-control" value="0" min="0" readonly id="presentDays">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">أيام الغياب <span class="text-danger">*</span></label>
                            <input type="number" name="absent_days" class="form-control" value="{{ old('absent_days', 0) }}" min="0" id="absentDays" onchange="calculateTotalDays()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">أيام التأخير</label>
                            <input type="number" name="late_days" class="form-control" value="{{ old('late_days', 0) }}" min="0" id="lateDays" onchange="calculateTotalDays()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">إجمالي الأيام</label>
                            <input type="number" name="total_days" class="form-control" value="{{ old('total_days', 0) }}" min="0" readonly id="totalDays">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recitation Fields -->
            <div class="card mb-4" id="recitationFields" style="display: none;">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="mb-0 text-info"><i class="fas fa-microphone me-2"></i>إحصائيات التسميع</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">إجمالي الجلسات</label>
                            <input type="number" name="total_sessions" class="form-control" value="{{ old('total_sessions', 0) }}" min="0" id="totalSessions" onchange="calculateRecitationStats()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">جلسات ناجحة</label>
                            <input type="number" name="passed_sessions" class="form-control" value="0" min="0" readonly id="passedSessions">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">جلسات راسبة <span class="text-danger">*</span></label>
                            <input type="number" name="failed_sessions" class="form-control" value="{{ old('failed_sessions', 0) }}" min="0" id="failedSessions" onchange="calculateRecitationStats()">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Data Section -->
            <div class="card mb-4" id="attendanceDataSection">
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
            <div class="card mb-4" id="recitationDataSection">
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
                    @if($student)
                        <div class="text-center mb-4">
                            <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle mb-2" style="width:60px;height:60px;object-fit:cover;">
                            <h5>{{ $student->user->name }}</h5>
                            <p class="text-muted mb-1">{{ $student->student_number }}</p>
                            <span class="badge bg-light text-dark">{{ $student->level->name ?? '-' }}</span>
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

function toggleWarningFields() {
    const type = document.getElementById('typeSelect').value;
    const attendanceFields = document.getElementById('attendanceFields');
    const recitationFields = document.getElementById('recitationFields');

    attendanceFields.style.display = (type === 'attendance') ? 'block' : 'none';
    recitationFields.style.display = (type === 'recitation') ? 'block' : 'none';
}

function calculateTotalDays() {
    const absent = parseInt(document.getElementById('absentDays').value) || 0;
    const late = parseInt(document.getElementById('lateDays').value) || 0;
    document.getElementById('totalDays').value = absent + late;
}

function calculateRecitationStats() {
    const total = parseInt(document.getElementById('totalSessions').value) || 0;
    const failed = parseInt(document.getElementById('failedSessions').value) || 0;
    document.getElementById('passedSessions').value = total - failed;
}

// Auto-fetch stats when student is selected
document.getElementById('studentSelect').addEventListener('change', function() {
    const studentId = this.value;
    if (studentId) {
        const fromDate = document.querySelector('input[name="from_date"]').value || '{{ date("Y-m-01") }}';
        const toDate = document.querySelector('input[name="to_date"]').value || '{{ date("Y-m-d") }}';
        window.location = '{{ route("supervisor.warnings.create") }}?student_id=' + studentId + '&from_date=' + fromDate + '&to_date=' + toDate;
    }
});

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
        alert('يرجى اختيار طالب أولاً');
        return;
    }

    const fromDate = document.getElementById('attendanceFromDate').value;
    const url = '{{ route("supervisor.warnings.attendance", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;

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

// Render attendance table
function renderAttendanceTable() {
    const tbody = document.getElementById('attendanceTableBody');
    const container = document.getElementById('attendanceTableContainer');
    const noDataMsg = document.getElementById('noAttendanceMessage');

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
        const formattedDate = record.date ? record.date.split('T')[0] : '-';
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
        alert('يرجى اختيار طالب أولاً');
        return;
    }

    const fromDate = document.getElementById('recitationFromDate').value;
    const url = '{{ route("supervisor.warnings.recitations", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;

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

// Render recitation table
function renderRecitationTable() {
    const tbody = document.getElementById('recitationTableBody');
    const container = document.getElementById('recitationTableContainer');
    const noDataMsg = document.getElementById('noRecitationMessage');

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
        const passedPages = (record.total_pages || 0) - (record.unmastered_pages_count || 0);
        // Format date to show only date part (without time)
        const formattedDate = record.session_date ? record.session_date.split('T')[0] : '-';
        // Get existing notes or empty string for manual entry
        const existingNotes = record.notes || '';
        row.innerHTML = `
            <td>${escapeHtml(record.day || '-')}</td>
            <td>${escapeHtml(formattedDate)}</td>
            <td>${escapeHtml(typeLabels[record.type] || record.type)}</td>
            <td>${passedPages}</td>
            <td>${record.unmastered_pages_count || 0}</td>
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
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleWarningFields();
    calculateTotalDays();
    calculateRecitationStats();
    updateHiddenInputs();
});
</script>
@endpush
