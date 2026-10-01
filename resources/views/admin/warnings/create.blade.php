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
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
}
body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }
.form-card {
    background: #fff;
    border-radius: 13px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 16px;
}
.form-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.form-header i { color: var(--gold); font-size: 20px; }
.form-body { padding: 16px 16px; }
.form-group { margin-bottom: 16px; }
.form-label {
    font-weight: 600; color: #5a6c7d;
    margin-bottom: 10px; display: block;
    font-size: 14px;
}
.form-label i { color: var(--gold); margin-left: 6px; }
.form-label .required { color: var(--danger); }
.form-control, .form-select {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
}
.form-control:focus, .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212,160,48,0.12);
}
.form-control.is-invalid, .form-select.is-invalid {
    border-color: var(--danger);
}
.invalid-feedback { color: var(--danger); font-size: 13px; margin-top: 5px; }
.section-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(24,43,72,0.1), transparent);
    margin: 25px 0;
}
.data-section {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    border: 1px solid rgba(24,43,72,0.08);
    border-radius: 13px;
    padding: 16px;
    margin-bottom: 16px;
}
.data-section-header {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 15px;
    font-weight: 700; color: var(--primary);
    font-size: 15px;
}
.data-section-header i { color: var(--gold); }
.data-table {
    width: 100%; border-collapse: collapse;
}
.data-table th {
    padding: 10px 12px;
    font-size: 11px; font-weight: 700;
    color: var(--primary);
    text-align: center;
    background: rgba(24,43,72,0.05);
    border-bottom: 1px solid rgba(24,43,72,0.08);
}
.data-table td {
    padding: 10px 12px;
    font-size: 13px; color: #5a6c7d;
    text-align: center;
    border-bottom: 1px solid rgba(24,43,72,0.05);
}
.data-table tbody tr:hover { background: rgba(212,160,48,0.03); }
.data-table tbody tr:last-child td { border-bottom: none; }
.btn-fetch {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 16px;
    background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%);
    color: #fff; border: none; border-radius: 10px;
    font-weight: 600; font-size: 13px;
    transition: all 0.3s ease;
}
.btn-fetch:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(52,152,219,0.3); }
.sidebar-card {
    background: #fff;
    border-radius: 13px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    position: sticky;
    top: 20px;
}
.sidebar-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.05) 0%, rgba(26,51,86,0.08) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.sidebar-header i { color: var(--gold); font-size: 18px; }
.sidebar-body { padding: 16px 16px; }
.student-preview {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 13px;
    padding: 16px;
    text-align: center;
    margin-bottom: 16px;
}
.student-avatar {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
    font-size: 28px; color: #fff;
}
.student-name { font-size: 17px; font-weight: 700; color: var(--primary); }
.stat-box {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    margin-bottom: 12px;
}
.stat-box.warning-type { background: rgba(243,156,18,0.1); border: 1px solid rgba(243,156,18,0.2); }
.stat-box.info-type { background: rgba(52,152,219,0.1); border: 1px solid rgba(52,152,219,0.2); }
.stat-box .stat-value { font-size: 22px; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 5px; }
.stat-box .stat-value.success { color: var(--success); }
.stat-box .stat-value.danger { color: var(--danger); }
.action-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    padding: 14px 16px; border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
    width: 100%;
}
.action-btn.submit {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.action-btn.submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); }
.action-btn.cancel {
    background: transparent;
    color: #7f8c8d; border: 2px solid #e9ecef;
}
.action-btn.cancel:hover { border-color: var(--danger); color: var(--danger); }
</style>

<form action="{{ route('admin.warnings.store') }}" method="POST" id="warningForm">
    @csrf
    <input type="hidden" name="attendance_data" id="attendanceData">
    <input type="hidden" name="recitation_data" id="recitationData">
    <input type="hidden" name="test_data" id="testData">

    <x-page-hero icon="fas fa-exclamation-triangle">
        <h1 class="page-banner-title">إضافة إنذار جديد</h1>
        <p class="page-banner-sub">إصدار إنذار لطالب في جمعية تحفيظ القرآن الكريم</p>
    </x-page-hero>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-user-graduate"></i>
                    <span>معلومات الطالب</span>
                </div>
                <div class="form-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user"></i>اختر الطالب
                                <span class="required">*</span>
                            </label>
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
                            <label class="form-label">
                                <i class="fas fa-exclamation-circle"></i>نوع الإنذار
                                <span class="required">*</span>
                            </label>
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
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-calendar-day"></i>تاريخ الإنذار
                                <span class="required">*</span>
                            </label>
                            <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card" id="attendanceDataSection" style="display:none">
                <div class="form-header">
                    <i class="fas fa-calendar-alt" style="color:var(--warning)"></i>
                    <span>سجل الحضور (اختياري)</span>
                </div>
                <div class="form-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-calendar"></i>بدء من تاريخ</label>
                            <input type="date" class="form-control" id="attendanceFromDate" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn-fetch" onclick="fetchAttendance()">
                                <i class="fas fa-search"></i>بحث
                            </button>
                        </div>
                    </div>
                    <div class="mt-3" id="attendanceTableContainer" style="display:none">
                        <div class="table-responsive">
                            <table class="data-table" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th>اليوم</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>وقت الحضور</th>
                                        <th>الملاحظات</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTableBody"></tbody>
                            </table>
                        </div>
                        <div class="text-muted small mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            يمكنك حذف أي صف غير مرغوب فيه قبل إصدار الإنذار
                        </div>
                    </div>
                    <div class="mt-2" id="noAttendanceMessage" style="display:none">
                        <div class="alert alert-info mb-0">لا توجد سجلات حضور للفترة المحددة</div>
                    </div>
                </div>
            </div>

            <div class="form-card" id="recitationDataSection" style="display:none">
                <div class="form-header">
                    <i class="fas fa-microphone" style="color:var(--info)"></i>
                    <span>سجل التسميع (اختياري)</span>
                </div>
                <div class="form-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-calendar"></i>بدء من تاريخ</label>
                            <input type="date" class="form-control" id="recitationFromDate" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn-fetch" onclick="fetchRecitations()">
                                <i class="fas fa-search"></i>بحث
                            </button>
                        </div>
                    </div>
                    <div class="mt-3" id="recitationTableContainer" style="display:none">
                        <div class="table-responsive">
                            <table class="data-table" id="recitationTable">
                                <thead>
                                    <tr>
                                        <th>اليوم</th>
                                        <th>التاريخ</th>
                                        <th>نوع التسميع</th>
                                        <th>المطلوب</th>
                                        <th>نجح</th>
                                        <th>رسب</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="recitationTableBody"></tbody>
                            </table>
                        </div>
                        <div class="text-muted small mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            يمكنك حذف أي صف غير مرغوب فيه قبل إصدار الإنذار
                        </div>
                    </div>
                    <div class="mt-2" id="noRecitationMessage" style="display:none">
                        <div class="alert alert-info mb-0">لا توجد سجلات تسميع للفترة المحددة</div>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-clipboard-list"></i>
                    <span>الاختبارات</span>
                </div>
                <div class="form-body">
                    <div class="table-responsive">
                        <table class="data-table" id="testTable">
                            <thead>
                                <tr>
                                    <th style="width:45%">الاختبار</th>
                                    <th style="width:45%">ملاحظات</th>
                                    <th style="width:10%"></th>
                                </tr>
                            </thead>
                            <tbody id="testTableBody"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn-fetch mt-2" onclick="addTestRow()">
                        <i class="fas fa-plus"></i>إضافة صف
                    </button>
                </div>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-file-alt"></i>
                    <span>محتوى الإنذار</span>
                </div>
                <div class="form-body">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-heading"></i>عنوان الإنذار
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="مثال: إنذار غياب متكرر" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-file-alt"></i>محتوى الإنذار
                            <span class="required">*</span>
                        </label>
                        <textarea name="content" rows="5" class="form-control @error('content') is-invalid @enderror" placeholder="اكتب تفاصيل الإنذار..." required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label"><i class="fas fa-sticky-note"></i>ملاحظات إضافية</label>
                        <textarea name="additional_notes" rows="3" class="form-control" placeholder="أي ملاحظات إضافية...">{{ old('additional_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <i class="fas fa-chart-bar"></i>
                    <span>معاينة وإحصائيات</span>
                </div>
                <div class="sidebar-body">
                    @if($selectedStudent)
                        <div class="student-preview">
                            <div class="student-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="student-name">{{ $selectedStudent->user->name }}</div>
                            <div class="student-number">{{ $selectedStudent->student_number }}</div>
                        </div>

                        @if($attendanceStats)
                            <div class="stat-box warning-type">
                                <div class="stat-label" style="color:var(--warning);margin-bottom:10px">
                                    <i class="fas fa-calendar-times me-1"></i>إحصائيات الحضور
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="stat-value success">{{ $attendanceStats['present'] }}</div>
                                        <div class="stat-label">حاضر</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-value danger">{{ $attendanceStats['absent'] }}</div>
                                        <div class="stat-label">غائب</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-value" style="color:var(--warning)">{{ $attendanceStats['late'] }}</div>
                                        <div class="stat-label">متأخر</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-value">{{ $attendanceStats['total'] }}</div>
                                        <div class="stat-label">الإجمالي</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($recitationStats)
                            <div class="stat-box info-type">
                                <div class="stat-label" style="color:var(--info);margin-bottom:10px">
                                    <i class="fas fa-microphone me-1"></i>إحصائيات التسميع
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="stat-value">{{ $recitationStats['total'] }}</div>
                                        <div class="stat-label">الإجمالي</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-value success">{{ $recitationStats['passed'] }}</div>
                                        <div class="stat-label">ناجح</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-value danger">{{ $recitationStats['failed'] }}</div>
                                        <div class="stat-label">راسب</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div style="text-align:center;padding:16px 0;color:#95a5a6">
                            <i class="fas fa-user-graduate fa-2x mb-2 d-block" style="opacity:0.4"></i>
                            <p>اختر طالباً لعرض الإحصائيات</p>
                        </div>
                    @endif

                    <div class="section-divider"></div>

                    <div style="display:flex;flex-direction:column;gap:12px">
                        <button type="submit" class="action-btn submit">
                            <i class="fas fa-save"></i>إصدار الإنذار
                        </button>
                        <a href="{{ route('admin.warnings.index') }}" class="action-btn cancel">
                            <i class="fas fa-arrow-right"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="attendanceRowTemplate">
    <tr>
        <td class="att-day"></td>
        <td class="att-date"></td>
        <td class="att-status"></td>
        <td class="att-time"></td>
        <td class="att-notes"></td>
        <td>
            <button type="button" class="btn btn-sm" style="background:rgba(192,57,43,0.1);color:var(--danger);border-radius:8px" onclick="deleteAttendanceRow(__INDEX__)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<template id="recitationRowTemplate">
    <tr>
        <td class="rec-day"></td>
        <td class="rec-date"></td>
        <td class="rec-type"></td>
        <td class="rec-total"></td>
        <td class="rec-passed"></td>
        <td class="rec-failed"></td>
        <td>
            <button type="button" class="btn btn-sm" style="background:rgba(192,57,43,0.1);color:var(--danger);border-radius:8px" onclick="deleteRecitationRow(__INDEX__)">
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

document.getElementById('studentSelect').addEventListener('change', function() {
    const studentId = this.value;
    if (studentId) {
        window.location = '{{ route("admin.warnings.create") }}?student_id=' + studentId;
    }
});

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

function setText(el, text) { el.textContent = text || '-'; }

function createTextSpan(text) {
    const span = document.createElement('span');
    span.textContent = text || '-';
    return span;
}

function fetchAttendance() {
    const studentId = document.getElementById('studentSelect').value;
    if (!studentId) { alert('يرجى اختيار طالب أولاً'); return; }

    const fromDate = document.getElementById('attendanceFromDate').value;
    const url = '{{ route("admin.warnings.attendance", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            attendanceData = data;
            renderAttendanceTable();
            updateHiddenInputs();
        })
        .catch(error => { console.error('Error:', error); alert('حدث خطأ أثناء جلب بيانات الحضور'); });
}

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

    const statusLabels = { 'present': 'حاضر', 'absent': 'غائب', 'late': 'متأخر', 'excused': 'غائب بعذر', 'holiday': 'عطلة' };
    const statusColors = { 'present': 'success', 'absent': 'danger', 'late': 'warning', 'excused': 'info', 'holiday': 'secondary' };

    attendanceData.forEach(function(record, index) {
        const clone = template.content.cloneNode(true);
        const tr = clone.querySelector('tr');
        clone.querySelector('.btn').setAttribute('onclick', 'deleteAttendanceRow(' + index + ')');
        setText(clone.querySelector('.att-day'), record.day);
        setText(clone.querySelector('.att-date'), record.date ? record.date.split('T')[0] : '-');
        setText(clone.querySelector('.att-time'), record.arrival_time || '-');
        setText(clone.querySelector('.att-notes'), record.notes ||'-');

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

function fetchRecitations() {
    const studentId = document.getElementById('studentSelect').value;
    if (!studentId) { alert('يرجى اختيار طالب أولاً'); return; }

    const fromDate = document.getElementById('recitationFromDate').value;
    const url = '{{ route("admin.warnings.recitations", ":student") }}'.replace(':student', studentId) + '?from_date=' + fromDate;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            recitationData = data;
            renderRecitationTable();
            updateHiddenInputs();
        })
        .catch(error => { console.error('Error:', error); alert('حدث خطأ أثناء جلب بيانات التسميع'); });
}

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

    const typeLabels = { 'new_memorization': 'حفظ جديد', 'review': 'مراجعة', 'unmastered': 'حفظ غير متقن', 'recitation': 'سرد', 'mafsil': 'مفصل', 'not_recitate': 'لم يسمع' };

    recitationData.forEach(function(record, index) {
        const clone = template.content.cloneNode(true);
        clone.querySelector('.btn').setAttribute('onclick', 'deleteRecitationRow(' + index + ')');
        setText(clone.querySelector('.rec-day'), record.day);
        setText(clone.querySelector('.rec-date'), record.session_date ? record.session_date.split('T')[0] : '-');
        setText(clone.querySelector('.rec-type'), typeLabels[record.type] || record.type || '-');
        setText(clone.querySelector('.rec-total'), record.total_pages || 0);
        setText(clone.querySelector('.rec-passed'), record.passed_pages || 0);
        setText(clone.querySelector('.rec-failed'), record.failed_pages || 0);

        const notesCell = clone.querySelector('.rec-notes') || clone.querySelector('td:nth-child(7)');
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.style.cssText = 'border-radius: 8px; font-size: 12px;';
        input.placeholder = 'ملاحظات...';
        input.value = record.notes || '';
        input.addEventListener('change', function() { updateRecitationNotes(index, input.value); });
        notesCell.appendChild(input);

        tbody.appendChild(clone);
    });
}

function updateRecitationNotes(index, notes) { recitationData[index].notes = notes; updateHiddenInputs(); }
function deleteRecitationRow(index) { recitationData.splice(index, 1); renderRecitationTable(); updateHiddenInputs(); }

function updateHiddenInputs() {
    document.getElementById('attendanceData').value = JSON.stringify(attendanceData);
    document.getElementById('recitationData').value = JSON.stringify(recitationData);
    document.getElementById('testData').value = JSON.stringify(testData);
}

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
    input1.style.cssText = 'border-radius: 8px;';
    input1.placeholder = 'أدخل اسم الاختبار...';
    input1.addEventListener('change', function() { updateTestRow(rowIndex, 'test', input1.value); });
    td1.appendChild(input1);

    const td2 = document.createElement('td');
    const input2 = document.createElement('input');
    input2.type = 'text';
    input2.className = 'form-control form-control-sm';
    input2.style.cssText = 'border-radius: 8px;';
    input2.placeholder = 'أضف ملاحظات...';
    input2.addEventListener('change', function() { updateTestRow(rowIndex, 'notes', input2.value); });
    td2.appendChild(input2);

    const td3 = document.createElement('td');
    const delBtn = document.createElement('button');
    delBtn.type = 'button';
    delBtn.className = 'btn btn-sm';
    delBtn.style.cssText = 'background: rgba(192,57,43,0.1); color: var(--danger); border-radius: 8px;';
    delBtn.innerHTML = '<i class="fas fa-trash"></i>';
    delBtn.addEventListener('click', function() { deleteTestRow(rowIndex); });
    td3.appendChild(delBtn);

    tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
    tbody.appendChild(tr);
}

function updateTestRow(index, field, value) { if (testData[index]) { testData[index][field] = value; updateHiddenInputs(); } }
function deleteTestRow(index) { testData.splice(index, 1); renderTestTable(); updateHiddenInputs(); }

function renderTestTable() {
    const tbody = document.getElementById('testTableBody');
    tbody.innerHTML = '';
    testData.forEach(function(item, index) {
        const tr = document.createElement('tr');
        tr.id = 'testRow_' + index;

        [ { field: 'test', placeholder: 'أدخل اسم الاختبار...' }, { field: 'notes', placeholder: 'أضف ملاحظات...' } ].forEach(function(inputConfig) {
            const td = document.createElement('td');
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control form-control-sm';
            input.style.cssText = 'border-radius: 8px;';
            input.placeholder = inputConfig.placeholder;
            input.value = item[inputConfig.field] || '';
            input.addEventListener('change', function() { updateTestRow(index, inputConfig.field, input.value); });
            td.appendChild(input);
            tr.appendChild(td);
        });

        const td3 = document.createElement('td');
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-sm';
        delBtn.style.cssText = 'background: rgba(192,57,43,0.1); color: var(--danger); border-radius: 8px;';
        delBtn.innerHTML = '<i class="fas fa-trash"></i>';
        delBtn.addEventListener('click', function() { deleteTestRow(index); });
        td3.appendChild(delBtn);
        tr.appendChild(td3);

        tbody.appendChild(tr);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    toggleWarningFields();
    updateHiddenInputs();
});
</script>
@endpush