@extends('layouts.app')

@section('title', 'نشاط النطق')
@section('page-title', 'نشاط النطق والتجويد')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.pronunciation-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.pronunciation-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.pronunciation-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.pronunciation-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.pronunciation-page .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(52,152,219,0.35);
}

.pronunciation-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.pronunciation-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.pronunciation-page .toolbar-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 16px 24px;
    margin-bottom: 24px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.pronunciation-page .toolbar-title { font-size: 16px; font-weight: 700; color: var(--navy); }

.pronunciation-page .btn-new {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
    display: flex; align-items: center; gap: 8px;
}

.pronunciation-page .btn-new:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

.pronunciation-page .attempts-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.pronunciation-page .attempts-table { width: 100%; border-collapse: collapse; margin: 0; }
.pronunciation-page .attempts-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.pronunciation-page .attempts-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.pronunciation-page .attempts-table tbody tr { transition: all 0.2s ease; }
.pronunciation-page .attempts-table tbody tr:hover { background: linear-gradient(90deg, rgba(52,152,219,0.04) 0%, rgba(24,43,72,0.02) 100%); }

.pronunciation-page .score-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.pronunciation-page .status-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.pronunciation-page .view-btn {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(24,43,72,0.1);
    background: white;
    color: var(--navy);
    transition: all 0.2s ease;
}

.pronunciation-page .view-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(24,43,72,0.2); background: var(--navy); color: white; }

.pronunciation-page .empty-state { text-align: center; padding: 80px 20px; }
.pronunciation-page .empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(52,152,219,0.15), rgba(41,128,185,0.15));
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
}
.pronunciation-page .empty-icon i { font-size: 40px; color: var(--info); }

.pronunciation-page .pagination-wrapper {
    padding: 16px 20px;
    border-top: 1px solid rgba(24,43,72,0.04);
    display: flex; justify-content: center;
}

@media (max-width: 992px) {
    .pronunciation-page .page-hero { padding: 20px 24px; }
    .pronunciation-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .pronunciation-page .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="pronunciation-page">
    <div class="page-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon">
                    <i class="fas fa-microphone-alt"></i>
                </div>
                <div>
                    <h1 class="hero-title">نشاط النطق</h1>
                    <p class="hero-sub">سجل محاولات النطق والتجويد</p>
                </div>
            </div>
            <a href="{{ route('student.pronunciation.practice') }}" class="btn-new">
                <i class="fas fa-plus"></i>محاولة جديدة
            </a>
        </div>
    </div>

    @if($attempts->count() > 0)
        <div class="attempts-card">
            <div class="table-responsive">
                <table class="attempts-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>السورة</th>
                            <th>الآيات</th>
                            <th>النتيجة</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attempts as $attempt)
                        <tr>
                            <td><small>{{ $attempt->created_at->format('Y-m-d H:i') }}</small></td>
                            <td class="fw-semibold" style="color: var(--navy);">سورة {{ $attempt->surah }}</td>
                            <td><span class="verses-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">{{ $attempt->verse_start }} - {{ $attempt->verse_end }}</span></td>
                            <td>
                                @if($attempt->status === 'completed')
                                    @php
                                        $scoreColor = $attempt->tajweed_score >= 85 ? 'success' : ($attempt->tajweed_score >= 70 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="score-badge bg-{{ $scoreColor }}">{{ $attempt->tajweed_score }}%</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attempt->status === 'completed')
                                    <span class="status-badge" style="background: rgba(39,174,96,0.15); color: var(--success);">مكتمل</span>
                                @elseif($attempt->status === 'processing')
                                    <span class="status-badge" style="background: rgba(243,156,18,0.15); color: var(--warning);">قيد المعالجة</span>
                                @elseif($attempt->status === 'failed')
                                    <span class="status-badge" style="background: rgba(192,57,43,0.15); color: var(--danger);">فشل</span>
                                @else
                                    <span class="status-badge" style="background: rgba(108,117,125,0.15); color: #6c757d;">في الانتظار</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('student.pronunciation.results', $attempt) }}" class="view-btn" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $attempts->links() }}
            </div>
        </div>
    @else
        <div class="attempts-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-microphone-alt"></i>
                </div>
                <h5 class="mb-2" style="color: var(--navy); font-weight: 700;">لا توجد محاولات سابقة</h5>
                <p class="text-muted mb-3">ابدأ بتمرين النطق لتحسين تجويدك</p>
                <a href="{{ route('student.pronunciation.practice') }}" class="btn-new">
                    <i class="fas fa-plus me-1"></i>محاولة جديدة
                </a>
            </div>
        </div>
    @endif
</div>
@endsection