@extends('layouts.app')
@section('title', 'بوابة ولي الأمر')
@section('page-title', 'متابعة أبنائي')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('content')

<div class="portal-container">
    <div class="welcome-section mb-4">
        <div class="welcome-wrapper">
            <div class="welcome-pattern"></div>
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="welcome-content">
                        <div class="welcome-badge">
                            <i class="fas fa-moon"></i>
                            <span>بوابة ولي الأمر</span>
                        </div>
                        <h2 class="welcome-title">أهلاً {{ auth()->user()->name }}</h2>
                        <p class="welcome-subtitle">بارك الله فيك - تابع تقدم أبنائك في حفظ القرآن الكريم</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="date-wrapper">
                        <div class="date-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="date-info">
                            <span class="date-label">التاريخ</span>
                            <span class="date-value" id="currentDate"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ count($studentsData) }}</div>
                <div class="stat-label">عدد الأبناء</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-book-quran"></i>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                @php $totalPresent = array_sum(array_column(array_column($studentsData, 'attendance'), 'present')); @endphp
                <div class="stat-number">{{ $totalPresent }}</div>
                <div class="stat-label">حضور هذا الشهر</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-microphone"></i>
                </div>
            </div>
            <div class="stat-content">
                @php $totalSessions = array_sum(array_column(array_column($studentsData, 'sessions'), 'total')); @endphp
                <div class="stat-number">{{ $totalSessions }}</div>
                <div class="stat-label">جلسات التسميع</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-quran"></i>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-content">
                @php $totalWarnings = array_sum(array_map(fn($w) => $w->count(), array_column($studentsData, 'recent_warnings'))); @endphp
                <div class="stat-number">{{ $totalWarnings }}</div>
                <div class="stat-label">إنذارات جديدة</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-bell"></i>
            </div>
        </div>
    </div>

    <div class="guide-card">
        <div class="guide-header">
            <div class="guide-icon-header">
                <i class="fas fa-info-circle"></i>
            </div>
            <h5>دليل الألوان والتقييم</h5>
        </div>
        <div class="guide-body">
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="guide-item success">
                        <div class="guide-icon"><i class="fas fa-check"></i></div>
                        <div class="guide-text">
                            <strong>أخضر - ممتاز</strong>
                            <small>لم يخطئ أو تردد بحد أقصى ترددين</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="guide-item warning">
                        <div class="guide-icon"><i class="fas fa-exclamation"></i></div>
                        <div class="guide-text">
                            <strong>برتقالي - جيد</strong>
                            <small>خطأ واحد أو من 3 إلى 5 ترددات</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="guide-item danger">
                        <div class="guide-icon"><i class="fas fa-times"></i></div>
                        <div class="guide-text">
                            <strong>أحمر - ضعيف</strong>
                            <small>خطآن أو ما يعادلهما من الترددات</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="guide-item dark">
                        <div class="guide-icon"><i class="fas fa-ban"></i></div>
                        <div class="guide-text">
                            <strong>أسود - راسب</strong>
                            <small>خطآن + تردد = رسوب</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="guide-legend">
                <div class="legend-item">
                    <span class="badge bg-success">مفصل 3 و 6</span>
                    <small>الرسوب أقل من 90%</small>
                </div>
                <div class="legend-item">
                    <span class="badge bg-warning text-dark">مفصل 10 و 15</span>
                    <small>الرسوب أقل من 80%</small>
                </div>
                <div class="legend-item">
                    <span class="badge bg-danger">مفصل 20 و 25 و 30</span>
                    <small>الرسوب أقل من 75%</small>
                </div>
            </div>
            <small class="guide-note"><i class="fas fa-calculator me-1"></i>خطأ = -3 علامات | كل 3 ترددات = -1 علامة</small>
        </div>
    </div>

    @foreach($studentsData as $data)
    @php $student = $data['student']; @endphp
    <div class="student-card">
        <div class="student-card-header">
            <div class="student-info">
                <div class="student-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="student-details">
                    <h5>{{ $student->user->name }}</h5>
                    <span class="level-badge">{{ $student->level->name ?? 'غير محدد' }}</span>
                </div>
            </div>
            <a href="{{ route('parent.student.detail', $student->id) }}" class="view-details-btn">
                <i class="fas fa-eye"></i>
                <span>عرض التفاصيل</span>
            </a>
        </div>
        <div class="student-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="metric-section">
                        <div class="metric-header">
                            <div class="metric-icon-wrapper">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <span>الحضور هذا الشهر</span>
                        </div>
                        <div class="metric-grid">
                            <div class="metric-box success">
                                <div class="metric-value">{{ $data['attendance']['present'] }}</div>
                                <div class="metric-label">حاضر</div>
                            </div>
                            <div class="metric-box danger">
                                <div class="metric-value">{{ $data['attendance']['absent'] }}</div>
                                <div class="metric-label">غائب</div>
                            </div>
                            <div class="metric-box warning">
                                <div class="metric-value">{{ $data['attendance']['late'] }}</div>
                                <div class="metric-label">متأخر</div>
                            </div>
                            <div class="metric-box info">
                                <div class="metric-value">{{ $data['attendance']['excused'] }}</div>
                                <div class="metric-label">بعذر</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="metric-section">
                        <div class="metric-header">
                            <div class="metric-icon-wrapper">
                                <i class="fas fa-microphone"></i>
                            </div>
                            <span>التسميع هذا الشهر</span>
                        </div>
                        <div class="metric-grid">
                            <div class="metric-box primary">
                                <div class="metric-value">{{ $data['sessions']['total'] }}</div>
                                <div class="metric-label">جلسات</div>
                            </div>
                            <div class="metric-box success">
                                <div class="metric-value">{{ $data['sessions']['passed'] }}</div>
                                <div class="metric-label">ناجح</div>
                            </div>
                            <div class="metric-box danger">
                                <div class="metric-value">{{ $data['sessions']['failed'] }}</div>
                                <div class="metric-label">راسب</div>
                            </div>
                            <div class="metric-box purple">
                                <div class="metric-value">{{ $data['sessions']['total'] > 0 ? round($data['sessions']['passed']/$data['sessions']['total']*100) : 100 }}%</div>
                                <div class="metric-label">نسبة</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($data['recent_warnings']->count() > 0)
            <div class="warnings-section">
                <div class="warnings-header">
                    <div class="warnings-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <span>إنذارات جديدة ({{ $data['recent_warnings']->count() }})</span>
                </div>
                <div class="warnings-list">
                    @foreach($data['recent_warnings'] as $warning)
                    <div class="warning-item">
                        <div class="warning-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="warning-content">
                            <span class="warning-title">{{ $warning->title }}</span>
                            <small class="warning-date">{{ $warning->issue_date->format('d/m/Y') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endforeach

    @if(count($studentsData) === 0)
    <div class="empty-state">
        <div class="empty-illustration">
            <i class="fas fa-users"></i>
        </div>
        <h5>لا يوجد طلاب مرتبطون بحسابك</h5>
        <p>يرجى التواصل مع إدارة الجمعية لإضافة الأبناء</p>
    </div>
    @endif
</div>

<style>
.portal-container {
    max-width: 1400px;
    margin: 0 auto;
}

.welcome-section {
    margin-bottom: 30px;
}

.welcome-wrapper {
    background: linear-gradient(135deg, #182B48 0%, #1a3356 50%, #243656 100%);
    border-radius: 20px;
    padding: 35px 40px;
    position: relative;
    overflow: hidden;
}

.welcome-wrapper::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(212,160,48,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.welcome-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.welcome-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(circle at 10% 20%, rgba(212,160,48,0.05) 0%, transparent 50%),
                      radial-gradient(circle at 90% 80%, rgba(212,160,48,0.05) 0%, transparent 50%);
    pointer-events: none;
}

.welcome-content {
    position: relative;
    z-index: 1;
}

.welcome-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(212,160,48,0.15);
    color: #d4a030;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 15px;
}

.welcome-title {
    color: #fff;
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 8px 0;
}

.welcome-subtitle {
    color: rgba(255,255,255,0.7);
    font-size: 15px;
    margin: 0;
}

.date-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255,255,255,0.08);
    padding: 15px 20px;
    border-radius: 14px;
    border: 1px solid rgba(212,160,48,0.15);
    justify-content: flex-end;
    position: relative;
    z-index: 1;
}

.date-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}

.date-info {
    display: flex;
    flex-direction: column;
    text-align: left;
}

.date-label {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.date-value {
    color: #d4a030;
    font-weight: 700;
    font-size: 14px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card {
    position: relative;
    padding: 28px;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 18px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
}

.stat-card.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card.success { background: linear-gradient(135deg, #16835a 0%, #27ae60 100%); }
.stat-card.warning { background: linear-gradient(135deg, #d48806 0%, #f39c12 100%); }
.stat-card.purple { background: linear-gradient(135deg, #7d3c98 0%, #9b59b6 100%); }

.stat-glow {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
}

.stat-icon-wrapper {
    position: relative;
    z-index: 1;
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
}

.stat-content {
    flex: 1;
    position: relative;
    z-index: 1;
}

.stat-number {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    margin-bottom: 6px;
}

.stat-label {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    font-weight: 500;
}

.stat-decoration {
    position: absolute;
    bottom: 12px;
    left: 18px;
    font-size: 45px;
    opacity: 0.1;
    color: #fff;
}

.guide-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
    margin-bottom: 30px;
}

.guide-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.guide-icon-header {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.guide-header h5 {
    color: #182B48;
    font-size: 17px;
    font-weight: 700;
    margin: 0;
}

.guide-body {
    padding: 28px;
}

.guide-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px;
    border-radius: 14px;
    color: #fff;
}

.guide-item.success { background: linear-gradient(135deg, #16835a, #27ae60); }
.guide-item.warning { background: linear-gradient(135deg, #d48806, #f39c12); }
.guide-item.danger { background: linear-gradient(135deg, #a82e1c, #c0392b); }
.guide-item.dark { background: linear-gradient(135deg, #2c3e50, #34495e); }

.guide-icon {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.guide-text {
    display: flex;
    flex-direction: column;
}

.guide-text strong {
    font-size: 14px;
    font-weight: 700;
}

.guide-text small {
    font-size: 11px;
    opacity: 0.9;
}

.guide-legend {
    display: flex;
    gap: 20px;
    margin-top: 20px;
    padding: 18px;
    background: #f8f9fa;
    border-radius: 12px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.legend-item small {
    color: #7f8c8d;
    font-size: 12px;
}

.guide-note {
    display: block;
    margin-top: 15px;
    color: #95a5a6;
    font-size: 12px;
}

.student-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
    margin-bottom: 25px;
    transition: all 0.3s ease;
}

.student-card:hover {
    box-shadow: 0 8px 35px rgba(0,0,0,0.08);
}

.student-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(26,51,86,0.03));
    border-bottom: 1px solid #f0f4f8;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 18px;
}

.student-avatar {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 22px;
}

.student-details h5 {
    color: #182B48;
    font-size: 19px;
    font-weight: 700;
    margin: 0 0 6px 0;
}

.level-badge {
    display: inline-block;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    color: #182B48;
    padding: 5px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.view-details-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 22px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.view-details-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(24,43,72,0.35);
    color: #fff;
}

.student-card-body {
    padding: 28px;
}

.metric-section {
    margin-bottom: 18px;
}

.metric-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    color: #182B48;
    font-weight: 700;
    font-size: 16px;
}

.metric-icon-wrapper {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

@media (max-width: 576px) { .metric-grid { grid-template-columns: repeat(2, 1fr); } }

.metric-box {
    padding: 18px;
    border-radius: 14px;
    text-align: center;
    color: #fff;
}

.metric-box.success { background: linear-gradient(135deg, #16835a, #27ae60); }
.metric-box.danger { background: linear-gradient(135deg, #a82e1c, #c0392b); }
.metric-box.warning { background: linear-gradient(135deg, #d48806, #f39c12); color: #fff; }
.metric-box.info { background: linear-gradient(135deg, #2475a8, #3498db); }
.metric-box.primary { background: linear-gradient(135deg, #182B48, #2A4570); }
.metric-box.purple { background: linear-gradient(135deg, #7d3c98, #9b59b6); }

.metric-value {
    font-size: 28px;
    font-weight: 800;
}

.metric-label {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 6px;
}

.warnings-section {
    margin-top: 22px;
    padding: 22px;
    background: linear-gradient(135deg, rgba(243,156,18,0.06), rgba(230,126,34,0.06));
    border-radius: 14px;
    border: 1px solid rgba(243,156,18,0.12);
}

.warnings-header {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #d48806;
    font-weight: 700;
    margin-bottom: 16px;
    font-size: 15px;
}

.warnings-icon {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #d48806, #f39c12);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

.warnings-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.warning-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.warning-icon {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #d48806, #f39c12);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

.warning-content {
    flex: 1;
}

.warning-title {
    font-weight: 600;
    color: #182B48;
    display: block;
    font-size: 14px;
}

.warning-date {
    color: #95a5a6;
    font-size: 12px;
}

.empty-state {
    text-align: center;
    padding: 70px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
}

.empty-illustration {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 40px;
    color: #d4a030;
}

.empty-state h5 {
    color: #182B48;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

.empty-state p {
    color: #95a5a6;
}
</style>

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
@endsection