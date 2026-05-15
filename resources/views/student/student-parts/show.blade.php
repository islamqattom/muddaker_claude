@extends('layouts.app')

@section('title', 'سجل أجزاء الطالب')
@section('page-title', 'أجزاء الطالب')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.parts-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.parts-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.parts-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.parts-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.parts-page .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(212,160,48,0.35);
}

.parts-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.parts-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.parts-page .stats-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 24px;
    margin-bottom: 24px;
}

.parts-page .stat-item {
    text-align: center;
    padding: 16px;
}

.parts-page .stat-icon {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: 0 auto 12px;
}

.parts-page .stat-icon.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: var(--gold); }
.parts-page .stat-icon.success { background: linear-gradient(135deg, var(--success), #2ecc71); color: white; }

.parts-page .stat-value { font-size: 28px; font-weight: 800; color: var(--navy); line-height: 1; }
.parts-page .stat-label { font-size: 12px; color: #6c7a89; font-weight: 500; margin-top: 4px; }

.parts-page .parts-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.parts-page .parts-card-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
}

.parts-page .parts-card-header h5 { color: white; font-weight: 700; margin: 0; font-size: 15px; display: flex; align-items: center; gap: 10px; }
.parts-page .parts-card-header h5 i { color: var(--gold); }

.parts-page .parts-table { width: 100%; border-collapse: collapse; margin: 0; }
.parts-page .parts-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.parts-page .parts-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.parts-page .parts-table tbody tr { transition: all 0.2s ease; }
.parts-page .parts-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }
.parts-page .parts-table tbody tr.completed { background: rgba(39,174,96,0.08); }
.parts-page .parts-table tbody tr.not-completed { background: rgba(192,57,43,0.05); }

.parts-page .status-badge {
    padding: 6px 14px; border-radius: 10px;
    font-size: 12px; font-weight: 600;
}
.parts-page .status-badge.mafsil { background: rgba(52,152,219,0.15); color: var(--info); }
.parts-page .status-badge.mutqin { background: rgba(39,174,96,0.15); color: var(--success); }
.parts-page .status-badge.not-completed { background: rgba(192,57,43,0.15); color: var(--danger); }

.parts-page .score-val { font-weight: 700; color: var(--navy); }
.parts-page .errors-val { color: var(--danger); font-weight: 700; }
.parts-page .hesitations-val { color: var(--warning); font-weight: 700; }

.parts-page .empty-state { text-align: center; padding: 60px 20px; }
.parts-page .empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, rgba(24,43,72,0.06), rgba(42,69,112,0.08));
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
}
.parts-page .empty-icon i { font-size: 36px; color: var(--navy); opacity: 0.4; }

@media (max-width: 992px) {
    .parts-page .page-hero { padding: 20px 24px; }
    .parts-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .parts-page .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="parts-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-book-quran"></i>
            </div>
            <div>
                <h1 class="hero-title">سجل أجزاء الطالب</h1>
                <p class="hero-sub">{{ $student->user->name }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" style="border-radius: 12px; padding: 16px 20px; background: rgba(39,174,96,0.15); border-color: rgba(39,174,96,0.3); color: #27ae60;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="stats-card">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="stat-item">
                    <div class="stat-icon primary">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-value">{{ $mafsilCount }}</div>
                    <div class="stat-label">عدد المفصل</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-item">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $totalMafsilAndMutqin }}</div>
                    <div class="stat-label">المفصل + المتقن</div>
                </div>
            </div>
        </div>
    </div>

    <div class="parts-card">
        <div class="parts-card-header">
            <h5><i class="fas fa-list"></i>جدول الأجزاء</h5>
        </div>
        <div class="table-responsive">
            <table class="parts-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الجزء</th>
                        <th>الحالة</th>
                        <th>الأخطاء</th>
                        <th>التلعثم</th>
                        <th>الدرجة</th>
                        <th>آخر تسميع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                        @php
                            $record = $records->get($part->part_number);
                            $isCompleted = $record && $record->is_completed;
                        @endphp
                        <tr class="{{ $isCompleted ? 'completed' : 'not-completed' }}">
                            <td><span style="color: var(--gold); font-weight: 700;">{{ $part->part_number }}</span></td>
                            <td class="fw-semibold" style="color: var(--navy);">{{ $part->name }}</td>
                            <td>
                                @php
                                    $sessionType = $partSessionTypes[$part->part_number] ?? null;
                                    $statusLabel = match(true) {
                                        $sessionType === 'mafsil' => 'مفصل',
                                        $sessionType === 'recitation' && $isCompleted => 'متقن',
                                        $sessionType === 'recitation' && !$isCompleted => 'غير متقن',
                                        default => 'غير متقن'
                                    };
                                    $statusClass = match($sessionType) {
                                        'mafsil' => 'mafsil',
                                        'recitation' => $isCompleted ? 'mutqin' : 'not-completed',
                                        default => 'not-completed'
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @if($record && $record->total_errors !== null)
                                    <span class="errors-val">{{ $record->total_errors }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record && $record->total_hesitations !== null)
                                    <span class="hesitations-val">{{ $record->total_hesitations }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record && $record->total_score !== null)
                                    <span class="score-val">{{ number_format($record->total_score, 1) }}%</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record && $record->last_recitation_date)
                                    <span class="date-val">{{ $record->last_recitation_date->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection