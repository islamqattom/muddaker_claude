@extends('layouts.app')
@section('title', 'تعديل مشرف')
@section('page-title', 'تعديل مشرف')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.supervisors.index') }}">المشرفين</a></li>
    <li class="breadcrumb-item active">تعديل {{ $supervisor->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    تعديل بيانات المشرف
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.supervisors.update', $supervisor->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="col-12 mb-3">
                        <label class="form-label">صورة الملف الشخصي</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $supervisor->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:70px;height:70px;object-fit:cover;">
                            <div>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                            </div>
                        </div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 text-primary">
                        <i class="fas fa-user me-1"></i>معلومات الحساب
                    </h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المشرف <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supervisor->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $supervisor->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supervisor->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supervisor->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور الجديدة</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="اتركها فارغة إذا لم تريد التغيير">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تأكيد كلمة المرور</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                        <i class="fas fa-briefcase me-1"></i>معلومات العمل
                    </h6>

                    @if($supervisor->supervisor)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">رقم الموظف</label>
                                <input type="text" class="form-control" value="{{ $supervisor->supervisor->employee_number }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاريخ التعيين</label>
                                <input type="text" class="form-control" value="{{ $supervisor->supervisor->hire_date?->format('d/m/Y') }}" disabled>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>تحديث
                        </button>
                        <a href="{{ route('admin.supervisors.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>رجوع
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
