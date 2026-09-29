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

<div class="page-header">
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

.header-stats { display: flex; gap: 8px; flex-wrap: wrap; }
.stat-pill.success { background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; }
.stat-pill.danger { background: linear-gradient(135deg, #c0392b, #e74c3c); color: #fff; }
.stat-pill.warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }
.stat-pill i { font-size: 13px; }

.table-actions { display: flex; align-items: center; gap: 12px; }
.time-value { font-family: monospace; color: #182B48; font-weight: 600; }
.notes-text { color: #7f8c8d; font-size: 12.5px; }
.badge-modern.orange { background: rgba(230, 126, 34, 0.15); color: #e67e22; }
</style>

@endsection