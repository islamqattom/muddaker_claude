@extends('layouts.app')

@section('title', 'جلسات التسميع')
@section('page-title', 'جلسات التسميع')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.student-sessions {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.student-sessions .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.student-sessions .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.student-sessions .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.student-sessions .hero-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 6px 18px rgba(212,160,48,0.35);
    flex-shrink: 0;
}

.student-sessions .hero-title { color: white; font-size: 18px; font-weight: 700; margin-bottom: 4px; }
.student-sessions .hero-sub { color: rgba(255,255,255,0.75); font-size: 12.5px; margin: 0; }

.student-sessions .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 12.5px;
    color: rgba(255,255,255,0.6);
    text-align: center;
    padding: 8px 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212,160,48,0.2);
    margin-top: 12px;
    position: relative; z-index: 1;
}

.student-sessions .sessions-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.student-sessions .sessions-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
    display: flex; align-items: center; justify-content: space-between;
}

.student-sessions .sessions-header h5 { color: white; font-weight: 700; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; }
.student-sessions .sessions-header h5 i { color: var(--gold); }

.student-sessions .sessions-header .count-badge {
    background: rgba(255,255,255,0.2);
    color: white;
    font-size: 11.5px; padding: 3px 10px;
    border-radius: 999px;
    font-weight: 600;
}

.student-sessions .sessions-table { margin: 0; }

.student-sessions .sessions-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 11px 14px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.student-sessions .sessions-table tbody td {
    padding: 11px 14px;
    vertical-align: middle;
    border-color: rgba(24,43,72,0.04);
    font-size: 13px;
    text-align: center;
}

.student-sessions .sessions-table tbody tr { transition: all 0.2s ease; }
.student-sessions .sessions-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }
.student-sessions .sessions-table tbody tr:hover td:first-child { border-right: 3px solid var(--gold); }

.student-sessions .type-badge {
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
}

.student-sessions .type-badge.new_memorization { background: rgba(39,174,96,0.15); color: var(--success); }
.student-sessions .type-badge.review { background: rgba(52,152,219,0.15); color: var(--info); }
.student-sessions .type-badge.unmastered { background: rgba(243,156,18,0.15); color: var(--warning); }
.student-sessions .type-badge.recitation { background: rgba(155,89,182,0.15); color: #8e44ad; }
.student-sessions .type-badge.mafsil { background: rgba(231,76,60,0.15); color: var(--danger); }

.student-sessions .part-info {
    font-size: 12.5px;
    color: var(--navy);
    font-weight: 500;
}

.student-sessions .errors-val { color: var(--danger); font-weight: 700; }
.student-sessions .hesitations-val { color: var(--warning); font-weight: 700; }

.student-sessions .score-val { font-weight: 700; color: var(--navy); }
.student-sessions .score-max { color: #95a5a6; font-size: 12px; }

.student-sessions .status-badge {
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
    color: white;
}

.student-sessions .day-badge {
    background: rgba(24,43,72,0.06); color: var(--navy);
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
}

.student-sessions .date-val { font-size: 12.5px; color: #6c7a89; }

.student-sessions .view-btn {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(24,43,72,0.1);
    background: white;
    color: var(--navy);
    transition: all 0.2s ease;
}

.student-sessions .view-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(24,43,72,0.2);
    background: var(--navy);
    color: white;
}

.student-sessions .pagination-wrapper {
    padding: 12px 16px;
    border-top: 1px solid rgba(24,43,72,0.04);
    display: flex; justify-content: center;
}

.student-sessions .empty-state { text-align: center; padding: 40px 16px; }
.student-sessions .empty-icon {
    width: 66px; height: 66px;
    background: linear-gradient(135deg, rgba(24,43,72,0.06), rgba(42,69,112,0.08));
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
}
.student-sessions .empty-icon i { font-size: 26px; color: var(--navy); opacity: 0.4; }

@media (max-width: 992px) {
    .student-sessions .page-hero { padding: 14px 16px; }
    .student-sessions .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .student-sessions .hero-title { font-size: 18px; }
    .student-sessions .sessions-table { font-size: 12px; }
    .student-sessions .sessions-table thead th, .student-sessions .sessions-table tbody td { padding: 10px 6px; }
}
</style>
@endpush

@section('content')
<div class="student-sessions">
    <x-page-hero icon="fas fa-microphone">
        <h1 class="page-banner-title">جلسات التسميع</h1>
        <p class="page-banner-sub">سجل جلسات التحفيظ والتقييم</p>
    </x-page-hero>

    <div class="sessions-card">
        <div class="sessions-header">
            <h5><i class="fas fa-list"></i>جدول الجلسات</h5>
            <span class="count-badge">
                <i class="fas fa-microphone-alt me-1"></i>
                {{ $sessions->total() }} جلسة
            </span>
        </div>

        @if($sessions->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-microphone-alt"></i>
                </div>
                <h5 class="text-muted mb-2">لا توجد جلسات تسميع</h5>
                <p class="text-muted small">لم يتم تسجيل أي جلسات تسميع حتى الآن</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table sessions-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نوع الجلسة</th>
                            <th>الجزء/الصفحات</th>
                            <th>الأخطاء</th>
                            <th>الترددات</th>
                            <th>الدرجة</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>اليوم</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $index => $session)
                        <tr>
                            <td>{{ $sessions->firstItem() + $index }}</td>
                            <td>
                                <span class="type-badge {{ $session->type }}">
                                    {{ $session->type_label }}
                                </span>
                            </td>
                            <td>
                                @if($session->parts_label !== '-')
                                    <span class="part-info">{{ $session->parts_label }}</span>
                                @elseif($session->start_page && $session->end_page)
                                    @if($session->start_page === $session->end_page)
                                        <span class="part-info">صفحة {{ $session->start_page }}</span>
                                    @else
                                        <span class="part-info">{{ $session->start_page }} - {{ $session->end_page }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($session->total_errors > 0)
                                    <span class="errors-val">{{ $session->total_errors }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if($session->total_hesitations > 0)
                                    <span class="hesitations-val">{{ $session->total_hesitations }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if($session->final_score !== null)
                                    <span class="score-val">{{ number_format($session->final_score, 1) }}</span>
                                    <span class="score-max">/ {{ $session->max_score }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge bg-{{ $session->status_color }}">
                                    {{ $session->status_label }}
                                </span>
                            </td>
                            <td>
                                <span class="date-val">{{ $session->session_date->format('Y/m/d') }}</span>
                            </td>
                            <td>
                                <span class="day-badge">{{ $session->day }}</span>
                            </td>
                            <td>
                                <a href="{{ route('student.sessions.show', $session->id) }}" class="view-btn" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection