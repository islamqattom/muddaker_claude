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
<div class="page-card" style="max-width: 720px; margin: 16px auto;">
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
                    <input type="text" name="student_name" class="form-control text-center" required maxlength="255"
                           placeholder="أدخل اسم الطالب" value="{{ old('student_name') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-mosque me-1"></i>اسم المحضن</label>
                    <input type="text" name="mahd_name" class="form-control text-center" maxlength="255"
                           placeholder="أدخل اسم المحضن" value="{{ old('mahd_name') }}">
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-layer-group me-1"></i>عدد الأجزاء</label>
                    <input type="number" name="mafsil_type" id="mafsilType" class="form-control text-center"
                           required min="1" max="30" step="1"
                           placeholder="من 1 إلى 30"
                           value="{{ old('mafsil_type') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-map-marker-alt me-1"></i>عدد المواضع لكل جزء</label>
                    <input type="number" name="positions_per_part" id="positionsPerPart" class="form-control text-center"
                           required min="1" max="10" step="1"
                           placeholder="مثال: 3"
                           value="{{ old('positions_per_part', $positionsPerPart ?? 3) }}">
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 me-auto">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-calendar me-1"></i>تاريخ الجلسة</label>
                    <input type="date" name="session_date" class="form-control text-center"
                           value="{{ old('session_date', $today) }}">
                </div>
            </div>
        </div>

        <hr style="border-color: #eef0f4; margin: 16px 0;">

        <h6 style="color: var(--navy); font-size: 14px; font-weight: 700; margin-bottom: 12px;">
            <i class="fas fa-sliders-h me-1" style="color: var(--gold);"></i>إعدادات علامات الاختبار
        </h6>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-times-circle me-1" style="color: var(--danger);"></i>علامة الخطأ</label>
                    <input type="number" name="error_score" id="errorScore" class="form-control text-center"
                           step="0.01" min="0" max="100"
                           placeholder="مثال: 3"
                           value="{{ old('error_score', 3) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-question-circle me-1" style="color: var(--warning);"></i>علامة التردد</label>
                    <input type="number" name="hesitation_score" id="hesitationScore" class="form-control text-center"
                           step="0.01" min="0" max="100"
                           placeholder="مثال: 0.33"
                           value="{{ old('hesitation_score', 0.33) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="label-text"><i class="fas fa-flag-checkered me-1" style="color: var(--danger);"></i>علامة الرسوب (%)</label>
                    <input type="number" name="fail_score" id="failScore" class="form-control text-center"
                           step="0.01" min="0" max="100"
                           placeholder="مثال: 90"
                           value="{{ old('fail_score', 90) }}">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between gap-2 flex-wrap mt-3">
            <button type="submit" class="btn-gold">
                <i class="fas fa-arrow-left me-1"></i> التالي: اختيار الأجزاء
            </button>
        </div>
    </form>

    <div class="alert alert-gold mt-3 small">
        <i class="fas fa-info-circle me-1"></i>
        صفحة عامة - لا حاجة لتسجيل دخول. سيتم اختيار الأجزاء وتحديد مواضع التسميع في الخطوة التالية.
    </div>

    {{-- Suggestions: recommended passing scores by part count --}}
    <div class="suggestions-card mt-4" dir="rtl">
        <div class="suggestions-header">
            <i class="fas fa-lightbulb"></i>
            <span>مقترحات علامات الإختبار</span>
        </div>
        <p class="suggestions-hint">علامات الرسوب المقترحة حسب عدد الأجزاء:</p>
        <div class="suggestions-list">
            <div class="suggestion-row" data-test="3,6">
                <span class="suggestion-parts">اختبار 3 و 6</span>
                <span class="suggestion-dot">—</span>
                <span class="suggestion-label">الرسوب أقل من</span>
                <span class="suggestion-value">90%</span>
            </div>
            <div class="suggestion-row" data-test="10,15">
                <span class="suggestion-parts">اختبار 10 و 15</span>
                <span class="suggestion-dot">—</span>
                <span class="suggestion-label">الرسوب أقل من</span>
                <span class="suggestion-value">80%</span>
            </div>
            <div class="suggestion-row" data-test="20,25,30">
                <span class="suggestion-parts">اختبار 20 و 25 و 30</span>
                <span class="suggestion-dot">—</span>
                <span class="suggestion-label">الرسوب أقل من</span>
                <span class="suggestion-value">75%</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .suggestions-card {
        background: #fbfcfe;
        border: 1px solid rgba(24, 43, 72, 0.08);
        border-radius: 10px;
        padding: 12px 16px;
    }
    .suggestions-header {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--navy);
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 2px;
    }
    .suggestions-header i { color: var(--gold); font-size: 15px; }
    .suggestions-hint {
        color: #6b7a90;
        font-size: 12px;
        margin: 0 0 8px 0;
    }
    .suggestions-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .suggestion-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 13px;
        transition: background 0.15s;
    }
    .suggestion-row.active {
        background: rgba(212, 160, 48, 0.08);
    }
    .suggestion-row .suggestion-parts { font-weight: 700; color: var(--navy); }
    .suggestion-row .suggestion-dot { color: #bdc3c7; }
    .suggestion-row .suggestion-label { color: #6b7a90; }
    .suggestion-row .suggestion-value { color: var(--danger); font-weight: 700; margin-inline-start: auto; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const typeInput = document.getElementById('mafsilType');
        const rows = document.querySelectorAll('.suggestion-row');

        if (!typeInput || rows.length === 0) return;

        const highlight = () => {
            const val = parseInt(typeInput.value, 10);
            rows.forEach(row => {
                const tests = (row.getAttribute('data-test') || '').split(',').map(Number);
                row.classList.toggle('active', !isNaN(val) && tests.includes(val));
            });
        };

        typeInput.addEventListener('input', highlight);
        typeInput.addEventListener('change', highlight);
        highlight();
    })();
</script>
@endpush
