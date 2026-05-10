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

<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-history"></i>
        </div>
        <div class="header-text">
            <h4>سجل الحضور والغياب</h4>
            <p>عرض سجل الحضور والغياب للطلاب</p>
        </div>
    </div>
</div>

<div class="filter-card mb-4">
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
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

@media (max-width: 768px) { .page-header { flex-direction: column; align-items: flex-start; } }

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #d4a030;
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.filter-card {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.filter-form { margin: 0; }

.filter-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
    gap: 20px;
    align-items: end;
}

@media (max-width: 992px) { .filter-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 576px) { .filter-grid { grid-template-columns: 1fr; } }

.filter-label {
    display: block;
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.filter-actions { display: flex; gap: 10px; }

.table-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
}

.table-title i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.table-actions { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }

.record-count { color: #7f8c8d; font-size: 13px; display: flex; align-items: center; gap: 8px; }
.record-count i { color: #d4a030; }

.student-filter-badge {
    background: rgba(52,152,219,0.15);
    color: #3498db;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.modern-table { width: 100%; border-collapse: collapse; }
.modern-table thead { background: #f8f9fa; }

.modern-table th {
    padding: 16px 20px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 14px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.row-num { color: #bdc3c7; font-weight: 600; font-size: 13px; }

.user-info-cell { display: flex; align-items: center; gap: 14px; }

.user-avatar-table {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 16px;
}

.user-details { display: flex; flex-direction: column; }
.user-name { color: #182B48; font-weight: 600; font-size: 14px; }

.date-cell { color: #7f8c8d; font-size: 13px; }

.day-badge {
    background: #f0f4f8;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #182B48;
}

.badge-modern {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.badge-modern.success, .badge-modern.green { background: rgba(39,174,96,0.15); color: #27ae60; }
.badge-modern.danger, .badge-modern.red { background: rgba(192,57,43,0.15); color: #c0392b; }
.badge-modern.warning { background: rgba(243,156,18,0.15); color: #f39c12; }
.badge-modern.info { background: rgba(52,152,219,0.15); color: #3498db; }
.badge-modern.orange { background: rgba(230,126,34,0.15); color: #e67e22; }

.time-value { font-family: monospace; color: #182B48; font-weight: 600; }
.notes-text { color: #7f8c8d; font-size: 13px; }
.no-data { color: #bdc3c7; }

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.3s ease;
    cursor: pointer;
    background: rgba(192,57,43,0.1);
    color: #c0392b;
}

.action-btn:hover { background: #c0392b; color: #fff; transform: scale(1.05); }

.table-footer {
    padding: 20px 25px;
    border-top: 1px solid #f0f4f8;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination { margin: 0; gap: 5px; }

.pagination-wrapper .page-link {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 14px;
    border: 1px solid #e9ecef;
    color: #182B48;
}

.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-color: #182B48;
    color: #fff;
}

.pagination-wrapper .page-item.disabled .page-link { color: #bdc3c7; }

.empty-state { text-align: center; padding: 60px 20px; }

.empty-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: #d4a030;
}

.empty-state h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state p { color: #7f8c8d; margin-bottom: 25px; }
</style>

@endsection