@extends('layouts.app')

@section('title', 'لوحة تحكم المشرف')
@section('page-title', 'لوحة التحكم')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')

<div class="welcome-section mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="welcome-card">
                <div class="welcome-icon">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="welcome-content">
                    <h4>مرحباً بك {{ auth()->user()->name }}</h4>
                    <p>نظام إدارة جلسات القرآن الكريم</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="date-badge">
                <i class="fas fa-calendar-alt me-2"></i>
                <span id="currentDate"></span>
            </div>
        </div>
    </div>
</div>

@if(!$stats['attendance_recorded'] && $stats['total_students'] > 0)
<div class="alert-warning-custom mb-4">
    <div class="alert-content">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>تنبيه:</strong> لم يتم تسجيل الحضور والغياب لهذا اليوم بعد.
            <a href="{{ route('supervisor.attendance.index') }}" class="alert-link">سجل الحضور الآن</a>
        </div>
    </div>
</div>
@endif

<div class="stats-grid">
    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_students'] }}</div>
            <div class="stat-label">إجمالي الطلاب</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-user-graduate"></i>
        </div>
    </div>

    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_present'] }}</div>
            <div class="stat-label">حاضر اليوم</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>

    <div class="stat-card-modern danger">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_absent'] }}</div>
            <div class="stat-label">غائب اليوم</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-times-circle"></i>
        </div>
    </div>

    <div class="stat-card-modern warning">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-microphone"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['today_sessions'] }}</div>
            <div class="stat-label">جلسات اليوم</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-quran"></i>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-users"></i>
                <span>حالة الطلاب اليوم</span>
            </div>
            <a href="{{ route('supervisor.attendance.index') }}" class="view-all-btn">
                تسجيل الحضور <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الطالب</th>
                            <th>المستوى</th>
                            <th>الحضور</th>
                            <th>جلسات اليوم</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students->take(8) as $index => $student)
                        <tr>
                            <td class="row-num">{{ $index + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span>{{ $student->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($student->level)
                                    <span class="level-badge">{{ $student->level->name }}</span>
                                @else
                                    <span class="no-data">-</span>
                                @endif
                            </td>
                            <td>
                                @php $att = $student->today_attendance; @endphp
                                @if($att)
                                    <span class="badge-modern {{ $att->status_color }}">{{ $att->status_label }}</span>
                                    @if($att->arrival_time)
                                        <small class="text-muted d-block">{{ $att->arrival_time }}</small>
                                    @endif
                                @else
                                    <span class="badge-modern secondary">غير مسجل</span>
                                @endif
                            </td>
                            <td>
                                @php $sessionCount = $student->recitationSessions()->whereDate('session_date', today())->count(); @endphp
                                <span class="badge-modern {{ $sessionCount > 0 ? 'success' : 'secondary' }}">
                                    {{ $sessionCount }} جلسة
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('supervisor.students.show', $student) }}" class="action-btn view" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-cell">لا يوجد طلاب</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card actions-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-bolt"></i>
                <span>إجراءات سريعة</span>
            </div>
        </div>
        <div class="qa-body">
            <a href="{{ route('supervisor.attendance.index') }}" class="qa-item">
                <div class="qa-item-icon success">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="qa-item-content">
                    <span class="qa-item-title">تسجيل الحضور</span>
                    <span class="qa-item-desc">تسجيل حضور وغياب الطلاب</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('supervisor.recitation.index') }}" class="qa-item">
                <div class="qa-item-icon primary">
                    <i class="fas fa-microphone"></i>
                </div>
                <div class="qa-item-content">
                    <span class="qa-item-title">بدء جلسة تسميع</span>
                    <span class="qa-item-desc">تسميع جديد أو مراجعة</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('supervisor.warnings.create') }}" class="qa-item">
                <div class="qa-item-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="qa-item-content">
                    <span class="qa-item-title">إصدار إنذار</span>
                    <span class="qa-item-desc">إنذار طالب بسبب غياب أو تأخر</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
            <a href="{{ route('supervisor.reports.index') }}" class="qa-item">
                <div class="qa-item-icon info">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="qa-item-content">
                    <span class="qa-item-title">التقارير</span>
                    <span class="qa-item-desc">عرض تقارير الحضور والتسميع</span>
                </div>
                <i class="fas fa-arrow-left qa-arrow"></i>
            </a>
        </div>
    </div>
</div>

<div class="tables-grid">
    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-history"></i>
                <span>آخر جلسات التسميع</span>
            </div>
            <a href="{{ route('supervisor.sessions.index') }}" class="view-all-btn">
                عرض الكل <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>النوع</th>
                            <th>الجزء</th>
                            <th>الأخطاء</th>
                            <th>النتيجة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSessions as $session)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span>{{ $session->student->user->name }}</span>
                                </div>
                            </td>
                            <td><span class="badge-modern info">{{ $session->type_label }}</span></td>
                            <td>{{ $session->part ? 'الجزء '.$session->part->part_number : '-' }}</td>
                            <td>
                                <span class="text-danger fw-bold">{{ $session->total_errors }}</span>
                                <small class="text-muted">/ {{ $session->total_hesitations }} تردد</small>
                            </td>
                            <td><span class="badge-modern {{ $session->status_color }}">{{ $session->status_label }}</span></td>
                            <td class="date-cell">{{ $session->session_date->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-cell">لا توجد جلسات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-exclamation-triangle"></i>
                <span>آخر الإنذارات</span>
            </div>
            <a href="{{ route('supervisor.warnings.create') }}" class="view-all-btn">
                إضافة <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            @forelse($recentWarnings as $warning)
            <div class="warning-item">
                <div class="warning-content">
                    <div class="fw-bold warning-title">{{ $warning->student->user->name ?? '-' }}</div>
                    <div class="text-muted warning-desc">{{ $warning->title }}</div>
                    <span class="badge-modern warning">{{ $warning->type_label }}</span>
                </div>
                <small class="date-cell">{{ $warning->issue_date->format('d/m') }}</small>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>لا توجد إنذارات</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.welcome-section { margin-bottom: 25px; }

.welcome-card {
    display: flex;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, #182B48 0%, #1a3356 100%);
    padding: 25px 30px;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(212,160,48,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.welcome-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.welcome-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(212,160,48,0.4);
    position: relative;
    z-index: 1;
}

.welcome-content { position: relative; z-index: 1; }

.welcome-content h4 {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.welcome-content p {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    margin: 0;
}

.date-badge {
    background: rgba(255,255,255,0.1);
    padding: 12px 20px;
    border-radius: 12px;
    color: #d4a030;
    font-weight: 600;
    font-size: 14px;
    border: 1px solid rgba(212,160,48,0.2);
}

.alert-warning-custom {
    background: rgba(243,156,18,0.15);
    border: 1px solid rgba(243,156,18,0.3);
    border-radius: 12px;
    padding: 16px 20px;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 15px;
    color: #d4a030;
    font-size: 14px;
}

.alert-content i { font-size: 20px; }

.alert-link {
    color: #d4a030;
    font-weight: 600;
    text-decoration: none;
    margin-right: 10px;
}

.alert-link:hover { text-decoration: underline; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card-modern {
    position: relative;
    padding: 25px;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    transition: all 0.3s ease;
}

.stat-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.stat-card-modern.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card-modern.secondary { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); }
.stat-card-modern.success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card-modern.danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); }
.stat-card-modern.warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }

.stat-card-bg {
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.stat-icon {
    width: 55px;
    height: 55px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    backdrop-filter: blur(5px);
    flex-shrink: 0;
}

.stat-content { flex: 1; position: relative; z-index: 1; }

.stat-number {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: rgba(255,255,255,0.8);
    font-weight: 500;
}

.stat-decoration {
    position: absolute;
    bottom: 10px;
    left: 15px;
    font-size: 40px;
    opacity: 0.15;
    color: #fff;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }

.dashboard-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.card-header-custom {
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.card-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
}

.card-title i {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.card-body-custom { padding: 25px; }

.view-all-btn {
    color: #d4a030;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.view-all-btn:hover { color: #B4802C; }
.view-all-btn i { font-size: 11px; }

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead { background: #f8f9fa; }

.data-table th {
    padding: 14px 20px;
    font-size: 12px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
}

.data-table td {
    padding: 14px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.data-table tbody tr { transition: all 0.2s; }
.data-table tbody tr:hover { background: #fafbfc; }

.user-cell { display: flex; align-items: center; gap: 12px; }

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.row-num { color: #bdc3c7; font-weight: 600; font-size: 13px; }

.level-badge {
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.no-data { color: #bdc3c7; }

.date-cell { color: #7f8c8d; font-size: 13px; }

.badge-modern {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.badge-modern.info { background: rgba(52,152,219,0.15); color: #3498db; }
.badge-modern.success, .badge-modern.green { background: rgba(39,174,96,0.15); color: #27ae60; }
.badge-modern.danger, .badge-modern.red { background: rgba(192,57,43,0.15); color: #c0392b; }
.badge-modern.warning { background: rgba(243,156,18,0.15); color: #f39c12; }
.badge-modern.orange { background: rgba(230,126,34,0.15); color: #e67e22; }
.badge-modern.secondary { background: rgba(108,117,125,0.15); color: #6c757d; }

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
}

.action-btn.view { background: rgba(52,152,219,0.1); color: #3498db; }
.action-btn.view:hover { background: #3498db; color: #fff; transform: scale(1.05); }

.qa-body { padding: 15px 18px; }

.qa-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-bottom: 10px;
    border: 1px solid transparent;
}

.qa-item:last-child { margin-bottom: 0; }

.qa-item:hover {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04) 0%, rgba(42, 69, 112, 0.07) 100%);
    border-color: rgba(212, 160, 48, 0.15);
}

.qa-item:hover .qa-arrow { transform: translateX(-5px); color: #d4a030; }

.qa-item-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.qa-item-icon.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); color: #d4a030; box-shadow: 0 4px 15px rgba(24, 43, 72, 0.25); }
.qa-item-icon.success { background: linear-gradient(135deg, rgba(39, 174, 96, 0.15) 0%, rgba(46, 204, 113, 0.2) 100%); color: #27ae60; }
.qa-item-icon.warning { background: linear-gradient(135deg, rgba(243, 156, 18, 0.15) 0%, rgba(230, 126, 34, 0.2) 100%); color: #f39c12; }
.qa-item-icon.info { background: linear-gradient(135deg, rgba(52, 152, 219, 0.15) 0%, rgba(41, 128, 185, 0.2) 100%); color: #3498db; }

.qa-item-content { flex: 1; display: flex; flex-direction: column; gap: 3px; }

.qa-item-title { font-weight: 700; font-size: 14px; color: #182B48; }
.qa-item-desc { font-size: 12px; color: #7f8c8d; }

.qa-arrow { font-size: 12px; color: #bdc3c7; transition: all 0.3s ease; }

.tables-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) { .tables-grid { grid-template-columns: 1fr; } }

.warning-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f4f8;
}

.warning-content { display: flex; flex-direction: column; gap: 4px; }

.warning-title { font-weight: 600; font-size: 14px; color: #182B48; }
.warning-desc { font-size: 12px; color: #7f8c8d; }

.empty-cell, .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #bdc3c7;
}

.empty-state { display: flex; flex-direction: column; align-items: center; gap: 15px; }
.empty-state i { font-size: 40px; color: #d4a030; opacity: 0.5; }
</style>

@endsection

@push('scripts')
<script>
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('ar-SA', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});
</script>
@endpush