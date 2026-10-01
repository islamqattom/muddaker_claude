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
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
}
body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }
.form-card {
    background: #fff;
    border-radius: 13px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 16px;
}
.form-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    position: relative;
}
.form-header i { color: var(--gold); font-size: 20px; }
.form-header h5 { margin: 0; font-weight: 700; font-size: 18px; }
.form-header::after {
    content: '';
    position: absolute;
    bottom: 0; right: 0; left: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.form-body { padding: 16px 16px; }
.section-title {
    display: flex; align-items: center; gap: 10px;
    color: var(--primary); font-weight: 700; font-size: 15px;
    margin-bottom: 16px; padding-bottom: 12px;
    border-bottom: 2px solid rgba(24,43,72,0.08);
}
.section-title i { color: var(--gold); font-size: 16px; }
.section-title::before { content: '◆'; color: var(--gold); font-size: 8px; }
.form-label {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 8px;
    font-size: 14px;
}
.form-control, .form-select {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
}
.form-control:focus, .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212,160,48,0.12);
}
.form-control.is-invalid, .form-select.is-invalid {
    border-color: var(--danger);
}
.invalid-feedback {
    color: var(--danger);
    font-size: 12px;
    margin-top: 5px;
}
.text-danger { color: var(--danger); }
.avatar-upload {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.02) 0%, rgba(42,69,112,0.04) 100%);
    border-radius: 13px;
    border: 2px dashed rgba(24,43,72,0.1);
}
.avatar-preview {
    width: 80px; height: 80px;
    border-radius: 13px;
    object-fit: cover;
    border: 3px solid var(--gold);
}
.avatar-info { flex: 1; }
.avatar-info input { cursor: pointer; }
.btn-submit {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 16px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    color: #fff; border: none; border-radius: 12px;
    font-weight: 700; font-size: 15px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    box-shadow: 0 4px 15px rgba(212,160,48,0.3);
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212,160,48,0.4);
}
.btn-submit i { font-size: 16px; }
.btn-cancel {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 16px;
    background: #fff;
    color: var(--primary); border: 2px solid var(--primary);
    border-radius: 12px;
    font-weight: 700; font-size: 15px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    text-decoration: none;
}
.btn-cancel:hover {
    background: var(--primary);
    color: #fff;
}
.alert-success {
    background: rgba(39,174,96,0.1);
    border: 1px solid rgba(39,174,96,0.3);
    border-radius: 12px;
    color: var(--success);
    padding: 16px 16px;
}
.alert-danger {
    background: rgba(192,57,43,0.1);
    border: 1px solid rgba(192,57,43,0.3);
    border-radius: 12px;
    color: var(--danger);
    padding: 16px 16px;
}
.alert-danger ul { margin: 0; padding-right: 20px; }
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #f0f4f8;
}
@media (max-width: 768px) {
.form-body { padding: 16px 15px; }
.form-actions { flex-direction: column; }
.avatar-upload { flex-direction: column; text-align: center; }
}
</style>

<x-page-hero icon="fas fa-user-edit">
    <h1 class="page-banner-title">تعديل بيانات الطالب</h1>
    <p class="page-banner-sub">تعديل بيانات الطالب في نظام التحفيظ</p>
</x-page-hero>

<div class="form-card">
    <div class="form-header">
        <i class="fas fa-user-cog"></i>
        <h5>معلومات الطالب</h5>
    </div>
    <div class="form-body">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.students.update', $student->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="avatar-upload mb-4">
                <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="avatar-preview">
                <div class="avatar-info">
                    <label class="form-label">صورة الملف الشخصي</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    <small style="color:#95a5a6">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-user-circle"></i>
                <span>معلومات الحساب</span>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">اسم الطالب <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $student->user->username) }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->user->phone) }}">
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
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="اتركها فارغة إذا لم تريد التغيير">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="أعد إدخال كلمة المرور">
                </div>
            </div>

            <div class="section-title mt-4">
                <i class="fas fa-graduation-cap"></i>
                <span>معلومات الطالب</span>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
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
                <div class="col-md-6">
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

            <div class="row g-4 mt-2">
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <label class="form-label">الرقم الوطني</label>
                    <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}" placeholder="أدخل الرقم الوطني">
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">تاريخ الميلاد</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ التسجيل</label>
                    <input type="date" name="enrollment_date" class="form-control" value="{{ old('enrollment_date', $student->enrollment_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">العنوان</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $student->address) }}" placeholder="أدخل العنوان">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="1" placeholder="ملاحظات إضافية">{{ old('notes', $student->notes) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    <span>تحديث البيانات</span>
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn-cancel">
                    <i class="fas fa-arrow-right"></i>
                    <span>رجوع</span>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection