@extends('guest.layout')

@section('title', 'يوم الهمة - نتيجة التقييم')

@section('content')
<div class="page-card">
    <div class="text-center mb-4">
        <i class="fas fa-check-circle" style="font-size: 60px; color: var(--success);"></i>
        <h2 class="mt-3">تم حفظ التقييم بنجاح</h2>
    </div>

    <div class="alert alert-gold">
        <div><i class="fas fa-user-tie me-1"></i> المشرف: <strong>{{ $session->supervisor_name }}</strong></div>
        <div><i class="fas fa-user-graduate me-1"></i> الطالب: <strong>{{ $session->student_name }}</strong></div>
        <div><i class="fas fa-calendar me-1"></i> التاريخ: <strong>{{ $session->session_date->format('Y-m-d') }}</strong></div>
    </div>

    <h3 style="color: var(--navy); font-size: 16px; font-weight: 700; margin-top: 18px;">
        <i class="fas fa-book-quran me-1"></i> الأجزاء ({{ count($session->parts) }})
    </h3>
    <div class="mb-3">
        @foreach($session->parts as $p)
            <span class="badge" style="background: var(--gold-soft); color: var(--navy); padding: 6px 12px; border-radius: 8px; margin: 2px;">الجزء {{ $p }}</span>
        @endforeach
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="lbl">إجمالي الأخطاء</div>
            <div class="val">{{ $session->total_errors }}</div>
        </div>
        <div class="summary-item">
            <div class="lbl">إجمالي الترددات</div>
            <div class="val">{{ $session->total_hesitations }}</div>
        </div>
    </div>

    @if($session->notes)
    <div class="mt-3">
        <strong>ملاحظات:</strong>
        <p class="text-muted">{{ $session->notes }}</p>
    </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('guest.yawm-al-himma.index') }}" class="btn-gold">
            <i class="fas fa-plus me-1"></i> تسجيل تقييم جديد
        </a>
    </div>
</div>
@endsection
