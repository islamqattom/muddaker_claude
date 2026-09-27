@extends('layouts.app')
@section('title', 'إضافة ولي أمر')
@section('page-title', 'إضافة ولي أمر')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">أولياء الأمور</a></li>
    <li class="breadcrumb-item active">إضافة ولي أمر</li>
@endsection

@section('content')

{{-- Islamic Pattern Header --}}
<div class="islamic-header-banner">
    <div class="banner-pattern"></div>
    <div class="bismillah-banner">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
</div>

{{-- Page Header --}}
<div class="page-header">
    <div class="header-icon">
        <i class="fas fa-user-plus"></i>
    </div>
    <div class="header-text">
        <h4>إضافة ولي أمر جديد</h4>
        <p>إدخال بيانات ولي أمر جديد للنظام</p>
    </div>
</div>

<div class="form-card-islamic">
    <div class="form-header-islamic">
        <div class="form-title-icon">
            <i class="fas fa-user-plus"></i>
        </div>
        <span>معلومات ولي الأمر</span>
        <div class="form-header-decoration">
            <i class="fas fa-star-of-david"></i>
        </div>
    </div>
    <div class="form-body-islamic">
        @if(session('success'))
            <div class="alert-success-islamic">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-danger-islamic">
                <i class="fas fa-exclamation-triangle"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.parents.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="avatar-upload-section">
                <div class="avatar-upload-wrapper">
                    <div class="avatar-preview">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="avatar-upload-info">
                        <h5>صورة الملف الشخصي</h5>
                        <p>رفع صورة لولي الأمر</p>
                        <label class="upload-btn">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>اختر صورة</span>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </label>
                        <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group-islamic">
                        <label class="form-label-islamic">
                            <i class="fas fa-user"></i>
                            اسم ولي الأمر <span class="required">*</span>
                        </label>
                        <input type="text" name="name" class="form-control-islamic" value="{{ old('name') }}" placeholder="أدخل الاسم الكامل" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group-islamic">
                        <label class="form-label-islamic">
                            <i class="fas fa-at"></i>
                            اسم المستخدم <span class="required">*</span>
                        </label>
                        <input type="text" name="username" class="form-control-islamic" value="{{ old('username') }}" placeholder="أرقام إنجليزية وحروف" required>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group-islamic">
                        <label class="form-label-islamic">
                            <i class="fas fa-phone"></i>
                            رقم الهاتف
                        </label>
                        <input type="text" name="phone" class="form-control-islamic" value="{{ old('phone') }}" placeholder="رقم الهاتف">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group-islamic">
                        <label class="form-label-islamic">
                            <i class="fas fa-envelope"></i>
                            البريد الإلكتروني
                        </label>
                        <input type="email" name="email" class="form-control-islamic" value="{{ old('email') }}" placeholder="البريد الإلكتروني">
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group-islamic">
                        <label class="form-label-islamic">
                            <i class="fas fa-lock"></i>
                            كلمة المرور <span class="required">*</span>
                        </label>
                        <input type="password" name="password" class="form-control-islamic" required placeholder="كلمة المرور">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group-islamic">
                        <label class="form-label-islamic">
                            <i class="fas fa-lock"></i>
                            تأكيد كلمة المرور <span class="required">*</span>
                        </label>
                        <input type="password" name="password_confirmation" class="form-control-islamic" required placeholder="تأكيد كلمة المرور">
                    </div>
                </div>
            </div>

            <div class="form-group-islamic">
                <label class="form-label-islamic">
                    <i class="fas fa-link"></i>
                    صلة القرابة
                </label>
                <select name="relationship" class="form-select-islamic">
                    <option value="father">الأب</option>
                    <option value="mother">الأم</option>
                    <option value="guardian">ولي أمر</option>
                </select>
            </div>

            <div class="form-group-islamic">
                <label class="form-label-islamic">
                    <i class="fas fa-user-graduate"></i>
                    ربط بطلاب
                </label>
                <select name="student_ids[]" class="form-select-islamic select2" multiple>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->student_number }})</option>
                    @endforeach
                </select>
                <small class="text-muted">يمكن اختيار واحد أو أكثر من الطلاب</small>
            </div>

            <div class="form-actions-islamic">
                <button type="submit" class="btn-save-islamic">
                    <i class="fas fa-save"></i>
                    <span>حفظ</span>
                </button>
                <a href="{{ route('admin.parents.index') }}" class="btn-back-islamic">
                    <i class="fas fa-arrow-right"></i>
                    <span>رجوع</span>
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.islamic-header-banner {
    position: relative;
    height: 70px;
    margin-bottom: 16px;
    padding: 16px 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 14px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
}
.banner-pattern {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}
.bismillah-banner {
    font-family: 'Amiri', serif;
    font-size: 22px;
    color: #d4a030;
    text-shadow: 0 2px 10px rgba(212,160,48,0.3);
    position: relative;
    z-index: 1;
    letter-spacing: 2px;
}
.form-card-islamic {
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
}
.form-header-islamic {
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
.form-header-islamic span {
    font-size: 18px;
    font-weight: 700;
    color: #182B48;
}
.form-header-decoration {
    position: absolute;
    left: 30px;
    font-size: 25px;
    color: #d4a030;
    opacity: 0.15;
}
.form-body-islamic { padding: 16px; }
.alert-success-islamic {
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
.alert-success-islamic i { font-size: 20px; }
.alert-danger-islamic {
    background: linear-gradient(135deg, rgba(192,57,43,0.1), rgba(192,57,43,0.1));
    border: 1px solid rgba(192,57,43,0.2);
    color: #c0392b;
    padding: 16px 16px;
    border-radius: 13px;
    margin-bottom: 16px;
}
.alert-danger-islamic i { margin-left: 10px; }
.alert-danger-islamic ul { padding-right: 20px; }
.avatar-upload-section {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 16px;
    background: linear-gradient(135deg, #f8f9fa, #f0f4f8);
    border-radius: 13px;
    margin-bottom: 16px;
}
.avatar-upload-wrapper { display: flex; align-items: center; gap: 25px; }
.avatar-preview {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 40px;
    border: 3px solid #d4a030;
}
.avatar-upload-info h5 { color: #182B48; font-weight: 700; margin: 0 0 5px 0; font-size: 16px; }
.avatar-upload-info p { color: #7f8c8d; font-size: 13px; margin: 0 0 12px 0; }
.upload-btn {
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
.upload-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(212,160,48,0.3); }
.upload-btn input { display: none; }
.text-muted { color: #7f8c8d; font-size: 12px; }
.form-group-islamic { margin-bottom: 16px; }
.form-label-islamic {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    color: #182B48;
    margin-bottom: 10px;
    font-size: 15px;
}
.form-label-islamic i { color: #d4a030; font-size: 16px; }
.required { color: #c0392b; }
.form-control-islamic, .form-select-islamic {
    width: 100%;
    padding: 14px 16px;
    border-radius: 13px;
    border: 2px solid #e9ecef;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fff;
}
.form-control-islamic:focus, .form-select-islamic:focus {
    border-color: #d4a030;
    box-shadow: 0 0 0 4px rgba(212,160,48,0.15);
    outline: none;
}
.form-control-islamic::placeholder { color: #bdc3c7; }
.form-actions-islamic {
    display: flex;
    gap: 15px;
    padding-top: 25px;
    border-top: 2px solid rgba(212,160,48,0.1);
    margin-top: 30px;
}
.btn-save-islamic {
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
.btn-save-islamic:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(24,43,72,0.35);
    color: #fff;
}
.btn-back-islamic {
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
.btn-back-islamic:hover { border-color: #d4a030; color: #d4a030; background: #fff; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        dir: 'rtl',
        placeholder: 'اختر الطلاب',
        allowClear: true
    });
});
</script>
@endpush
@endsection