@extends('layouts.app')

@section('title', 'مركز ترتيل - التسميع والحفظ والتجويد')
@section('page-title', 'مركز ترتيل')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="api-token" content="{{ session('api_token', '') }}">
<meta name="surahs-url" content="{{ route('api.student.tarteel.surahs') ?? '' }}">

@php
$user = Auth::user();
$token = $user->currentAccessToken();
$apiToken = $token ? $token->token : null;
if ($apiToken) {
    session(['api_token' => $apiToken]);
}
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

<div id="recitationAnnouncer" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>

<link rel="stylesheet" href="{{ asset('css/student/tarteel-center.css') }}?v={{ time() }}">

<div class="recitation-wrapper">
    <div class="tarteeel-tabs">
        <button class="tarteeel-tab active" data-tab="recitation">
            <i class="fas fa-microphone-alt"></i>
            <span>التلاوة</span>
        </button>
        <button class="tarteeel-tab" data-tab="memorization">
            <i class="fas fa-book"></i>
            <span>الحفظ</span>
        </button>
        <button class="tarteeel-tab" data-tab="tajweed">
            <i class="fas fa-book-open"></i>
            <span>التجويد</span>
        </button>
    </div>

    <div class="tarteeel-content active" id="recitation-tab">
        @include('student.live-recitation._recitation-tab')
    </div>

    <div class="tarteeel-content" id="memorization-tab">
        @include('student.live-recitation._memorization-tab')
    </div>

    <div class="tarteeel-content" id="tajweed-tab">
        @include('student.live-recitation._tajweed-tab')
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<style>
.tarteeel-tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid #e8e4dc;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.tarteeel-tab {
    padding: 0.875rem 1.5rem;
    border: none;
    background: transparent;
    color: #4a5568;
    font-weight: 500;
    font-size: 1rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 8px 8px 0 0;
}

.tarteeel-tab:hover {
    color: #1a7a4a;
    background: rgba(26, 122, 74, 0.05);
}

.tarteeel-tab.active {
    color: #1a7a4a;
    border-bottom-color: #1a7a4a;
    background: rgba(26, 122, 74, 0.08);
}

.tarteeel-tab i {
    font-size: 1.1rem;
}

.tarteeel-content {
    display: none;
}

.tarteeel-content.active {
    display: block;
}

.recitation-wrapper {
    background: linear-gradient(135deg, rgba(250, 248, 245, 0.97) 0%, rgba(249, 246, 242, 0.97) 100%);
    min-height: calc(100vh - 120px);
    padding: 1.5rem;
    border-radius: 16px;
}

@media (max-width: 768px) {
    .tarteeel-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    .tarteeel-tab {
        white-space: nowrap;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tarteeel-tab');
    const contents = document.querySelectorAll('.tarteeel-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.dataset.tab;

            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(targetId + '-tab').classList.add('active');
        });
    });
});
</script>
<script src="{{ asset('js/student/live-recitation.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/student/tarteel-memorization.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/student/tarteel-tajweed.js') }}?v={{ time() }}"></script>
@endpush
@endsection