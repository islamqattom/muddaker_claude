@extends('layouts.app')
@section('title', 'إدارة المشرفين')
@section('page-title', 'إدارة المشرفين')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المشرفين</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-user-tie me-2"></i>
            قائمة المشرفين
        </h6>
        <a href="{{ route('admin.supervisors.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            إضافة مشرف
        </a>
    </div>
    <div class="card-body p-0">
        @if($supervisors->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-user-tie fa-3x mb-3 d-block"></i>
                <p>لا يوجد مشرفين مسجلين</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم المشرف</th>
                            <th>رقم الموظف</th>
                            <th>رقم الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th>التخصص</th>
                            <th>عدد الطلاب</th>
                            <th width="150">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supervisors as $index => $supervisor)
                        <tr>
                            <td>{{ $supervisors->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $supervisor->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $supervisor->name }}</div>
                                        <small class="text-muted">{{ $supervisor->username ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $supervisor->supervisor->employee_number ?? '-' }}</code></td>
                            <td>{{ $supervisor->phone }}</td>
                            <td>{{ $supervisor->email ?? '-' }}</td>
                            <td>{{ $supervisor->supervisor->specialization ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $supervisor->supervisedStudents->count() }} طالب</span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.supervisors.show', $supervisor->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.supervisors.edit', $supervisor->id) }}" class="btn btn-sm btn-outline-secondary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.supervisors.destroy', $supervisor->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشرف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $supervisors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
