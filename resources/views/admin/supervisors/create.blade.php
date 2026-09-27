@extends('layouts.app')
@section('title', 'إضافة مشرف')
@section('page-title', 'إضافة مشرف')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.supervisors.index') }}">المشرفين</a></li>
    <li class="breadcrumb-item active">إضافة مشرف</li>
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
}
.form-header i { color: var(--gold); font-size: 20px; }
.form-header h5 { margin: 0; font-weight: 700; font-size: 18px; }
.form-header::before {
    content: '';
    position: absolute;
    top: 0; right: 0; left: 0;
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
.image-upload {
    border: 2px dashed rgba(24,43,72,0.15);
    border-radius: 13px;
    padding: 16px;
    text-align: center;
    background: linear-gradient(135deg, rgba(24,43,72,0.02) 0%, rgba(42,69,112,0.04) 100%);
    transition: all 0.3s ease;
    cursor: pointer;
}
.image-upload:hover {
    border-color: var(--gold);
    background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%);
}
.image-upload i { font-size: 40px; color: var(--gold); margin-bottom: 10px; }
.image-upload p { color: #6c7a89; font-size: 13px; margin: 0; }
.image-upload input { display: none; }
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
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="header-text">
            <h4>إضافة مشرف جديد</h4>
            <p>إضافة مشرف جديد للنظام</p>
        </div>
    </div>
</div>

<div class="form-card">
    <div class="form-header">
        <i class="fas fa-user-plus"></i>
        <h5>معلومات المشرف</h5>
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

        <form method="POST" action="{{ route('admin.supervisors.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="image-upload mb-4">
                <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(this)">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>اسحب الصورة هنا أو انقر للاختيار</p>
                <small style="color:#95a5a6">الصيغ المدعومة: jpeg, png, jpg, gif, webp (الحد الأقصى 10MB)</small>
            </div>

            <div class="section-title">
                <i class="fas fa-user-circle"></i>
                <span>معلومات الحساب</span>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">اسم المشرف <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="أدخل اسم المشرف" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="حروف إنجليزية وأرقام وشرطة سفلية (_)" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="أدخل رقم الهاتف">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="أدخل البريد الإلكتروني">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="أدخل كلمة المرور" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="أعد إدخال كلمة المرور" required>
                </div>
            </div>

            <div class="section-title mt-4">
                <i class="fas fa-briefcase"></i>
                <span>معلومات العمل</span>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">التخصص</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" placeholder="مثال: حفظ القرآن الكريم">
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ التعيين</label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', date('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    <span>حفظ المشرف</span>
                </button>
                <a href="{{ route('admin.supervisors.index') }}" class="btn-cancel">
                    <i class="fas fa-arrow-right"></i>
                    <span>رجوع</span>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection