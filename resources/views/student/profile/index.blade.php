@extends('layouts.app')

@section('title', 'ملفي الشخصي')
@section('page-title', 'ملفي الشخصي')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">ملفي الشخصي</li>
@endsection

@section('content')
<div class="row">
    <!-- Student Information Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                <h5 class="mb-1">{{ $student->user->name }}</h5>
                <p class="text-muted mb-3">{{ $student->user->email }}</p>

                <hr>

                <div class="text-start">
                    <div class="mb-3">
                        <label class="text-muted small d-block">رقم الطالب</label>
                        <span class="fw-semibold">{{ $student->student_number }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">رقم الهوية</label>
                        <span class="fw-semibold">{{ $student->national_id }}</span>
                    </div>
                    @if($student->level)
                    <div class="mb-3">
                        <label class="text-muted small d-block">المستوى</label>
                        <span class="badge bg-secondary">{{ $student->level->name }}</span>
                    </div>
                    @endif
                    @if($student->supervisor)
                    <div class="mb-3">
                        <label class="text-muted small d-block">المشرف</label>
                        <span class="fw-semibold">{{ $student->supervisor->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    تعديل البيانات الشخصية
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="col-12 mb-3">
                        <label class="form-label">صورة الملف الشخصي</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                            <div>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                            </div>
                        </div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ $student->user->name }}" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم المستخدم</label>
                            <input type="text" name="username" class="form-control" value="{{ $student->user->username }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $student->user->email }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $student->phone }}" placeholder="05xxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ $student->address }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ التغييرات
                    </button>
                </form>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    معلومات إضافية
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small d-block">الجزء الحالي</label>
                        <span class="fw-semibold">
                            @if($student->current_part)
                                جزء {{ $student->current_part }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small d-block">الصفحة الحالية</label>
                        <span class="fw-semibold">
                            @if($student->current_page)
                                صفحة {{ $student->current_page }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small d-block">تاريخ التسجيل</label>
                        <span class="fw-semibold">
                            @if($student->enrollment_date)
                                {{ $student->enrollment_date->format('Y/m/d') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lock me-2"></i>
                    تغيير كلمة المرور
                </h6>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('student.profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">كلمة المرور الحالية</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required minlength="8">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>تغيير كلمة المرور
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
