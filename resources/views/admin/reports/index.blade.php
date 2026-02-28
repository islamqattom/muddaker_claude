@extends('layouts.app')
@section('title', 'التقارير الشاملة')
@section('page-title', 'التقارير الشاملة')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التقارير</li>
@endsection

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card bg-primary bg-opacity-10 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">إجمالي الطلاب</h6>
                        <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                    </div>
                    <div class="fs-1 text-primary opacity-50">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-info bg-opacity-10 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">إجمالي الجلسات</h6>
                        <h3 class="mb-0">{{ $stats['total_sessions'] }}</h3>
                    </div>
                    <div class="fs-1 text-info opacity-50">
                        <i class="fas fa-microphone"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success bg-opacity-10 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">جلسات ناجحة</h6>
                        <h3 class="mb-0">{{ $stats['passed_sessions'] }}</h3>
                    </div>
                    <div class="fs-1 text-success opacity-50">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-danger bg-opacity-10 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">جلسات راسبة</h6>
                        <h3 class="mb-0">{{ $stats['failed_sessions'] }}</h3>
                    </div>
                    <div class="fs-1 text-danger opacity-50">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reports Links -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-microphone me-2"></i>
                    تقارير التسميع
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted">عرض تقارير جلسات التسميع والاختبارات</p>
                <a href="{{ route('admin.reports.recitation') }}" class="btn btn-primary">
                    <i class="fas fa-chart-line me-2"></i>عرض التقارير
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    تقارير الحضور
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted">عرض تقارير حضور وانصراف الطلاب</p>
                <a href="{{ route('admin.reports.attendance') }}" class="btn btn-info">
                    <i class="fas fa-chart-line me-2"></i>عرض التقارير
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Success Rate -->
@if($stats['total_sessions'] > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-percentage me-2"></i>
                    نسبة النجاح الإجمالية
                </h6>
            </div>
            <div class="card-body">
                <div class="progress" style="height: 30px;">
                    @php
                        $successRate = round(($stats['passed_sessions'] / $stats['total_sessions']) * 100);
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $successRate }}%">
                        {{ $successRate }}%
                    </div>
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ 100 - $successRate }}%">
                        {{ 100 - $successRate }}%
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-success">ناجح: {{ $stats['passed_sessions'] }}</span>
                    <span class="text-danger">راسب: {{ $stats['failed_sessions'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
