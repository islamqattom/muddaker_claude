@extends('layouts.app')

@section('title', 'يوم الهمة')
@section('page-title', 'يوم الهمة - الأيام')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.yawm-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --gold-soft: #fef5e7;
    --danger: #e74c3c;
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
.yawm-page .hero-content { position: relative; z-index: 1; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
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
.yawm-page .hero-actions { margin-right: auto; display: flex; gap: 10px; }
.yawm-page .btn-gold {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    color: white; border: none; padding: 9px 18px; border-radius: 10px;
    font-weight: 700; font-size: 13px; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 14px rgba(212,160,48,0.35);
}
.yawm-page .btn-gold:hover { color: white; transform: translateY(-1px); }

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

.yawm-page .days-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}
.yawm-page .day-card {
    background: white; border-radius: var(--radius); padding: 22px;
    box-shadow: var(--shadow-sm); border: 1px solid rgba(24,43,72,0.05);
    text-decoration: none; color: inherit; transition: all 0.2s;
    position: relative; overflow: hidden;
}
.yawm-page .day-card:hover {
    transform: translateY(-3px); box-shadow: 0 8px 24px rgba(24,43,72,0.12); color: inherit;
}
.yawm-page .day-card::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
}
.yawm-page .day-date {
    font-size: 16px; font-weight: 800; color: var(--navy); margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.yawm-page .day-date i { color: var(--gold); }
.yawm-page .day-stats {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px;
}
.yawm-page .day-stat {
    background: #f8f9fc; border-radius: var(--radius-sm); padding: 12px 8px;
    text-align: center; border: 1px solid #eef1f6;
}
.yawm-page .day-stat-icon {
    width: 34px; height: 34px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center; font-size: 14px; color: white;
    margin-bottom: 6px;
}
.yawm-page .day-stat-icon.navy { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); }
.yawm-page .day-stat-icon.danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); }
.yawm-page .day-stat-icon.warning { background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%); }
.yawm-page .day-stat-value { font-size: 18px; font-weight: 800; color: var(--navy); }
.yawm-page .day-stat-label { font-size: 11px; color: #6b7a90; }
.yawm-page .day-arrow {
    text-align: left; color: var(--gold); font-size: 14px; font-weight: 700;
}
.yawm-page .day-arrow i { margin-right: 4px; }

.yawm-page .empty-state {
    text-align: center; padding: 60px 20px; color: #6b7a90;
    background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
}
.yawm-page .empty-state i { font-size: 60px; margin-bottom: 18px; opacity: 0.3; }
</style>
@endpush

@section('content')
<div class="yawm-page">
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-icon"><i class="fas fa-mosque"></i></div>
            <div>
                <h1 class="hero-title">يوم الهمة - الأيام</h1>
                <p class="hero-sub">اختر يوم الهمة لعرض جلسات السرد الخاصة به</p>
            </div>
            <div class="hero-actions">
                <a href="{{ url('/guest/yawm-al-himma') }}" target="_blank" class="btn-gold">
                    <i class="fas fa-external-link-alt"></i> فتح بوابة التسجيل
                </a>
            </div>
        </div>
    </div>

    @if($days->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p class="mb-1">لا توجد أيام مسجلة</p>
            <small>سيظهر هنا أي يوم يتم تسجيل جلسات فيه عبر بوابة يوم الهمة</small>
        </div>
    @else
        <div class="days-grid">
            @foreach($days as $day)
            <a href="{{ route('admin.yawm-al-himma.show', $day->day) }}" class="day-card">
                <div class="day-stats">
                    <div class="day-stat">
                        <div class="day-stat-icon navy"><i class="fas fa-list"></i></div>
                        <div class="day-stat-info">
                            <div class="day-stat-value">{{ number_format($day->sessions_count) }}</div>
                            <div class="day-stat-label">جلسة</div>
                        </div>
                    </div>
                    <div class="day-stat">
                        <div class="day-stat-icon danger"><i class="fas fa-times-circle"></i></div>
                        <div class="day-stat-info">
                            <div class="day-stat-value">{{ number_format($day->total_errors) }}</div>
                            <div class="day-stat-label">خطأ</div>
                        </div>
                    </div>
                    <div class="day-stat">
                        <div class="day-stat-icon warning"><i class="fas fa-question-circle"></i></div>
                        <div class="day-stat-info">
                            <div class="day-stat-value">{{ number_format($day->total_hesitations) }}</div>
                            <div class="day-stat-label">تردد</div>
                        </div>
                    </div>
                </div>
                <div class="day-date">
                    <i class="fas fa-calendar-day"></i>
                    {{ \Carbon\Carbon::parse($day->day)->translatedFormat('l، Y-m-d') }}
                </div>
                <div class="day-arrow">
                    <i class="fas fa-eye"></i> عرض الجلسات
                </div>
            </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
