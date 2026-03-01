@extends('layouts.app')
@section('title', 'تقرير التسميع')
@section('page-title', 'تقرير التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير التسميع</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.recitation') }}" class="row g-3 align-items-end">
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
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate ? $fromDate->format('Y-m-d') : now()->startOfMonth()->toDateString()) }}">
            </div>
            <div class="col-md-2" id="toDateCol" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate ? $toDate->format('Y-m-d') : now()->toDateString()) }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                <a href="{{ route('admin.reports.recitation', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-1"></i>تصدير PDF
                </a>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-microphone me-2"></i>
            نتائج التسميع
        </h6>
        <span class="badge bg-info">{{ $sessions->total() }} جلسة</span>
    </div>
    <div class="card-body p-0">
        @if($sessions->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-microphone fa-3x mb-3 d-block"></i>
                <p>لا توجد جلسات تسميع في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم الطالب</th>
                            <th>المشرف</th>
                            <th>الجزء</th>
                            <th>الصفحات</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $index => $session)
                        <tr>
                            <td>{{ $sessions->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $session->student->user->avatar_url ?? asset('images/default-avatar.png') }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <span>{{ $session->student->user->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td>{{ $session->supervisor->name ?? '-' }}</td>
                            <td>{{ $session->part->name ?? '-' }}</td>
                            <td>{{ $session->start_page ?? '-' }} - {{ $session->end_page ?? '-' }}</td>
                            <td>{{ $session->session_date?->format('d/m/Y') }}</td>
                            <td>
                                @if($session->status === 'passed')
                                    <span class="badge bg-success">ناجح</span>
                                @elseif($session->status === 'failed')
                                    <span class="badge bg-danger">راسب</span>
                                @else
                                    <span class="badge bg-secondary">{{ $session->status }}</span>
                                @endif
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
        if (option.value === '') return;

        const studentSupervisorId = option.getAttribute('data-supervisor-id');

        if (!selectedSupervisorId || studentSupervisorId === selectedSupervisorId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });

    studentSelect.value = '';
}

document.addEventListener('DOMContentLoaded', function() {
    filterStudents();
});
</script>
@endpush
