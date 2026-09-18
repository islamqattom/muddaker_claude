@extends('layouts.app')
@section('title', 'إعدادات النظام')
@section('page-title', 'إعدادات النظام')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإعدادات</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="header-text">
            <h4>إعدادات النظام</h4>
            <p>إدارة إعدادات ومعلومات مركز التسميع</p>
        </div>
    </div>
</div>

<div class="settings-layout">
    {{-- Settings Form --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fas fa-sliders-h"></i>
            <span>إعدادات النظام</span>
        </div>
        <div class="settings-card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                {{-- Marker so an empty work-days selection is saved as empty
                     instead of falling back to the default list. --}}
                <input type="hidden" name="work_days_submitted" value="1">
                <input type="hidden" name="recitation_days_submitted" value="1">

                {{-- Center Info Section --}}
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-mosque"></i>
                        <span>معلومات المركز</span>
                    </div>
                    <div class="section-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">اسم المركز</label>
                                <div class="input-icon">
                                    <i class="fas fa-building"></i>
                                    <input type="text" name="center_name" class="form-control" value="{{ config('app.center_name', 'مركز حفظ القرآن الكريم') }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">البريد الإلكتروني</label>
                                <div class="input-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" name="center_email" class="form-control" value="{{ config('app.center_email', 'info@quran-center.com') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">رقم الهاتف</label>
                            <div class="input-icon">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="center_phone" class="form-control" value="{{ config('app.center_phone', '') }}" placeholder="05xxxxxxxx">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Work Hours Section --}}
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-clock"></i>
                        <span>أوقات العمل</span>
                    </div>
                    <div class="section-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">وقت بدء العمل</label>
                                <div class="input-icon">
                                    <i class="fas fa-play"></i>
                                    <input type="time" name="work_start_time" class="form-control" value="{{ config('app.work_start_time', '08:00') }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">وقت انتهاء العمل</label>
                                <div class="input-icon">
                                    <i class="fas fa-stop"></i>
                                    <input type="time" name="work_end_time" class="form-control" value="{{ config('app.work_end_time', '20:00') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Work Days Section --}}
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-calendar-alt"></i>
                        <span>أيام العمل</span>
                    </div>
                    <div class="section-body">
                        <label class="form-label">اختر أيام العمل</label>
                        <div class="days-grid">
                            @php
                                $workDays = explode(',', config('app.work_days', 'saturday,sunday,monday,tuesday,thursday'));
                            @endphp
                            <div class="day-checkbox">
                                <input type="checkbox" name="work_days[]" value="saturday" id="saturday" {{ in_array('saturday', $workDays) ? 'checked' : '' }}>
                                <label for="saturday">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>السبت</span>
                                </label>
                            </div>
                            <div class="day-checkbox">
                                <input type="checkbox" name="work_days[]" value="sunday" id="sunday" {{ in_array('sunday', $workDays) ? 'checked' : '' }}>
                                <label for="sunday">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>الأحد</span>
                                </label>
                            </div>
                            <div class="day-checkbox">
                                <input type="checkbox" name="work_days[]" value="monday" id="monday" {{ in_array('monday', $workDays) ? 'checked' : '' }}>
                                <label for="monday">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>الاثنين</span>
                                </label>
                            </div>
                            <div class="day-checkbox">
                                <input type="checkbox" name="work_days[]" value="tuesday" id="tuesday" {{ in_array('tuesday', $workDays) ? 'checked' : '' }}>
                                <label for="tuesday">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>الثلاثاء</span>
                                </label>
                            </div>
                            <div class="day-checkbox">
                                <input type="checkbox" name="work_days[]" value="wednesday" id="wednesday" {{ in_array('wednesday', $workDays) ? 'checked' : '' }}>
                                <label for="wednesday">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>الأربعاء</span>
                                </label>
                            </div>
                            <div class="day-checkbox">
                                <input type="checkbox" name="work_days[]" value="thursday" id="thursday" {{ in_array('thursday', $workDays) ? 'checked' : '' }}>
                                <label for="thursday">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>الخميس</span>
                                </label>
                            </div>
                            <div class="day-checkbox">
                                <input type="checkbox" name="work_days[]" value="friday" id="friday" {{ in_array('friday', $workDays) ? 'checked' : '' }}>
                                <label for="friday">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>الجمعة</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recitation Days Section --}}
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-microphone-alt"></i>
                        <span>أيام التسميع</span>
                    </div>
                    <div class="section-body">
                        <label class="form-label">اختر الأيام التي يكون فيها تسميع</label>
                        <div class="days-grid">
                            @php
                                $recitationDays = explode(',', config('app.recitation_days', 'saturday,tuesday'));
                                $recitationDayOptions = [
                                    'saturday'  => 'السبت',
                                    'sunday'    => 'الأحد',
                                    'monday'    => 'الاثنين',
                                    'tuesday'   => 'الثلاثاء',
                                    'wednesday' => 'الأربعاء',
                                    'thursday'  => 'الخميس',
                                    'friday'    => 'الجمعة',
                                ];
                            @endphp
                            @foreach($recitationDayOptions as $value => $label)
                                <div class="day-checkbox">
                                    <input type="checkbox" name="recitation_days[]" value="{{ $value }}" id="recitation_{{ $value }}" {{ in_array($value, $recitationDays) ? 'checked' : '' }}>
                                    <label for="recitation_{{ $value }}">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>{{ $label }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-2">في هذه الأيام يُسجَّل الطالب الحاضر الذي لم يسمّع ضمن تقرير "لم يسمع".</small>
                    </div>
                </div>

                {{-- Notifications Section --}}
                <div class="settings-section">
                    <div class="section-header">
                        <i class="fas fa-bell"></i>
                        <span>الإشعارات</span>
                    </div>
                    <div class="section-body">
                        <div class="form-group">
                            <div class="toggle-setting">
                                <input type="checkbox" name="enable_notifications" value="1" id="enable_notifications" {{ config('app.enable_notifications', true) ? 'checked' : '' }}>
                                <label for="enable_notifications">
                                    <span class="toggle-slider"></span>
                                    <span class="toggle-label">تفعيل الإشعارات</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ الإعدادات
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #d4a030;
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.settings-layout { max-width: 800px; margin: 0 auto; }

.settings-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.settings-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
    color: #182B48;
    font-weight: 700;
    font-size: 16px;
}

.settings-card-header i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.settings-card-body { padding: 25px; }

.settings-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #f0f4f8;
}

.settings-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    color: #182B48;
    font-weight: 700;
    font-size: 15px;
}

.section-header i { color: #d4a030; font-size: 18px; }

.section-body { padding-right: 30px; }

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 20px; }

.form-label {
    display: block;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 1.5px solid #dee2e6;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #182B48;
    box-shadow: 0 0 0 0.2rem rgba(24,43,72,0.15);
    outline: none;
}

.input-icon { position: relative; }

.input-icon i {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #d4a030;
    font-size: 16px;
}

.input-icon .form-control { padding-right: 45px; }

.days-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 15px;
}

.day-checkbox input { display: none; }

.day-checkbox label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.day-checkbox label i { font-size: 20px; color: #7f8c8d; transition: all 0.3s ease; }
.day-checkbox label span { font-size: 13px; font-weight: 600; color: #7f8c8d; transition: all 0.3s ease; }

.day-checkbox input:checked + label {
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-color: #182B48;
}

.day-checkbox input:checked + label i { color: #d4a030; }
.day-checkbox input:checked + label span { color: #182B48; }

.day-checkbox label:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.toggle-setting { display: flex; align-items: center; }

.toggle-setting input { display: none; }

.toggle-setting label {
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
}

.toggle-slider {
    width: 55px;
    height: 28px;
    background: #e9ecef;
    border-radius: 14px;
    position: relative;
    transition: all 0.3s ease;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    top: 3px;
    right: 3px;
    width: 22px;
    height: 22px;
    background: #fff;
    border-radius: 50%;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.toggle-setting input:checked + label .toggle-slider {
    background: linear-gradient(135deg, #182B48, #2A4570);
}

.toggle-setting input:checked + label .toggle-slider::before {
    right: 30px;
}

.toggle-label {
    font-size: 14px;
    font-weight: 600;
    color: #182B48;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #f0f4f8;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(24,43,72,0.3);
}

.btn-outline-secondary {
    background: transparent;
    color: #7f8c8d;
    border: 1.5px solid #dee2e6;
}

.btn-outline-secondary:hover {
    background: #f8f9fa;
    color: #182B48;
}
</style>
@endsection