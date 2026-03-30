@extends('layouts.app')

@section('title', 'لوحة تحكم الطالب')
@section('page-title', 'لوحتي الشخصية')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
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

<!-- Color Guide -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>نظام الألوان والتقييم</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded text-white" style="background:#27ae60;">
                            <div class="fw-bold fs-5 mb-1">🟢 أخضر - ممتاز</div>
                            <div style="font-size:13px;">لم يخطئ الطالب أو تردد بحد أقصى ترددين</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded text-dark" style="background:#f39c12;">
                            <div class="fw-bold fs-5 mb-1">🟠 برتقالي - جيد</div>
                            <div style="font-size:13px;">خطأ واحد أو من 3 إلى 5 ترددات</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded text-white" style="background:#c0392b;">
                            <div class="fw-bold fs-5 mb-1">🔴 أحمر - ضعيف</div>
                            <div style="font-size:13px;">خطآن أو ما يعادلهما من الترددات</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="p-3 rounded text-white" style="background:#2c3e50;">
                            <div class="fw-bold fs-5 mb-1">⚫ أسود - راسب</div>
                            <div style="font-size:13px;">خطآن + تردد واحد أو أكثر = رسوب</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-primary">المفاصل وعلاماتها:</h6>
                    <table class="table table-sm mb-0">
                        <tr><td><strong>مفصل 3 و6</strong></td><td>علامة الرسوب أقل من: <span class="badge bg-success">90%</span></td></tr>
                        <tr><td><strong>مفصل 10 و15</strong></td><td>علامة الرسوب أقل من: <span class="badge bg-warning text-dark">80%</span></td></tr>
                        <tr><td><strong>مفصل 20 و25 و30</strong></td><td>علامة الرسوب أقل من: <span class="badge bg-danger">75%</span></td></tr>
                    </table>
                    <small class="text-muted mt-2 d-block">خطأ = -3 علامات | كل 3 ترددات = -1 علامة</small>
                </div>
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
            <div class="text-warning fw-bold" style="font-size:1.5rem;">{{ $sessionStats['saved_parts'] }}</div>
            <small class="text-muted">الأجزاء المحفوظة</small>
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
                                <td>
                                    @if($session->type === 'mafsil')
                                        @php
                                            $partNumbers = $session->pages->pluck('page_number')->unique()->sort()->values();
                                        @endphp
                                        {{ $partNumbers->isNotEmpty() ? $partNumbers->map(fn($num) => 'الجزء '.$num)->join('، ') : '-' }}
                                    @else
                                        {{ $session->part ? 'الجزء '.$session->part->part_number : '-' }}
                                    @endif
                                </td>
                                <td>{{ $session->type === 'mafsil' ? '' : $session->total_pages }}</td>
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
