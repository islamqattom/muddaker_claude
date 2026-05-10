@extends('layouts.app')
@section('title', 'جلسة تسميع')
@section('page-title', 'جلسة تسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.recitation-session-placeholder {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.recitation-session-placeholder .empty-state-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    position: relative;
}

.recitation-session-placeholder .empty-state-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.recitation-session-placeholder .empty-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 20px;
    position: relative;
}

.recitation-session-placeholder .empty-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.recitation-session-placeholder .empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.recitation-session-placeholder .empty-icon i {
    font-size: 36px;
    color: #adb5bd;
}

.recitation-session-placeholder .back-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.3);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.recitation-session-placeholder .back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.4);
    color: white;
}
</style>
@endpush

@section('content')
<div class="recitation-session-placeholder">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="empty-state-card islamic-border">
                <div class="empty-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-tools me-2"></i>جلسة تسميع</h5>
                </div>
                <div class="card-body text-center py-5" style="position: relative; z-index: 1;">
                    <div class="empty-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h4 class="text-muted mb-3">هذه الصفحة غير مستخدمة حالياً</h4>
                    <p class="text-muted mb-4">الرجاء استخدام الصفحات الأخرى للتسميع</p>
                    <a href="{{ route('supervisor.recitation.index') }}" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للتسميع
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection