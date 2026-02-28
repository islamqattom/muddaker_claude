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
                            <div class="student-avatar mx-auto mb-2" style="width:60px;height:60px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:24px;">
                                {{ mb_substr($student->user->name, 0, 1) }}
                            </div>
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleWarningFields();
    calculateTotalDays();
    calculateRecitationStats();
});
</script>
@endpush
