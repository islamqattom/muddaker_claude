@extends('layouts.app')

@section('title', 'نتيجة التقييم')
@section('page-title', 'نتيجة تقييم النطق')
@section('role-label', 'طالب')

@section('sidebar-menu')
    <span class="nav-section-title">الرئيسية</span>
    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحتي الشخصية
    </a>
    <span class="nav-section-title mt-2">القرآن الكريم</span>
    <a href="{{ route('student.quran') }}" class="nav-link {{ request()->routeIs('student.quran*') ? 'active' : '' }}">
        <i class="fas fa-book-quran"></i> قراءة القرآن
    </a>
    <span class="nav-section-title mt-2">المتابعة</span>
    <a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
        <i class="fas fa-microphone"></i> جلسات التسميع
    </a>
    <span class="nav-section-title mt-2">التقدم</span>
    <a href="{{ route('student.progress') }}" class="nav-link {{ request()->routeIs('student.progress*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> تقدمي
    </a>
    <a href="{{ route('student.pronunciation.index') }}" class="nav-link {{ request()->routeIs('student.pronunciation*') ? 'active' : '' }}">
        <i class="fas fa-microphone-alt"></i> النطق
    </a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">نتيجة التقييم</h5>
        <a href="{{ route('student.pronunciation.practice') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>محاولة جديدة
        </a>
    </div>
    <div class="card-body">
        <!-- Verse Info -->
        <div class="text-center mb-4">
            <h6 class="text-muted mb-2">سورة {{ $attempt->surah }} - آيات {{ $attempt->verse_start }} : {{ $attempt->verse_end }}</h6>
            <div class="p-3 rounded bg-light" style="font-size: 1.5rem; line-height: 2.2;">
                {{ $verse['text'] ?? '' }}
            </div>
        </div>

        <!-- Status Check -->
        @if($attempt->status === 'processing')
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5>جارٍ معالجة التقييم...</h5>
                <p class="text-muted">يتم تحليل تلاوتك الآن</p>
            </div>
        @elseif($attempt->status === 'failed')
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <h5>فشل التقييم</h5>
                <p class="mb-0">{{ $attempt->error_message ?? 'حدث خطأ غير متوقع' }}</p>
            </div>
        @elseif($attempt->status === 'completed')
            <!-- Score Display -->
            <div class="text-center mb-4">
                <div class="display-1 fw-bold text-{{ $attempt->tajweed_score >= 85 ? 'success' : ($attempt->tajweed_score >= 70 ? 'warning' : 'danger') }}">
                    {{ $attempt->tajweed_score }}%
                </div>
                <div class="text-muted">
                    @if($attempt->tajweed_score >= 95)
                        ممتاز - تجويد صحيح
                    @elseif($attempt->tajweed_score >= 85)
                        جيد جداً
                    @elseif($attempt->tajweed_score >= 75)
                        جيد
                    @elseif($attempt->tajweed_score >= 60)
                        مقبول
                    @else
                        يحتاج تحسين
                    @endif
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="progress" style="height: 30px;">
                    <div class="progress-bar bg-{{ $attempt->tajweed_score >= 85 ? 'success' : ($attempt->tajweed_score >= 70 ? 'warning' : 'danger') }}"
                         style="width: {{ $attempt->tajweed_score }}%">
                    </div>
                </div>
            </div>

            <!-- Error Segments -->
            @if($attempt->error_segments && count($attempt->error_segments) > 0)
            <div class="card bg-light mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>الملاحظات</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @foreach($attempt->error_segments as $error)
                        <li class="mb-2">
                            @if($error['type'] === 'missing')
                                <span class="text-danger">كلمة مفقودة: {{ $error['expected'] }}</span>
                            @elseif($error['type'] === 'extra')
                                <span class="text-warning">كلمة إضافية: {{ $error['detected'] }}</span>
                            @elseif($error['type'] === 'mismatch')
                                <span class="text-danger">خطأ: {{ $error['expected'] }}</span>
                                @if($error['rule'])
                                    <span class="badge bg-warning me-1">{{ $error['rule'] }}</span>
                                @endif
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Audio Playback -->
            @if($attempt->audio_url)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-headphones me-2"></i>تلاوة الطالب</h6>
                </div>
                <div class="card-body">
                    <audio src="{{ asset('storage/' . $attempt->audio_url) }}" controls class="w-100"></audio>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('student.pronunciation.practice') }}" class="btn btn-primary">
                    <i class="fas fa-redo me-1"></i>حاول مرة أخرى
                </a>
                <a href="{{ route('student.quran.page', 1) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-book-quran me-1"></i>اذهب للقرآن
                </a>
            </div>
        @endif
    </div>
</div>

<div class="text-center">
    <a href="{{ route('student.pronunciation.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right me-1"></i>العودة للسجل
    </a>
</div>
@endsection
