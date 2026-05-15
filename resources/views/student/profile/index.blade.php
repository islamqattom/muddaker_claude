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

@push('styles')
<style>
.student-profile {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.student-profile .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.student-profile .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.student-profile .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.student-profile .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(212,160,48,0.35);
}

.student-profile .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.student-profile .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.student-profile .profile-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    height: 100%;
}

.student-profile .profile-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 30px;
    text-align: center;
    position: relative;
}

.student-profile .profile-header::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
}

.student-profile .avatar-wrapper {
    width: 120px; height: 120px;
    border-radius: 50%;
    border: 4px solid var(--gold);
    overflow: hidden;
    margin: 0 auto 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.student-profile .avatar-wrapper img { width: 100%; height: 100%; object-fit: cover; }

.student-profile .profile-name { color: white; font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.student-profile .profile-email { color: rgba(255,255,255,0.7); font-size: 13px; margin-bottom: 0; }

.student-profile .profile-body { padding: 24px; }

.student-profile .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid rgba(24,43,72,0.06);
}

.student-profile .info-item:last-child { border-bottom: none; }

.student-profile .info-label { font-size: 12px; color: #6c7a89; font-weight: 500; }
.student-profile .info-value { font-size: 14px; font-weight: 700; color: var(--navy); }

.student-profile .form-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 20px;
}

.student-profile .form-card-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.student-profile .form-card-header i { color: var(--gold); font-size: 18px; }
.student-profile .form-card-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.student-profile .form-card-body { padding: 24px; }

.student-profile .form-label { font-weight: 600; color: var(--navy); margin-bottom: 8px; font-size: 13px; }

.student-profile .form-control {
    border: 2px solid rgba(24,43,72,0.1);
    border-radius: 10px;
    padding: 10px 14px;
    transition: all 0.2s ease;
}

.student-profile .form-control:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
}

.student-profile .form-control:disabled { background: rgba(24,43,72,0.04); color: #6c7a89; }

.student-profile .avatar-upload {
    display: flex;
    align-items: center;
    gap: 16px;
}

.student-profile .avatar-upload img {
    width: 80px; height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--gold);
}

.student-profile .btn-save {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 28px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.student-profile .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); }

.student-profile .btn-password {
    background: linear-gradient(135deg, var(--warning), #e67e22);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 28px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.student-profile .btn-password:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(243,156,18,0.3); }

.student-profile .additional-card {
    background: linear-gradient(135deg, rgba(39,174,96,0.08), rgba(46,204,113,0.08));
    border: 2px solid rgba(39,174,96,0.15);
    border-radius: 16px;
    padding: 20px;
}

.student-profile .additional-item {
    text-align: center;
    padding: 12px;
}

.student-profile .additional-value { font-size: 22px; font-weight: 800; color: var(--navy); }
.student-profile .additional-label { font-size: 11px; color: #6c7a89; font-weight: 500; text-transform: uppercase; }

.student-profile .alert {
    border-radius: 12px;
    padding: 16px 20px;
    font-size: 14px;
}

.student-profile .alert-success { background: rgba(39,174,96,0.15); border-color: rgba(39,174,96,0.3); color: var(--success); }
.student-profile .alert-danger { background: rgba(192,57,43,0.15); border-color: rgba(192,57,43,0.3); color: var(--danger); }

@media (max-width: 992px) {
    .student-profile .page-hero { padding: 20px 24px; }
    .student-profile .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .student-profile .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="student-profile">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-user-cog"></i>
            </div>
            <div>
                <h1 class="hero-title">ملفي الشخصي</h1>
                <p class="hero-sub">عرض وتعديل بياناتك الشخصية</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar-wrapper">
                        <img src="{{ $student->user->avatar_url }}" alt="Avatar">
                    </div>
                    <h4 class="profile-name">{{ $student->user->name }}</h4>
                    <p class="profile-email">{{ $student->user->email }}</p>
                </div>
                <div class="profile-body">
                    <div class="info-item">
                        <span class="info-label">رقم الطالب</span>
                        <span class="info-value">{{ $student->student_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم الهوية</span>
                        <span class="info-value">{{ $student->national_id }}</span>
                    </div>
                    @if($student->level)
                    <div class="info-item">
                        <span class="info-label">المستوى</span>
                        <span class="badge-modern success">{{ $student->level->name }}</span>
                    </div>
                    @endif
                    @if($student->supervisor)
                    <div class="info-item">
                        <span class="info-label">المشرف</span>
                        <span class="info-value">{{ $student->supervisor->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="form-card">
                <div class="form-card-header">
                    <i class="fas fa-user-edit"></i>
                    <h5>تعديل البيانات الشخصية</h5>
                </div>
                <div class="form-card-body">
                    <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="avatar-upload mb-4">
                            <img src="{{ $student->user->avatar_url }}" alt="Avatar">
                            <div>
                                <label class="form-label">صورة الملف الشخصي</label>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted d-block mt-1">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الاسم</label>
                                <input type="text" name="name" class="form-control" value="{{ $student->user->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" name="username" class="form-control" value="{{ $student->user->username }}" disabled>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $student->user->email }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $student->phone }}" placeholder="05xxxxxxxx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ $student->address }}">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-save mt-4">
                            <i class="fas fa-save me-2"></i>حفظ التغييرات
                        </button>
                    </form>
                </div>
            </div>

            <div class="form-card">
                <div class="form-card-header">
                    <i class="fas fa-info-circle"></i>
                    <h5>معلومات إضافية</h5>
                </div>
                <div class="form-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="additional-card">
                                <div class="additional-item">
                                    <div class="additional-value">
                                        @if($student->current_part)
                                            {{ $student->current_part }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                    <div class="additional-label">الجزء الحالي</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="additional-card">
                                <div class="additional-item">
                                    <div class="additional-value">
                                        @if($student->current_page)
                                            {{ $student->current_page }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                    <div class="additional-label">الصفحة الحالية</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="additional-card">
                                <div class="additional-item">
                                    <div class="additional-value">
                                        @if($student->enrollment_date)
                                            {{ $student->enrollment_date->format('Y/m/d') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                    <div class="additional-label">تاريخ التسجيل</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="form-card">
                <div class="form-card-header" style="background: linear-gradient(135deg, var(--warning), #e67e22);">
                    <i class="fas fa-lock"></i>
                    <h5>تغيير كلمة المرور</h5>
                </div>
                <div class="form-card-body">
                    <form method="POST" action="{{ route('student.profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">كلمة المرور الحالية</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">كلمة المرور الجديدة</label>
                                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required minlength="8">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">تأكيد كلمة المرور</label>
                                <input type="password" name="new_password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-password mt-4">
                            <i class="fas fa-key me-2"></i>تغيير كلمة المرور
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-modern { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-modern.success { background: rgba(39,174,96,0.15); color: #27ae60; }
</style>
@endsection