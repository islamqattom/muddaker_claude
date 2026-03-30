@extends('layouts.app')
@section('title', 'بوابة ولي الأمر')
@section('page-title', 'متابعة أبنائي')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('content')
<div class="card mb-4" style="background:linear-gradient(135deg,#182B48,#0D1829);color:#fff;">
    <div class="card-body py-4 px-4">
        <h4 class="mb-1">أهلاً {{ auth()->user()->name }}</h4>
        <p class="mb-0 opacity-75">تابع تقدم أبنائك في حفظ القرآن الكريم</p>
    </div>
</div>

<!-- Color Guide -->
<div class="card mb-4">
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
                    <div class="col-3"><div class="p-2 rounded bg-info text-white"><div class="fw-bold fs-5">{{ $data['attendance']['excused'] }}</div><small>بعذر</small></div></div>
                </div>
                <div class="row g-2 text-center mt-2">
                    <div class="col-12"><div class="p-2 rounded bg-secondary text-white"><div class="fw-bold fs-5">{{ $data['attendance']['total'] }}</div><small>إجمالي</small></div></div>
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
