@extends('layouts.app')

@section('title', 'تسميع القرآن - التسميع والحفظ والتجويد')
@section('page-title', 'تسميع القرآن')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')

<x-page-hero icon="fas fa-microphone">
    <h1 class="page-banner-title">تسميع القرآن</h1>
</x-page-hero>
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
$user = Auth::user();
// Clean up old page tokens (keep max 5)
$tokenCount = $user->tokens()->where('name', 'like', 'live-recitation-page-%')->count();
if ($tokenCount >= 5) {
    $user->tokens()
        ->where('name', 'like', 'live-recitation-page-%')
        ->orderBy('created_at', 'asc')
        ->limit($tokenCount - 4)
        ->delete();
}
// Create a fresh Sanctum token for the page (currentAccessToken returns hashed token which is unusable)
$tokenResult = $user->createToken('live-recitation-page-' . time());
$apiToken = $tokenResult->plainTextToken;
@endphp

@if($apiToken)
<meta name="api-token" content="{{ $apiToken }}">
@endif

<meta name="token-url" content="{{ route('student.live-recitation.token') }}">
<meta name="preview-url" content="{{ route('api.live-recitation.preview') }}">
<meta name="start-url" content="{{ route('api.live-recitation.start') }}">
<meta name="error-url" content="{{ route('api.live-recitation.error') }}">
<meta name="progress-url" content="{{ route('api.live-recitation.progress') }}">
<meta name="report-url-template" content="{{ route('api.live-recitation.report', ['id' => '__ID__']) }}">

<meta name="memorization-plan-url" content="{{ route('api.student.tarteel.memorization-plan') }}">
<meta name="memorization-stats-url" content="{{ route('api.student.tarteel.memorization-stats') }}">
<meta name="daily-goal-url" content="{{ route('api.student.tarteel.daily-goal') }}">
<meta name="tajweed-rules-url" content="{{ route('api.student.tarteel.tajweed-rules') }}">
<meta name="tajweed-example-url" content="{{ route('api.student.tarteel.tajweed-example', ['id' => '__ID__']) }}">

<div id="recitationAnnouncer" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>

<script>
    window.SURAH_INFO = @json(\App\Services\QuranApiService::SURAH_INFO);
</script>

<link rel="stylesheet" href="{{ asset('css/student/tarteel-center.css') }}?v={{ time() }}">

<div class="tarteel-center">
    <div class="container-fluid">
        <div class="row g-0">
            <div class="col-12">
                <ul class="nav nav-tabs tartteel-nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-recitation" data-bs-toggle="tab" data-bs-target="#panel-recitation" type="button" role="tab">
                            <i class="fas fa-microphone-alt me-2"></i>التلاوة
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-memorization" data-bs-toggle="tab" data-bs-target="#panel-memorization" type="button" role="tab">
                            <i class="fas fa-book me-2"></i>الحفظ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-tajweed" data-bs-toggle="tab" data-bs-target="#panel-tajweed" type="button" role="tab">
                            <i class="fas fa-book-open me-2"></i>التجويد
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="panel-recitation" role="tabpanel">
                <div class="tarteel-panel">
                    @include('student.live-recitation._recitation-tab')
                </div>
            </div>
            <div class="tab-pane fade" id="panel-memorization" role="tabpanel">
                <div class="tarteel-panel">
                    @include('student.live-recitation._memorization-tab')
                </div>
            </div>
            <div class="tab-pane fade" id="panel-tajweed" role="tabpanel">
                <div class="tarteel-panel">
                    @include('student.live-recitation._tajweed-tab')
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 start-50 translate-middle-x mb-4 z-index-1050" id="toastContainer"></div>

<style>
.toast-container .toast-error,
.toast-container .toast-success {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    animation: slideUp 0.3s ease;
    font-weight: 500;
}

.toast-container .toast-error {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.toast-container .toast-success {
    background: linear-gradient(135deg, #1a7a4a, #2d9d63);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<style>
.tarteel-center {
    background: linear-gradient(135deg, #faf9f7 0%, #f5f3f0 100%);
    min-height: calc(100vh - 140px);
    padding: 0;
    border-radius: 0;
}

.tartteel-nav-tabs {
    background: #fff;
    border-bottom: 2px solid #e8e4dc;
    padding: 1rem 1.5rem 0;
    gap: 0.5rem;
    flex-wrap: nowrap;
    overflow-x: auto;
}

.tartteel-nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    border-radius: 8px 8px 0 0;
    padding: 0.875rem 1.5rem;
    color: #6c757d;
    font-weight: 500;
    white-space: nowrap;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.tartteel-nav-tabs .nav-link:hover {
    color: #1a7a4a;
    background: rgba(26, 122, 74, 0.05);
}

.tartteel-nav-tabs .nav-link.active {
    color: #1a7a4a;
    background: rgba(26, 122, 74, 0.08);
    border-bottom-color: #1a7a4a;
}

.tartteel-nav-tabs .nav-link i {
    font-size: 1.1rem;
}

.tarteel-panel {
    padding: 1.5rem;
    background: transparent;
}

@media (max-width: 768px) {
    .tartteel-nav-tabs {
        padding: 0.75rem 1rem 0;
    }
    .tartteel-nav-tabs .nav-link {
        padding: 0.625rem 1rem;
        font-size: 0.9rem;
    }
    .tarteel-panel {
        padding: 1rem;
    }
}
</style>

@push('scripts')
<script src="{{ asset('js/student/live-recitation.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/student/tarteel-memorization.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/student/tarteel-tajweed.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/student/smart-memorization.js') }}?v={{ time() }}"></script>
@endpush
@endsection