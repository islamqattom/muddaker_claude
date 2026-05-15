@extends('layouts.app')
@section('title', 'الملف الشخصي')
@section('page-title', 'الملف الشخصي')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الملف الشخصي</li>
@endsection
@section('content')

<div class="portal-container">
    <div class="profile-layout">
        <div class="profile-card">
            <div class="profile-card-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h4>{{ $parent->user->name }}</h4>
                <p>{{ $parent->user->email }}</p>
            </div>
            <div class="profile-card-body">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <label>رقم الهاتف</label>
                        <span>{{ $parent->phone ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="info-content">
                        <label>رقم الهوية</label>
                        <span>{{ $parent->national_id ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <label>العنوان</label>
                        <span>{{ $parent->address ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="edit-card">
            <div class="edit-card-header">
                <div class="edit-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h5>تعديل البيانات الشخصية</h5>
            </div>
            <div class="edit-card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('parent.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <img src="{{ $parent->user->avatar_url }}" alt="Avatar">
                        </div>
                        <div class="upload-controls">
                            <label class="form-label">صورة الملف الشخصي</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" class="form-control" value="{{ $parent->user->name }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $parent->user->email }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $parent->phone }}" placeholder="05xxxxxxxx">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>حفظ التغييرات</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="linked-students-card mt-4">
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon">
                    <i class="fas fa-users"></i>
                </div>
                <span>الأبناء المرتبطون ({{ $parent->students->count() }})</span>
            </div>
        </div>
        <div class="card-body-custom">
            @if($parent->students->isEmpty())
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>لا يوجد أبناء مرتبطون بحسابك</h5>
                </div>
            @else
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>المستوى</th>
                                <th>المشرف</th>
                                <th>الجزء الحالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parent->students as $student)
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="user-name">{{ $student->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($student->level)
                                        <span class="level-badge">{{ $student->level->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->supervisor)
                                        <span class="supervisor-text">{{ $student->supervisor->name }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->current_part)
                                        <span class="part-badge">جزء {{ $student->current_part }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="password-card mt-4">
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <span>تغيير كلمة المرور</span>
            </div>
        </div>
        <div class="card-body-custom">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('parent.profile.password') }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required minlength="8">
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-key"></i>
                    <span>تغيير كلمة المرور</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.portal-container {
    max-width: 1400px;
    margin: 0 auto;
}

.profile-layout {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 25px;
}

@media (max-width: 992px) { .profile-layout { grid-template-columns: 1fr; } }

.profile-card, .edit-card, .linked-students-card, .password-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.profile-card-header {
    background: linear-gradient(135deg, #182B48 0%, #1a3356 100%);
    padding: 45px 30px;
    text-align: center;
    position: relative;
}

.profile-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.profile-avatar {
    width: 110px;
    height: 110px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 22px;
    font-size: 44px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(212,160,48,0.4);
}

.profile-card-header h4 {
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 6px;
}

.profile-card-header p {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    margin: 0;
}

.profile-card-body { padding: 28px; }

.info-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #f0f4f8;
}

.info-item:last-child { border-bottom: none; }

.info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.info-content { display: flex; flex-direction: column; }
.info-content label { font-size: 12px; color: #95a5a6; margin-bottom: 3px; }
.info-content span { font-weight: 600; color: #182B48; font-size: 15px; }

.edit-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.edit-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.edit-card-header h5 {
    color: #182B48;
    font-size: 19px;
    font-weight: 700;
    margin: 0;
}

.edit-card-body { padding: 28px; }

.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 22px;
}

.alert-success {
    background: rgba(39,174,96,0.1);
    color: #16835a;
}

.alert-danger {
    background: rgba(192,57,43,0.1);
    color: #a82e1c;
}

.avatar-upload {
    display: flex;
    align-items: center;
    gap: 22px;
    margin-bottom: 28px;
}

.avatar-preview {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #d4a030;
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-controls { flex: 1; }
.upload-controls .form-label { font-weight: 600; color: #182B48; margin-bottom: 10px; display: block; }
.upload-controls .text-muted { display: block; margin-top: 8px; color: #95a5a6; font-size: 12px; }

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.user-info-cell { display: flex; align-items: center; gap: 14px; }
.user-name { color: #182B48; font-weight: 600; }

.level-badge {
    display: inline-block;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    color: #182B48;
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.supervisor-text { color: #7f8c8d; font-size: 13px; }

.part-badge {
    display: inline-block;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
}

.table-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.card-body-custom { padding: 0; }

.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-illustration {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 32px;
    color: #d4a030;
}

.empty-state h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}

.table-responsive { overflow-x: auto; }

.modern-table { width: 100%; border-collapse: collapse; }
.modern-table thead { background: #f8f9fa; }

.modern-table th {
    padding: 18px 22px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 18px 22px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.card-body-custom .alert {
    margin: 20px 28px;
}

.card-body-custom form {
    padding: 28px;
}

.card-body-custom .btn {
    padding: 12px 24px;
}
</style>
@endsection