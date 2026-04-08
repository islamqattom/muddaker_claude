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
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">المستخدم</label>
                <select name="user_id" class="form-select">
                    <option value="">جميع المستخدمين</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">نوع الإجراء</label>
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
            <div class="col-md-2">
                <label class="form-label">نوع النموذج</label>
                <select name="model_type" class="form-select">
                    <option value="">جميع النماذج</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                            {{ class_basename($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search me-1"></i>بحث
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
        <div class="row g-3 mt-2">
            <div class="col-md-12 text-end">
                <div class="btn-group">
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-csv me-1"></i>تصدير CSV
                    </a>
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i>تصدير PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-history me-2"></i>
            سجل التدقيق
        </h6>
        <span class="badge bg-info">{{ $logs->total() }} سجل</span>
    </div>
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-history fa-3x mb-3 d-block"></i>
                <p>لا توجد سجلات</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>التاريخ</th>
                            <th>المستخدم</th>
                            <th>الدور</th>
                            <th>الإجراء</th>
                            <th>النموذج</th>
                            <th>IP</th>
                            <th width="100">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $index => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $index }}</td>
                            <td>
                                <small>{{ $log->created_at?->format('d/m/Y H:i:s') }}</small>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="fw-semibold">{{ $log->user->name }}</div>
                                    <small class="text-muted">{{ $log->user->email }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @switch($log->role)
                                    @case('admin') <span class="badge bg-danger">مدير</span> @break
                                    @case('supervisor') <span class="badge bg-primary">مشرف</span> @break
                                    @case('student') <span class="badge bg-success">طالب</span> @break
                                    @case('parent') <span class="badge bg-warning text-dark">ولي أمر</span> @break
                                    @default <span class="badge bg-secondary">{{ $log->role }}</span>
                                @endswitch
                            </td>
                            <td>
                                @switch($log->action)
                                    @case('created') <span class="badge bg-success">تم الإنشاء</span> @break
                                    @case('updated') <span class="badge bg-warning text-dark">تم التعديل</span> @break
                                    @case('deleted') <span class="badge bg-danger">تم الحذف</span> @break
                                    @case('login') <span class="badge bg-info">تسجيل دخول</span> @break
                                    @case('logout') <span class="badge bg-secondary">تسجيل خروج</span> @break
                                    @case('login_failed') <span class="badge bg-dark">فشل الدخول</span> @break
                                    @default <span class="badge bg-light text-dark">{{ $log->action }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($log->model_type)
                                    <small>{{ class_basename($log->model_type) }}</small>
                                    @if($log->model_id)
                                        <code class="ms-1">#{{ $log->model_id }}</code>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><small>{{ $log->ip_address ?? '-' }}</small></td>
                            <td>
                                <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
