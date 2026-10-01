@extends('layouts.app')

@section('title', 'يوم الهمة - المشرف')
@section('page-title', 'يوم الهمة - إنجازات الطلاب')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
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
.yawm-page .stat-icon.navy { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white; }
.yawm-page .stat-icon.gold { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%); color: white; }
.yawm-page .stat-icon.danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); color: white; }
.yawm-page .stat-icon.warning { background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%); color: white; }

.yawm-page .filter-card .form-select {
    border: 2px solid #e4eaf2; border-radius: 10px; padding: 8px 12px; font-size: 13px;
}

.yawm-page .table-card .table { margin: 0; }
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
    background: #3498db; color: white; border: none; padding: 6px 12px;
    border-radius: 8px; font-weight: 600; font-size: 12px; text-decoration: none;
    display: inline-flex; align-items: center; gap: 4px;
}
.yawm-page .btn-view:hover { background: #2980b9; color: white; }
</style>
@endpush

@section('content')
<div class="yawm-page">
    <x-page-hero icon="fas fa-mosque">
        <h1 class="page-banner-title">يوم الهمة - إنجازات الطلاب</h1>
        <p class="page-banner-sub">تابع تدريبات السرد لطلابك وتقدمهم في كل جلسة</p>
    </x-page-hero>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon navy"><i class="fas fa-list"></i></div>
            <div>
                <div class="stat-label">إجمالي الجلسات</div>
                <div class="stat-number">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="stat-label">جلسات هذا الشهر</div>
                <div class="stat-number">{{ number_format($stats['this_month']) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="stat-label">إجمالي الأخطاء</div>
                <div class="stat-number">{{ number_format($stats['total_errors']) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-question-circle"></i></div>
            <div>
                <div class="stat-label">إجمالي الترددات</div>
                <div class="stat-number">{{ number_format($stats['total_hesitations']) }}</div>
            </div>
        </div>
    </div>

    @if($students->count() > 0)
    <div class="filter-card">
        <form method="GET" action="{{ route('supervisor.yawm-al-himma.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">تصفية حسب الطالب</label>
                <select name="student_id" class="form-select" onchange="this.form.submit()">
                    <option value="">جميع الطلاب</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ (string)$studentFilter === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->user->name ?? $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('supervisor.yawm-al-himma.index') }}" class="btn btn-outline-secondary w-100">
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
                        <th>اسم الطالب</th>
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
                            <a href="{{ route('supervisor.yawm-al-himma.show', $session) }}" class="btn-view">
                                <i class="fas fa-eye"></i> تفاصيل
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p class="mb-1">لا توجد جلسات مسجلة لطلابك بعد</p>
                                <small>سيظهر هنا أي تقييم يتم تسجيله عبر بوابة يوم الهمة لطلابك</small>
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
