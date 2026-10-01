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

<x-page-hero icon="fas fa-user-graduate">
    <h1 class="page-banner-title">تعديل بيانات الطالب</h1>
</x-page-hero>

<div class="quranic-verse-header mb-4">
    <div class="verse-decoration"></div>
    <p class="quranic-verse">﴿ خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ ﴾</p>
    <div class="verse-decoration"></div>
</div>

<form action="{{ route('supervisor.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="main-form-card">
                <div class="card-header-custom">
                    <div class="header-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="header-text">
                        <h5>معلومات الطالب الأساسية</h5>
                        <p>البيانات الشخصية للطالب</p>
                    </div>
                </div>
                <div class="card-body-custom">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-user me-2"></i>اسم الطالب <span class="required">*</span>
                            </label>
                            <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror" value="{{ old('name', $student->user->name) }}" required>
                            @error('name')
                                <div class="error-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-at me-2"></i>اسم المستخدم <span class="required">*</span>
                            </label>
                            <input type="text" name="username" class="form-control-custom @error('username') is-invalid @enderror" value="{{ old('username', $student->user->username) }}" placeholder="أرقام إنجليزية وحروف" required>
                            @error('username')
                                <div class="error-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-phone me-2"></i>رقم الهاتف
                            </label>
                            <input type="text" name="phone" class="form-control-custom @error('phone') is-invalid @enderror" value="{{ old('phone', $student->phone) }}">
                            @error('phone')
                                <div class="error-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-envelope me-2"></i>البريد الإلكتروني
                            </label>
                            <input type="email" name="email" class="form-control-custom @error('email') is-invalid @enderror" value="{{ old('email', $student->user->email) }}">
                            @error('email')
                                <div class="error-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">
                                <i class="fas fa-key me-2"></i>كلمة المرور الجديدة
                            </label>
                            <input type="password" name="password" class="form-control-custom @error('password') is-invalid @enderror" placeholder="اتركها فارغة إذا لم تريد تغييرها">
                            @error('password')
                                <div class="error-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-form-card mt-4">
                <div class="card-header-custom">
                    <div class="header-icon secondary">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="header-text">
                        <h5>معلومات إضافية</h5>
                        <p>بيانات التسجيل والمستوى</p>
                    </div>
                </div>
                <div class="card-body-custom">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-layer-group me-2"></i>المستوى <span class="required">*</span>
                            </label>
                            <select name="level_id" class="form-select-custom @error('level_id') is-invalid @enderror" required>
                                <option value="">-- اختر المستوى --</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" {{ old('level_id', $student->level_id) == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level_id')
                                <div class="error-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-id-card me-2"></i>الرقم الوطني
                            </label>
                            <input type="text" name="national_id" class="form-control-custom" value="{{ old('national_id', $student->national_id) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-calendar-alt me-2"></i>تاريخ الميلاد
                            </label>
                            <input type="date" name="birth_date" class="form-control-custom" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">
                                <i class="fas fa-calendar-plus me-2"></i>تاريخ التسجيل
                            </label>
                            <input type="date" name="enrollment_date" class="form-control-custom" value="{{ old('enrollment_date', $student->enrollment_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">
                                <i class="fas fa-map-marker-alt me-2"></i>العنوان
                            </label>
                            <input type="text" name="address" class="form-control-custom" value="{{ old('address', $student->address) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">
                                <i class="fas fa-sticky-note me-2"></i>ملاحظات
                            </label>
                            <textarea name="notes" class="form-control-custom textarea-custom" rows="3">{{ old('notes', $student->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sidebar-card sticky-top" style="top: 20px;">
                <div class="card-header-custom compact">
                    <div class="header-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="header-text">
                        <h5>إجراءات</h5>
                    </div>
                </div>
                <div class="card-body-custom text-center">
                    <div class="avatar-upload-section">
                        <div class="avatar-preview">
                            <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="avatar-image">
                            <div class="avatar-overlay">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <h5 class="student-name-preview">{{ $student->user->name }}</h5>
                        <p class="student-number-preview">{{ $student->student_number }}</p>
                    </div>

                    <div class="upload-section">
                        <label class="form-label-custom text-center w-100">
                            <i class="fas fa-cloud-upload-alt me-2"></i>تغيير الصورة
                        </label>
                        <input type="file" name="image" class="form-control-custom @error('image') is-invalid @enderror" accept="image/*">
                        <small class="text-muted d-block mt-2">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                        @error('image')
                            <div class="error-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="divider">

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary-custom btn-save">
                            <i class="fas fa-save me-2"></i>حفظ التغييرات
                        </button>
                        <a href="{{ route('supervisor.students.show', $student->id) }}" class="btn btn-outline-custom">
                            <i class="fas fa-arrow-right me-2"></i>العودة للملف
                        </a>
                        <a href="{{ route('supervisor.students.index') }}" class="btn btn-link-custom">
                            <i class="fas fa-list me-2"></i>قائمة الطلاب
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.quranic-verse-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.05));
    border-radius: 13px;
    margin-bottom: 16px;
}

.quranic-verse-header .verse-decoration {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a030, transparent);
    max-width: 100px;
}

.quranic-verse-header .quranic-verse {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: #182B48;
    margin: 0;
    text-shadow: 0 1px 2px rgba(212,160,48,0.2);
}

.main-form-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
}

.header-icon.secondary {
    background: linear-gradient(135deg, #B4802C, #d4a030);
}

.header-text {
    flex: 1;
}

.header-text h5 {
    color: #182B48;
    font-weight: 700;
    font-size: 18px;
    margin: 0 0 4px 0;
}

.header-text p {
    color: #7f8c8d;
    font-size: 12.5px;
    margin: 0;
}

.card-body-custom {
    padding: 16px;
}

.form-label-custom {
    display: flex;
    align-items: center;
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-label-custom i {
    color: #d4a030;
    font-size: 13px;
}

.form-label-custom .required {
    color: #c0392b;
    margin-right: 4px;
}

.form-control-custom, .form-select-custom {
    border: 2px solid #e9ecef;
    border-radius: 9px;
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
    background: #fff;
    color: #2c3e50;
}

.form-control-custom:focus, .form-select-custom:focus {
    border-color: #B4802C;
    box-shadow: 0 0 0 0.2rem rgba(180,128,44,0.15);
    outline: none;
}

.form-control-custom.is-invalid, .form-select-custom.is-invalid {
    border-color: #c0392b;
}

.form-control-custom::placeholder {
    color: #bdc3c7;
}

.textarea-custom {
    min-height: 100px;
    resize: vertical;
}

.error-feedback {
    color: #c0392b;
    font-size: 12px;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.error-feedback::before {
    content: '\f071';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
}

.sidebar-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
}

.avatar-upload-section {
    padding: 16px 0;
}

.avatar-preview {
    position: relative;
    width: 96px;
    height: 96px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: 3px solid #d4a030;
    padding: 3px;
}

.avatar-overlay {
    position: absolute;
    inset: 0;
    background: rgba(24,43,72,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    opacity: 0;
    transition: all 0.3s ease;
    cursor: pointer;
    border-radius: 50%;
}

.avatar-preview:hover .avatar-overlay {
    opacity: 1;
}

.student-name-preview {
    color: #182B48;
    font-weight: 700;
    font-size: 15px;
    margin: 0 0 4px 0;
}

.student-number-preview {
    color: #7f8c8d;
    font-size: 12.5px;
    margin: 0;
    font-family: monospace;
    background: #f0f4f8;
    padding: 3px 10px;
    border-radius: 7px;
    display: inline-block;
}

.upload-section {
    padding: 0;
    margin-top: 10px;
}

.divider {
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a030, transparent);
    margin: 16px 0;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary-custom:hover {
    background: linear-gradient(135deg, #2A4570, #182B48);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(24,43,72,0.3);
    color: #fff;
}

.btn-primary-custom i {
    color: #d4a030;
}

.btn-outline-custom {
    background: transparent;
    color: #182B48;
    border: 2px solid #182B48;
    border-radius: 9px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-outline-custom:hover {
    background: #182B48;
    color: #fff;
}

.btn-link-custom {
    background: transparent;
    color: #7f8c8d;
    border: none;
    padding: 8px;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-link-custom:hover {
    color: #182B48;
    text-decoration: underline;
}

@media (max-width: 992px) {
    .sidebar-card {
        position: static !important;
        margin-top: 16px;
    }
}
</style>

@endsection