@extends('layouts.app')
@section('title', 'سجل الحضور')
@section('page-title', 'سجل الحضور')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.attendance.index') }}">الحضور والغياب</a></li>
    <li class="breadcrumb-item active">السجل</li>
@endsection

@section('content')

<x-page-hero icon="fas fa-history">
    <h1 class="page-banner-title">سجل الحضور والغياب</h1>
    <p class="page-banner-sub">عرض سجل الحضور والغياب للطلاب</p>
</x-page-hero>

<div class="filter-card">
    <form method="GET" action="{{ route('supervisor.attendance.history') }}" class="filter-form">
        <div class="filter-grid">
            <div class="filter-group">
                <label class="filter-label">الطالب</label>
                <select name="student_id" class="form-select">
                    <option value="">-- جميع الطلاب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate->toDateString()) }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate->toDateString()) }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>بحث
                </button>
            </div>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-list"></i>
            <span>سجل الحضور</span>
        </div>
        <div class="table-actions">
            <span class="record-count">
                <i class="fas fa-folder-open"></i>
                {{ $attendances->total() }} سجل
            </span>
            @if($studentId)
                @php $selectedStudent = $students->firstWhere('id', $studentId) @endphp
                @if($selectedStudent)
                    <span class="student-filter-badge">
                        <i class="fas fa-user-graduate me-1"></i>{{ $selectedStudent->user->name }}
                    </span>
                @endif
            @endif
        </div>
    </div>

    @if($attendances->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h5>لا توجد سجلات</h5>
            <p>لم يتم العثور على سجلات في الفترة المحددة</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>التاريخ</th>
                        <th>اليوم</th>
                        <th>الحالة</th>
                        <th>وقت الحضور</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $index => $attendance)
                    <tr>
                        <td class="row-num">{{ $attendances->firstItem() + $index }}</td>
                        <td>
                            <div class="user-info-cell">
                                <div class="user-avatar-table">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-details">
                                    <span class="user-name">{{ $attendance->student->user->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="date-cell">{{ $attendance->date->format('Y/m/d') }}</td>
                        <td><span class="day-badge">{{ $attendance->day }}</span></td>
                        <td>
                            <span class="badge-modern {{ $attendance->status_color }}">
                                {{ $attendance->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($attendance->arrival_time)
                                <span class="time-value">{{ $attendance->arrival_time }}</span>
                            @else
                                <span class="no-data">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->notes)
                                <span class="notes-text">{{ $attendance->notes }}</span>
                            @else
                                <span class="no-data">-</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="action-btn delete" title="حذف" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $attendance->id }}">
                                <i class="fas fa-trash"></i>
                            </button>

                            <div class="modal fade" id="deleteModal{{ $attendance->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">حذف سجل الحضور</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>هل أنت متأكد من حذف سجل حضور الطالب <strong>{{ $attendance->student->user->name }}</strong>؟</p>
                                            <p class="text-muted small">بتاريخ: {{ $attendance->date->format('Y/m/d') }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <form action="{{ route('supervisor.attendance.destroy', $attendance->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">حذف</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="table-footer">
                <div class="pagination-wrapper">
                    {{ $attendances->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    @endif
</div>

<style>
.table-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.student-filter-badge {
    background: rgba(52,152,219,0.15);
    color: #3498db;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.time-value { font-family: monospace; color: #182B48; font-weight: 600; }
.notes-text { color: #7f8c8d; font-size: 12.5px; }
</style>

@endsection