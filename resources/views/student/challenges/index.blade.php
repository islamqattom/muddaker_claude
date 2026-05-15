@extends('layouts.app')

@section('title', 'التحديات')
@section('page-title', 'تحديات الحفظ')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.challenges-page {
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

.challenges-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.challenges-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.challenges-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.challenges-page .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--purple) 0%, #8e44ad 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(155,89,182,0.35);
}

.challenges-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.challenges-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.challenges-page .stat-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    transition: all 0.3s ease;
}

.challenges-page .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }

.challenges-page .stat-card-body { padding: 20px; text-align: center; }

.challenges-page .stat-icon {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: 0 auto 12px;
}

.challenges-page .stat-icon.success { background: linear-gradient(135deg, var(--success), #2ecc71); color: white; }
.challenges-page .stat-icon.danger { background: linear-gradient(135deg, var(--danger), #e74c3c); color: white; }
.challenges-page .stat-icon.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: var(--gold); }
.challenges-page .stat-icon.warning { background: linear-gradient(135deg, var(--warning), #e67e22); color: white; }

.challenges-page .stat-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1; }
.challenges-page .stat-label { font-size: 11px; color: #6c7a89; font-weight: 500; margin-top: 4px; text-transform: uppercase; }

.challenges-page .toolbar-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 16px 24px;
    margin-bottom: 24px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.challenges-page .toolbar-title { font-size: 16px; font-weight: 700; color: var(--navy); }

.challenges-page .btn-new {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
    display: flex; align-items: center; gap: 8px;
}

.challenges-page .btn-new:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

.challenges-page .challenge-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.challenges-page .challenge-card.accent-warning { border-right: 4px solid var(--warning); }
.challenges-page .challenge-card.accent-primary { border-right: 4px solid var(--navy); }

.challenges-page .challenge-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.challenges-page .challenge-header.warning { background: linear-gradient(135deg, var(--warning), #e67e22); }
.challenges-page .challenge-header i { color: var(--gold); font-size: 20px; }
.challenges-page .challenge-header.warning i { color: white; }
.challenges-page .challenge-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.challenges-page .challenges-table { width: 100%; border-collapse: collapse; margin: 0; }
.challenges-page .challenges-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.challenges-page .challenges-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.challenges-page .challenges-table tbody tr { transition: all 0.2s ease; }
.challenges-page .challenges-table tbody tr:hover { background: linear-gradient(90deg, rgba(155,89,182,0.04) 0%, rgba(24,43,72,0.02) 100%); }

.challenges-page .status-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.challenges-page .vs-badge {
    background: rgba(155,89,182,0.15);
    color: var(--purple);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.challenges-page .action-btn {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(24,43,72,0.1);
    background: white;
    color: var(--navy);
    transition: all 0.2s ease;
}

.challenges-page .action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(24,43,72,0.2); background: var(--navy); color: white; }

.challenges-page .accept-btn {
    background: linear-gradient(135deg, var(--success), #2ecc71);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.2s ease;
}

.challenges-page .accept-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(39,174,96,0.3); }

.challenges-page .decline-btn {
    background: white;
    color: var(--danger);
    border: 1px solid rgba(192,57,43,0.3);
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.2s ease;
}

.challenges-page .decline-btn:hover { background: var(--danger); color: white; }

.challenges-page .empty-state { text-align: center; padding: 80px 20px; }
.challenges-page .empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(155,89,182,0.15), rgba(142,68,173,0.15));
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
}
.challenges-page .empty-icon i { font-size: 40px; color: var(--purple); }

@media (max-width: 992px) {
    .challenges-page .page-hero { padding: 20px 24px; }
    .challenges-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .challenges-page .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="challenges-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-swords"></i>
            </div>
            <div>
                <h1 class="hero-title">تحدياتي</h1>
                <p class="hero-sub">تحديات الحفظ مع الزملاء</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon success">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-value">{{ $stats['won'] }}</div>
                    <div class="stat-label">تحديات فاز بها</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['lost'] }}</div>
                    <div class="stat-label">تحديات خسرها</div>
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
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-label">في الانتظار</div>
                </div>
            </div>
        </div>
    </div>

    <div class="toolbar-card">
        <div class="toolbar-title">تحدياتي</div>
        <a href="{{ route('student.challenges.create') }}" class="btn-new">
            <i class="fas fa-plus"></i>تحد جديد
        </a>
    </div>

    @if($pendingRequests->count() > 0)
    <div class="challenge-card accent-warning">
        <div class="challenge-header warning">
            <i class="fas fa-bell"></i>
            <h5>تحديات تنتظر قبولك ({{ $pendingRequests->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="challenges-table">
                <tbody>
                    @foreach($pendingRequests as $challenge)
                    <tr>
                        <td>
                            <div class="fw-semibold" style="color: var(--navy);">{{ $challenge->challenger->name }}</div>
                            <small class="text-muted">تحداك في سورة {{ $challenge->surah }}</small>
                        </td>
                        <td>
                            <small>تاريخ الهدف:</small>
                            <br>
                            <span class="fw-semibold">{{ $challenge->target_date->format('Y-m-d') }}</span>
                        </td>
                        <td>
                            <form action="{{ route('student.challenges.accept', $challenge) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="accept-btn">قبول</button>
                            </form>
                            <form action="{{ route('student.challenges.decline', $challenge) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="decline-btn">رفض</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($activeChallenges->count() > 0)
    <div class="challenge-card accent-primary">
        <div class="challenge-header">
            <i class="fas fa-swords"></i>
            <h5>تحديات نشطة</h5>
        </div>
        <div class="table-responsive">
            <table class="challenges-table">
                <thead>
                    <tr>
                        <th>المتسابقين</th>
                        <th>السورة</th>
                        <th>تاريخ الهدف</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeChallenges as $challenge)
                    <tr>
                        <td>
                            {{ $challenge->challenger->name }}
                            <span class="vs-badge">VS</span>
                            {{ $challenge->challenged->name }}
                        </td>
                        <td>
                            <span class="surah-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">
                                سورة {{ $challenge->surah }} ({{ $challenge->verse_start }}-{{ $challenge->verse_end }})
                            </span>
                        </td>
                        <td><small>{{ $challenge->target_date->format('Y-m-d') }}</small></td>
                        <td>
                            @if($challenge->status === 'accepted')
                                <span class="status-badge" style="background: rgba(39,174,96,0.15); color: var(--success);">مقبول</span>
                            @else
                                <span class="status-badge" style="background: rgba(243,156,18,0.15); color: var(--warning);">معلق</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('student.challenges.show', $challenge) }}" class="action-btn" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($history->count() > 0)
    <div class="challenge-card accent-primary">
        <div class="challenge-header">
            <i class="fas fa-history"></i>
            <h5>التحديات السابقة</h5>
        </div>
        <div class="table-responsive">
            <table class="challenges-table">
                <thead>
                    <tr>
                        <th>المتسابقين</th>
                        <th>السورة</th>
                        <th>النتيجة</th>
                        <th>الفائز</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $challenge)
                    <tr>
                        <td>
                            {{ $challenge->challenger->name }}
                            <span class="vs-badge">VS</span>
                            {{ $challenge->challenged->name }}
                        </td>
                        <td>سورة {{ $challenge->surah }}</td>
                        <td>
                            @if($challenge->status === 'completed')
                                <span class="status-badge" style="background: rgba(39,174,96,0.15); color: var(--success);">مكتمل</span>
                            @elseif($challenge->status === 'failed')
                                <span class="status-badge" style="background: rgba(192,57,43,0.15); color: var(--danger);">فشل</span>
                            @else
                                <span class="status-badge" style="background: rgba(108,117,125,0.15); color: #6c757d;">مرفوض</span>
                            @endif
                        </td>
                        <td>
                            @if($challenge->winner)
                                <span class="winner-badge" style="background: linear-gradient(135deg, var(--gold), var(--gold-light)); color: white; padding: 6px 14px; border-radius: 10px; font-size: 12px; font-weight: 600;">
                                    {{ $challenge->winner->name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($activeChallenges->isEmpty() && $pendingRequests->isEmpty() && $history->isEmpty())
    <div class="challenge-card">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-swords"></i>
            </div>
            <h5 class="mb-2" style="color: var(--navy); font-weight: 700;">لا توجد تحديات</h5>
            <p class="text-muted mb-3">ابدأ تحدياً جديداً مع زملائك!</p>
            <a href="{{ route('student.challenges.create') }}" class="btn-new">
                <i class="fas fa-plus me-1"></i>تحد جديد
            </a>
        </div>
    </div>
    @endif
</div>
@endsection