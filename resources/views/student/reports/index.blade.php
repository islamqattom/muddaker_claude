@extends('layouts.app')

@section('title', 'تقاريري')
@section('page-title', 'تقاريري')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">تقاريري</li>
@endsection

@push('styles')
<style>
.student-reports {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.student-reports .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.student-reports .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.student-reports .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.student-reports .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(212,160,48,0.35);
}

.student-reports .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.student-reports .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.student-reports .stat-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.student-reports .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }

.student-reports .stat-card-accent {
    position: absolute;
    top: 0; right: 0;
    width: 60px; height: 4px;
}

.student-reports .stat-card-body {
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.student-reports .stat-icon {
    width: 60px; height: 60px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
    flex-shrink: 0;
}

.student-reports .stat-icon.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: var(--gold); }
.student-reports .stat-icon.success { background: linear-gradient(135deg, var(--success), #2ecc71); color: white; }
.student-reports .stat-icon.info { background: linear-gradient(135deg, var(--info), #2980b9); color: white; }
.student-reports .stat-icon.warning { background: linear-gradient(135deg, var(--warning), #e67e22); color: white; }

.student-reports .stat-label { font-size: 12px; color: #6c7a89; margin-bottom: 4px; font-weight: 500; }
.student-reports .stat-value { font-size: 28px; font-weight: 800; color: var(--navy); line-height: 1; }

.student-reports .content-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.student-reports .content-card-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.student-reports .content-card-header i { color: var(--gold); font-size: 18px; }
.student-reports .content-card-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.student-reports .data-table { width: 100%; border-collapse: collapse; margin: 0; }
.student-reports .data-table thead { background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%); }
.student-reports .data-table th {
    padding: 14px 16px;
    font-size: 11px; font-weight: 700;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.student-reports .data-table td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.student-reports .data-table tbody tr { transition: all 0.2s ease; }
.student-reports .data-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }

.student-reports .empty-state { text-align: center; padding: 40px 20px; }
.student-reports .empty-state i { font-size: 36px; color: rgba(24,43,72,0.3); }

@media (max-width: 992px) {
    .student-reports .page-hero { padding: 20px 24px; }
    .student-reports .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .student-reports .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="student-reports">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div>
                <h1 class="hero-title">تقاريري</h1>
                <p class="hero-sub">عرض إحصائيات وتقارير أدائك</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-accent" style="background: linear-gradient(90deg, var(--navy), transparent);"></div>
                <div class="stat-card-body">
                    <div class="stat-icon primary">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <div>
                        <div class="stat-label">إجمالي الجلسات</div>
                        <div class="stat-value">{{ $totalSessions }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-accent" style="background: linear-gradient(90deg, var(--success), transparent);"></div>
                <div class="stat-card-body">
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="stat-label">نسبة الحضور</div>
                        <div class="stat-value">{{ $attendanceRate }}%</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-accent" style="background: linear-gradient(90deg, var(--info), transparent);"></div>
                <div class="stat-card-body">
                    <div class="stat-icon info">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <div class="stat-label">نسبة النجاح</div>
                        <div class="stat-value">{{ $passRate }}%</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-accent" style="background: linear-gradient(90deg, var(--warning), transparent);"></div>
                <div class="stat-card-body">
                    <div class="stat-icon warning">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div class="stat-label">الأجزاء المحفوظة</div>
                        <div class="stat-value">{{ $memorizedParts }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-history"></i>
                    <h5>آخر جلسات التسميع</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentSessions->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-microphone-alt"></i>
                            <p class="mt-2 mb-0 text-muted">لا توجد جلسات</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>النوع</th>
                                        <th>الجزء</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSessions as $session)
                                    <tr>
                                        <td class="fw-semibold">{{ $session->type_label }}</td>
                                        <td>
                                            <small class="text-muted">
                                                @if($session->type === 'mafsil')
                                                    @php
                                                        $partNumbers = $session->pages->pluck('page_number')->unique()->sort()->values();
                                                    @endphp
                                                    {{ $partNumbers->isNotEmpty() ? $partNumbers->map(fn($num) => 'الجزء '.$num)->join('، ') : '-' }}
                                                @elseif($session->part)
                                                    جزء {{ $session->part->part_number }}
                                                @else
                                                    ص {{ $session->start_page }} - {{ $session->end_page }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge-modern {{ $session->status_color }}">{{ $session->status_label }}</span>
                                        </td>
                                        <td><small class="text-muted">{{ $session->session_date->format('Y/m/d') }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-calendar-alt"></i>
                    <h5>آخر سجلات الحضور</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentAttendances->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p class="mt-2 mb-0 text-muted">لا يوجد سجل حضور</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>وقت الحضور</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAttendances as $attendance)
                                    <tr>
                                        <td class="fw-semibold">{{ $attendance->date->format('Y/m/d') }}</td>
                                        <td>
                                            <span class="badge-modern {{ $attendance->status_color }}">{{ $attendance->status_label }}</span>
                                        </td>
                                        <td>
                                            @if($attendance->arrival_time)
                                                <span class="time-val">{{ \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-modern { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-modern.success, .badge-modern.green { background: rgba(39,174,96,0.15); color: #27ae60; }
.badge-modern.danger, .badge-modern.red { background: rgba(192,57,43,0.15); color: #c0392b; }
.badge-modern.warning { background: rgba(243,156,18,0.15); color: #f39c12; }
.badge-modern.info { background: rgba(52,152,219,0.15); color: #3498db; }
.time-val { font-family: monospace; color: var(--navy); font-weight: 600; }
</style>
@endsection