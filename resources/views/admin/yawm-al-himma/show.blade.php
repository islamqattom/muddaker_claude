@extends('layouts.app')

@section('title', 'يوم الهمة - تفاصيل اليوم')
@section('page-title', 'تفاصيل يوم الهمة')

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
.yawm-page .hero-actions { margin-right: auto; }
.yawm-page .btn-ghost {
    background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25);
    padding: 8px 16px; border-radius: 10px; font-weight: 600; font-size: 13px;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    backdrop-filter: blur(4px);
}
.yawm-page .btn-ghost:hover { background: rgba(255,255,255,0.25); color: white; }

.yawm-page .filter-card {
    background: white; border-radius: var(--radius); padding: 18px 20px;
    box-shadow: var(--shadow-sm); margin-bottom: 18px;
}
.yawm-page .filter-card .form-control {
    border: 2px solid #e4eaf2; border-radius: 10px; padding: 8px 12px; font-size: 13px;
}

.yawm-page .table-card {
    background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.yawm-page .table-card .table { margin: 0; }
.yawm-page .table-card thead th {
    background: #f8f9fc; color: var(--navy); font-weight: 700; font-size: 12px;
    padding: 14px 16px; border-bottom: 2px solid var(--gold-soft); text-align: right;
    text-transform: uppercase; letter-spacing: 0.3px;
}
.yawm-page .table-card tbody td {
    padding: 14px 16px; border-bottom: 1px solid #eef1f6; vertical-align: middle;
    font-size: 13px;
}
.yawm-page .table-card tbody tr:last-child td { border-bottom: none; }
.yawm-page .table-card tbody tr:hover { background: #fafbfd; }

.yawm-page .badge-soft {
    display: inline-block; padding: 4px 10px; border-radius: 8px;
    font-size: 12px; font-weight: 700;
}
.yawm-page .badge-soft.navy { background: var(--navy); color: white; }
.yawm-page .badge-soft.gold { background: var(--gold-soft); color: var(--navy); }
.yawm-page .badge-soft.danger { background: #fde8e6; color: var(--danger); }
.yawm-page .badge-soft.warning { background: #fef5e7; color: #b36b00; }

.yawm-page .action-btn {
    width: 32px; height: 32px; border-radius: 8px; border: none; color: white;
    display: inline-flex; align-items: center; justify-content: center;
    margin: 0 2px; transition: all 0.15s; font-size: 12px;
}
.yawm-page .action-btn.view { background: #3498db; }
.yawm-page .action-btn.delete { background: var(--danger); }
.yawm-page .action-btn:hover { transform: translateY(-1px); }

.yawm-page .day-summary {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px; margin-bottom: 22px;
}
.yawm-page .day-summary-item {
    background: white; border-radius: var(--radius); padding: 18px;
    box-shadow: var(--shadow-sm); text-align: center; border: 1px solid rgba(24,43,72,0.05);
}
.yawm-page .day-summary-item .icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center; font-size: 18px; color: white;
    margin-bottom: 8px;
}
.yawm-page .day-summary-item .icon.navy { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); }
.yawm-page .day-summary-item .icon.gold { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%); }
.yawm-page .day-summary-item .icon.danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); }
.yawm-page .day-summary-item .icon.warning { background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%); }
.yawm-page .day-summary-item .num { font-size: 24px; font-weight: 800; color: var(--navy); }
.yawm-page .day-summary-item .lbl { font-size: 12px; color: #6b7a90; }
.yawm-page .day-summary-item.danger .num { color: var(--danger); }
.yawm-page .day-summary-item.warning .num { color: #b36b00; }

.yawm-page .empty-state {
    text-align: center; padding: 50px 20px; color: #6b7a90;
}
.yawm-page .empty-state i { font-size: 50px; margin-bottom: 14px; opacity: 0.3; }

.yawm-page .chart-card {
    background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    padding: 22px; margin-bottom: 22px;
}
.yawm-page .chart-card .chart-header {
    display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
}
.yawm-page .chart-card .chart-header i {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 16px;
}
.yawm-page .chart-card .chart-title { font-size: 16px; font-weight: 700; color: var(--navy); margin: 0; }
.yawm-page .chart-card .chart-subtitle { font-size: 12px; color: #6b7a90; margin: 0; }
.yawm-page .chart-card .chart-container { position: relative; height: 420px; }
.yawm-page .chart-card .chart-actions {
    display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px;
}
.yawm-page .chart-card .chart-actions .btn {
    font-size: 12px; padding: 6px 12px; border-radius: 8px; font-weight: 600;
}
.yawm-page .chart-card .chart-tabs {
    display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid #eef1f6; padding-bottom: 10px;
}
.yawm-page .chart-card .chart-tabs .chart-tab {
    border: 1px solid #e4eaf2; background: #f8f9fc; color: #6b7a90;
    padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 12px;
    display: inline-flex; align-items: center; gap: 5px; cursor: pointer;
}
.yawm-page .chart-card .chart-tabs .chart-tab.active, .yawm-page .chart-card .chart-tabs .chart-tab:hover {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    color: white; border-color: var(--gold);
}
.yawm-page .chart-card .chart-view { display: none; }
.yawm-page .chart-card .chart-view.active { display: block; }
.yawm-page .precision-card {
    background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    padding: 22px; margin-bottom: 22px;
}
.yawm-page .precision-card .precision-title {
    font-size: 16px; font-weight: 700; color: var(--navy); margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.yawm-page .precision-card .precision-title i { color: #27ae60; }
.yawm-page .precision-card .precision-list { list-style: none; padding: 0; margin: 0; }
.yawm-page .precision-card .precision-item {
    display: flex; align-items: center; gap: 12px; padding: 10px 0;
    border-bottom: 1px solid #eef1f6;
}
.yawm-page .precision-card .precision-item:last-child { border-bottom: none; }
.yawm-page .precision-card .precision-rank {
    width: 28px; height: 28px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 12px; color: white; flex-shrink: 0;
    background: #27ae60;
}
.yawm-page .precision-card .precision-name { flex: 1; font-weight: 600; color: var(--navy); }
.yawm-page .precision-card .precision-meta {
    display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end;
}
.yawm-page .precision-card .precision-meta span {
    padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;
}
.yawm-page .precision-card .precision-meta .parts { background: var(--gold-soft); color: var(--navy); }
.yawm-page .precision-card .precision-meta .errors { background: #fde8e6; color: var(--danger); }
.yawm-page .precision-card .precision-meta .hesitations { background: #fef5e7; color: #b36b00; }
.yawm-page .precision-card .precision-score {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white; padding: 4px 10px; border-radius: 8px;
    font-weight: 700; font-size: 12px; min-width: 70px; text-align: center;
}
.yawm-page .precision-card .precision-score.per-part {
    background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
}
.yawm-page .precision-info {
    background: linear-gradient(135deg, #e8f8f0 0%, #d5f5e3 100%);
    border: 1px solid rgba(39, 174, 96, 0.15);
    border-radius: var(--radius-sm);
    padding: 14px 16px;
    margin-bottom: 16px;
    color: #1e8449;
    font-size: 13px;
}
.yawm-page .precision-info.blue {
    background: linear-gradient(135deg, #eaf2f8 0%, #d6eaf8 100%);
    border-color: rgba(41, 128, 185, 0.15);
    color: #2471a3;
}
.yawm-page .precision-info .info-title {
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}
.yawm-page .precision-info .formula {
    background: rgba(255,255,255,0.6);
    border-radius: 8px;
    padding: 10px 12px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 8px;
    direction: ltr;
    display: inline-block;
}
.yawm-page .precision-info ul {
    margin: 0;
    padding-right: 18px;
}
.yawm-page .precision-info li {
    margin-bottom: 4px;
}
.yawm-page .grouped-precision-select {
    background: white; border: 1px solid #e4eaf2; border-radius: 10px;
    padding: 10px 14px; font-weight: 600; color: var(--navy); font-size: 13px;
    margin-bottom: 16px; width: 100%; max-width: 320px;
}
.yawm-page .grouped-precision-empty {
    text-align: center; color: #6b7a90; padding: 30px;
    background: #f8f9fc; border-radius: var(--radius-sm);
}
.yawm-page .leaderboard-card {
    background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    padding: 22px; margin-bottom: 22px;
}
.yawm-page .leaderboard-card .leaderboard-title {
    font-size: 16px; font-weight: 700; color: var(--navy); margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.yawm-page .leaderboard-card .leaderboard-title i { color: var(--gold); }
.yawm-page .leaderboard-card .leaderboard-list { list-style: none; padding: 0; margin: 0; }
.yawm-page .leaderboard-card .leaderboard-item {
    display: flex; align-items: center; gap: 12px; padding: 10px 0;
    border-bottom: 1px solid #eef1f6;
}
.yawm-page .leaderboard-card .leaderboard-item:last-child { border-bottom: none; }
.yawm-page .leaderboard-card .rank {
    width: 28px; height: 28px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 12px; color: white; flex-shrink: 0;
}
.yawm-page .leaderboard-card .rank.gold { background: linear-gradient(135deg, #d4a030 0%, #e6b84d 100%); }
.yawm-page .leaderboard-card .rank.silver { background: linear-gradient(135deg, #95a5a6 0%, #bdc3c7 100%); }
.yawm-page .leaderboard-card .rank.bronze { background: linear-gradient(135deg, #cd7f32 0%, #d35400 100%); }
.yawm-page .leaderboard-card .rank.other { background: #e4eaf2; color: #6b7a90; }
.yawm-page .leaderboard-card .student-name { flex: 1; font-weight: 600; color: var(--navy); }
.yawm-page .leaderboard-card .parts-count {
    background: var(--gold-soft); color: var(--navy); padding: 4px 10px;
    border-radius: 8px; font-weight: 700; font-size: 12px;
}
.yawm-page .section-toggle {
    background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    padding: 14px 18px; margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap;
}
.yawm-page .section-toggle .btn-toggle {
    border: 1px solid #e4eaf2; background: #f8f9fc; color: #6b7a90;
    padding: 8px 16px; border-radius: 10px; font-weight: 600; font-size: 13px;
    display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s;
}
.yawm-page .section-toggle .btn-toggle.active, .yawm-page .section-toggle .btn-toggle:hover {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white; border-color: var(--navy);
}
.yawm-page .tab-content-section { display: none; }
.yawm-page .tab-content-section.active { display: block; }
</style>
@endpush

@section('content')
<div class="yawm-page">
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-icon"><i class="fas fa-mosque"></i></div>
            <div>
                <h1 class="hero-title">جلسات يوم الهمة</h1>
                <p class="hero-sub">{{ $day->translatedFormat('l، Y-m-d') }} • {{ $sessions->total() }} جلسة</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('admin.yawm-al-himma.index') }}" class="btn-ghost">
                    <i class="fas fa-arrow-right"></i> العودة للأيام
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="section-toggle">
        <button type="button" class="btn-toggle active" data-tab="tab-table">
            <i class="fas fa-table"></i> قائمة الجلسات
        </button>
        <button type="button" class="btn-toggle" data-tab="tab-chart">
            <i class="fas fa-chart-line"></i> التنافسية والرسم البياني
        </button>
    </div>

    <div id="tab-table" class="tab-content-section active">

    <div class="day-summary">
        <div class="day-summary-item">
            <div class="icon navy"><i class="fas fa-list"></i></div>
            <div class="num">{{ number_format($dayStats['sessions_count']) }}</div>
            <div class="lbl">جلسة</div>
        </div>
        <div class="day-summary-item danger">
            <div class="icon danger"><i class="fas fa-times-circle"></i></div>
            <div class="num">{{ number_format($dayStats['total_errors']) }}</div>
            <div class="lbl">خطأ</div>
        </div>
        <div class="day-summary-item warning">
            <div class="icon warning"><i class="fas fa-question-circle"></i></div>
            <div class="num">{{ number_format($dayStats['total_hesitations']) }}</div>
            <div class="lbl">تردد</div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('admin.yawm-al-himma.show', $day->format('Y-m-d')) }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">اسم المشرف</label>
                <input type="text" name="supervisor_name" value="{{ request('supervisor_name') }}" class="form-control" placeholder="بحث بالمشرف">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">اسم الطالب</label>
                <input type="text" name="student_name" value="{{ request('student_name') }}" class="form-control" placeholder="بحث بالطالب">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('admin.yawm-al-himma.show', $day->format('Y-m-d')) }}" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المشرف</th>
                        <th>الطالب</th>
                        <th>الأجزاء</th>
                        <th>الأخطاء</th>
                        <th>الترددات</th>
                        <th style="width: 110px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td><span class="badge-soft gold">#{{ $session->id }}</span></td>
                        <td><strong>{{ $session->supervisor_name }}</strong></td>
                        <td>
                            <div>{{ $session->student_name }}</div>
                            @if($session->student && $session->student->user)
                                <small class="text-muted">{{ $session->student->student_number ?? '' }}</small>
                            @endif
                        </td>
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
                            <a href="#" class="action-btn view" title="عرض" data-bs-toggle="modal" data-bs-target="#sessionModal{{ $session->id }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.yawm-al-himma.destroy', $session) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn delete" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p class="mb-1">لا توجد جلسات في هذا اليوم</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $sessions->links() }}
    </div>

    </div> <!-- /tab-table -->

    <div id="tab-chart" class="tab-content-section">
        <div class="chart-card">
            <div class="chart-header">
                <i class="fas fa-trophy"></i>
                <div>
                    <p class="chart-title">المنافسة بين الطلاب - عدد الأجزاء المسردة</p>
                    <p class="chart-subtitle">اختر "الأجزاء" لترتيب العدد، أو "الأكثر تقنًا" لمن سرد أكثر بأقل أخطاء، أو "التقدم" لمتابعة كل طالب عبر الأجزاء.</p>
                </div>
            </div>
            <div class="chart-tabs">
                <button type="button" class="chart-tab active" data-chart="barChart"><i class="fas fa-chart-bar"></i> الأجزاء (الترتيب)</button>
                <button type="button" class="chart-tab" data-chart="precisionChart"><i class="fas fa-bullseye"></i> الأكثر تقنًا (المطلق)</button>
                <button type="button" class="chart-tab" data-chart="groupedPrecisionChart"><i class="fas fa-users"></i> مقارنة حسب عدد الأجزاء</button>
                <button type="button" class="chart-tab" data-chart="lineChart"><i class="fas fa-chart-line"></i> التقدم</button>
            </div>
            <div class="chart-view active" id="barChartView">
                <div class="chart-container">
                    <canvas id="competitionBarChart"></canvas>
                </div>
            </div>
            <div class="chart-view" id="precisionChartView">
                <div class="chart-container">
                    <canvas id="precisionBarChart"></canvas>
                </div>
            </div>
            <div class="chart-view" id="groupedPrecisionChartView">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">اختر عدد الأجزاء للمقارنة</label>
                    <select id="groupedPrecisionSelect" class="grouped-precision-select">
                        @forelse($groupedPrecisionCharts as $partsCount => $group)
                            <option value="{{ $partsCount }}">{{ $partsCount }} أجزاء ({{ count($group['students']) }} طالب)</option>
                        @empty
                            <option value="" disabled selected>لا توجد مجموعات للمقارنة</option>
                        @endforelse
                    </select>
                </div>
                <div id="groupedPrecisionEmpty" class="grouped-precision-empty" style="display: none;">
                    <i class="fas fa-users-slash fa-2x mb-2"></i><br>
                    لا يوجد طلاب كافيين بنفس عدد الأجزاء للمقارنة
                </div>
                <div class="chart-container">
                    <canvas id="groupedPrecisionBarChart"></canvas>
                </div>
            </div>
            <div class="chart-view" id="lineChartView">
                <div class="chart-container">
                    <canvas id="competitionChart"></canvas>
                </div>
            </div>
            <div class="chart-actions">
                <button type="button" class="btn btn-outline-secondary" id="toggleAllLines"><i class="fas fa-eye-slash"></i> إخفاء/إظهار الكل</button>
                <button type="button" class="btn btn-outline-secondary" id="downloadChart"><i class="fas fa-download"></i> تحميل الصورة</button>
            </div>
        </div>

        <div class="leaderboard-card">
            <div class="leaderboard-title"><i class="fas fa-medal"></i> لوحة المتصدرين - أكثر الطلاب سردًا للأجزاء</div>
            @if($leaderboard->isEmpty())
                <div class="text-muted text-center py-3">لا توجد بيانات متاحة</div>
            @else
                <ul class="leaderboard-list">
                    @foreach($leaderboard as $idx => $row)
                        @php
                            $rankClass = match($idx) {
                                0 => 'gold',
                                1 => 'silver',
                                2 => 'bronze',
                                default => 'other',
                            };
                        @endphp
                        <li class="leaderboard-item">
                            <span class="rank {{ $rankClass }}">{{ $idx + 1 }}</span>
                            <span class="student-name">{{ $row['name'] }}</span>
                            <span class="parts-count">{{ $row['parts'] }} جزء</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="precision-card">
            <div class="precision-title"><i class="fas fa-bullseye"></i> لوحة الأكثر تقنًا - أقل أخطاء وترددات</div>

            <div class="precision-info">
                <div class="info-title"><i class="fas fa-info-circle"></i> كيف يتم حساب درجة التقن لكل جزء؟</div>
                <div class="formula">
                    (10 نقاط لكل جزء) + مكافأة جودة الجزء + مكافأة طلاقة الجزء
                </div>
                <ul>
                    <li>كل جزء مسرد يضيف <strong>10 نقاط</strong>.</li>
                    <li>مكافأة جودة الجزء: إذا كان معدل الأخطاء للجزء 0 تحصل على <strong>+10 نقاط</strong> للجزء، وتقل تدريجيًا حتى تصبح 0 عند معدل 10 أخطاء فأكثر للجزء.</li>
                    <li>مكافأة طلاقة الجزء: إذا كان معدل الترددات للجزء 0 تحصل على <strong>+5 نقاط</strong> للجزء، وتقل تدريجيًا (نصف نقطة لكل تردد) حتى تصبح 0 عند معدل 10 ترددات فأكثر للجزء.</li>
                    <li>نقوم بتوزيع أخطاء وترددات الجلسة بالتساوي على أجزائها لحساب المكافأة لكل جزء.</li>
                    <li>الطالب الأعلى درجة هو من سرد أكبر عدد من الأجزاء بأقل أخطاء وترددات لكل جزء.</li>
                </ul>
            </div>

            @if($precisionList->isEmpty())
                <div class="text-muted text-center py-3">لا توجد بيانات متاحة</div>
            @else
                <ul class="precision-list">
                    @foreach($precisionList as $idx => $row)
                        <li class="precision-item">
                            <span class="precision-rank">{{ $idx + 1 }}</span>
                            <span class="precision-name">{{ $row['name'] }}</span>
                            <div class="precision-meta">
                                <span class="parts">{{ $row['parts'] }} جزء</span>
                                <span class="errors">{{ $row['errors'] }} خطأ</span>
                                <span class="hesitations">{{ $row['hesitations'] }} تردد</span>
                            </div>
                            <span class="precision-score" title="نقاط الأجزاء: {{ $row['parts_points'] }} | مكافأة الجودة: {{ $row['quality_bonus'] }} | مكافأة الطلاقة: {{ $row['fluency_bonus'] }}">
                                {{ $row['score'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div> <!-- /tab-chart -->

    @foreach($sessions as $session)
    @php $grouped = collect($session->pages_data)->groupBy('part_number'); @endphp
    <div class="modal fade" id="sessionModal{{ $session->id }}" tabindex="-1" aria-labelledby="sessionModalLabel{{ $session->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white;">
                    <h5 class="modal-title" id="sessionModalLabel{{ $session->id }}">
                        <i class="fas fa-mosque me-2"></i> تفاصيل الجلسة #{{ $session->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">المشرف</small>
                            <strong>{{ $session->supervisor_name }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">الطالب</small>
                            <strong>{{ $session->student_name }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">التاريخ</small>
                            <strong>{{ $session->session_date->format('Y-m-d') }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">الأجزاء</small>
                            <strong>{{ count($session->parts) }} أجزاء</strong>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="p-3 text-center rounded" style="background: #fde8e6;">
                                <div class="text-muted small">الأخطاء</div>
                                <div class="fw-bold fs-4" style="color: var(--danger);">{{ $session->total_errors }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 text-center rounded" style="background: #fef5e7;">
                                <div class="text-muted small">الترددات</div>
                                <div class="fw-bold fs-4" style="color: #b36b00;">{{ $session->total_hesitations }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 text-center rounded" style="background: #f8f9fc;">
                                <div class="text-muted small">الصفحات</div>
                                <div class="fw-bold fs-4" style="color: var(--navy);">{{ count($session->pages_data) }}</div>
                            </div>
                        </div>
                    </div>

                    @foreach($session->parts as $partNumber)
                        @php $partPages = $grouped->get($partNumber, collect()); @endphp
                        <div class="card mb-3 border-0" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-sm);">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); border-radius: var(--radius-sm) var(--radius-sm) 0 0;">
                                <i class="fas fa-book-open me-2"></i> الجزء {{ $partNumber }}
                                <span class="float-end opacity-75 small">{{ $partPages->count() }} صفحة</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">الصفحة</th>
                                                <th class="text-center"><i class="fas fa-times-circle" style="color: var(--danger);"></i> الأخطاء</th>
                                                <th class="text-center"><i class="fas fa-question-circle" style="color: #b36b00;"></i> الترددات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($partPages->sortBy('page_number') as $p)
                                            <tr>
                                                <td class="text-center"><span class="badge-soft gold">ص {{ $p['page_number'] }}</span></td>
                                                <td class="text-center">
                                                    @if(($p['errors'] ?? 0) > 0)
                                                        <span class="badge-soft danger">{{ $p['errors'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if(($p['hesitations'] ?? 0) > 0)
                                                        <span class="badge-soft warning">{{ $p['hesitations'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                            @php
                                                $partErrors = $partPages->sum(fn($p) => (int)($p['errors'] ?? 0));
                                                $partHesitations = $partPages->sum(fn($p) => (int)($p['hesitations'] ?? 0));
                                            @endphp
                                            <tr style="background: var(--gold-soft); font-weight: 700; color: var(--navy);">
                                                <td class="text-center"><i class="fas fa-calculator me-1" style="color: var(--gold);"></i> مجموع الجزء</td>
                                                <td class="text-center"><span class="badge-soft danger">{{ $partErrors }}</span></td>
                                                <td class="text-center"><span class="badge-soft warning">{{ $partHesitations }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($session->notes)
                    <div class="alert" style="background: var(--gold-soft); color: var(--navy);">
                        <strong><i class="fas fa-sticky-note me-1"></i> الملاحظات:</strong>
                        <div style="white-space: pre-line;">{{ $session->notes }}</div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <form method="POST" action="{{ route('admin.yawm-al-himma.destroy', $session) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> حذف الجلسة
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    var competitionChartData = @json($competitionChart);
    var barChartData = @json($barChart);
    var precisionChartData = @json($precisionChart);
    var groupedPrecisionChartsData = @json($groupedPrecisionCharts);
    var competitionCtx = document.getElementById('competitionChart');
    var barCtx = document.getElementById('competitionBarChart');
    var precisionCtx = document.getElementById('precisionBarChart');
    var groupedPrecisionCtx = document.getElementById('groupedPrecisionBarChart');
    var competitionChart;
    var competitionBarChart;
    var precisionBarChart;
    var groupedPrecisionBarChart;

    var palette = [
        '#d4a030', '#182B48', '#e74c3c', '#27ae60', '#8e44ad',
        '#2980b9', '#f39c12', '#16a085', '#c0392b', '#2c3e50',
    ];

    function barBackground(ctx, alpha) {
        var idx = ctx.dataIndex;
        var color = palette[idx % palette.length];
        return hexToRgba(color, alpha);
    }

    function hexToRgba(hex, alpha) {
        var r = parseInt(hex.slice(1, 3), 16);
        var g = parseInt(hex.slice(3, 5), 16);
        var b = parseInt(hex.slice(5, 7), 16);
        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    function initNormalizedPrecisionChart() {
        if (!normalizedPrecisionCtx) return;
        if (normalizedPrecisionBarChart) {
            normalizedPrecisionBarChart.destroy();
        }
        normalizedPrecisionBarChart = new Chart(normalizedPrecisionCtx, {
            type: 'bar',
            data: {
                labels: normalizedPrecisionChartData.labels,
                datasets: [{
                    label: 'متوسط تقن الجزء',
                    data: normalizedPrecisionChartData.data,
                    borderWidth: 0,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    backgroundColor: function(context) {
                        return 'rgba(41, 128, 185, 0.85)';
                    },
                    hoverBackgroundColor: function(context) {
                        return 'rgba(41, 128, 185, 1)';
                    },
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(24, 43, 72, 0.9)',
                        titleColor: '#d4a030',
                        bodyColor: '#fff',
                        borderColor: '#d4a030',
                        borderWidth: 1,
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                return 'متوسط تقن الجزء: ' + context.parsed.x;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'متوسط درجة التقن لكل جزء',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#6b7a90' },
                        grid: { color: 'rgba(24,43,72,0.05)' },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'الطالب',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#182B48', font: { weight: '600' } },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    function initGroupedPrecisionChart(partsCount) {
        if (!groupedPrecisionCtx) return;
        var data = groupedPrecisionChartsData[partsCount];
        if (!data) return;
        if (groupedPrecisionBarChart) {
            groupedPrecisionBarChart.destroy();
        }
        groupedPrecisionBarChart = new Chart(groupedPrecisionCtx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'درجة التقن',
                    data: data.data,
                    borderWidth: 0,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    backgroundColor: function(context) {
                        return 'rgba(41, 128, 185, 0.85)';
                    },
                    hoverBackgroundColor: function(context) {
                        return 'rgba(41, 128, 185, 1)';
                    },
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'مقارنة الطلاب الذين سردوا ' + partsCount + ' أجزاء',
                        color: '#182B48',
                        font: { size: 14, weight: 'bold', family: 'Segoe UI, Tahoma, Arial' },
                    },
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(24, 43, 72, 0.9)',
                        titleColor: '#d4a030',
                        bodyColor: '#fff',
                        borderColor: '#d4a030',
                        borderWidth: 1,
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                return 'درجة التقن: ' + context.parsed.x;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'درجة التقن',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#6b7a90' },
                        grid: { color: 'rgba(24,43,72,0.05)' },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'الطالب',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#182B48', font: { weight: '600' } },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    function initPrecisionChart() {
        if (!precisionCtx) return;
        if (precisionBarChart) {
            precisionBarChart.destroy();
        }
        precisionBarChart = new Chart(precisionCtx, {
            type: 'bar',
            data: {
                labels: precisionChartData.labels,
                datasets: [{
                    label: 'درجة التقن',
                    data: precisionChartData.data,
                    borderWidth: 0,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    backgroundColor: function(context) {
                        return barBackground(context, 0.85);
                    },
                    hoverBackgroundColor: function(context) {
                        return barBackground(context, 1);
                    },
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(24, 43, 72, 0.9)',
                        titleColor: '#d4a030',
                        bodyColor: '#fff',
                        borderColor: '#d4a030',
                        borderWidth: 1,
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                return 'درجة التقن: ' + context.parsed.x + ' (10 لكل جزء + مكافأة جودة وطلاقة لكل جزء)';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'درجة التقن',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#6b7a90' },
                        grid: { color: 'rgba(24,43,72,0.05)' },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'الطالب',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#182B48', font: { weight: '600' } },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    function initBarChart() {
        if (!barCtx) return;
        if (competitionBarChart) {
            competitionBarChart.destroy();
        }
        competitionBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barChartData.labels,
                datasets: [{
                    label: 'عدد الأجزاء',
                    data: barChartData.data,
                    borderWidth: 0,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    backgroundColor: function(context) {
                        return barBackground(context, 0.85);
                    },
                    hoverBackgroundColor: function(context) {
                        return barBackground(context, 1);
                    },
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(24, 43, 72, 0.9)',
                        titleColor: '#d4a030',
                        bodyColor: '#fff',
                        borderColor: '#d4a030',
                        borderWidth: 1,
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' جزء';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'عدد الأجزاء',
                            color: '#6b7a90',
                        },
                        ticks: { stepSize: 1, color: '#6b7a90' },
                        grid: { color: 'rgba(24,43,72,0.05)' },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'الطالب',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#182B48', font: { weight: '600' } },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    function initCompetitionChart() {
        if (!competitionCtx) return;
        if (competitionChart) {
            competitionChart.destroy();
        }
        competitionChart = new Chart(competitionCtx, {
            type: 'line',
            data: {
                labels: competitionChartData.labels,
                datasets: competitionChartData.datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 14,
                            usePointStyle: true,
                            font: { size: 12, family: 'Segoe UI, Tahoma, Arial' },
                            color: '#182B48',
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(24, 43, 72, 0.9)',
                        titleColor: '#d4a030',
                        bodyColor: '#fff',
                        borderColor: '#d4a030',
                        borderWidth: 1,
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' جزء';
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'عدد الأجزاء التراكمي',
                            color: '#6b7a90',
                        },
                        ticks: { stepSize: 1, color: '#6b7a90' },
                        grid: { color: 'rgba(24,43,72,0.05)' },
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'أرقام الأجزاء',
                            color: '#6b7a90',
                        },
                        ticks: { color: '#6b7a90' },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    function getActiveChartInstance() {
        var activeView = document.querySelector('.yawm-page .chart-card .chart-view.active');
        if (!activeView) return competitionBarChart || competitionChart || precisionBarChart || groupedPrecisionBarChart;
        if (activeView.id === 'lineChartView') return competitionChart;
        if (activeView.id === 'precisionChartView') return precisionBarChart;
        if (activeView.id === 'groupedPrecisionChartView') return groupedPrecisionBarChart;
        return competitionBarChart;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tabButtons = document.querySelectorAll('.yawm-page .section-toggle .btn-toggle');
        var tabContents = document.querySelectorAll('.yawm-page .tab-content-section');

        tabButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = this.dataset.tab;
                tabButtons.forEach(function(b) { b.classList.remove('active'); });
                tabContents.forEach(function(c) { c.classList.remove('active'); });
                this.classList.add('active');
                document.getElementById(target).classList.add('active');
                if (target === 'tab-chart') {
                    initBarChart();
                }
            });
        });

        var chartTabs = document.querySelectorAll('.yawm-page .chart-card .chart-tabs .chart-tab');
        var chartViews = document.querySelectorAll('.yawm-page .chart-card .chart-view');
        chartTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var target = this.dataset.chart;
                chartTabs.forEach(function(t) { t.classList.remove('active'); });
                chartViews.forEach(function(v) { v.classList.remove('active'); });
                this.classList.add('active');
                if (target === 'lineChart') {
                    document.getElementById('lineChartView').classList.add('active');
                    initCompetitionChart();
                } else if (target === 'precisionChart') {
                    document.getElementById('precisionChartView').classList.add('active');
                    initPrecisionChart();
                } else if (target === 'groupedPrecisionChart') {
                    document.getElementById('groupedPrecisionChartView').classList.add('active');
                    var select = document.getElementById('groupedPrecisionSelect');
                    if (select && select.value) {
                        initGroupedPrecisionChart(select.value);
                    }
                } else {
                    document.getElementById('barChartView').classList.add('active');
                    initBarChart();
                }
            });
        });

        var toggleAllBtn = document.getElementById('toggleAllLines');
        if (toggleAllBtn) {
            toggleAllBtn.addEventListener('click', function() {
                var chart = getActiveChartInstance();
                if (!chart) return;
                if (chart.config.type === 'bar') {
                    chart.data.datasets.forEach(function(ds) {
                        ds.hidden = !ds.hidden;
                    });
                } else {
                    var anyVisible = chart.data.datasets.some(function(ds) { return !ds.hidden; });
                    chart.data.datasets.forEach(function(ds) {
                        ds.hidden = anyVisible;
                    });
                }
                chart.update();
            });
        }

        var groupedSelect = document.getElementById('groupedPrecisionSelect');
        if (groupedSelect) {
            groupedSelect.addEventListener('change', function() {
                var partsCount = this.value;
                var emptyState = document.getElementById('groupedPrecisionEmpty');
                var canvas = document.getElementById('groupedPrecisionBarChart');
                if (!partsCount || !groupedPrecisionChartsData[partsCount]) {
                    if (emptyState) emptyState.style.display = 'block';
                    if (canvas) canvas.style.display = 'none';
                    return;
                }
                if (emptyState) emptyState.style.display = 'none';
                if (canvas) canvas.style.display = 'block';
                initGroupedPrecisionChart(partsCount);
            });
        }

        var downloadBtn = document.getElementById('downloadChart');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                var chart = getActiveChartInstance();
                if (!chart) return;
                var link = document.createElement('a');
                link.href = chart.toBase64Image();
                var activeView = document.querySelector('.yawm-page .chart-card .chart-view.active');
                var suffix = 'competition';
                if (activeView) {
                    if (activeView.id === 'precisionChartView') suffix = 'precision';
                    else if (activeView.id === 'groupedPrecisionChartView') suffix = 'grouped-precision';
                }
                link.download = 'yawm-al-himma-' + suffix + '-' + new Date().toISOString().slice(0, 10) + '.png';
                link.click();
            });
        }
    });
</script>
@endpush
