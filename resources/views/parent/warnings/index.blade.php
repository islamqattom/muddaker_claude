@extends('layouts.app')
@section('title', 'الإنذارات')
@section('page-title', 'الإنذارات')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('parent.sessions.index') }}" class="nav-link"><i class="fas fa-microphone"></i> جلسات التسميع</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link"><i class="fas fa-calendar-check"></i> سجل الحضور</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link active">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge bg-danger">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> التقارير</a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإنذارات</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            سجل الإنذارات
        </h6>
    </div>
    <div class="card-body p-0">
        @if($warnings->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                <p>لا توجد إنذارات مسجلة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>نوع الإنذار</th>
                            <th>العنوان</th>
                            <th>التاريخ</th>
                            <th>اليوم</th>
                            <th>الرقم</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warnings as $index => $warning)
                        <tr class="{{ !$warning->is_read_by_parent ? 'table-warning' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:36px;height:36px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;">
                                        {{ mb_substr($warning->student->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $warning->student->user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($warning->type === 'attendance')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-calendar-times me-1"></i>حضور
                                    </span>
                                @elseif($warning->type === 'recitation')
                                    <span class="badge bg-info">
                                        <i class="fas fa-microphone me-1"></i>تسميع
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-exclamation-circle me-1"></i>عام
                                    </span>
                                @endif
                            </td>
                            <td>{{ $warning->title }}</td>
                            <td>{{ $warning->issue_date->format('d/m/Y') }}</td>
                            <td><span class="badge bg-secondary">{{ $warning->issue_day }}</span></td>
                            <td><code class="small">{{ $warning->warning_number }}</code></td>
                            <td>
                                @if($warning->is_read_by_parent)
                                    <span class="badge bg-success">مقروء</span>
                                @else
                                    <span class="badge bg-danger">جديد</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('parent.warnings.show', $warning->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> عرض
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
@endsection
