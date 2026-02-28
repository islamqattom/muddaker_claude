@extends('layouts.app')
@section('title', 'بيانات ولي الأمر')
@section('page-title', 'بيانات ولي الأمر')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">أولياء الأمور</a></li>
    <li class="breadcrumb-item active">{{ $parent->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Parent Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    معلومات ولي الأمر
                </h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-auto">
                        <img src="{{ $parent->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                    </div>
                    <div class="col">
                        <h4 class="mb-1">{{ $parent->name }}</h4>
                        <p class="text-muted mb-0">{{ $parent->username }}</p>
                        @if($parent->parentProfile)
                            <span class="badge bg-secondary mt-2">
                                @if($parent->parentProfile->relationship === 'father')
                                    الأب
                                @elseif($parent->parentProfile->relationship === 'mother')
                                    الأم
                                @else
                                    {{ $parent->parentProfile->relationship }}
                                @endif
                            </span>
                        @endif
                    </div>
                </div>

                <table class="table table-borderless">
                    <tr>
                        <th width="150">رقم الهاتف</th>
                        <td>{{ $parent->phone }}</td>
                    </tr>
                    <tr>
                        <th>البريد الإلكتروني</th>
                        <td>{{ $parent->email ?? '-' }}</td>
                    </tr>
                    @if($parent->parentProfile && $parent->parentProfile->national_id)
                    <tr>
                        <th>الرقم الوطني</th>
                        <td>{{ $parent->parentProfile->national_id }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Linked Students Card -->
        @if($parent->parentProfile && $parent->parentProfile->students->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    الطلاب المرتبطون
                </h6>
                <span class="badge bg-info">{{ $parent->parentProfile->students->count() }} طالب</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>اسم الطالب</th>
                                <th>رقم الطالب</th>
                                <th>المستوى</th>
                                <th>المشرف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parent->parentProfile->students as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                        <span>{{ $student->user->name }}</span>
                                    </div>
                                </td>
                                <td><code>{{ $student->student_number }}</code></td>
                                <td>{{ $student->level->name ?? '-' }}</td>
                                <td>{{ $student->supervisor->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>الإجراءات</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.parents.edit', $parent->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>تعديل البيانات
                    </a>
                    <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                    <form action="{{ route('admin.parents.destroy', $parent->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا ولي الأمر؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>حذف ولي الأمر
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
