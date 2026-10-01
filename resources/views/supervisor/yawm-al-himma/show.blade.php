@extends('layouts.app')

@section('title', 'يوم الهمة - تفاصيل')
@section('page-title', 'تفاصيل جلسة يوم الهمة')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.yawm-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --radius: 16px;
    --radius-sm: 12px;
    --shadow-sm: 0 2px 10px rgba(24,43,72,0.05);
}
.yawm-page .info-card {
    background: white; border-radius: 13px; padding: 16px;
    box-shadow: var(--shadow-sm); margin-bottom: 16px;
}
.yawm-page .info-card h2 {
    color: var(--navy); font-size: 16px; font-weight: 700; margin-bottom: 12px;
    padding-bottom: 10px; border-bottom: 2px solid #fef5e7;
}
.yawm-page .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
.yawm-page .info-item .lbl { font-size: 12px; color: #6b7a90; margin-bottom: 4px; }
.yawm-page .info-item .val { font-size: 15px; font-weight: 700; color: var(--navy); }

.yawm-page .summary-box { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
.yawm-page .summary-item {
    background: #f8f9fc; border-radius: 13px; padding: 14px; text-align: center;
    border: 1px solid #eef1f6;
}
.yawm-page .summary-item .lbl { font-size: 12px; color: #6b7a90; margin-bottom: 6px; }
.yawm-page .summary-item .val { font-size: 22px; font-weight: 800; color: var(--navy); }
.yawm-page .summary-item.danger .val { color: #c0392b; }
.yawm-page .summary-item.warning .val { color: #b36b00; }

.yawm-page .part-block {
    background: white; border-radius: 13px; box-shadow: var(--shadow-sm);
    margin-bottom: 16px; overflow: hidden;
}
.yawm-page .part-block-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: white; padding: 12px 16px; font-weight: 700; font-size: 14px;
}
.yawm-page .part-block-body { padding: 0; }
.yawm-page .page-row {
    padding: 11px 14px; border-bottom: 1px solid #eef1f6;
    display: grid; grid-template-columns: 100px 1fr 1fr; gap: 14px;
    align-items: center; justify-items: center; font-size: 13px;
}
.yawm-page .page-row:last-of-type { border-bottom: none; }
.yawm-page .page-row.header { background: #f8f9fc; font-weight: 700; color: var(--navy); }
.yawm-page .page-num-badge { background: #fef5e7; color: var(--navy); font-weight: 700; padding: 3px 10px; border-radius: 7px; font-size: 11.5px; }
</style>
@endpush

@section('content')
<div class="yawm-page">
    <x-page-hero icon="fas fa-mosque">
        <h1 class="page-banner-title">تفاصيل جلسة يوم الهمة</h1>
        <p class="page-banner-sub">الطالب: {{ $session->student_name }} • {{ $session->session_date->format('Y-m-d') }}</p>
    </x-page-hero>

    <div class="info-card">
        <h2><i class="fas fa-info-circle me-2" style="color: var(--gold);"></i>بيانات الجلسة</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="lbl"><i class="fas fa-user-tie me-1"></i> المشرف</div>
                <div class="val">{{ $session->supervisor_name }}</div>
            </div>
            <div class="info-item">
                <div class="lbl"><i class="fas fa-user-graduate me-1"></i> الطالب</div>
                <div class="val">{{ $session->student_name }}</div>
            </div>
            <div class="info-item">
                <div class="lbl"><i class="fas fa-calendar me-1"></i> تاريخ الجلسة</div>
                <div class="val">{{ $session->session_date->format('Y-m-d') }}</div>
            </div>
            <div class="info-item">
                <div class="lbl"><i class="fas fa-book-quran me-1"></i> عدد الأجزاء</div>
                <div class="val">{{ count($session->parts) }} أجزاء</div>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h2><i class="fas fa-calculator me-2" style="color: var(--gold);"></i>ملخص الأخطاء والترددات</h2>
        <div class="summary-box">
            <div class="summary-item danger">
                <div class="lbl"><i class="fas fa-times-circle"></i> إجمالي الأخطاء</div>
                <div class="val">{{ $session->total_errors }}</div>
            </div>
            <div class="summary-item warning">
                <div class="lbl"><i class="fas fa-question-circle"></i> إجمالي الترددات</div>
                <div class="val">{{ $session->total_hesitations }}</div>
            </div>
            <div class="summary-item">
                <div class="lbl"><i class="fas fa-file-alt"></i> عدد الصفحات</div>
                <div class="val">{{ count($session->pages_data) }}</div>
            </div>
        </div>
    </div>

    @php $grouped = collect($session->pages_data)->groupBy('part_number'); @endphp

    @foreach($session->parts as $partNumber)
        @php $partPages = $grouped->get($partNumber, collect()); @endphp
        <div class="part-block">
            <div class="part-block-header">
                <i class="fas fa-book-open me-2"></i> الجزء {{ $partNumber }}
                <span class="float-end" style="opacity: 0.85; font-weight: 400; font-size: 13px;">
                    {{ $partPages->count() }} صفحة
                </span>
            </div>
            <div class="part-block-body">
                <div class="page-row header">
                    <div>الصفحة</div>
                    <div><i class="fas fa-times-circle" style="color: #c0392b;"></i> الأخطاء</div>
                    <div><i class="fas fa-question-circle" style="color: #b36b00;"></i> الترددات</div>
                </div>
                @foreach($partPages->sortBy('page_number') as $p)
                <div class="page-row">
                    <div><span class="page-num-badge">ص {{ $p['page_number'] }}</span></div>
                    <div>
                        @if(($p['errors'] ?? 0) > 0)
                            <span class="badge-modern danger">{{ $p['errors'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </div>
                    <div>
                        @if(($p['hesitations'] ?? 0) > 0)
                            <span class="badge-modern warning">{{ $p['hesitations'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </div>
                </div>
                @endforeach
                @php
                    $partErrors = $partPages->sum(fn($p) => (int)($p['errors'] ?? 0));
                    $partHesitations = $partPages->sum(fn($p) => (int)($p['hesitations'] ?? 0));
                @endphp
                <div class="page-row" style="background: #fef5e7; font-weight: 700; color: var(--navy);">
                    <div style="text-align: center;"><i class="fas fa-calculator me-1" style="color: var(--gold);"></i> مجموع الجزء</div>
                    <div style="text-align: center;">
                        <span class="badge-modern danger">{{ $partErrors }}</span>
                    </div>
                    <div style="text-align: center;">
                        <span class="badge-modern warning">{{ $partHesitations }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if($session->notes)
    <div class="info-card">
        <h2><i class="fas fa-sticky-note me-2" style="color: var(--gold);"></i>الملاحظات</h2>
        <p class="mb-0" style="white-space: pre-line;">{{ $session->notes }}</p>
    </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('supervisor.yawm-al-himma.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
        </a>
    </div>
</div>
@endsection
