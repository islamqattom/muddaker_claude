@extends('layouts.app')
@section('title', 'سجل التدقيق')
@section('page-title', 'سجل التدقيق')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">سجل التدقيق</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-history"></i>
        </div>
        <div class="header-text">
            <h4>سجل التدقيق</h4>
            <p>مراقبة جميع الأنشطة والإجراءات في النظام</p>
        </div>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="export-btn csv">
            <i class="fas fa-file-csv me-2"></i>تصدير CSV
        </a>
        <a href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="export-btn pdf">
            <i class="fas fa-file-pdf me-2"></i>تصدير PDF
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="filter-card">
    <form method="GET" class="filter-grid">
        <div class="filter-field">
            <label class="filter-label">المستخدم</label>
            <select name="user_id" class="form-select">
                <option value="">جميع المستخدمين</option>
                @foreach($users as $id => $name)
                    <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label class="filter-label">نوع الإجراء</label>
            <select name="action" class="form-select">
                <option value="">جميع الإجراءات</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        @switch($action)
                            @case('created') تم الإنشاء @break
                            @case('updated') تم التعديل @break
                            @case('deleted') تم الحذف @break
                            @case('login') تسجيل دخول @break
                            @case('logout') تسجيل خروج @break
                            @case('login_failed') فشل تسجيل الدخول @break
                        @endswitch
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label class="filter-label">نوع النموذج</label>
            <select name="model_type" class="form-select">
                <option value="">جميع النماذج</option>
                @foreach($modelTypes as $type)
                    <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                        {{ class_basename($type) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label class="filter-label">من تاريخ</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="filter-field">
            <label class="filter-label">إلى تاريخ</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search me-1"></i>بحث
            </button>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- Audit Logs Table --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-list"></i>
            <span>سجل التدقيق ({{ $logs->total() }} سجل)</span>
        </div>
    </div>
    <div class="card-body-custom p-0">
        @if($logs->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h5>لا توجد سجلات</h5>
                <p>لم يتم تسجيل أي إجراءات في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>المستخدم</th>
                            <th>الدور</th>
                            <th>الإجراء</th>
                            <th>النموذج</th>
                            <th>IP</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $index => $log)
                        <tr>
                            <td class="row-num">{{ $logs->firstItem() + $index }}</td>
                            <td>
                                <div class="datetime-cell">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ $log->created_at?->format('d/m/Y') }}</span>
                                    <small>{{ $log->created_at?->format('H:i:s') }}</small>
                                </div>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="user-details">
                                            <span class="user-name">{{ $log->user->name }}</span>
                                            <small class="user-email">{{ $log->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @switch($log->role)
                                    @case('admin')
                                        <span class="role-badge admin">مدير</span>
                                        @break
                                    @case('supervisor')
                                        <span class="role-badge supervisor">مشرف</span>
                                        @break
                                    @case('student')
                                        <span class="role-badge student">طالب</span>
                                        @break
                                    @case('parent')
                                        <span class="role-badge parent">ولي أمر</span>
                                        @break
                                    @default
                                        <span class="role-badge default">{{ $log->role }}</span>
                                @endswitch
                            </td>
                            <td>
                                @switch($log->action)
                                    @case('created')
                                        <span class="action-badge created">
                                            <i class="fas fa-plus me-1"></i>إنشاء
                                        </span>
                                        @break
                                    @case('updated')
                                        <span class="action-badge updated">
                                            <i class="fas fa-edit me-1"></i>تعديل
                                        </span>
                                        @break
                                    @case('deleted')
                                        <span class="action-badge deleted">
                                            <i class="fas fa-trash me-1"></i>حذف
                                        </span>
                                        @break
                                    @case('login')
                                        <span class="action-badge login">
                                            <i class="fas fa-sign-in-alt me-1"></i>دخول
                                        </span>
                                        @break
                                    @case('logout')
                                        <span class="action-badge logout">
                                            <i class="fas fa-sign-out-alt me-1"></i>خروج
                                        </span>
                                        @break
                                    @case('login_failed')
                                        <span class="action-badge failed">
                                            <i class="fas fa-lock me-1"></i>فشل
                                        </span>
                                        @break
                                    @default
                                        <span class="action-badge default">{{ $log->action }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($log->model_type)
                                    <div class="model-cell">
                                        <span class="model-name">{{ class_basename($log->model_type) }}</span>
                                        @if($log->model_id)
                                            <code class="model-id">#{{ $log->model_id }}</code>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="ip-address">{{ $log->ip_address ?? '-' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="action-btn view" title="عرض">
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
                    {{ $logs->links() }}
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

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.header-actions { display: flex; gap: 10px; }

.export-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.export-btn.csv {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: #fff;
}

.export-btn.csv:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(39,174,96,0.3);
}

.export-btn.pdf {
    background: linear-gradient(135deg, #c0392b, #e74c3c);
    color: #fff;
}

.export-btn.pdf:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(192,57,43,0.3);
}

.filter-card {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    margin-bottom: 25px;
}

.filter-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr 1fr;
    gap: 15px;
    align-items: end;
}

@media (max-width: 992px) { .filter-grid { grid-template-columns: 1fr 1fr 1fr; } }
@media (max-width: 576px) { .filter-grid { grid-template-columns: 1fr; } }

.filter-field { display: flex; flex-direction: column; }
.filter-label { color: #182B48; font-size: 13px; font-weight: 600; margin-bottom: 8px; }
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

.row-num { color: #bdc3c7; font-weight: 600; font-size: 13px; }

.datetime-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.datetime-cell i { color: #d4a030; font-size: 12px; }
.datetime-cell span { font-weight: 600; color: #182B48; }
.datetime-cell small { color: #7f8c8d; font-size: 11px; }

.user-info-cell { display: flex; align-items: center; gap: 12px; }

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.user-details { display: flex; flex-direction: column; }
.user-name { color: #182B48; font-weight: 600; }
.user-email { color: #7f8c8d; font-size: 11px; }

.role-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.role-badge.admin { background: rgba(192,57,43,0.15); color: #c0392b; }
.role-badge.supervisor { background: rgba(52,152,219,0.15); color: #3498db; }
.role-badge.student { background: rgba(39,174,96,0.15); color: #27ae60; }
.role-badge.parent { background: rgba(243,156,18,0.15); color: #f39c12; }
.role-badge.default { background: rgba(127,140,141,0.15); color: #7f8c8d; }

.action-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.action-badge.created { background: rgba(39,174,96,0.15); color: #27ae60; }
.action-badge.updated { background: rgba(243,156,18,0.15); color: #f39c12; }
.action-badge.deleted { background: rgba(192,57,43,0.15); color: #c0392b; }
.action-badge.login { background: rgba(52,152,219,0.15); color: #3498db; }
.action-badge.logout { background: rgba(127,140,141,0.15); color: #7f8c8d; }
.action-badge.failed { background: rgba(24,43,72,0.15); color: #182B48; }
.action-badge.default { background: rgba(127,140,141,0.15); color: #7f8c8d; }

.model-cell { display: flex; flex-direction: column; gap: 3px; }
.model-name { font-weight: 600; color: #182B48; }
.model-id {
    background: #f0f4f8;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    color: #7f8c8d;
}

.ip-address {
    font-family: monospace;
    font-size: 12px;
    color: #7f8c8d;
}

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