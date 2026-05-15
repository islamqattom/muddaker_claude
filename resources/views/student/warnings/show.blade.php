@extends('layouts.app')
@section('title', 'تفاصيل الإنذار')
@section('page-title', 'تفاصيل الإنذار')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('student.warnings.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">تفاصيل الإنذار</li>
@endsection

@push('styles')
<style>
.warning-detail {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.warning-detail .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.warning-detail .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.warning-detail .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.warning-detail .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--danger) 0%, #e74c3c 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(192,57,43,0.35);
}

.warning-detail .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.warning-detail .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.warning-detail .type-badge-display {
    padding: 8px 18px; border-radius: 12px;
    font-size: 13px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 8px;
}

.warning-detail .type-badge-display.attendance { background: rgba(243,156,18,0.15); color: var(--warning); }
.warning-detail .type-badge-display.recitation { background: rgba(52,152,219,0.15); color: var(--info); }
.warning-detail .type-badge-display.general { background: rgba(108,117,125,0.15); color: #6c757d; }

.warning-detail .info-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.warning-detail .info-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }

.warning-detail .card-accent {
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.warning-detail .info-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 14px;
}

.warning-detail .info-header i { color: var(--gold); font-size: 20px; }
.warning-detail .info-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.warning-detail .info-body { padding: 24px; }

.warning-detail .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.warning-detail .info-item {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 1px solid rgba(24,43,72,0.06);
    border-radius: 12px;
    padding: 16px;
}

.warning-detail .info-item .info-label { font-size: 11px; color: #6c7a89; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.warning-detail .info-item .info-value { font-size: 15px; font-weight: 700; color: var(--navy); }

.warning-detail .content-box {
    background: linear-gradient(135deg, rgba(212,160,48,0.08) 0%, rgba(180,128,44,0.12) 100%);
    border: 2px solid rgba(212,160,48,0.15);
    border-radius: 16px;
    padding: 20px;
}

.warning-detail .content-box h5 { color: var(--navy); font-weight: 700; margin-bottom: 12px; font-size: 16px; }
.warning-detail .content-box p { font-size: 15px; color: #5a6c7d; line-height: 1.7; margin: 0; }

.warning-detail .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

.warning-detail .stat-box {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 1px solid rgba(24,43,72,0.06);
    border-radius: 14px;
    padding: 18px;
    text-align: center;
    transition: all 0.3s ease;
}

.warning-detail .stat-box:hover { transform: translateY(-2px); border-color: var(--gold); }

.warning-detail .stat-box .stat-value { font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 6px; }
.warning-detail .stat-box .stat-value.success { color: var(--success); }
.warning-detail .stat-box .stat-value.danger { color: var(--danger); }
.warning-detail .stat-box .stat-value.warning { color: var(--warning); }
.warning-detail .stat-box .stat-value.primary { color: var(--navy); }

.warning-detail .stat-box .stat-label { font-size: 12px; color: #6c7a89; font-weight: 500; }

.warning-detail .data-table-wrap { border-radius: 16px; overflow: hidden; border: 1px solid rgba(24,43,72,0.06); }

.warning-detail .data-table {
    width: 100%; border-collapse: collapse; margin: 0;
}

.warning-detail .data-table thead {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: #fff;
}

.warning-detail .data-table th {
    padding: 14px 16px;
    font-size: 11px; font-weight: 700;
    text-align: center;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.warning-detail .data-table tbody tr { transition: all 0.2s ease; }
.warning-detail .data-table tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%); }
.warning-detail .data-table tbody tr:last-child td { border-bottom: none; }

.warning-detail .data-table td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    text-align: center;
}

.warning-detail .status-badge {
    padding: 5px 12px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
    color: white;
}

.warning-detail .back-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
    text-decoration: none;
}

.warning-detail .back-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

@media (max-width: 992px) {
    .warning-detail .stat-grid { grid-template-columns: repeat(2, 1fr); }
    .warning-detail .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .warning-detail .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="warning-detail">
    <div class="page-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h1 class="hero-title">تفاصيل الإنذار</h1>
                    <p class="hero-sub">بيانات وتقرير الإنذار</p>
                </div>
            </div>
            <span class="type-badge-display {{ $warning->type }}">
                <i class="fas fa-{{ $warning->type === 'attendance' ? 'calendar-times' : ($warning->type === 'recitation' ? 'microphone' : 'exclamation-circle') }}"></i>
                {{ $warning->type_label }}
            </span>
        </div>
    </div>

    <div class="info-card">
        <div class="card-accent"></div>
        <div class="info-header">
            <i class="fas fa-info-circle"></i>
            <h5>معلومات الإنذار</h5>
        </div>
        <div class="info-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">رقم الإنذار</div>
                    <div class="info-value">{{ $warning->warning_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">تاريخ الإصدار</div>
                    <div class="info-value">{{ $warning->issue_date->format('Y/m/d') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">اليوم</div>
                    <div class="info-value">{{ $warning->issue_day }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">المشرف</div>
                    <div class="info-value">{{ $warning->issuedBy->name }}</div>
                </div>
            </div>

            @if($warning->from_date && $warning->to_date)
            <div class="info-grid mt-3">
                <div class="info-item">
                    <div class="info-label">من تاريخ</div>
                    <div class="info-value">{{ $warning->from_date->format('Y/m/d') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">من يوم</div>
                    <div class="info-value">{{ $warning->from_day }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">إلى تاريخ</div>
                    <div class="info-value">{{ $warning->to_date->format('Y/m/d') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">إلى يوم</div>
                    <div class="info-value">{{ $warning->to_day }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="info-card">
        <div class="card-accent"></div>
        <div class="info-header">
            <i class="fas fa-file-alt"></i>
            <h5>محتوى الإنذار</h5>
        </div>
        <div class="info-body">
            <div class="content-box">
                <h5>{{ $warning->title }}</h5>
                <p>{{ $warning->content }}</p>
            </div>

            @if($warning->additional_notes)
            <div class="content-box mt-3" style="background: rgba(108,117,125,0.08); border-color: rgba(108,117,125,0.15);">
                <h5><i class="fas fa-sticky-note me-2"></i>ملاحظات إضافية</h5>
                <p>{{ $warning->additional_notes }}</p>
            </div>
            @endif
        </div>
    </div>

    @if($warning->total_days)
    <div class="info-card">
        <div class="card-accent"></div>
        <div class="info-header" style="background: linear-gradient(135deg, var(--warning) 0%, #e67e22 100%);">
            <i class="fas fa-calendar-times"></i>
            <h5>إحصائيات الحضور والغياب</h5>
        </div>
        <div class="info-body">
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-value success">{{ $warning->total_days - $warning->absent_days - $warning->late_days }}</div>
                    <div class="stat-label">أيام الحضور</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value danger">{{ $warning->absent_days }}</div>
                    <div class="stat-label">أيام الغياب</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value warning">{{ $warning->late_days }}</div>
                    <div class="stat-label">أيام التأخير</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value primary">{{ $warning->total_days }}</div>
                    <div class="stat-label">الإجمالي</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($warning->total_sessions)
    <div class="info-card">
        <div class="card-accent"></div>
        <div class="info-header" style="background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%);">
            <i class="fas fa-microphone"></i>
            <h5>إحصائيات التسميع</h5>
        </div>
        <div class="info-body">
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-value primary">{{ $warning->total_sessions }}</div>
                    <div class="stat-label">إجمالي الجلسات</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value success">{{ $warning->total_sessions - $warning->failed_sessions }}</div>
                    <div class="stat-label">جلسات ناجحة</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value danger">{{ $warning->failed_sessions }}</div>
                    <div class="stat-label">جلسات راسبة</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($warning->attendance_data && is_array($warning->attendance_data) && count($warning->attendance_data) > 0)
    <div class="info-card">
        <div class="card-accent"></div>
        <div class="info-header">
            <i class="fas fa-calendar-alt"></i>
            <h5>سجل الحضور</h5>
        </div>
        <div class="info-body" style="padding: 0;">
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>اليوم</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>وقت الحضور</th>
                            <th>الملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warning->attendance_data as $record)
                        <tr>
                            <td>{{ $record['day'] ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::of($record['date'] ?? '')->before('T') }}</td>
                            <td>
                                @php
                                    $statusLabels = ['present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'غائب بعذر', 'holiday' => 'عطلة'];
                                    $statusColors = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'excused' => 'info', 'holiday' => 'secondary'];
                                @endphp
                                <span class="status-badge bg-{{ $statusColors[$record['status']] ?? 'secondary' }}">
                                    {{ $statusLabels[$record['status']] ?? $record['status'] }}
                                </span>
                            </td>
                            <td>{{ $record['arrival_time'] ?? '-' }}</td>
                            <td>{{ $record['notes'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($warning->recitation_data && is_array($warning->recitation_data) && count($warning->recitation_data) > 0)
    <div class="info-card">
        <div class="card-accent"></div>
        <div class="info-header">
            <i class="fas fa-microphone-alt"></i>
            <h5>سجل التسميع</h5>
        </div>
        <div class="info-body" style="padding: 0;">
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>اليوم</th>
                            <th>التاريخ</th>
                            <th>نوع التسميع</th>
                            <th>المطلوب</th>
                            <th>نجح</th>
                            <th>رسَب</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $typeLabels = ['new_memorization' => 'حفظ جديد', 'review' => 'مراجعة', 'unmastered' => 'حفظ غير متقن', 'recitation' => 'سرد', 'mafsil' => 'مفصل', 'not_recitate' => 'لم يسمع'];
                        @endphp
                        @foreach($warning->recitation_data as $record)
                        <tr>
                            <td>{{ $record['day'] ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::of($record['session_date'] ?? '')->before('T') }}</td>
                            <td>{{ $typeLabels[$record['type']] ?? $record['type'] }}</td>
                            <td>{{ $record['total_pages'] ?? 0 }}</td>
                            <td>{{ $record['passed_pages'] ?? 0 }}</td>
                            <td>{{ $record['failed_pages'] ?? 0 }}</td>
                            <td>{{ $record['notes'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($warning->test_data && is_array($warning->test_data) && count($warning->test_data) > 0)
    <div class="info-card">
        <div class="card-accent"></div>
        <div class="info-header">
            <i class="fas fa-clipboard-list"></i>
            <h5>الاختبارات</h5>
        </div>
        <div class="info-body" style="padding: 0;">
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الاختبار</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warning->test_data as $record)
                        <tr>
                            <td>{{ $record['test'] ?? '-' }}</td>
                            <td>{{ $record['notes'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="text-center">
        <a href="{{ route('student.warnings.index') }}" class="back-btn">
            <i class="fas fa-arrow-right"></i>العودة للقائمة
        </a>
    </div>
</div>
@endsection