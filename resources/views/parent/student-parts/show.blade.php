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

@push('styles')
<style>
    :root {
        --primary: #182B48;
        --primary-light: #2A4570;
        --primary-lighter: #3D5A80;
        --gold: #d4a030;
        --gold-dark: #B4802C;
        --gold-light: #E8C45A;
        --success: #27ae60;
        --success-light: rgba(39,174,96,0.12);
        --success-row: rgba(39,174,96,0.08);
        --danger: #c0392b;
        --info: #3498db;
        --bg-main: #f8fafc;
        --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
    }

    body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

    .btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 16px;
        background: #fff;
        color: var(--primary); border: 2px solid var(--primary);
        border-radius: 9px;
        font-weight: 600; font-size: 13px;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .btn-back:hover {
        background: var(--primary);
        color: #fff;
    }
    .btn-back i { color: var(--gold); }

    .stats-card {
        background: #fff;
        border-radius: 13px;
        padding: 14px 16px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(24,43,72,0.05);
        display: flex;
        align-items: center;
        gap: 12px;
        height: 100%;
    }
    .stats-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .stats-icon.gold { background: linear-gradient(135deg, var(--gold), var(--gold-dark)); color: #fff; }
    .stats-icon.success { background: linear-gradient(135deg, var(--success), #219a52); color: #fff; }
    .stats-icon.info { background: linear-gradient(135deg, var(--info), #2980b9); color: #fff; }
    .stats-text h3 { color: var(--primary); font-size: 22px; font-weight: 800; margin: 0 0 2px 0; line-height: 1.1; }
    .stats-text p { color: #7f8c8d; font-size: 12px; margin: 0; }

    .data-table tr.row-success {
        background: var(--success-row);
    }
    .data-table tr.row-success:hover {
        background: rgba(39,174,96,0.14);
    }
    .data-table tr.row-success td:first-child {
        border-right: 4px solid var(--success);
    }

    .status-badge.mafsil { background: rgba(52,152,219,0.15); color: var(--info); }
    .status-badge.mutqin { background: var(--success-light); color: var(--success); }
    .status-badge.not-completed { background: rgba(192,57,43,0.15); color: var(--danger); }

    .score-high { color: var(--success); font-weight: 700; }
    .score-mid { color: var(--gold); font-weight: 700; }
    .score-low { color: var(--danger); font-weight: 700; }

    .override-badge {
        background: rgba(212,160,48,0.2);
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 4px;
        color: var(--gold-dark);
        margin-right: 6px;
    }

    .alert-success {
        background: rgba(39,174,96,0.1);
        border: 1px solid rgba(39,174,96,0.3);
        border-radius: 13px;
        color: var(--success);
        padding: 12px 16px;
    }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start; }
        .stats-row { flex-direction: column; }

        .data-table thead { display: none; }
        .data-table, .data-table tbody, .data-table tr, .data-table td { display: block; width: 100%; }
        .data-table tr {
            background: #fff;
            border: 1px solid #eef1f6;
            border-radius: 12px;
            margin-bottom: 12px;
            padding: 10px 12px;
            box-shadow: 0 2px 6px rgba(24,43,72,0.05);
        }
        .data-table tr.row-success { background: var(--success-row); }
        .data-table td {
            text-align: right;
            padding: 6px 0;
            border-bottom: 1px dashed #eef1f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .data-table td:last-child { border-bottom: none; padding-top: 10px; }
        .data-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--primary);
            font-size: 12px;
            flex-shrink: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-book-quran"></i>
        </div>
        <div class="header-text">
            <h4>سجل أجزاء الطالب — {{ $student->user->name }}</h4>
            <p>تابع تقدم ابنك في حفظ أجزاء القرآن الكريم</p>
        </div>
    </div>
    <a href="{{ route('parent.student-parts.index') }}" class="btn-back">
        <i class="fas fa-arrow-right"></i>
        رجوع
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon gold">
                <i class="fas fa-star"></i>
            </div>
            <div class="stats-text">
                <h3>{{ $mafsilCount }}</h3>
                <p>عدد المفصل</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-text">
                <h3>{{ $totalMafsilAndMutqin }}</h3>
                <p>المفصل + المتقن</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stats-text">
                <h3>{{ $parts->count() }}</h3>
                <p>إجمالي الأجزاء</p>
            </div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-list"></i>
            <span>سجل الأجزاء</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الجزء</th>
                    <th>الحالة</th>
                    <th>الأخطاء</th>
                    <th>الترددات</th>
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
                            $isOverride && $recordMasteryStatus === 'مفصل' => 'مفصل',
                            $sessionType === 'mafsil' => 'مفصل',
                            $sessionType === 'recitation' && $isCompleted => 'متقن',
                            $sessionType === 'recitation' && !$isCompleted => 'غير متقن',
                            default => 'غير متقن'
                        };
                        $statusClass = match($statusLabel) {
                            'مفصل' => 'mafsil',
                            'متقن' => 'mutqin',
                            default => 'not-completed'
                        };
                        $score = $record && $record->total_score !== null ? $record->total_score : null;
                        $scoreClass = match(true) {
                            $score !== null && $score >= 90 => 'score-high',
                            $score !== null && $score >= 70 => 'score-mid',
                            $score !== null => 'score-low',
                            default => ''
                        };
                        $rowClass = in_array($statusLabel, ['مفصل', 'متقن'], true) ? 'row-success' : '';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td data-label="الجزء"><strong>{{ $part->part_number }}</strong></td>
                        <td data-label="اسم الجزء">{{ $part->name }}</td>
                        <td data-label="الحالة">
                            <span class="status-badge {{ $statusClass }}">
                                @if($statusLabel === 'مفصل')<i class="fas fa-star"></i>
                                @elseif($statusLabel === 'متقن')<i class="fas fa-check"></i>
                                @else<i class="fas fa-times"></i>
                                @endif
                                {{ $statusLabel }}
                            </span>
                            @if($isOverride)
                                <span class="override-badge">يدوي</span>
                            @endif
                        </td>
                        <td data-label="الأخطاء">{{ $record ? $record->total_errors : '-' }}</td>
                        <td data-label="الترددات">{{ $record ? $record->total_hesitations : '-' }}</td>
                        <td data-label="الدرجة" class="{{ $scoreClass }}">{{ $score !== null ? number_format($score, 1) . '%' : '-' }}</td>
                        <td data-label="آخر تسميع">{{ $record && $record->last_recitation_date ? $record->last_recitation_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
