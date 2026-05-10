@extends('layouts.app')

@section('title', $rule ? 'تعديل القاعدة' : 'إضافة قاعدة جديدة')
@section('page-title', $rule ? 'تعديل القاعدة' : 'إضافة قاعدة جديدة')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.warning-rules.index') }}">قواعد الإنذار</a></li>
    <li class="breadcrumb-item active">{{ $rule ? 'تعديل القاعدة' : 'إضافة قاعدة' }}</li>
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
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
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
    margin-bottom: 25px;
}
.form-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.form-header i { color: var(--gold); font-size: 20px; }
.form-body { padding: 25px 28px; }

.form-group { margin-bottom: 20px; }
.form-label {
    font-weight: 600; color: #5a6c7d;
    margin-bottom: 10px; display: block;
    font-size: 14px;
}
.form-label i { color: var(--gold); margin-left: 6px; }
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
.form-text { font-size: 12px; color: #95a5a6; margin-top: 5px; }

.form-switch { display: flex; align-items: center; gap: 12px; }
.form-switch-input {
    width: 50px; height: 26px;
    cursor: pointer;
}
.form-switch-label {
    font-weight: 600; color: var(--primary);
    cursor: pointer;
}
.form-switch-input:checked { background: linear-gradient(135deg, var(--success) 0%, #219a52 100%); }

.section-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(24,43,72,0.1), transparent);
    margin: 25px 0;
}

.action-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
    text-decoration: none;
}
.action-btn.submit {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.action-btn.submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); }
.action-btn.cancel {
    background: transparent;
    color: #7f8c8d; border: 2px solid #e9ecef;
}
.action-btn.cancel:hover { border-color: var(--danger); color: var(--danger); }
.action-btn i { color: var(--gold); }

@media (max-width: 992px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-gavel"></i>
        </div>
        <div class="header-text">
            <h4>{{ $rule ? 'تعديل القاعدة' : 'إضافة قاعدة جديدة' }}</h4>
            <p>ضبط قواعد إصدار الإنذارات automatique</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <form method="POST" action="{{ $rule ? route('admin.warning-rules.update', $rule->id) : route('admin.warning-rules.store') }}">
            @csrf
            @if($rule)
                @method('PUT')
            @endif

            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-info-circle"></i>
                    <span>معلومات القاعدة</span>
                </div>
                <div class="form-body">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tag"></i>اسم القاعدة
                        </label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name ?? '') }}" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-exclamation-circle"></i>نوع الإنذار
                                </label>
                                <select name="type" class="form-select" required>
                                    <option value="attendance" @selected(old('type', $rule->type ?? '') === 'attendance')>
                                        <i class="fas fa-calendar-times"></i> حضور
                                    </option>
                                    <option value="recitation" @selected(old('type', $rule->type ?? '') === 'recitation')>
                                        <i class="fas fa-microphone"></i> تقييم حفظ
                                    </option>
                                    <option value="general" @selected(old('type', $rule->type ?? '') === 'general')>
                                        <i class="fas fa-exclamation-circle"></i> عام
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-filter"></i>نوع الشرط
                                </label>
                                <select name="condition_type" class="form-select" required>
                                    <option value="absent_days" @selected(old('condition_type', $rule->condition_type ?? '') === 'absent_days')>
                                        أيام الغياب
                                    </option>
                                    <option value="late_days" @selected(old('condition_type', $rule->condition_type ?? '') === 'late_days')>
                                        أيام التأخير
                                    </option>
                                    <option value="failed_sessions" @selected(old('condition_type', $rule->condition_type ?? '') === 'failed_sessions')>
                                        حصص الرسوب
                                    </option>
                                    <option value="not_recitate_count" @selected(old('condition_type', $rule->condition_type ?? '') === 'not_recitate_count')>
                                        حصص لم يسمع
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-bell"></i>الحد (الحد الأدنى للمحفّز)
                                </label>
                                <input type="number" name="threshold" class="form-control" min="1" value="{{ old('threshold', $rule->threshold ?? 3) }}" required>
                                <div class="form-text">الحد الأدنى للشرط لإصدار الإنذار</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt"></i>الفترة (بالأيام)
                                </label>
                                <input type="number" name="period_days" class="form-control" min="1" value="{{ old('period_days', $rule->period_days ?? 30) }}">
                                <div class="form-text">عدد الأيام للرجوع إليها عند التحقق</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-layer-group"></i>الصفوف (اختياري)
                        </label>
                        <select name="level_ids[]" class="form-select" multiple>
                            @foreach($levels ?? [] as $level)
                                <option value="{{ $level->id }}" @selected(in_array($level->id, old('level_ids', $rule->level_ids ?? [])))>
                                    {{ $level->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">اتركها فارغة لتطبيق القاعدة على جميع الصفوف</div>
                    </div>

                    <div class="form-switch">
                        <input type="checkbox" name="is_active" class="form-switch-input" id="is_active" value="1" @checked(old('is_active', $rule->is_active ?? true))>
                        <label class="form-switch-label" for="is_active">
                            <i class="fas fa-power-off" style="color: var(--success);"></i>
                            مفعّل
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <button type="submit" class="action-btn submit">
                    <i class="fas fa-save"></i>حفظ
                </button>
                <a href="{{ route('admin.warning-rules.index') }}" class="action-btn cancel">
                    <i class="fas fa-arrow-right"></i>إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection