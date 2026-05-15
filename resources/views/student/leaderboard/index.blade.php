@extends('layouts.app')

@section('title', 'لوحة المتصدرين')
@section('page-title', 'لوحة المتصدرين')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.leaderboard-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #9b59b6;
}

.leaderboard-page .page-hero {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.leaderboard-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.leaderboard-page .hero-icon {
    width: 56px; height: 56px;
    background: rgba(255,255,255,0.25);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    backdrop-filter: blur(5px);
}

.leaderboard-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.leaderboard-page .hero-sub { color: rgba(255,255,255,0.85); font-size: 13px; margin: 0; }

.leaderboard-page .filter-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 20px 24px;
    margin-bottom: 24px;
}

.leaderboard-page .filter-form { display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }

.leaderboard-page .filter-group { flex: 1; min-width: 150px; }
.leaderboard-page .filter-label { font-weight: 600; color: var(--navy); margin-bottom: 8px; font-size: 13px; display: block; }

.leaderboard-page .form-select {
    border: 2px solid rgba(24,43,72,0.1);
    border-radius: 10px;
    padding: 10px 14px;
    width: 100%;
    transition: all 0.2s ease;
}

.leaderboard-page .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
}

.leaderboard-page .btn-filter {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: flex; align-items: center; gap: 8px;
}

.leaderboard-page .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

.leaderboard-page .rank-card {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.leaderboard-page .rank-card::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(212,160,48,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.leaderboard-page .rank-position {
    font-size: 64px;
    font-weight: 800;
    color: var(--gold);
    line-height: 1;
}

.leaderboard-page .rank-label { color: rgba(255,255,255,0.7); font-size: 14px; margin-top: 4px; }

.leaderboard-page .rank-info { position: relative; z-index: 1; }
.leaderboard-page .rank-score-label { color: rgba(255,255,255,0.7); font-size: 12px; margin-bottom: 4px; }
.leaderboard-page .rank-score-value { color: white; font-size: 28px; font-weight: 700; }

.leaderboard-page .leaderboard-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.leaderboard-page .leaderboard-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.leaderboard-page .leaderboard-header i { color: var(--gold); font-size: 20px; }
.leaderboard-page .leaderboard-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.leaderboard-page .leaderboard-table { width: 100%; border-collapse: collapse; margin: 0; }
.leaderboard-page .leaderboard-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.leaderboard-page .leaderboard-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
    text-align: center;
}
.leaderboard-page .leaderboard-table tbody tr { transition: all 0.2s ease; }
.leaderboard-page .leaderboard-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }
.leaderboard-page .leaderboard-table tbody tr.me-row { background: rgba(212,160,48,0.1); font-weight: 700; }

.leaderboard-page .medal-icon {
    font-size: 22px;
}

.leaderboard-page .me-badge {
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.leaderboard-page .score-badge {
    background: rgba(24,43,72,0.06);
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    color: var(--navy);
}

.leaderboard-page .info-card {
    background: linear-gradient(135deg, rgba(52,152,219,0.08), rgba(41,128,185,0.08));
    border: 2px solid rgba(52,152,219,0.15);
    border-radius: 16px;
    padding: 20px;
}

.leaderboard-page .info-card h6 { color: var(--navy); font-weight: 700; margin-bottom: 8px; }
.leaderboard-page .info-card p { font-size: 13px; color: #6c7a89; margin-bottom: 0; }

@media (max-width: 992px) {
    .leaderboard-page .page-hero { padding: 20px 24px; }
    .leaderboard-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .leaderboard-page .hero-title { font-size: 18px; }
    .leaderboard-page .filter-form { flex-direction: column; }
    .leaderboard-page .filter-group { min-width: 100%; }
}
</style>
@endpush

@section('content')
<div class="leaderboard-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div>
                <h1 class="hero-title">لوحة المتصدرين</h1>
                <p class="hero-sub">تصنيف الطلاب حسب الأداء</p>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label class="filter-label">الفترة</label>
                <select name="period" class="form-select">
                    <option value="weekly" {{ $currentPeriod === 'weekly' ? 'selected' : '' }}>الأسبوع الحالي</option>
                    <option value="monthly" {{ $currentPeriod === 'monthly' ? 'selected' : '' }}>الشهر الحالي</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">النوع</label>
                <select name="type" class="form-select">
                    <option value="pages" {{ $currentType === 'pages' ? 'selected' : '' }}>الصفحات المحفوظة</option>
                    <option value="accuracy" {{ $currentType === 'accuracy' ? 'selected' : '' }}>معدل الدقة</option>
                    <option value="streak" {{ $currentType === 'streak' ? 'selected' : '' }}>السلسلة</option>
                    <option value="reviews" {{ $currentType === 'reviews' ? 'selected' : '' }}>المراجعات</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i>تحديث
            </button>
        </form>
    </div>

    @if($myRank)
    <div class="rank-card">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="rank-position">#{{ $myRank['rank'] }}</div>
                <div class="rank-label">ترتيبك</div>
            </div>
            <div class="col text-start">
                <div class="rank-info">
                    <div class="rank-score-label">مجموع نقاطك</div>
                    <div class="rank-score-value">{{ $myRank['score'] }} نقطة</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="leaderboard-card">
        <div class="leaderboard-header">
            <i class="fas fa-trophy"></i>
            <h5>{{ App\Services\LeaderboardService::getPeriodLabel($currentPeriod) }} - {{ App\Services\LeaderboardService::getTypeLabel($currentType) }}</h5>
        </div>
        <div class="table-responsive">
            <table class="leaderboard-table">
                <thead>
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
                    <tr class="{{ $isMe ? 'me-row' : '' }}">
                        <td class="text-center">
                            @if($entry['rank'] == 1)
                                <i class="fas fa-medal medal-icon" style="color: #FFD700;"></i>
                            @elseif($entry['rank'] == 2)
                                <i class="fas fa-medal medal-icon" style="color: #C0C0C0;"></i>
                            @elseif($entry['rank'] == 3)
                                <i class="fas fa-medal medal-icon" style="color: #CD7F32;"></i>
                            @else
                                <span class="rank-num" style="font-weight: 700; color: var(--navy);">{{ $entry['rank'] }}</span>
                            @endif
                        </td>
                        <td>
                            @if($isMe)
                                <i class="fas fa-user-check me-1" style="color: var(--gold);"></i>
                            @endif
                            {{ $entry['student_name'] }}
                        </td>
                        <td>
                            <span class="level-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">
                                {{ $entry['level_name'] ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="score-badge">{{ $entry['score'] }}</span>
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

    <div class="info-card mt-4">
        <h6><i class="fas fa-info-circle me-2"></i>كيف تعمل لوحة المتصدرين؟</h6>
        <p>
            يتم تحديث التصنيفات بشكل دوري. استمر في التسميع اليومي والمراجعة لتحسين ترتيبك.
            التقييم يشمل: الصفحات المحفوظة، معدل الدقة، سلسلة الأيام المتتالية، والمراجعات المكتملة.
        </p>
    </div>
</div>
@endsection