@extends('layouts.app')
@section('title', 'تعديل ولي أمر')
@section('page-title', 'تعديل ولي أمر')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">أولياء الأمور</a></li>
    <li class="breadcrumb-item active">تعديل ولي أمر</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon-wrapper">
            <div class="header-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <div class="header-icon-glow"></div>
        </div>
        <div class="header-text">
            <h4>تعديل بيانات ولي الأمر</h4>
            <p>تعديل معلومات وإعدادات حساب ولي الأمر</p>
        </div>
    </div>
</div>

<div class="form-card-edit">
    <div class="form-header-edit">
        <div class="form-title-icon">
            <i class="fas fa-user-cog"></i>
        </div>
        <span>معلومات ولي الأمر</span>
        <div class="form-header-decoration">
            <i class="fas fa-moon"></i>
        </div>
    </div>
    <div class="form-body-edit">
        @if(session('success'))
            <div class="alert-success-edit">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-danger-edit">
                <i class="fas fa-exclamation-triangle"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.parents.update', $parent->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="avatar-edit-section">
                <div class="avatar-edit-wrapper">
                    <div class="avatar-edit-preview">
                        <img src="{{ $parent->avatar_url }}" alt="Avatar" class="avatar-img">
                        <div class="avatar-edit-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <div class="avatar-edit-info">
                        <h5>{{ $parent->name }}</h5>
                        <p>صورة الملف الشخصي</p>
                        <label class="upload-btn-edit">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>تغيير الصورة</span>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </label>
                        <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group-edit">
                        <label class="form-label-edit">
                            <i class="fas fa-user"></i>
                            اسم ولي الأمر <span class="required">*</span>
                        </label>
                        <input type="text" name="name" class="form-control-edit" value="{{ old('name', $parent->name) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group-edit">
                        <label class="form-label-edit">
                            <i class="fas fa-at"></i>
                            اسم المستخدم <span class="required">*</span>
                        </label>
                        <input type="text" name="username" class="form-control-edit" value="{{ old('username', $parent->username) }}" required>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group-edit">
                        <label class="form-label-edit">
                            <i class="fas fa-phone"></i>
                            رقم الهاتف
                        </label>
                        <input type="text" name="phone" class="form-control-edit" value="{{ old('phone', $parent->phone) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group-edit">
                        <label class="form-label-edit">
                            <i class="fas fa-envelope"></i>
                            البريد الإلكتروني
                        </label>
                        <input type="email" name="email" class="form-control-edit" value="{{ old('email', $parent->email) }}">
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group-edit">
                        <label class="form-label-edit">
                            <i class="fas fa-lock"></i>
                            كلمة المرور الجديدة
                        </label>
                        <input type="password" name="password" class="form-control-edit" placeholder="اتركها فارغة إذا لم تريد التغيير">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group-edit">
                        <label class="form-label-edit">
                            <i class="fas fa-lock"></i>
                            تأكيد كلمة المرور
                        </label>
                        <input type="password" name="password_confirmation" class="form-control-edit" placeholder="تأكيد كلمة المرور">
                    </div>
                </div>
            </div>

            @if($parent->parentProfile)
            <div class="form-group-edit">
                <label class="form-label-edit">
                    <i class="fas fa-link"></i>
                    صلة القرابة
                </label>
                <select name="relationship" class="form-select-edit">
                    <option value="father" {{ $parent->parentProfile->relationship == 'father' ? 'selected' : '' }}>الأب</option>
                    <option value="mother" {{ $parent->parentProfile->relationship == 'mother' ? 'selected' : '' }}>الأم</option>
                    <option value="guardian" {{ $parent->parentProfile->relationship == 'guardian' ? 'selected' : '' }}>ولي أمر</option>
                </select>
            </div>
            @endif

            <div class="form-actions-edit">
                <button type="submit" class="btn-save-edit">
                    <i class="fas fa-save"></i>
                    <span>تحديث البيانات</span>
                </button>
                <a href="{{ route('admin.parents.index') }}" class="btn-back-edit">
                    <i class="fas fa-arrow-right"></i>
                    <span>العودة للقائمة</span>
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.header-icon-wrapper { position: relative; }
.header-icon-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90px;
    height: 90px;
    background: radial-gradient(circle, rgba(212,160,48,0.2) 0%, transparent 70%);
    border-radius: 50%;
    z-index: 0;
}
.form-card-edit {
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
}
.form-header-edit {
    padding: 16px 16px;
    border-bottom: 2px solid rgba(212,160,48,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
    position: relative;
}
.form-title-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}
.form-header-edit span { font-size: 18px; font-weight: 700; color: #182B48; }
.form-header-decoration {
    position: absolute;
    left: 30px;
    font-size: 25px;
    color: #d4a030;
    opacity: 0.15;
}
.form-body-edit { padding: 16px; }
.alert-success-edit {
    background: linear-gradient(135deg, rgba(39,174,96,0.1), rgba(46,204,113,0.1));
    border: 1px solid rgba(39,174,96,0.2);
    color: #27ae60;
    padding: 16px 16px;
    border-radius: 13px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
}
.alert-success-edit i { font-size: 20px; }
.alert-danger-edit {
    background: linear-gradient(135deg, rgba(192,57,43,0.1), rgba(192,57,43,0.1));
    border: 1px solid rgba(192,57,43,0.2);
    color: #c0392b;
    padding: 16px 16px;
    border-radius: 13px;
    margin-bottom: 16px;
}
.alert-danger-edit i { margin-left: 10px; }
.alert-danger-edit ul { padding-right: 20px; }
.avatar-edit-section {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 16px;
    background: linear-gradient(135deg, #f8f9fa, #f0f4f8);
    border-radius: 13px;
    margin-bottom: 16px;
}
.avatar-edit-wrapper { display: flex; align-items: center; gap: 25px; }
.avatar-edit-preview {
    position: relative;
    width: 110px;
    height: 110px;
}
.avatar-img {
    width: 110px;
    height: 110px;
    border-radius: 13px;
    object-fit: cover;
    border: 3px solid #d4a030;
}
.avatar-edit-overlay {
    position: absolute;
    bottom: -8px;
    right: -8px;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
    border: 3px solid #fff;
}
.avatar-edit-info h5 { color: #182B48; font-weight: 700; margin: 0 0 5px 0; font-size: 16px; }
.avatar-edit-info p { color: #7f8c8d; font-size: 13px; margin: 0 0 12px 0; }
.upload-btn-edit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 8px;
}
.upload-btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(212,160,48,0.3);
}
.upload-btn-edit input { display: none; }
.text-muted { color: #7f8c8d; font-size: 12px; }
.form-group-edit { margin-bottom: 16px; }
.form-label-edit {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    color: #182B48;
    margin-bottom: 10px;
    font-size: 15px;
}
.form-label-edit i { color: #d4a030; font-size: 16px; }
.required { color: #c0392b; }
.form-control-edit, .form-select-edit {
    width: 100%;
    padding: 14px 16px;
    border-radius: 13px;
    border: 2px solid #e9ecef;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fff;
}
.form-control-edit:focus, .form-select-edit:focus {
    border-color: #d4a030;
    box-shadow: 0 0 0 4px rgba(212,160,48,0.15);
    outline: none;
}
.form-control-edit::placeholder { color: #bdc3c7; }
.form-actions-edit {
    display: flex;
    gap: 15px;
    padding-top: 25px;
    border-top: 2px solid rgba(212,160,48,0.1);
    margin-top: 30px;
}
.btn-save-edit {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 35px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border: none;
    border-radius: 13px;
    font-weight: 700;
    font-size: 15px;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.btn-save-edit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(24,43,72,0.35);
    color: #fff;
}
.btn-back-edit {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 35px;
    background: #f8f9fa;
    color: #182B48;
    border: 2px solid #e9ecef;
    border-radius: 13px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    text-decoration: none;
}
.btn-back-edit:hover { border-color: #d4a030; color: #d4a030; background: #fff; }
</style>
@endsection