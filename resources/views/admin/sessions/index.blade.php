@extends('layouts.app')

@section('title', 'جلسات التسميع')
@section('page-title', 'جلسات التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">جلسات التسميع</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sessions.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">المشرف</label>
                <select name="supervisor_id" class="form-select" id="supervisorSelect" onchange="filterStudents()">
                    <option value="">-- جميع المشرفين --</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->user_id }}" {{ request('supervisor_id') == $supervisor->user_id ? 'selected' : '' }}>
                            {{ $supervisor->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الطالب</label>
                <select name="student_id" class="form-select" id="studentSelect">
                    <option value="">-- جميع الطلاب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}"
                                data-supervisor-id="{{ $student->supervisor_id }}"
                                {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">نوع التسميع</label>
                <select name="type" class="form-select">
                    <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="new_memorization" {{ request('type') == 'new_memorization' ? 'selected' : '' }}>حفظ جديد</option>
                    <option value="review" {{ request('type') == 'review' ? 'selected' : '' }}>مراجعة</option>
                    <option value="unmastered" {{ request('type') == 'unmastered' ? 'selected' : '' }}>حفظ غير متقن</option>
                    <option value="recitation" {{ request('type') == 'recitation' ? 'selected' : '' }}>سرد</option>
                    <option value="mafsil" {{ request('type') == 'mafsil' ? 'selected' : '' }}>مفصل</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الفترة</label>
                <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                    <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                </select>
            </div>
            <div class="col-md-2" id="fromDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}">
            </div>
            <div class="col-md-2" id="toDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-microphone me-2"></i>
            جلسات التسميع ({{ $sessions->total() }})
        </h6>
    </div>
    <div class="card-body p-0">
        @if($sessions->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-microphone-alt fa-3x mb-3 d-block"></i>
                <p>لا توجد جلسات تسميع مسجلة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>الطالب</th>
                            <th>المشرف</th>
                            <th>نوع الجلسة</th>
                            <th>الجزء/الصفحات</th>
                            <th>الأخطاء</th>
                            <th>الترددات</th>
                            <th>الدرجة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $index => $session)
                        <tr>
                            <td>{{ $sessions->firstItem() + $index }}</td>
                            <td class="fw-semibold">{{ $session->session_date->format('Y/m/d') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:bold;">
                                        {{ mb_substr($session->student->user->name, 0, 1) }}
                                    </div>
                                    <span>{{ $session->student->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($session->supervisor)
                                    <span>{{ $session->supervisor->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $session->type_label }}</span>
                            </td>
                            <td>
                                @if($session->part)
                                    <span class="fw-semibold">جزء {{ $session->part->part_number }}</span>
                                @else
                                    <span>{{ $session->start_page }} - {{ $session->end_page }}</span>
                                    <span class="text-muted">(صفحات)</span>
                                @endif
                            </td>
                            <td>
                                @if($session->total_errors > 0)
                                    <span class="text-danger fw-semibold">{{ $session->total_errors }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($session->total_hesitations > 0)
                                    <span class="text-warning fw-semibold">{{ $session->total_hesitations }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($session->final_score !== null)
                                    <span class="fw-semibold">{{ number_format($session->final_score, 1) }}</span>
                                    <span class="text-muted">/ {{ $session->max_score }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.sessions.show', $session->id) }}" class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function handlePeriodChange() {
    const period = document.getElementById('periodSelect').value;
    const fromDateCol = document.getElementById('fromDateCol');
    const toDateCol = document.getElementById('toDateCol');

    if (period === 'custom') {
        fromDateCol.style.display = '';
        toDateCol.style.display = '';
    } else {
        fromDateCol.style.display = 'none';
        toDateCol.style.display = 'none';
    }
}

function filterStudents() {
    const supervisorSelect = document.getElementById('supervisorSelect');
    const studentSelect = document.getElementById('studentSelect');
    const selectedSupervisorId = supervisorSelect.value;

    const studentOptions = studentSelect.querySelectorAll('option');

    studentOptions.forEach(option => {
        if (option.value === '') return; // Skip the default option

        const studentSupervisorId = option.getAttribute('data-supervisor-id');

        if (!selectedSupervisorId || studentSupervisorId === selectedSupervisorId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });

    // Reset student selection when supervisor changes
    studentSelect.value = '';
}

// Initialize student filter on page load
document.addEventListener('DOMContentLoaded', function() {
    filterStudents();
});
</script>
@endpush
