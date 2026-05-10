@extends('layouts.app')
@section('title', 'تقرير ' . $student->user->name)
@section('page-title', 'تقرير الطالب')
@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name }}</li>
@endsection

@section('content')

<div class="quranic-verse-header mb-4">
    <div class="verse-decoration"></div>
    <p class="quranic-verse">﴿ وَتِلْكَ الْأَيَّامُ نُدَاوِلُهَا بَيْنَ النَّاسِ ﴾</p>
    <div class="verse-decoration"></div>
</div>

<div class="student-report-header mb-4">
    <div class="student-info-section">
        <div class="student-avatar">
            <img src="{{ $student->user->avatar_url }}" alt="Avatar">
        </div>
        <div class="student-details">
            <h4>{{ $student->user->name }}</h4>
            <div class="student-meta">
                <span class="meta-tag">
                    <i class="fas fa-layer-group"></i>
                    {{ $student->level->name ?? '-' }}
                </span>
                <span class="meta-tag secondary">
                    <i class="fas fa-user-tie"></i>
                    {{ $student->supervisor->user->name ?? '-' }}
                </span>
            </div>
        </div>
    </div>
    <div class="report-actions">
        @if(isset($fromDate) && isset($toDate))
        <a href="{{ route('supervisor.reports.student', array_merge(['student' => $student->id, 'from_date' => $fromDate->toDateString(), 'to_date' => $toDate->toDateString()], ['export' => 'pdf'])) }}" class="btn btn-export" target="_blank">
            <i class="fas fa-file-pdf"></i>
            تصدير PDF
        </a>
        @endif
    </div>
</div>

<div class="filter-card mb-4">
    <form method="GET" action="{{ route('supervisor.reports.student', $student->id) }}" class="filter-form">
        <div class="filter-grid">
            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-calendar me-2"></i>من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate->toDateString() }}">
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-calendar me-2"></i>إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate->toDateString() }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>بحث
                </button>
            </div>
            <div class="filter-period">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</span>
            </div>
        </div>
    </form>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card attendance">
        <div class="stat-icon-wrapper">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-row">
                <div class="stat-box success">
                    <span class="stat-number">{{ $attendanceStats['present'] }}</span>
                    <span class="stat-text">حاضر</span>
                </div>
                <div class="stat-box danger">
                    <span class="stat-number">{{ $attendanceStats['absent'] }}</span>
                    <span class="stat-text">غائب</span>
                </div>
                <div class="stat-box warning">
                    <span class="stat-number">{{ $attendanceStats['late'] }}</span>
                    <span class="stat-text">متأخر</span>
                </div>
            </div>
            <div class="stat-progress">
                <div class="progress-header">
                    <span>نسبة الحضور</span>
                    <span class="progress-value">
                        @php
                            $attendancePercentage = $attendanceStats['total'] > 0
                                ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100)
                                : 0;
                        @endphp
                        {{ $attendancePercentage }}%
                    </span>
                </div>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill success" style="width: {{ $attendancePercentage }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="stat-card recitation">
        <div class="stat-icon-wrapper secondary">
            <i class="fas fa-quran"></i>
        </div>
        <div class="stat-content">
            <div class="stat-row">
                <div class="stat-box primary">
                    <span class="stat-number">{{ $sessionStats['total'] }}</span>
                    <span class="stat-text">إجمالي</span>
                </div>
                <div class="stat-box success">
                    <span class="stat-number">{{ $sessionStats['passed'] }}</span>
                    <span class="stat-text">ناجح</span>
                </div>
                <div class="stat-box danger">
                    <span class="stat-number">{{ $sessionStats['failed'] }}</span>
                    <span class="stat-text">راسب</span>
                </div>
            </div>
            <div class="stat-progress">
                <div class="progress-header">
                    <span>نسبة النجاح</span>
                    <span class="progress-value">
                        @php
                            $passRate = $sessionStats['total'] > 0
                                ? round(($sessionStats['passed'] / $sessionStats['total']) * 100)
                                : 0;
                        @endphp
                        {{ $passRate }}%
                    </span>
                </div>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill info" style="width: {{ $passRate }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-card mb-4">
    <div class="section-header">
        <div class="section-icon">
            <i class="fas fa-history"></i>
        </div>
        <h5>جلسات التسميع</h5>
    </div>
    <div class="section-body p-0">
        @if($sessions->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                <p>لا توجد جلسات تسميع في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>الجزء</th>
                            <th class="text-center">الصفحات</th>
                            <th class="text-center">الأخطاء</th>
                            <th class="text-center">الترددات</th>
                            <th class="text-center">العلامة</th>
                            <th class="text-center">الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                            <tr>
                                <td><span class="type-pill">{{ $session->type_label }}</span></td>
                                <td>
                                    @if($session->part)
                                        <span class="part-label">الجزء {{ $session->part->part_number }}</span>
                                    @elseif($session->mafsil_type)
                                        <span class="mafsil-label">مفصل ({{ $session->mafsil_type }})</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($session->total_pages)
                                        <span class="pages-badge">{{ $session->total_pages }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center"><span class="errors-num">{{ $session->total_errors ?? 0 }}</span></td>
                                <td class="text-center"><span class="hesitations-num">{{ $session->total_hesitations ?? 0 }}</span></td>
                                <td class="text-center">
                                    @if($session->final_score !== null)
                                        @php
                                            $scoreClass = $session->final_score >= 90 ? 'bg-success' : ($session->final_score >= 80 ? 'bg-warning text-dark' : 'bg-danger');
                                        @endphp
                                        <span class="score-badge {{ $scoreClass }}">{{ number_format($session->final_score, 1) }}%</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="status-pill {{ $session->status_color }}">{{ $session->status_label }}</span>
                                </td>
                                <td><span class="date-text">{{ $session->session_date->format('Y/m/d') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="section-card">
    <div class="section-header">
        <div class="section-icon secondary">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <h5>سجلات الحضور</h5>
    </div>
    <div class="section-body p-0">
        @if($attendances->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                <p>لا توجد سجلات حضور في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>الحالة</th>
                            <th>وقت الحضور</th>
                            <th>ملاحظة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                            <tr>
                                <td>
                                    @switch($attendance->status)
                                        @case('present')
                                            <span class="status-pill success"><i class="fas fa-check me-1"></i>حاضر</span>
                                            @break
                                        @case('absent')
                                            <span class="status-pill danger"><i class="fas fa-times me-1"></i>غائب</span>
                                            @break
                                        @case('late')
                                            <span class="status-pill warning"><i class="fas fa-clock me-1"></i>متأخر</span>
                                            @break
                                        @case('excused')
                                            <span class="status-pill info"><i class="fas fa-file-alt me-1"></i>بعذر</span>
                                            @break
                                        @case('holiday')
                                            <span class="status-pill purple"><i class="fas fa-calendar-week me-1"></i>عطلة</span>
                                            @break
                                        @default
                                            <span class="status-pill secondary">غير محدد</span>
                                    @endswitch
                                </td>
                                <td><span class="time-text">{{ $attendance->arrival_time ?? '-' }}</span></td>
                                <td><span class="notes-text">{{ $attendance->notes ?? '-' }}</span></td>
                                <td><span class="date-text">{{ $attendance->date->format('Y/m/d') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
.quranic-verse-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 18px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.05));
    border-radius: 14px;
}

.quranic-verse-header .verse-decoration {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a030, transparent);
    max-width: 80px;
}

.quranic-verse-header .quranic-verse {
    font-family: 'Amiri', serif;
    font-size: 20px;
    color: #182B48;
    margin: 0;
}

.student-report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
}

@media (max-width: 768px) {
    .student-report-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
}

.student-info-section {
    display: flex;
    align-items: center;
    gap: 20px;
}

.student-avatar {
    width: 75px;
    height: 75px;
    border-radius: 18px;
    overflow: hidden;
    border: 3px solid #d4a030;
}

.student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-details h4 {
    color: #182B48;
    font-weight: 700;
    font-size: 22px;
    margin: 0 0 10px 0;
}

.student-meta {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.meta-tag.secondary {
    background: linear-gradient(135deg, rgba(180,128,44,0.15), rgba(212,160,48,0.1));
    color: #B4802C;
}

.meta-tag i {
    color: #d4a030;
}

.btn-export {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #c0392b, #e74c3c);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-export:hover {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(192,57,43,0.3);
    color: #fff;
}

.btn-export i {
    font-size: 16px;
}

.filter-card {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid rgba(212,160,48,0.1);
}

.filter-form { margin: 0; }

.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1.5fr;
    gap: 20px;
    align-items: end;
}

@media (max-width: 992px) {
    .filter-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 576px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
}

.filter-label {
    display: block;
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.filter-label i {
    color: #d4a030;
    margin-left: 4px;
}

.filter-actions { display: flex; align-items: flex-end; }

.filter-period {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: linear-gradient(135deg, rgba(24,43,72,0.05), rgba(42,69,112,0.05));
    border-radius: 12px;
    color: #182B48;
    font-size: 14px;
    font-weight: 600;
}

.filter-period i {
    color: #d4a030;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    display: flex;
    gap: 20px;
}

.stat-icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.stat-icon-wrapper.secondary {
    background: linear-gradient(135deg, #182B48, #2A4570);
}

.stat-content {
    flex: 1;
}

.stat-row {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.stat-box {
    flex: 1;
    padding: 15px;
    border-radius: 14px;
    text-align: center;
}

.stat-box.success {
    background: linear-gradient(135deg, rgba(39,174,96,0.15), rgba(46,204,113,0.1));
}

.stat-box.danger {
    background: linear-gradient(135deg, rgba(192,57,43,0.15), rgba(231,76,60,0.1));
}

.stat-box.warning {
    background: linear-gradient(135deg, rgba(243,156,18,0.15), rgba(230,126,34,0.1));
}

.stat-box.primary {
    background: linear-gradient(135deg, rgba(52,152,219,0.15), rgba(41,128,185,0.1));
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #182B48;
    line-height: 1.2;
}

.stat-text {
    display: block;
    font-size: 11px;
    color: #7f8c8d;
    font-weight: 500;
}

.stat-progress .progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 13px;
    color: #7f8c8d;
}

.stat-progress .progress-value {
    font-weight: 700;
    color: #182B48;
}

.progress-bar-track {
    height: 8px;
    background: #f0f4f8;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.progress-bar-fill.success { background: linear-gradient(90deg, #27ae60, #2ecc71); }
.progress-bar-fill.info { background: linear-gradient(90deg, #3498db, #2980b9); }

.section-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px 25px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.04));
    border-bottom: 1px solid #f0f4f8;
}

.section-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.section-icon.secondary {
    background: linear-gradient(135deg, #B4802C, #d4a030);
}

.section-header h5 {
    color: #182B48;
    font-weight: 700;
    font-size: 17px;
    margin: 0;
}

.section-body {
    padding: 25px;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 32px;
    color: #d4a030;
}

.empty-state p {
    color: #7f8c8d;
    font-size: 15px;
    margin: 0;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead {
    background: #f8f9fa;
}

.modern-table th {
    padding: 14px 18px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 14px 18px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr:hover {
    background: #fafbfc;
}

.type-pill {
    background: rgba(127,140,141,0.15);
    color: #7f8c8d;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.part-label {
    color: #182B48;
    font-weight: 600;
}

.mafsil-label {
    color: #c0392b;
    font-weight: 600;
}

.pages-badge {
    font-family: monospace;
    background: #182B48;
    color: #fff;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
}

.errors-num, .hesitations-num {
    font-weight: 600;
    color: #c0392b;
}

.score-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-pill.success { background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; }
.status-pill.danger { background: linear-gradient(135deg, #c0392b, #e74c3c); color: #fff; }
.status-pill.warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }
.status-pill.info { background: linear-gradient(135deg, #3498db, #2980b9); color: #fff; }
.status-pill.purple { background: linear-gradient(135deg, #8e44ad, #9b59b6); color: #fff; }
.status-pill.secondary { background: linear-gradient(135deg, #7f8c8d, #95a5a6); color: #fff; }

.date-text {
    color: #7f8c8d;
    font-size: 13px;
}

.time-text {
    font-family: monospace;
    color: #182B48;
}

.notes-text {
    color: #7f8c8d;
    font-size: 13px;
}
</style>

@endsection