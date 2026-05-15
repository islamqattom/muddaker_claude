@extends('layouts.app')

@section('title', 'أحداث الحفظ الجماعي')
@section('page-title', 'أحداث الحفظ الجماعي')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.events-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.events-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.events-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.events-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.events-page .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(212,160,48,0.35);
}

.events-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.events-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.events-page .event-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.events-page .event-card.accent-primary { border-right: 4px solid var(--navy); }
.events-page .event-card.accent-success { border-right: 4px solid var(--success); }

.events-page .event-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.events-page .event-header.success { background: linear-gradient(135deg, var(--success), #2ecc71); }
.events-page .event-header i { color: var(--gold); font-size: 20px; }
.events-page .event-header.success i { color: white; }
.events-page .event-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; }

.events-page .events-table { width: 100%; border-collapse: collapse; margin: 0; }
.events-page .events-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.events-page .events-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.events-page .events-table tbody tr { transition: all 0.2s ease; }
.events-page .events-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }

.events-page .progress-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    background: linear-gradient(135deg, var(--success), #2ecc71);
    color: white;
}

.events-page .view-btn {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(24,43,72,0.1);
    background: white;
    color: var(--navy);
    transition: all 0.2s ease;
}

.events-page .view-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(24,43,72,0.2); background: var(--navy); color: white; }

.events-page .join-btn {
    background: linear-gradient(135deg, var(--success), #2ecc71);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.2s ease;
    display: inline-flex; align-items: center; gap: 6px;
}

.events-page .join-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(39,174,96,0.3); color: white; }

.events-page .participant-badge {
    background: rgba(24,43,72,0.06);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--navy);
}

.events-page .empty-state { text-align: center; padding: 80px 20px; }
.events-page .empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.06), rgba(42,69,112,0.08));
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
}
.events-page .empty-icon i { font-size: 40px; color: var(--navy); opacity: 0.4; }

@media (max-width: 992px) {
    .events-page .page-hero { padding: 20px 24px; }
    .events-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .events-page .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="events-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h1 class="hero-title">الأحداث الجماعية</h1>
                <p class="hero-sub">أحداث الحفظ الجماعي والتحديات</p>
            </div>
        </div>
    </div>

    @if($myEvents->count() > 0)
    <div class="event-card accent-primary">
        <div class="event-header">
            <i class="fas fa-star"></i>
            <h5>أحداثي النشطة</h5>
        </div>
        <div class="table-responsive">
            <table class="events-table">
                <thead>
                    <tr>
                        <th>الحدث</th>
                        <th>الجزء</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>المشاركون</th>
                        <th>التقدم</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($myEvents as $event)
                    @php
                        $participants = $event->participants->count();
                        $progress = $event->progress->last();
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold" style="color: var(--navy);">{{ $event->name }}</div>
                            <small class="text-muted">{{ $event->supervisor?->name ?? '-' }}</small>
                        </td>
                        <td><span class="juz-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">الجزء {{ $event->juz_number }}</span></td>
                        <td><small>{{ $event->start_date->format('Y-m-d') }}</small></td>
                        <td><small>{{ $event->end_date->format('Y-m-d') }}</small></td>
                        <td><span class="participant-badge">{{ $participants }} طالب</span></td>
                        <td>
                            @if($progress)
                                <span class="progress-badge">{{ $progress->completion_percentage }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('student.events.show', $event) }}" class="view-btn" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($availableEvents->count() > 0)
    <div class="event-card accent-success">
        <div class="event-header success">
            <i class="fas fa-plus-circle"></i>
            <h5>أحداث متاحة للانضمام</h5>
        </div>
        <div class="table-responsive">
            <table class="events-table">
                <thead>
                    <tr>
                        <th>الحدث</th>
                        <th>الجزء</th>
                        <th>المشرف</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>المشاركون</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availableEvents as $event)
                    <tr>
                        <td class="fw-semibold" style="color: var(--navy);">{{ $event->name }}</td>
                        <td><span class="juz-badge" style="background: rgba(24,43,72,0.06); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--navy);">الجزء {{ $event->juz_number }}</span></td>
                        <td><small class="text-muted">{{ $event->supervisor?->name ?? '-' }}</small></td>
                        <td><small>{{ $event->start_date->format('Y-m-d') }}</small></td>
                        <td><small>{{ $event->end_date->format('Y-m-d') }}</small></td>
                        <td><span class="participant-badge">{{ $event->participants->count() }}</span></td>
                        <td>
                            <form action="{{ route('student.events.join', $event) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="join-btn">
                                    <i class="fas fa-plus"></i>انضم
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($myEvents->isEmpty() && $availableEvents->isEmpty())
    <div class="event-card">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-users"></i>
            </div>
            <h5 class="mb-2" style="color: var(--navy); font-weight: 700;">لا توجد أحداث جماعية</h5>
            <p class="text-muted mb-3">لا توجد حالياً أحداث حفظ جماعي متاحة</p>
            <p class="small text-muted mb-0">تحدث مع المشرف لإنشاء حدث جديد!</p>
        </div>
    </div>
    @endif
</div>
@endsection