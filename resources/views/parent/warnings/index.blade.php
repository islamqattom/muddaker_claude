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

<div class="portal-container">
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

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-list"></i>
                <span>جميع الإنذارات</span>
            </div>
        </div>
        <div class="card-body-custom p-0">
            @if($warnings->isEmpty())
                <div class="empty-state success">
                    <div class="empty-illustration success">
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
                                                <i class="fas fa-calendar-times"></i>
                                                <span>حضور</span>
                                            </span>
                                            @break
                                        @case('recitation')
                                            <span class="type-badge recitation">
                                                <i class="fas fa-microphone"></i>
                                                <span>تسميع</span>
                                            </span>
                                            @break
                                        @default
                                            <span class="type-badge general">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>عام</span>
                                            </span>
                                    @endswitch
                                </td>
                                <td>
                                    <span class="warning-title">{{ $warning->title }}</span>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar"></i>
                                        {{ $warning->issue_date->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td><span class="day-badge">{{ $warning->issue_day }}</span></td>
                                <td><code class="warning-number">{{ $warning->warning_number }}</code></td>
                                <td>
                                    @if($warning->is_read_by_parent)
                                        <span class="status-badge read">
                                            <i class="fas fa-check"></i>
                                            <span>مقروء</span>
                                        </span>
                                    @else
                                        <span class="status-badge new">
                                            <i class="fas fa-bell"></i>
                                            <span>جديد</span>
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
</div>

<style>
.header-icon.warning {
    background: linear-gradient(135deg, #d48806, #f39c12);
    color: #fff;
}

.header-stats { display: flex; gap: 12px; }

.stat-pill.danger {
    background: linear-gradient(135deg, #a82e1c, #c0392b);
    color: #fff;
}
.stat-pill i { color: #fff; }

.empty-illustration {
    width: 66px;
    height: 66px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 26px;
    color: #d4a030;
}

.empty-illustration.success {
    background: linear-gradient(135deg, #16835a, #27ae60);
    color: #fff;
}

.modern-table tbody tr.unread-row { background: rgba(243,156,18,0.05); }

.user-avatar.warning {
    background: linear-gradient(135deg, #d48806, #f39c12);
    color: #fff;
}

.type-badge.attendance { background: rgba(243,156,18,0.12); color: #d48806; }
.type-badge.recitation { background: rgba(52,152,219,0.12); color: #2475a8; }
.type-badge.general { background: rgba(127,140,141,0.12); color: #7f8c8d; }

.date-cell i { color: #d4a030; }

.day-badge {
    background: #f0f4f8;
    color: #7f8c8d;
}

.warning-number {
    background: #f0f4f8;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    color: #182B48;
}

.status-badge.read { background: rgba(39,174,96,0.12); color: #16835a; }
.status-badge.new { background: rgba(192,57,43,0.12); color: #a82e1c; }
</style>
@endsection