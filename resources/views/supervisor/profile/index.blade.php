@extends('layouts.app')
@section('title', 'الملف الشخصي')
@section('page-title', 'الملف الشخصي')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الملف الشخصي</li>
@endsection

@section('content')
<style>
.profile-page {
    --primary: #182B48;
    --primary-light: #2A4570;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --danger: #c0392b;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
}

.profile-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 20px;
    padding: 35px;
    position: relative;
    overflow: hidden;
    margin-bottom: 25px;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.profile-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}

.profile-content {
    display: flex;
    align-items: center;
    gap: 30px;
    position: relative;
    z-index: 1;
}

.profile-avatar-wrapper {
    position: relative;
}

.profile-avatar {
    width: 130px;
    height: 130px;
    border-radius: 20px;
    object-fit: cover;
    border: 4px solid var(--gold);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3), 0 0 40px rgba(212, 160, 48, 0.2);
}

.profile-avatar-badge {
    position: absolute;
    bottom: -8px;
    right: -8px;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    box-shadow: 0 4px 15px rgba(212, 160, 48, 0.4);
    border: 3px solid #fff;
}

.profile-info {
    flex: 1;
}

.profile-info h3 {
    color: #fff;
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.profile-role {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(212, 160, 48, 0.2);
    color: var(--gold);
    padding: 8px 18px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 15px;
    border: 1px solid rgba(212, 160, 48, 0.3);
}

.profile-stats {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.profile-stat {
    text-align: center;
}

.profile-stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
}

.profile-stat-label {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 4px;
}

.profile-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24, 43, 72, 0.05);
    overflow: hidden;
    margin-bottom: 25px;
}

.profile-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.03) 0%, rgba(42, 69, 112, 0.05) 100%);
    border-bottom: 1px solid rgba(24, 43, 72, 0.05);
    position: relative;
}

.profile-card-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}

.profile-card-header i {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gold);
    font-size: 18px;
    box-shadow: 0 4px 15px rgba(24, 43, 72, 0.2);
}

.profile-card-header h5 {
    color: var(--primary);
    font-weight: 700;
    font-size: 18px;
    margin: 0;
}

.profile-card-body {
    padding: 28px;
}

.form-label {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 10px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-label i {
    color: var(--gold);
    font-size: 12px;
}

.form-control, .form-select {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 14px 18px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
}

.form-control:focus, .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212, 160, 48, 0.12);
}

.form-control.is-invalid {
    border-color: var(--danger);
}

.invalid-feedback {
    color: var(--danger);
    font-size: 12px;
    margin-top: 5px;
}

.text-danger { color: var(--danger); }

.image-upload-card {
    border: 2px dashed rgba(24, 43, 72, 0.15);
    border-radius: 16px;
    padding: 25px;
    text-align: center;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.04) 100%);
    transition: all 0.3s ease;
    cursor: pointer;
}

.image-upload-card:hover {
    border-color: var(--gold);
    background: linear-gradient(135deg, rgba(212, 160, 48, 0.05) 0%, rgba(180, 128, 44, 0.08) 100%);
}

.image-upload-card i {
    font-size: 40px;
    color: var(--gold);
    margin-bottom: 10px;
}

.image-upload-card p {
    color: #6c7a89;
    font-size: 13px;
    margin: 0;
}

.image-upload-card input {
    display: none;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 35px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    box-shadow: 0 4px 15px rgba(212, 160, 48, 0.3);
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 160, 48, 0.4);
}

.btn-save i {
    font-size: 16px;
}

.alert-success {
    background: rgba(39, 174, 96, 0.1);
    border: 1px solid rgba(39, 174, 96, 0.3);
    border-radius: 12px;
    color: var(--success);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success i {
    font-size: 18px;
}

@media (max-width: 768px) {
    .profile-content {
        flex-direction: column;
        text-align: center;
    }

    .profile-stats {
        justify-content: center;
    }

    .profile-card-body {
        padding: 20px 15px;
    }
}
</style>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="profile-page">
    <div class="profile-header">
        <div class="profile-content">
            <div class="profile-avatar-wrapper">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="profile-avatar">
                <div class="profile-avatar-badge">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
            <div class="profile-info">
                <h3>{{ $user->name }}</h3>
                <div class="profile-role">
                    <i class="fas fa-shield-alt"></i>
                    مشرف نظام التسميع
                </div>
                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $stats['students_count'] ?? 0 }}</div>
                        <div class="profile-stat-label">طالب</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $stats['sessions_count'] ?? 0 }}</div>
                        <div class="profile-stat-label">جلسة</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $stats['warnings_count'] ?? 0 }}</div>
                        <div class="profile-stat-label">إنذار</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="fas fa-user-edit"></i>
                    <h5>تعديل الملف الشخصي</h5>
                </div>
                <div class="profile-card-body">
                    <form method="POST" action="{{ route('supervisor.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="image-upload-card mb-4">
                            <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(this)">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>انقر لتغيير صورة الملف الشخصي</p>
                            <small style="color: #95a5a6;">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user"></i>
                                    الاسم الكامل
                                </label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $user->name }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-at"></i>
                                    اسم المستخدم
                                </label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ $user->username }}">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    البريد الإلكتروني
                                </label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i>
                                    رقم الهاتف
                                </label>
                                <input type="text" name="phone" class="form-control" value="{{ $user->phone ?? '' }}" placeholder="أدخل رقم الهاتف">
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-key"></i>
                                    كلمة المرور الجديدة
                                </label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="اتركها فارغة إذا لم تريد التغيير">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-key"></i>
                                    تأكيد كلمة المرور
                                </label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="أعد إدخال كلمة المرور">
                            </div>
                        </div>

                        <div class="mt-4" style="border-top: 1px solid #f0f4f8; padding-top: 25px;">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                <span>حفظ التغييرات</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection