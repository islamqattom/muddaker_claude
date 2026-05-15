@extends('layouts.app')
@section('title', 'تفاصيل الطالب')
@section('page-title', 'تفاصيل الطالب')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">تفاصيل الطالب</li>
@endsection
@section('content')

<div class="portal-container">
    <div class="student-header-card mb-4">
        <div class="student-header-pattern"></div>
        <div class="student-header-content">
            <div class="student-avatar-xl">
                <i class="fas fa-user"></i>
            </div>
            <div class="student-info">
                <h4>{{ $student->user->name }}</h4>
                <p class="student-meta">
                    {{ $student->level->name ?? '' }} | المشرف: {{ $student->supervisor->name ?? 'غير محدد' }}
                </p>
                <div class="student-badges">
                    @if($student->current_part)
                        <span class="badge-custom primary">
                            <i class="fas fa-book"></i>
                            <span>الجزء الحالي: {{ $student->current_part }}</span>
                        </span>
                    @endif
                    @if($student->current_page)
                        <span class="badge-custom secondary">
                            <i class="fas fa-file-alt"></i>
                            <span>صفحة {{ $student->current_page }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-student-progress-chart :student-id="$student->id" :velocity-data="$velocityData" />

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <x-page-difficulty-heatmap :student-id="$student->id" :heatmap-data="$heatmapData" />
        </div>
        <div class="col-lg-4">
            <x-recent-session-timeline :sessions="$recentSessions" />
        </div>
    </div>

    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('parent.student.detail', $student->id) }}" class="filter-grid">
            <div class="filter-field">
                <label class="filter-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate->format('Y-m-d') }}">
            </div>
            <div class="filter-field">
                <label class="filter-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate->format('Y-m-d') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    <span>تصفية</span>
                </button>
                <a href="{{ route('parent.student.detail', $student->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="table-card mb-4">
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon">
                    <i class="fas fa-microphone"></i>
                </div>
                <span>سجل جلسات التسميع ({{ $sessions->count() }})</span>
            </div>
        </div>
        <div class="card-body-custom">
            @if($sessions->isEmpty())
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-microphone-alt"></i>
                    </div>
                    <p>لا توجد جلسات تسميع في هذه الفترة</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>نوع الجلسة</th>
                                <th>الجزء/الصفحات</th>
                                <th>الأخطاء</th>
                                <th>الترددات</th>
                                <th>الدرجة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr>
                                <td class="fw-bold">{{ $session->session_date->format('Y/m/d') }}</td>
                                <td>
                                    <span class="type-badge-sm {{ $session->type }}">
                                        {{ $session->type_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($session->part)
                                        جزء {{ $session->part->part_number }}
                                    @else
                                        {{ $session->start_page }} - {{ $session->end_page }}
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_errors > 0)
                                        <span class="text-danger fw-bold">{{ $session->total_errors }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_hesitations > 0)
                                        <span class="text-warning fw-bold">{{ $session->total_hesitations }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->final_score !== null)
                                        <span class="fw-bold">{{ number_format($session->final_score, 1) }} / {{ $session->max_score }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge-sm {{ $session->status_color }}">
                                        {{ $session->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span>سجل الحضور ({{ $attendances->count() }})</span>
            </div>
        </div>
        <div class="card-body-custom">
            @if($attendances->isEmpty())
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <p>لا يوجد سجل حضور في هذه الفترة</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>الحالة</th>
                                <th>وقت الوصول</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                            <tr>
                                <td class="fw-bold">{{ $attendance->date->format('Y/m/d') }}</td>
                                <td>
                                    <span class="status-badge-sm {{ $attendance->status_color }}">
                                        {{ $attendance->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($attendance->arrival_time)
                                        <span class="time-value">{{ \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->notes)
                                        <span class="notes-value">{{ $attendance->notes }}</span>
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

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <span>الإنذارات ({{ $warnings->count() }})</span>
            </div>
        </div>
        <div class="card-body-custom">
            @if($warnings->isEmpty())
                <div class="empty-state success">
                    <div class="empty-illustration success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p>لا توجد إنذارات</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>نوع الإنذار</th>
                                <th>العنوان</th>
                                <th>التاريخ</th>
                                <th>الرقم</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warnings as $warning)
                            <tr>
                                <td>
                                    @if($warning->type === 'attendance')
                                        <span class="type-badge-sm attendance">حضور</span>
                                    @elseif($warning->type === 'recitation')
                                        <span class="type-badge-sm recitation">تسميع</span>
                                    @else
                                        <span class="type-badge-sm general">عام</span>
                                    @endif
                                </td>
                                <td>{{ $warning->title }}</td>
                                <td class="fw-bold">{{ $warning->issue_date->format('Y/m/d') }}</td>
                                <td><code class="warning-code">{{ $warning->warning_number }}</code></td>
                                <td>
                                    @if($warning->is_read_by_parent)
                                        <span class="status-badge-sm read">مقروء</span>
                                    @else
                                        <span class="status-badge-sm new">جديد</span>
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

<style>
.portal-container {
    max-width: 1400px;
    margin: 0 auto;
}

.student-header-card {
    background: linear-gradient(135deg, #182B48 0%, #1a3356 100%);
    border-radius: 20px;
    padding: 35px;
    position: relative;
    overflow: hidden;
}

.student-header-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(circle at 10% 20%, rgba(212,160,48,0.05) 0%, transparent 50%),
                      radial-gradient(circle at 90% 80%, rgba(212,160,48,0.05) 0%, transparent 50%);
    pointer-events: none;
}

.student-header-pattern::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.student-header-content {
    display: flex;
    align-items: center;
    gap: 25px;
    position: relative;
    z-index: 1;
}

.student-avatar-xl {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(212,160,48,0.35);
}

.student-info h4 {
    color: #fff;
    font-size: 26px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.student-meta {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    margin: 0 0 12px 0;
}

.student-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.badge-custom {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
}

.badge-custom.primary {
    background: rgba(212,160,48,0.2);
    color: #d4a030;
}

.badge-custom.secondary {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.filter-card {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 18px;
    align-items: end;
}

@media (max-width: 768px) { .filter-grid { grid-template-columns: 1fr; } }

.filter-field { display: flex; flex-direction: column; }

.filter-label {
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.filter-actions { display: flex; gap: 10px; }

.table-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
}

.table-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.table-icon.warning {
    background: linear-gradient(135deg, #d48806, #f39c12);
    color: #fff;
}

.card-body-custom { padding: 0; }

.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-illustration {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 32px;
    color: #d4a030;
}

.empty-illustration.success {
    background: linear-gradient(135deg, #16835a, #27ae60);
    color: #fff;
}

.empty-state p { color: #95a5a6; font-size: 14px; }

.table-responsive { overflow-x: auto; }

.modern-table { width: 100%; border-collapse: collapse; }
.modern-table thead { background: #f8f9fa; }

.modern-table th {
    padding: 18px 22px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 16px 22px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.type-badge-sm {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.type-badge-sm.new_memorization { background: rgba(39,174,96,0.12); color: #16835a; }
.type-badge-sm.review { background: rgba(52,152,219,0.12); color: #2475a8; }
.type-badge-sm.unmastered { background: rgba(230,126,34,0.12); color: #d48806; }
.type-badge-sm.recitation { background: rgba(142,68,173,0.12); color: #7d3c98; }
.type-badge-sm.mafsil { background: rgba(24,43,72,0.12); color: #182B48; }
.type-badge-sm.attendance { background: rgba(243,156,18,0.12); color: #d48806; }
.type-badge-sm.general { background: rgba(127,140,141,0.12); color: #7f8c8d; }

.status-badge-sm {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.status-badge-sm.success,
.status-badge-sm.passed,
.status-badge-sm.read { background: rgba(39,174,96,0.12); color: #16835a; }

.status-badge-sm.danger,
.status-badge-sm.failed,
.status-badge-sm.new { background: rgba(192,57,43,0.12); color: #a82e1c; }

.status-badge-sm.warning,
.status-badge-sm.late { background: rgba(243,156,18,0.12); color: #d48806; }

.status-badge-sm.info,
.status-badge-sm.excused { background: rgba(52,152,219,0.12); color: #2475a8; }

.status-badge-sm.pending { background: rgba(127,140,141,0.12); color: #7f8c8d; }

.warning-code {
    background: #f0f4f8;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    color: #182B48;
}

.time-value { color: #7f8c8d; font-size: 13px; }
.notes-value { color: #7f8c8d; font-size: 13px; }
</style>
@endsection