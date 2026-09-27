@extends('layouts.app')
@section('title', 'بوابة ولي الأمر')
@section('page-title', 'متابعة أبنائي')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('content')

<div class="portal-container">
    <div class="welcome-section">
        <div class="welcome-wrapper">
            <div class="row align-items-center g-3">
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
        <div class="stat-card-modern primary">
            <div class="stat-card-bg"></div>
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ count($studentsData) }}</div>
                <div class="stat-label">عدد الأبناء</div>
            </div>
            <div class="stat-decoration"><i class="fas fa-book-quran"></i></div>
        </div>

        <div class="stat-card-modern success">
            <div class="stat-card-bg"></div>
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                @php $totalPresent = array_sum(array_column(array_column($studentsData, 'attendance'), 'present')); @endphp
                <div class="stat-number">{{ $totalPresent }}</div>
                <div class="stat-label">حضور هذا الشهر</div>
            </div>
            <div class="stat-decoration"><i class="fas fa-calendar-check"></i></div>
        </div>

        <div class="stat-card-modern secondary">
            <div class="stat-card-bg"></div>
            <div class="stat-icon"><i class="fas fa-microphone"></i></div>
            <div class="stat-content">
                @php $totalSessions = array_sum(array_column(array_column($studentsData, 'sessions'), 'total')); @endphp
                <div class="stat-number">{{ $totalSessions }}</div>
                <div class="stat-label">جلسات التسميع</div>
            </div>
            <div class="stat-decoration"><i class="fas fa-quran"></i></div>
        </div>

        <div class="stat-card-modern warning">
            <div class="stat-card-bg"></div>
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-content">
                @php $totalWarnings = array_sum(array_map(fn($w) => $w->count(), array_column($studentsData, 'recent_warnings'))); @endphp
                <div class="stat-number">{{ $totalWarnings }}</div>
                <div class="stat-label">إنذارات جديدة</div>
            </div>
            <div class="stat-decoration"><i class="fas fa-bell"></i></div>
        </div>
    </div>

    <details class="guide-card dash-collapse">
        <summary>
            <span class="card-title"><i class="fas fa-info-circle"></i><span>دليل الألوان والتقييم</span></span>
        </summary>
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
    </details>

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
            <div class="row g-3">
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
            <div class="warnings-section mt-3">
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
