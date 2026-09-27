@extends('layouts.app')
@section('title', 'بيانات المشرف')
@section('page-title', 'بيانات المشرف')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.supervisors.index') }}">المشرفين</a></li>
    <li class="breadcrumb-item active">{{ $supervisor->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Supervisor Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-tie me-2"></i>
                    معلومات المشرف
                </h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-auto">
                        <img src="{{ $supervisor->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover">
                    </div>
                    <div class="col">
                        <h4 class="mb-1">{{ $supervisor->name }}</h4>
                        @if($supervisor->supervisor)
                            <p class="text-muted mb-0">{{ $supervisor->supervisor->employee_number }}</p>
                        @endif
                    </div>
                </div>

                <table class="table table-borderless">
                    <tr>
                        <th width="150">اسم المستخدم</th>
                        <td>{{ $supervisor->username ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>رقم الهاتف</th>
                        <td>{{ $supervisor->phone }}</td>
                    </tr>
                    <tr>
                        <th>البريد الإلكتروني</th>
                        <td>{{ $supervisor->email ?? '-' }}</td>
                    </tr>
                    @if($supervisor->supervisor)
                    <tr>
                        <th>التخصص</th>
                        <td>{{ $supervisor->supervisor->specialization ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ التعيين</th>
                        <td>{{ $supervisor->supervisor->hire_date?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Supervised Students Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    الطلاب تحت إشرافه
                </h6>
                <span class="badge bg-info">{{ $supervisor->supervisedStudents->count() }} طالب</span>
            </div>
            <div class="card-body p-0">
                @if($supervisor->supervisedStudents->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                        <p class="mb-0">لا يوجد طلاب تحت إشراف هذا المشرف</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الطالب</th>
                                    <th>رقم الطالب</th>
                                    <th>المستوى</th>
                                    <th>تاريخ التسجيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supervisor->supervisedStudents as $student)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover">
                                            <span>{{ $student->user->name }}</span>
                                        </div>
                                    </td>
                                    <td><code>{{ $student->student_number }}</code></td>
                                    <td>{{ $student->level->name ?? '-' }}</td>
                                    <td>{{ $student->enrollment_date?->format('d/m/Y') ?? '-' }}</td>
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
        <div class="card sticky-top" style="top:20px">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>الإجراءات</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.supervisors.edit', $supervisor->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>تعديل البيانات
                    </a>
                    <a href="{{ route('admin.supervisors.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                    <form action="{{ route('admin.supervisors.destroy', $supervisor->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشرف؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>حذف المشرف
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
