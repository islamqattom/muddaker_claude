@extends('layouts.app')
@section('title', 'تقرير التسميع')
@section('page-title', 'تقرير التسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.rec-report {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.rec-report .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}

.rec-report .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rec-report .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 120px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.rec-report .hero-icon {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: white;
    box-shadow: 0 8px 24px rgba(212, 160, 48, 0.35);
}

.rec-report .hero-title { color: white; font-size: 24px; font-weight: 700; margin-bottom: 4px; }
.rec-report .hero-sub { color: rgba(255,255,255,0.75); font-size: 14px; margin: 0; }

.rec-report .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    text-align: center;
    padding: 10px 20px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212, 160, 48, 0.2);
    margin-top: 20px;
    position: relative; z-index: 1;
}

.rec-report .filter-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.rec-report .filter-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.rec-report .filter-header i { color: var(--gold); font-size: 20px; }
.rec-report .filter-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.rec-report .filter-body { padding: 20px 24px; }

.rec-report .form-control, .rec-report .form-select {
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 12px;
    padding: 10px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.rec-report .form-control:focus, .rec-report .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
}

.rec-report .btn-filter {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 12px;
    padding: 10px 24px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24,43,72,0.25);
}

.rec-report .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.35); }

.rec-report .btn-export {
    background: linear-gradient(135deg, var(--danger) 0%, #e74c3c 100%);
    border: none;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(220,38,38,0.25);
}

.rec-report .btn-export:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(220,38,38,0.35); }

.rec-report .stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.rec-report .stat-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    transition: all 0.3s ease;
}

.rec-report .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }

.rec-report .stat-card-body { padding: 20px; text-align: center; position: relative; }
.rec-report .stat-card-body::before {
    content: '';
    position: absolute;
    top: 0; left: 50%; transform: translateX(-50%);
    width: 50px; height: 3px;
    border-radius: 0 0 3px 3px;
}

.rec-report .stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
    font-size: 20px;
}

.rec-report .stat-value { font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
.rec-report .stat-label { font-size: 12px; color: #6b7280; font-weight: 600; }

.rec-report .stat-primary .stat-card-body::before { background: linear-gradient(90deg, var(--navy), var(--gold)); }
.rec-report .stat-primary .stat-value { color: var(--navy); }
.rec-report .stat-primary .stat-icon { background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.15)); color: var(--navy); }

.rec-report .stat-success .stat-card-body::before { background: #059669; }
.rec-report .stat-success .stat-value { color: #059669; }
.rec-report .stat-success .stat-icon { background: linear-gradient(135deg, rgba(5,150,105,0.1), rgba(16,185,129,0.15)); color: #059669; }

.rec-report .stat-danger .stat-card-body::before { background: #dc2626; }
.rec-report .stat-danger .stat-value { color: #dc2626; }
.rec-report .stat-danger .stat-icon { background: linear-gradient(135deg, rgba(220,38,38,0.1), rgba(239,68,68,0.15)); color: #dc2626; }

.rec-report .stat-warning .stat-card-body::before { background: #d97706; }
.rec-report .stat-warning .stat-value { color: #d97706; }
.rec-report .stat-warning .stat-icon { background: linear-gradient(135deg, rgba(217,119,6,0.1), rgba(245,158,11,0.15)); color: #d97706; }

.rec-report .stat-info .stat-card-body::before { background: #0891b2; }
.rec-report .stat-info .stat-value { color: #0891b2; }
.rec-report .stat-info .stat-icon { background: linear-gradient(135deg, rgba(8,145,178,0.1), rgba(6,182,212,0.15)); color: #0891b2; }

.rec-report .stat-secondary .stat-card-body::before { background: #6b7280; }
.rec-report .stat-secondary .stat-value { color: #6b7280; }
.rec-report .stat-secondary .stat-icon { background: linear-gradient(135deg, rgba(107,114,128,0.1), rgba(156,163,175,0.15)); color: #6b7280; }

.rec-report .table-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.rec-report .table-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
}

.rec-report .table-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; display: flex; align-items: center; gap: 10px; }
.rec-report .table-header h5 i { color: var(--gold); }

.rec-report .table-header .badge { background: rgba(255,255,255,0.2); color: white; font-size: 12px; padding: 6px 14px; border-radius: 20px; }

.rec-report .sessions-table { margin: 0; }

.rec-report .sessions-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rec-report .sessions-table thead th:first-child { text-align: center; }

.rec-report .sessions-table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-color: rgba(24,43,72,0.04);
    font-size: 14px;
    text-align: center;
}

.rec-report .sessions-table tbody tr { transition: all 0.2s ease; }
.rec-report .sessions-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }
.rec-report .sessions-table tbody tr:hover td:first-child { border-right: 3px solid var(--gold); }

.rec-report .row-num {
    width: 30px; height: 30px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.12));
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: var(--navy); font-size: 12px;
    margin: 0 auto;
}

.rec-report .student-link {
    color: var(--navy); font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.rec-report .student-link:hover { color: var(--gold); }

.rec-report .type-badge {
    padding: 6px 14px; border-radius: 10px;
    font-size: 12px; font-weight: 600;
}

.rec-report .type-badge.new_memorization { background: rgba(39,174,96,0.15); color: #27ae60; }
.rec-report .type-badge.review { background: rgba(52,152,219,0.15); color: #3498db; }
.rec-report .type-badge.unmastered { background: rgba(243,156,18,0.15); color: #d97706; }
.rec-report .type-badge.recitation { background: rgba(155,89,182,0.15); color: #8e44ad; }
.rec-report .type-badge.mafsil { background: rgba(231,76,60,0.15); color: #dc2626; }

.rec-report .part-badge {
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.12));
    color: var(--navy);
    padding: 4px 12px; border-radius: 8px;
    font-size: 12px; font-weight: 600;
}

.rec-report .pages-badge {
    background: var(--navy); color: white;
    padding: 4px 10px; border-radius: 8px;
    font-size: 11px; font-weight: 600;
}

.rec-report .errors-val { color: #dc2626; font-weight: 700; }
.rec-report .hesitations-val { color: #d97706; font-weight: 700; }

.rec-report .score-badge {
    padding: 6px 14px; border-radius: 10px;
    font-size: 13px; font-weight: 700;
}

.rec-report .status-badge {
    padding: 6px 14px; border-radius: 10px;
    font-size: 12px; font-weight: 600;
}

.rec-report .day-badge {
    background: rgba(24,43,72,0.06); color: var(--navy);
    padding: 4px 12px; border-radius: 8px;
    font-size: 11px; font-weight: 600;
}

.rec-report .empty-state { text-align: center; padding: 60px 20px; }
.rec-report .empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, rgba(24,43,72,0.06), rgba(42,69,112,0.08));
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
}
.rec-report .empty-icon i { font-size: 36px; color: var(--navy); opacity: 0.4; }

@media (max-width: 1200px) {
    .rec-report .stats-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .rec-report .page-hero { padding: 20px 24px; }
    .rec-report .hero-icon { width: 52px; height: 52px; font-size: 22px; }
    .rec-report .hero-title { font-size: 20px; }
    .rec-report .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .rec-report .sessions-table { font-size: 12px; }
    .rec-report .sessions-table thead th, .rec-report .sessions-table tbody td { padding: 10px 6px; }
}
</style>
@endpush

@section('content')
<div class="rec-report">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-4">
            <div class="hero-icon">
                <i class="fas fa-quran"></i>
            </div>
            <div>
                <h1 class="hero-title">تقرير التسميع</h1>
                <p class="hero-sub">عرض إحصائيات وتسجيلات جلسات التسميع للطلاب</p>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran me-2"></i>
            {{ 'إِنَّ هَذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ' }}
        </div>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <i class="fas fa-filter"></i>
            <h5>تصفية التقرير</h5>
        </div>
        <div class="filter-body">
            <form method="GET" action="{{ route('supervisor.reports.recitation') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-2">الطالب</label>
                    <select name="student_id" class="form-select">
                        <option value="">-- جميع الطلاب --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">نوع التسميع</label>
                    <select name="type" class="form-select">
                        <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                        <option value="new_memorization" {{ request('type') == 'new_memorization' ? 'selected' : '' }}>حفظ جديد</option>
                        <option value="review" {{ request('type') == 'review' ? 'selected' : '' }}>مراجعة</option>
                        <option value="unmastered" {{ request('type') == 'unmastered' ? 'selected' : '' }}>حفظ غير متقن</option>
                        <option value="recitation" {{ request('type') == 'recitation' ? 'selected' : '' }}>سرد</option>
                        <option value="mafsil" {{ request('type') == 'mafsil' ? 'selected' : '' }}>مفصل</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">الفترة</label>
                    <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>أسبوع</option>
                        <option value="month" {{ request('period', 'month') == 'month' ? 'selected' : '' }}>شهر</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                    </select>
                </div>
                <div class="col-md-2" id="fromDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label fw-bold mb-2">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="col-md-2" id="toDateCol" style="{{ request('period', 'month') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label fw-bold mb-2">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @if(isset($sessions) && $sessions->isNotEmpty())
                <div class="col-md-1">
                    <a href="{{ route('supervisor.reports.recitation', array_merge(request()->except('export'), ['export' => 'pdf'])) }}" class="btn btn-export w-100" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    @if(isset($sessions))
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-card-body">
                <div class="stat-icon"><i class="fas fa-list"></i></div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">إجمالي الجلسات</div>
            </div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-card-body">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value">{{ $stats['passed'] }}</div>
                <div class="stat-label">ناجح</div>
            </div>
        </div>
        <div class="stat-card stat-danger">
            <div class="stat-card-body">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-value">{{ $stats['failed'] }}</div>
                <div class="stat-label">راسب</div>
            </div>
        </div>
        <div class="stat-card stat-warning">
            <div class="stat-card-body">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-value">{{ $stats['total_errors'] }}</div>
                <div class="stat-label">إجمالي الأخطاء</div>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-card-body">
                <div class="stat-icon"><i class="fas fa-pause-circle"></i></div>
                <div class="stat-value">{{ $stats['total_hesitations'] }}</div>
                <div class="stat-label">إجمالي الترددات</div>
            </div>
        </div>
        <div class="stat-card stat-secondary">
            <div class="stat-card-body">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-value">{{ $stats['total_pages'] }}</div>
                <div class="stat-label">إجمالي الصفحات</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h5><i class="fas fa-list"></i>تفاصيل الجلسات</h5>
            <span class="badge">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}
            </span>
        </div>

        @if($sessions->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                <h5 class="text-muted mb-2">لا توجد جلسات</h5>
                <p class="text-muted small">لا توجد جلسات تسميع في الفترة المحددة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table sessions-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>النوع</th>
                            <th>الجزء</th>
                            <th>الصفحات</th>
                            <th>الأخطاء</th>
                            <th>الترددات</th>
                            <th>العلامة</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>اليوم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                            <tr>
                                <td><div class="row-num">{{ $loop->iteration }}</div></td>
                                <td>
                                    <a href="{{ route('supervisor.reports.student', $session->student->id) }}" class="student-link">
                                        {{ $session->student->user->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="type-badge {{ $session->type }}">
                                        {{ $session->type_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="part-badge">{{ $session->parts_label }}</span>
                                </td>
                                <td>
                                    @if($session->total_pages)
                                        <span class="pages-badge">{{ $session->total_pages }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_errors > 0)
                                        <span class="errors-val">{{ $session->total_errors }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_hesitations > 0)
                                        <span class="hesitations-val">{{ $session->total_hesitations }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->final_score !== null)
                                        @php
                                            $scoreClass = $session->final_score >= 90 ? 'bg-success text-white' : ($session->final_score >= 80 ? 'bg-warning text-dark' : 'bg-danger text-white');
                                        @endphp
                                        <span class="score-badge {{ $scoreClass }}">{{ number_format($session->final_score, 1) }}%</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge bg-{{ $session->status_color }}" style="color: white;">{{ $session->status_label }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $session->session_date->format('Y/m/d') }}</small>
                                </td>
                                <td>
                                    <span class="day-badge">{{ $session->day }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function handlePeriodChange() {
    const period = document.getElementById('periodSelect').value;
    const fromDateCol = document.getElementById('fromDateCol');
    const toDateCol = document.getElementById('toDateCol');

    if (period === 'custom') {
        fromDateCol.style.display = '';
        toDateCol.style.display = '';
    } else {
        fromDateCol.style.display = 'none';
        toDateCol.style.display = 'none';
    }
}
</script>
@endpush