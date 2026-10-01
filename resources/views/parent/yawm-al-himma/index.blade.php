@extends('layouts.app')

@section('title', 'يوم الهمة')
@section('page-title', 'يوم الهمة - متابعة الأبناء')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection

@push('styles')
<style>
.yawm-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --radius: 14px;
    --radius-sm: 10px;
    --shadow-sm: 0 2px 10px rgba(24,43,72,0.05);
}
.yawm-page .hero-section {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative; overflow: hidden;
}
.yawm-page .hero-section::before {
    content: ''; position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.yawm-page .hero-content { position: relative; z-index: 1; display: flex; align-items: center; gap: 16px; }
.yawm-page .hero-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 6px 20px rgba(212,160,48,0.35);
    flex-shrink: 0;
}
.yawm-page .hero-title { color: white; font-size: 18px; font-weight: 700; margin: 0 0 4px 0; }
.yawm-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 12.5px; margin: 0; }

.yawm-page .stat-card {
    background: white; border-radius: 13px; padding: 14px 16px;
    box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 12px;
    border: 1px solid rgba(24,43,72,0.05);
}
.yawm-page .stat-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
}
.yawm-page .stat-icon.navy { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white; }
.yawm-page .stat-icon.gold { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%); color: white; }
.yawm-page .stat-icon.danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); color: white; }
.yawm-page .stat-icon.warning { background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%); color: white; }
.yawm-page .stat-label { font-size: 12px; color: #6b7a90; margin-bottom: 4px; }
.yawm-page .stat-value { font-size: 22px; font-weight: 800; color: var(--navy); }

.yawm-page .filter-card .form-select {
    border: 2px solid #e4eaf2; border-radius: 10px; padding: 8px 12px; font-size: 13px;
}

.yawm-page .table-card thead th {
    background: #f8f9fc; color: var(--navy); font-weight: 700; font-size: 11px;
    padding: 11px 14px; border-bottom: 2px solid #fef5e7; text-align: right;
}
.yawm-page .table-card tbody td {
    padding: 11px 14px; border-bottom: 1px solid #eef1f6; vertical-align: middle; font-size: 13px;
}
.yawm-page .table-card tbody tr:last-child td { border-bottom: none; }
.yawm-page .table-card tbody tr:hover { background: #fafbfd; }

.yawm-page .badge-soft {
    display: inline-block; padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 700;
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

.yawm-page .empty-state { text-align: center; padding: 40px 16px; color: #6b7a90; }
.yawm-page .empty-state i { font-size: 26px; margin-bottom: 14px; opacity: 0.3; }
</style>
@endpush

@section('content')
<div class="yawm-page">
    <x-page-hero icon="fas fa-mosque">
        <h1 class="page-banner-title">يوم الهمة - متابعة الأبناء</h1>
        <p class="page-banner-sub">تابع تدريبات السرد لأبنائك في يوم الهمة</p>
    </x-page-hero>

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

    @if($children->count() > 1)
    <div class="filter-card">
        <form method="GET" action="{{ route('parent.yawm-al-himma.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">تصفية حسب الطالب</label>
                <select name="student_id" class="form-select" onchange="this.form.submit()">
                    <option value="">جميع الأبناء</option>
                    @foreach($children as $child)
                        <option value="{{ $child->id }}" {{ request('student_id') == $child->id ? 'selected' : '' }}>
                            {{ $child->user->name ?? $child->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('parent.yawm-al-himma.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-redo"></i> إعادة
                </a>
            </div>
        </form>
    </div>
    @endif

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>الطالب</th>
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
                        <td><strong>{{ $session->student_name }}</strong></td>
                        <td>{{ $session->supervisor_name }}</td>
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
                            <a href="{{ route('parent.yawm-al-himma.show', $session) }}" class="btn-view">
                                <i class="fas fa-eye"></i> تفاصيل
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p class="mb-1">لا توجد جلسات مسجلة لأبنائك</p>
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
