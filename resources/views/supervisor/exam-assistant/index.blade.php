@extends('layouts.app')

@section('title', 'اختبار المواضع')
@section('page-title', 'اختبار المواضع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.exam-assistant {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.exam-assistant .page-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.exam-assistant .page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.exam-assistant .page-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 16px;
}

.exam-assistant .page-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 6px 18px rgba(212, 160, 48, 0.35);
}

.exam-assistant .page-title {
    color: white;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 4px;
}

.exam-assistant .page-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    margin: 0;
}

.exam-assistant .student-select-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.exam-assistant .card-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .card-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .card-header h5 i {
    color: var(--gold);
}

.exam-assistant .card-body {
    padding: 24px;
}

.exam-assistant .form-select {
    border: 2px solid rgba(24, 43, 72, 0.08);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.exam-assistant .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 160, 48, 0.15);
}

.exam-assistant .btn-start {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    padding: 14px 40px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
    color: white;
}

.exam-assistant .btn-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
}

.exam-assistant .exam-header {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.exam-assistant .exam-info {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.exam-assistant .exam-student {
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .exam-student-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.exam-assistant .student-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--navy);
}

.exam-assistant .status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.exam-assistant .status-progress {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(99, 102, 241, 0.2));
    color: #3b82f6;
}

.exam-assistant .status-complete {
    background: linear-gradient(135deg, rgba(5, 150, 105, 0.15), rgba(16, 185, 129, 0.2));
    color: #059669;
}

.exam-assistant .exam-stats {
    display: flex;
    gap: 12px;
}

.exam-assistant .stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04), rgba(42, 69, 112, 0.06));
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
}

.exam-assistant .stat-item i {
    color: var(--gold);
}

.exam-assistant .exam-actions {
    display: flex;
    gap: 10px;
}

.exam-assistant .btn-action {
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
}

.exam-assistant .btn-finish {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
}

.exam-assistant .btn-finish:hover {
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
}

.exam-assistant .btn-cancel {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color: white;
}

.exam-assistant .btn-cancel:hover {
    box-shadow: 0 6px 18px rgba(220, 38, 38, 0.35);
}

.exam-assistant .settings-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
    margin-bottom: 16px;
}

.exam-assistant .settings-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .settings-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 14px;
}

.exam-assistant .settings-header h5 i {
    color: var(--gold);
}

.exam-assistant .settings-body {
    padding: 16px;
}

.exam-assistant .juz-grid {
    max-height: 160px;
    overflow-y: auto;
    border: 1px solid rgba(24, 43, 72, 0.08);
    border-radius: 10px;
    padding: 12px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02), rgba(42, 69, 112, 0.03));
}

.exam-assistant .juz-grid::-webkit-scrollbar {
    width: 6px;
}

.exam-assistant .juz-grid::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.exam-assistant .juz-grid::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.exam-assistant .juz-item {
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    background: white;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
}

.exam-assistant .juz-item:hover {
    border-color: var(--gold);
    background: rgba(212, 160, 48, 0.05);
}

.exam-assistant .juz-item.active {
    border-color: var(--navy);
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
}

.exam-assistant .juz-item input {
    display: none;
}

.exam-assistant .btn-select-all {
    background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.exam-assistant .btn-select-all:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.exam-assistant .verse-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.exam-assistant .verse-header {
    background: linear-gradient(135deg, #1a2a4a 0%, #2A4570 50%, #1e3a5f 100%);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.exam-assistant .verse-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .verse-header h5 i {
    color: var(--gold);
}

.exam-assistant .verse-header .btn-next {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    color: white;
    transition: all 0.2s;
}

.exam-assistant .verse-header .btn-next:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.3);
}

.exam-assistant .verse-header .btn-next:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.exam-assistant .keyboard-hint {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 8px;
}

.exam-assistant .verse-body {
    padding: 0;
}

.exam-assistant .verse-display {
    background: linear-gradient(145deg, #1a2a4a 0%, #2A4570 50%, #1e3a5f 100%);
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-family: 'Amiri', 'Scheherazade', serif;
    font-size: 28px;
    line-height: 2.2;
    direction: rtl;
    color: white;
    position: relative;
    overflow: hidden;
}

.exam-assistant .verse-display::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
    animation: shimmer 8s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(10%, 10%); }
}

.exam-assistant .verse-text {
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
    position: relative;
    z-index: 1;
    padding: 30px;
}

.exam-assistant .verse-placeholder {
    opacity: 0.7;
    font-size: 16px;
    font-family: 'Tajawal', sans-serif;
    position: relative;
    z-index: 1;
}

.exam-assistant .verse-placeholder i {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.1); opacity: 1; }
}

.exam-assistant .result-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
    margin-bottom: 16px;
}

.exam-assistant .result-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .result-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .result-header h5 i {
    color: var(--gold);
}

.exam-assistant .result-body {
    padding: 16px;
}

.exam-assistant .form-control {
    border: 2px solid rgba(24, 43, 72, 0.08);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.exam-assistant .form-control:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 160, 48, 0.15);
}

.exam-assistant .btn-add-result {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    color: white;
    transition: all 0.3s ease;
    flex: 1;
}

.exam-assistant .btn-add-result:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
}

.exam-assistant .btn-back {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    color: white;
    transition: all 0.3s ease;
}

.exam-assistant .btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(24, 43, 72, 0.35);
}

.exam-assistant .questions-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.exam-assistant .questions-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .questions-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.exam-assistant .questions-header h5 i {
    color: var(--gold);
}

.exam-assistant .questions-body {
    padding: 0;
    max-height: 300px;
    overflow-y: auto;
}

.exam-assistant .question-item {
    transition: all 0.2s ease;
    border-right: 3px solid transparent;
    padding: 12px 16px;
    cursor: pointer;
}

.exam-assistant .question-item:hover {
    background: linear-gradient(90deg, rgba(212, 160, 48, 0.05) 0%, rgba(24, 43, 72, 0.02) 100%);
    border-right-color: var(--gold);
}

.exam-assistant .question-item.active-review {
    background: rgba(245, 158, 11, 0.1);
    border-right-color: #f59e0b;
}

.exam-assistant .order-badge {
    background: var(--navy);
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.exam-assistant .zero-errors {
    color: #059669;
    font-weight: 600;
}

.exam-assistant .empty-questions {
    text-align: center;
    padding: 40px 20px;
}

.exam-assistant .empty-questions-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.06), rgba(42, 69, 112, 0.08));
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.exam-assistant .empty-questions-icon i {
    font-size: 28px;
    color: var(--navy);
    opacity: 0.4;
}

.exam-assistant .btn-warning-custom {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border: none;
    color: white;
}

.exam-assistant .btn-warning-custom:hover {
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.35);
}

.exam-assistant .exam-complete-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

.exam-assistant .exam-complete-overlay.show {
    opacity: 1;
    visibility: visible;
}

.exam-assistant .exam-complete-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    max-width: 400px;
    transform: scale(0.8);
    transition: transform 0.3s;
}

.exam-assistant .exam-complete-overlay.show .exam-complete-card {
    transform: scale(1);
}

.exam-assistant .success-checkmark {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #059669, #10b981);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.exam-assistant .success-checkmark i {
    font-size: 40px;
    color: white;
}

.exam-assistant .btn-loading {
    position: relative;
    pointer-events: none;
}

.exam-assistant .btn-loading::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    top: 50%;
    left: 50%;
    margin: -9px 0 0 -9px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.exam-assistant .selected-count {
    font-size: 12px;
    color: #6b7280;
    margin-top: 8px;
}

.exam-assistant .quran-page-bg {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 0 0 12px 12px;
}

@media (max-width: 768px) {
    .exam-assistant .exam-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .exam-assistant .exam-stats {
        flex-wrap: wrap;
    }

    .exam-assistant .exam-actions {
        width: 100%;
        flex-wrap: wrap;
    }

    .exam-assistant .juz-item {
        padding: 6px 10px;
        font-size: 12px;
    }
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="exam-assistant">
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <h1 class="page-title">اختبار المواضع</h1>
                <p class="page-subtitle">اختبار تحديد مستوى الطالب في القرآن الكريم</p>
            </div>
        </div>
    </div>

    <div id="student-selection" class="row justify-content-center">
        <div class="col-lg-6">
            <div class="student-select-card">
                <div class="card-header">
                    <h5><i class="fas fa-user-graduate"></i>اختر الطالب</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">الطالب</label>
                        <select id="studentSelect" class="form-select select2" style="font-size: 16px; padding: 12px 16px;">
                            <option value="">-- ابحث عن طالب --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->user->name ?? '' }} - {{ $student->level->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="button" id="startExamBtn" class="btn btn-start">
                            <i class="fas fa-play me-2"></i>بدء الاختبار
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="exam-interface" class="d-none">
        <div class="exam-header">
            <div class="exam-info">
                <div class="exam-student">
                    <div class="exam-student-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <span id="examStudentName" class="student-name"></span>
                </div>
                <span id="examStatusBadge" class="status-badge status-progress">قيد التقدم</span>
                <div class="exam-stats">
                    <div class="stat-item">
                        <i class="fas fa-list"></i>
                        <span id="questionsCount">0</span> سؤال
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <span id="totalErrors">0</span> خطأ
                    </div>
                </div>
            </div>
            <div class="exam-actions">
                <button type="button" id="finishExamBtn" class="btn-action btn-finish">
                    <i class="fas fa-check-circle"></i>
                    إنهاء الاختبار
                </button>
                <button type="button" id="cancelExamBtn" class="btn-action btn-cancel">
                    <i class="fas fa-times-circle"></i>
                    إلغاء
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="settings-card">
                    <div class="settings-header">
                        <h5><i class="fas fa-filter"></i>إعدادات الاختبار</h5>
                    </div>
                    <div class="settings-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="mb-0 fw-bold">اختر الأجزاء (<span id="selectedJuzCount">0</span> محدد)</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn-select-all" id="selectAllJuz">تحديد الكل</button>
                                <button type="button" class="btn-select-all" id="clearAllJuz">إلغاء</button>
                            </div>
                        </div>
                        <div class="juz-grid">
                            <div class="row g-2">
                                @for($i = 1; $i <= 30; $i++)
                                    <div class="col-4 col-md-3">
                                        <label class="juz-item" for="juz{{ $i }}">
                                            <input class="juz-checkbox" type="checkbox" value="{{ $i }}" id="juz{{ $i }}"> الجزء {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div class="selected-count">اضغط على الجزء لتحديده أو إلغاء تحديده</div>
                    </div>
                </div>

                <div class="verse-card">
                    <div class="verse-header">
                        <h5><i class="fas fa-book-quran"></i>صفحة القرآن</h5>
                        <button type="button" class="btn-next" id="nextQuestionBtn">
                            <i class="fas fa-arrow-right me-1"></i>السؤال التالي<span class="keyboard-hint">N</span>
                        </button>
                    </div>
                    <div class="verse-body">
                        <div class="verse-display" id="pageDisplay">
                            <div class="verse-placeholder">
                                <i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>
                                اضغط على "السؤال التالي" لعرض آية عشوائية
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="result-card" id="resultFormCard">
                    <div class="result-header">
                        <h5><i class="fas fa-clipboard-check"></i>تسجيل النتيجة</h5>
                    </div>
                    <div class="result-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">عدد الأخطاء</label>
                            <input type="number" id="errorsCount" class="form-control" min="0" value="0" placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ملاحظات</label>
                            <textarea id="notesInput" class="form-control" rows="3" placeholder="ملاحظات المشرف..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" id="addResultBtn" class="btn btn-add-result">
                                <i class="fas fa-plus me-2"></i>إضافة النتيجة
                            </button>
                            <button type="button" id="backToExamBtn" class="btn btn-back d-none">
                                <i class="fas fa-arrow-left me-1"></i>العودة
                            </button>
                        </div>
                    </div>
                </div>

                <div class="questions-card">
                    <div class="questions-header">
                        <h5><i class="fas fa-list"></i>الأسئلة</h5>
                    </div>
                    <div class="questions-body" id="questionsList">
                        <div class="empty-questions" id="noQuestionsMsg">
                            <div class="empty-questions-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <p class="text-muted mb-1">لا توجد أسئلة بعد</p>
                            <small class="text-muted">اضغط على "السؤال التالي" لبدء الاختبار</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="exam-complete-overlay" id="examCompleteOverlay">
        <div class="exam-complete-card">
            <div class="success-checkmark">
                <i class="fas fa-check"></i>
            </div>
            <h4 class="mb-3">تم إنهاء الاختبار!</h4>
            <p class="text-muted mb-4">تم حفظ جميع الإجابات بنجاح</p>
            <p class="mb-4">
                <strong id="finalQuestionsCount">0</strong> سؤال |
                <strong id="finalErrorsCount" class="text-danger">0</strong> خطأ
            </p>
            <button type="button" class="btn btn-start" onclick="location.reload()">
                <i class="fas fa-redo me-2"></i>بدء اختبار جديد
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    let examId = null;
    let currentQuestionId = null;
    let isReviewMode = false;
    let currentVerseData = null;
    let originalErrors = 0;
    let originalNotes = '';
    let questionCount = 0;
    let totalErrorsCount = 0;

    checkInProgressExam();

    function updateJuzCount() {
        const selected = $('.juz-checkbox:checked').length;
        $('#selectedJuzCount').text(selected);
        $('#nextQuestionBtn').prop('disabled', selected === 0);
    }

    $('#selectAllJuz').click(function() {
        $('.juz-checkbox').prop('checked', true);
        $('.juz-item').addClass('active');
        updateJuzCount();
    });

    $('#clearAllJuz').click(function() {
        $('.juz-checkbox').prop('checked', false);
        $('.juz-item').removeClass('active');
        updateJuzCount();
    });

    $('.juz-item').click(function(e) {
        if (e.target.tagName === 'INPUT') return;
        const checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        $(this).toggleClass('active', checkbox.prop('checked'));
    });

    $('.juz-checkbox').on('change', updateJuzCount);
    $('#nextQuestionBtn').prop('disabled', true);

    $('#questionsList').on('click', '.question-item', function() {
        const $el = $(this);
        enterReviewMode({
            id: $el.data('id'),
            surah: $el.data('surah'),
            ayah_number: $el.data('ayah'),
            page_number: $el.data('page'),
            errors_count: $el.data('errors'),
            notes: $el.data('notes'),
            verse_text: $el.data('text')
        });
    });

    if ($('.select2').length && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            placeholder: '-- ابحث عن طالب --',
            allowClear: true,
            width: '100%',
            dir: 'rtl'
        });
    }

    $('#startExamBtn').click(function() {
        const studentId = $('#studentSelect').val();
        if (!studentId) {
            toastr.warning('الرجاء اختيار الطالب');
            return;
        }
        startExam(studentId);
    });

    $('#nextQuestionBtn').click(function() {
        generateQuestion();
    });

    $('#backToExamBtn').click(function() {
        exitReviewMode();
    });

    $('#finishExamBtn').click(function() {
        if (!examId) return;
        const msg = questionCount === 0
            ? 'لا توجد أسئلة مسجلة. هل أنت متأكد من إنهاء الاختبار؟'
            : 'هل أنت متأكد من إنهاء الاختبار؟';
        if (!confirm(msg)) return;
        finishExam();
    });

    $('#cancelExamBtn').click(function() {
        if (!examId) return;
        if (confirm('هل أنت متأكد من إلغاء الاختبار؟ سيتم حذف جميع البيانات.')) {
            cancelExam();
        }
    });

    function checkInProgressExam() {
        $.get('/api/exam/in-progress')
        .done(function(response) {
            if (response.exam) {
                examId = response.exam.id;
                loadInProgressExam(response.exam, response.questions);
            }
        })
        .fail(function(xhr) {
            console.error('Failed to check in-progress exam:', xhr);
        });
    }

    function startExam(studentId) {
        const $btn = $('#startExamBtn');
        $btn.prop('disabled', true).addClass('btn-loading').html('<i class="fas fa-spinner fa-spin me-2"></i>جاري البدء');
        $.post('/api/exam/start', {
            _token: csrfToken,
            student_id: studentId
        }, function(response) {
            examId = response.exam.id;
            showExamInterface(response.exam);
            toastr.success('تم بدء الاختبار');
            $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-play me-2"></i>بدء الاختبار');
        }).fail(function(xhr) {
            const msg = xhr.responseJSON?.error || 'حدث خطأ أثناء بدء الاختبار';
            toastr.error(msg);
            $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-play me-2"></i>بدء الاختبار');
        });
    }

    function showExamInterface(exam) {
        $('#student-selection').addClass('d-none');
        $('#exam-interface').removeClass('d-none');
        $('#examStudentName').text(exam.student?.user?.name || exam.student?.name || 'طالب');

        let examDate = exam.exam_date;
        if (typeof examDate === 'object' && examDate.date) {
            examDate = examDate.date;
        }
        const dateObj = new Date(examDate);
        const formattedDate = dateObj.toLocaleDateString('ar-SA', { year: 'numeric', month: 'long', day: 'numeric' });

        $('#examStatusBadge').removeClass('status-complete').addClass('status-progress').text('قيد التقدم');
        $('#questionsList').html('<div class="empty-questions" id="noQuestionsMsg"><div class="empty-questions-icon"><i class="fas fa-inbox"></i></div><p class="text-muted mb-1">لا توجد أسئلة بعد</p><small class="text-muted">اضغط على "السؤال التالي" لبدء الاختبار</small></div>');
        $('#questionsCount').text('0');
        $('#totalErrors').text('0');
        $('#finishExamBtn, #cancelExamBtn, #nextQuestionBtn, #addResultBtn').prop('disabled', false);
        questionCount = 0;
        totalErrorsCount = 0;
    }

    function loadInProgressExam(exam, questions) {
        if (!exam || !exam.id || !exam.student || !exam.student.user) {
            $('#student-selection').removeClass('d-none');
            $('#exam-interface').addClass('d-none');
            examId = null;
            return;
        }
        showExamInterface(exam);
        renderQuestionsList(questions);
    }

    function generateQuestion() {
        if (isReviewMode) {
            toastr.warning('الرجاء إنهاء التعديل أولاً');
            return;
        }
        const selectedJuz = $('.juz-checkbox:checked').map(function() { return parseInt(this.value); }).get();
        if (selectedJuz.length === 0) {
            toastr.warning('الرجاء اختيار جزء واحد على الأقل');
            return;
        }
        const $btn = $('#nextQuestionBtn');
        $btn.prop('disabled', true).addClass('btn-loading').html('<i class="fas fa-spinner fa-spin me-1"></i>جاري التحميل');
        $.ajax({
            url: '{{ route("supervisor.exam-assistant.generate") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                juz_list: selectedJuz,
            },
            success: function(response) {
                if (response.success) {
                    currentVerseData = response;
                    $('#pageDisplay').html(response.page_html);
                    $('#errorsCount').val(0);
                    $('#notesInput').val('');
                    toastr.success('تم توليد السؤال');
                }
                $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-arrow-right me-1"></i>السؤال التالي<span class="keyboard-hint">N</span>');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'حدث خطأ';
                toastr.error(msg);
                $btn.prop('disabled', false).removeClass('btn-loading').html('<i class="fas fa-arrow-right me-1"></i>السؤال التالي<span class="keyboard-hint">N</span>');
            }
        });
    }

    function addResult() {
        if (!examId || !currentVerseData) {
            toastr.warning('الرجاء توليد سؤال أولاً');
            return;
        }
        const ayahData = currentVerseData.ayah;
        const verseText = ayahData?.text || (typeof ayahData === 'string' ? ayahData : null);
        if (!verseText) {
            console.error('Verse text missing:', currentVerseData);
            toastr.error('بيانات الآية غير مكتملة');
            return;
        }
        const $btn = $('#addResultBtn');
        $btn.prop('disabled', true).addClass('btn-loading');
        $.post('/api/exam/add-question', {
            _token: csrfToken,
            exam_id: examId,
            surah_id: currentVerseData.surah_id,
            ayah_id: ayahData.id || currentVerseData.ayah_id,
            ayah_number: ayahData.ayah_number || 0,
            page_number: ayahData.page_number || 0,
            juz_number: ayahData.juz_number || 0,
            verse_text: verseText,
            errors_count: $('#errorsCount').val(),
            notes: $('#notesInput').val()
        }, function(response) {
            appendQuestion(response.question);
            currentVerseData = null;
            $('#pageDisplay').html('<div class="verse-placeholder"><i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>اضغط على "السؤال التالي" لعرض آية عشوائية</div>');
            $('#errorsCount').val(0);
            $('#notesInput').val('');
            toastr.success('تم إضافة النتيجة');
            $btn.prop('disabled', false).removeClass('btn-loading');
        }).fail(function(xhr) {
            console.error('addResult error:', xhr);
            const msg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'حدث خطأ';
            toastr.error(msg);
            $btn.prop('disabled', false).removeClass('btn-loading');
        });
    }

    function appendQuestion(q) {
        $('#noQuestionsMsg').addClass('d-none');
        $('#questionsList').append(buildQuestionItemHTML(q));
        questionCount++;
        totalErrorsCount += parseInt(q.errors_count) || 0;
        $('#questionsCount').text(questionCount);
        $('#totalErrors').text(totalErrorsCount);
    }

    function updateStatsOnReload() {
        questionCount = 0;
        totalErrorsCount = 0;
        $('#questionsList .question-item').each(function() {
            questionCount++;
            totalErrorsCount += parseInt($(this).data('errors') || 0);
        });
        $('#questionsCount').text(questionCount);
        $('#totalErrors').text(totalErrorsCount);
    }

    const _escapeDiv = document.createElement('div');
    function escapeHtml(text) {
        if (!text) return '';
        _escapeDiv.textContent = text;
        return _escapeDiv.innerHTML;
    }

    function buildQuestionItemHTML(q) {
        const ref = buildQuestionRef(q);
        const errorsLabel = q.errors_count === 0
            ? '<span class="zero-errors">✓ لا أخطاء</span>'
            : '<small class="text-muted">أخطاء: ' + q.errors_count + '</small>';
        return '<div class="list-group-item list-group-item-action question-item" data-id="' + q.id + '" data-surah="' + escapeHtml(q.surah?.name_ar || '') + '" data-ayah="' + q.ayah_number + '" data-page="' + q.page_number + '" data-errors="' + q.errors_count + '" data-notes="' + escapeHtml(q.notes || '') + '" data-text="' + escapeHtml(q.verse_text || '') + '" style="cursor:pointer;"><div class="d-flex justify-content-between align-items-start"><div><div class="fw-bold">' + ref + '</div>' + errorsLabel + (q.notes ? '<div class="small text-muted mt-1">' + truncateNotes(q.notes) + '</div>' : '') + '</div><span class="order-badge">#' + (q.order || 1) + '</span></div></div>';
    }

    function buildQuestionRef(q) {
        return q.surah?.name_ar + ' - آية ' + q.ayah_number + ' - صفحة ' + q.page_number;
    }

    function truncateNotes(notes) {
        if (!notes) return '';
        return escapeHtml(notes).substring(0, 50) + (notes.length > 50 ? '...' : '');
    }

    function renderQuestionsList(questions) {
        if (!questions || questions.length === 0) {
            $('#noQuestionsMsg').removeClass('d-none');
            questionCount = 0;
            totalErrorsCount = 0;
            $('#questionsCount').text('0');
            $('#totalErrors').text('0');
            return;
        }

        let html = '';
        questions.forEach(function(q) {
            html += buildQuestionItemHTML(q);
        });

        $('#questionsList').html(html);
        $('#noQuestionsMsg').addClass('d-none');
        updateStatsOnReload();
    }

    function enterReviewMode(question) {
        if ($('#finishExamBtn').prop('disabled')) return;
        isReviewMode = true;
        currentQuestionId = question.id;
        originalErrors = parseInt(question.errors_count) || 0;
        originalNotes = question.notes || '';
        $('#errorsCount').val(originalErrors);
        $('#notesInput').val(originalNotes);
        $('#addResultBtn').removeClass('btn-add-result').addClass('btn-warning-custom').text('حفظ التعديلات');
        $('#backToExamBtn').removeClass('d-none');
        $('#resultFormCard .result-header h5').html('<i class="fas fa-edit"></i>تعديل النتيجة');
        $('.question-item.active-review').removeClass('active-review');
        $('.question-item[data-id="' + question.id + '"]').addClass('active-review');
        toastr.info('وضعية المراجعة - عدّل وحفظ');
    }

    function exitReviewMode() {
        isReviewMode = false;
        currentQuestionId = null;
        $('#errorsCount').val(0);
        $('#notesInput').val('');
        $('#addResultBtn').removeClass('btn-warning-custom').addClass('btn-add-result').text('إضافة النتيجة');
        $('#backToExamBtn').addClass('d-none');
        $('#resultFormCard .result-header h5').html('<i class="fas fa-clipboard-check"></i>تسجيل النتيجة');
        $('.question-item.active-review').removeClass('active-review');
    }

    $('#addResultBtn').click(function() {
        if (isReviewMode && currentQuestionId) {
            updateQuestion();
        } else {
            addResult();
        }
    });

    function updateQuestion() {
        const $btn = $('#addResultBtn');
        $btn.prop('disabled', true).addClass('btn-loading');
        $.ajax({
            url: '/api/exam/questions/' + currentQuestionId,
            method: 'PUT',
            data: {
                _token: csrfToken,
                errors_count: $('#errorsCount').val(),
                notes: $('#notesInput').val()
            },
            success: function(response) {
                toastr.success('تم تحديث النتيجة');
                exitReviewMode();
                reloadQuestions();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'حدث خطأ';
                toastr.error(msg);
                $btn.prop('disabled', false).removeClass('btn-loading');
            }
        });
    }

    function reloadQuestions() {
        if (!examId) return;
        $.get('/api/exam/' + examId + '/questions', function(response) {
            renderQuestionsList(response.questions);
        });
    }

    function finishExam() {
        $.post('/api/exam/' + examId + '/finish', {
            _token: csrfToken
        }, function(response) {
            window.examFinished = true;
            toastr.success('تم إنهاء الاختبار بنجاح');
            $('#examStatusBadge').removeClass('status-progress').addClass('status-complete').text('مكتمل');
            $('#finishExamBtn, #cancelExamBtn, #nextQuestionBtn, #addResultBtn').prop('disabled', true);
            $('#finalQuestionsCount').text(questionCount);
            $('#finalErrorsCount').text(totalErrorsCount);
            $('#examCompleteOverlay').addClass('show');
        }).fail(function(xhr) {
            const msg = xhr.responseJSON?.error || 'حدث خطأ';
            toastr.error(msg);
        });
    }

    function cancelExam() {
        if (!examId) return;
        $.ajax({
            url: '/api/exam/' + examId,
            method: 'DELETE',
            data: { _token: csrfToken },
            success: function() {
                examId = null;
                location.reload();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'حدث خطأ';
                toastr.error(msg);
            }
        });
    }

    $(window).on('beforeunload', function() {
        if (examId && !window.examFinished) {
            return 'لديك اختبار قيد التنفيذ. هل أنت متأكد من المغادرة؟';
        }
    });

    $(document).on('keydown', function(e) {
        const tag = e.target.tagName;
        const isInput = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

        if (e.key === 'Escape' && isReviewMode) {
            const currentErrors = parseInt($('#errorsCount').val()) || 0;
            const currentNotes = $('#notesInput').val() || '';
            if (currentErrors !== originalErrors || currentNotes !== originalNotes) {
                if (!confirm('توجد تغييرات غير محفوظة. هل تريد المغادرة؟')) {
                    return;
                }
            }
            exitReviewMode();
        }

        if (isInput && e.key !== 'Escape') return;

        if (e.key === 'Enter') {
            const target = e.target;
            if (target.id === 'errorsCount' || target.id === 'notesInput') {
                e.preventDefault();
                if (isReviewMode && currentQuestionId) {
                    updateQuestion();
                } else if (!isReviewMode && currentVerseData) {
                    addResult();
                }
            }
        }

        if (e.key === 'n' || e.key === 'N') {
            if (!isInput && !isReviewMode) {
                generateQuestion();
            }
        }
    });
});
</script>
@endpush