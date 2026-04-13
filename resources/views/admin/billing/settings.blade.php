@extends('layouts.app')
@section('title', 'إعدادات الفوترة')
@section('page-title', 'إعدادات الفوترة')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.billing.index') }}">الرسوم</a></li>
    <li class="breadcrumb-item active">الإعدادات</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>إعدادات الفوترة</h6>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.billing.settings.update') }}">
            @csrf
            @method('PUT')

            <h6 class="border-bottom pb-2 mb-3 text-primary">
                <i class="fas fa-money-bill me-1"></i>الرسوم الشهرية
            </h6>

            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>ملاحظة:</strong> يتم الآن تحديد الرسوم الشهرية عبر <a href="{{ route('admin.billing.feeCategories') }}" class="alert-link">فئات الرسوم</a>.
                قم بإضافة فئات رسوم وإسناد الطلاب إليها.
            </div>

            <h6 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                <i class="fas fa-bell me-1"></i>التذكيرات
            </h6>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox"
                           name="billing_reminder_enabled" value="1"
                           {{ $settings['billing_reminder_enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label">تفعيل تذكيرات الدفع</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">عدد الأيام بين كل تذكير</label>
                <input type="number" name="billing_reminder_frequency_days"
                       class="form-control"
                       value="{{ $settings['billing_reminder_frequency_days'] }}"
                       min="1" max="365">
                <small class="text-muted">سيتم إرسال تذكير كل هذا العدد من الأيام للرسوم غير المدفوعة</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>حفظ الإعدادات
                </button>
                <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-2"></i>رجوع
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
