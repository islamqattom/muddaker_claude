@extends('layouts.app')
@section('title', 'بوابة ولي الأمر')
@section('page-title', 'متابعة أبنائي')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('content')

{{-- Welcome Section --}}
<div class="welcome-section mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="welcome-card">
                <div class="welcome-icon">
                    <i class="fas fa-moon"></i>
                </div>
                <div class="welcome-content">
                    <h4>أهلاً {{ auth()->user()->name }} بارك الله فيك</h4>
                    <p>تابع تقدم أبنائك في حفظ القرآن الكريم</p>
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

{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ count($studentsData) }}</div>
            <div class="stat-label">عدد الأبناء</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-book-quran"></i>
        </div>
    </div>

    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
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

    <div class="stat-card-modern secondary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-microphone"></i>
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

    <div class="stat-card-modern warning">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
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

{{-- Color Guide --}}
<div class="dashboard-card mb-4">
    <div class="card-header-custom">
        <div class="card-title">
            <i class="fas fa-info-circle"></i>
            <span>دليل الألوان والتقييم</span>
        </div>
    </div>
    <div class="card-body-custom">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="evaluation-guide success">
                    <div class="guide-icon"><i class="fas fa-check"></i></div>
                    <div class="guide-text">
                        <strong>أخضر - ممتاز</strong>
                        <small>لم يخطئ أو تردد بحد أقصى ترددين</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="evaluation-guide warning">
                    <div class="guide-icon"><i class="fas fa-exclamation"></i></div>
                    <div class="guide-text">
                        <strong>برتقالي - جيد</strong>
                        <small>خطأ واحد أو من 3 إلى 5 ترددات</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="evaluation-guide danger">
                    <div class="guide-icon"><i class="fas fa-times"></i></div>
                    <div class="guide-text">
                        <strong>أحمر - ضعيف</strong>
                        <small>خطآن أو ما يعادلهما من الترددات</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="evaluation-guide dark">
                    <div class="guide-icon"><i class="fas fa-ban"></i></div>
                    <div class="guide-text">
                        <strong>أسود - راسب</strong>
                        <small>خطآن + تردد = رسوب</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 p-3 bg-light rounded">
            <h6 class="text-primary mb-2"><i class="fas fa-graduation-cap me-2"></i>المفاصل وعلاماتها:</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success">مفصل 3 و 6</span>
                        <small>الرسوب أقل من 90%</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark">مفصل 10 و 15</span>
                        <small>الرسوب أقل من 80%</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-danger">مفصل 20 و 25 و 30</span>
                        <small>الرسوب أقل من 75%</small>
                    </div>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="fas fa-calculator me-1"></i>خطأ = -3 علامات | كل 3 ترددات = -1 علامة</small>
        </div>
    </div>
</div>

{{-- Students Cards --}}
@foreach($studentsData as $data)
@php $student = $data['student']; @endphp
<div class="student-card mb-4">
    <div class="student-card-header">
        <div class="student-info">
            <div class="user-avatar-lg">
                <i class="fas fa-user"></i>
            </div>
            <div class="student-details">
                <h5>{{ $student->user->name }}</h5>
                <span class="level-badge">{{ $student->level->name ?? 'غير محدد' }}</span>
            </div>
        </div>
        <a href="{{ route('parent.student.detail', $student->id) }}" class="view-details-btn">
            <i class="fas fa-eye me-2"></i>التفاصيل
        </a>
    </div>
    <div class="student-card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="metric-section">
                    <div class="metric-header">
                        <i class="fas fa-calendar-check"></i>
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
                        <i class="fas fa-microphone"></i>
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
                        <div class="metric-box secondary">
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
                <i class="fas fa-exclamation-triangle"></i>
                <span>إنذارات جديدة ({{ $data['recent_warnings']->count() }})</span>
            </div>
            <div class="warnings-list">
                @foreach($data['recent_warnings'] as $warning)
                <div class="warning-item">
                    <div class="warning-icon"><i class="fas fa-bell"></i></div>
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
<div class="empty-state-card">
    <div class="empty-icon">
        <i class="fas fa-users"></i>
    </div>
    <h5>لا يوجد طلاب مرتبطون بحسابك</h5>
    <p>يرجى التواصل مع إدارة الجمعية لإضافة الأبناء</p>
</div>
@endif

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
.welcome-content h4 { color: #fff; font-size: 22px; font-weight: 700; margin: 0 0 8px 0; }
.welcome-content p { color: rgba(255,255,255,0.7); font-size: 14px; margin: 0; }

.date-badge {
    background: rgba(255,255,255,0.1);
    padding: 12px 20px;
    border-radius: 12px;
    color: #d4a030;
    font-weight: 600;
    font-size: 14px;
    border: 1px solid rgba(212,160,48,0.2);
}

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

.stat-card-modern:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.2); }
.stat-card-modern.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card-modern.secondary { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); }
.stat-card-modern.success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
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
.stat-number { font-size: 32px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 5px; }
.stat-label { font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 500; }
.stat-decoration { position: absolute; bottom: 10px; left: 15px; font-size: 40px; opacity: 0.15; color: #fff; }

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

.evaluation-guide {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 12px;
    color: #fff;
}

.evaluation-guide.success { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.evaluation-guide.warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
.evaluation-guide.danger { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.evaluation-guide.dark { background: linear-gradient(135deg, #2c3e50, #34495e); }

.guide-icon {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.guide-text { display: flex; flex-direction: column; }
.guide-text strong { font-size: 14px; }
.guide-text small { font-size: 11px; opacity: 0.9; }

.student-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.student-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(26,51,86,0.03));
    border-bottom: 1px solid #f0f4f8;
}

.student-info { display: flex; align-items: center; gap: 15px; }

.user-avatar-lg {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.student-details h5 { color: #182B48; font-size: 18px; font-weight: 700; margin: 0 0 5px 0; }

.level-badge {
    display: inline-block;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.view-details-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.view-details-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(24,43,72,0.3);
    color: #fff;
}

.student-card-body { padding: 25px; }

.metric-section { margin-bottom: 15px; }

.metric-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    color: #182B48;
    font-weight: 700;
    font-size: 15px;
}

.metric-header i { color: #d4a030; font-size: 18px; }

.metric-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

@media (max-width: 576px) { .metric-grid { grid-template-columns: repeat(2, 1fr); } }

.metric-box {
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    color: #fff;
}

.metric-box.success { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.metric-box.danger { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.metric-box.warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }
.metric-box.info { background: linear-gradient(135deg, #3498db, #2980b9); }
.metric-box.primary { background: linear-gradient(135deg, #182B48, #2A4570); }
.metric-box.secondary { background: linear-gradient(135deg, #8e44ad, #9b59b6); }

.metric-value { font-size: 24px; font-weight: 800; }
.metric-label { font-size: 11px; opacity: 0.9; margin-top: 5px; }

.warnings-section {
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(243,156,18,0.1), rgba(230,126,34,0.1));
    border-radius: 12px;
    border: 1px solid rgba(243,156,18,0.2);
}

.warnings-header {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #e67e22;
    font-weight: 700;
    margin-bottom: 15px;
}

.warnings-header i { font-size: 18px; }

.warnings-list { display: flex; flex-direction: column; gap: 10px; }

.warning-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #fff;
    border-radius: 10px;
}

.warning-icon {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #f39c12, #e67e22);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.warning-content { flex: 1; }
.warning-title { font-weight: 600; color: #182B48; display: block; }
.warning-date { color: #7f8c8d; font-size: 12px; }

.empty-state-card {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.empty-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: #d4a030;
}

.empty-state-card h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state-card p { color: #7f8c8d; }
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