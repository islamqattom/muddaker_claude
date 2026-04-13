@extends('layouts.app')
@section('title', 'تعديل طالب')
@section('page-title', 'تعديل طالب')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">الطلاب</a></li>
    <li class="breadcrumb-item active">تعديل {{ $student->user->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    تعديل بيانات الطالب
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

                <form method="POST" action="{{ route('admin.students.update', $student->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="col-12 mb-3">
                        <label class="form-label">صورة الملف الشخصي</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:70px;height:70px;object-fit:cover;">
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
                                <label class="form-label">اسم الطالب <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $student->user->username) }}" required>
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
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->user->email) }}">
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
                        <i class="fas fa-graduation-cap me-1"></i>معلومات الطالب
                    </h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">المستوى <span class="text-danger">*</span></label>
                                <select name="level_id" class="form-select @error('level_id') is-invalid @enderror" required>
                                    <option value="">اختر المستوى</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level->id }}" {{ old('level_id', $student->level_id) == $level->id ? 'selected' : '' }}>{{ $level->name }} - المستوى {{ $level->level_number }}</option>
                                    @endforeach
                                </select>
                                @error('level_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">المشرف <span class="text-danger">*</span></label>
                                <select name="supervisor_id" class="form-select @error('supervisor_id') is-invalid @enderror" required>
                                    <option value="">اختر المشرف</option>
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}" {{ old('supervisor_id', $student->supervisor_id) == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                                    @endforeach
                                </select>
                                @error('supervisor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">فئة الرسوم</label>
                                <select name="fee_category_id" class="form-select @error('fee_category_id') is-invalid @enderror">
                                    <option value="">— بدون فئة —</option>
                                    @foreach($feeCategories as $category)
                                        <option value="{{ $category->id }}" {{ old('fee_category_id', $student->fee_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }} ({{ number_format($category->amount, 2) }} دينار)
                                        </option>
                                    @endforeach
                                </select>
                                @error('fee_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الرقم الوطني</label>
                                <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاريخ الميلاد</label>
                                <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $student->address) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاريخ التسجيل</label>
                                <input type="date" name="enrollment_date" class="form-control" value="{{ old('enrollment_date', $student->enrollment_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $student->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>تحديث
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>رجوع
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
