@extends('layouts.app')
@section('title', 'تقرير التسميع')
@section('page-title', 'تقرير التسميع')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير التسميع</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('supervisor.reports.recitation') }}" class="row g-3 align-items-end">
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
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>شهر</option>
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
            @if(isset($sessions) && $sessions->isNotEmpty())
            <div class="col-md-1">
                <a href="{{ route('supervisor.reports.recitation', array_merge(request()->except('export'), ['export' => 'pdf'])) }}" class="btn btn-outline-danger w-100" target="_blank">
                    <i class="fas fa-file-pdf"></i>
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

@if(isset($sessions))
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['total'] }}</div>
                <div>إجمالي الجلسات</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['passed'] }}</div>
                <div>ناجح</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['failed'] }}</div>
                <div>راسب</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['total_errors'] }}</div>
                <div>إجمالي الأخطاء</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-dark">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['total_hesitations'] }}</div>
                <div>إجمالي الترددات</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ $stats['total_pages'] }}</div>
                <div>إجمالي الصفحات</div>
            </div>
        </div>
    </div>
</div>

<!-- Sessions Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>تفاصيل الجلسات</h5>
        <span class="badge bg-primary fs-6">الفترة: {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px">#</th>
                        <th>الطالب</th>
                        <th>النوع</th>
                        <th>الجزء</th>
                        <th class="text-center">الصفحات</th>
                        <th class="text-center">الأخطاء</th>
                        <th class="text-center">الترددات</th>
                        <th class="text-center">العلامة</th>
                        <th class="text-center">الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('supervisor.reports.student', $session->student->id) }}">
                                    {{ $session->student->user->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $session->type_label }}</span>
                            </td>
                            <td>
                                @if($session->part)
                                    <span class="text-primary">الجزء {{ $session->part->part_number }}</span>
                                @elseif($session->mafsil_type)
                                    <span class="text-danger">مفصل ({{ $session->mafsil_type }})</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->total_pages)
                                    <span class="badge bg-dark">{{ $session->total_pages }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->total_errors > 0)
                                    <span class="text-danger fw-bold">{{ $session->total_errors }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->total_hesitations > 0)
                                    <span class="text-warning fw-bold">{{ $session->total_hesitations }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->final_score !== null)
                                    @php
                                        $scoreClass = $session->final_score >= 90 ? 'bg-success' : ($session->final_score >= 80 ? 'bg-warning text-dark' : 'bg-danger');
                                    @endphp
                                    <span class="badge {{ $scoreClass }}">{{ number_format($session->final_score, 1) }}%</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $session->session_date->format('Y/m/d') }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد جلسات في الفترة المحددة
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
