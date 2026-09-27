@extends('layouts.app')

@section('title', 'لوحة تحكم الطالب')
@section('page-title', 'لوحتي الشخصية')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')

<div class="welcome-section">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
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
        <div class="col-lg-4">
            <div class="date-badge d-block text-center">
                <i class="fas fa-calendar-alt me-2"></i>
                <span id="currentDate"></span>
            </div>
        </div>
    </div>
</div>

{{-- Evaluation guide (collapsible) --}}
<details class="color-guide-card dash-collapse">
    <summary>
        <span class="card-title"><i class="fas fa-info-circle"></i><span>دليل الألوان والتقييم</span></span>
    </summary>
    <div class="guide-body">
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
</details>

<div class="stats-grid">
    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $attendanceStats['present'] }}</div>
            <div class="stat-label">حضور</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-user-check"></i></div>
    </div>

    <div class="stat-card-modern danger">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $attendanceStats['absent'] }}</div>
            <div class="stat-label">غياب</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-user-times"></i></div>
    </div>

    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-microphone"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $sessionStats['total'] }}</div>
            <div class="stat-label">جلسات</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-quran"></i></div>
    </div>

    <div class="stat-card-modern warning">
        <div class="stat-card-bg"></div>
        <div class="stat-icon"><i class="fas fa-book"></i></div>
        <div class="stat-content">
            <div class="stat-number">{{ $sessionStats['saved_parts'] }}</div>
            <div class="stat-label">الأجزاء المحفوظة</div>
        </div>
        <div class="stat-decoration"><i class="fas fa-award"></i></div>
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
                            <td>{{ $session->parts_label !== '-' ? $session->parts_label : '-' }}</td>
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
