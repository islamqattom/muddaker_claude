@extends('layouts.app')
@section('title', 'تقرير الحضور والغياب')
@section('page-title', 'تقرير الحضور والغياب')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير الحضور</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('supervisor.reports.attendance') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">الطالب</label>
                <select name="student_id" class="form-select">
                    <option value="">-- جميع الطلاب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">الفترة</label>
                <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                    <option value="month" {{ request('period', 'month') == 'month' ? 'selected' : '' }}>شهر</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                </select>
            </div>
            <div class="col-md-2" id="fromDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}">
            </div>
            <div class="col-md-2" id="toDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            @if(isset($attendances) && $attendances->isNotEmpty())
            <div class="col-md-1">
                <a href="{{ route('supervisor.reports.attendance', array_merge(request()->except('export'), ['export' => 'pdf'])) }}" class="btn btn-outline-danger w-100" target="_blank">
                    <i class="fas fa-file-pdf"></i>
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

@if(isset($attendances))
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['total'] }}</div>
                <div>إجمالي السجلات</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['present'] }}</div>
                <div>حاضر</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['absent'] }}</div>
                <div>غائب</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['late'] }}</div>
                <div>متأخر</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-dark">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['excused'] }}</div>
                <div>بعذر</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                @php
                    $percentage = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100) : 0;
                @endphp
                <div class="fs-2 fw-bold">{{ $percentage }}%</div>
                <div>نسبة الحضور</div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>سجلات الحضور</h5>
        <span class="badge bg-primary fs-6">الفترة: {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px">#</th>
                        <th>الطالب</th>
                        <th>الحالة</th>
                        <th>وقت الحضور</th>
                        <th>ملاحظة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('supervisor.reports.student', $attendance->student->id) }}">
                                    {{ $attendance->student->user->name }}
                                </a>
                            </td>
                            <td>
                                @switch($attendance->status)
                                    @case('present')
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>حاضر</span>
                                        @break
                                    @case('absent')
                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>غائب</span>
                                        @break
                                    @case('late')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>متأخر</span>
                                        @break
                                    @case('excused')
                                        <span class="badge bg-info text-dark"><i class="fas fa-file-alt me-1"></i>بعذر</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">غير محدد</span>
                                @endswitch
                            </td>
                            <td>
                                @if($attendance->arrival_time)
                                    <span class="text-muted">{{ $attendance->arrival_time }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($attendance->notes)
                                    <span class="text-muted">{{ $attendance->notes }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $attendance->date->format('Y/m/d') }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد سجلات في الفترة المحددة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
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
</script>
@endpush
