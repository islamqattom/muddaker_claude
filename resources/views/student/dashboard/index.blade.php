@extends('layouts.app')

@section('title', 'لوحة تحكم الطالب')
@section('page-title', 'لوحتي الشخصية')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
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
                    <h4>مرحباً {{ $student->user->name }}</h4>
                    <p>{{ $student->level->name ?? '' }}</p>
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

<div class="color-guide-card mb-4">
    <div class="card-header-custom">
        <div class="card-title">
            <i class="fas fa-info-circle"></i>
            <span>دليل الألوان والتقييم</span>
        </div>
    </div>
    <div class="card-body-custom">
        <div class="color-grid">
            <div class="color-item success">
                <div class="color-indicator"></div>
                <div class="color-content">
                    <div class="fw-bold">ممتاز</div>
                    <div class="color-desc">لم يخطئ أو تردد بحد أقصى ترددين</div>
                </div>
            </div>
            <div class="color-item warning">
                <div class="color-indicator"></div>
                <div class="color-content">
                    <div class="fw-bold">جيد</div>
                    <div class="color-desc">خطأ واحد أو من 3 إلى 5 ترددات</div>
                </div>
            </div>
            <div class="color-item danger">
                <div class="color-indicator"></div>
                <div class="color-content">
                    <div class="fw-bold">ضعيف</div>
                    <div class="color-desc">خطآن أو ما يعادلهما من الترددات</div>
                </div>
            </div>
            <div class="color-item dark">
                <div class="color-indicator"></div>
                <div class="color-content">
                    <div class="fw-bold">راسب</div>
                    <div class="color-desc">خطآن + تردد واحد أو أكثر</div>
                </div>
            </div>
        </div>
        <div class="mafsil-guide mt-3">
            <h6><i class="fas fa-book-quran me-2"></i>المفاصل وعلاماتها:</h6>
            <div class="mafsil-grid">
                <div class="mafsil-item">
                    <span class="mafsil-name">مفصل 3 و6</span>
                    <span class="badge-modern danger">رسوب أقل من 90%</span>
                </div>
                <div class="mafsil-item">
                    <span class="mafsil-name">مفصل 10 و15</span>
                    <span class="badge-modern warning">رسوب أقل من 80%</span>
                </div>
                <div class="mafsil-item">
                    <span class="mafsil-name">مفصل 20 و25 و30</span>
                    <span class="badge-modern danger">رسوب أقل من 75%</span>
                </div>
            </div>
            <small class="text-muted mt-2 d-block">خطأ = -3 علامات | كل 3 ترددات = -1 علامة</small>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $attendanceStats['present'] }}</div>
            <div class="stat-label">حضور</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-user-check"></i>
        </div>
    </div>

    <div class="stat-card-modern danger">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $attendanceStats['absent'] }}</div>
            <div class="stat-label">غياب</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-user-times"></i>
        </div>
    </div>

    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-microphone"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $sessionStats['total'] }}</div>
            <div class="stat-label">جلسات</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-quran"></i>
        </div>
    </div>

    <div class="stat-card-modern warning">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $sessionStats['saved_parts'] }}</div>
            <div class="stat-label">الأجزاء المحفوظة</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-award"></i>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card table-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-history"></i>
                <span>آخر جلسات التسميع</span>
            </div>
            <a href="{{ route('student.sessions.index') }}" class="view-all-btn">
                عرض الكل <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>الجزء</th>
                            <th>الصفحات</th>
                            <th>الأخطاء</th>
                            <th>النتيجة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSessions as $session)
                        <tr>
                            <td><span class="badge-modern info">{{ $session->type_label }}</span></td>
                            <td>
                                {{ $session->parts_label !== '-' ? $session->parts_label : '-' }}
                            </td>
                            <td>{{ $session->type === 'mafsil' ? '' : $session->total_pages }}</td>
                            <td>
                                <span class="text-danger fw-bold">{{ $session->total_errors }}</span>
                                <small class="text-muted">/ {{ $session->total_hesitations }} تردد</small>
                            </td>
                            <td><span class="badge-modern {{ $session->status_color }}">{{ $session->status_label }}</span></td>
                            <td class="date-cell"><small>{{ $session->session_date->format('d/m/Y') }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-cell">لا توجد جلسات بعد</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card sidebar-card">
        @if($recentWarnings->count() > 0)
        <div class="card-header-custom">
            <div class="card-title danger">
                <i class="fas fa-exclamation-triangle"></i>
                <span>إنذارات جديدة</span>
            </div>
        </div>
        <div class="card-body-custom p-0">
            @foreach($recentWarnings as $warning)
            <div class="warning-item">
                <div class="warning-content">
                    <div class="fw-bold warning-title">{{ $warning->title }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ $warning->issue_date->format('d/m/Y') }}</div>
                    <span class="badge-modern warning" style="font-size:10px;">{{ $warning->type_label }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="memorization-tip-card">
            <div class="tip-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="tip-content">
                <h6>تعلم طريقة الحفظ المتقن</h6>
                <p>اكتشف طريقة الحفظ الصحيحة</p>
            </div>
            <a href="{{ route('student.memorization') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>عرض
            </a>
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

.color-guide-card {
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

.card-title.danger i { background: linear-gradient(135deg, #c0392b, #e74c3c); color: #fff; }

.card-body-custom { padding: 25px; }

.color-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

@media (max-width: 992px) { .color-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .color-grid { grid-template-columns: 1fr; } }

.color-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    border-radius: 12px;
    color: #fff;
}

.color-item.success { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.color-item.warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #2c3e50; }
.color-item.danger { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.color-item.dark { background: linear-gradient(135deg, #2c3e50, #34495e); }

.color-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
}

.color-content { flex: 1; }
.color-content .fw-bold { font-size: 15px; margin-bottom: 3px; }
.color-desc { font-size: 11px; opacity: 0.9; }

.mafsil-guide h6 {
    color: #182B48;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 12px;
}

.mafsil-grid { display: flex; gap: 15px; flex-wrap: wrap; }

.mafsil-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 10px;
}

.mafsil-name { font-weight: 600; color: #182B48; font-size: 13px; }

.badge-modern {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.badge-modern.success, .badge-modern.green { background: rgba(39,174,96,0.15); color: #27ae60; }
.badge-modern.warning { background: rgba(243,156,18,0.15); color: #f39c12; }
.badge-modern.danger, .badge-modern.red { background: rgba(192,57,43,0.15); color: #c0392b; }
.badge-modern.info { background: rgba(52,152,219,0.15); color: #3498db; }
.badge-modern.orange { background: rgba(230,126,34,0.15); color: #e67e22; }

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
}

@media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }

.dashboard-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

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

.data-table { width: 100%; border-collapse: collapse; }
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

.date-cell { color: #7f8c8d; font-size: 13px; }

.empty-cell { text-align: center; padding: 40px 20px; color: #bdc3c7; }

.sidebar-card { display: flex; flex-direction: column; }

.warning-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f4f8;
}

.warning-content { display: flex; flex-direction: column; gap: 4px; }
.warning-title { font-weight: 600; font-size: 14px; color: #182B48; }

.memorization-tip-card {
    margin: 15px;
    padding: 20px;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    border-radius: 14px;
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.tip-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 15px;
}

.tip-content h6 { font-weight: 700; font-size: 15px; margin-bottom: 5px; }
.tip-content p { font-size: 12px; opacity: 0.9; margin-bottom: 15px; }
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