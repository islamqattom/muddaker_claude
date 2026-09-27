@extends('layouts.app')
@section('title', 'جلسة تسميع')
@section('page-title', 'جلسة تسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.rec-session {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
}

.rec-session .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.rec-session .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rec-session .hero-content { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }

.rec-session .hero-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 6px 20px rgba(212,160,48,0.35);
}

.rec-session .student-info { display: flex; align-items: center; gap: 14px; }

.rec-session .student-avatar {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: white;
    box-shadow: 0 4px 12px rgba(212,160,48,0.3);
}

.rec-session .student-name { font-size: 18px; font-weight: 700; color: white; margin-bottom: 4px; }

.rec-session .page-card {
    border: 1px solid rgba(24,43,72,0.08);
    border-radius: 13px;
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    margin-bottom: 16px;
}

.rec-session .page-card-header {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    padding: 12px 16px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid rgba(24,43,72,0.04);
}

.rec-session .empty-state-icon {
    width: 66px; height: 66px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
}

.rec-session .empty-state-icon i {
    font-size: 26px;
    color: #adb5bd;
}

.rec-session .back-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-size: 13px; font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(24,43,72,0.3);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.rec-session .back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24,43,72,0.4);
    color: white;
}

@media (max-width: 768px) {
    .rec-session .hero-content { flex-direction: column; text-align: center; }
    .rec-session .student-info { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="rec-session">
    <div class="page-hero">
        <div class="hero-content">
            <div class="student-info">
                <div class="student-avatar">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h2 class="student-name">جلسة تسميع</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <h6 class="mb-0" style="color: var(--navy);">
                <i class="fas fa-tools me-2" style="color: var(--gold);"></i>
                هذه الصفحة غير مستخدمة حالياً
            </h6>
        </div>
        <div class="page-card-body text-center py-5">
            <div class="empty-state-icon">
                <i class="fas fa-cog"></i>
            </div>
            <h4 class="text-muted mb-3">الرجاء استخدام الصفحات الأخرى للتسميع</h4>
            <a href="{{ route('supervisor.recitation.index') }}" class="back-btn">
                <i class="fas fa-arrow-right"></i>
                العودة للتسميع
            </a>
        </div>
    </div>
</div>
@endsection