@extends('layouts.app')

@section('title', 'سجل الحضور')
@section('page-title', 'سجل الحضور')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">سجل الحضور</li>
@endsection

@section('content')

<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="header-text">
            <h4>سجل الحضور</h4>
            <p>عرض سجل الحضور والغياب</p>
        </div>
    </div>
    <div class="header-stats">
        <div class="stat-pill success">
            <i class="fas fa-check-circle"></i>
            <span>{{ $stats['present'] }} حاضر</span>
        </div>
        <div class="stat-pill danger">
            <i class="fas fa-times-circle"></i>
            <span>{{ $stats['absent'] }} غائب</span>
        </div>
        <div class="stat-pill warning">
            <i class="fas fa-clock"></i>
            <span>{{ $stats['late'] }} متأخر</span>
        </div>
    </div>
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
        </div>
    </div>

    @if($attendances->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h5>لا يوجد سجل حضور</h5>
            <p>لم يتم العثور على سجل حضور</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>اليوم</th>
                        <th>الحالة</th>
                        <th>وقت الوصول</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $index => $attendance)
                    <tr>
                        <td class="row-num">{{ $attendances->firstItem() + $index }}</td>
                        <td class="fw-semibold">{{ $attendance->date->format('Y/m/d') }}</td>
                        <td><span class="day-badge">{{ $attendance->day }}</span></td>
                        <td>
                            <span class="badge-modern {{ $attendance->status_color }}">
                                {{ $attendance->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($attendance->arrival_time)
                                <span class="time-value">{{ \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i') }}</span>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="pagination-wrapper">
                {{ $attendances->links() }}
            </div>
        </div>
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

.header-stats { display: flex; gap: 10px; flex-wrap: wrap; }

.stat-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 13px;
    color: #fff;
}

.stat-pill.success { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.stat-pill.danger { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.stat-pill.warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
.stat-pill i { font-size: 14px; }

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

.table-actions { display: flex; align-items: center; gap: 15px; }

.record-count { color: #7f8c8d; font-size: 13px; display: flex; align-items: center; gap: 8px; }
.record-count i { color: #d4a030; }

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
    padding: 16px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.row-num { color: #bdc3c7; font-weight: 600; font-size: 13px; }

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

.time-value {
    font-family: monospace;
    color: #182B48;
    font-weight: 600;
}

.notes-text { color: #7f8c8d; font-size: 13px; }

.no-data { color: #bdc3c7; }

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