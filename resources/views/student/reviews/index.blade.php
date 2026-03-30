@extends('layouts.app')

@section('title', 'المراجعة')
@section('page-title', 'جدول المراجعة')
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
    <a href="{{ route('student.bookmarks') }}" class="nav-link {{ request()->routeIs('student.bookmarks*') ? 'active' : '' }}">
        <i class="fas fa-bookmark"></i> علاماتي
    </a>
    <span class="nav-section-title mt-2">الإعدادات</span>
    <a href="{{ route('student.profile.index') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
        <i class="fas fa-user-cog"></i> ملفي الشخصي
    </a>
@endsection

@section('content')
<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3 border-danger">
            <div class="text-danger fw-bold" style="font-size: 1.5rem;">{{ $stats['due'] }}</div>
            <small class="text-muted">مستحقة الآن</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3 border-warning">
            <div class="text-warning fw-bold" style="font-size: 1.5rem;">{{ $stats['upcoming'] }}</div>
            <small class="text-muted">قادمة قريباً</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3 border-success">
            <div class="text-success fw-bold" style="font-size: 1.5rem;">{{ $stats['future'] }}</div>
            <small class="text-muted">مستقبلية</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-primary fw-bold" style="font-size: 1.5rem;">{{ $stats['total'] }}</div>
            <small class="text-muted">الإجمالي</small>
        </div>
    </div>
</div>

<!-- Start Review Session -->
@if($stats['due'] > 0)
<div class="card mb-4 border-danger">
    <div class="card-body py-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="fas fa-exclamation-circle text-danger me-2"></i>لديك {{ $stats['due'] }} مراجعة مستحقة</h5>
                <small class="text-muted">ابدأ جلسة المراجعة الآن لتثبيت الحفظ</small>
            </div>
            <a href="{{ route('student.reviews.session') }}" class="btn btn-danger btn-lg">
                <i class="fas fa-play me-2"></i>ابدأ المراجعة
            </a>
        </div>
    </div>
</div>
@endif

<!-- Overdue Reviews -->
@if($overdueItems->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>مراجعات متأخرة ({{ $overdueItems->count() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>السورة</th>
                        <th>الآيات</th>
                        <th>آخر مراجعة</th>
                        <th>التكرار</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueItems as $item)
                    <tr>
                        <td>سورة {{ $item->surah }}</td>
                        <td>{{ $item->verse_start }} - {{ $item->verse_end }}</td>
                        <td>{{ $item->last_reviewed_at?->diffForHumans() ?? 'لم تراجع بعد' }}</td>
                        <td><span class="badge bg-secondary">{{ $item->repetitions }}</span></td>
                        <td>
                            <form action="{{ route('student.reviews.destroy', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Due Today -->
@if($todayItems->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0"><i class="fas fa-clock me-2"></i>مراجعات اليوم ({{ $todayItems->count() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>السورة</th>
                        <th>الآيات</th>
                        <th>سهولة</th>
                        <th>التكرار</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayItems as $item)
                    <tr>
                        <td>سورة {{ $item->surah }}</td>
                        <td>{{ $item->verse_start }} - {{ $item->verse_end }}</td>
                        <td>
                            @php
                                $easeColor = $item->ease_factor >= 2.5 ? 'success' : ($item->ease_factor >= 1.8 ? 'warning' : 'danger');
                            @endphp
                            <span class="badge bg-{{ $easeColor }}">{{ number_format($item->ease_factor, 1) }}</span>
                        </td>
                        <td><span class="badge bg-secondary">{{ $item->repetitions }}</span></td>
                        <td>
                            <form action="{{ route('student.reviews.destroy', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Upcoming -->
@if($upcomingItems->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>قادمة خلال يومين ({{ $upcomingItems->count() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>السورة</th>
                        <th>الآيات</th>
                        <th>تاريخ المراجعة</th>
                        <th>التكرار</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingItems->take(10) as $item)
                    <tr>
                        <td>سورة {{ $item->surah }}</td>
                        <td>{{ $item->verse_start }} - {{ $item->verse_end }}</td>
                        <td>{{ $item->next_review_date?->format('Y-m-d') }}</td>
                        <td><span class="badge bg-secondary">{{ $item->repetitions }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Future -->
@if($futureItems->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>مستقبلية ({{ $futureItems->count() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>السورة</th>
                        <th>الآيات</th>
                        <th>تاريخ المراجعة</th>
                        <th>التكرار</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($futureItems->take(10) as $item)
                    <tr>
                        <td>سورة {{ $item->surah }}</td>
                        <td>{{ $item->verse_start }} - {{ $item->verse_end }}</td>
                        <td>{{ $item->next_review_date?->format('Y-m-d') }}</td>
                        <td><span class="badge bg-secondary">{{ $item->repetitions }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Empty State -->
@if($stats['total'] === 0)
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-clipboard-list fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="text-muted">لا توجد مراجعات مجدولة</h5>
        <p class="text-muted">ستظهر هنا الآيات التي تحتاج مراجعة بناءً على خوارزمية SM-2</p>
        <a href="{{ route('student.sessions.index') }}" class="btn btn-primary">
            <i class="fas fa-microphone me-2"></i>سجل تسميع جديد
        </a>
    </div>
</div>
@endif
@endsection
