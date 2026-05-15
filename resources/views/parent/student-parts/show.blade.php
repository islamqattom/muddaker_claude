@extends('layouts.app')
@section('title', 'سجل أجزاء الطالب — ' . $student->user->name)
@section('page-title', 'أجزاء الطالب')
@section('role-label', 'ولي الأمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('parent.student-parts.index') }}">أجزاء الأبناء</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name }}</li>
@endsection
@section('content')

<div class="portal-container">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon primary">
                <i class="fas fa-book-quran"></i>
            </div>
            <div class="header-text">
                <h4>سجل أجزاء الطالب — {{ $student->user->name }}</h4>
                <p>تابع تقدم ابنك في حفظ أجزاء القرآن الكريم</p>
            </div>
        </div>
        <a href="{{ route('parent.student-parts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right"></i>
            <span>رجوع</span>
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="stats-mini-cards mb-4">
        <div class="stat-mini success">
            <div class="stat-mini-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-mini-content">
                <span class="stat-mini-value">{{ $mafsilCount }}</span>
                <span class="stat-mini-label">عدد المفصل</span>
            </div>
        </div>
        <div class="stat-mini primary">
            <div class="stat-mini-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-mini-content">
                <span class="stat-mini-value">{{ $totalMafsilAndMutqin }}</span>
                <span class="stat-mini-label">المفصل + المتقن</span>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon">
                    <i class="fas fa-list"></i>
                </div>
                <span>سجل الأجزاء</span>
            </div>
        </div>
        <div class="card-body-custom">
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
                                $isOverride = $record && $record->is_manual_override;
                                $sessionType = $partSessionTypes[$part->part_number] ?? null;
                                $recordMasteryStatus = $record->mastery_status ?? null;
                                $statusLabel = match(true) {
                                    $isOverride && $recordMasteryStatus === 'متقن' => 'متقن',
                                    $isOverride && $recordMasteryStatus === 'غير متقن' => 'غير متقن',
                                    $sessionType === 'mafsil' => 'مفصل',
                                    $sessionType === 'recitation' && $isCompleted => 'متقن',
                                    $sessionType === 'recitation' && !$isCompleted => 'غير متقن',
                                    default => 'غير متقن'
                                };
                            @endphp
                            <tr class="{{ $isCompleted ? 'completed' : 'not-completed' }}">
                                <td class="fw-bold">{{ $part->part_number }}</td>
                                <td class="fw-bold text-primary">{{ $part->name }}</td>
                                <td>
                                    <span class="status-badge-custom {{ str_replace('status-', '', match($statusLabel) { 'مفصل' => 'status-mafsil', 'متقن' => 'status-mutqin', default => 'status-not-completed' }) }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if($isOverride)
                                        <span class="override-badge">يدوي</span>
                                    @endif
                                </td>
                                <td>{{ $record ? $record->total_errors : '-' }}</td>
                                <td>{{ $record ? $record->total_hesitations : '-' }}</td>
                                <td>
                                    @if($record && $record->total_score !== null)
                                        <span class="score-value">{{ number_format($record->total_score, 1) }}%</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record && $record->last_recitation_date)
                                        <span class="date-value">{{ $record->last_recitation_date->format('Y-m-d') }}</span>
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

.header-icon.primary {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030;
}

.header-text h4 {
    color: #182B48;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.header-text p { color: #95a5a6; font-size: 14px; margin: 0; }

.stats-mini-cards {
    display: flex;
    gap: 15px;
}

.stat-mini {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 24px;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.stat-mini.success { border-right: 4px solid #16835a; }
.stat-mini.primary { border-right: 4px solid #182B48; }

.stat-mini-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-mini.success .stat-mini-icon {
    background: rgba(39,174,96,0.12);
    color: #16835a;
}

.stat-mini.primary .stat-mini-icon {
    background: rgba(24,43,72,0.1);
    color: #182B48;
}

.stat-mini-content { display: flex; flex-direction: column; }

.stat-mini-value {
    font-size: 22px;
    font-weight: 800;
    color: #182B48;
    line-height: 1;
}

.stat-mini-label {
    font-size: 12px;
    color: #95a5a6;
}

.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 22px;
}

.alert-success {
    background: rgba(39,174,96,0.1);
    color: #16835a;
}

.table-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
}

.table-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.card-body-custom { padding: 0; }

.table-responsive { overflow-x: auto; }

.parts-table {
    width: 100%;
    border-collapse: collapse;
}

.parts-table thead { background: #f8f9fa; }

.parts-table th {
    padding: 18px 22px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.parts-table td {
    padding: 16px 22px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.parts-table tbody tr { transition: all 0.2s; }
.parts-table tbody tr:hover { background: #fafbfc; }

.parts-table tbody tr.completed { background: rgba(39,174,96,0.05); }
.parts-table tbody tr.not-completed { background: rgba(192,57,43,0.05); }

.parts-table tbody tr:last-child td { border-bottom: none; }

.status-badge-custom {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
}

.status-badge-custom.status-mafsil {
    background: rgba(24,43,72,0.1);
    color: #182B48;
}

.status-badge-custom.status-mutqin {
    background: rgba(39,174,96,0.12);
    color: #16835a;
}

.status-badge-custom.status-not-completed {
    background: rgba(192,57,43,0.12);
    color: #a82e1c;
}

.override-badge {
    background: #fff3cd;
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 6px;
    margin-right: 6px;
    color: #856404;
    font-weight: 600;
}

.score-value {
    font-weight: 700;
    color: #182B48;
}

.date-value {
    color: #7f8c8d;
    font-size: 13px;
}
</style>
@endsection