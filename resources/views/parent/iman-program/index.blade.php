@extends('layouts.app')

@section('title', 'البرنامج الإيماني')
@section('page-title', 'البرنامج الإيماني - ' . ($student?->user->name ?? 'اختر طالباً'))
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection

@push('styles')
<style>
    .check-cell-static {
        background: #f0f4f8 !important;
        color: transparent !important;
    }
    .check-cell-static.is-checked {
        background: linear-gradient(135deg, #27ae60, #2ecc71) !important;
        color: #fff !important;
    }
    .number-cell-static {
        background: #f0f4f8 !important;
        color: #6b7a90 !important;
    }
    .number-cell-static.is-done {
        background: rgba(39,174,96,0.1) !important;
        color: #27ae60 !important;
    }
</style>
@endpush

@section('content')
@php
    $today = \Carbon\Carbon::today()->toDateString();
    $progressPercent = $stats['week_total'] > 0 ? round(($stats['week_completed'] / $stats['week_total']) * 100) : 0;
@endphp

<div style="background:linear-gradient(135deg,#182B48 0%,#2A4570 100%);border-radius:16px;padding:24px 30px;margin-bottom:24px;color:#fff;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
    <div style="width:60px;height:60px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;">
        <i class="fas fa-star-and-crescent"></i>
    </div>
    <div>
        <h1 style="margin:0;font-size:22px;font-weight:700;">البرنامج الإيماني</h1>
        <p style="margin:0;color:rgba(255,255,255,0.8);font-size:13px;">متابعة التزام ابنك على مدار الأسبوع</p>
    </div>
    @if($student)
        <div style="margin-right:auto;">
            <a href="{{ route('parent.iman-program.student-report', $student->id) }}"
               style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.25);padding:10px 18px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-chart-pie"></i> تقرير ابنك
            </a>
        </div>
    @endif
</div>

<div style="background:#fff;border-radius:16px;padding:18px 22px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:20px;">
    <form method="GET" action="{{ route('parent.iman-program.index') }}" class="row g-3 align-items-end">
        <div class="col-md-7">
            <label class="form-label">اختر الطالب</label>
            <select name="student_id" class="form-select" onchange="this.form.submit()">
                <option value="">— اختر طالباً —</option>
                @foreach($children as $c)
                    <option value="{{ $c->id }}" {{ $studentFilter == $c->id ? 'selected' : '' }}>{{ $c->user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">الأسبوع</label>
            <input type="date" name="week" class="form-control" value="{{ $weekStart->toDateString() }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> عرض</button>
        </div>
    </form>
</div>

@if($student)
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;">
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#182B48,#2A4570);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-bookmark"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">البرامج</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $stats['programs_count'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-list-check"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">المعايير</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $stats['criteria_count'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#27ae60,#2ecc71);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-circle-check"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">منجز هذا الأسبوع</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $stats['week_completed'] }} / {{ $stats['week_total'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#3498db,#5dade2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-chart-pie"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">نسبة الإنجاز</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $progressPercent }}%</div></div>
        </div>
    </div>
@endif

@if(!$student)
    <div style="background:#fff;border-radius:16px;padding:60px 20px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05);">
        <i class="fas fa-user-graduate" style="font-size:50px;opacity:0.3;"></i>
        <h5 style="color:#182B48;margin-top:14px;">اختر طالباً للاطلاع على برنامجه الإيماني</h5>
    </div>
@else
    @forelse($programs as $program)
        <x-iman-program-card
            :program="$program"
            :week-dates="$weekDates"
            :entries="$entries"
            :today="$today"
            :edit-route="route('parent.iman-program.show', $program)"
            :can-manage="false"
            :can-edit="false" />
    @empty
        <div style="background:#fff;border-radius:16px;padding:60px 20px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05);">
            <i class="fas fa-star-and-crescent" style="font-size:50px;opacity:0.3;"></i>
            <h5 style="color:#182B48;margin-top:14px;">لا توجد برامج إيمانية مسجلة</h5>
            <p>سيقوم المشرف أو الطالب بإنشاء البرنامج الإيماني ومتابعته</p>
        </div>
    @endforelse
@endif
@endsection
