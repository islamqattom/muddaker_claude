@extends('layouts.app')
@section('title', 'بوابة ولي الأمر')
@section('page-title', 'متابعة أبنائي')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}"><i class="fas fa-microphone"></i> جلسات التسميع</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link"><i class="fas fa-calendar-check"></i> سجل الحضور</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge bg-danger">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> التقارير</a>
@endsection
@section('content')
<div class="card mb-4" style="background:linear-gradient(135deg,#182B48,#0D1829);color:#fff;">
    <div class="card-body py-4 px-4">
        <h4 class="mb-1">أهلاً {{ auth()->user()->name }}</h4>
        <p class="mb-0 opacity-75">تابع تقدم أبنائك في حفظ القرآن الكريم</p>
    </div>
</div>

@foreach($studentsData as $data)
@php $student = $data['student']; @endphp
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <div style="width:40px;height:40px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;font-weight:bold;">
                {{ mb_substr($student->user->name, 0, 1) }}
            </div>
            <div>
                <h6 class="mb-0">{{ $student->user->name }}</h6>
                <small class="text-muted">{{ $student->level->name ?? '' }}</small>
            </div>
        </div>
        <a href="{{ route('parent.student.detail', $student->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-eye me-1"></i>التفاصيل
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <h6 class="text-muted mb-2"><i class="fas fa-calendar me-1"></i>الحضور هذا الشهر</h6>
                <div class="row g-2 text-center">
                    <div class="col-3"><div class="p-2 rounded bg-success text-white"><div class="fw-bold fs-5">{{ $data['attendance']['present'] }}</div><small>حاضر</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-danger text-white"><div class="fw-bold fs-5">{{ $data['attendance']['absent'] }}</div><small>غائب</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-warning text-dark"><div class="fw-bold fs-5">{{ $data['attendance']['late'] }}</div><small>متأخر</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-secondary text-white"><div class="fw-bold fs-5">{{ $data['attendance']['total'] }}</div><small>إجمالي</small></div></div>
                </div>
            </div>
            <div class="col-12">
                <h6 class="text-muted mb-2"><i class="fas fa-microphone me-1"></i>التسميع هذا الشهر</h6>
                <div class="row g-2 text-center">
                    <div class="col-3"><div class="p-2 rounded bg-primary text-white"><div class="fw-bold fs-5">{{ $data['sessions']['total'] }}</div><small>جلسات</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-success text-white"><div class="fw-bold fs-5">{{ $data['sessions']['passed'] }}</div><small>ناجح</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-danger text-white"><div class="fw-bold fs-5">{{ $data['sessions']['failed'] }}</div><small>راسب</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-light text-dark"><div class="fw-bold fs-5">{{ $data['sessions']['total'] > 0 ? round($data['sessions']['passed']/$data['sessions']['total']*100) : 100 }}%</div><small>نسبة</small></div></div>
                </div>
            </div>
        </div>

        @if($data['recent_warnings']->count() > 0)
        <div class="mt-3 p-3 bg-warning bg-opacity-10 rounded border border-warning">
            <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>إنذارات جديدة</h6>
            @foreach($data['recent_warnings'] as $warning)
            <div class="d-flex justify-content-between">
                <span style="font-size:13px;">{{ $warning->title }}</span>
                <small class="text-muted">{{ $warning->issue_date->format('d/m/Y') }}</small>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endforeach

@if(count($studentsData) === 0)
<div class="text-center py-5">
    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
    <h5 class="text-muted">لا يوجد طلاب مرتبطون بحسابك</h5>
    <p class="text-muted">يرجى التواصل مع إدارة الجمعية</p>
</div>
@endif
@endsection
