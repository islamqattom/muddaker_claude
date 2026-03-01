@extends('layouts.app')

@section('title', 'تعديل بيانات الطالب')
@section('page-title', 'تعديل بيانات الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.students.index') }}">الطلاب</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.students.show', $student->id) }}">{{ $student->user->name }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<form action="{{ route('supervisor.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <!-- Student Basic Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>معلومات الطالب الأساسية</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم الطالب <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $student->user->username) }}" placeholder="أرقام إنجليزية وحروف" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->user->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="اتركها فارغة إذا لا تريد تغييرها">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات إضافية</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">المستوى <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-select @error('level_id') is-invalid @enderror" required>
                                <option value="">-- اختر المستوى --</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" {{ old('level_id', $student->level_id) == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الرقم الوطني</label>
                            <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">تاريخ الميلاد</label>
                            <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">تاريخ التسجيل</label>
                            <input type="date" name="enrollment_date" class="form-control" value="{{ old('enrollment_date', $student->enrollment_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $student->address) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $student->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>إجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle mb-2" style="width:70px;height:70px;object-fit:cover;">
                        <h5>{{ $student->user->name }}</h5>
                        <p class="text-muted mb-1">{{ $student->student_number }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">تغيير الصورة</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>حفظ التغييرات
                        </button>
                        <a href="{{ route('supervisor.students.show', $student->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
