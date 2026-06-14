@extends('layouts.app')

@section('title', 'يوم الهمة')
@section('page-title', 'يوم الهمة - تدريبات السرد')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.yawm-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --radius: 16px;
    --radius-sm: 12px;
    --shadow-sm: 0 2px 10px rgba(24,43,72,0.05);
}

.yawm-page .hero-section {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    padding: 28px;
    margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.yawm-page .hero-section::before {
    content: ''; position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.yawm-page .hero-content { position: relative; z-index: 1; display: flex; align-items: center; gap: 16px; }
.yawm-page .hero-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: white;
    box-shadow: 0 6px 20px rgba(212,160,48,0.35);
    flex-shrink: 0;
}
.yawm-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.yawm-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.yawm-page .stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px; margin-bottom: 22px;
}
.yawm-page .stat-card {
    background: white; border-radius: var(--radius); padding: 18px 20px;
    box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 14px;
    border: 1px solid rgba(24,43,72,0.05);
}
.yawm-page .stat-icon {
    width: 50px; height: 50px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;
}
.yawm-page .stat-icon.navy { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white; }
.yawm-page .stat-icon.gold { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%); color: white; }
.yawm-page .stat-icon.danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); color: white; }
.yawm-page .stat-icon.warning { background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%); color: white; }
.yawm-page .stat-label { font-size: 12px; color: #6b7a90; margin-bottom: 4px; }
.yawm-page .stat-value { font-size: 22px; font-weight: 800; color: var(--navy); }

.yawm-page .table-card {
    background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.yawm-page .table-card .table { margin: 0; }
.yawm-page .table-card thead th {
    background: #f8f9fc; color: var(--navy); font-weight: 700; font-size: 12px;
    padding: 14px 16px; border-bottom: 2px solid #fef5e7; text-align: right;
}
.yawm-page .table-card tbody td {
    padding: 14px 16px; border-bottom: 1px solid #eef1f6; vertical-align: middle; font-size: 13px;
}
.yawm-page .table-card tbody tr:last-child td { border-bottom: none; }
.yawm-page .table-card tbody tr:hover { background: #fafbfd; }

.yawm-page .badge-soft {
    display: inline-block; padding: 4px 10px; border-radius: 8px;
    font-size: 12px; font-weight: 700;
}
.yawm-page .badge-soft.navy { background: var(--navy); color: white; }
.yawm-page .badge-soft.gold { background: #fef5e7; color: var(--navy); }
.yawm-page .badge-soft.danger { background: #fde8e6; color: #c0392b; }
.yawm-page .badge-soft.warning { background: #fef5e7; color: #b36b00; }

.yawm-page .btn-view {
    background: #3498db; color: white; border: none; padding: 6px 14px;
    border-radius: 8px; font-weight: 600; font-size: 12px; text-decoration: none;
    display: inline-flex; align-items: center; gap: 4px;
}
.yawm-page .btn-view:hover { background: #2980b9; color: white; }

.yawm-page .empty-state { text-align: center; padding: 50px 20px; color: #6b7a90; }
.yawm-page .empty-state i { font-size: 50px; margin-bottom: 14px; opacity: 0.3; }
</style>
@endpush

@section('content')
<div class="yawm-page">
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-icon"><i class="fas fa-mosque"></i></div>
            <div>
                <h1 class="hero-title">يوم الهمة</h1>
                <p class="hero-sub">تابع تدريبات السرد وتقدمك في كل جلسة</p>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon navy"><i class="fas fa-list"></i></div>
            <div>
                <div class="stat-label">إجمالي الجلسات</div>
                <div class="stat-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="stat-label">جلسات هذا الشهر</div>
                <div class="stat-value">{{ number_format($stats['this_month']) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="stat-label">إجمالي الأخطاء</div>
                <div class="stat-value">{{ number_format($stats['total_errors']) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-question-circle"></i></div>
            <div>
                <div class="stat-label">إجمالي الترددات</div>
                <div class="stat-value">{{ number_format($stats['total_hesitations']) }}</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>المشرف</th>
                        <th>الأجزاء</th>
                        <th>الأخطاء</th>
                        <th>الترددات</th>
                        <th style="width: 90px;">عرض</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td><span class="badge-soft gold">#{{ $session->id }}</span></td>
                        <td>{{ $session->session_date->format('Y-m-d') }}</td>
                        <td><strong>{{ $session->supervisor_name }}</strong></td>
                        <td>
                            @foreach($session->parts as $p)
                                <span class="badge-soft navy" style="margin: 1px;">{{ $p }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge-soft danger">
                                <i class="fas fa-times-circle"></i> {{ $session->total_errors }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-soft warning">
                                <i class="fas fa-question-circle"></i> {{ $session->total_hesitations }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('student.yawm-al-himma.show', $session) }}" class="btn-view">
                                <i class="fas fa-eye"></i> تفاصيل
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p class="mb-1">لا توجد جلسات مسجلة بعد</p>
                                <small>سيظهر هنا أي تقييم يتم تسجيله عبر بوابة يوم الهمة</small>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $sessions->links() }}</div>
</div>
@endsection
