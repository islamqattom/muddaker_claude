@extends('layouts.app')
@section('title', 'الرسوم')
@section('page-title', 'الرسوم الشهرية')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon warning">
            <i class="fas fa-money-bill"></i>
        </div>
        <div class="header-text">
            <h4>الرسوم الشهرية</h4>
            <p>متابعة المدفوعات والرسوم المستحقة</p>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="stats-grid">
    <div class="stat-card-modern success">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($totalPaidThisYear, 0) }}</div>
            <div class="stat-label">المدفوع هذا العام (دينار)</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-coins"></i>
        </div>
    </div>

    <div class="stat-card-modern danger">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($totalUnpaid, 0) }}</div>
            <div class="stat-label">المستحق (غير مدفوع)</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-file-invoice"></i>
        </div>
    </div>

    <div class="stat-card-modern primary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $studentFees ? count($studentFees) : 0 }}</div>
            <div class="stat-label">عدد الأبناء</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-user-graduate"></i>
        </div>
    </div>

    <div class="stat-card-modern secondary">
        <div class="stat-card-bg"></div>
        <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ date('Y') }}</div>
            <div class="stat-label">السنة الحالية</div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-calendar"></i>
        </div>
    </div>
</div>

{{-- Student Fees Cards --}}
@foreach($studentFees as $studentId => $data)
@php $student = $data['student']; @endphp
<div class="student-fee-card mb-4">
    <div class="fee-card-header">
        <div class="student-info">
            <div class="user-avatar-lg">
                <i class="fas fa-user"></i>
            </div>
            <div class="student-details">
                <h5>{{ $student->user->name }}</h5>
                <span class="level-badge">{{ $student->level->name ?? 'غير محدد' }}</span>
            </div>
        </div>
    </div>
    <div class="fee-card-body">
        @php
            $feesByYear = $data['fees']->groupBy('year')->sortKeysDesc();
        @endphp

        @forelse($feesByYear as $year => $yearFees)
            <div class="year-section">
                <div class="year-header">
                    <i class="fas fa-calendar"></i>
                    <span>{{ $year }}</span>
                </div>
                <div class="months-grid">
                    @foreach($yearFees->sortBy('month') as $fee)
                        <div class="month-box {{ $fee->status === 'paid' ? 'paid' : 'unpaid' }}">
                            <div class="month-name">{{ $months[$fee->month] ?? $fee->month }}</div>
                            @if($fee->fee_amount)
                                <div class="month-amount">{{ number_format($fee->fee_amount, 0) }} د.ع</div>
                            @endif
                            @if($fee->status === 'paid')
                                <div class="month-status paid">
                                    <i class="fas fa-check"></i>
                                    <span>مدفوع</span>
                                </div>
                                @if($fee->paid_at)
                                    <div class="paid-date">{{ $fee->paid_at->format('d/m') }}</div>
                                @endif
                            @else
                                <div class="month-status unpaid">
                                    <i class="fas fa-times"></i>
                                    <span>غير مدفوع</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h5>لا توجد سجلات مالية</h5>
                <p>لم يتم تسجيل أي رسوم لهذا الطالب</p>
            </div>
        @endforelse
    </div>
</div>
@endforeach

@if(count($studentFees) === 0)
<div class="empty-state-card">
    <div class="empty-icon">
        <i class="fas fa-users"></i>
    </div>
    <h5>لا يوجد طلاب مرتبطون بحسابك</h5>
    <p>يرجى التواصل مع إدارة الجمعية</p>
</div>
@endif

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #d4a030;
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}

.header-icon.warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 992px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card-modern {
    position: relative;
    padding: 25px;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    transition: all 0.3s ease;
}

.stat-card-modern:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.2); }
.stat-card-modern.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card-modern.secondary { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); }
.stat-card-modern.success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card-modern.danger { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); }

.stat-card-bg {
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.stat-icon {
    width: 55px;
    height: 55px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    backdrop-filter: blur(5px);
    flex-shrink: 0;
}

.stat-content { flex: 1; position: relative; z-index: 1; }
.stat-number { font-size: 32px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 5px; }
.stat-label { font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 500; }
.stat-decoration { position: absolute; bottom: 10px; left: 15px; font-size: 40px; opacity: 0.15; color: #fff; }

.student-fee-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.fee-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(26,51,86,0.03));
    border-bottom: 1px solid #f0f4f8;
}

.student-info { display: flex; align-items: center; gap: 15px; }

.user-avatar-lg {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.student-details h5 { color: #182B48; font-size: 18px; font-weight: 700; margin: 0 0 5px 0; }

.level-badge {
    display: inline-block;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.fee-card-body { padding: 25px; }

.year-section { margin-bottom: 20px; }
.year-section:last-child { margin-bottom: 0; }

.year-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    color: #182B48;
    font-weight: 700;
    font-size: 16px;
}

.year-header i { color: #d4a030; }

.months-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.month-box {
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
}

.month-box:hover { transform: translateY(-3px); }

.month-box.paid {
    background: linear-gradient(135deg, rgba(39,174,96,0.1), rgba(46,204,113,0.1));
    border: 1px solid rgba(39,174,96,0.2);
}

.month-box.unpaid {
    background: linear-gradient(135deg, rgba(192,57,43,0.1), rgba(231,76,60,0.1));
    border: 1px solid rgba(192,57,43,0.2);
}

.month-name {
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
    margin-bottom: 5px;
}

.month-amount {
    font-size: 12px;
    color: #7f8c8d;
    margin-bottom: 10px;
}

.month-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.month-status.paid { background: #27ae60; color: #fff; }
.month-status.unpaid { background: #c0392b; color: #fff; }

.paid-date { font-size: 11px; color: #7f8c8d; margin-top: 8px; }

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 28px;
    color: #d4a030;
}

.empty-state h5 { color: #182B48; font-size: 16px; font-weight: 700; margin-bottom: 5px; }
.empty-state p { color: #7f8c8d; font-size: 13px; }

.empty-state-card {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.empty-state-card .empty-icon { width: 90px; height: 90px; font-size: 36px; }
.empty-state-card h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state-card p { color: #7f8c8d; }
</style>
@endsection