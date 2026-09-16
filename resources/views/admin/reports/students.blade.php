@extends('layouts.app')

@section('title', 'تقارير الطالب')
@section('page-title', 'تقارير الطالب')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقارير الطالب</li>
@endsection

@section('content')
@include('admin.reports._styles')

@php
    $periodOptions = [
        'today'   => 'اليوم',
        'week'    => 'هذا الأسبوع',
        'month'   => 'هذا الشهر',
        'quarter' => 'هذا الربع',
        'half'    => 'هذا النصف',
        'year'    => 'هذه السنة',
        'all'     => 'كل الفترات',
        'custom'  => 'فترة مخصصة',
    ];
    $currentPeriod = request('period', $period ?? 'year');
@endphp

<div class="sp-hero">
    <div class="sp-hero-main">
        <div class="sp-hero-icon"><i class="fas fa-chart-line"></i></div>
        <div>
            <h4>تقارير الطالب</h4>
            <p>اختر الطالب والفترة لعرض التقرير المكتوب وتفاصيل أدائه</p>
        </div>
    </div>
    @if($selectedStudent)
        <div class="sp-hero-actions">
            <a href="{{ route('admin.reports.students', array_merge(request()->query(), ['export' => 'pdf'])) }}" target="_blank" class="rpt-btn rpt-btn-danger">
                <i class="fas fa-file-pdf"></i> تصدير PDF
            </a>
            <a href="{{ route('admin.students.show', $selectedStudent->id) }}" class="rpt-btn rpt-btn-ghost">
                <i class="fas fa-id-card"></i> ملف الطالب
            </a>
        </div>
    @endif
</div>

<div class="rpt-filter">
    <div class="rpt-filter-header">
        <i class="fas fa-filter"></i>
        <span>اختيار الطالب والفترة</span>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الطالب</label>
                    <select name="student_id" class="form-select" onchange="this.form.submit()">
                        <option value="">— اختر طالباً —</option>
                        @foreach($allStudents as $s)
                            <option value="{{ $s->id }}" {{ (string) request('student_id') === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->user->name }} — {{ $s->student_number }}@if($s->supervisor) — {{ $s->supervisor->name }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الفترة</label>
                    <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                        @foreach($periodOptions as $key => $label)
                            <option value="{{ $key }}" {{ $currentPeriod === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2" id="fromDateCol" style="{{ request('period', 'year') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate ? $fromDate->format('Y-m-d') : now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="col-md-2" id="toDateCol" style="{{ request('period', 'year') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate ? $toDate->format('Y-m-d') : now()->toDateString()) }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="rpt-btn rpt-btn-primary flex-fill"><i class="fas fa-search"></i> عرض التقرير</button>
                    @if($selectedStudent)
                        <a href="{{ route('admin.reports.students') }}" class="rpt-btn rpt-btn-ghost" title="إعادة تعيين"><i class="fas fa-redo"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@if(!$selectedStudent)
    <div class="rpt-chart-card">
        <div class="rpt-empty">
            <div class="rpt-empty-icon"><i class="fas fa-user-graduate"></i></div>
            <h5>لم يتم اختيار طالب</h5>
            <p>يرجى اختيار طالب من الفلتر بالأعلى لعرض تقريره.</p>
        </div>
    </div>
@else
    <div class="sp-student-banner">
        <div class="sp-student-avatar"><i class="fas fa-user-graduate"></i></div>
        <div class="sp-student-meta">
            <h5>{{ $selectedStudent->user->name }}</h5>
            <div class="sp-student-tags">
                <span><i class="fas fa-layer-group"></i> {{ $selectedStudent->level->name ?? '—' }}</span>
                <span><i class="fas fa-hashtag"></i> {{ $selectedStudent->student_number }}</span>
                @if($selectedStudent->supervisor)<span><i class="fas fa-user-tie"></i> {{ $selectedStudent->supervisor->name }}</span>@endif
                @if($selectedStudent->phone)<span><i class="fas fa-phone"></i> {{ $selectedStudent->phone }}</span>@endif
            </div>
        </div>
        <div class="sp-student-rates">
            <div class="sp-rate">
                <span class="sp-rate-value {{ $attStats['attendance_rate'] < 50 ? 'text-danger' : 'text-success' }}">{{ $attStats['attendance_rate'] }}%</span>
                <span class="sp-rate-label">نسبة الحضور</span>
            </div>
            <div class="sp-rate">
                <span class="sp-rate-value text-info">{{ $recStats['pass_rate'] }}%</span>
                <span class="sp-rate-label">نسبة النجاح</span>
            </div>
        </div>
    </div>

    <div class="rpt-kpi-grid">
        @include('reports._kpi', ['label' => 'جلسات التسميع', 'value' => $recStats['total'], 'icon' => 'fa-microphone', 'color' => '#182B48', 'bg' => 'rgba(24,43,72,0.10)', 'sub' => $periodLabel])
        @include('reports._kpi', ['label' => 'نسبة النجاح', 'value' => $recStats['pass_rate'] . '%', 'icon' => 'fa-check-circle', 'color' => '#27ae60', 'bg' => 'rgba(39,174,96,0.10)', 'sub' => $periodLabel])
        @include('reports._kpi', ['label' => 'نسبة الحضور', 'value' => $attStats['attendance_rate'] . '%', 'icon' => 'fa-calendar-check', 'color' => '#059669', 'bg' => 'rgba(5,150,105,0.10)', 'sub' => $periodLabel])
    </div>

    @include('reports._written_report', ['breakdown' => $breakdown, 'periodLabel' => $periodLabel, 'studentLabel' => 'طالب'])
@endif

<style>
.sp-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    padding: 24px 26px;
    border-radius: 18px;
    margin-bottom: 24px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    box-shadow: 0 12px 32px rgba(24,43,72,0.28);
    position: relative;
    overflow: hidden;
}
.sp-hero::after {
    content: '';
    position: absolute;
    left: -60px;
    top: -60px;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212,160,48,0.22), transparent 70%);
    pointer-events: none;
}
.sp-hero-main { display: flex; align-items: center; gap: 18px; position: relative; z-index: 1; }
.sp-hero-icon {
    width: 64px; height: 64px; flex-shrink: 0;
    border-radius: 16px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 28px;
    box-shadow: 0 8px 20px rgba(212,160,48,0.35);
}
.sp-hero h4 { color: #fff; font-size: 22px; font-weight: 700; margin: 0 0 4px; }
.sp-hero p { color: rgba(255,255,255,0.72); font-size: 13px; margin: 0; }
.sp-hero-actions { position: relative; z-index: 1; }

.sp-student-banner {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
    background: #fff;
    border-radius: 16px;
    padding: 18px 22px;
    margin-bottom: 24px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
}
.sp-student-avatar {
    width: 58px; height: 58px; border-radius: 15px; flex-shrink: 0;
    background: linear-gradient(135deg, #8e44ad, #7d3c98);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 24px;
}
.sp-student-meta { flex: 1; min-width: 180px; }
.sp-student-meta h5 { margin: 0 0 6px; color: var(--primary); font-weight: 700; font-size: 18px; }
.sp-student-tags { display: flex; flex-wrap: wrap; gap: 14px; color: #6c7a89; font-size: 13px; }
.sp-student-tags i { color: var(--gold); margin-inline-end: 4px; }
.sp-student-rates { display: flex; gap: 26px; }
.sp-rate { text-align: center; }
.sp-rate-value { display: block; font-size: 22px; font-weight: 800; }
.sp-rate-label { font-size: 12px; color: #94a3b8; }
.text-success { color: #27ae60 !important; }
.text-danger { color: #c0392b !important; }
.text-info { color: #3498db !important; }

@media (max-width: 768px) {
    .sp-hero { flex-direction: column; align-items: flex-start; }
    .sp-hero-actions { width: 100%; }
    .sp-hero-actions .rpt-btn { width: 100%; }
}
</style>

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    document.getElementById('fromDateCol').style.display = period === 'custom' ? '' : 'none';
    document.getElementById('toDateCol').style.display = period === 'custom' ? '' : 'none';
}
</script>
@endpush
@endsection
