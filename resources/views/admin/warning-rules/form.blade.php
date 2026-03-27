@extends('layouts.app')

@section('title', $rule ? 'تعديل قاعدة' : 'إضافة قاعدة جديدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $rule ? 'تعديل قاعدة' : 'إضافة قاعدة جديدة' }}</h3>
                </div>
                <form method="POST"
                      action="{{ $rule ? route('admin.warning-rules.update', $rule->id) : route('admin.warning-rules.store') }}">
                    @csrf
                    @if($rule)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label>اسم القاعدة</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $rule->name ?? '') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع الإنذار</label>
                                    <select name="type" class="form-control" required>
                                        <option value="attendance" @selected(old('type', $rule->type ?? '') === 'attendance')>
                                            حضور
                                        </option>
                                        <option value="recitation" @selected(old('type', $rule->type ?? '') === 'recitation')>
                                            تقييم حفظ
                                        </option>
                                        <option value="general" @selected(old('type', $rule->type ?? '') === 'general')>
                                            عام
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع الشرط</label>
                                    <select name="condition_type" class="form-control" required>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحد (الحد الأدنى للمحفّز)</label>
                                    <input type="number" name="threshold" class="form-control" min="1"
                                           value="{{ old('threshold', $rule->threshold ?? 3) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الفترة (بالأيام)</label>
                                    <input type="number" name="period_days" class="form-control" min="1"
                                           value="{{ old('period_days', $rule->period_days ?? 30) }}">
                                    <small class="form-text text-muted">عدد الأيام للرجوع إليها</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>الصفوف (اختياري)</label>
                            <select name="level_ids[]" class="form-control" multiple>
                                @foreach($levels ?? [] as $level)
                                    <option value="{{ $level->id }}"
                                        @selected(in_array($level->id, old('level_ids', $rule->level_ids ?? [])))>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">اتركها فارغة لتطبيق القاعدة على جميع الصفوف</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" class="custom-control-input"
                                       id="is_active" value="1"
                                       @checked(old('is_active', $rule->is_active ?? true))>
                                <label class="custom-control-label" for="is_active">مفعّل</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                        <a href="{{ route('admin.warning-rules.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection