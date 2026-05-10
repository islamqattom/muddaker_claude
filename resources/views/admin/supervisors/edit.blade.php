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

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #6c7a89; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.form-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 30px;
}
.form-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 28px;
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

.form-body { padding: 30px 28px; }

.section-title {
    display: flex; align-items: center; gap: 10px;
    color: var(--primary); font-weight: 700; font-size: 15px;
    margin-bottom: 20px; padding-bottom: 12px;
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
    padding: 20px;
    background: linear-gradient(135deg, rgba(24,43,72,0.02) 0%, rgba(42,69,112,0.04) 100%);
    border-radius: 16px;
    border: 2px dashed rgba(24,43,72,0.1);
}
.avatar-preview {
    width: 80px; height: 80px;
    border-radius: 16px;
    object-fit: cover;
    border: 3px solid var(--gold);
}
.avatar-info { flex: 1; }
.avatar-info input { cursor: pointer; }

.btn-submit {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 30px;
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
    padding: 14px 30px;
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
    padding: 16px 20px;
}
.alert-danger {
    background: rgba(192,57,43,0.1);
    border: 1px solid rgba(192,57,43,0.3);
    border-radius: 12px;
    color: var(--danger);
    padding: 16px 20px;
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
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
    .form-body { padding: 20px 15px; }
    .form-actions { flex-direction: column; }
    .avatar-upload { flex-direction: column; text-align: center; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-user-edit"></i>
        </div>
        <div class="header-text">
            <h4>تعديل بيانات المشرف</h4>
            <p>تعديل بيانات المشرف في النظام</p>
        </div>
    </div>
</div>

<div class="form-card">
    <div class="form-header">
        <i class="fas fa-user-cog"></i>
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

        <form method="POST" action="{{ route('admin.supervisors.update', $supervisor->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="avatar-upload mb-4">
                <img src="{{ $supervisor->avatar_url }}" alt="Avatar" class="avatar-preview">
                <div class="avatar-info">
                    <label class="form-label">صورة الملف الشخصي</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    <small style="color: #95a5a6;">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
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
                    <label class="form-label">اسم المشرف <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supervisor->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $supervisor->username) }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supervisor->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supervisor->email) }}">
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
                <i class="fas fa-briefcase"></i>
                <span>معلومات العمل</span>
            </div>

            @if($supervisor->supervisor)
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">رقم الموظف</label>
                    <input type="text" class="form-control" value="{{ $supervisor->supervisor->employee_number }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">التخصص</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $supervisor->supervisor->specialization) }}" placeholder="مثال: حفظ القرآن الكريم">
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label class="form-label">تاريخ التعيين</label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', $supervisor->supervisor->hire_date?->format('Y-m-d')) }}">
                </div>
            </div>
            @endif

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    <span>تحديث البيانات</span>
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