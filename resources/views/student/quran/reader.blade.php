@extends('layouts.app')

@section('title', 'قراءة القرآن')
@section('page-title', 'قراءة القرآن الكريم')
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
    <a href="{{ route('student.attendance.index') }}" class="nav-link {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> سجل حضوري
    </a>
    <a href="{{ route('student.reports.index') }}" class="nav-link {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i> تقاريري
    </a>
    <span class="nav-section-title mt-2">التقدم</span>
    <a href="{{ route('student.progress') }}" class="nav-link {{ request()->routeIs('student.progress*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> تقدمي
    </a>
    <a href="{{ route('student.achievements') }}" class="nav-link {{ request()->routeIs('student.achievements*') ? 'active' : '' }}">
        <i class="fas fa-trophy"></i> إنجازاتي
    </a>
    <a href="{{ route('student.bookmarks') }}" class="nav-link {{ request()->routeIs('student.bookmarks*') ? 'active' : '' }}">
        <i class="fas fa-bookmark"></i> علاماتي
    </a>
    <span class="nav-section-title mt-2">الإعدادات</span>
    <a href="{{ route('student.profile.index') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
        <i class="fas fa-user-cog"></i> ملفي الشخصي
    </a>
@endsection

@section('content')
<!-- Navigation Header -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center g-2">
            <div class="col-auto">
                <a href="{{ route('student.quran.page', ['page' => max(1, $currentPage - 1)]) }}"
                   class="btn btn-outline-primary btn-sm {{ $currentPage <= 1 ? 'disabled' : '' }}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="col-auto">
                <span class="fw-bold">الصفحة {{ $currentPage }} من 604</span>
            </div>
            <div class="col-auto">
                <a href="{{ route('student.quran.page', ['page' => min(604, $currentPage + 1)]) }}"
                   class="btn btn-outline-primary btn-sm {{ $currentPage >= 604 ? 'disabled' : '' }}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </div>
            <div class="col">
                <select class="form-select form-select-sm" id="juzSelect" onchange="jumpToJuz(this.value)">
                    <option value="">-- اختر الجزء --</option>
                    @foreach($juzOptions as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="number" class="form-control form-control-sm" id="pageInput"
                       placeholder="صفحة" min="1" max="604" style="width: 80px;"
                       onkeypress="if(event.key==='Enter') jumpToPage()">
                <button class="btn btn-sm btn-outline-secondary" onclick="jumpToPage()">Go</button>
            </div>
        </div>
    </div>
</div>

<!-- Quran Page Display -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-book-quran me-2"></i>القرآن الكريم</h5>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="autoScrollToggle" checked>
            <label class="form-check-label" for="autoScrollToggle">تلقائي</label>
        </div>
    </div>
    <div class="card-body p-0" style="max-height: 60vh; overflow-y: auto;">
        @if(isset($pageData))
            @php
                $partPageRanges = [
                    1 => [1, 21], 2 => [22, 41], 3 => [42, 62], 4 => [63, 82], 5 => [83, 103],
                    6 => [104, 124], 7 => [125, 144], 8 => [145, 164], 9 => [165, 185], 10 => [186, 205],
                    11 => [206, 226], 12 => [227, 246], 13 => [247, 266], 14 => [267, 285], 15 => [286, 306],
                    16 => [307, 325], 17 => [326, 345], 18 => [346, 364], 19 => [365, 384], 20 => [385, 404],
                    21 => [405, 424], 22 => [425, 444], 23 => [445, 462], 24 => [463, 481], 25 => [482, 501],
                    26 => [502, 520], 27 => [521, 539], 28 => [540, 557], 29 => [558, 577], 30 => [578, 604],
                ];
                $currentJuz = 1;
                foreach ($partPageRanges as $juz => $range) {
                    if ($currentPage >= $range[0] && $currentPage <= $range[1]) {
                        $currentJuz = $juz;
                        break;
                    }
                }
            @endphp
            <div class="text-center p-3 bg-light border-bottom">
                <small class="text-muted">الجزء {{ $currentJuz }} - صفحة {{ $currentPage }}</small>
            </div>
            <div class="quran-text p-4" dir="rtl"
                 data-current-page="{{ $currentPage }}"
                 data-audio-urls="{{ json_encode($audioUrls ?? []) }}"
                 id="pageContainer">
                @foreach($pageData['ayahs'] as $index => $verse)
                    <span class="verse-element"
                          data-surah="{{ $verse['surah']['number'] }}"
                          data-verse="{{ $verse['numberInSurah'] }}"
                          id="verse-{{ $verse['number'] }}">
                        <sup class="verse-number mx-1 text-primary" style="font-size: 0.7em;">{{ $verse['numberInSurah'] }}</sup>
                        {{ $verse['text'] }}
                    </span>
                    @if($index === 0 && $verse['surah']['number'] !== 1 && $verse['numberInSurah'] === 1)
                        <span class="text-primary fw-bold">﷽</span>
                    @endif
                @endforeach
            </div>
        @else
            <div class="text-center p-5">
                <i class="fas fa-book-quran fa-4x text-muted mb-3"></i>
                <h5>اختر صفحة لعرض القرآن</h5>
                <p class="text-muted">استخدم أزرار التنقل أو أدخل رقم الصفحة</p>
            </div>
        @endif
    </div>
</div>

<!-- Audio Player -->
@if(isset($pageData))
    @include('student.partials.audio-player')
@endif

<!-- Bookmark Button -->
@if(isset($pageData))
    <div class="text-center mb-3">
        <button class="btn btn-outline-warning" onclick="openBookmarkModal({{ $currentPage }})">
            <i class="fas fa-bookmark me-1"></i> إضافة علامة
        </button>
    </div>
@endif
@endsection
