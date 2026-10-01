@extends('layouts.app')

@section('title', 'تفاصيل التحدي')
@section('page-title', 'تفاصيل التحدي')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')

<x-page-hero icon="fas fa-swords">
    <h1 class="page-banner-title">تفاصيل التحدي</h1>
</x-page-hero>
@php
$isChallenger = $challenge->challenger_id === auth()->user()->student->id;
$isChallenged = $challenge->challenged_id === auth()->user()->student->id;
$myVerification = $challenge->verifications->where('student_id', auth()->user()->student->id)->first();
$opponentVerification = $challenge->verifications->where('student_id', '!=', auth()->user()->student->id)->first();
@endphp

<!-- Challenge Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center text-center">
            <div class="col-4">
                <div class="p-3 {{ $isChallenger ? 'bg-primary text-white' : 'bg-light' }} rounded">
                    <h5 class="mb-1">{{ $challenge->challenger->name }}</h5>
                    <small>{{ $isChallenger ? 'أنت' : 'المتسابق' }}</small>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 bg-warning text-dark rounded">
                    <h4 class="mb-1 fw-bold">VS</h4>
                    <small>السورة {{ $challenge->surah }}</small>
                    <br>
                    <small>{{ $challenge->verse_start }} - {{ $challenge->verse_end }}</small>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 {{ $isChallenged ? 'bg-primary text-white' : 'bg-light' }} rounded">
                    <h5 class="mb-1">{{ $challenge->challenged->name }}</h5>
                    <small>{{ $isChallenged ? 'أنت' : 'المتسابق' }}</small>
                </div>
            </div>
        </div>

        <hr>

        <div class="row text-center">
            <div class="col-4">
                <div class="text-muted small">تاريخ الهدف</div>
                <div class="fw-bold">{{ $challenge->target_date->format('Y-m-d') }}</div>
            </div>
            <div class="col-4">
                <div class="text-muted small">عدد الأيام المتبقية</div>
                <div class="fw-bold">
                    @if($challenge->status === 'completed' || $challenge->status === 'failed')
                        -
                    @else
                        {{ max(0, now()->diffInDays($challenge->target_date)) }} يوم
                    @endif
                </div>
            </div>
            <div class="col-4">
                <div class="text-muted small">الحالة</div>
                <div class="fw-bold">
                    @if($challenge->status === 'pending')
                        <span class="badge bg-warning">في انتظار القبول</span>
                    @elseif($challenge->status === 'accepted')
                        <span class="badge bg-success">نشط</span>
                    @elseif($challenge->status === 'completed')
                        <span class="badge bg-primary">مكتمل</span>
                    @elseif($challenge->status === 'failed')
                        <span class="badge bg-danger">فشل</span>
                    @else
                        <span class="badge bg-secondary">مرفوض</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Winner Banner -->
@if($challenge->status === 'completed' && $challenge->winner)
<div class="alert alert-success text-center mb-4">
    <i class="fas fa-trophy fa-2x me-2"></i>
    <strong>الفائز: {{ $challenge->winner->name }}</strong>
</div>
@endif

<!-- Verification Status -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>حالة إتمام التسميع</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6 text-center mb-3 mb-md-0">
                <div class="p-3 {{ $myVerification ? 'bg-success text-white' : 'bg-light' }} rounded">
                    <i class="fas {{ $myVerification ? 'fa-check' : 'fa-clock' }} fa-2x mb-2"></i>
                    <br>
                    <strong>{{ $challenge->challenger->name }}</strong>
                    <br>
                    <small>{{ $myVerification ? 'تم التسميع' : ($isChallenger ? 'بانتظارك' : 'بانتظار المنافس') }}</small>
                </div>
            </div>
            <div class="col-6 text-center">
                <div class="p-3 {{ $opponentVerification ? 'bg-success text-white' : 'bg-light' }} rounded">
                    <i class="fas {{ $opponentVerification ? 'fa-check' : 'fa-clock' }} fa-2x mb-2"></i>
                    <br>
                    <strong>{{ $challenge->challenged->name }}</strong>
                    <br>
                    <small>{{ $opponentVerification ? 'تم التسميع' : ($isChallenged ? 'بانتظارك' : 'بانتظار المنافس') }}</small>
                </div>
            </div>
        </div>

        @if(!$myVerification && $challenge->status === 'accepted')
        <div class="alert alert-info mt-3 mb-0 text-center">
            <i class="fas fa-info-circle me-1"></i>
            سجل جلسة تسميع جديدة وأخبر المشرف برقم الجلسة لتأكيد إتمامك للتحدي
        </div>
        @endif
    </div>
</div>

<!-- Actions -->
<div class="d-flex gap-2 justify-content-center">
    @if($isChallenged && $challenge->status === 'pending')
        <form action="{{ route('student.challenges.accept', $challenge) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-check me-1"></i>قبول التحدي
            </button>
        </form>
        <form action="{{ route('student.challenges.decline', $challenge) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-lg">
                <i class="fas fa-times me-1"></i>رفض
            </button>
        </form>
    @else
        <a href="{{ route('student.challenges.index') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-right me-1"></i>العودة
        </a>
        @if($challenge->status === 'accepted')
            <a href="{{ route('student.sessions.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-microphone me-1"></i>تسميع جديد
            </a>
        @endif
    @endif
</div>
@endsection
