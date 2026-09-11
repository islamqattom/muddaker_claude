@extends('guest.layout')

@section('title', 'يوم الهمة - تعديل جلسة #' . $session->id)

@section('content')
<div class="page-card">
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <div style="width: 52px; height: 52px; border-radius: 12px; background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px;">
            <i class="fas fa-pen"></i>
        </div>
        <div class="flex-grow-1">
            <h2 style="margin: 0; color: var(--navy); font-size: 20px; font-weight: 700;">تعديل الجلسة #{{ $session->id }}</h2>
            <p style="margin: 2px 0 0; color: #6b7a90; font-size: 13px;">يمكنك تعديل بيانات الجلسة. مجموع الأخطاء والترددات والصفحات الحالية محفوظة كما هي.</p>
        </div>
        <a href="{{ route('guest.yawm-al-himma.sessions.index', ['supervisor_name' => $session->supervisor_name]) }}" class="btn-outline-navy">
            <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="background: #fde8e6; border: 1px solid var(--danger); color: #922b1f; border-radius: var(--radius-sm); padding: 12px 14px;">
            <strong><i class="fas fa-exclamation-circle me-1"></i> يرجى تصحيح الأخطاء التالية:</strong>
            <ul class="mb-0 mt-1" style="padding-right: 18px;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('guest.yawm-al-himma.sessions.update', ['session' => $session->id]) }}">
        @csrf
        @method('PATCH')

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="label-text">اسم المشرف <span style="color: var(--danger);">*</span></label>
                <input type="text" name="supervisor_name" value="{{ old('supervisor_name', $session->supervisor_name) }}" class="form-control" maxlength="255" required>
                @error('supervisor_name') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="label-text">اسم الطالب <span style="color: var(--danger);">*</span></label>
                <input type="text" name="student_name" value="{{ old('student_name', $session->student_name) }}" class="form-control" maxlength="255" required>
                @error('student_name') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="label-text">تاريخ الجلسة <span style="color: var(--danger);">*</span></label>
                <input type="date" name="session_date" value="{{ old('session_date', $session->session_date->format('Y-m-d')) }}" class="form-control" required>
                @error('session_date') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="label-text">الأجزاء <span style="color: var(--danger);">*</span></label>
            <div class="row g-2 mt-1">
                @foreach($parts as $part)
                    @php
                        $checked = in_array($part->part_number, old('parts', $session->parts ?? []), true);
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <input type="checkbox" name="parts[]" value="{{ $part->part_number }}" id="part-{{ $part->id }}" class="part-checkbox" {{ $checked ? 'checked' : '' }}>
                        <label for="part-{{ $part->id }}" class="part-card">
                            <div class="num">{{ $part->part_number }}</div>
                            <div class="lbl">{{ $part->name }}</div>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('parts') <div class="text-danger mt-2" style="font-size: 13px;">{{ $message }}</div> @enderror
            @error('parts.*') <div class="text-danger mt-2" style="font-size: 13px;">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="label-text">ملاحظات</label>
            <textarea name="notes" class="form-control" rows="4" maxlength="2000" placeholder="ملاحظات اختيارية">{{ old('notes', $session->notes) }}</textarea>
            @error('notes') <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div> @enderror
        </div>

        <div class="alert alert-gold" style="margin-bottom: 18px;">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div><i class="fas fa-list me-1"></i> عدد الصفحات المسجلة: <strong>{{ count($session->pages_data ?? []) }}</strong></div>
                <div><i class="fas fa-times-circle me-1" style="color: var(--danger);"></i> الأخطاء الحالية: <strong>{{ $session->total_errors }}</strong></div>
                <div><i class="fas fa-question-circle me-1" style="color: #b36b00;"></i> الترددات الحالية: <strong>{{ $session->total_hesitations }}</strong></div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end flex-wrap">
            <a href="{{ route('guest.yawm-al-himma.sessions.index', ['supervisor_name' => $session->supervisor_name]) }}" class="btn-outline-navy">
                إلغاء
            </a>
            <button type="submit" class="btn-gold">
                <i class="fas fa-save me-1"></i> حفظ التعديلات
            </button>
        </div>
    </form>
</div>
@endsection
