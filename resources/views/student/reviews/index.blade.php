@extends('layouts.app')

@section('title', 'المراجعة')
@section('page-title', 'جدول المراجعة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.reviews-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #9b59b6;
}

.reviews-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.reviews-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.reviews-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.reviews-page .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--success) 0%, #2ecc71 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(39,174,96,0.35);
}

.reviews-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.reviews-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.reviews-page .stat-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    transition: all 0.3s ease;
}

.reviews-page .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }

.reviews-page .stat-card-body { padding: 20px; text-align: center; }

.reviews-page .stat-icon {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: 0 auto 12px;
}

.reviews-page .stat-icon.danger { background: linear-gradient(135deg, var(--danger), #e74c3c); color: white; }
.reviews-page .stat-icon.warning { background: linear-gradient(135deg, var(--warning), #e67e22); color: white; }
.reviews-page .stat-icon.success { background: linear-gradient(135deg, var(--success), #2ecc71); color: white; }
.reviews-page .stat-icon.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: var(--gold); }

.reviews-page .stat-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1; }
.reviews-page .stat-label { font-size: 11px; color: #6c7a89; font-weight: 500; margin-top: 4px; text-transform: uppercase; }

.reviews-page .due-card {
    background: linear-gradient(135deg, var(--danger) 0%, #e74c3c 100%);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.reviews-page .due-card::before {
    content: '';
    position: absolute;
    top: -30%; left: -10%;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.reviews-page .due-content { position: relative; z-index: 1; }
.reviews-page .due-title { color: white; font-size: 20px; font-weight: 700; margin-bottom: 8px; }
.reviews-page .due-subtitle { color: rgba(255,255,255,0.8); font-size: 14px; margin-bottom: 0; }

.reviews-page .btn-start {
    background: white;
    color: var(--danger);
    border: none;
    border-radius: 12px;
    padding: 12px 24px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.2s ease;
    display: flex; align-items: center; gap: 8px;
}

.reviews-page .btn-start:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }

.reviews-page .review-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.reviews-page .review-card.accent-danger { border-right: 4px solid var(--danger); }
.reviews-page .review-card.accent-warning { border-right: 4px solid var(--warning); }

.reviews-page .review-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.reviews-page .review-header.danger { background: linear-gradient(135deg, var(--danger), #e74c3c); }
.reviews-page .review-header.warning { background: linear-gradient(135deg, var(--warning), #e67e22); }
.reviews-page .review-header i { color: var(--gold); font-size: 20px; }
.reviews-page .review-header.danger i, .reviews-page .review-header.warning i { color: white; }
.reviews-page .review-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.reviews-page .review-table { width: 100%; border-collapse: collapse; margin: 0; }
.reviews-page .review-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.reviews-page .review-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.reviews-page .review-table tbody tr { transition: all 0.2s ease; }
.reviews-page .review-table tbody tr:hover { background: linear-gradient(90deg, rgba(243,156,18,0.04) 0%, rgba(24,43,72,0.02) 100%); }

.reviews-page .ease-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.reviews-page .delete-btn {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(192,57,43,0.2);
    background: white;
    color: var(--danger);
    transition: all 0.2s ease;
}

.reviews-page .delete-btn:hover { background: var(--danger); color: white; }

.reviews-page .empty-state { text-align: center; padding: 80px 20px; }
.reviews-page .empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.06), rgba(42,69,112,0.08));
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
}
.reviews-page .empty-icon i { font-size: 40px; color: var(--navy); opacity: 0.4; }

.reviews-page .btn-start-page {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex; align-items: center; gap: 8px;
}

.reviews-page .btn-start-page:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

@media (max-width: 992px) {
    .reviews-page .page-hero { padding: 20px 24px; }
    .reviews-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .reviews-page .hero-title { font-size: 18px; }
    .reviews-page .due-card { flex-direction: column; text-align: center; }
}
</style>
@endpush

@section('content')
<div class="reviews-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <h1 class="hero-title">جدول المراجعة</h1>
                <p class="hero-sub">مراجعة الحفظ باستخدام خوارزمية SM-2</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['due'] }}</div>
                    <div class="stat-label">مستحقة الآن</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">{{ $stats['upcoming'] }}</div>
                    <div class="stat-label">قادمة قريباً</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value">{{ $stats['future'] }}</div>
                    <div class="stat-label">مستقبلية</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon primary">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">الإجمالي</div>
                </div>
            </div>
        </div>
    </div>

    @if($stats['due'] > 0)
    <div class="due-card">
        <div class="row align-items-center">
            <div class="col">
                <div class="due-content">
                    <h5 class="due-title"><i class="fas fa-exclamation-circle me-2"></i>لديك {{ $stats['due'] }} مراجعة مستحقة</h5>
                    <p class="due-subtitle">ابدأ جلسة المراجعة الآن لتثبيت الحفظ</p>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('student.reviews.session') }}" class="btn-start">
                    <i class="fas fa-play"></i>ابدأ المراجعة
                </a>
            </div>
        </div>
    </div>
    @endif

    @if($overdueItems->count() > 0)
    <div class="review-card accent-danger">
        <div class="review-header danger">
            <i class="fas fa-exclamation-triangle"></i>
            <h5>مراجعات متأخرة ({{ $overdueItems->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="review-table">
                <thead>
                    <tr>
                        <th>السورة</th>
                        <th>الآيات</th>
                        <th>آخر مراجعة</th>
                        <th>التكرار</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueItems as $item)
                    <tr>
                        <td class="fw-semibold" style="color: var(--navy);">سورة {{ $item->surah }}</td>
                        <td><span class="verses-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">{{ $item->verse_start }} - {{ $item->verse_end }}</span></td>
                        <td><small class="text-muted">{{ $item->last_reviewed_at?->diffForHumans() ?? 'لم تراجع بعد' }}</small></td>
                        <td><span class="ease-badge" style="background: rgba(108,117,125,0.15); color: #6c757d;">{{ $item->repetitions }}</span></td>
                        <td>
                            <form action="{{ route('student.reviews.destroy', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" onclick="return confirm('هل أنت متأكد؟')">
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
    @endif

    @if($todayItems->count() > 0)
    <div class="review-card accent-warning">
        <div class="review-header warning">
            <i class="fas fa-clock"></i>
            <h5>مراجعات اليوم ({{ $todayItems->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="review-table">
                <thead>
                    <tr>
                        <th>السورة</th>
                        <th>الآيات</th>
                        <th>سهولة</th>
                        <th>التكرار</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayItems as $item)
                    <tr>
                        <td class="fw-semibold" style="color: var(--navy);">سورة {{ $item->surah }}</td>
                        <td><span class="verses-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">{{ $item->verse_start }} - {{ $item->verse_end }}</span></td>
                        <td>
                            @php
                                $easeColor = $item->ease_factor >= 2.5 ? 'success' : ($item->ease_factor >= 1.8 ? 'warning' : 'danger');
                            @endphp
                            <span class="ease-badge bg-{{ $easeColor }}" style="color: white;">{{ number_format($item->ease_factor, 1) }}</span>
                        </td>
                        <td><span class="ease-badge" style="background: rgba(108,117,125,0.15); color: #6c757d;">{{ $item->repetitions }}</span></td>
                        <td>
                            <form action="{{ route('student.reviews.destroy', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" onclick="return confirm('هل أنت متأكد؟')">
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
    @endif

    @if($upcomingItems->count() > 0)
    <div class="review-card">
        <div class="review-header">
            <i class="fas fa-calendar"></i>
            <h5>قادمة خلال يومين ({{ $upcomingItems->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="review-table">
                <thead>
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
                        <td class="fw-semibold" style="color: var(--navy);">سورة {{ $item->surah }}</td>
                        <td><span class="verses-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">{{ $item->verse_start }} - {{ $item->verse_end }}</span></td>
                        <td><small>{{ $item->next_review_date?->format('Y-m-d') }}</small></td>
                        <td><span class="ease-badge" style="background: rgba(108,117,125,0.15); color: #6c757d;">{{ $item->repetitions }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($futureItems->count() > 0)
    <div class="review-card">
        <div class="review-header">
            <i class="fas fa-calendar-alt"></i>
            <h5>مستقبلية ({{ $futureItems->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="review-table">
                <thead>
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
                        <td class="fw-semibold" style="color: var(--navy);">سورة {{ $item->surah }}</td>
                        <td><span class="verses-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">{{ $item->verse_start }} - {{ $item->verse_end }}</span></td>
                        <td><small>{{ $item->next_review_date?->format('Y-m-d') }}</small></td>
                        <td><span class="ease-badge" style="background: rgba(108,117,125,0.15); color: #6c757d;">{{ $item->repetitions }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($stats['total'] === 0)
    <div class="review-card">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h5 class="mb-2" style="color: var(--navy); font-weight: 700;">لا توجد مراجعات مجدولة</h5>
            <p class="text-muted mb-3">ستظهر هنا الآيات التي تحتاج مراجعة بناءً على خوارزمية SM-2</p>
            <a href="{{ route('student.sessions.index') }}" class="btn-start-page">
                <i class="fas fa-microphone me-1"></i>سجل تسميع جديد
            </a>
        </div>
    </div>
    @endif
</div>
@endsection