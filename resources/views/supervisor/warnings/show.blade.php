@extends('layouts.app')
@section('title', 'تفاصيل الإنذار')
@section('page-title', 'تفاصيل الإنذار')
@section('role-label', 'مشرف')

@section('sidebar-menu')
@include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.warnings.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">تفاصيل الإنذار</li>
@endsection

@section('content')
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
    --card-shadow-hover: 0 12px 40px rgba(24, 43, 72, 0.15);
}

body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; border-radius: 9px;
    text-decoration: none; font-weight: 600; font-size: 13px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.btn-back:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); color: #fff; }
.btn-back i { color: var(--gold); }

.info-card {
    background: #fff; border-radius: 13px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 16px;
}
.info-header {
    display: flex; align-items: center; gap: 14px;
    padding: 16px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    position: relative;
}
.info-header::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--danger), #c0392b, var(--danger));
}
.info-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
    border-radius: 13px; display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #fff;
    box-shadow: 0 4px 15px rgba(192,57,43,0.35);
}
.info-content h5 { color: #fff; font-size: 18px; font-weight: 700; margin: 0 0 12px 0; }
.warning-meta { display: flex; gap: 12px; flex-wrap: wrap; }
.meta-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 4px 12px; border-radius: 999px;
    font-size: 12.5px; font-weight: 500;
    background: rgba(255,255,255,0.15);
    color: #fff;
    backdrop-filter: blur(5px);
}
.meta-badge i { color: var(--gold); }
.type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
}
.type-badge.attendance { background: rgba(243,156,18,0.25); color: #f1c40f; border: 1px solid rgba(243,156,18,0.3); }
.type-badge.recitation { background: rgba(52,152,219,0.25); color: #5dade2; border: 1px solid rgba(52,152,219,0.3); }
.type-badge.general { background: rgba(149,165,166,0.25); color: #bdc3c7; border: 1px solid rgba(149,165,166,0.3); }

.section-card {
    background: #fff; border-radius: 13px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden; margin-bottom: 16px;
    transition: all 0.3s ease;
}
.section-card:hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(24,43,72,0.1); }
.section-header {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 14px; color: var(--primary);
}
.section-header i { color: var(--gold); font-size: 18px; }
.section-body { padding: 16px; }

.student-info-card {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 13px;
    padding: 16px;
    text-align: center;
}
.student-avatar {
    width: 66px; height: 66px;
    background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
    font-size: 26px; color: #fff;
    box-shadow: 0 4px 15px rgba(142,68,173,0.3);
}
.student-name { font-size: 18px; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
.student-number { font-size: 12.5px; color: #95a5a6; margin-bottom: 10px; }
.student-level { display: inline-block; padding: 3px 10px; background: rgba(24,43,72,0.08); border-radius: 7px; font-size: 11.5px; color: var(--primary); }

.warning-content-card {
    background: linear-gradient(135deg, rgba(212,160,48,0.08) 0%, rgba(180,128,44,0.12) 100%);
    border: 2px solid rgba(212,160,48,0.15);
    border-radius: 13px;
    padding: 16px;
}
.warning-number-display {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 4px 12px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: var(--gold);
    border-radius: 999px;
    font-weight: 700;
    font-size: 12.5px;
    margin-bottom: 12px;
}
.warning-title-display { font-size: 18px; font-weight: 700; color: var(--primary); margin-bottom: 12px; }
.warning-text { font-size: 14px; color: #5a6c7d; line-height: 1.9; }

.notes-card {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 1px solid rgba(24,43,72,0.06);
    border-radius: 13px;
    padding: 16px;
    color: #5a6c7d;
    font-size: 13px;
    line-height: 1.7;
}

.stat-card {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 1px solid rgba(24,43,72,0.06);
    border-radius: 13px;
    padding: 14px 16px;
    text-align: center;
    transition: all 0.3s ease;
}
.stat-card:hover { transform: translateY(-3px); border-color: var(--gold); }
.stat-card .stat-number { font-size: 22px; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 8px; }
.stat-card .stat-number.success { color: var(--success); }
.stat-card .stat-number.danger { color: var(--danger); }
.stat-card .stat-number.warning { color: var(--warning); }
.stat-card .stat-label { font-size: 12px; color: #6c7a89; font-weight: 500; }

.info-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 11px 0; border-bottom: 1px solid rgba(24,43,72,0.05);
}
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 12.5px; color: #95a5a6; }
.info-value { font-size: 14px; font-weight: 700; color: var(--primary); }
.info-value i { color: var(--gold); margin-left: 8px; }

.sidebar-card {
    background: #fff; border-radius: 13px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    position: sticky;
    top: 20px;
}
.sidebar-header {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.05) 0%, rgba(26,51,86,0.08) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 14px; color: var(--primary);
}
.sidebar-header i { color: var(--gold); font-size: 18px; }
.sidebar-body { padding: 14px 16px; }

.action-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 8px 16px; border-radius: 9px;
    text-decoration: none; font-weight: 600; font-size: 13px;
    transition: all 0.3s ease;
    border: none; cursor: pointer;
    font-family: 'Tajawal', sans-serif;
    width: 100%;
}
.action-btn.primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.action-btn.primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); }
.action-btn.danger {
    background: linear-gradient(135deg, rgba(192,57,43,0.1) 0%, rgba(173,51,38,0.15) 100%);
    color: var(--danger);
    border: 1px solid rgba(192,57,43,0.2);
}
.action-btn.danger:hover {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(192,57,43,0.3);
}

.data-table {
    width: 100%; border-collapse: collapse;
}
.data-table th {
    padding: 11px 14px;
    font-size: 11px; font-weight: 700;
    color: var(--primary);
    text-align: center;
    background: rgba(24,43,72,0.05);
    border-bottom: 1px solid rgba(24,43,72,0.08);
}
.data-table td {
    padding: 11px 14px;
    font-size: 13px; color: #5a6c7d;
    text-align: center;
    border-bottom: 1px solid rgba(24,43,72,0.05);
}
.data-table tbody tr:hover { background: rgba(212,160,48,0.03); }

@media (max-width: 992px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="header-text">
            <h4>تفاصيل الإنذار</h4>
            <p>معلومات وإنذارات الطالب</p>
        </div>
    </div>
    <a href="{{ route('supervisor.warnings.index') }}" class="btn-back">
        <i class="fas fa-arrow-right"></i>العودة للقائمة
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="info-card">
            <div class="info-header">
                <div class="info-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="info-content">
                    <h5>معلومات الإنذار</h5>
                    <div class="warning-meta">
                        <span class="meta-badge date">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $warning->issue_date->format('Y/m/d') }}
                        </span>
                        <span class="type-badge {{ $warning->type }}">
                            @if($warning->type === 'attendance')
                                <i class="fas fa-calendar-times"></i>إنذار حضور
                            @elseif($warning->type === 'recitation')
                                <i class="fas fa-microphone"></i>إنذار تسميع
                            @else
                                <i class="fas fa-exclamation-circle"></i>إنذار عام
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-file-alt"></i>
                <span>محتوى الإنذار</span>
            </div>
            <div class="section-body">
                <div class="warning-content-card">
                    <div class="warning-number-display">
                        <i class="fas fa-hashtag"></i>
                        {{ $warning->warning_number }}
                    </div>
                    <div class="warning-title-display">{{ $warning->title }}</div>
                    <div class="warning-text">{{ $warning->content }}</div>
                </div>

                @if($warning->additional_notes)
                <div style="margin-top: 20px;">
                    <div style="font-weight: 700; color: var(--primary); margin-bottom: 10px;">
                        <i class="fas fa-sticky-note" style="color: var(--gold); margin-left: 8px;"></i>
                        ملاحظات إضافية
                    </div>
                    <div class="notes-card">{{ $warning->additional_notes }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($warning->type === 'attendance' && $warning->total_days)
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-calendar-times" style="color: var(--warning);"></i>
                <span>إحصائيات الحضور والغياب</span>
            </div>
            <div class="section-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number success">{{ $warning->total_days - $warning->absent_days - $warning->late_days }}</div>
                        <div class="stat-label">أيام الحضور</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number danger">{{ $warning->absent_days }}</div>
                        <div class="stat-label">أيام الغياب</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number warning">{{ $warning->late_days }}</div>
                        <div class="stat-label">أيام التأخير</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $warning->total_days }}</div>
                        <div class="stat-label">الإجمالي</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($warning->type === 'recitation' && $warning->total_sessions)
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-microphone" style="color: var(--info);"></i>
                <span>إحصائيات التسميع</span>
            </div>
            <div class="section-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">{{ $warning->total_sessions }}</div>
                        <div class="stat-label">إجمالي الجلسات</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number success">{{ $warning->total_sessions - $warning->failed_sessions }}</div>
                        <div class="stat-label">جلسات ناجحة</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number danger">{{ $warning->failed_sessions }}</div>
                        <div class="stat-label">جلسات راسبة</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($warning->attendance_data && is_array($warning->attendance_data) && count($warning->attendance_data) > 0)
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-calendar-alt"></i>
                <span>سجل الحضور</span>
            </div>
            <div class="section-body">
                <div class="table-responsive">
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
                                <td>{{ \Illuminate\Support\Str::of($record['date'])->before('T') }}</td>
                                <td>
                                    @php
                                        $statusLabels = [
                                            'present' => 'حاضر',
                                            'absent' => 'غائب',
                                            'late' => 'متأخر',
                                            'excused' => 'غائب بعذر',
                                            'holiday' => 'عطلة'
                                        ];
                                        $statusColors = [
                                            'present' => 'success',
                                            'absent' => 'danger',
                                            'late' => 'warning',
                                            'excused' => 'info',
                                            'holiday' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$record['status']] ?? 'secondary' }}">
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
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-microphone-alt"></i>
                <span>سجل التسميع</span>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>اليوم</th>
                                <th>التاريخ</th>
                                <th>نوع التسميع</th>
                                <th>المطلوب</th>
                                <th>نجح</th>
                                <th>رسب</th>
                                <th>الملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $typeLabels = [
                                    'new_memorization' => 'حفظ جديد',
                                    'review' => 'مراجعة',
                                    'unmastered' => 'حفظ غير متقن',
                                    'recitation' => 'سرد',
                                    'mafsil' => 'مفصل',
                                    'not_recitate' => 'لم يسمع'
                                ];
                            @endphp
                            @foreach($warning->recitation_data as $record)
                            <tr>
                                <td>{{ $record['day'] ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::of($record['session_date'])->before('T') }}</td>
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
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-clipboard-list"></i>
                <span>الاختبارات</span>
            </div>
            <div class="section-body">
                <div class="table-responsive">
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
    </div>

    <div class="col-lg-4">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <i class="fas fa-user-graduate"></i>
                <span>معلومات الطالب</span>
            </div>
            <div class="sidebar-body">
                <div class="student-info-card">
                    <div class="student-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="student-name">{{ $warning->student->user->name }}</div>
                    <div class="student-number">{{ $warning->student->student_number }}</div>
                    <span class="student-level">{{ $warning->student->level->name ?? '-' }}</span>
                </div>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(24,43,72,0.08);">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-calendar-plus"></i>تاريخ الإصدار</span>
                        <span class="info-value">{{ $warning->issue_date->format('Y/m/d') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-user"></i>المشرف</span>
                        <span class="info-value">{{ $warning->issuedBy->name }}</span>
                    </div>
                    @if($warning->from_date && $warning->to_date)
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-calendar-alt"></i>الفترة</span>
                        <span class="info-value" style="font-size: 13px;">{{ $warning->from_date->format('Y/m/d') }} - {{ $warning->to_date->format('Y/m/d') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="sidebar-card" style="margin-top: 25px;">
            <div class="sidebar-header">
                <i class="fas fa-cogs"></i>
                <span>الإجراءات</span>
            </div>
            <div class="sidebar-body">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="{{ route('supervisor.warnings.print', $warning->id) }}" class="action-btn primary" style="background: linear-gradient(135deg, var(--success) 0%, #219a52 100%);">
                        <i class="fas fa-print"></i>
                        طباعة الإنذار
                    </a>
                    <a href="{{ route('supervisor.warnings.index') }}" class="action-btn primary">
                        <i class="fas fa-arrow-right"></i>
                        العودة للقائمة
                    </a>
                    <form action="{{ route('supervisor.warnings.destroy', $warning->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإنذار؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn danger">
                            <i class="fas fa-trash-alt"></i>
                            حذف الإنذار
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
