@extends('layouts.app')
@section('title', 'الرسوم')
@section('page-title', 'الرسوم الشهرية')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('content')

<div class="portal-container">
    <x-page-hero icon="fas fa-money-bill">
        <h1 class="page-banner-title">الرسوم الشهرية</h1>
        <p class="page-banner-sub">متابعة المدفوعات والرسوم المستحقة</p>
    </x-page-hero>

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
.header-icon.warning {
    background: linear-gradient(135deg, #d48806, #f39c12);
    color: #fff;
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
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
}

.stat-content { flex: 1; position: relative; z-index: 1; }

.stat-number { color: #fff; margin-bottom: 3px; }
.stat-label { color: rgba(255,255,255,0.75); }
.stat-decoration { color: #fff; opacity: 0.1; }

.student-fee-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
    margin-bottom: 16px;
}

.fee-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(26,51,86,0.03));
    border-bottom: 1px solid #f0f4f8;
}

.fee-card-body { padding: 16px; }

.year-section { margin-bottom: 16px; }
.year-section:last-child { margin-bottom: 0; }

.year-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #182B48;
    font-weight: 700;
    font-size: 14px;
}

.year-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 13px;
}

.months-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 14px;
}

.month-box {
    padding: 14px;
    border-radius: 13px;
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
    font-size: 14px;
    font-weight: 700;
    color: #182B48;
    margin-bottom: 6px;
}

.month-amount {
    font-size: 12px;
    color: #95a5a6;
    margin-bottom: 8px;
}

.month-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 600;
}

.month-status.paid { background: #16835a; color: #fff; }
.month-status.unpaid { background: #a82e1c; color: #fff; }

.paid-date { font-size: 11px; color: #95a5a6; margin-top: 8px; }

.empty-illustration {
    width: 66px;
    height: 66px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 26px;
    color: #d4a030;
}

.empty-state-card {
    text-align: center;
    padding: 40px 16px;
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
}

.empty-state-card h5 {
    color: #182B48;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 6px;
}
.empty-state-card p { color: #7f8c8d; font-size: 13px; }
</style>
@endsection