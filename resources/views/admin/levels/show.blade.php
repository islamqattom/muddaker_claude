@extends('layouts.app')
@section('title', 'تفاصيل المستوى')
@section('page-title', 'تفاصيل المستوى')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.levels.index') }}">المستويات</a></li>
    <li class="breadcrumb-item active">{{ $level->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Level Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-layer-group me-2"></i>
                    معلومات المستوى
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">اسم المستوى</th>
                        <td class="fw-semibold">{{ $level->name }}</td>
                    </tr>
                    <tr>
                        <th>رقم المستوى</th>
                        <td><span class="badge bg-primary fs-6">{{ $level->level_number }}</span></td>
                    </tr>
                    <tr>
                        <th>صفحات الحفظ الجديدة</th>
                        <td>{{ $level->new_memorization_pages }} صفحة</td>
                    </tr>
                    <tr>
                        <th>صفحات المراجعة</th>
                        <td>{{ $level->review_pages }} صفحة</td>
                    </tr>
                    @if($level->description)
                    <tr>
                        <th>الوصف</th>
                        <td>{{ $level->description }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Students Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    الطلاب في هذا المستوى
                </h6>
                <span class="badge bg-info">{{ $level->students_count }} طالب</span>
            </div>
            <div class="card-body p-0">
                @if($level->students->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                        <p class="mb-0">لا يوجد طلاب في هذا المستوى</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الطالب</th>
                                    <th>رقم الطالب</th>
                                    <th>المشرف</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($level->students as $student)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                            <span>{{ $student->user->name }}</span>
                                        </div>
                                    </td>
                                    <td><code>{{ $student->student_number }}</code></td>
                                    <td>{{ $student->supervisor->name ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>الإجراءات</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.levels.edit', $level->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>تعديل المستوى
                    </a>
                    <a href="{{ route('admin.levels.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                    <form action="{{ route('admin.levels.destroy', $level->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستوى؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>حذف المستوى
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
