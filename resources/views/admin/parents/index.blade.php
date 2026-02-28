@extends('layouts.app')
@section('title', 'إدارة أولياء الأمور')
@section('page-title', 'إدارة أولياء الأمور')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">أولياء الأمور</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-users me-2"></i>
            قائمة أولياء الأمور
        </h6>
        <a href="{{ route('admin.parents.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            إضافة ولي أمر
        </a>
    </div>
    <div class="card-body p-0">
        @if($parents->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                <p>لا توجد أولياء أمور مسجلين</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم ولي الأمر</th>
                            <th>رقم الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th>صلة القرابة</th>
                            <th>عدد الطلاب</th>
                            <th width="150">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parents as $index => $parent)
                        <tr>
                            <td>{{ $parents->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $parent->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $parent->name }}</div>
                                        <small class="text-muted">{{ $parent->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $parent->phone }}</td>
                            <td>{{ $parent->email ?? '-' }}</td>
                            <td>
                                @if($parent->parentProfile)
                                    <span class="badge bg-secondary">
                                        @if($parent->parentProfile->relationship === 'father')
                                            الأب
                                        @elseif($parent->parentProfile->relationship === 'mother')
                                            الأم
                                        @else
                                            {{ $parent->parentProfile->relationship }}
                                        @endif
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($parent->parentProfile)
                                    <span class="badge bg-info">{{ $parent->parentProfile->students_count ?? 0 }} طالب</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.parents.show', $parent->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.parents.edit', $parent->id) }}" class="btn btn-sm btn-outline-secondary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.parents.destroy', $parent->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا ولي الأمر؟')">
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
                {{ $parents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
