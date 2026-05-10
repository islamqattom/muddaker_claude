@extends('layouts.app')

@section('title', 'قواعد الإنذار')
@section('page-title', 'قواعد الإنذار')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">قواعد الإنذار</li>
@endsection

@section('content')
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
    --card-shadow-hover: 0 12px 40px rgba(24, 43, 72, 0.15);
}

body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #6c7a89; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.add-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 28px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; border-radius: 14px;
    text-decoration: none; font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.add-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); color: #fff; }
.add-btn i { color: var(--gold); }

.table-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
}
.table-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.table-header i { color: var(--gold); font-size: 20px; }

.rules-table {
    width: 100%; border-collapse: collapse;
}
.rules-table thead {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
}
.rules-table th {
    padding: 16px 18px;
    font-size: 12px; font-weight: 700;
    text-align: center;
    border: none;
}
.rules-table td {
    padding: 16px 18px;
    font-size: 14px; color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.05);
    text-align: center;
    vertical-align: middle;
}
.rules-table tbody tr { transition: all 0.3s ease; }
.rules-table tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%); }
.rules-table tbody tr:last-child td { border-bottom: none; }

.row-number {
    width: 35px; height: 35px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: var(--gold);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px;
    margin: 0 auto;
}

.rule-name { font-weight: 700; color: var(--primary); }

.type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.type-badge.attendance { background: rgba(243,156,18,0.15); color: var(--warning); }
.type-badge.recitation { background: rgba(192,57,43,0.15); color: var(--danger); }
.type-badge.general { background: rgba(52,152,219,0.15); color: var(--info); }
.type-badge::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.status-badge.active { background: rgba(39,174,96,0.15); color: var(--success); }
.status-badge.inactive { background: rgba(149,165,166,0.15); color: #95a5a6; }
.status-badge::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

.action-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 8px 14px; border-radius: 10px;
    text-decoration: none; font-weight: 600; font-size: 12px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
}
.action-btn.edit {
    background: linear-gradient(135deg, rgba(243,156,18,0.1) 0%, rgba(230,126,34,0.15) 100%);
    color: #e67e22;
    border: 1px solid rgba(243,156,18,0.2);
}
.action-btn.edit:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: #fff;
    transform: translateY(-2px);
}
.action-btn.toggle {
    background: linear-gradient(135deg, rgba(52,152,219,0.1) 0%, rgba(41,128,185,0.15) 100%);
    color: var(--info);
    border: 1px solid rgba(52,152,219,0.2);
}
.action-btn.toggle:hover {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #fff;
    transform: translateY(-2px);
}
.action-btn.delete {
    background: linear-gradient(135deg, rgba(192,57,43,0.1) 0%, rgba(173,51,38,0.15) 100%);
    color: var(--danger);
    border: 1px solid rgba(192,57,43,0.2);
}
.action-btn.delete:hover {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: #fff;
    transform: translateY(-2px);
}

.pagination-section { display: flex; justify-content: center; margin-top: 25px; }
.pagination-section .pagination { gap: 8px; }
.pagination-section .page-link {
    padding: 10px 18px; border-radius: 12px; font-size: 14px;
    border: 2px solid #e9ecef; color: var(--primary);
    transition: all 0.3s ease;
}
.pagination-section .page-link:hover { border-color: var(--gold); color: var(--gold); }
.pagination-section .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-color: var(--primary); color: #fff;
}

.empty-state-card {
    text-align: center;
    padding: 60px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
}
.empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08) 0%, rgba(42,69,112,0.12) 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
    font-size: 38px; color: var(--gold);
    border: 3px solid rgba(212,160,48,0.15);
}
.empty-state-card h5 { color: var(--primary); font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state-card p { color: #95a5a6; margin-bottom: 20px; font-size: 14px; }

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-gavel"></i>
        </div>
        <div class="header-text">
            <h4>قواعد الإنذار</h4>
            <p>إدارة وضبط قواعد إصدار الإنذارات automatiques</p>
        </div>
    </div>
    <a href="{{ route('admin.warning-rules.create') }}" class="add-btn">
        <i class="fas fa-plus-circle"></i>
        إضافة قاعدة جديدة
    </a>
</div>

<div class="table-card">
    <div class="table-header">
        <i class="fas fa-list"></i>
        <span>قائمة القواعد</span>
    </div>
    <div style="padding: 0;">
        @if($rules->isEmpty())
            <div class="empty-state-card">
                <div class="empty-icon">
                    <i class="fas fa-gavel"></i>
                </div>
                <h5>لا توجد قواعد</h5>
                <p>قم بإضافة قواعد جديدة لإصدار الإنذارات تلقائياً</p>
                <a href="{{ route('admin.warning-rules.create') }}" class="add-btn" style="display: inline-flex; margin: 0 auto;">
                    <i class="fas fa-plus-circle"></i>
                    إضافة قاعدة
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="rules-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>الاسم</th>
                            <th>النوع</th>
                            <th>الشرط</th>
                            <th>الحد</th>
                            <th>الفترة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules as $rule)
                        <tr>
                            <td>
                                <span class="row-number">{{ $loop->iteration }}</span>
                            </td>
                            <td class="rule-name">{{ $rule->name }}</td>
                            <td>
                                @if($rule->type === 'attendance')
                                    <span class="type-badge attendance">
                                        <i class="fas fa-calendar-times"></i>حضور
                                    </span>
                                @elseif($rule->type === 'recitation')
                                    <span class="type-badge recitation">
                                        <i class="fas fa-microphone"></i>تقييم
                                    </span>
                                @else
                                    <span class="type-badge general">
                                        <i class="fas fa-exclamation-circle"></i>عام
                                    </span>
                                @endif
                            </td>
                            <td style="font-weight: 600;">{{ $rule->condition_type }}</td>
                            <td style="font-weight: 700; color: var(--gold);">{{ $rule->threshold }}</td>
                            <td>{{ $rule->period_days }} يوم</td>
                            <td>
                                @if($rule->is_active)
                                    <span class="status-badge active">
                                        <i class="fas fa-check"></i>مفعّل
                                    </span>
                                @else
                                    <span class="status-badge inactive">
                                        <i class="fas fa-pause"></i>معطّل
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.warning-rules.edit', $rule->id) }}" class="action-btn edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.warning-rules.toggle', $rule->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn toggle">
                                        <i class="fas fa-{{ $rule->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.warning-rules.destroy', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($rules->hasPages())
            <div style="padding: 20px 25px; border-top: 1px solid rgba(24,43,72,0.05);">
                {{ $rules->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection