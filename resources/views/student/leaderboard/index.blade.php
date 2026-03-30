@extends('layouts.app')

@section('title', 'لوحة المتصدرين')
@section('page-title', 'لوحة المتصدرين')
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
    <a href="{{ route('student.reviews.index') }}" class="nav-link {{ request()->routeIs('student.reviews*') ? 'active' : '' }}">
        <i class="fas fa-clock"></i> المراجعة
    </a>
    <a href="{{ route('student.achievements') }}" class="nav-link {{ request()->routeIs('student.achievements*') ? 'active' : '' }}">
        <i class="fas fa-trophy"></i> إنجازاتي
    </a>
    <a href="{{ route('student.leaderboard.index') }}" class="nav-link {{ request()->routeIs('student.leaderboard*') ? 'active' : '' }}">
        <i class="fas fa-ranking-star"></i> المتصدرين
    </a>
@endsection

@section('content')
<!-- Period & Type Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">الفترة</label>
                <select name="period" class="form-select">
                    <option value="weekly" {{ $currentPeriod === 'weekly' ? 'selected' : '' }}>الأسبوع الحالي</option>
                    <option value="monthly" {{ $currentPeriod === 'monthly' ? 'selected' : '' }}>الشهر الحالي</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">النوع</label>
                <select name="type" class="form-select">
                    <option value="pages" {{ $currentType === 'pages' ? 'selected' : '' }}>الصفحات المحفوظة</option>
                    <option value="accuracy" {{ $currentType === 'accuracy' ? 'selected' : '' }}>معدل الدقة</option>
                    <option value="streak" {{ $currentType === 'streak' ? 'selected' : '' }}>السلسلة</option>
                    <option value="reviews" {{ $currentType === 'reviews' ? 'selected' : '' }}>المراجعات</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>تحديث
                </button>
            </div>
        </form>
    </div>
</div>

<!-- My Rank Card -->
@if($myRank)
<div class="card mb-4 border-primary">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-4 text-center">
                <div class="display-4 fw-bold text-primary">#{{ $myRank['rank'] }}</div>
                <small class="text-muted">ترتيبك</small>
            </div>
            <div class="col-8">
                <div class="d-flex justify-content-between mb-1">
                    <span>مجموع نقاطك</span>
                    <strong>{{ $myRank['score'] }}</strong>
                </div>
                <small class="text-muted">تصدر القائمة مع الاستمرار في التسميع</small>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Leaderboard Table -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-trophy me-2"></i>
            {{ App\Services\LeaderboardService::getPeriodLabel($currentPeriod) }} -
            {{ App\Services\LeaderboardService::getTypeLabel($currentType) }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>الطالب</th>
                        <th>المستوى</th>
                        <th class="text-center">النقاط</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaderboard as $entry)
                    @php $isMe = isset($entry['student_id']) && $entry['student_id'] === auth()->user()->student->id; @endphp
                    <tr class="{{ $isMe ? 'table-primary fw-bold' : '' }}">
                        <td class="text-center">
                            @if($entry['rank'] == 1)
                                <i class="fas fa-medal text-warning fa-lg"></i>
                            @elseif($entry['rank'] == 2)
                                <i class="fas fa-medal text-secondary fa-lg"></i>
                            @elseif($entry['rank'] == 3)
                                <i class="fas fa-medal text-warning fa-lg" style="color: #CD7F32 !important;"></i>
                            @else
                                {{ $entry['rank'] }}
                            @endif
                        </td>
                        <td>
                            @if($isMe)
                                <i class="fas fa-user-check text-primary me-1"></i>
                            @endif
                            {{ $entry['student_name'] }}
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $entry['level_name'] ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $entry['score'] }}</span>
                        </td>
                    </tr>
                    @endforeach

                    @if($leaderboard->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            لا توجد بيانات كافية للتصنيف
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Info -->
<div class="card mt-4 bg-light">
    <div class="card-body">
        <h6><i class="fas fa-info-circle me-2"></i>كيف تعمل لوحة المتصدرين؟</h6>
        <p class="small text-muted mb-0">
            يتم تحديث التصنيفات بشكل دوري. استمر في التسميع اليومي والمراجعة لتحسين ترتيبك.
            التقييم يشمل: الصفحات المحفوظة، معدل الدقة، سلسلة الأيام المتتالية، والمراجعات المكتملة.
        </p>
    </div>
</div>
@endsection
