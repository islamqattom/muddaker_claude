@extends('layouts.app')
@section('title', 'الرسوم')
@section('page-title', 'الرسوم الشهرية')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
@include('parent.partials.sidebar')
@endsection

@section('content')
{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small>المدفوع هذا العام</small>
                        <h4 class="mb-0">{{ number_format($totalPaidThisYear, 2) }} ريال</h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small>المستحق (غير مدفوع)</small>
                        <h4 class="mb-0">{{ number_format($totalUnpaid, 2) }} ريال</h4>
                    </div>
                    <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small>الرسوم لكل طالب</small>
                        <h4 class="mb-0">{{ number_format(config('app.billing_monthly_fee_per_student', 0), 2) }} ريال</h4>
                    </div>
                    <i class="fas fa-user-graduate fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Legend --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex gap-4">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success p-2"><i class="fas fa-check"></i></span>
                <span>مدفوع</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger p-2"><i class="fas fa-times"></i></span>
                <span>غير مدفوع</span>
            </div>
        </div>
    </div>
</div>

{{-- Student Billing Details --}}
@foreach($studentFees as $data)
@php $student = $data['student']; @endphp
<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <div style="width:40px;height:40px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;font-weight:bold;">
            {{ mb_substr($student->user->name, 0, 1) }}
        </div>
        <div>
            <h6 class="mb-0">{{ $student->user->name }}</h6>
            <small class="text-muted">{{ $student->level->name ?? '' }}</small>
        </div>
    </div>
    <div class="card-body">
        @php
            $feesByYear = $data['fees']->groupBy('year')->sortKeysDesc();
        @endphp

        @forelse($feesByYear as $year => $yearFees)
            <h6 class="text-primary mb-3">{{ $year }}</h6>
            <div class="row g-2 mb-4">
                @foreach($yearFees->sortBy('month') as $fee)
                    <div class="col-md-2 col-4">
                        <div class="p-2 rounded text-center {{ $fee->status === 'paid' ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' }}">
                            <div class="fw-bold">{{ $months[$fee->month] ?? $fee->month }}</div>
                            @if($fee->status === 'paid')
                                <span class="badge bg-success mt-1">
                                    <i class="fas fa-check"></i> مدفوع
                                </span>
                                @if($fee->paid_at)
                                    <div class="small text-muted mt-1">{{ $fee->paid_at->format('d/m') }}</div>
                                @endif
                            @else
                                <span class="badge bg-danger mt-1">
                                    <i class="fas fa-times"></i> غير مدفوع
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>
                <p>لا توجد سجلات مالية</p>
            </div>
        @endforelse
    </div>
</div>
@endforeach

@if(count($studentFees) === 0)
<div class="text-center py-5">
    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
    <h5 class="text-muted">لا يوجد طلاب مرتبطون بحسابك</h5>
</div>
@endif
@endsection
