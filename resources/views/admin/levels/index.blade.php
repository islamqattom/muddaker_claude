@extends('layouts.app')
@section('title', 'إدارة المستويات')
@section('page-title', 'إدارة المستويات')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المستويات</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-layer-group me-2"></i>
            قائمة المستويات
        </h6>
        <a href="{{ route('admin.levels.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            إضافة مستوى
        </a>
    </div>
    <div class="card-body p-0">
        @if($levels->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-layer-group fa-3x mb-3 d-block"></i>
                <p>لا توجد مستويات مسجلة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم المستوى</th>
                            <th>رقم المستوى</th>
                            <th>صفحات الحفظ الجديدة</th>
                            <th>صفحات المراجعة</th>
                            <th>عدد الطلاب</th>
                            <th width="150">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($levels as $index => $level)
                        <tr>
                            <td>{{ $levels->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold">{{ $level->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $level->level_number }}</span>
                            </td>
                            <td>{{ $level->new_memorization_pages }}</td>
                            <td>{{ $level->review_pages }}</td>
                            <td>
                                <span class="badge bg-info">{{ $level->students_count }} طالب</span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.levels.show', $level->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.levels.edit', $level->id) }}" class="btn btn-sm btn-outline-secondary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.levels.destroy', $level->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستوى؟')">
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
                {{ $levels->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
