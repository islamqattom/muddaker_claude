@extends('layouts.app')

@section('title', 'لوحة تحكم الطالب')
@section('page-title', 'لوحتي الشخصية')
@section('role-label', 'طالب')

@section('sidebar-menu')
    <span class="nav-section-title">الرئيسية</span>
    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحتي الشخصية
    </a>
    <span class="nav-section-title mt-2">المتابعة</span>
    <a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
        <i class="fas fa-microphone"></i> جلسات التسميع
    </a>
    <a href="{{ route('student.attendance.index') }}" class="nav-link {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> سجل حضوري
    </a>
    <a href="{{ route('student.reports.index') }}" class="nav-link {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i> تقاريري
    </a>
    <a href="{{ route('student.warnings.index') }}" class="nav-link {{ request()->routeIs('student.warnings.*') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle"></i> الإنذارات
    </a>
    <span class="nav-section-title mt-2">المساعدة</span>
    <a href="{{ route('student.memorization') }}" class="nav-link {{ request()->routeIs('student.memorization') ? 'active' : '' }}">
        <i class="fas fa-book-open"></i> طرق الحفظ
    </a>
    <span class="nav-section-title mt-2">الإعدادات</span>
    <a href="{{ route('student.profile.index') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
        <i class="fas fa-user-cog"></i> ملفي الشخصي
    </a>
@endsection

@section('content')
<!-- Welcome Banner -->
<div class="card mb-4" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff; border:none;">
    <div class="card-body py-3 px-3">
        <div class="row align-items-center">
            <div class="col-8">
                <h5 class="mb-1">مرحباً {{ $student->user->name }}</h5>
                <p class="mb-0 opacity-75" style="font-size:13px;">{{ $student->level->name ?? '' }}</p>
            </div>
            <div class="col-4 text-end">
                <div style="font-size: 2rem; opacity: 0.3;"><i class="fas fa-quran"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-success fw-bold" style="font-size:1.5rem;">{{ $attendanceStats['present'] }}</div>
            <small class="text-muted">حضور</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-danger fw-bold" style="font-size:1.5rem;">{{ $attendanceStats['absent'] }}</div>
            <small class="text-muted">غياب</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-primary fw-bold" style="font-size:1.5rem;">{{ $sessionStats['total'] }}</div>
            <small class="text-muted">جلسات</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-warning fw-bold" style="font-size:1.5rem;">{{ $sessionStats['new_memorization'] }}</div>
            <small class="text-muted">صفحات</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Sessions -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6><i class="fas fa-history me-2"></i>آخر جلسات التسميع</h6>
                <a href="{{ route('student.sessions.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr><th>النوع</th><th>الجزء</th><th>الصفحات</th><th>الأخطاء</th><th>النتيجة</th><th>التاريخ</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentSessions as $session)
                            <tr>
                                <td><span class="badge bg-info text-dark">{{ $session->type_label }}</span></td>
                                <td>{{ $session->part ? 'الجزء '.$session->part->part_number : '-' }}</td>
                                <td>{{ $session->total_pages }}</td>
                                <td>
                                    <span class="text-danger">{{ $session->total_errors }}</span>
                                    <small class="text-muted">/ {{ $session->total_hesitations }}ت</small>
                                </td>
                                <td><span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span></td>
                                <td><small>{{ $session->session_date->format('d/m/Y') }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-3 text-muted">لا توجد جلسات بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Warnings -->
        @if($recentWarnings->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>إنذارات جديدة</h6>
            </div>
            <div class="card-body p-0">
                @foreach($recentWarnings as $warning)
                <div class="p-3 border-bottom">
                    <div class="fw-bold" style="font-size:13px;">{{ $warning->title }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ $warning->issue_date->format('d/m/Y') }}</div>
                    <span class="badge bg-danger" style="font-size:11px;">{{ $warning->type_label }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Memorization Tips Card -->
        <div class="card" style="background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff;">
            <div class="card-body text-center p-4">
                <i class="fas fa-book-open fa-3x mb-3 opacity-75"></i>
                <h6 class="mb-2">تعلم طريقة الحفظ المتقن</h6>
                <p style="font-size:13px;opacity:0.9;" class="mb-3">اكتشف طريقة الحفظ الصحيحة في 45-60 دقيقة</p>
                <a href="{{ route('student.memorization') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>عرض طرق الحفظ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
