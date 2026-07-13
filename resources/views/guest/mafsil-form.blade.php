@extends('guest.layout')

@section('title', 'تسميع - اختبار الأجزاء - عام')

@section('hero')
<div class="page-hero">
    <div class="hero-title-row">
        <div class="hero-logo-wrap">
            <img src="{{ asset('images/logo-white.png') }}" alt="مدكر" class="hero-logo">
        </div>
        <div class="hero-title-text">
            <h1><i class="fas fa-book-quran me-2"></i>اختبار الأجزاء</h1>
            <p>صفحة اختبار أجزاء القرآن - للأولياء والمشرفين</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-hero {
        position: relative;
    }
    .page-hero .hero-title-row {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 22px;
        flex-wrap: wrap;
    }
    .page-hero .hero-logo-wrap {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    }
    .page-hero .hero-logo {
        height: 110px;
        width: auto;
        object-fit: contain;
        filter: drop-shadow(0 4px 14px rgba(0, 0, 0, 0.3));
    }
    .page-hero .hero-title-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        width: 100%;
        z-index: 1;
        pointer-events: none;
    }
    .page-hero .hero-title-text h1 {
        margin: 0 0 6px 0;
    }
    .page-hero .hero-title-text p {
        margin: 0;
    }

    @media (max-width: 640px) {
        .page-hero {
            padding: 22px 16px;
        }
        .page-hero .hero-logo {
            height: 70px;
        }
        .page-hero .hero-title-text h1 {
            font-size: 20px;
        }
        .page-hero .hero-title-text p {
            font-size: 12px;
        }
        .page-hero .hero-title-text {
            padding: 0 90px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-card" style="max-width: 720px; margin: 24px auto;">
    <h2 style="text-align: center; margin-bottom: 8px;">
        <i class="fas fa-quran me-2" style="color: var(--gold);"></i>اختبار الأجزاء - للزوار
    </h2>
    <p style="text-align: center; color: #6b7a90; margin-bottom: 24px;">
        صفحة عامة - لا حاجة لتسجيل الدخول
    </p>

    @if($errors->any())
    <div class="alert alert-danger" style="border-radius: var(--radius-sm);">
        <ul class="mb-0" style="padding-inline-start: 20px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('public.recitation.mafsil') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-user-graduate me-1"></i>اسم الطالب</label>
                    <input type="text" name="student_name" class="form-control" required maxlength="255"
                           placeholder="أدخل اسم الطالب" value="{{ old('student_name') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-mosque me-1"></i>اسم المحضن</label>
                    <input type="text" name="mahd_name" class="form-control" maxlength="255"
                           placeholder="أدخل اسم المحضن" value="{{ old('mahd_name') }}">
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-layer-group me-1"></i>عدد الأجزاء</label>
                    <input type="number" name="mafsil_type" id="mafsilType" class="form-control"
                           required min="1" max="30" step="1"
                           placeholder="من 1 إلى 30"
                           value="{{ old('mafsil_type') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-calendar me-1"></i>تاريخ الجلسة</label>
                    <input type="date" name="session_date" class="form-control"
                           value="{{ old('session_date', $today) }}">
                </div>
            </div>
        </div>

        <hr style="border-color: var(--gold-soft); margin: 18px 0;">

        <h5 style="color: var(--navy); font-size: 16px; font-weight: 700; margin-bottom: 14px;">
            <i class="fas fa-sliders-h me-1" style="color: var(--gold);"></i>إعدادات علامات الاختبار
        </h5>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-times-circle me-1" style="color: var(--danger);"></i>علامة الخطأ</label>
                    <input type="number" name="error_score" id="errorScore" class="form-control"
                           step="0.01" min="0" max="100"
                           placeholder="مثال: 3"
                           value="{{ old('error_score', 3) }}">
                    <small class="text-muted">قيمة الخصم لكل خطأ</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-question-circle me-1" style="color: var(--warning);"></i>علامة التردد</label>
                    <input type="number" name="hesitation_score" id="hesitationScore" class="form-control"
                           step="0.01" min="0" max="100"
                           placeholder="مثال: 0.33"
                           value="{{ old('hesitation_score', 0.33) }}">
                    <small class="text-muted">قيمة الخصم لكل تردد</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-flag-checkered me-1" style="color: var(--danger);"></i>علامة الرسوب (%)</label>
                    <input type="number" name="fail_score" id="failScore" class="form-control"
                           step="0.01" min="0" max="100"
                           placeholder="مثال: 60"
                           value="{{ old('fail_score', 60) }}">
                    <small class="text-muted">الحد الأدنى للنجاح</small>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between gap-2 flex-wrap mt-3">
            <button type="submit" class="btn-gold">
                <i class="fas fa-arrow-left me-1"></i> التالي: اختيار الأجزاء
            </button>
        </div>
    </form>

    <div class="alert alert-gold mt-4">
        <i class="fas fa-info-circle me-1"></i>
        هذه الصفحة مخصصة لتسجيل تقييم اختبار الأجزاء للطلاب دون الحاجة لتسجيل دخول.
        سيتم اختيار الأجزاء وتحديد مواضع التسميع في الخطوات التالية.
    </div>
</div>
@endsection
