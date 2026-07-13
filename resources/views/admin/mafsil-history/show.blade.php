@extends('layouts.app')

@section('title', 'تفاصيل جلسة اختبار الأجزاء')
@section('page-title', 'تفاصيل جلسة اختبار الأجزاء')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.mafsil-history.index') }}">سجل اختبار الأجزاء</a></li>
    <li class="breadcrumb-item active">تفاصيل الجلسة</li>
@endsection

@push('styles')
<style>
    body { background: #f8fafc; font-family: 'Tajawal', sans-serif; }
    .detail-page { max-width: 980px; margin: 0 auto; }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary, #182B48);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        padding: 8px 14px;
        border-radius: 10px;
        background: white;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
    }
    .back-link:hover { background: #f8fafc; color: var(--primary, #182B48); }
    .header-content { display: flex; align-items: center; gap: 18px; }
    .header-icon {
        width: 60px; height: 60px;
        background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; color: #d4a030;
        box-shadow: 0 8px 22px rgba(24,43,72,0.2);
    }
    .header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
    .header-text p { color: #6c7a89; font-size: 13px; margin: 0; }

    .info-card {
        background: #ffffff;
        border: 1px solid #eef0f4;
        border-radius: 16px;
        padding: 20px 22px;
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(15, 28, 51, 0.03);
    }
    .info-card-title {
        font-size: 12px;
        font-weight: 700;
        color: #8a96a8;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-card-title i { color: #d4a030; font-size: 13px; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }
    .info-cell {
        padding: 12px 14px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #f1f3f7;
    }
    .info-cell .lbl { font-size: 11px; color: #94a3b8; font-weight: 600; margin-bottom: 4px; }
    .info-cell .val { font-size: 15px; color: #182B48; font-weight: 700; line-height: 1.3; }

    .score-highlight {
        text-align: center;
        padding: 32px 24px;
        background: linear-gradient(135deg, #fcf6e8 0%, #fef9ed 100%);
        border: 1px solid #f0e2b8;
        border-radius: 18px;
        margin-bottom: 16px;
    }
    .score-circle {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 120px; height: 120px;
        border-radius: 50%;
        background: white;
        box-shadow: 0 4px 18px rgba(212, 160, 48, 0.18);
        margin-bottom: 12px;
    }
    .score-circle::before {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        background: conic-gradient(var(--score-color, #d4a030) calc(var(--score-pct, 100) * 1%), #eef0f4 0);
        z-index: 0;
    }
    .score-circle .score-inner {
        position: relative; z-index: 1;
        background: white;
        width: 106px; height: 106px;
        border-radius: 50%;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
    }
    .score-circle .score-value { font-size: 28px; font-weight: 800; color: #182B48; line-height: 1; }
    .score-circle .score-suffix { font-size: 12px; color: #94a3b8; font-weight: 600; margin-top: 2px; }
    .score-status { font-size: 16px; font-weight: 700; color: var(--score-color, #d4a030); margin: 0; }

    .status-passed { --score-color: #16a34a; --score-pct: var(--final-pct, 100); }
    .status-failed { --score-color: #dc2626; --score-pct: var(--final-pct, 100); }

    .notes-card {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 12px 16px;
        color: #78350f;
        font-size: 14px;
    }
    .notes-card strong { color: #92400e; }
</style>
@endpush

@section('content')
@php
    $isPassed = $session->status === 'passed';
    $finalScore = (float) $session->final_score;
    $studentName = $session->student_name ?? ($session->student->user->name ?? 'غير معروف');
@endphp
<div class="detail-page">

    <div class="page-header">
        <div class="header-content">
            <div class="header-icon"><i class="fas fa-book-quran"></i></div>
            <div class="header-text">
                <h4>تفاصيل جلسة اختبار الأجزاء</h4>
                <p>عرض كامل لبيانات جلسة اختبار الأجزاء</p>
            </div>
        </div>
        <a href="{{ route('admin.mafsil-history.index') }}" class="back-link">
            <i class="fas fa-arrow-right"></i> رجوع للسجل
        </a>
    </div>

    <div class="info-card">
        <div class="info-card-title"><i class="fas fa-user-graduate"></i>معلومات الطالب</div>
        <div class="info-grid">
            <div class="info-cell">
                <div class="lbl">الطالب</div>
                <div class="val">{{ $studentName }}</div>
            </div>
            @if($session->mahd_name)
            <div class="info-cell">
                <div class="lbl">المحضن</div>
                <div class="val">{{ $session->mahd_name }}</div>
            </div>
            @endif
            <div class="info-cell">
                <div class="lbl">التاريخ</div>
                <div class="val">{{ $session->session_date->format('Y-m-d') }}</div>
            </div>
            <div class="info-cell">
                <div class="lbl">عدد الأجزاء</div>
                <div class="val">{{ $session->mafsil_type }} أجزاء</div>
            </div>
        </div>
    </div>

    @if($session->error_score !== null || $session->hesitation_score !== null || $session->fail_score !== null)
    <div class="info-card">
        <div class="info-card-title"><i class="fas fa-sliders-h"></i>إعدادات العلامات</div>
        <div class="info-grid">
            @if($session->error_score !== null)
            <div class="info-cell">
                <div class="lbl">علامة الخطأ</div>
                <div class="val" style="color: #dc2626;">-{{ number_format((float) $session->error_score, 2) }}</div>
            </div>
            @endif
            @if($session->hesitation_score !== null)
            <div class="info-cell">
                <div class="lbl">علامة التردد</div>
                <div class="val" style="color: #b36b00;">-{{ number_format((float) $session->hesitation_score, 2) }}</div>
            </div>
            @endif
            @if($session->fail_score !== null)
            <div class="info-cell">
                <div class="lbl">علامة الرسوب</div>
                <div class="val">{{ number_format((float) $session->fail_score, 2) }}%</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="score-highlight">
        <div class="score-circle {{ $isPassed ? 'status-passed' : 'status-failed' }}" style="--final-pct: {{ min(100, max(0, $finalScore)) }};">
            <div class="score-inner">
                <div class="score-value">{{ number_format($finalScore, 1) }}</div>
                <div class="score-suffix">%</div>
            </div>
        </div>
        <p class="score-status">{{ $isPassed ? 'ناجح' : 'راسب' }}</p>
    </div>

    <div class="info-card">
        <div class="info-card-title"><i class="fas fa-chart-pie"></i>ملخص الأداء</div>
        <div class="info-grid">
            <div class="info-cell">
                <div class="lbl">العلامة النهائية</div>
                <div class="val">{{ number_format($finalScore, 2) }}%</div>
            </div>
            <div class="info-cell">
                <div class="lbl">الحالة</div>
                <div class="val" style="color: {{ $isPassed ? '#16a34a' : '#dc2626' }};">{{ $isPassed ? 'ناجح' : 'راسب' }}</div>
            </div>
            <div class="info-cell">
                <div class="lbl">إجمالي الأخطاء</div>
                <div class="val" style="color: #dc2626;">{{ $session->total_errors }}</div>
            </div>
            <div class="info-cell">
                <div class="lbl">إجمالي الترددات</div>
                <div class="val" style="color: #b36b00;">{{ $session->total_hesitations }}</div>
            </div>
        </div>
    </div>

    @if($session->notes)
    <div class="notes-card">
        <strong>ملاحظات:</strong> {{ $session->notes }}
    </div>
    @endif
</div>
@endsection
