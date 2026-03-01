@extends('layouts.app')
@section('title', 'تقرير الحضور')
@section('page-title', 'تقرير الحضور')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير الحضور</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>بحث
                </button>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-calendar-check me-2"></i>
            سجلات الحضور
        </h6>
        <span class="badge bg-info">{{ $attendances->total() }} سجل</span>
    </div>
    <div class="card-body p-0">
        @if($attendances->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-check fa-3x mb-3 d-block"></i>
                <p>لا توجد سجلات حضور في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم الطالب</th>
                            <th>المشرف</th>
                            <th>التاريخ</th>
                            <th>وقت الحضور</th>
                            <th>وقت الانصراف</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $index => $attendance)
                        <tr>
                            <td>{{ $attendances->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $attendance->student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <span>{{ $attendance->student->user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $attendance->supervisor->name ?? '-' }}</td>
                            <td>{{ $attendance->date ?? '-' }}</td>
                            <td>{{ $attendance->arrival_time ?? '-' }}</td>
                            <td>{{ $attendance->departure_time ?? '-' }}</td>
                            <td>
                                @if($attendance->status === 'present')
                                    <span class="badge bg-success">حاضر</span>
                                @elseif($attendance->status === 'absent')
                                    <span class="badge bg-danger">غائب</span>
                                @elseif($attendance->status === 'late')
                                    <span class="badge bg-warning text-dark">متأخر</span>
                                @elseif($attendance->status === 'excused')
                                    <span class="badge bg-info">معذور</span>
                                @else
                                    <span class="badge bg-secondary">{{ $attendance->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
