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
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>
            تفاصيل سجل التدقيق
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">المعرف</th>
                        <td><code>#{{ $auditLog->id }}</code></td>
                    </tr>
                    <tr>
                        <th>التاريخ والوقت</th>
                        <td>{{ $auditLog->created_at?->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>المستخدم</th>
                        <td>
                            @if($auditLog->user)
                                {{ $auditLog->user->name }}
                                <br><small class="text-muted">{{ $auditLog->user->email }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>الدور</th>
                        <td>
                            @switch($auditLog->role)
                                @case('admin') <span class="badge bg-danger">مدير</span> @break
                                @case('supervisor') <span class="badge bg-primary">مشرف</span> @break
                                @case('student') <span class="badge bg-success">طالب</span> @break
                                @case('parent') <span class="badge bg-warning text-dark">ولي أمر</span> @break
                                @default <span class="badge bg-secondary">{{ $auditLog->role }}</span>
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <th>الإجراء</th>
                        <td>
                            @switch($auditLog->action)
                                @case('created') <span class="badge bg-success">تم الإنشاء</span> @break
                                @case('updated') <span class="badge bg-warning text-dark">تم التعديل</span> @break
                                @case('deleted') <span class="badge bg-danger">تم الحذف</span> @break
                                @case('login') <span class="badge bg-info">تسجيل دخول</span> @break
                                @case('logout') <span class="badge bg-secondary">تسجيل خروج</span> @break
                                @case('login_failed') <span class="badge bg-dark">فشل الدخول</span> @break
                                @default <span class="badge bg-light text-dark">{{ $auditLog->action }}</span>
                            @endswitch
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">نوع النموذج</th>
                        <td>
                            @if($auditLog->model_type)
                                {{ class_basename($auditLog->model_type) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>معرف النموذج</th>
                        <td>
                            @if($auditLog->model_id)
                                <code>#{{ $auditLog->model_id }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>عنوان IP</th>
                        <td>{{ $auditLog->ip_address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>المتصفح</th>
                        <td><small>{{ $auditLog->user_agent ?? '-' }}</small></td>
                    </tr>
                </table>
            </div>
        </div>

        @if($auditLog->action === 'updated' && ($auditLog->old_values || $auditLog->new_values))
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-arrow-left me-2"></i>القيم القديمة
                        </div>
                        <div class="card-body p-0">
                            <pre class="mb-0 p-3" style="max-height: 300px; overflow:auto; background: #f8f9fa;">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-arrow-right me-2"></i>القيم الجديدة
                        </div>
                        <div class="card-body p-0">
                            <pre class="mb-0 p-3" style="max-height: 300px; overflow:auto; background: #f8f9fa;">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($auditLog->action === 'created' && $auditLog->new_values)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-plus me-2"></i>قيمة السجل الجديد
                        </div>
                        <div class="card-body p-0">
                            <pre class="mb-0 p-3" style="max-height: 300px; overflow:auto; background: #f8f9fa;">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($auditLog->action === 'deleted' && $auditLog->old_values)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-trash me-2"></i>قيمة السجل المحذوف
                        </div>
                        <div class="card-body p-0">
                            <pre class="mb-0 p-3" style="max-height: 300px; overflow:auto; background: #f8f9fa;">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-1"></i>العودة للقائمة
            </a>
        </div>
    </div>
</div>
@endsection
