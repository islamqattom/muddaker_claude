@extends('layouts.app')
@section('title', 'تفاصيل سجل التدقيق')
@section('page-title', 'تفاصيل سجل التدقيق')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.audit-logs.index') }}">سجل التدقيق</a></li>
    <li class="breadcrumb-item active">تفاصيل</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-search"></i>
        </div>
        <div class="header-text">
            <h4>تفاصيل سجل التدقيق</h4>
            <p>عرض التفاصيل الكاملة للسجل رقم #{{ $auditLog->id }}</p>
        </div>
    </div>
    <a href="{{ route('admin.audit-logs.index') }}" class="back-btn">
        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
    </a>
</div>

<div class="details-layout">
    {{-- Main Info Card --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="log-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="log-title">
                <h4>سجل رقم #{{ $auditLog->id }}</h4>
                <span class="log-date">{{ $auditLog->created_at?->format('d/m/Y - H:i:s') }}</span>
            </div>
        </div>
        <div class="info-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="info-content">
                        <label>المستخدم</label>
                        @if($auditLog->user)
                            <span class="user-name-lg">{{ $auditLog->user->name }}</span>
                            <small class="user-email-lg">{{ $auditLog->user->email }}</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <div class="info-content">
                        <label>الدور</label>
                        @switch($auditLog->role)
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
                                <span class="role-badge default">{{ $auditLog->role }}</span>
                        @endswitch
                    </div>
                </div>
            </div>
            <div class="info-grid mt-3">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="info-content">
                        <label>الإجراء</label>
                        @switch($auditLog->action)
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
                                    <i class="fas fa-sign-in-alt me-1"></i>تسجيل دخول
                                </span>
                                @break
                            @case('logout')
                                <span class="action-badge logout">
                                    <i class="fas fa-sign-out-alt me-1"></i>تسجيل خروج
                                </span>
                                @break
                            @case('login_failed')
                                <span class="action-badge failed">
                                    <i class="fas fa-lock me-1"></i>فشل تسجيل الدخول
                                </span>
                                @break
                            @default
                                <span class="action-badge default">{{ $auditLog->action }}</span>
                        @endswitch
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="info-content">
                        <label>النموذج</label>
                        @if($auditLog->model_type)
                            <span class="model-info">{{ class_basename($auditLog->model_type) }}</span>
                            @if($auditLog->model_id)
                                <code class="model-id-badge">#{{ $auditLog->model_id }}</code>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Technical Info Card --}}
    <div class="tech-card">
        <div class="tech-card-header">
            <i class="fas fa-server"></i>
            <span>المعلومات التقنية</span>
        </div>
        <div class="tech-card-body">
            <div class="tech-item">
                <label><i class="fas fa-globe"></i> عنوان IP</label>
                <span>{{ $auditLog->ip_address ?? '-' }}</span>
            </div>
            <div class="tech-item">
                <label><i class="fas fa-desktop"></i> المتصفح</label>
                <small>{{ $auditLog->user_agent ?? '-' }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Changes Section --}}
@if($auditLog->action === 'updated' && ($auditLog->old_values || $auditLog->new_values))
    <div class="changes-section">
        <div class="changes-grid">
            <div class="change-card old">
                <div class="change-card-header">
                    <i class="fas fa-arrow-left"></i>
                    <span>القيم القديمة</span>
                </div>
                <div class="change-card-body">
                    <pre class="json-data">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
            <div class="change-card new">
                <div class="change-card-header">
                    <i class="fas fa-arrow-right"></i>
                    <span>القيم الجديدة</span>
                </div>
                <div class="change-card-body">
                    <pre class="json-data">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    </div>
@elseif($auditLog->action === 'created' && $auditLog->new_values)
    <div class="changes-section">
        <div class="change-card full">
            <div class="change-card-header">
                <i class="fas fa-plus"></i>
                <span>قيمة السجل الجديد</span>
            </div>
            <div class="change-card-body">
                <pre class="json-data">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
@elseif($auditLog->action === 'deleted' && $auditLog->old_values)
    <div class="changes-section">
        <div class="change-card full">
            <div class="change-card-header danger">
                <i class="fas fa-trash"></i>
                <span>قيمة السجل المحذوف</span>
            </div>
            <div class="change-card-body">
                <pre class="json-data">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
@endif

{{-- Back Button --}}
<div class="back-section">
    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
    </a>
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

.back-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    background: transparent;
    color: #7f8c8d;
    border: 1.5px solid #dee2e6;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: #f8f9fa;
    color: #182B48;
}

.details-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 25px;
    margin-bottom: 25px;
}

@media (max-width: 992px) { .details-layout { grid-template-columns: 1fr; } }

.info-card, .tech-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 30px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    position: relative;
}

.info-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.log-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(212,160,48,0.4);
}

.log-title h4 { color: #fff; font-size: 22px; font-weight: 700; margin: 0 0 8px 0; }

.log-date {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    padding: 5px 15px;
    border-radius: 20px;
    color: rgba(255,255,255,0.9);
    font-size: 13px;
}

.info-card-body { padding: 30px; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 576px) { .info-grid { grid-template-columns: 1fr; } }

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.info-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
    flex-shrink: 0;
}

.info-content { display: flex; flex-direction: column; gap: 3px; }
.info-content label { font-size: 12px; color: #7f8c8d; }

.user-name-lg { font-size: 16px; font-weight: 700; color: #182B48; }
.user-email-lg { font-size: 12px; color: #7f8c8d; }

.role-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
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
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.action-badge.created { background: rgba(39,174,96,0.15); color: #27ae60; }
.action-badge.updated { background: rgba(243,156,18,0.15); color: #f39c12; }
.action-badge.deleted { background: rgba(192,57,43,0.15); color: #c0392b; }
.action-badge.login { background: rgba(52,152,219,0.15); color: #3498db; }
.action-badge.logout { background: rgba(127,140,141,0.15); color: #7f8c8d; }
.action-badge.failed { background: rgba(24,43,72,0.15); color: #182B48; }
.action-badge.default { background: rgba(127,140,141,0.15); color: #7f8c8d; }

.model-info { font-weight: 600; color: #182B48; font-size: 14px; }
.model-id-badge {
    background: #f0f4f8;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 12px;
    color: #7f8c8d;
    margin-top: 5px;
    display: inline-block;
}

.tech-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
    color: #182B48;
    font-weight: 700;
    font-size: 16px;
}

.tech-card-header i {
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

.tech-card-body { padding: 25px; }

.tech-item {
    padding: 15px 0;
    border-bottom: 1px solid #f0f4f8;
}

.tech-item:last-child { border-bottom: none; }

.tech-item label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.tech-item label i { color: #d4a030; }
.tech-item span { font-weight: 600; color: #182B48; font-family: monospace; }
.tech-item small { color: #7f8c8d; font-size: 12px; word-break: break-all; }

.changes-section { margin-bottom: 25px; }

.changes-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

@media (max-width: 768px) { .changes-grid { grid-template-columns: 1fr; } }

.change-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.change-card.full { grid-column: 1 / -1; }

.change-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    font-weight: 700;
    font-size: 15px;
    color: #182B48;
}

.change-card-header.danger { background: rgba(192,57,43,0.1); color: #c0392b; }

.change-card.old .change-card-header { background: rgba(243,156,18,0.1); color: #f39c12; }
.change-card.new .change-card-header { background: rgba(39,174,96,0.1); color: #27ae60; }

.change-card-header i { font-size: 16px; }

.change-card-body {
    padding: 20px;
    background: #f8f9fa;
    max-height: 350px;
    overflow: auto;
}

.json-data {
    margin: 0;
    font-size: 12px;
    color: #2c3e50;
    font-family: monospace;
    white-space: pre-wrap;
    word-break: break-all;
}

.back-section {
    display: flex;
    justify-content: center;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-outline-secondary {
    background: transparent;
    color: #7f8c8d;
    border: 1.5px solid #dee2e6;
}

.btn-outline-secondary:hover {
    background: #f8f9fa;
    color: #182B48;
}
</style>
@endsection