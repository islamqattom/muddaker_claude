@extends('layouts.app')

@section('title', $lesson->title)
@section('page-title', 'تفاصيل الدرس')

@php
    $role = auth()->user()->role;
    $roleLabel = $role === 'admin' ? 'مدير' : 'معلم';
@endphp

@section('role-label', $roleLabel)

@section('sidebar-menu')
    @include($role . '.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.index') }}">المواد</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
    <li class="breadcrumb-item active">الدرس</li>
@endsection

@push('styles')
<style>
.lesson-show-page { font-family: 'Tajawal', sans-serif; }
.subject-color-badge { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.btn { border-radius: 12px; padding: 10px 16px; font-weight: 700; font-family: 'Tajawal', sans-serif; border: none; }
.detail-card { background: #fff; border-radius: 13px; padding: 16px; box-shadow: 0 4px 20px rgba(24,43,72,0.07); border: 1px solid rgba(24,43,72,0.05); margin-bottom: 16px; }
.detail-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 16px; }
.detail-block { padding: 16px; background: rgba(24,43,72,0.03); border-radius: 12px; border-right: 4px solid {{ $subject->color }}; }
.detail-label { color: #5a6c7d; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
.detail-label i { color: {{ $subject->color }}; }
.detail-value { color: #182B48; font-size: 16px; font-weight: 700; }
.content-section { padding: 16px; background: rgba(24,43,72,0.03); border-radius: 12px; margin-bottom: 16px; }
.content-section h6 { color: #182B48; font-weight: 800; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
.content-section h6 i { color: {{ $subject->color }}; }
.content-section p { color: #5a6c7d; line-height: 1.8; margin: 0; white-space: pre-line; }
.status-badge.pending { background: rgba(243,156,18,0.15); color: #f39c12; }
.status-badge.completed { background: rgba(39,174,96,0.15); color: #27ae60; }
.status-badge.cancelled { background: rgba(127,140,141,0.15); color: #7f8c8d; }
.status-badge.overdue { background: rgba(192,57,43,0.15); color: #c0392b; }
@media (max-width: 768px) {
.detail-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="lesson-show-page">
    <x-page-hero icon="fas fa-book-open">
        <h1 class="page-banner-title">{{ $lesson->title }}</h1>
        <p class="page-banner-sub">المادة: <strong>{{ $subject->name }}</strong> • {{ $lesson->day_name }} - {{ $lesson->lesson_date ? $lesson->lesson_date->format('Y/m/d') : '-' }}</p>
        <x-slot:actions>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a href="{{ route($role . '.lessons.edit', [$subject->id, $lesson->id]) }}" class="btn" style="background:#8e44ad;color:#fff">
                    <i class="fas fa-edit ms-1"></i> تعديل
                </a>
                <a href="{{ route($role . '.subjects.show', $subject->id) }}" class="btn btn-secondary" style="background:#f0f4f8;color:#182B48">
                    <i class="fas fa-arrow-right ms-1"></i> رجوع
                </a>
            </div>
        </x-slot:actions>
    </x-page-hero>

    <div class="detail-card">
        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
            <h5 style="color:#182B48;font-weight:800;margin:0">
                <i class="fas fa-info-circle" style="color:{{ $subject->color }}"></i> معلومات الدرس
            </h5>
            @if($lesson->is_overdue)
                <span class="status-badge overdue"><i class="fas fa-exclamation"></i> متأخر</span>
            @else
                <span class="status-badge {{ $lesson->status }}">{{ $lesson->status_label }}</span>
            @endif
        </div>

        <div class="detail-row">
            <div class="detail-block">
                <div class="detail-label"><i class="fas fa-calendar"></i> اليوم</div>
                <div class="detail-value">{{ $lesson->day_name }}</div>
            </div>
            <div class="detail-block">
                <div class="detail-label"><i class="fas fa-calendar-day"></i> التاريخ</div>
                <div class="detail-value">{{ $lesson->lesson_date ? $lesson->lesson_date->format('Y/m/d') : '-' }}</div>
            </div>
            <div class="detail-block">
                <div class="detail-label"><i class="fas fa-clock"></i> الوقت</div>
                <div class="detail-value">{{ $lesson->time_range }}</div>
            </div>
            <div class="detail-block">
                <div class="detail-label"><i class="fas fa-user-tie"></i> أضافه</div>
                <div class="detail-value">{{ $lesson->creator->name ?? '-' }}</div>
            </div>
            @if($lesson->completed_at)
                <div class="detail-block">
                    <div class="detail-label"><i class="fas fa-check-circle"></i> تم في</div>
                    <div class="detail-value">{{ $lesson->completed_at->format('Y/m/d H:i') }}</div>
                </div>
            @endif
        </div>

        @if($lesson->content)
            <div class="content-section">
                <h6><i class="fas fa-book-reader"></i> محتوى الدرس</h6>
                <p>{{ $lesson->content }}</p>
            </div>
        @endif

        @if($lesson->notes)
            <div class="content-section" style="background:rgba(212,160,48,0.05)">
                <h6><i class="fas fa-sticky-note"></i> ملاحظات</h6>
                <p>{{ $lesson->notes }}</p>
            </div>
        @endif

        <div class="d-flex gap-2 mt-3 flex-wrap">
            @if($lesson->status !== 'completed')
                <button type="button" class="btn" style="background:#27ae60;color:#fff" onclick="setStatus('completed')">
                    <i class="fas fa-check ms-1"></i> تحديد كمنجز
                </button>
            @else
                <button type="button" class="btn" style="background:#f39c12;color:#fff" onclick="setStatus('pending')">
                    <i class="fas fa-undo ms-1"></i> إعادة للحالة السابقة
                </button>
            @endif
            @if($lesson->status !== 'cancelled')
                <button type="button" class="btn" style="background:#7f8c8d;color:#fff" onclick="setStatus('cancelled')">
                    <i class="fas fa-ban ms-1"></i> إلغاء الدرس
                </button>
            @endif
            <button type="button" class="btn" style="background:#c0392b;color:#fff" onclick="deleteLesson()">
                <i class="fas fa-trash ms-1"></i> حذف
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const rolePrefix = @json($role);
function setStatus(status) {
    const url = `/${rolePrefix}/subjects/{{ $subject->id }}/lessons/{{ $lesson->id }}/status`;
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 800);
        }
    });
}

function deleteLesson() {
    Swal.fire({
        title: 'تأكيد الحذف',
        html: `هل تريد حذف الدرس <strong>{{ addslashes($lesson->title) }}</strong>؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#c0392b',
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/${rolePrefix}/subjects/{{ $subject->id }}/lessons/{{ $lesson->id }}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
