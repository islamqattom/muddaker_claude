@extends('layouts.app')

@section('title', 'بيانات الطالب')
@section('page-title', 'بيانات الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.students.index') }}">الطلاب</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name }}</li>
@endsection

@section('content')

<x-page-hero icon="fas fa-user-graduate">
    <h1 class="page-banner-title">بيانات الطالب</h1>
</x-page-hero>

<div class="quranic-verse-header mb-4">
    <div class="verse-decoration"></div>
    <p class="quranic-verse">﴿ اقْرَأْ بِاسْمِ رَبِّكَ الَّذِي خَلَقَ ﴾</p>
    <div class="verse-decoration"></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="student-profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="{{ $student->user->avatar_url }}" alt="Avatar">
                </div>
                <div class="profile-info">
                    <h4>{{ $student->user->name }}</h4>
                    <div class="profile-meta">
                        <span class="meta-item">
                            <i class="fas fa-id-card"></i>
                            {{ $student->student_number }}
                        </span>
                        @if($student->level)
                            <span class="level-badge">{{ $student->level->name }}</span>
                        @endif
                    </div>
                </div>
                <div class="profile-actions-header">
                    <a href="{{ route('supervisor.students.edit', $student->id) }}" class="action-btn edit" title="تعديل">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value {{ $student->attendance_rate < 50 ? 'text-danger' : 'text-success' }}">{{ $student->attendance_rate }}%</span>
                        <span class="stat-label">نسبة الحضور</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon primary">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value text-info">{{ $student->pass_rate }}%</span>
                        <span class="stat-label">نسبة النجاح</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon warning">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $student->recitationSessions->count() }}</span>
                        <span class="stat-label">جلسات التسميع</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $student->warnings->count() }}</span>
                        <span class="stat-label">الإنذارات</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-grid-card">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">الرقم الوطني</span>
                    <span class="info-value">{{ $student->national_id ?? '-' }}</span>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">تاريخ الميلاد</span>
                    <span class="info-value">{{ $student->birth_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">رقم الهاتف</span>
                    <span class="info-value">{{ $student->phone ?? '-' }}</span>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">العنوان</span>
                    <span class="info-value">{{ $student->address ?? '-' }}</span>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">تاريخ التسجيل</span>
                    <span class="info-value">{{ $student->enrollment_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-book-quran"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">الأجزاء المحفوظة</span>
                    <span class="info-value">{{ $student->memorized_parts ?? 0 }}</span>
                </div>
            </div>
        </div>

        <x-student-progress-chart :student-id="$student->id" :velocity-data="$velocityData" />

        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <x-page-difficulty-heatmap :student-id="$student->id" :heatmap-data="$heatmapData" />
            </div>
            <div class="col-md-4">
                <x-recent-session-timeline :sessions="$recentSessions" />
            </div>
        </div>

        <div class="section-card mb-4">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h5>سجل الحضور الأخير</h5>
            </div>
            <div class="section-body">
                @if($student->attendances->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-calendar-times"></i></div>
                        <p>لا يوجد سجل حضور</p>
                    </div>
                @else
                    <div class="attendance-grid">
                        @foreach($student->attendances->take(12) as $attendance)
                            <div class="attendance-item {{ $attendance->status }}">
                                <span class="attendance-date">{{ $attendance->date->format('d/m') }}</span>
                                <span class="attendance-status">
                                    @if($attendance->status === 'present')
                                        <i class="fas fa-check text-success"></i>
                                    @elseif($attendance->status === 'absent')
                                        <i class="fas fa-times text-danger"></i>
                                    @elseif($attendance->status === 'late')
                                        <i class="fas fa-clock text-warning"></i>
                                    @elseif($attendance->status === 'excused')
                                        <i class="fas fa-file-alt text-info"></i>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="section-card mb-4">
            <div class="section-header">
                <div class="section-icon secondary">
                    <i class="fas fa-microphone"></i>
                </div>
                <h5>جلسات التسميع الأخيرة</h5>
            </div>
            <div class="section-body p-0">
                @if($student->recitationSessions->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-book-open"></i></div>
                        <p>لا يوجد جلسات تسميع</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الجزء</th>
                                    <th>من صفحة</th>
                                    <th>إلى صفحة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->recitationSessions->take(10) as $session)
                                <tr>
                                    <td>{{ $session->session_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($session->part)
                                            <span class="part-badge">{{ $session->part->name }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span class="page-num">{{ $session->start_page }}</span></td>
                                    <td><span class="page-num">{{ $session->end_page }}</span></td>
                                    <td>
                                        <span class="status-badge {{ $session->status_color }}">{{ $session->status_label }}</span>
                                    </td>
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
                <div class="section-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5>الإنذارات</h5>
            </div>
            <div class="section-body p-0">
                @if($student->warnings->isEmpty())
                    <div class="empty-state success">
                        <div class="empty-icon success"><i class="fas fa-check-circle"></i></div>
                        <p>لا يوجد إنذارات - أحسنت!</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>رقم الإنذار</th>
                                    <th>النوع</th>
                                    <th>العنوان</th>
                                    <th>التاريخ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->warnings as $warning)
                                <tr>
                                    <td><code class="warning-code">{{ $warning->warning_number }}</code></td>
                                    <td>
                                        @if($warning->type === 'attendance')
                                            <span class="type-badge attendance">حضور</span>
                                        @elseif($warning->type === 'recitation')
                                            <span class="type-badge recitation">تسميع</span>
                                        @else
                                            <span class="type-badge general">عام</span>
                                        @endif
                                    </td>
                                    <td>{{ $warning->title }}</td>
                                    <td>{{ $warning->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('supervisor.warnings.show', $warning->id) }}" class="action-btn view" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

    <div class="col-lg-4">
        <div class="actions-card sticky-top" style="top: 20px;">
            <div class="actions-header">
                <div class="actions-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h5>إجراءات سريعة</h5>
            </div>
            <div class="actions-body">
                <a href="{{ route('supervisor.students.edit', $student->id) }}" class="action-item">
                    <div class="action-icon-main edit">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="action-text">
                        <span class="action-title">تعديل البيانات</span>
                        <span class="action-desc">تعديل معلومات الطالب</span>
                    </div>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="{{ route('supervisor.recitation.index') }}?student_id={{ $student->id }}" class="action-item">
                    <div class="action-icon-main primary">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <div class="action-text">
                        <span class="action-title">تسميع جديد</span>
                        <span class="action-desc">بدء جلسة تسميع</span>
                    </div>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="{{ route('supervisor.attendance.index') }}?student_id={{ $student->id }}" class="action-item">
                    <div class="action-icon-main success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="action-text">
                        <span class="action-title">تسجيل حضور</span>
                        <span class="action-desc">تسجيل حضور اليوم</span>
                    </div>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="{{ route('supervisor.warnings.create') }}?student_id={{ $student->id }}" class="action-item">
                    <div class="action-icon-main warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="action-text">
                        <span class="action-title">إنذار جديد</span>
                        <span class="action-desc">إصدار إنذار للطالب</span>
                    </div>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="{{ route('supervisor.reports.student', $student->id) }}" class="action-item">
                    <div class="action-icon-main info">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="action-text">
                        <span class="action-title">عرض التقارير</span>
                        <span class="action-desc">تقارير تفصيلية</span>
                    </div>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <div class="action-divider"></div>
                <button type="button" class="action-item danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <div class="action-icon-main danger">
                        <i class="fas fa-trash"></i>
                    </div>
                    <div class="action-text">
                        <span class="action-title">حذف الطالب</span>
                        <span class="action-desc">حذف جميع البيانات</span>
                    </div>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <a href="{{ route('supervisor.students.index') }}" class="action-item">
                    <div class="action-icon-main secondary">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-text">
                        <span class="action-title">العودة للقائمة</span>
                        <span class="action-desc">عرض جميع الطلاب</span>
                    </div>
                    <i class="fas fa-chevron-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon-wrapper">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-title">حذف الطالب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف الطالب <strong>{{ $student->user->name }}</strong>؟</p>
                <p class="text-danger small mb-0"><i class="fas fa-warning me-1"></i>سيتم حذف جميع بيانات الطالب بشكل نهائي.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('supervisor.students.destroy', $student->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>حذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.quranic-verse-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.05));
    border-radius: 13px;
}

.quranic-verse-header .verse-decoration {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a030, transparent);
    max-width: 80px;
}

.quranic-verse-header .quranic-verse {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: #182B48;
    margin: 0;
}

.student-profile-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    margin-bottom: 16px;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.04));
    border-bottom: 1px solid #f0f4f8;
}

.profile-avatar {
    width: 72px;
    height: 72px;
    border-radius: 13px;
    overflow: hidden;
    border: 3px solid #d4a030;
    flex-shrink: 0;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info {
    flex: 1;
}

.profile-info h4 {
    color: #182B48;
    font-weight: 700;
    font-size: 18px;
    margin: 0 0 8px 0;
}

.profile-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #7f8c8d;
    font-size: 12.5px;
    font-family: monospace;
}

.meta-item i {
    color: #d4a030;
}

.profile-actions-header {
    display: flex;
    gap: 8px;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    padding: 14px 16px;
}

@media (max-width: 768px) {
    .profile-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .profile-header {
        flex-wrap: wrap;
    }
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #fafbfc;
    border-radius: 13px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: #f0f4f8;
    transform: translateY(-2px);
}

.stat-icon.success {
    background: linear-gradient(135deg, rgba(39,174,96,0.15), rgba(46,204,113,0.15));
    color: #27ae60;
}

.stat-icon.primary {
    background: linear-gradient(135deg, rgba(52,152,219,0.15), rgba(41,128,185,0.15));
    color: #3498db;
}

.stat-icon.warning {
    background: linear-gradient(135deg, rgba(243,156,18,0.15), rgba(230,126,34,0.15));
    color: #f39c12;
}

.stat-icon.danger {
    background: linear-gradient(135deg, rgba(192,57,43,0.15), rgba(231,76,60,0.15));
    color: #c0392b;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #182B48;
    line-height: 1.2;
}

.stat-value.text-success { color: #27ae60; }
.stat-value.text-danger { color: #c0392b; }
.stat-value.text-info { color: #3498db; }

.info-grid-card {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

@media (max-width: 992px) {
    .info-grid-card {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .info-grid-card {
        grid-template-columns: 1fr;
    }
}

.info-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.info-icon {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
    flex-shrink: 0;
}

.info-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.info-label {
    font-size: 11.5px;
    color: #7f8c8d;
    font-weight: 500;
}

.info-value {
    font-size: 13px;
    color: #182B48;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.section-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.04));
    border-bottom: 1px solid #f0f4f8;
}

.section-icon {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.section-icon.secondary {
    background: linear-gradient(135deg, #B4802C, #d4a030);
}

.section-icon.warning {
    background: linear-gradient(135deg, #c0392b, #e74c3c);
}

.section-header h5 {
    color: #182B48;
    font-weight: 700;
    font-size: 15px;
    margin: 0;
}

.section-body {
    padding: 16px;
}

.empty-icon.success {
    background: linear-gradient(135deg, rgba(39,174,96,0.1), rgba(46,204,113,0.1));
    color: #27ae60;
}

.empty-state.success p {
    color: #27ae60;
    font-weight: 600;
}

.attendance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
    gap: 10px;
}

.attendance-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 8px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.2s;
}

.attendance-item:hover {
    transform: scale(1.05);
}

.attendance-item.present {
    background: linear-gradient(135deg, rgba(39,174,96,0.1), rgba(46,204,113,0.1));
}

.attendance-item.absent {
    background: linear-gradient(135deg, rgba(192,57,43,0.1), rgba(231,76,60,0.1));
}

.attendance-item.late {
    background: linear-gradient(135deg, rgba(243,156,18,0.1), rgba(230,126,34,0.1));
}

.attendance-item.excused {
    background: linear-gradient(135deg, rgba(52,152,219,0.1), rgba(41,128,185,0.1));
}

.attendance-date {
    font-size: 12px;
    color: #182B48;
    font-weight: 600;
    margin-bottom: 4px;
}

.attendance-status {
    font-size: 14px;
}

.part-badge {
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 600;
}

.page-num {
    font-family: monospace;
    background: #f0f4f8;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 12.5px;
}

.status-badge.success {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: #fff;
}

.status-badge.danger {
    background: linear-gradient(135deg, #c0392b, #e74c3c);
    color: #fff;
}

.status-badge.pending {
    background: linear-gradient(135deg, #7f8c8d, #95a5a6);
    color: #fff;
}

.warning-code {
    background: #f0f4f8;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 12.5px;
    color: #182B48;
}

.type-badge {
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 600;
}

.type-badge.attendance {
    background: rgba(243,156,18,0.15);
    color: #d68910;
}

.type-badge.recitation {
    background: rgba(52,152,219,0.15);
    color: #2980b9;
}

.type-badge.general {
    background: rgba(127,140,141,0.15);
    color: #7f8c8d;
}

.actions-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
}

.actions-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.04));
    border-bottom: 1px solid #f0f4f8;
}

.actions-icon {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.actions-header h5 {
    color: #182B48;
    font-weight: 700;
    font-size: 15px;
    margin: 0;
}

.actions-body {
    padding: 14px;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 9px;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: right;
}

.action-item:hover {
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.04));
}

.action-item.danger:hover {
    background: linear-gradient(135deg, rgba(192,57,43,0.08), rgba(231,76,60,0.08));
}

.action-icon-main {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.action-icon-main.edit {
    background: linear-gradient(135deg, rgba(243,156,18,0.15), rgba(243,156,18,0.1));
    color: #f39c12;
}

.action-icon-main.primary {
    background: linear-gradient(135deg, rgba(24,43,72,0.15), rgba(42,69,112,0.1));
    color: #182B48;
}

.action-icon-main.success {
    background: linear-gradient(135deg, rgba(39,174,96,0.15), rgba(46,204,113,0.1));
    color: #27ae60;
}

.action-icon-main.warning {
    background: linear-gradient(135deg, rgba(243,156,18,0.15), rgba(230,126,34,0.1));
    color: #e67e22;
}

.action-icon-main.info {
    background: linear-gradient(135deg, rgba(52,152,219,0.15), rgba(41,128,185,0.1));
    color: #3498db;
}

.action-icon-main.danger {
    background: linear-gradient(135deg, rgba(192,57,43,0.15), rgba(231,76,60,0.1));
    color: #c0392b;
}

.action-icon-main.secondary {
    background: linear-gradient(135deg, rgba(127,140,141,0.15), rgba(149,165,166,0.1));
    color: #7f8c8d;
}

.action-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    text-align: right;
}

.action-title {
    font-size: 14px;
    font-weight: 600;
    color: #182B48;
}

.action-desc {
    font-size: 11.5px;
    color: #7f8c8d;
}

.action-item > i {
    color: #bdc3c7;
    font-size: 12px;
}

.action-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #e9ecef, transparent);
    margin: 10px 0;
}

.modal-header {
    background: linear-gradient(135deg, rgba(24,43,72,0.05), rgba(42,69,112,0.05));
    border-bottom: 2px solid #f0f4f8;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-icon-wrapper {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, rgba(192,57,43,0.1), rgba(231,76,60,0.1));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #c0392b;
    font-size: 16px;
}

.modal-header .modal-title {
    color: #182B48;
    font-weight: 700;
    font-size: 15px;
    margin: 0;
}

.modal-body {
    padding: 16px;
}

.modal-body p {
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 12px;
}

.modal-footer {
    padding: 12px 16px;
    background: #fafbfc;
    border-top: 1px solid #f0f4f8;
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}
</style>

@endsection