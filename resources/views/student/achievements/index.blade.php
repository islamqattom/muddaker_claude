@extends('layouts.app')

@section('title', 'إنجازاتي')
@section('page-title', 'إنجازاتي')
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
<!-- Stats Summary -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card text-center p-3" style="background: linear-gradient(135deg, #FFD700, #FFA500); color: #fff;">
            <i class="fas fa-trophy fa-2x mb-2"></i>
            <h3 class="mb-0">{{ $earnedBadges->count() }}</h3>
            <small>إنجاز حصل عليه</small>
        </div>
    </div>
    <div class="col-6">
        <div class="card text-center p-3" style="background: linear-gradient(135deg, #6c757d, #495057); color: #fff;">
            <i class="fas fa-lock fa-2x mb-2"></i>
            <h3 class="mb-0">{{ $nextBadges->count() }}</h3>
            <small>إنجاز قيد التحقيق</small>
        </div>
    </div>
</div>

<!-- Earned Badges -->
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-award me-2 text-warning"></i>الإنجازات المحصل عليها</h5>
    </div>
    <div class="card-body">
        @if($earnedBadges->count() > 0)
            <div class="row g-3">
                @foreach($earnedBadges as $badge)
                    <div class="col-6 col-lg-4 col-xl-3">
                        <div class="card h-100 text-center border-0 shadow-sm">
                            <div class="card-body p-3">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 80px; height: 80px; background: {{ $badge->color }}20;">
                                    <i class="fas {{ $badge->icon }} fa-3x" style="color: {{ $badge->color }};"></i>
                                </div>
                                <h6 class="mb-1">{{ $badge->name }}</h6>
                                <p class="small text-muted mb-0">{{ $badge->description }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-award fa-4x opacity-25 mb-3"></i>
                <p class="mb-0">لم تحصل على أي إنجاز بعد</p>
                <small>استمر في الحفظ والمتابعة للحصول على الإنجازات!</small>
            </div>
        @endif
    </div>
</div>

<!-- Next Earnable Badges -->
@if($nextBadges->count() > 0)
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-clock me-2 text-info"></i>إنجازات قيد التحقيق</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الإنجاز</th>
                        <th>النوع</th>
                        <th>التقدم</th>
                        <th>المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nextBadges as $badge)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px; background: {{ $badge['color'] }}20;">
                                        <i class="fas {{ $badge['icon'] }}" style="color: {{ $badge['color'] }};"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $badge['name_ar'] }}</strong>
                                        <small class="d-block text-muted">{{ $badge['description_ar'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $badge['type'] }}</span>
                            </td>
                            <td style="min-width: 150px;">
                                <div class="progress mb-1" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ $badge['progress']['percent'] }}%; background: {{ $badge['color'] }};"></div>
                                </div>
                                <small class="text-muted">{{ $badge['progress']['current'] }} / {{ $badge['progress']['required'] }}</small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $badge['progress']['required'] - $badge['progress']['current'] }} متبقي
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
