@extends('layouts.app')

@section('title', 'التسميع المباشر')
@section('page-title', 'التسميع المباشر')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
$user = Auth::user();
$token = $user->currentAccessToken();
$apiToken = $token ? $token->token : null;
@endphp
@if($apiToken)
<meta name="api-token" content="{{ $apiToken }}">
@endif

<div class="row g-4">
    <!-- Setup Card -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-cog me-2"></i>إعدادات التسميع</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">نوع التسميع</label>
                    <select class="form-select" id="modeSelect">
                        <option value="page">صفحة واحدة</option>
                        <option value="range">نطاق صفحات</option>
                    </select>
                </div>

                <div class="mb-3" id="pageInputs">
                    <label class="form-label">من صفحة</label>
                    <input type="number" class="form-control" id="startPage" value="1" min="1" max="604">
                </div>

                <div class="mb-3 d-none" id="endPageInput">
                    <label class="form-label">إلى صفحة</label>
                    <input type="number" class="form-control" id="endPage" value="1" min="1" max="604">
                </div>

                <div class="mb-3">
                    <label class="form-label">عتبة التشابه (%)</label>
                    <input type="number" class="form-control" id="similarityThreshold" value="70" min="50" max="100">
                    <small class="text-muted">إذا كان التشابه أقل من هذه القيمة يعتبر خطأ</small>
                </div>

                <button class="btn btn-success w-100" id="startRecitation">
                    <i class="fas fa-microphone me-2"></i>ابدأ التسميع
                </button>
            </div>
        </div>

        <!-- Status Card -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2"></i>حالة التسميع</h5>
            </div>
            <div class="card-body text-center">
                <div id="recitationStatus" class="h4 mb-3">في انتظار البدء</div>

                <div class="mb-3">
                    <span class="badge bg-primary p-2">
                        <span id="recitationProgress">0 / 0</span>
                    </span>
                </div>

                <div class="mb-3">
                    <span class="text-danger">
                        <i class="fas fa-times-circle me-1"></i>
                        الأخطاء: <span id="errorCount">0</span>
                    </span>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary d-none" id="nextVerse">
                        <i class="fas fa-forward me-1"></i>التالي
                    </button>
                    <button class="btn btn-danger d-none" id="stopRecitation">
                        <i class="fas fa-stop me-1"></i>إنهاء التسميع
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Current Verse Display -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-book-quran me-2"></i>الآيات</h5>
                <small class="text-muted">الآية النشطة محددة باللون الأخضر</small>
            </div>
            <div class="card-body text-center">
                <div id="currentVerse">
                    <div class="text-muted p-5">
                        <i class="fas fa-microphone-alt fa-4x mb-3 opacity-25"></i>
                        <h5>اضغط "ابدأ التسميع" للبدء</h5>
                        <p class="text-muted">سيتم الاستماع إليك ومقارنة نطقك بالنص القرآني</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transcript Display -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-comment-dots me-2"></i>ما تم نطقه</h5>
            </div>
            <div class="card-body">
                <div id="transcript" class="p-3 bg-light rounded text-end" dir="rtl" style="min-height: 60px; font-size: 1.2rem;">
                    <span class="text-muted">جارٍ الاستماع...</span>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-question-circle me-2"></i>كيفية الاستخدام</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">اختر صفحة البداية (صفحة واحدة أو نطاق)</li>
                    <li class="mb-2">اضغط "ابدأ التسميع" وامنح الإذن لاستخدام الميكروفون</li>
                    <li class="mb-2">ستظهر جميع الآيات في الصفحة - ابدأ بتلاوة الآية الأولى</li>
                    <li class="mb-2">سيتم الاستماع إليك ومطابقة نطقك مع الآيات تلقائياً</li>
                    <li class="mb-2">عند اكتشاف خطأ، ستسمع صوت تنبيه وتُضاء الآية باللون الأحمر</li>
                    <li class="mb-2">عند إكمال آية بنجاح، يتم الانتقال تلقائياً للآية التالية</li>
                    <li>عند الانتهاء، سيظهر تقرير مفصل بالأخطاء</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<style>
.error-verse {
    background-color: #f8d7da !important;
    border: 2px solid #dc3545 !important;
    border-radius: 8px;
    padding: 10px;
    animation: shake 0.5s ease-in-out;
}

.active-verse {
    background: linear-gradient(135deg, #d1e7dd, #a3cfbb) !important;
    border: 2px solid #198754 !important;
    border-radius: 8px;
    padding: 10px;
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
}

.verses-container {
    max-height: 500px;
    overflow-y: auto;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.verse-card {
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.verse-text {
    font-size: 1.8rem;
    line-height: 2.2;
    font-family: 'Amiri', serif;
}
</style>
@endsection

@section('scripts')
<script src="{{ asset('js/student/live-recitation.js') }}"></script>
@endsection
