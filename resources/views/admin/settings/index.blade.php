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
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    إعدادات النظام
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <h6 class="border-bottom pb-2 mb-3 text-primary">
                        <i class="fas fa-school me-1"></i>معلومات المركز
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">اسم المركز</label>
                        <input type="text" name="center_name" class="form-control" value="{{ config('app.center_name', 'مركز حفظ القرآن الكريم') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="center_email" class="form-control" value="{{ config('app.center_email', 'info@quran-center.com') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="center_phone" class="form-control" value="{{ config('app.center_phone', '') }}">
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                        <i class="fas fa-clock me-1"></i>أوقات العمل
                    </h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">وقت بدء العمل</label>
                                <input type="time" name="work_start_time" class="form-control" value="{{ config('app.work_start_time', '08:00') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">وقت انتهاء العمل</label>
                                <input type="time" name="work_end_time" class="form-control" value="{{ config('app.work_end_time', '20:00') }}">
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                        <i class="fas fa-calendar me-1"></i>أيام العمل
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">أيام العمل (اختر الأيام)</label>
                        <div class="row">
                            @php
                                $workDays = explode(',', config('app.work_days', 'saturday,sunday,monday,tuesday,wednesday,thursday'));
                            @endphp
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_days[]" value="saturday" {{ in_array('saturday', $workDays) ? 'checked' : '' }}>
                                    <label class="form-check-label">السبت</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_days[]" value="sunday" {{ in_array('sunday', $workDays) ? 'checked' : '' }}>
                                    <label class="form-check-label">الأحد</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_days[]" value="monday" {{ in_array('monday', $workDays) ? 'checked' : '' }}>
                                    <label class="form-check-label">الاثنين</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_days[]" value="tuesday" {{ in_array('tuesday', $workDays) ? 'checked' : '' }}>
                                    <label class="form-check-label">الثلاثاء</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_days[]" value="wednesday" {{ in_array('wednesday', $workDays) ? 'checked' : '' }}>
                                    <label class="form-check-label">الأربعاء</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_days[]" value="thursday" {{ in_array('thursday', $workDays) ? 'checked' : '' }}>
                                    <label class="form-check-label">الخميس</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_days[]" value="friday" {{ in_array('friday', $workDays) ? 'checked' : '' }}>
                                    <label class="form-check-label">الجمعة</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                        <i class="fas fa-bell me-1"></i>الإشعارات
                    </h6>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enable_notifications" value="1" {{ config('app.enable_notifications', true) ? 'checked' : '' }}>
                            <label class="form-check-label">تفعيل الإشعارات</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
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
</div>
@endsection
