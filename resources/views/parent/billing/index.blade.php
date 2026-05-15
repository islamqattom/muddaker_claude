@extends('layouts.app')
@section('title', 'الرسوم')
@section('page-title', 'الرسوم الشهرية')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('content')

<div class="portal-container">
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

    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($totalPaidThisYear, 0) }}</div>
                <div class="stat-label">المدفوع هذا العام (دينار)</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-coins"></i>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($totalUnpaid, 0) }}</div>
                <div class="stat-label">المستحق (غير مدفوع)</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>

        <div class="stat-card primary">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $studentFees ? count($studentFees) : 0 }}</div>
                <div class="stat-label">عدد الأبناء</div>
            </div>
            <div class="stat-decoration">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-glow"></div>
            <div class="stat-icon-wrapper">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
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

    @foreach($studentFees as $studentId => $data)
    @php $student = $data['student']; @endphp
    <div class="student-fee-card">
        <div class="fee-card-header">
            <div class="student-info">
                <div class="student-avatar">
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
                        <div class="year-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
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
                    <div class="empty-illustration">
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
        <div class="empty-illustration">
            <i class="fas fa-users"></i>
        </div>
        <h5>لا يوجد طلاب مرتبطون بحسابك</h5>
        <p>يرجى التواصل مع إدارة الجمعية</p>
    </div>
    @endif
</div>

<style>
.portal-container {
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    box-shadow: 0 10px 30px rgba(24,43,72,0.2);
}

.header-icon.warning {
    background: linear-gradient(135deg, #d48806, #f39c12);
    color: #fff;
}

.header-text h4 {
    color: #182B48;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.header-text p { color: #95a5a6; font-size: 14px; margin: 0; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 992px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card {
    position: relative;
    padding: 28px;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: 18px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}

.stat-card.primary { background: linear-gradient(135deg, #182B48, #2A4570); }
.stat-card.success { background: linear-gradient(135deg, #16835a, #27ae60); }
.stat-card.danger { background: linear-gradient(135deg, #a82e1c, #c0392b); }
.stat-card.purple { background: linear-gradient(135deg, #7d3c98, #9b59b6); }

.stat-glow {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.stat-icon-wrapper { position: relative; z-index: 1; }

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
}

.stat-content { flex: 1; position: relative; z-index: 1; }

.stat-number {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    margin-bottom: 6px;
}

.stat-label {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    font-weight: 500;
}

.stat-decoration {
    position: absolute;
    bottom: 12px;
    left: 18px;
    font-size: 45px;
    opacity: 0.1;
    color: #fff;
}

.student-fee-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
    margin-bottom: 25px;
}

.fee-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(26,51,86,0.03));
    border-bottom: 1px solid #f0f4f8;
}

.student-info { display: flex; align-items: center; gap: 18px; }

.student-avatar {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 22px;
}

.student-details h5 {
    color: #182B48;
    font-size: 19px;
    font-weight: 700;
    margin: 0 0 6px 0;
}

.level-badge {
    display: inline-block;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    color: #182B48;
    padding: 5px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.fee-card-body { padding: 28px; }

.year-section { margin-bottom: 22px; }
.year-section:last-child { margin-bottom: 0; }

.year-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    color: #182B48;
    font-weight: 700;
    font-size: 17px;
}

.year-icon {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.months-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 14px;
}

.month-box {
    padding: 18px;
    border-radius: 14px;
    text-align: center;
    transition: all 0.3s ease;
}

.month-box:hover { transform: translateY(-4px); }

.month-box.paid {
    background: linear-gradient(135deg, rgba(39,174,96,0.08), rgba(46,204,113,0.08));
    border: 1px solid rgba(39,174,96,0.15);
}

.month-box.unpaid {
    background: linear-gradient(135deg, rgba(192,57,43,0.08), rgba(231,76,60,0.08));
    border: 1px solid rgba(192,57,43,0.15);
}

.month-name {
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
    margin-bottom: 6px;
}

.month-amount {
    font-size: 12px;
    color: #95a5a6;
    margin-bottom: 12px;
}

.month-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.month-status.paid { background: #16835a; color: #fff; }
.month-status.unpaid { background: #a82e1c; color: #fff; }

.paid-date { font-size: 11px; color: #95a5a6; margin-top: 10px; }

.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-illustration {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 32px;
    color: #d4a030;
}

.empty-state h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}

.empty-state p { color: #95a5a6; font-size: 13px; }

.empty-state-card {
    text-align: center;
    padding: 70px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
}

.empty-state-card .empty-illustration { width: 100px; height: 100px; font-size: 40px; }
.empty-state-card h5 {
    color: #182B48;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}
.empty-state-card p { color: #95a5a6; }
</style>
@endsection