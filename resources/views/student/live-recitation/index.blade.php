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

{{-- Live region for screen readers --}}
<div id="recitationAnnouncer" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>

<div class="recitation-wrapper">
    <div class="row g-4">
        <!-- Settings Sidebar -->
        <div class="col-lg-4">
            <!-- Settings Card -->
            <div class="card settings-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-sliders-h me-2"></i>إعدادات التسميع</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">نوع التسميع</label>
                        <select class="form-select" id="modeSelect" aria-describedby="modeHelp">
                            <option value="page">صفحة واحدة</option>
                            <option value="range">نطاق صفحات</option>
                            <option value="surah">سورة</option>
                        </select>
                        <small id="modeHelp" class="text-muted">اختر نوع التسميع</small>
                    </div>

                    <div class="mb-3" id="pageInputs">
                        <label class="form-label" for="startPage">من صفحة</label>
                        <input type="number" class="form-control" id="startPage" value="1" min="1" max="604" aria-describedby="pageHelp">
                        <small id="pageHelp" class="text-muted">رقم الصفحة (1-604)</small>
                    </div>

                    <div class="mb-3 d-none" id="endPageInput">
                        <label class="form-label" for="endPage">إلى صفحة</label>
                        <input type="number" class="form-control" id="endPage" value="1" min="1" max="604">
                    </div>

                    <div class="mb-3 d-none" id="surahInputs">
                        <label class="form-label" for="surahSelect">السورة</label>
                        <select class="form-select" id="surahSelect">
                            <option value="">اختر السورة</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="verseRangeInputs">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" for="verseStart">من آية</label>
                                <input type="number" class="form-control" id="verseStart" value="1" min="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="verseEnd">إلى آية</label>
                                <input type="number" class="form-control" id="verseEnd" value="1" min="1">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="similarityThreshold">
                            عتبة التشابه
                            <span class="threshold-value" id="thresholdValue">70%</span>
                        </label>
                        <input type="range" class="form-range" id="similarityThreshold" value="70" min="50" max="100" step="5" aria-describedby="thresholdHelp">
                        <small id="thresholdHelp" class="text-muted">إذا كان التشابه أقل من هذه القيمة يعتبر خطأ</small>
                    </div>

                    <button class="btn btn-start w-100" id="startRecitation" aria-describedby="startHelp">
                        <i class="fas fa-microphone me-2"></i>ابدأ التسميع
                    </button>
                    <small id="startHelp" class="text-muted d-block mt-2 text-center">اضغط للبدء أو اضغط空格</small>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card status-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-info-circle me-2"></i>حالة التسميع</h5>
                    <span class="status-indicator" id="statusIndicator"></span>
                </div>
                <div class="card-body text-center">
                    <div id="recitationStatus" class="status-text mb-3">في انتظار البدء</div>

                    <div class="progress-wrapper mb-3">
                        <div class="progress">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="progress-text" id="recitationProgress">0 / 0</span>
                    </div>

                    <div class="stats-row mb-3">
                        <div class="stat-item">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            <span>الآيات: <span id="versesCount">0</span></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-times-circle text-danger me-1"></i>
                            <span>الأخطاء: <span id="errorCount">0</span></span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary d-none" id="nextVerse">
                            <i class="fas fa-forward me-1"></i>التالي
                        </button>
                        <button class="btn btn-stop d-none" id="stopRecitation">
                            <i class="fas fa-stop me-1"></i>إنهاء التسميع
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Current Verse Display -->
            <div class="card verses-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-book-quran me-2"></i>الآيات</h5>
                    <small class="text-muted hint-text">اضغط على آية للانتقال إليها</small>
                </div>
                <div class="card-body text-center">
                    <div id="currentVerse">
                        <div class="idle-state">
                            <i class="fas fa-microphone-alt fa-4x mb-3 opacity-25"></i>
                            <h5>اضغط "ابدأ التسميع" للبدء</h5>
                            <p class="text-muted">سيتم الاستماع إليك ومقارنة نطقك بالنص القرآني</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transcript Display -->
            <div class="card transcript-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-comment-dots me-2"></i>ما تم نطقه</h5>
                    <button class="btn btn-sm btn-outline-secondary" id="clearTranscript" title="مسح النص">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div id="transcript" class="transcript-text" dir="rtl" role="status" aria-live="polite">
                        <span class="text-muted">جارٍ الاستماع...</span>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card instructions-card">
                <div class="card-header">
                    <h5><i class="fas fa-question-circle me-2"></i>كيفية الاستخدام</h5>
                </div>
                <div class="card-body">
                    <ol class="instructions-list mb-0">
                        <li class="mb-2">
                            <span class="step-number">١</span>
                            <span>اختر نوع التسميع (صفحة أو نطاق أو سورة)</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٢</span>
                            <span>اضغط "ابدأ التسميع" وامنح الإذن لاستخدام الميكروفون</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٣</span>
                            <span>ستظهر جميع الآيات - ابدأ بتلاوة الآية الأولى</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٤</span>
                            <span>سيتم الاستماع إليك ومطابقة نطقك مع الآيات تلقائياً</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٥</span>
                            <span>عند اكتشاف خطأ، ستسمع صوت تنبيه وتُضاء الآية باللون الأحمر</span>
                        </li>
                        <li class="mb-2">
                            <span class="step-number">٦</span>
                            <span>عند إكمال آية بنجاح، يتم الانتقال تلقائياً للآية التالية</span>
                        </li>
                        <li>
                            <span class="step-number">٧</span>
                            <span>عند الانتهاء، سيظهر تقرير مفصل بالأخطاء</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for error notifications -->
<div class="toast-container" id="toastContainer"></div>

<style>
/* ========================================
   LIGHT EDITORIAL DESIGN - LIVE RECITATION
   ======================================== */

.recitation-wrapper {
    background:
        linear-gradient(135deg, rgba(250, 248, 245, 0.97) 0%, rgba(249, 246, 242, 0.97) 100%),
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4cfc4' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    min-height: calc(100vh - 120px);
    padding: 1.5rem;
    border-radius: 16px;
}

/* Card Base - Book-like appearance */
.card {
    background: #fffef9;
    border: 1px solid #e8e4dc;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.card-header {
    background: linear-gradient(to bottom, #fffef9, #f9f7f2);
    border-bottom: 1px solid #e8e4dc;
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.25rem;
}

.card-header h5 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 600;
    color: #2c3e2c;
    margin-bottom: 0;
}

/* Settings Card */
.settings-card .form-label {
    font-weight: 500;
    color: #4a5568;
    margin-bottom: 0.5rem;
}

.settings-card .form-select,
.settings-card .form-control {
    background: #fffef9;
    border: 1px solid #d4cfc4;
    border-radius: 8px;
    padding: 0.625rem 0.875rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.settings-card .form-select:focus,
.settings-card .form-control:focus {
    border-color: #1a7a4a;
    box-shadow: 0 0 0 3px rgba(26, 122, 74, 0.1);
}

.settings-card .form-range {
    height: 6px;
    border-radius: 3px;
}

.settings-card .form-range::-webkit-slider-thumb {
    appearance: none;
    width: 18px;
    height: 18px;
    background: linear-gradient(135deg, #1a7a4a, #2d9d63);
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(26, 122, 74, 0.3);
}

.threshold-value {
    font-weight: 600;
    color: #1a7a4a;
}

/* Buttons */
.btn-start {
    background: linear-gradient(135deg, #1a7a4a, #2d9d63);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 0.875rem 1.5rem;
    font-weight: 600;
    font-size: 1.05rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(26, 122, 74, 0.25);
}

.btn-start:hover:not(:disabled) {
    background: linear-gradient(135deg, #16663d, #1a7a4a);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 122, 74, 0.35);
}

.btn-start:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-stop {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.25rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-stop:hover:not(:disabled) {
    background: linear-gradient(135deg, #c82333, #bd2130);
    transform: translateY(-1px);
}

.btn-outline-primary {
    color: #1a7a4a;
    border: 2px solid #1a7a4a;
    border-radius: 10px;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover:not(:disabled) {
    background: #1a7a4a;
    color: white;
}

/* Status Card */
.status-card .card-body {
    padding: 1.25rem;
}

.status-text {
    font-size: 1.1rem;
    font-weight: 500;
    color: #2c3e2c;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #d4cfc4;
    display: inline-block;
}

.status-indicator.connecting {
    background: #ffc107;
    animation: pulse 1.5s ease-in-out infinite;
}

.status-indicator.active {
    background: #1a7a4a;
    animation: pulse 2s ease-in-out infinite;
}

.status-indicator.error {
    background: #dc3545;
}

.status-indicator.completed {
    background: #28a745;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
}

/* Progress */
.progress-wrapper {
    position: relative;
}

.progress {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, #1a7a4a, #2d9d63);
    border-radius: 4px;
    transition: width 0.4s ease;
}

.progress-text {
    display: block;
    margin-top: 0.5rem;
    font-weight: 500;
    color: #4a5568;
}

.stats-row {
    display: flex;
    justify-content: center;
    gap: 2rem;
}

.stat-item {
    font-size: 0.95rem;
    color: #4a5568;
}

/* Verses Card */
.verses-card {
    min-height: 300px;
}

.verses-card .hint-text {
    font-size: 0.85rem;
}

.idle-state {
    padding: 3rem 2rem;
}

.idle-state i {
    color: #1a7a4a;
}

.idle-state h5 {
    color: #4a5568;
    font-weight: 500;
}

/* Verse Cards */
.verses-container {
    max-height: 450px;
    overflow-y: auto;
    padding: 0.5rem;
}

.verse-card {
    padding: 1.25rem;
    background: linear-gradient(135deg, #fffef9, #f9f7f2);
    border: 1px solid #e8e4dc;
    border-radius: 10px;
    margin-bottom: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.verse-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: transparent;
    transition: background 0.3s ease;
}

.verse-card:hover {
    border-color: #c4bdb0;
    transform: translateX(4px);
}

.verse-card.active-verse {
    background: linear-gradient(135deg, #e8f5ec, #d1eacc);
    border-color: #1a7a4a;
    box-shadow: 0 4px 12px rgba(26, 122, 74, 0.15);
}

.verse-card.active-verse::before {
    background: linear-gradient(to bottom, #1a7a4a, #2d9d63);
}

.verse-card.error-verse {
    background: linear-gradient(135deg, #fce8e8, #f5c6c6);
    border-color: #dc3545;
    animation: shake 0.5s ease-in-out;
}

.verse-card.error-verse::before {
    background: #dc3545;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
}

.verse-badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
}

.verse-counter {
    font-size: 0.85rem;
    color: #6c757d;
}

.verse-text {
    font-size: 1.6rem;
    line-height: 2.1;
    font-family: 'Amiri', serif;
    color: #2c3e2c;
    margin-bottom: 0;
}

/* Transcript Card */
.transcript-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.transcript-text {
    min-height: 70px;
    padding: 1rem;
    background: #f9f7f2;
    border-radius: 8px;
    font-size: 1.15rem;
    line-height: 1.8;
    color: #2c3e2c;
}

.transcript-text .text-success {
    color: #1a7a4a !important;
}

/* Instructions Card */
.instructions-card .card-body {
    padding: 1.25rem;
}

.instructions-list {
    padding-right: 1.5rem;
}

.instructions-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    color: #4a5568;
}

.step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #1a7a4a, #2d9d63);
    color: white;
    border-radius: 50%;
    font-size: 0.85rem;
    font-weight: 600;
}

/* Toast Notifications */
.toast-container {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.toast-error {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(220, 53, 69, 0.4);
    animation: slideUp 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toast-success {
    background: linear-gradient(135deg, #1a7a4a, #2d9d63);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(26, 122, 74, 0.4);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Connecting State */
.connecting-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
}

.connecting-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid #e9ecef;
    border-top-color: #1a7a4a;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 991px) {
    .recitation-wrapper {
        padding: 1rem;
    }

    .verse-text {
        font-size: 1.4rem;
    }
}

@media (max-width: 576px) {
    .stats-row {
        flex-direction: column;
        gap: 0.5rem;
    }

    .verse-text {
        font-size: 1.25rem;
    }
}

/* Accessibility */
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

*:focus-visible {
    outline: 3px solid rgba(26, 122, 74, 0.4);
    outline-offset: 2px;
}
</style>
@endsection

@push('scripts')
<script src="{{ asset('js/student/live-recitation.js') }}"></script>
@endpush
