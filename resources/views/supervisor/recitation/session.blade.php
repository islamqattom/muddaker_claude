@extends('layouts.app')
@section('title', 'جلسة تسميع')
@section('page-title', 'جلسة تسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.recitation.index') }}">التسميع</a></li>
    <li class="breadcrumb-item active">جلسة تسميع</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-tools fa-3x text-muted mb-3 d-block"></i>
        <h4 class="text-muted">جلسة تسميع</h4>
        <p class="text-muted">هذه الصفحة غير مستخدمة حالياً</p>
        <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-outline-primary mt-2">
            <i class="fas fa-arrow-right me-2"></i>العودة للتسميع
        </a>
    </div>
</div>
@endsection
