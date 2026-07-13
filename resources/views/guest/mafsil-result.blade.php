@extends('guest.layout')

@section('title', 'نتيجة اختبار الأجزاء - عام')

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

@push('styles')
<style>
    .result-page {
        max-width: 980px;
        margin: 0 auto;
        color: #2d3a4a;
    }

    .result-header {
        text-align: center;
        margin-bottom: 32px;
        padding: 8px 0;
    }

    .result-header h2 {
        color: #182B48;
        font-size: 26px;
        font-weight: 700;
        margin: 0 0 6px 0;
        letter-spacing: 0;
    }

    .result-header .subtitle {
        color: #8a96a8;
        font-size: 14px;
        margin: 0;
    }

    .info-card {
        background: #ffffff;
        border: 1px solid #eef0f4;
        border-radius: 18px;
        padding: 20px 22px;
        margin-bottom: 18px;
        box-shadow: 0 1px 2px rgba(15, 28, 51, 0.03);
    }

    .info-card-title {
        font-size: 12px;
        font-weight: 700;
        color: #8a96a8;
        text-transform: none;
        margin-bottom: 14px;
        letter-spacing: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-card-title i {
        color: #d4a030;
        font-size: 13px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 14px;
    }

    .info-cell {
        padding: 12px 14px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #f1f3f7;
        transition: all 0.2s ease;
    }

    .info-cell:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

    .info-cell .lbl {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-cell .val {
        font-size: 15px;
        color: #182B48;
        font-weight: 700;
        line-height: 1.3;
    }

    .score-highlight {
        text-align: center;
        padding: 36px 24px;
        background: linear-gradient(135deg, #fcf6e8 0%, #fef9ed 100%);
        border: 1px solid #f0e2b8;
        border-radius: 20px;
        margin-bottom: 18px;
        position: relative;
        overflow: hidden;
    }

    .score-highlight::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(212, 160, 48, 0.08), transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .score-circle {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: white;
        box-shadow: 0 4px 20px rgba(212, 160, 48, 0.18);
        margin-bottom: 14px;
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
        position: relative;
        z-index: 1;
        background: white;
        width: 116px;
        height: 116px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .score-circle .score-value {
        font-size: 30px;
        font-weight: 800;
        color: #182B48;
        line-height: 1;
    }

    .score-circle .score-suffix {
        font-size: 13px;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 2px;
    }

    .score-status {
        font-size: 16px;
        font-weight: 700;
        color: var(--score-color, #d4a030);
        margin: 0;
    }

    .status-passed {
        --score-color: #16a34a;
        --score-pct: var(--final-pct, 100);
    }

    .status-failed {
        --score-color: #dc2626;
        --score-pct: var(--final-pct, 100);
    }

    .congrats-banner {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 32px 24px;
        text-align: center;
        margin-bottom: 18px;
        position: relative;
        overflow: hidden;
    }

    .congrats-banner::before {
        content: '';
        position: absolute;
        top: -30%;
        left: -10%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(34, 197, 94, 0.1), transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .congrats-banner::after {
        content: '';
        position: absolute;
        bottom: -30%;
        right: -10%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(212, 160, 48, 0.1), transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .congrats-content {
        position: relative;
        z-index: 1;
    }

    .congrats-icon {
        font-size: 44px;
        margin-bottom: 8px;
        line-height: 1;
    }

    .congrats-icon .trophy {
        color: #d4a030;
    }

    .congrats-icon .sparkle {
        color: #16a34a;
        font-size: 28px;
    }

    .congrats-title {
        color: #15803d;
        font-size: 26px;
        font-weight: 800;
        margin: 0 0 6px 0;
    }

    .congrats-subtitle {
        font-size: 16px;
        color: #166534;
        margin: 0 0 4px 0;
        font-weight: 600;
    }

    .congrats-blessing {
        font-size: 14px;
        color: #4b5563;
        margin: 0;
    }

    .action-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 28px;
    }

    .btn-elegant {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .btn-elegant.primary {
        background: linear-gradient(135deg, #d4a030 0%, #e6b84d 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(212, 160, 48, 0.25);
    }

    .btn-elegant.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(212, 160, 48, 0.35);
        color: white;
    }

    .btn-elegant.secondary {
        background: white;
        color: #182B48;
        border-color: #e2e8f0;
    }

    .btn-elegant.secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #182B48;
    }

    .notes-card {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 8px;
        color: #78350f;
        font-size: 14px;
    }

    .notes-card strong {
        color: #92400e;
    }

    @media (max-width: 600px) {
        .score-circle {
            width: 110px;
            height: 110px;
        }
        .score-circle .score-inner {
            width: 96px;
            height: 96px;
        }
        .score-circle .score-value {
            font-size: 26px;
        }
        .congrats-title {
            font-size: 22px;
        }
        .info-card {
            padding: 16px;
        }
    }
</style>
@endpush

@section('content')
@php
    $isPassed = $session->status === 'passed';
    $finalScore = (float) $session->final_score;
@endphp
<div class="result-page">

    <div class="result-header">
        <h2><i class="fas fa-clipboard-check me-2" style="color: #d4a030;"></i>نتيجة جلسة اختبار الأجزاء</h2>
        <p class="subtitle">تفاصيل تقييم الطالب في اختبار الأجزاء</p>
    </div>

    <div class="info-card">
        <div class="info-card-title"><i class="fas fa-user-graduate"></i>معلومات الطالب</div>
        <div class="info-grid">
            <div class="info-cell">
                <div class="lbl">الطالب</div>
                <div class="val">{{ $session->student_name ?? ($session->student->user->name ?? 'غير معروف') }}</div>
            </div>
            @if($session->mahd_name)
            <div class="info-cell">
                <div class="lbl">المحضن</div>
                <div class="val">{{ $session->mahd_name }}</div>
            </div>
            @endif
            <div class="info-cell">
                <div class="lbl">التاريخ</div>
                <div class="val">{{ $session->session_date }}</div>
            </div>
            <div class="info-cell">
                <div class="lbl">نوع الاختبار</div>
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

    @if($isPassed)
    <div class="congrats-banner">
        <div class="congrats-content">
            <div class="congrats-icon">
                <i class="fas fa-trophy trophy"></i>
                <i class="fas fa-sparkles sparkle"></i>
            </div>
            <h2 class="congrats-title">ألف مبارك</h2>
            <p class="congrats-blessing">
                بارك الله فيك وفي محفظك، وجعله في ميزان حسنااتكما
            </p>
        </div>
    </div>
    @endif

    @if($session->notes)
    <div class="notes-card">
        <strong>ملاحظات:</strong> {{ $session->notes }}
    </div>
    @endif

    <div class="action-row">
        <a href="{{ url('public/recitation/mafsil') }}" class="btn-elegant primary">
            <i class="fas fa-plus"></i> جلسة اختبار جديدة
        </a>
        <button type="button" id="exportPdfBtn" class="btn-elegant secondary">
            <i class="fas fa-file-pdf"></i> تصدير PDF
        </button>
        <button type="button" id="exportImageBtn" class="btn-elegant secondary">
            <i class="fas fa-image"></i> تصدير صورة
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(function() {
    const exportTarget = document.querySelector('.result-page');
    if (!exportTarget) return;

    const fileName = 'نتيجة_اختبار_' + @json($session->student_name ?? 'الطالب') + '_' + @json((string) $session->session_date);

    function showLoading(btn, text) {
        btn.disabled = true;
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + text;
    }

    function restoreBtn(btn) {
        btn.disabled = false;
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
        }
    }

    async function captureCanvas() {
        return await html2canvas(exportTarget, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        });
    }

    const pdfBtn = document.getElementById('exportPdfBtn');
    pdfBtn.addEventListener('click', async function() {
        try {
            showLoading(pdfBtn, 'جاري التصدير...');
            const canvas = await captureCanvas();
            const imgData = canvas.toDataURL('image/png');

            const { jsPDF } = window.jspdf;
            const orientation = canvas.width > canvas.height ? 'landscape' : 'portrait';
            const pdf = new jsPDF({
                orientation: orientation,
                unit: 'pt',
                format: 'a4'
            });

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 20;
            const maxWidth = pageWidth - (margin * 2);
            const maxHeight = pageHeight - (margin * 2);

            const ratio = Math.min(maxWidth / canvas.width, maxHeight / canvas.height);
            const imgWidth = canvas.width * ratio;
            const imgHeight = canvas.height * ratio;
            const x = (pageWidth - imgWidth) / 2;
            const y = (pageHeight - imgHeight) / 2;

            pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
            pdf.save(fileName + '.pdf');

            toastr.success('تم تصدير الملف بصيغة PDF بنجاح');
        } catch (e) {
            console.error(e);
            toastr.error('حدث خطأ أثناء تصدير PDF');
        } finally {
            restoreBtn(pdfBtn);
        }
    });

    const imgBtn = document.getElementById('exportImageBtn');
    imgBtn.addEventListener('click', async function() {
        try {
            showLoading(imgBtn, 'جاري التصدير...');
            const canvas = await captureCanvas();
            const link = document.createElement('a');
            link.download = fileName + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();

            toastr.success('تم تصدير الصورة بنجاح');
        } catch (e) {
            console.error(e);
            toastr.error('حدث خطأ أثناء تصدير الصورة');
        } finally {
            restoreBtn(imgBtn);
        }
    });
})();
</script>
@endsection
