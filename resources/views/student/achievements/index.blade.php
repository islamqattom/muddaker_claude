@extends('layouts.app')

@section('title', 'إنجازاتي')
@section('page-title', 'إنجازاتي')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.achievements-page {
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

.achievements-page .page-hero {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.achievements-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.achievements-page .hero-icon {
    width: 48px; height: 48px;
    background: rgba(255,255,255,0.25);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    backdrop-filter: blur(5px);
    flex-shrink: 0;
}

.achievements-page .hero-title { color: white; font-size: 18px; font-weight: 700; margin-bottom: 4px; }
.achievements-page .hero-sub { color: rgba(255,255,255,0.85); font-size: 12.5px; margin: 0; }
.achievements-page .hero-value { color: white; font-size: 34px; font-weight: 800; line-height: 1; }

.achievements-page .stats-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.achievements-page .stats-card-body {
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.achievements-page .stats-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.achievements-page .stats-icon.gold { background: linear-gradient(135deg, var(--gold), var(--gold-light)); color: white; }
.achievements-page .stats-icon.gray { background: linear-gradient(135deg, #6c757d, #495057); color: white; }

.achievements-page .stats-label { font-size: 12px; color: #6c7a89; margin-bottom: 4px; font-weight: 500; }
.achievements-page .stats-value { font-size: 22px; font-weight: 800; color: var(--navy); line-height: 1; }

.achievements-page .badge-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 16px;
}

.achievements-page .badge-card-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
    display: flex; align-items: center; gap: 10px;
}

.achievements-page .badge-card-header.gold-header { background: linear-gradient(135deg, var(--gold), var(--gold-light)); }
.achievements-page .badge-card-header i { color: white; font-size: 16px; }
.achievements-page .badge-card-header h5 { color: white; font-weight: 700; margin: 0; font-size: 14px; }

.achievements-page .badge-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
    padding: 16px;
}

.achievements-page .badge-item {
    text-align: center;
    padding: 16px 14px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.06));
    border-radius: 13px;
    transition: all 0.3s ease;
}

.achievements-page .badge-item:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }

.achievements-page .badge-icon {
    width: 66px; height: 66px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
    margin: 0 auto 14px;
}

.achievements-page .badge-name { font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 8px; }
.achievements-page .badge-desc { font-size: 11px; color: #6c7a89; margin-bottom: 0; }

.achievements-page .progress-table-wrap { border-radius: 13px; overflow: hidden; border: 1px solid rgba(24,43,72,0.06); }

.achievements-page .progress-table {
    width: 100%; border-collapse: collapse; margin: 0;
}

.achievements-page .progress-table thead {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: #fff;
}

.achievements-page .progress-table th {
    padding: 11px 14px;
    font-size: 11px; font-weight: 700;
    text-align: center;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.achievements-page .progress-table tbody tr { transition: all 0.2s ease; }
.achievements-page .progress-table tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%); }
.achievements-page .progress-table tbody tr:last-child td { border-bottom: none; }

.achievements-page .progress-table td {
    padding: 11px 14px;
    font-size: 13px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}

.achievements-page .badge-icon-sm {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
}

.achievements-page .badge-mini-name { font-weight: 700; color: var(--navy); }
.achievements-page .badge-mini-desc { font-size: 11px; color: #6c7a89; }

.achievements-page .progress-bar {
    height: 8px;
    border-radius: 4px;
    background: rgba(24,43,72,0.1);
    overflow: hidden;
}

.achievements-page .progress-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.4s ease;
}

.achievements-page .empty-state { text-align: center; padding: 40px 16px; }
.achievements-page .empty-icon {
    width: 66px; height: 66px;
    background: linear-gradient(135deg, rgba(212,160,48,0.15), rgba(212,160,48,0.2));
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
}
.achievements-page .empty-icon i { font-size: 26px; color: var(--gold); }

@media (max-width: 992px) {
    .achievements-page .page-hero { padding: 14px 16px; }
    .achievements-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .achievements-page .hero-title { font-size: 18px; }
    .achievements-page .badge-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')
<div class="achievements-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div>
                <h1 class="hero-title">إنجازاتي</h1>
                <p class="hero-sub">عرض إنجازاتك والشهادات التي حققتها</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-icon gold">
                        <i class="fas fa-award"></i>
                    </div>
                    <div>
                        <div class="stats-label">إنجاز حصل عليه</div>
                        <div class="stats-value">{{ $earnedBadges->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-icon gray">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stats-label">إنجاز قيد التحقيق</div>
                        <div class="stats-value">{{ $nextBadges->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="badge-card">
        <div class="badge-card-header gold-header">
            <i class="fas fa-award"></i>
            <h5>الإنجازات المحصلة عليها</h5>
        </div>
        @if($earnedBadges->count() > 0)
            <div class="badge-grid">
                @foreach($earnedBadges as $badge)
                    <div class="badge-item">
                        <div class="badge-icon" style="background: {{ $badge->color }}20; color: {{ $badge->color }};">
                            <i class="fas {{ $badge->icon }}"></i>
                        </div>
                        <div class="badge-name">{{ $badge->name }}</div>
                        <div class="badge-desc">{{ $badge->description }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h5 class="text-muted mb-2">لم تحصل على أي إنجاز بعد</h5>
                <p class="text-muted small mb-0">استمر في الحفظ والمتابعة للحصول على الإنجازات!</p>
            </div>
        @endif
    </div>

    @if($nextBadges->count() > 0)
    <div class="badge-card">
        <div class="badge-card-header">
            <i class="fas fa-clock"></i>
            <h5>إنجازات قيد التحقيق</h5>
        </div>
        <div class="table-responsive">
            <div class="progress-table-wrap">
                <table class="progress-table">
                    <thead>
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
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="badge-icon-sm" style="background: {{ $badge['color'] }}20; color: {{ $badge['color'] }};">
                                            <i class="fas {{ $badge['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="badge-mini-name">{{ $badge['name_ar'] }}</div>
                                            <div class="badge-mini-desc">{{ $badge['description_ar'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="type-badge" style="background: rgba(108,117,125,0.15); color: #6c757d; padding: 3px 10px; border-radius: 7px; font-size: 11.5px; font-weight: 600;">
                                        {{ $badge['type'] }}
                                    </span>
                                </td>
                                <td style="min-width: 150px;">
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill" style="width: {{ $badge['progress']['percent'] }}%; background: {{ $badge['color'] }};"></div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">{{ $badge['progress']['current'] }} / {{ $badge['progress']['required'] }}</small>
                                </td>
                                <td>
                                    <span class="remaining-badge" style="background: rgba(243,156,18,0.15); color: var(--warning); padding: 3px 10px; border-radius: 7px; font-size: 11.5px; font-weight: 600;">
                                        {{ $badge['progress']['required'] - $badge['progress']['current'] }} متبقي
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection