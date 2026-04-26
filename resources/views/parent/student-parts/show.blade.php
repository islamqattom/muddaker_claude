@extends('layouts.app')

@section('title', 'سجل أجزاء الطالب — ' . $student->user->name)
@section('page-title', 'أجزاء الطالب')
@section('role-label', 'ولي الأمر')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection

@push('styles')
<style>
    .parts-table th { background: #f1f3f5; font-weight: 600; }
    .parts-table .completed { background-color: #d4edda !important; }
    .parts-table .not-completed { background-color: #f8d7da !important; }
    .override-badge {
        background: #fff3cd;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 4px;
    }
    .status-mafsil { color: #0d6efd; font-weight: bold; }
    .status-mutqin { color: #28a745; font-weight: bold; }
    .status-not-completed { color: #dc3545; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book-quran"></i> سجل أجزاء الطالب — {{ $student->user->name }}</h2>
        <a href="{{ route('parent.student-parts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> رجوع
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info mb-4">
        <div>
            <strong>عدد المفصل:</strong> {{ $mafsilCount }}
        </div>
        <div class="mt-1">
            <strong>المفصل + المتقن:</strong> {{ $totalMafsilAndMutqin }}
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered parts-table">
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
                            @endphp
                            <tr class="{{ $isCompleted ? 'completed' : 'not-completed' }}">
                                <td>{{ $part->part_number }}</td>
                                <td>{{ $part->name }}</td>
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
                                            'mafsil' => 'status-mafsil',
                                            'recitation' => $isCompleted ? 'status-mutqin' : 'status-not-completed',
                                            default => 'status-not-completed'
                                        };
                                    @endphp
                                    <span class="{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if($isOverride)
                                        <span class="override-badge">يدوي</span>
                                    @endif
                                </td>
                                <td>{{ $record ? $record->total_errors : '-' }}</td>
                                <td>{{ $record ? $record->total_hesitations : '-' }}</td>
                                <td>{{ $record && $record->total_score !== null ? number_format($record->total_score, 1) . '%' : '-' }}</td>
                                <td>{{ $record && $record->last_recitation_date ? $record->last_recitation_date->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection