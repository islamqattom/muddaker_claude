@extends('layouts.app')

@section('title', 'تفاصيل الحدث')
@section('page-title', 'تفاصيل الحدث الجماعي')
@section('role-label', 'طالب')

@section('sidebar-menu')
    <span class="nav-section-title">الرئيسية</span>
    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحتي الشخصية
    </a>
    <span class="nav-section-title mt-2">القرآن الكريم</span>
    <a href="{{ route('student.quran') }}" class="nav-link {{ request()->routeIs('student.quran*') ? 'active' : '' }}">
        <i class="fas fa-book-quran"></i> قراءة القرآن
    </a>
    <span class="nav-section-title mt-2">المتابعة</span>
    <a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
        <i class="fas fa-microphone"></i> جلسات التسميع
    </a>
    <span class="nav-section-title mt-2">التقدم</span>
    <a href="{{ route('student.progress') }}" class="nav-link {{ request()->routeIs('student.progress*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> تقدمي
    </a>
    <a href="{{ route('student.events.index') }}" class="nav-link {{ request()->routeIs('student.events*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> أحداث جماعية
    </a>
@endsection

@section('content')
<!-- Event Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">{{ $event->name }}</h4>
                <p class="text-muted mb-1">
                    <i class="fas fa-user-tie me-1"></i>
                    المشرف: {{ $event->supervisor?->name ?? '-' }}
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar me-1"></i>
                    {{ $event->start_date->format('Y-m-d') }} إلى {{ $event->end_date->format('Y-m-d') }}
                </p>
            </div>
            <div class="col-md-4 text-center">
                <div class="display-4 fw-bold text-primary">{{ $event->juz_number }}</div>
                <div class="text-muted">الجزء {{ $event->juz_number }}</div>
                <div class="mt-2">
                    @if($event->status === 'active')
                        <span class="badge bg-success">نشط</span>
                    @elseif($event->status === 'completed')
                        <span class="badge bg-primary">مكتمل</span>
                    @elseif($event->status === 'cancelled')
                        <span class="badge bg-danger">ملغى</span>
                    @elseif($event->status === 'draft')
                        <span class="badge bg-warning text-dark">مسودة</span>
                    @else
                        <span class="badge bg-secondary">{{ $event->status }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Collective Progress -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>التقدم الجماعي</h6>
    </div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col-4">
                <div class="text-muted small">الهدف</div>
                <div class="fw-bold">{{ $event->target_pages }} صفحة</div>
            </div>
            <div class="col-4">
                <div class="text-muted small">المكتمل</div>
                <div class="fw-bold text-success">{{ $progress['total_completed'] ?? 0 }} صفحة</div>
            </div>
            <div class="col-4">
                <div class="text-muted small">المشاركون</div>
                <div class="fw-bold">{{ $progress['participating_count'] ?? 0 }} طالب</div>
            </div>
        </div>

        <div class="progress mb-3" style="height: 30px;">
            <div class="progress-bar bg-success progress-bar-striped {{ ($progress['completion_percentage'] ?? 0) >= 100 ? 'progress-bar-animated' : '' }}"
                 style="width: {{ min(100, $progress['completion_percentage'] ?? 0) }}%">
                {{ $progress['completion_percentage'] ?? 0 }}%
            </div>
        </div>

        @if(($progress['completion_percentage'] ?? 0) >= 100)
        <div class="alert alert-success text-center mb-0">
            <i class="fas fa-trophy fa-2x me-2"></i>
            <strong>تم إكمال الحدث بنجاح!</strong>
        </div>
        @endif
    </div>
</div>

<!-- Participant Progress -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-users me-2"></i>تقدم المشاركين</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>الصفحات المكتملة</th>
                        <th>آخر صفحة</th>
                        <th>نسبة الإنجاز</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($progress['student_progress'] ?? [] as $index => $student)
                    @php $isMe = $student['student_id'] === auth()->user()?->student?->id; @endphp
                    <tr class="{{ $isMe ? 'table-primary fw-bold' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($isMe)
                                <i class="fas fa-user-check text-primary me-1"></i>
                            @endif
                            {{ $student['student_name'] }}
                        </td>
                        <td>{{ $student['pages_memorized'] }}</td>
                        <td>{{ $student['last_page_reached'] }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $student['completion_percentage'] >= 100 ? 'success' : 'primary' }}"
                                         style="width: {{ min(100, $student['completion_percentage']) }}%"></div>
                                </div>
                                <small>{{ $student['completion_percentage'] }}%</small>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if(empty($progress['student_progress']))
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">لا توجد بيانات</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="d-flex gap-2 justify-content-center">
    <a href="{{ route('student.events.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right me-1"></i>العودة
    </a>
    <a href="{{ route('student.quran.part', $event->juz_number) }}" class="btn btn-primary">
        <i class="fas fa-book-quran me-1"></i>اذهب للقرآن (الجزء {{ $event->juz_number }})
    </a>
</div>
@endsection
