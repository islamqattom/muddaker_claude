@extends('layouts.app')
@section('title', 'الإنذارات')
@section('page-title', 'الإنذارات')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإنذارات</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="header-text">
            <h4>سجل الإنذارات</h4>
            <p>متابعة إنذارات أبنائك والتعامل معها</p>
        </div>
    </div>
    <div class="header-stats">
        @if($warnings->count() > 0)
            <div class="stat-pill danger">
                <i class="fas fa-bell"></i>
                <span>{{ $warnings->total() }} إنذار</span>
            </div>
        @endif
    </div>
</div>

{{-- Warnings Table --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-list"></i>
            <span>جميع الإنذارات</span>
        </div>
    </div>
    <div class="card-body-custom p-0">
        @if($warnings->isEmpty())
            <div class="empty-state">
                <div class="empty-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5>لا توجد إنذارات</h5>
                <p>جميع أبنائك في حالة جيدة - لا توجد إنذارات مسجلة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>نوع الإنذار</th>
                            <th>العنوان</th>
                            <th>التاريخ</th>
                            <th>اليوم</th>
                            <th>الرقم</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warnings as $index => $warning)
                        <tr class="{{ !$warning->is_read_by_parent ? 'unread-row' : '' }}">
                            <td class="row-num">{{ $index + 1 }}</td>
                            <td>
                                <div class="user-info-cell">
                                    <div class="user-avatar {{ !$warning->is_read_by_parent ? 'warning' : '' }}">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-details">
                                        <span class="user-name">{{ $warning->student->user->name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @switch($warning->type)
                                    @case('attendance')
                                        <span class="type-badge attendance">
                                            <i class="fas fa-calendar-times me-1"></i>حضور
                                        </span>
                                        @break
                                    @case('recitation')
                                        <span class="type-badge recitation">
                                            <i class="fas fa-microphone me-1"></i>تسميع
                                        </span>
                                        @break
                                    @default
                                        <span class="type-badge general">
                                            <i class="fas fa-exclamation-circle me-1"></i>عام
                                        </span>
                                @endswitch
                            </td>
                            <td>
                                <span class="warning-title">{{ $warning->title }}</span>
                            </td>
                            <td>
                                <div class="date-cell">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $warning->issue_date->format('d/m/Y') }}
                                </div>
                            </td>
                            <td><span class="badge bg-secondary">{{ $warning->issue_day }}</span></td>
                            <td><code class="warning-number">{{ $warning->warning_number }}</code></td>
                            <td>
                                @if($warning->is_read_by_parent)
                                    <span class="status-badge read">
                                        <i class="fas fa-check me-1"></i>مقروء
                                    </span>
                                @else
                                    <span class="status-badge new">
                                        <i class="fas fa-bell me-1"></i>جديد
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('parent.warnings.show', $warning->id) }}" class="action-btn view">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="pagination-wrapper">
                    {{ $warnings->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

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

.header-icon.warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.header-stats { display: flex; gap: 10px; }

.stat-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
}

.stat-pill.danger {
    background: linear-gradient(135deg, #c0392b, #e74c3c);
    color: #fff;
}

.stat-pill i { color: #d4a030; }
.stat-pill.danger i { color: #fff; }

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

.card-body-custom { padding: 25px; }

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

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

.empty-icon.success {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: #fff;
}

.empty-state h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state p { color: #7f8c8d; }

.table-responsive { overflow-x: auto; }

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
.modern-table tbody tr.unread-row { background: rgba(243,156,18,0.05); }

.row-num { color: #bdc3c7; font-weight: 600; font-size: 13px; }

.user-info-cell { display: flex; align-items: center; gap: 12px; }

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.user-avatar.warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: #fff;
}

.user-details { display: flex; flex-direction: column; }
.user-name { color: #182B48; font-weight: 600; }

.type-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.type-badge.attendance { background: rgba(243,156,18,0.15); color: #f39c12; }
.type-badge.recitation { background: rgba(52,152,219,0.15); color: #3498db; }
.type-badge.general { background: rgba(127,140,141,0.15); color: #7f8c8d; }

.date-cell {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #7f8c8d;
    font-size: 13px;
}

.date-cell i { color: #d4a030; }

.warning-number {
    background: #f0f4f8;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    color: #182B48;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.read { background: rgba(39,174,96,0.15); color: #27ae60; }
.status-badge.new { background: rgba(192,57,43,0.15); color: #c0392b; }

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(52,152,219,0.1);
    color: #3498db;
    transition: all 0.3s ease;
    text-decoration: none;
}

.action-btn:hover {
    background: #3498db;
    color: #fff;
    transform: scale(1.05);
}

.table-footer {
    padding: 20px 25px;
    border-top: 1px solid #f0f4f8;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination { margin: 0; gap: 5px; }
.pagination-wrapper .page-link { padding: 8px 14px; border-radius: 8px; font-size: 14px; border: 1px solid #e9ecef; color: #182B48; }
.pagination-wrapper .page-item.active .page-link { background: linear-gradient(135deg, #182B48, #2A4570); border-color: #182B48; color: #fff; }
.pagination-wrapper .page-item.disabled .page-link { color: #bdc3c7; }
</style>
@endsection